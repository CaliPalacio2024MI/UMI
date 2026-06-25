<?php

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facturacion\Billing;
use App\Models\Facturacion\BillingConcept;
use App\Models\Users\User;
use App\Models\Users\Period; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB; 
use Symfony\Component\HttpFoundation\StreamedResponse;

class BillingController extends Controller
{ 
    public function index(Request $request)
    {
        Carbon::setLocale('es');
        $user = Auth::user();
        
        // 1. Obtener ID de Institución
        $institutionId = session('active_institution_id') ?? $user->institution_id;

        // 2. Obtener Periodos y Calcular Meses (Lógica movida antes del return)
        $periods = Period::orderBy('start_date', 'desc')->get();

        $periods->map(function ($period) {
            $fechasConfiguradas = $period->payment_dates ?? [];

            if ($period->start_date && $period->end_date) {
                $start = Carbon::parse($period->start_date)->startOfMonth();
                $end = Carbon::parse($period->end_date)->endOfMonth();
                
                $monthRange = CarbonPeriod::create($start, '1 month', $end);
                $months = [];
                $index = 0; 

                foreach ($monthRange as $date) {
                    if (isset($fechasConfiguradas[$index])) {
                        $fechaVencimiento = $fechasConfiguradas[$index]; 
                    } else {
                        $fechaVencimiento = $date->format('Y-m-d'); 
                    }

                    $months[] = [
                        'label' => ucfirst($date->translatedFormat('F Y')), 
                        'key'   => $date->format('Y-m'),
                        'date'  => $fechaVencimiento 
                    ];
                    $index++;
                }
                $period->setAttribute('meses_calculados', $months);
            } else {
                $period->setAttribute('meses_calculados', []);
            }
            return $period;
        });

        // 3. Obtener Conceptos
        $conceptosDisponibles = BillingConcept::where('institution_id', $institutionId)
                                ->where('is_active', 1)
                                ->orderBy('concept', 'asc')
                                ->get();

        // 4. Lógica Diferenciada (Admin vs Alumno)
        $isAdmin = $user->hasActiveRole('master') || $user->hasActiveRole('control_administrativo');
        
        $viewData = [
            'periods' => $periods, 
            'conceptosDisponibles' => $conceptosDisponibles,
            'usersWithBillings' => null, 
            'billings' => null           
        ];

        if ($isAdmin) {
            // --- ADMINISTRADOR ---
            $query = User::query();
            
            if ($request->filled('search')) {
                $searchTerm = $request->input('search'); // No usamos strtolower aquí para dejar que BD decida o usar LIKE
                
                $query->where(function($q) use ($searchTerm) {
                    // 1. Búsqueda por Nombre Completo (Concatenado)
                    // Esto permite buscar "Pagador Listo" y encontrarlo aunque estén en columnas separadas.
                    // Usamos COALESCE para evitar problemas si apellido_materno es null.
                    $q->where(DB::raw("CONCAT(nombre, ' ', apellido_paterno, ' ', COALESCE(apellido_materno, ''))"), 'LIKE', "%{$searchTerm}%")
                      
                      // 2. Búsqueda por Email
                      ->orWhere('email', 'like', "%{$searchTerm}%")
                      
                      // 3. Búsqueda en Facturas (Concepto o Folio)
                      ->orWhereHas('billings', function ($qBilling) use ($searchTerm) {
                          $qBilling->where('concepto', 'like', "%{$searchTerm}%")
                                   ->orWhere('factura_uid', 'like', "%{$searchTerm}%");
                      });
                });
            }

            // Solo alumnos
            $query->whereHas('roles', function ($q) {
                $q->whereIn('name', ['estudiante']);
            });
            $query->distinct(); 

            $usersWithBillings = $query->with(['billings.payments', 'roles'])->orderBy('nombre')->get();

            // Filtro de Estatus
                if ($request->filled('status')) {
                $statusFilter = $request->input('status');
                $selectedPeriodId = $request->input('period_id');

                // Iteramos sobre cada usuario para filtrar sus facturas INTERNAS
                $usersWithBillings = $usersWithBillings->map(function ($user) use ($statusFilter, $selectedPeriodId) {
                    
                    // Filtramos la colección de facturas de este usuario
                    $facturasFiltradas = $user->billings->filter(function ($billing) use ($statusFilter, $selectedPeriodId) {
                        
                        // Validar Periodo (si está seleccionado)
                        if ($selectedPeriodId && $billing->period_id != $selectedPeriodId) {
                            return false;
                        }

                        // Validar Estatus (Estricto)
                        return $billing->computed_status === $statusFilter;
                    });

                    // Sobrescribimos la relación para que la vista solo vea las filtradas
                    $user->setRelation('billings', $facturasFiltradas);

                    return $user;
                });

                // 2. Eliminamos a los usuarios que se quedaron sin facturas tras el filtro
                $usersWithBillings = $usersWithBillings->filter(function ($user) {
                    return $user->billings->isNotEmpty();
                });
            }

            $viewData['usersWithBillings'] = $usersWithBillings;

        } else {
            // --- ALUMNO (Lógica de facturas directa) ---
            $query = Billing::where('user_id', $user->id)->with('payments');

            if ($request->filled('search')) {
                $searchTerm = strtolower($request->input('search')); 
                $query->where(function ($q) use ($searchTerm) {
                    $q->where(DB::raw('LOWER(concepto)'), 'like', "%{$searchTerm}%")
                      ->orWhere('factura_uid', 'like', "%{$searchTerm}%");
                });
            }
            
            if ($request->filled('period_id')) {
                $query->where('period_id', $request->input('period_id'));
            }

            $billings = $query->orderBy('fecha_vencimiento', 'desc')->get();

            // Filtro estricto para alumno
            if ($request->filled('status')) {
                $statusFilter = $request->input('status');
                $billings = $billings->filter(fn($b) => $b->computed_status === $statusFilter);
            }
            $viewData['billings'] = $billings;
        }

        
        // 5. Alertas de Vencimiento
        $alertasVencimiento = [];
        
        if (!$isAdmin) {
            $facturasPorVencer = Billing::with('payments') 
                ->where('user_id', $user->id)
                ->whereIn('status', ['Pendiente', 'Abonado']) 
                ->whereDate('fecha_vencimiento', '<=', Carbon::today()->addDays(7))
                
                // Filtro SQL: Ignorar EXT y RE desde la base de datos
                ->where('factura_uid', 'not like', 'EXT-%')
                ->get();

            foreach ($facturasPorVencer as $factura) {
                // Filtro PHP (Doble Seguridad)
                // Si por alguna razón pasó el filtro SQL, lo matamos aquí.
                if (str_starts_with($factura->factura_uid, 'EXT-')) continue;

                if ($factura->computed_status === 'Pagada') continue; 

                $diasRestantes = Carbon::today()->diffInDays(Carbon::parse($factura->fecha_vencimiento)->startOfDay(), false);
                $agregarAlerta = false;
                $tipo = 'info'; $titulo = ''; $mensaje = '';

                if ($diasRestantes <= 7 && $diasRestantes >= 3) {
                    $titulo = "📅 Recordatorio";
                    $mensaje = "Tu factura '{$factura->concepto}' vence en {$diasRestantes} días.";
                    $agregarAlerta = true;
                } elseif ($diasRestantes <= 2 && $diasRestantes >= 0) {
                    $tipo = 'warning';
                    $titulo = "⚠ Pago Próximo";
                    $mensaje = "Tu factura '{$factura->concepto}' está por vencer.";
                    $agregarAlerta = true;
                } elseif ($diasRestantes < 0) {
                    $tipo = 'error';
                    $titulo = "⛔ AVISO DE BAJA";
                    $mensaje = "Tu factura venció hace " . abs($diasRestantes) . " días.";
                    $agregarAlerta = true;
                }

                if ($agregarAlerta) {
                    $alertasVencimiento[] = compact('titulo', 'mensaje', 'tipo');
                }
            }
        }
        $viewData['alertasVencimiento'] = $alertasVencimiento;
        // 6. Retorno ÚNICO a la vista
        return view('layouts.Facturacion.index', $viewData);
    }

    // 2. FUNCIÓN PARA CREAR (Genera UID Robusto y Respeta Fechas)
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasActiveRole('master') && !$user->hasActiveRole('control_administrativo')) abort(403);

        if ($request->input('porcentaje_cargo_moratorio') === '' || $request->input('porcentaje_cargo_moratorio') === null) {
            $request->merge(['porcentaje_cargo_moratorio' => null]);
        }
        if ($request->input('cargo_monetario') === '' || $request->input('cargo_monetario') === null) {
            $request->merge(['cargo_monetario' => null]);
        }

        $validated = $request->validate([
            'concepto'    => 'required|string|max:255',
            'monto'       => 'required|numeric|min:0',
            'porcentaje_cargo_moratorio' => 'nullable|numeric|min:0|max:100',
            'cargo_monetario' => 'nullable|numeric|min:0',
            'fecha'       => 'required|date', 
            'archivo'     => 'nullable|file|mimes:pdf|max:5120',
            'status'      => 'required|in:Pendiente,Pagada',
            'user_id'     => 'required|exists:users,id',
            'period_id'   => 'required|exists:periods,id',
            'archivo_xml' => 'nullable|file|mimes:xml|max:5120',
            'uid_prefix'  => 'nullable|string' // Recibimos MEN- o EXT- del hidden input
        ]);
        
        // A. OBTENER PREFIJO (EXT- o MEN-)
        $prefix = $request->input('uid_prefix', 'FAC-'); 

        // B. GENERAR UID ROBUSTO: PREFIJO + AÑOMESDIA + CONSECUTIVO (6 Dígitos)
        // Ejemplo: EXT-20251202000001
        $fechaHoy = now()->format('Ymd'); 
        $baseUid = $prefix . $fechaHoy;

        $ultimo = Billing::withTrashed()
                         ->where('factura_uid', 'like', $baseUid . '%')
                         ->orderBy('id', 'desc') // Ordenar por ID es más seguro
                         ->first();

        if ($ultimo) {
            // Tomar los últimos 6 dígitos
            $consecutivo = intval(substr($ultimo->factura_uid, -6)) + 1;
        } else {
            $consecutivo = 1;
        }
        
        $uidFinal = $baseUid . str_pad($consecutivo, 6, '0', STR_PAD_LEFT);

        $esMensualidad = str_starts_with((string) $prefix, 'MEN-');
        // Cargo moratorio desde la carrera del alumno (para mensualidades)
        $careerCargo = ['porcentaje' => null, 'monetario' => null];
        if ($esMensualidad) {
            $alumno = User::with('academicProfile')->find($validated['user_id']);
            if ($alumno && $alumno->academicProfile && $alumno->academicProfile->career_id) {
                $carrera = \App\Models\Users\Career::find($alumno->academicProfile->career_id);
                if ($carrera && $carrera->porcentaje_cargo_moratorio > 0) {
                    $careerCargo['porcentaje'] = $carrera->porcentaje_cargo_moratorio;
                    $careerCargo['monetario'] = round($validated['monto'] * ($carrera->porcentaje_cargo_moratorio / 100), 2);
                }
            }
        }

        // C. GUARDAR
        $filePath = $request->hasFile('archivo') ? $request->file('archivo')->store('facturas', 'public') : null;
        $xmlPath = $request->hasFile('archivo_xml') ? $request->file('archivo_xml')->store('facturas_xml', 'public') : null;

        Billing::create([
            'factura_uid'       => $uidFinal, // <--- UID NUEVO
            'user_id'           => $validated['user_id'],
            'period_id'         => $validated['period_id'],
            'concepto'          => $validated['concepto'],
            'monto'             => $validated['monto'],
            'porcentaje_cargo_moratorio' => $esMensualidad ? $careerCargo['porcentaje'] : ($validated['porcentaje_cargo_moratorio'] ?? null),
            'cargo_monetario' => $esMensualidad ? $careerCargo['monetario'] : ($validated['cargo_monetario'] ?? null),
            'fecha_vencimiento' => $validated['fecha'], // <--- RESPETAMOS LA FECHA DEL INPUT (NO "NOW")
            'archivo_path'      => $filePath,
            'xml_path'          => $xmlPath,
            'status'            => $validated['status'],
        ]);

        $anchor = '#factura-target-user-' . $validated['user_id'] . '-period-' . $validated['period_id'];
        
        return redirect()->to(route('Facturacion.index', ['period_id' => $validated['period_id']]) . $anchor)
                         ->with('success', 'Factura creada exitosamente. Folio: ' . $uidFinal);
    }

    public function destroy(Billing $billing)
    {
        $user = Auth::user();
        if (!$user->hasActiveRole('master') && !$user->hasActiveRole('control_administrativo')) abort(403);

        if ($billing->archivo_path && $billing->archivo_path !== 'facturas/placeholder.pdf') { 
            Storage::disk('public')->delete($billing->archivo_path);
        }
        if ($billing->xml_path) { 
            Storage::disk('public')->delete($billing->xml_path);
        }

        $userId = $billing->user_id;
        $periodId = $billing->period_id;

        $billing->delete();
        
        // REDIRECCIÓN CON ANCLAJE
        $anchor = '#factura-target-user-' . $userId . '-period-' . $periodId;

        return redirect()->to(route('Facturacion.index', ['period_id' => $periodId]) . $anchor)
                         ->with('success', 'Factura eliminada exitosamente.');
    }

     public function exportCsv(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasActiveRole('master') || $user->hasActiveRole('control_administrativo');

        // 1. INICIAR CONSULTA SEGURA
        // Si es Admin, inicia consultando todo. Si es Alumno, solo SU ID.
        $query = Billing::query()->with(['user', 'period']);

        if (!$isAdmin) {
            // ¡AQUÍ ESTÁ EL CANDADO DE SEGURIDAD! 🔒
            // Forzamos que solo traiga facturas de este usuario
            $query->where('user_id', $user->id);
        }

        // 2. Filtro: Periodo
        if ($request->filled('period_id')) {
            $query->where('period_id', $request->input('period_id'));
        }

        // 3. Filtro: Búsqueda (Inteligente)
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('concepto', 'like', "%{$searchTerm}%")
                  ->orWhere('factura_uid', 'like', "%{$searchTerm}%");
                
                // Solo permitimos buscar por nombre de usuario si es admin (por eficiencia y privacidad)
                // Aunque si es alumno, buscar por su propio nombre no hace daño.
                $q->orWhereHas('user', function ($qUser) use ($searchTerm) {
                    $qUser->where(DB::raw("CONCAT(nombre, ' ', apellido_paterno, ' ', COALESCE(apellido_materno, ''))"), 'LIKE', "%{$searchTerm}%")
                            ->orWhere('email', 'like', "%{$searchTerm}%");
                });
            });
        }

        // 4. Ordenamiento
        $query->orderBy('fecha_vencimiento', 'desc');

        // 5. Generar CSV (Streaming)
        return new StreamedResponse(function () use ($query, $request) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // BOM para Excel

            // Encabezados
            fputcsv($handle, [
                'Folio', 'Alumno', 'Concepto', 'Monto', 'Pagado', 'Saldo', 
                'Vencimiento', 'Estatus', 'Periodo'
            ]);

            // Obtenemos el filtro de estatus que viene de la vista (si existe)
            $statusFilter = $request->input('status');

            // Procesamos en lotes de 500 para no saturar el servidor
            $query->chunk(500, function ($facturas) use ($handle, $statusFilter) {
                foreach ($facturas as $factura) {
                    
                    // Calcular Estatus Real (Computed)
                    // Esto es vital porque el filtro de BD 'status' no sabe si una factura parcial ya está pagada
                    $estatusReal = $factura->computed_status; 

                    // Si hay filtro de estatus y no coincide, saltamos esta fila
                    if ($statusFilter && $estatusReal !== $statusFilter) {
                        continue;
                    }

                    // Datos calculados
                    $pagado = $factura->payments->sum('monto');
                    $saldo = $factura->monto - $pagado;
                    
                    $nombreAlumno = $factura->user 
                        ? $factura->user->nombre . ' ' . $factura->user->apellido_paterno 
                        : 'Eliminado';

                    $nombrePeriodo = $factura->period ? $factura->period->name : 'Sin Periodo';

                    fputcsv($handle, [
                        $factura->factura_uid,
                        $nombreAlumno,
                        $factura->concepto,
                        number_format($factura->monto, 2, '.', ''),
                        number_format($pagado, 2, '.', ''),
                        number_format($saldo, 2, '.', ''),
                        $factura->fecha_vencimiento,
                        $estatusReal, // Usamos el estatus real (Abonado/Pagada/Pendiente)
                        $nombrePeriodo
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Reporte_Facturacion_' . date('Y-m-d_H-i') . '.csv"',
        ]);
    }
    
}