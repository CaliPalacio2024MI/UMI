<?php

namespace App\Http\Controllers\MiInformacion;

use App\Http\Controllers\Controller;
use App\Models\AdmonCont\HorarioClase;
use App\Models\AdmonCont\Tarea;
use App\Models\AdmonCont\TareaEntrega;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Lógica compartida entre "Tareas" y "Evaluaciones": ambas son asignaciones
 * (tabla `tareas`, columna `tipo`) que el docente da de alta para una clase
 * y el alumno entrega/consulta. Las subclases solo definen tipo, rutas y vistas.
 */
abstract class AsignacionController extends Controller
{
    /** @var string 'tarea' | 'evaluacion' */
    protected string $tipo = 'tarea';

    /** Prefijo de nombres de ruta para la vista global (sidebar), p.ej. 'MiInformacion.tareas' */
    protected string $routePrefix = 'MiInformacion.tareas';

    /** Prefijo de nombres de ruta para la vista por materia (portafolio/checklist del docente) */
    protected string $routePrefixClase = 'MiInformacion.clases.tareas';

    protected string $view = 'layouts.MiInformacion.tareas';

    protected string $viewDocente = 'layouts.MiInformacion.tareas_docente';

    protected string $viewEntregas = 'layouts.MiInformacion.tareas_entregas';

    protected string $tituloPagina = 'TAREAS';

    protected function claseIdsDelAlumno($user): array
    {
        $clases = $user->horarioClases()->get();

        if ($clases->isEmpty() && $user->academicProfile?->career_id) {
            $careerId = (int) $user->academicProfile->career_id;
            $semestre = $user->academicProfile->semestre;

            $clases = HorarioClase::query()
                ->where('career_id', $careerId)
                ->when($semestre !== null && $semestre !== '', function ($q) use ($semestre) {
                    $q->whereHas('materia', fn ($mq) => $mq->where('semestre', $semestre));
                })
                ->get();
        }

        return $clases->pluck('id')->all();
    }

    protected function alumnosDeClase(HorarioClase $clase)
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
                ->get();
        }

        return $alumnos;
    }

    public function index()
    {
        $user = Auth::user();
        $user->load('academicProfile');

        if ($user->hasActiveRole('docente')) {
            $misClases = HorarioClase::query()
                ->with('materia')
                ->where('user_id', $user->id)
                ->get();

            $tareas = Tarea::query()
                ->where('tipo', $this->tipo)
                ->whereHas('horarioClase', fn ($q) => $q->where('user_id', $user->id))
                ->with(['horarioClase.materia', 'entregas.alumno'])
                ->orderByDesc('created_at')
                ->get();

            return view($this->view, [
                'user' => $user,
                'tareas' => $tareas,
                'misClases' => $misClases,
                'routePrefix' => $this->routePrefix,
                'tituloPagina' => $this->tituloPagina,
            ]);
        }

        $claseIds = $this->claseIdsDelAlumno($user);

        $tareas = Tarea::query()
            ->where('tipo', $this->tipo)
            ->whereIn('horario_clase_id', $claseIds)
            ->with(['horarioClase.materia', 'entregas' => fn ($q) => $q->where('user_id', $user->id)])
            ->orderByDesc('fecha_vencimiento')
            ->get();

        return view($this->view, [
            'user' => $user,
            'tareas' => $tareas,
            'routePrefix' => $this->routePrefix,
            'tituloPagina' => $this->tituloPagina,
        ]);
    }

    /**
     * Vista del docente scopeada a una sola materia/clase (acceso desde la
     * tarjeta de Clases): "Docente / Tareas (Opción portafolio)" o
     * "Docente / Evaluaciones (Opción checklist)".
     */
    public function porMateria(HorarioClase $clase)
    {
        $user = Auth::user();
        abort_unless($user->hasActiveRole('docente') && $clase->user_id === $user->id, 403);

        $clase->load('materia');
        $totalAlumnos = max($this->alumnosDeClase($clase)->count(), 1);

        $tareas = Tarea::query()
            ->where('tipo', $this->tipo)
            ->where('horario_clase_id', $clase->id)
            ->with('entregas')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Tarea $tarea) use ($totalAlumnos) {
                $entregadas = $tarea->entregas->whereNotNull('fecha_entrega')->count();
                $tarea->cumplimiento = (int) round(($entregadas / $totalAlumnos) * 100);
                return $tarea;
            });

        return view($this->viewDocente, [
            'user' => $user,
            'clase' => $clase,
            'tareas' => $tareas,
            'routePrefix' => $this->routePrefix,
            'routePrefixClase' => $this->routePrefixClase,
            'tituloPagina' => $this->tituloPagina,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->hasActiveRole('docente'), 403);

        $data = $request->validate([
            'horario_clase_id' => 'required|exists:horario_clases,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_apertura' => 'nullable|date',
            'fecha_vencimiento' => 'required|date',
            'fecha_cierre' => 'nullable|date',
            'puntaje_maximo' => 'nullable|integer|min:1|max:1000',
        ]);

        $clase = HorarioClase::where('id', $data['horario_clase_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        Tarea::create([
            'horario_clase_id' => $clase->id,
            'created_by' => $user->id,
            'tipo' => $this->tipo,
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'] ?? null,
            'fecha_apertura' => $data['fecha_apertura'] ?? now(),
            'fecha_vencimiento' => $data['fecha_vencimiento'],
            'fecha_cierre' => $data['fecha_cierre'] ?? null,
            'puntaje_maximo' => $data['puntaje_maximo'] ?? 100,
        ]);

        return back()->with('success', ucfirst($this->tipo) . ' creada correctamente.');
    }

    public function update(Request $request, Tarea $tarea)
    {
        $user = Auth::user();
        $tarea->load('horarioClase');
        abort_unless($tarea->horarioClase->user_id === $user->id, 403);

        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_apertura' => 'nullable|date',
            'fecha_vencimiento' => 'required|date',
            'fecha_cierre' => 'nullable|date',
            'puntaje_maximo' => 'nullable|integer|min:1|max:1000',
        ]);

        $tarea->update($data);

        return back()->with('success', 'Actualizado correctamente.');
    }

    public function destroy(Tarea $tarea)
    {
        $user = Auth::user();
        $tarea->load('horarioClase');
        abort_unless($tarea->horarioClase->user_id === $user->id, 403);

        $tarea->delete();

        return back()->with('success', 'Eliminado correctamente.');
    }

    public function entregar(Tarea $tarea)
    {
        $user = Auth::user();
        $claseIds = $this->claseIdsDelAlumno($user);
        abort_unless(in_array($tarea->horario_clase_id, $claseIds, true), 403);

        TareaEntrega::updateOrCreate(
            ['tarea_id' => $tarea->id, 'user_id' => $user->id],
            ['fecha_entrega' => now()]
        );

        return back()->with('success', 'Entregado correctamente.');
    }

    public function entregas(Tarea $tarea)
    {
        $user = Auth::user();
        $tarea->load(['horarioClase.materia']);
        abort_unless($tarea->horarioClase->user_id === $user->id, 403);

        $alumnos = $this->alumnosDeClase($tarea->horarioClase);
        $entregas = $tarea->entregas()->get()->keyBy('user_id');

        return view($this->viewEntregas, compact('tarea', 'alumnos', 'entregas') + [
            'routePrefix' => $this->routePrefix,
            'routePrefixClase' => $this->routePrefixClase,
        ]);
    }

    public function calificar(Request $request, Tarea $tarea, User $alumno)
    {
        $user = Auth::user();
        $tarea->load('horarioClase');
        abort_unless($tarea->horarioClase->user_id === $user->id, 403);

        $data = $request->validate([
            'puntaje_obtenido' => 'required|integer|min:0|max:' . $tarea->puntaje_maximo,
        ]);

        TareaEntrega::updateOrCreate(
            ['tarea_id' => $tarea->id, 'user_id' => $alumno->id],
            ['puntaje_obtenido' => $data['puntaje_obtenido']]
        );

        return back()->with('success', 'Puntaje guardado correctamente.');
    }
}
