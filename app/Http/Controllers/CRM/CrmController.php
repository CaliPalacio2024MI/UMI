<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comision;
use Illuminate\Support\Facades\Storage;
use App\Models\Lead;
use App\Models\Users\User;
use Carbon\Carbon;
use App\Models\Carrera;
use App\Models\Users\CareerClassification;
use App\Exports\EstadisticasExport;
use Maatwebsite\Excel\Facades\Excel;


class CrmController extends Controller
{
    public function leads()
    {
        $query = Lead::with(['seguimientos', 'ctp']);

        $rol = session('active_role_name');
        $userId = auth()->id();

        // SI ES CTP: solo sus leads asignados
        if ($rol === 'ctp') {
            $query->where('ctp_id', $userId);
        }

        // Master y Coordinador ven todo
        $leads = $query->orderBy('created_at', 'desc')->get();

        // Solo master y coordinador necesitan la lista de CTPs
        $ctps = [];
        if (in_array($rol, ['master', 'coordinador_ctp'])) {
            $ctps = \App\Models\Users\User::whereHas('roles', function ($q) {
                $q->where('name', 'ctp');
            })->get();
        }
         // ── LOGO PARA PDF ──
    $logoPath = public_path('images/LogoUMI-Blanco.png');
    $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
    $clasificaciones = \App\Models\Users\CareerClassification::orderBy('name')->get();

    return view('crm.leads', compact('leads', 'ctps', 'logoBase64', 'clasificaciones'));
    }

    public function exportar(Request $request)
    {
        $query = Lead::with([
            'seguimientos' => function ($q) {
                $q->orderBy('fecha', 'asc')->orderBy('hora', 'asc');
            },
            'ctp'
        ]);

        $rol = session('active_role_name');
        $userId = auth()->id();

        if ($rol === 'ctp') {
            $query->where('ctp_id', $userId);
        }

        if (in_array($rol, ['master', 'coordinador_ctp']) && $request->filled('ctp_id')) {
            $query->where('ctp_id', $request->ctp_id);
        }

        if ($request->filled('carrera_id')) {
            $query->where('carrera_id', $request->carrera_id);
        }

        if ($request->filled('nivel_educativo')) {
            $query->whereHas('carrera', function ($q) use ($request) {
                $q->where('career_classification_id', $request->nivel_educativo);
            });
        }

        if ($request->filled('estatus')) {
            $query->whereHas('seguimientos', function ($q) use ($request) {
                $q->whereIn('id', function ($sub) {
                    $sub->selectRaw('MAX(id)')
                        ->from('lead_seguimientos')
                        ->groupBy('lead_id');
                })->where('estado', $request->estatus);
            });
        }

        if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
            $query->whereHas('seguimientos', function ($q) use ($request) {
                $q->whereIn('id', function ($sub) {
                    $sub->selectRaw('MAX(id)')
                        ->from('lead_seguimientos')
                        ->groupBy('lead_id');
                });
                if ($request->filled('fecha_inicio'))
                    $q->whereDate('fecha', '>=', $request->fecha_inicio);
                if ($request->filled('fecha_fin'))
                    $q->whereDate('fecha', '<=', $request->fecha_fin);
            });
        }

        $leads = $query->get();
        $totalLeads = $leads->count();
        $hoy = Carbon::now()->startOfDay();

        // ================= ESTADOS =================
        $totalFrio = 0;
        $totalCaliente = 0;
        $totalAspirante = 0;
        $totalAlumno = 0;

        foreach ($leads as $lead) {
            $ultimoEstado = $lead->seguimientos->last()?->estado ?? 'Prospecto frío';
            match ($ultimoEstado) {
                'Prospecto frío'     => $totalFrio++,
                'Prospecto caliente' => $totalCaliente++,
                'Aspirante'          => $totalAspirante++,
                'Alumno'             => $totalAlumno++,
                default              => null,
            };
        }

        // ================= PORCENTAJES REALES =================
        $queryTotal = Lead::query();
        if ($rol === 'ctp') $queryTotal->where('ctp_id', $userId);
        if (in_array($rol, ['master', 'coordinador_ctp']) && $request->filled('ctp_id'))
            $queryTotal->where('ctp_id', $request->ctp_id);
        if ($request->filled('carrera_id'))
            $queryTotal->where('carrera_id', $request->carrera_id);
        if ($request->filled('nivel_educativo'))
            $queryTotal->whereHas('carrera', function ($q) use ($request) {
                $q->where('career_classification_id', $request->nivel_educativo);
            });
        if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
            $queryTotal->whereHas('seguimientos', function ($q) use ($request) {
                $q->whereIn('id', function ($sub) {
                    $sub->selectRaw('MAX(id)')
                        ->from('lead_seguimientos')
                        ->groupBy('lead_id');
                });
                if ($request->filled('fecha_inicio'))
                    $q->whereDate('fecha', '>=', $request->fecha_inicio);
                if ($request->filled('fecha_fin'))
                    $q->whereDate('fecha', '<=', $request->fecha_fin);
            });
        }

        $leadsReales = $queryTotal->with([
            'seguimientos' => function ($q) {
                $q->orderBy('fecha', 'asc')->orderBy('hora', 'asc');
            }
        ])->get();

        $totalFrioReal = 0;
        $totalCalienteReal = 0;
        $totalAspiranteReal = 0;
        $totalAlumnoReal = 0;

        foreach ($leadsReales as $lead) {
            $ultimoEstado = $lead->seguimientos->last()?->estado ?? null;
            if (!$ultimoEstado) continue;
            match ($ultimoEstado) {
                'Prospecto frío'     => $totalFrioReal++,
                'Prospecto caliente' => $totalCalienteReal++,
                'Aspirante'          => $totalAspiranteReal++,
                'Alumno'             => $totalAlumnoReal++,
                default              => null,
            };
        }

        $totalGeneral = $totalFrioReal + $totalCalienteReal + $totalAspiranteReal + $totalAlumnoReal;

        $porcentajeFrio      = $totalGeneral > 0 ? round(($totalFrioReal / $totalGeneral) * 100, 1) : 0;
        $porcentajeCaliente  = $totalGeneral > 0 ? round(($totalCalienteReal / $totalGeneral) * 100, 1) : 0;
        $porcentajeAspirante = $totalGeneral > 0 ? round(($totalAspiranteReal / $totalGeneral) * 100, 1) : 0;
        $porcentajeAlumno    = $totalGeneral > 0 ? round(($totalAlumnoReal / $totalGeneral) * 100, 1) : 0;

        // ================= TIEMPOS — mismo criterio que estadisticas =================
        $tiempos = [
            'Prospecto frío'     => [],
            'Prospecto caliente' => [],
            'Aspirante'          => [],
            'Alumno'             => [],
        ];

        foreach ($leads as $lead) {
            $seguimientos = $lead->seguimientos;
            if ($seguimientos->isEmpty()) continue;

            $segs = $seguimientos->values();

            foreach ($segs as $index => $seg) {
                $estado = $seg->estado;
                if (!isset($tiempos[$estado])) continue;

                $fechaEntrada = Carbon::parse($seg->fecha . ' ' . $seg->hora);
                $siguiente = $segs->get($index + 1);

                if ($estado === 'Alumno' && !$siguiente) {
                    $anterior = $segs->get($index - 1);
                    $fechaSalida = $fechaEntrada;
                    $fechaEntrada = $anterior
                        ? Carbon::parse($anterior->fecha . ' ' . $anterior->hora)
                        : $fechaEntrada;
                } else {
                    $fechaSalida = $siguiente
                        ? Carbon::parse($siguiente->fecha . ' ' . $siguiente->hora)
                        : $hoy;
                }

                $segundos = $fechaEntrada->diffInSeconds($fechaSalida, true);
                $tiempos[$estado][] = $segundos;
            }
        }

        $convertirSegundos = function (float $segundos): string {
            $seg  = (int) $segundos;
            $dias = intdiv($seg, 86400);
            $seg -= $dias * 86400;
            $hrs  = intdiv($seg, 3600);
            $seg -= $hrs * 3600;
            $mins = intdiv($seg, 60);

            $partes = [];
            if ($dias) $partes[] = "{$dias} d";
            if ($hrs)  $partes[] = "{$hrs} h";
            if ($mins) $partes[] = "{$mins} min";

            return implode(', ', $partes) ?: '0 min';
        };

        $promedioFrio      = count($tiempos['Prospecto frío']) > 0
            ? $convertirSegundos(array_sum($tiempos['Prospecto frío']) / count($tiempos['Prospecto frío'])) : '0 min';
        $promedioCaliente  = count($tiempos['Prospecto caliente']) > 0
            ? $convertirSegundos(array_sum($tiempos['Prospecto caliente']) / count($tiempos['Prospecto caliente'])) : '0 min';
        $promedioAspirante = count($tiempos['Aspirante']) > 0
            ? $convertirSegundos(array_sum($tiempos['Aspirante']) / count($tiempos['Aspirante'])) : '0 min';
        $promedioAlumno    = count($tiempos['Alumno']) > 0
            ? $convertirSegundos(array_sum($tiempos['Alumno']) / count($tiempos['Alumno'])) : '0 min';

        // ================= CONVERSIÓN GENERAL =================
        $porcentajeConversion = $totalGeneral > 0
            ? round(($totalAlumnoReal / $totalGeneral) * 100, 1)
            : 0;


            
        // ================= EXCEL =================
        $data = [
            ['RESUMEN'],
            ['Total Leads', $totalLeads],
            ['Total Alumnos', $totalAlumno],
            ['Conversión General', $porcentajeConversion.'%'],

            [], [],

            ['DISTRIBUCIÓN DE PROSPECTOS'],
            ['Estado', 'Total'],
            ['Prospecto Frío', $totalFrio],
            ['Prospecto Caliente', $totalCaliente],
            ['Aspirante', $totalAspirante],
            ['Alumno', $totalAlumno],

            [], [],

            ['TASA DE CONVERSIÓN'],
            ['Estado', 'Conversión', 'Tiempo Promedio'],
            ['Prospecto Frío',      $porcentajeFrio.'%',      $promedioFrio],
            ['Prospecto Caliente',  $porcentajeCaliente.'%',  $promedioCaliente],
            ['Aspirante',           $porcentajeAspirante.'%', $promedioAspirante],
            ['Alumno',              $porcentajeAlumno.'%',    $promedioAlumno],
        ];

        return Excel::download(new EstadisticasExport($data), 'estadisticas.xlsx');
    }

   

    public function estadisticas(Request $request)
    {
        $query = Lead::with([
            'seguimientos' => function ($q) {
                $q->orderBy('fecha', 'asc')->orderBy('hora', 'asc');
            },
            'ctp'
        ]);

        $rol = session('active_role_name');
        $userId = auth()->id();

        if ($rol === 'ctp') {
            $query->where('ctp_id', $userId);
        }

        if (in_array($rol, ['master', 'coordinador_ctp']) && $request->filled('ctp_id')) {
            $query->where('ctp_id', $request->ctp_id);
        }

        if ($request->filled('carrera_id')) {
            $query->where('carrera_id', $request->carrera_id);
        }

        if ($request->filled('nivel_educativo')) {
            $query->whereHas('carrera', function ($q) use ($request) {
                $q->where('career_classification_id', $request->nivel_educativo);
            });
        }
        if ($request->filled('estatus')) {
            $query->whereHas('seguimientos', function ($q) use ($request) {
                $q->whereIn('id', function ($sub) {
                    $sub->selectRaw('MAX(id)')
                        ->from('lead_seguimientos')
                        ->groupBy('lead_id');
                })->where('estado', $request->estatus);
            });
        }

        // Traemos todos los leads que tienen al menos un seguimiento en el rango
        if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
            $query->whereHas('seguimientos', function ($q) use ($request) {

                // Solo el último seguimiento de cada lead
                $q->whereIn('id', function ($sub) {
                    $sub->selectRaw('MAX(id)')
                        ->from('lead_seguimientos')
                        ->groupBy('lead_id');
                });

                if ($request->filled('fecha_inicio')) {
                    $q->whereDate('fecha', '>=', $request->fecha_inicio);
                }

                if ($request->filled('fecha_fin')) {
                    $q->whereDate('fecha', '<=', $request->fecha_fin);
                }
            });
        }

        $leads = $query->get();
        $totalLeads = $leads->count();
        
        $hoy = Carbon::now()->startOfDay();

        /* ===========================
        ESTADO ACTUAL
        (último estado del lead hoy, sin importar filtro de fecha)
        =========================== */

        $totalFrio = 0;
        $totalCaliente = 0;
        $totalAspirante = 0;
        $totalAlumno = 0;

        foreach ($leads as $lead) {
            // Último seguimiento real (sin filtro de fecha) = estado actual
            $ultimoEstado = $lead->seguimientos->last()?->estado ?? 'Prospecto frío';

            match ($ultimoEstado) {
                'Prospecto frío'     => $totalFrio++,
                'Prospecto caliente' => $totalCaliente++,
                'Aspirante'          => $totalAspirante++,
                'Alumno'             => $totalAlumno++,
                default              => null,
            };
        }

        /* ===========================
        PORCENTAJES
        =========================== */

        $totalEstados = $totalFrio + $totalCaliente + $totalAspirante + $totalAlumno;

        $porcentajeFrio      = $totalEstados > 0 ? round(($totalFrio / $totalEstados) * 100, 1) : 0;
        $porcentajeCaliente  = $totalEstados > 0 ? round(($totalCaliente / $totalEstados) * 100, 1) : 0;
        $porcentajeAspirante = $totalEstados > 0 ? round(($totalAspirante / $totalEstados) * 100, 1) : 0;
        $porcentajeAlumno    = $totalEstados > 0 ? round(($totalAlumno / $totalEstados) * 100, 1) : 0;

        /* ===========================
        TIEMPO PROMEDIO POR ESTADO
        Criterio: tiempo REAL que cada lead estuvo en cada estado
        - Si ya avanzó al siguiente: fecha_entrada_siguiente - fecha_entrada_estado
        - Si sigue en ese estado: hoy - fecha_entrada_estado
        =========================== */

        $tiempos = [
            'Prospecto frío'     => [],
            'Prospecto caliente' => [],
            'Aspirante'          => [],
            'Alumno'             => [],
        ];

        foreach ($leads as $lead) {

            $seguimientos = $lead->seguimientos; // ya ordenados asc

            if ($seguimientos->isEmpty()) continue;

            // Convertir a array indexado para poder acceder al siguiente
            $segs = $seguimientos->values();

            foreach ($segs as $index => $seg) {

                $estado = $seg->estado;

                if (!isset($tiempos[$estado])) continue;

                $fechaEntrada = Carbon::parse($seg->fecha . ' ' . $seg->hora);

                // Si existe un seguimiento posterior, usamos su fecha como fecha de salida
                // Si no existe (es el último = estado actual), usamos hoy
                $siguiente = $segs->get($index + 1);

                // Si es Alumno (estado final) y no tiene siguiente,
                // buscamos el seguimiento anterior (Aspirante) para calcular el tiempo de transición
                if ($estado === 'Alumno' && !$siguiente) {
                    $anterior = $segs->get($index - 1);
                    $fechaSalida = $fechaEntrada; // misma entrada
                    $fechaEntrada = $anterior
                        ? Carbon::parse($anterior->fecha . ' ' . $anterior->hora)
                        : $fechaEntrada;
                } else {
                    $fechaSalida = $siguiente
                        ? Carbon::parse($siguiente->fecha . ' ' . $siguiente->hora)
                        : $hoy;
                }

                $segundos = $fechaEntrada->diffInSeconds($fechaSalida, true);

                $tiempos[$estado][] = $segundos;
            }
        }
        $convertirSegundos = function (float $segundos): string {
            $seg  = (int) $segundos;
            $dias = intdiv($seg, 86400);
            $seg -= $dias * 86400;
            $hrs  = intdiv($seg, 3600);
            $seg -= $hrs * 3600;
            $mins = intdiv($seg, 60);

            $partes = [];
            if ($dias) $partes[] = "{$dias} d";
            if ($hrs)  $partes[] = "{$hrs} h";
            if ($mins) $partes[] = "{$mins} min";

            return implode(', ', $partes) ?: '0 min';
        };

        $promedioFrio = count($tiempos['Prospecto frío']) > 0
            ? $convertirSegundos(array_sum($tiempos['Prospecto frío']) / count($tiempos['Prospecto frío']))
            : '0 min';

        $promedioCaliente = count($tiempos['Prospecto caliente']) > 0
            ? $convertirSegundos(array_sum($tiempos['Prospecto caliente']) / count($tiempos['Prospecto caliente']))
            : '0 min';

        $promedioAspirante = count($tiempos['Aspirante']) > 0
            ? $convertirSegundos(array_sum($tiempos['Aspirante']) / count($tiempos['Aspirante']))
            : '0 min';

        $promedioAlumno = count($tiempos['Alumno']) > 0
            ? $convertirSegundos(array_sum($tiempos['Alumno']) / count($tiempos['Alumno']))
            : '0 min';

        /* ===========================
        CONVERSIÓN
        =========================== */

        $totalInteresados  = $totalFrio + $totalCaliente;
        $totalConvertidos  = $totalAspirante + $totalAlumno;

        $porcentajeConversion = $totalEstados > 0
            ? round(($totalAlumno / $totalEstados) * 100, 1)
            : 0;

        /* ===========================
        DATOS POR MES
        Criterio: Mes = cuando entró por primera vez al CRM (primer seguimiento).
        Estado = su último estado actual.
        =========================== */
        $porMes = [
            'Prospecto frío'     => array_fill(1, 12, 0),
            'Prospecto caliente' => array_fill(1, 12, 0),
            'Aspirante'          => array_fill(1, 12, 0),
            'Alumno'             => array_fill(1, 12, 0),
        ];

        foreach ($leads as $lead) {

            $seguimientos = $lead->seguimientos;

            if ($seguimientos->isEmpty()) continue;

            $ultimoSeg = $seguimientos->last();

            $mes = \Carbon\Carbon::parse($ultimoSeg->fecha)->month;
            $estado = $ultimoSeg->estado;

            // Aplicar filtro de fechas si existe (sobre el primer seguimiento)
            if ($request->filled('fecha_inicio')) {
                $fechaInicio = \Carbon\Carbon::parse($request->fecha_inicio)->startOfDay();
                if (\Carbon\Carbon::parse($ultimoSeg->fecha)->lt($fechaInicio)) continue;
            }

            if ($request->filled('fecha_fin')) {
                $fechaFin = \Carbon\Carbon::parse($request->fecha_fin)->endOfDay();
                if (\Carbon\Carbon::parse($ultimoSeg->fecha)->gt($fechaFin)) continue;
            }

            if (isset($porMes[$estado][$mes])) {
                $porMes[$estado][$mes]++;
            }
        }

        $frioPorMes      = $porMes['Prospecto frío'];
        $calientePorMes  = $porMes['Prospecto caliente'];
        $aspirantePorMes = $porMes['Aspirante'];
        $alumnoPorMes    = $porMes['Alumno'];


    // Total SIN filtro de estatus NI de fecha (universo real para la dona)
    $queryTotal = Lead::query();
    if ($rol === 'ctp') $queryTotal->where('ctp_id', $userId);
    if (in_array($rol, ['master', 'coordinador_ctp']) && $request->filled('ctp_id'))
        $queryTotal->where('ctp_id', $request->ctp_id);
    if ($request->filled('carrera_id'))
        $queryTotal->where('carrera_id', $request->carrera_id);
    if ($request->filled('nivel_educativo'))
        $queryTotal->whereHas('carrera', function ($q) use ($request) {
            $q->where('career_classification_id', $request->nivel_educativo);
        });

    // ← AGREGA ESTO
    if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
        $queryTotal->whereHas('seguimientos', function ($q) use ($request) {
            $q->whereIn('id', function ($sub) {
                $sub->selectRaw('MAX(id)')
                    ->from('lead_seguimientos')
                    ->groupBy('lead_id');
            });
            if ($request->filled('fecha_inicio'))
                $q->whereDate('fecha', '>=', $request->fecha_inicio);
            if ($request->filled('fecha_fin'))
                $q->whereDate('fecha', '<=', $request->fecha_fin);
        });
    }

    // ← AGREGA ESTO
    $leadsReales = $queryTotal->with([
        'seguimientos' => function ($q) {
            $q->orderBy('fecha', 'asc')->orderBy('hora', 'asc');
        }
    ])->get();

    $totalFrioReal = 0;
    $totalCalienteReal = 0;
    $totalAspiranteReal = 0;
    $totalAlumnoReal = 0;

    foreach ($leadsReales as $lead) {
        $ultimoEstado = $lead->seguimientos->last()?->estado ?? 'Prospecto frío';
        match ($ultimoEstado) {
            'Prospecto frío'     => $totalFrioReal++,
            'Prospecto caliente' => $totalCalienteReal++,
            'Aspirante'          => $totalAspiranteReal++,
            'Alumno'             => $totalAlumnoReal++,
            default              => null,
        };
    }

    $totalSinFiltroEstatus = $totalFrioReal + $totalCalienteReal + $totalAspiranteReal + $totalAlumnoReal;

    $ctps    = User::whereHas('roles', function ($q) { $q->where('name', 'ctp'); })->get();

        $carreras = $request->filled('nivel_educativo')
            ? \App\Models\Users\Career::where('career_classification_id', $request->nivel_educativo)->orderBy('name')->get()
            : \App\Models\Users\Career::orderBy('name')->get();

        $clasificaciones = \App\Models\Users\CareerClassification::orderBy('name')->get();
                
        
        return view('crm.estadisticas', compact(
            'leads', 'ctps', 'carreras', 'clasificaciones',
            'totalLeads', 'totalFrio', 'totalCaliente', 'totalAspirante', 'totalAlumno',
            'totalInteresados', 'totalConvertidos', 'porcentajeConversion',
            'frioPorMes', 'calientePorMes', 'aspirantePorMes', 'alumnoPorMes',
            'porcentajeFrio', 'porcentajeCaliente', 'porcentajeAspirante', 'porcentajeAlumno',
            'promedioFrio', 'promedioCaliente', 'promedioAspirante', 'promedioAlumno','totalSinFiltroEstatus', 
            'totalFrioReal', 'totalCalienteReal', 'totalAspiranteReal', 'totalAlumnoReal'
        ));
    }
    
    public function update(Request $request, Lead $lead)
    {
        $request->validate([
            'alumno_nombre' => 'required|string|max:255',
            'alumno_paterno' => 'required|string|max:255',
            'alumno_materno' => 'required|string|max:255',
            'alumno_curp' => 'nullable|string|max:18',
            'telefono1' => 'required|string|max:20',
            'carrera_id' => 'nullable|exists:careers,id',
            'semestre' => 'nullable|integer|min:1|max:12',
            'doc_acta_nacimiento' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_certificado_prepa' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_curp' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_ine' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = $request->only(['alumno_nombre', 'alumno_paterno', 'alumno_materno', 'alumno_curp', 'telefono1', 'carrera_id', 'semestre']);

        foreach (['doc_acta_nacimiento', 'doc_certificado_prepa', 'doc_curp', 'doc_ine'] as $campo) {
            if ($request->hasFile($campo)) {
                if ($lead->$campo) {
                    Storage::disk('public')->delete($lead->$campo);
                }
                $data[$campo] = $request->file($campo)->store("documentos/leads/{$lead->id}", 'public');
            }
        }

        $lead->update($data);
        return response()->json(['success' => true, 'message' => 'Aspirante actualizado correctamente.']);
    }

    public function destroy(Request $request, Lead $lead)
    {
        $lead->delete();
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Aspirante eliminado correctamente.');
    }

    public function guardarSeguimiento(Request $request, Lead $lead)
    {
        // El CRM no puede registrar "Alumno", eso le toca a Control Escolar
    if ($request->estado === 'Alumno') {
        return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
    }
        $lead->seguimientos()->create([
            'estado'     => $request->estado,
            'fecha'      => now()->toDateString(),
            'hora'       => now()->toTimeString(),
            'comentario' => $request->comentario ?? null,
        ]);

        return response()->json([
            'success'      => true,
            'seguimientos' => $lead->seguimientos()->orderBy('id')->get()
        ]);
    }
    
    public function prospectos(Request $request)
    {
        $query = Lead::query();


        $rol = session('active_role_name');
        $userId = auth()->id();

        // CTP solo ve SUS prospectos
        if ($rol === 'ctp') {
            $query->where('ctp_id', $userId);
        }

        // Buscador
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('rfc', 'like', "%$search%")
                ->orWhere('alumno_nombre', 'like', "%$search%")
                ->orWhere('alumno_paterno', 'like', "%$search%")
                ->orWhere('alumno_materno', 'like', "%$search%")
                ->orWhere('clasificacion', 'like', "%$search%");
            });
        }

        $leads = $query->with(['seguimientos', 'ctp', 'carrera'])->orderBy('created_at', 'desc')->get();
        // ── LOGO PARA PDF ──
    $logoPath = public_path('images/LogoUMI-Azul.png');
    $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
    $clasificaciones = \App\Models\Users\CareerClassification::orderBy('name')->get();
    return view('crm.prospectos', compact('leads', 'logoBase64', 'clasificaciones'));

    }


    public function asignarCTP(Request $request, Lead $lead)
    {
        $lead->ctp_id = $request->ctp_id;
        
        if ($request->filled('comentario')) {
            $lead->comentario_reasignacion = $request->comentario;
        }
        
        $lead->save();

        // 👇 Registrar "Prospecto frío" automáticamente si no existe
        $yaExiste = $lead->seguimientos()->where('estado', 'Prospecto frío')->exists();
        if (!$yaExiste) {
            $lead->seguimientos()->create([
                'estado' => 'Prospecto frío',
                'fecha'  => now()->toDateString(),
                'hora'   => now()->toTimeString(),
            ]);
        }

        return response()->json(['success' => true]);
    }
    
    public function comisiones()
    {
        $ctps = User::whereHas('roles', function ($q) {
            $q->where('name', 'ctp');
        })->get();
    
        $ctps->each(function ($ctp) {
    
            // AHORA: solo cuenta los que llegaron a "Alumno"
            $ctp->num_conversiones = Lead::where('ctp_id', $ctp->id)
                ->whereHas('seguimientos', function ($q) {
                    $q->where('estado', 'Alumno');
                })->count();

            $leads = Lead::where('ctp_id', $ctp->id)
                ->whereHas('seguimientos', function ($q) {
                    $q->where('estado', 'Alumno');
                })
                ->with('carrera')
                ->get();

                $ctp->total_comisiones = $leads->sum(function ($lead) {
                    $comision = Comision::where('producto', $lead->carrera?->name)->first();
                    return $comision?->total ?? 0;
            });

        });

        $comisiones = Comision::join('career_classifications', 'comisiones.clasificacion', '=', 'career_classifications.id')
        ->select('comisiones.*', 'career_classifications.name as clasificacion_nombre')
        ->get();

        $logoPath = public_path('images/logoUMI-Azul.png');
        $logoBase64 = base64_encode(file_get_contents($logoPath));
        
        return view('crm.comisiones', [
            'ctps'       => $ctps,
            'carreras'   => \App\Models\Users\Career::orderBy('name')->get()->map(function ($c) {
                $c->precio_sem1 = $c->pricing_mode === 'per_month'
                    ? (float) (($c->monthly_prices[1] ?? $c->monthly_prices['1']) ?? 0)
                    : (float) ($c->monto_mensualidad ?? 0);
                return $c;
            }),
            'clasificaciones' => \App\Models\Users\CareerClassification::orderBy('name')->get(),
            'comisiones' => $comisiones,
            'logoBase64' => $logoBase64,
        ]);
    }

    public function storeComision(Request $request)
    {
        $comision = Comision::create([
            'clasificacion' => $request->clasificacion,
            'producto'      => $request->producto,
            'precio'        => $request->precio,
            'porcentaje'    => $request->porcentaje,
            'total'         => ($request->precio * $request->porcentaje) / 100,
        ]);

        $clasificacionNombre = \App\Models\Users\CareerClassification::find($comision->clasificacion)?->name;

        return response()->json([
            'id' => $comision->id,
            'clasificacion' => $clasificacionNombre,
            'producto' => $comision->producto,
            'precio' => $comision->precio,
            'porcentaje' => $comision->porcentaje,
            'total' => $comision->total,
        ]);
    }

    public function updateComision(Request $request, $id)
    {
        $comision = Comision::findOrFail($id);
        $comision->update([
            'clasificacion' => $request->clasificacion,
            'producto'      => $request->producto,
            'precio'        => $request->precio,
            'porcentaje'    => $request->porcentaje,
            'total'         => ($request->precio * $request->porcentaje) / 100,
        ]);

        $clasificacionNombre = \App\Models\Users\CareerClassification::find($comision->clasificacion)?->name;

        return response()->json([
            'id' => $comision->id,
            'clasificacion' => $clasificacionNombre,
            'producto' => $comision->producto,
            'precio' => $comision->precio,
            'porcentaje' => $comision->porcentaje,
            'total' => $comision->total,
        ]);
    }

    public function destroyComision($id)
    {
        Comision::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function detalleComision($ctpId)
    {
        $leads = Lead::where('ctp_id', $ctpId)
            ->whereHas('seguimientos', function ($q) {
                $q->where('estado', 'Alumno');
            })
            ->with('carrera.classification') 
            ->get();

        $resultado = $leads->map(function ($lead) {
            $comision = Comision::where('producto', $lead->carrera?->name)->first();

            return [
                'clasificacion' => $lead->carrera?->classification?->name ?? 'Sin clasificación',
                'producto'      => $lead->carrera?->name ?? 'Sin producto', 
                'alumno'        => $lead->alumno_nombre . ' ' . $lead->alumno_paterno,
                'comision'      => $comision?->total ?? 0,
            ];
        })->sortBy('clasificacion')->values();

        return response()->json([
            'data'  => $resultado,
            'total' => $resultado->sum('comision'),
        ]);
    }

    public function filtrarComisiones(Request $request)
    {
        $inicio = $request->input('fecha_inicio');
        $fin    = $request->input('fecha_fin');
        $buscar = strtolower($request->input('ctp', ''));

        $ctps = User::whereHas('roles', function ($q) {
            $q->where('name', 'ctp');
        })->get();

        $ctps = $ctps->filter(function ($ctp) use ($buscar) {
            if (!$buscar) return true;
            $nombre = strtolower($ctp->nombre . ' ' . $ctp->apellido_paterno);
            return str_contains($nombre, $buscar);
        });

        $ctps->each(function ($ctp) use ($inicio, $fin) {
            $query = Lead::where('ctp_id', $ctp->id)
                ->whereHas('seguimientos', function ($q) use ($inicio, $fin) {
                    $q->where('estado', 'Alumno');
                    if ($inicio) $q->whereDate('fecha', '>=', $inicio);
                    if ($fin)    $q->whereDate('fecha', '<=', $fin);
                });

            $ctp->num_conversiones = $query->count();

            $leads = $query->with('carrera')->get();

            $ctp->total_comisiones = $leads->sum(function ($lead) {
                $comision = Comision::where('producto', $lead->carrera?->name)->first();
                return $comision?->total ?? 0;
            });
        });

        return response()->json(
            $ctps->values()->map(fn($ctp) => [
                'id'               => $ctp->id,
                'nombre'           => $ctp->nombre . ' ' . $ctp->apellido_paterno,
                'num_conversiones' => $ctp->num_conversiones,
                'total_comisiones' => $ctp->total_comisiones,
            ])
        );
    }
}