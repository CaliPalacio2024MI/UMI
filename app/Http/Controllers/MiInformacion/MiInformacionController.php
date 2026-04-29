<?php

namespace App\Http\Controllers\MiInformacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MiInformacionController extends Controller
{
    /**
     * Muestra el Perfil del usuario.
     * Accesible para TODOS (Master, Admin, Docente, Alumno).
     */
    public function index()
    {
        // 1. Obtenemos al usuario logueado
        $user = Auth::user();
        
        // 2. Cargamos sus relaciones (Perfiles y Dirección si existe)
        // (Asegúrate de tener estas relaciones en tu modelo User si las vas a usar)
        $user->load(['academicProfile', 'corporateProfile', 'address']);

        // 3. Enviamos la variable $user a la vista
        return view('layouts.MiInformacion.index', compact('user'));
    }

    /**
     * Muestra las Clases del alumno (horarios en los que está inscrito).
     */
    public function showClases()
    {
        $user = Auth::user();
        $user->load('academicProfile');

        // 1) Clases explícitamente asignadas al alumno (pivot horario_clase_user)
        $clases = $user->horarioClases()
            ->with(['materia', 'carrera', 'user', 'aula', 'franjas'])
            ->get();

        // 2) Respaldo: si aún no hay asignación en pivot, mostrar las clases de su carrera/semestre
        // para que el alumno sí vea sus clases del periodo.
        if ($clases->isEmpty() && $user->academicProfile?->career_id) {
            $careerId = (int) $user->academicProfile->career_id;
            $semestre = $user->academicProfile->semestre;

            $clases = \App\Models\AdmonCont\HorarioClase::query()
                ->with(['materia', 'carrera', 'user', 'aula', 'franjas'])
                ->where('career_id', $careerId)
                ->when($semestre !== null && $semestre !== '', function ($q) use ($semestre) {
                    $q->whereHas('materia', fn ($mq) => $mq->where('semestre', $semestre));
                })
                ->orderBy('materia_id')
                ->get();
        }

        return view('layouts.MiInformacion.clases', compact('user', 'clases'));
    }

    /**
     * Muestra el Horario.
     */
    public function showHorario()
    {
        $user = Auth::user();
        $user->load('academicProfile');

        // 1) Horarios explícitamente asignados al alumno
        $clases = $user->horarioClases()
            ->with(['materia', 'carrera', 'user', 'aula', 'franjas'])
            ->get();

        // 2) Respaldo por carrera/semestre cuando aún no hay pivot cargado
        if ($clases->isEmpty() && $user->academicProfile?->career_id) {
            $careerId = (int) $user->academicProfile->career_id;
            $semestre = $user->academicProfile->semestre;

            $clases = \App\Models\AdmonCont\HorarioClase::query()
                ->with(['materia', 'carrera', 'user', 'aula', 'franjas'])
                ->where('career_id', $careerId)
                ->when($semestre !== null && $semestre !== '', function ($q) use ($semestre) {
                    $q->whereHas('materia', fn ($mq) => $mq->where('semestre', $semestre));
                })
                ->orderBy('materia_id')
                ->get();
        }

        return view('layouts.MiInformacion.horario', compact('user', 'clases'));
    }

    /**
     * Muestra el Historial Académico.
     */
    public function showHistorial()
    {
        $user = Auth::user();
        
        // SIMULACIÓN DE DATOS (Historial)
        // Cuando tengas la base de datos llena, esto vendrá de: $user->historial
        $semestres = [
            (object)[
                'numero' => 1,
                'periodo' => 'AGO 2024 - ENE 2025',
                'promedio' => 9.6,
                'materias' => [
                    (object)['nombre' => 'Inteligencia de Negocios', 'creditos' => 5, 'calificacion' => 100, 'evaluacion' => 'OR', 'observaciones' => '-'],
                    (object)['nombre' => 'Ética Profesional', 'creditos' => 4, 'calificacion' => 95, 'evaluacion' => 'ORD', 'observaciones' => '-'],
                ]
            ],
            (object)[
                'numero' => 2,
                'periodo' => 'FEB 2025 - JUL 2025',
                'promedio' => 9.2,
                'materias' => [
                    (object)['nombre' => 'Programación Web', 'creditos' => 5, 'calificacion' => 92, 'evaluacion' => 'ORD', 'observaciones' => '-'],
                    (object)['nombre' => 'Redes de Computadoras', 'creditos' => 5, 'calificacion' => 90, 'evaluacion' => 'ORD', 'observaciones' => '-'],
                ]
            ]
        ];

        // Enviamos $user y $semestres a la vista
        return view('layouts.MiInformacion.historial', compact('user', 'semestres'));
    }
}