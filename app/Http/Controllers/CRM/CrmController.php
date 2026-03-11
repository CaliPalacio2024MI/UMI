<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Users\User;

class CRMController extends Controller
{
    public function leads()
    {
        $query = Lead::with('seguimientos');

        $rol = session('active_role_name');
        $userId = auth()->id();

        if ($rol === 'ctp') {
            $query->where('ctp_id', $userId);
        }

        $leads = $query->orderBy('created_at', 'desc')->get();

        $ctps = [];
        if (in_array($rol, ['master', 'coordinador_ctp'])) {
            $ctps = User::whereHas('roles', function ($q) {
                $q->where('name', 'ctp');
            })->get();
        }

        return view('crm.leads', compact('leads', 'ctps'));
    }

    /* ===========================
    ESTADÍSTICAS
    =========================== */
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

        /* ===========================
        FILTRO POR ROL
        =========================== */

        if ($rol === 'ctp') {
            $query->where('ctp_id', $userId);
        }

        /* ===========================
        FILTRO POR CTP
        =========================== */

        if (in_array($rol, ['master', 'coordinador_ctp']) && $request->filled('buscar')) {
            $query->whereHas('ctp', function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->buscar . '%');
            });
        }

        /* ===========================
        FILTRO POR FECHAS
        =========================== */

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

        $leads = $query->get();

        /* ===========================
        TOTAL LEADS
        =========================== */

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

        /* ===========================
        CONTEOS GENERALES
        =========================== */

        $conteos = $estados->countBy();

        $totalFrio = $conteos['Prospecto frío'] ?? 0;
        $totalCaliente = $conteos['Prospecto caliente'] ?? 0;
        $totalAspirante = $conteos['Aspirante'] ?? 0;
        $totalAlumno = $conteos['Alumno'] ?? 0;

        /* ===========================
        PORCENTAJE POR ESTADO
        =========================== */

        $porcentajeFrio = $totalLeads > 0 ? round(($totalFrio / $totalLeads) * 100, 1) : 0;
        $porcentajeCaliente = $totalLeads > 0 ? round(($totalCaliente / $totalLeads) * 100, 1) : 0;
        $porcentajeAspirante = $totalLeads > 0 ? round(($totalAspirante / $totalLeads) * 100, 1) : 0;
        $porcentajeAlumno = $totalLeads > 0 ? round(($totalAlumno / $totalLeads) * 100, 1) : 0;

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

            $fechaInicio = \Carbon\Carbon::parse($lead->created_at);

            $ultimoSeguimiento = $lead->seguimientos->first();

            if (!$ultimoSeguimiento) {
                continue;
            }

            $estado = $ultimoSeguimiento->estado;

            $fechaFin = \Carbon\Carbon::parse($ultimoSeguimiento->fecha);

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

            $fechaInicio = \Carbon\Carbon::parse($lead->created_at);

            $ultimoSeguimiento = $lead->seguimientos->first();

            if ($ultimoSeguimiento) {

                $fechaFin = \Carbon\Carbon::parse(
                    $ultimoSeguimiento->fecha . ' ' . $ultimoSeguimiento->hora
                );

            } else {

                $fechaFin = now();
            }

            $segundos = $fechaInicio->diffInSeconds($fechaFin);

            $totalSegundos += $segundos;
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

            $ultimoSeguimiento = $lead->seguimientos->first();

            if (!$ultimoSeguimiento) {
                continue;
            }

            $estado = $ultimoSeguimiento->estado;

            $mes = date('n', strtotime($ultimoSeguimiento->fecha));

            if (isset($porMes[$estado])) {
                $porMes[$estado][$mes]++;
            }
        }

        $frioPorMes = $porMes['Prospecto frío'];
        $calientePorMes = $porMes['Prospecto caliente'];
        $aspirantePorMes = $porMes['Aspirante'];
        $alumnoPorMes = $porMes['Alumno'];

        /* ===========================
        LISTA CTPS
        =========================== */

        $ctps = User::whereHas('roles', function ($q) {
            $q->where('name', 'ctp');
        })->get();

        return view('crm.estadisticas', compact(
            'leads',
            'ctps',
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
        $request->validate([
            'estado' => 'required|in:Prospecto frío,Prospecto caliente,Aspirante,Alumno'
        ]);

        $lead->seguimientos()->create([
            'estado' => $request->estado,
            'fecha' => now()->toDateString(),
            'hora' => now()->toTimeString(),
        ]);

        return response()->json([
            'success' => true,
            'seguimientos' => $lead->seguimientos()->orderBy('id')->get()
        ]);
    }

    public function prospectos(Request $request)
    {
        $query = Lead::query();

        $rol = session('active_role_name');
        $userId = auth()->id();

        if ($rol === 'ctp') {
            $query->where('ctp_id', $userId);
        }

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

        $leads = $query->orderBy('created_at', 'desc')->get();

        return view('crm.prospectos', compact('leads'));
    }

    public function asignarCTP(Request $request, Lead $lead)
    {
        $lead->ctp_id = $request->ctp_id;

        if ($request->filled('comentario')) {
            $lead->comentario_reasignacion = $request->comentario;
        }

        $lead->save();

        $yaExiste = $lead->seguimientos()
            ->where('estado', 'Prospecto frío')
            ->exists();

        if (!$yaExiste) {
            $lead->seguimientos()->create([
                'estado' => 'Prospecto frío',
                'fecha'  => now()->toDateString(),
                'hora'   => now()->toTimeString(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}