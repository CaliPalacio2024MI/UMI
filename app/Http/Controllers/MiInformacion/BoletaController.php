<?php

namespace App\Http\Controllers\MiInformacion;

use App\Http\Controllers\Controller;
use App\Models\AdmonCont\HorarioClase;
use App\Models\AdmonCont\Calificacion;
use App\Models\Users\Career;
use App\Models\Users\Period;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoletaController extends Controller
{
    /**
     * Clases (materias) en las que el alumno está inscrito (pivot) o, en su
     * defecto, las de su carrera/semestre. Devuelve modelos HorarioClase.
     */
    private function clasesDelAlumno(User $user)
    {
        $clases = $user->horarioClases()->with(['materia', 'user', 'carrera'])->get();

        if ($clases->isEmpty() && $user->academicProfile?->career_id) {
            $careerId = (int) $user->academicProfile->career_id;
            $semestre = $user->academicProfile->semestre;

            $clases = HorarioClase::query()
                ->with(['materia', 'user', 'carrera'])
                ->where('career_id', $careerId)
                ->when($semestre !== null && $semestre !== '', function ($q) use ($semestre) {
                    $q->whereHas('materia', fn ($mq) => $mq->where('semestre', $semestre));
                })
                ->get();
        }

        return $clases;
    }

    /**
     * Alumnos que deben aparecer en la boleta de una clase (inscritos o, en su
     * defecto, por carrera/semestre de la materia).
     */
    private function alumnosDeClase(HorarioClase $clase)
    {
        $alumnos = $clase->alumnos()->get();

        if ($alumnos->isEmpty() && $clase->career_id) {
            $semestre = $clase->materia?->semestre;

            $alumnos = User::whereHas('roles', fn ($q) => $q->where('name', 'estudiante'))
                ->whereHas('academicProfile', function ($q) use ($clase, $semestre) {
                    $q->where('career_id', $clase->career_id);
                    if ($semestre !== null && $semestre !== '') {
                        $q->where('semestre', $semestre);
                    }
                })
                ->orderBy('apellido_paterno')->orderBy('apellido_materno')->orderBy('nombre')
                ->get();
        }

        return $alumnos;
    }

    private function periodoActivoId(): ?int
    {
        $institutionId = (int) session('active_institution_id', 0);

        return (int) (Period::query()
            ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
            ->where('is_active', 1)
            ->value('id')) ?: null;
    }

    /**
     * Boletas: docente ve sus materias para capturar; alumno ve sus
     * calificaciones del periodo (con filtro de periodos anteriores).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $user->load('academicProfile');

        if ($user->hasActiveRole('docente')) {
            $misClases = HorarioClase::query()
                ->with(['materia', 'carrera'])
                ->where('user_id', $user->id)
                ->get();

            return view('layouts.MiInformacion.boletas_docente', compact('user', 'misClases'));
        }

        // --- ALUMNO ---
        $institutionId = (int) session('active_institution_id', 0);
        $periodos = Period::query()
            ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
            ->orderByDesc('start_date')
            ->get(['id', 'name', 'start_date', 'end_date']);

        $periodoActivoId = $this->periodoActivoId();
        $periodoId = $request->filled('periodo_id') ? (int) $request->get('periodo_id') : $periodoActivoId;
        $esPeriodoVigente = $periodoId !== null && $periodoId === $periodoActivoId;

        $clases = $this->clasesDelAlumno($user);

        // Clases del periodo seleccionado. Para periodos anteriores nos basamos
        // en las calificaciones registradas (solo consulta).
        if ($periodoId !== null) {
            $clasesDelPeriodo = $clases->filter(fn ($c) => (int) ($c->period_id ?? 0) === $periodoId)->values();

            if ($clasesDelPeriodo->isEmpty()) {
                $idsConCalif = Calificacion::query()
                    ->where('user_id', $user->id)
                    ->where('period_id', $periodoId)
                    ->pluck('horario_clase_id')->unique()->all();
                if ($idsConCalif !== []) {
                    $clasesDelPeriodo = HorarioClase::with(['materia', 'user', 'carrera'])->whereIn('id', $idsConCalif)->get();
                } elseif ($esPeriodoVigente) {
                    // El periodo vigente muestra las clases actuales aunque aún no tengan period_id.
                    $clasesDelPeriodo = $clases;
                }
            }
        } else {
            $clasesDelPeriodo = $clases;
        }

        // Solo las calificaciones del periodo seleccionado (estricto por period_id
        // cuando hay un periodo elegido; así el vigente no mezcla notas anteriores).
        $calificaciones = Calificacion::query()
            ->where('user_id', $user->id)
            ->whereIn('horario_clase_id', $clasesDelPeriodo->pluck('id'))
            ->when($periodoId !== null, fn ($q) => $q->where('period_id', $periodoId))
            ->get()
            ->groupBy('horario_clase_id');

        return view('layouts.MiInformacion.boletas_alumno', [
            'user' => $user,
            'clases' => $clasesDelPeriodo,
            'calificaciones' => $calificaciones,
            'periodos' => $periodos,
            'periodoId' => $periodoId,
            'esPeriodoVigente' => $esPeriodoVigente,
        ]);
    }

    /**
     * Vista de captura del docente para una materia: alumnos x parciales.
     */
    public function materia(HorarioClase $clase)
    {
        $user = Auth::user();
        abort_unless($user->hasActiveRole('docente') && $clase->user_id === $user->id, 403);

        $clase->load('materia');
        $numParciales = max(1, (int) ($clase->materia->num_parciales ?? 3));
        $alumnos = $this->alumnosDeClase($clase);

        $calificaciones = Calificacion::where('horario_clase_id', $clase->id)
            ->get()
            ->groupBy('user_id');

        return view('layouts.MiInformacion.boletas_captura', compact('user', 'clase', 'alumnos', 'numParciales', 'calificaciones'));
    }

    /**
     * El docente guarda las calificaciones de un alumno (parciales) mientras no
     * estén confirmadas.
     */
    public function guardar(Request $request, HorarioClase $clase, User $alumno)
    {
        $user = Auth::user();
        $clase->load('materia');
        abort_unless($user->hasActiveRole('docente') && $clase->user_id === $user->id, 403);

        $numParciales = max(1, (int) ($clase->materia->num_parciales ?? 3));

        $data = $request->validate([
            'parciales' => 'required|array',
            'parciales.*' => 'nullable|integer|min:0|max:100',
        ]);

        $periodId = $clase->period_id ?: $this->periodoActivoId();

        for ($p = 1; $p <= $numParciales; $p++) {
            $valor = $data['parciales'][$p] ?? null;

            $existente = Calificacion::where('horario_clase_id', $clase->id)
                ->where('user_id', $alumno->id)
                ->where('parcial', $p)
                ->first();

            // No se edita lo ya confirmado.
            if ($existente && $existente->confirmada) {
                continue;
            }

            Calificacion::updateOrCreate(
                ['horario_clase_id' => $clase->id, 'user_id' => $alumno->id, 'parcial' => $p],
                ['calificacion' => $valor, 'period_id' => $periodId]
            );
        }

        return back()->with('success', 'Calificaciones guardadas.');
    }

    /**
     * El docente confirma (bloquea) las calificaciones de un alumno. Tras
     * confirmar ya no se pueden editar (queda en firme para reclamos).
     */
    public function confirmar(HorarioClase $clase, User $alumno)
    {
        $user = Auth::user();
        abort_unless($user->hasActiveRole('docente') && $clase->user_id === $user->id, 403);

        Calificacion::where('horario_clase_id', $clase->id)
            ->where('user_id', $alumno->id)
            ->update(['confirmada' => true]);

        return back()->with('success', 'Calificación confirmada.');
    }

    /**
     * El docente reabre (desbloquea) las calificaciones ya confirmadas de un
     * alumno, por si se equivocó al confirmar o ante un reclamo, para poder
     * editarlas de nuevo.
     */
    public function reabrir(HorarioClase $clase, User $alumno)
    {
        $user = Auth::user();
        abort_unless($user->hasActiveRole('docente') && $clase->user_id === $user->id, 403);

        Calificacion::where('horario_clase_id', $clase->id)
            ->where('user_id', $alumno->id)
            ->update(['confirmada' => false]);

        return back()->with('success', 'Calificación reabierta para edición.');
    }

    /**
     * Retícula de la carrera que cursa el alumno (solo consulta).
     */
    public function reticula()
    {
        $user = Auth::user();
        $user->load('academicProfile.career');

        $carrera = $user->academicProfile?->career;
        abort_unless($carrera !== null, 404, 'No tienes una carrera asignada.');

        $materias = $carrera->materias()
            ->orderBy('semestre')->orderBy('created_at')->orderBy('id')
            ->get();

        $totalSemesters = max(1, (int) ($carrera->semesters ?? 1));

        $grouped = $materias->groupBy(fn ($m) => max(1, min($totalSemesters, (int) ($m->semestre ?? 1))));
        $porSemestre = [];
        for ($s = 1; $s <= $totalSemesters; $s++) {
            $porSemestre[$s] = $grouped->get($s, collect())->values();
        }

        $semestreLabels = [
            1 => '1er Semestre', 2 => '2do Semestre', 3 => '3er Semestre', 4 => '4to Semestre',
            5 => '5to Semestre', 6 => '6to Semestre', 7 => '7mo Semestre',
        ];

        $totalMaterias = $materias->count();
        $totalCreditos = (int) $materias->sum(fn ($m) => (int) ($m->creditos ?? 0));

        return view('layouts.MiInformacion.reticula', compact(
            'user', 'carrera', 'porSemestre', 'totalSemesters', 'semestreLabels', 'totalMaterias', 'totalCreditos'
        ));
    }
}
