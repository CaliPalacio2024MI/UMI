<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Users\User;
use Carbon\Carbon;
use App\Models\Carrera;
use App\Exports\EstadisticasExport;
use Maatwebsite\Excel\Facades\Excel;


class CRMController extends Controller
{
    public function leads()
    {
        $query = Lead::with(['seguimientos', 'ctp']);

        $rol = session('active_role_name');
        $userId = auth()->id();

        // 👉 SI ES CTP: solo sus leads asignados
        if ($rol === 'ctp') {
            $query->where('ctp_id', $userId);
        }

        // 👉 Master y Coordinador ven todo
        $leads = $query->orderBy('created_at', 'desc')->get();

        // Solo master y coordinador necesitan la lista de CTPs
        $ctps = [];
        if (in_array($rol, ['master', 'coordinador_ctp'])) {
            $ctps = \App\Models\Users\User::whereHas('roles', function ($q) {
                $q->where('name', 'ctp');
            })->get();
        }

        return view('crm.leads', compact('leads', 'ctps'));
    }

    public function exportar(Request $request)
    {
        $query = Lead::with([
            'seguimientos' => function ($q) {
                $q->latest();
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

        if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
            $query->whereHas('seguimientos', function ($q) use ($request) {

                if ($request->filled('fecha_inicio')) {
                    $q->whereDate('fecha', '>=', $request->fecha_inicio);
                }

                if ($request->filled('fecha_fin')) {
                    $q->whereDate('fecha', '<=', $request->fecha_fin);
                }
            });
        }

        if ($request->filled('estatus')) {
            $query->whereHas('seguimientos', function ($q) use ($request) {
                $q->where('estado', $request->estatus);
            });
        }


        $leads = $query->get();

        $totalLeads = $leads->count();

        // ================= ESTADOS =================
        $estados = $leads->map(function ($lead) {
            $ultimoSeguimiento = $lead->seguimientos->first();

            return $ultimoSeguimiento
                ? $ultimoSeguimiento->estado
                : 'Prospecto frío';
        });

        $conteos = $estados->countBy();

        $totalFrio = $conteos['Prospecto frío'] ?? 0;
        $totalCaliente = $conteos['Prospecto caliente'] ?? 0;
        $totalAspirante = $conteos['Aspirante'] ?? 0;
        $totalAlumno = $conteos['Alumno'] ?? 0;

        // ================= PORCENTAJES =================
        $totalEstados = $totalFrio + $totalCaliente + $totalAspirante + $totalAlumno;

        $porcentajeFrio = $totalEstados > 0 ? round(($totalFrio / $totalEstados) * 100, 1) : 0;
        $porcentajeCaliente = $totalEstados > 0 ? round(($totalCaliente / $totalEstados) * 100, 1) : 0;
        $porcentajeAspirante = $totalEstados > 0 ? round(($totalAspirante / $totalEstados) * 100, 1) : 0;
        $porcentajeAlumno = $totalEstados > 0 ? round(($totalAlumno / $totalEstados) * 100, 1) : 0;

        // ================= TIEMPOS =================
        $tiempos = [
            'Prospecto frío' => [],
            'Prospecto caliente' => [],
            'Aspirante' => [],
            'Alumno' => [],
        ];

        foreach ($leads as $lead) {

            $fechaInicio = $lead->created_at;
            $ultimoSeguimiento = $lead->seguimientos->first();

            if (!$ultimoSeguimiento) continue;

            $estado = $ultimoSeguimiento->estado;
            $fechaFin = Carbon::parse($ultimoSeguimiento->fecha);
            $dias = $fechaInicio->diffInHours($fechaFin) / 24;

            if (isset($tiempos[$estado])) {
                $tiempos[$estado][] = $dias;
            }
        }

        $promedioFrio = count($tiempos['Prospecto frío']) > 0 ? round(array_sum($tiempos['Prospecto frío']) / count($tiempos['Prospecto frío']), 2) : 0;
        $promedioCaliente = count($tiempos['Prospecto caliente']) > 0 ? round(array_sum($tiempos['Prospecto caliente']) / count($tiempos['Prospecto caliente']), 2) : 0;
        $promedioAspirante = count($tiempos['Aspirante']) > 0 ? round(array_sum($tiempos['Aspirante']) / count($tiempos['Aspirante']), 2) : 0;
        $promedioAlumno = count($tiempos['Alumno']) > 0 ? round(array_sum($tiempos['Alumno']) / count($tiempos['Alumno']), 2) : 0;

        // ================= TIEMPO PROMEDIO GENERAL =================
        $totalSegundos = 0;

        foreach ($leads as $lead) {

            $fechaInicio = $lead->created_at;
            $ultimoSeguimiento = $lead->seguimientos->first();

            if ($ultimoSeguimiento) {
                $fechaFin = Carbon::parse($ultimoSeguimiento->fecha . ' ' . $ultimoSeguimiento->hora);
            } else {
                $fechaFin = now();
            }

            $totalSegundos += $fechaInicio->diffInSeconds($fechaFin);
        }


        $tiempoPromedio = $totalLeads > 0
            ? round(($totalSegundos / $totalLeads) / 86400, 2)
            : 0;



        // ================= EXCEL =================
        $data = [
            ['RESUMEN'],
            ['Total Leads', $totalLeads],
            ['Total Alumnos', $totalAlumno],
            ['Tiempo Promedio', $tiempoPromedio],

            [],
            [],

            ['DISTRIBUCIÓN DE PROSPECTOS'],
            ['Estado', 'Total'],
            ['Prospecto Frío', $totalFrio],
            ['Prospecto Caliente', $totalCaliente],
            ['Aspirante', $totalAspirante],
            ['Alumno', $totalAlumno],

            [],
            [],

            ['TASA DE CONVERSIÓN'],
            ['Estado', 'Conversión', 'Tiempo Promedio'],
            ['Prospecto Frío', $porcentajeFrio.'%', $promedioFrio.' días'],
            ['Prospecto Caliente', $porcentajeCaliente.'%', $promedioCaliente.' días'],
            ['Aspirante', $porcentajeAspirante.'%', $promedioAspirante.' días'],
            ['Alumno', $porcentajeAlumno.'%', $promedioAlumno.' días'],
        ];

        return Excel::download(new EstadisticasExport($data), 'estadisticas.xlsx');
    }

   

    public function estadisticas(Request $request)
    {
        $query = Lead::with([
            'seguimientos' => function ($q) {
                $q->latest();
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


        if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
            $query->whereHas('seguimientos', function ($q) use ($request) {

                if ($request->filled('fecha_inicio')) {
                    $q->whereDate('fecha', '>=', $request->fecha_inicio);
                }

                if ($request->filled('fecha_fin')) {
                    $q->whereDate('fecha', '<=', $request->fecha_fin);
                }
            });
        }

        if ($request->filled('estatus')) {
            $query->whereHas('seguimientos', function ($q) use ($request) {
                $q->where('estado', $request->estatus);
            });
        }


        $leads = $query->get();

        $totalLeads = $leads->count();

        /* ===========================
        ESTADO ACTUAL
        =========================== */

        $estados = $leads->map(function ($lead) {

            $ultimoSeguimiento = $lead->seguimientos->first();

            return $ultimoSeguimiento
                ? $ultimoSeguimiento->estado
                : 'Prospecto frío';
        });

        $conteos = $estados->countBy();

        $totalFrio = $conteos['Prospecto frío'] ?? 0;
        $totalCaliente = $conteos['Prospecto caliente'] ?? 0;
        $totalAspirante = $conteos['Aspirante'] ?? 0;
        $totalAlumno = $conteos['Alumno'] ?? 0;

        /* ===========================
        PORCENTAJES
        =========================== */

       $totalEstados = $totalFrio + $totalCaliente + $totalAspirante + $totalAlumno;

        $porcentajeFrio = $totalEstados > 0 ? round(($totalFrio / $totalEstados) * 100, 1) : 0;
        $porcentajeCaliente = $totalEstados > 0 ? round(($totalCaliente / $totalEstados) * 100, 1) : 0;
        $porcentajeAspirante = $totalEstados > 0 ? round(($totalAspirante / $totalEstados) * 100, 1) : 0;
        $porcentajeAlumno = $totalEstados > 0 ? round(($totalAlumno / $totalEstados) * 100, 1) : 0;

        /* ===========================
        TIEMPO PROMEDIO POR ESTADO
        =========================== */

        $tiempos = [
            'Prospecto frío' => [],
            'Prospecto caliente' => [],
            'Aspirante' => [],
            'Alumno' => [],
        ];

        foreach ($leads as $lead) {

            $fechaInicio = $lead->created_at;

            $ultimoSeguimiento = $lead->seguimientos->first();

            if (!$ultimoSeguimiento) {
                continue;
            }

            $estado = $ultimoSeguimiento->estado;

            $fechaFin = Carbon::parse($ultimoSeguimiento->fecha);

            $dias = $fechaInicio->diffInHours($fechaFin) / 24;

            if (isset($tiempos[$estado])) {
                $tiempos[$estado][] = $dias;
            }
        }

        $promedioFrio = count($tiempos['Prospecto frío']) > 0
            ? round(array_sum($tiempos['Prospecto frío']) / count($tiempos['Prospecto frío']), 2)
            : 0;

        $promedioCaliente = count($tiempos['Prospecto caliente']) > 0
            ? round(array_sum($tiempos['Prospecto caliente']) / count($tiempos['Prospecto caliente']), 2)
            : 0;

        $promedioAspirante = count($tiempos['Aspirante']) > 0
            ? round(array_sum($tiempos['Aspirante']) / count($tiempos['Aspirante']), 2)
            : 0;

        $promedioAlumno = count($tiempos['Alumno']) > 0
            ? round(array_sum($tiempos['Alumno']) / count($tiempos['Alumno']), 2)
            : 0;

        /* ===========================
        CONVERSIÓN
        =========================== */

        $totalInteresados = $totalFrio + $totalCaliente;
        $totalConvertidos = $totalAspirante + $totalAlumno;

        $porcentajeConversion = $totalInteresados > 0
            ? round(($totalConvertidos / $totalInteresados) * 100, 1)
            : 0;

        /* ===========================
        TIEMPO PROMEDIO GENERAL
        =========================== */

        $totalSegundos = 0;

        foreach ($leads as $lead) {

            $fechaInicio = $lead->created_at;

            $ultimoSeguimiento = $lead->seguimientos->first();

            if ($ultimoSeguimiento) {

                $fechaFin = Carbon::parse(
                    $ultimoSeguimiento->fecha . ' ' . $ultimoSeguimiento->hora
                );

            } else {

                $fechaFin = now();
            }

            $totalSegundos += $fechaInicio->diffInSeconds($fechaFin);
        }

        $tiempoPromedio = $totalLeads > 0
            ? round(($totalSegundos / $totalLeads) / 86400, 2)
            : 0;

        /* ===========================
        DATOS POR MES
        =========================== */

        $porMes = [
            'Prospecto frío' => array_fill(1, 12, 0),
            'Prospecto caliente' => array_fill(1, 12, 0),
            'Aspirante' => array_fill(1, 12, 0),
            'Alumno' => array_fill(1, 12, 0),
        ];
        
        foreach ($leads as $lead) {

            // Filtrar seguimientos por fecha seleccionada
            $seguimientosFiltrados = $lead->seguimientos->filter(function ($seg) use ($request) {

                $fecha = \Carbon\Carbon::parse($seg->fecha);

                if ($request->filled('fecha_inicio') && $fecha->lt($request->fecha_inicio)) {
                    return false;
                }

                if ($request->filled('fecha_fin') && $fecha->gt($request->fecha_fin)) {
                    return false;
                }

                return true;
            });

            // Tomar el último dentro del rango
            $ultimoSeguimiento = $seguimientosFiltrados->sortByDesc('fecha')->first();

            if (!$ultimoSeguimiento) {
                continue;
            }

            $estado = $ultimoSeguimiento->estado;
            $mes = \Carbon\Carbon::parse($ultimoSeguimiento->fecha)->month;

            if (isset($porMes[$estado])) {
                $porMes[$estado][$mes]++;
            }
        }

        $frioPorMes = $porMes['Prospecto frío'];
        $calientePorMes = $porMes['Prospecto caliente'];
        $aspirantePorMes = $porMes['Aspirante'];
        $alumnoPorMes = $porMes['Alumno'];

        $ctps = User::whereHas('roles', function ($q) {
            $q->where('name', 'ctp');
        })->get();

        $carreras = Carrera::orderBy('nombre')->get();

        return view('crm.estadisticas', compact(
            'leads',
            'ctps',
            'carreras',
            'totalLeads',
            'totalFrio',
            'totalCaliente',
            'totalAspirante',
            'totalAlumno',
            'totalInteresados',
            'totalConvertidos',
            'porcentajeConversion',
            'tiempoPromedio',
            'frioPorMes',
            'calientePorMes',
            'aspirantePorMes',
            'alumnoPorMes',
            'porcentajeFrio',
            'porcentajeCaliente',
            'porcentajeAspirante',
            'porcentajeAlumno',
            'promedioFrio',
            'promedioCaliente',
            'promedioAspirante',
            'promedioAlumno'
        ));
    }

    

    public function destroy(Lead $lead)
    {
        $lead->delete();
        return response()->json(['success' => true]);
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

        return view('crm.prospectos', compact('leads'));
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
        });
    
        return view('crm.comisiones', [
            'ctps'     => $ctps,
            'carreras' => \App\Models\Carrera::all(),
        ]);
    }
}
