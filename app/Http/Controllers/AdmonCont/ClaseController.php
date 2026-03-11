<?php

namespace App\Http\Controllers\AdmonCont;

use App\Http\Controllers\Controller;
use App\Models\Users\Career;
use App\Models\Users\User;
use App\Models\AdmonCont\Materia;
use App\Models\AdmonCont\HorarioClase;
use App\Models\AdmonCont\HorarioClaseOculta;
use Illuminate\Http\Request;

/**
 * Clases: el usuario de control académico asigna alumnos (status Alumno Activo)
 * a los horarios/clases. Crear, editar, eliminar y visualizar.
 */
class ClaseController extends Controller
{
    /**
     * Index: filtros Carrera, Semestre, Materia.
     * Si hay clase seleccionada: lista de alumnos disponibles (Alumno Activo) y panel de inscritos.
     * Tabla de clases existentes con Ver / Editar / Eliminar.
     */
    public function index(Request $request)
    {
        $carreras = Career::orderBy('name')->get();
        $carreraId = $request->filled('carrera_id') ? (int) $request->carrera_id : null;
        $semestre = $request->filled('semestre') ? $request->semestre : null;
        $materiaId = $request->filled('materia_id') ? (int) $request->materia_id : null;

        $semestresCarrera = range(1, 8);

        $materias = Materia::query()
            ->when($carreraId, fn($q) => $q->where('career_id', $carreraId))
            ->when($semestre !== null && $semestre !== '', fn($q) => $q->where('semestre', $semestre))
            ->orderBy('nombre')
            ->get();

        $clase = null;
        $alumnosDisponibles = collect();
        $alumnosInscritos = [];
        $claseIds = $request->has('clase_id')
            ? array_map('intval', (array) $request->clase_id)
            : [];

        if ($carreraId && $materiaId) {
            $query = HorarioClase::where('career_id', $carreraId)
                ->where('materia_id', $materiaId)
                ->with(['carrera', 'materia', 'user', 'aula', 'alumnos']);
            $clase = !empty($claseIds)
                ? $query->find($claseIds[0])
                : $query->first();

            if ($clase) {
                $semestreClase = $clase->materia->semestre ?? null;
                $alumnosInscritos = $clase->alumnos->pluck('id')->toArray();

                $alumnosDisponibles = User::query()
                    ->whereHas('roles', fn($q) => $q->where('name', 'estudiante'))
                    ->whereHas('academicProfile', function ($q) use ($carreraId, $semestreClase) {
                        $q->where('status', 'Alumno Activo')->where('career_id', $carreraId);
                        if ($semestreClase !== null && $semestreClase !== '') {
                            $q->where('semestre', $semestreClase);
                        }
                    })
                    ->with('academicProfile.career')
                    ->orderBy('nombre')
                    ->orderBy('apellido_paterno')
                    ->get();
            }
        }

        $clases = HorarioClase::query()
            ->with(['carrera', 'materia', 'user', 'aula', 'franjas', 'alumnos'])
            ->when($carreraId, fn($q) => $q->where('career_id', $carreraId))
            ->when($materiaId, fn($q) => $q->where('materia_id', $materiaId))
            ->when($semestre !== null && $semestre !== '', function ($q) use ($semestre) {
                $q->whereHas('materia', fn($mq) => $mq->where('semestre', $semestre));
            })
            ->orderBy('career_id')
            ->orderBy('materia_id')
            ->get();

        $clasesParaSelect = $materiaId ? collect($clases->all()) : collect();
        if ($clasesParaSelect->isNotEmpty()) {
            $clasesParaSelect = $clasesParaSelect->sortBy(function ($hc) {
                $minDay = 99;
                foreach ($hc->franjas ?? [] as $f) {
                    $dias = $f->dias_semana;
                    if (is_string($dias)) {
                        $dias = json_decode($dias, true);
                    }
                    if (is_array($dias)) {
                        foreach ($dias as $d) {
                            $minDay = min($minDay, (int) $d);
                        }
                    } elseif ($dias !== null && $dias !== '') {
                        $minDay = min($minDay, (int) $dias);
                    }
                }
                return $minDay;
            })->values();
        }

        $diaSemana = $request->filled('dia_semana') ? (int) $request->dia_semana : null;
        if ($diaSemana >= 1 && $diaSemana <= 7) {
            $clases = $clases->filter(function ($hc) use ($diaSemana) {
                foreach ($hc->franjas as $f) {
                    $dias = $f->dias_semana;
                    if (is_array($dias)) {
                        if (in_array($diaSemana, $dias)) {
                            return true;
                        }
                        continue;
                    }
                    if (is_string($dias)) {
                        $dec = json_decode($dias, true);
                        if (is_array($dec) && in_array($diaSemana, $dec)) {
                            return true;
                        }
                        continue;
                    }
                    if (is_numeric($dias) && (int) $dias === $diaSemana) {
                        return true;
                    }
                }
                return false;
            })->values();
        }

        // Ocultar clases ya "guardadas" (finalizadas) para que no vuelvan a aparecer en la tabla.
        // Origen: BD (horario_clase_ocultas por usuario). Si hay datos en sesión, se migran a BD una vez.
        $user = $request->user();
        $clasesOcultas = [];
        if ($user) {
            $clasesOcultas = $user->horarioClaseOcultas()->pluck('horario_clase_id')->map(fn ($id) => (int) $id)->toArray();
            $prevSession = array_map('intval', (array) $request->session()->get('clases_ocultas', []));
            if (!empty($prevSession)) {
                foreach ($prevSession as $hid) {
                    if ($hid > 0) {
                        HorarioClaseOculta::firstOrCreate(
                            ['user_id' => $user->id, 'horario_clase_id' => $hid]
                        );
                    }
                }
                $request->session()->forget('clases_ocultas');
                $clasesOcultas = $user->horarioClaseOcultas()->pluck('horario_clase_id')->map(fn ($id) => (int) $id)->toArray();
            }
        }
        $clasesOcultas = array_values(array_unique(array_filter($clasesOcultas, fn($v) => $v > 0)));
        $clasesParaTabla = $clases->filter(fn($hc) => !in_array((int) $hc->id, $clasesOcultas, true))->values();

        // Para la tabla superior: alumno representativo por carrera (prioridad semestre seleccionado; si no hay, cualquiera de la carrera)
        $careerIds = $clasesParaTabla->pluck('career_id')->filter()->unique()->values();
        $alumnoPorCarrera = collect();
        if ($careerIds->isNotEmpty()) {
            $alumnosQuery = User::query()
                ->whereHas('roles', fn($q) => $q->where('name', 'estudiante'))
                ->whereHas('academicProfile', function ($q) use ($careerIds, $semestre) {
                    $q->whereIn('career_id', $careerIds->all());
                    if ($semestre !== null && $semestre !== '') {
                        $q->where('semestre', $semestre);
                    }
                })
                ->with('academicProfile.career')
                ->orderBy('nombre')
                ->orderBy('apellido_paterno')
                ->orderBy('apellido_materno');

            $alumnos = $alumnosQuery->get();
            $alumnoPorCarrera = $alumnos->groupBy(fn($u) => (int) ($u->academicProfile->career_id ?? 0))
                ->map(fn($items) => $items->first());

            // Si al filtrar por semestre no hay alumno para alguna carrera, usar cualquier alumno de esa carrera para mostrar nombre/matrícula
            $careerIdsSinAlumno = $careerIds->filter(fn($id) => !$alumnoPorCarrera->has($id))->values();
            if ($careerIdsSinAlumno->isNotEmpty()) {
                $alumnosFallback = User::query()
                    ->whereHas('roles', fn($q) => $q->where('name', 'estudiante'))
                    ->whereHas('academicProfile', fn($q) => $q->whereIn('career_id', $careerIdsSinAlumno->all()))
                    ->with('academicProfile.career')
                    ->orderBy('nombre')
                    ->orderBy('apellido_paterno')
                    ->orderBy('apellido_materno')
                    ->get();
                foreach ($alumnosFallback->groupBy(fn($u) => (int) ($u->academicProfile->career_id ?? 0)) as $cid => $items) {
                    if (!$alumnoPorCarrera->has($cid)) {
                        $alumnoPorCarrera->put($cid, $items->first());
                    }
                }
            }
        }

        return view('layouts.ControlAdmin.Clases.index', compact(
            'carreras',
            'materias',
            'clase',
            'alumnosDisponibles',
            'alumnosInscritos',
            'clases',
            'clasesParaTabla',
            'alumnoPorCarrera',
            'carreraId',
            'semestre',
            'materiaId',
            'claseIds',
            'semestresCarrera',
            'diaSemana',
            'clasesParaSelect'
        ));
    }

    /**
     * Crear asignación: redirige al index con filtros para elegir clase y alumnos.
     */
    public function create(Request $request)
    {
        return redirect()->route('control.classes.index', $request->only(['carrera_id', 'semestre', 'materia_id']));
    }

    /**
     * Guardar alumnos seleccionados en la clase (horario_clase_user).
     */
    public function store(Request $request)
    {
        $request->validate([
            'horario_clase_id' => 'required|exists:horario_clases,id',
            'alumnos' => 'nullable|array',
            'alumnos.*' => 'exists:users,id',
        ]);

        $clase = HorarioClase::findOrFail($request->horario_clase_id);
        $ids = $request->input('alumnos', []);
        $clase->alumnos()->syncWithoutDetaching($ids);

        return redirect()
            ->route('control.classes.index', [
                'carrera_id' => $clase->career_id,
                'semestre' => $clase->materia->semestre,
                'materia_id' => $clase->materia_id,
            ])
            ->with('success', 'Alumnos agregados a la clase correctamente.');
    }

    /**
     * Guardar el contenido de la cajita (clases agregadas) en la base de datos.
     * Se guarda por usuario en horario_clase_ocultas para ocultar esas filas de la tabla.
     */
    public function guardarCajita(Request $request)
    {
        $request->validate([
            'clase_ids' => 'nullable|array',
            'clase_ids.*' => 'integer|exists:horario_clases,id',
            'carrera_id' => 'nullable|integer',
            'semestre' => 'nullable',
            'materia_id' => 'nullable|integer',
        ]);

        $ids = array_values(array_unique(array_map('intval', $request->input('clase_ids', []))));
        $user = $request->user();

        if ($user) {
            $prevSession = array_map('intval', (array) $request->session()->get('clases_ocultas', []));
            $todosIds = array_values(array_unique(array_merge($prevSession, $ids)));
            foreach ($todosIds as $horarioClaseId) {
                if ($horarioClaseId > 0) {
                    HorarioClaseOculta::firstOrCreate(
                        ['user_id' => $user->id, 'horario_clase_id' => $horarioClaseId]
                    );
                }
            }
        }

        $request->session()->forget('clases_agregadas_cajita');
        $request->session()->forget('clases_ocultas');

        return redirect()
            ->route('control.classes.index', array_filter([
                'carrera_id' => $request->input('carrera_id'),
                'semestre' => $request->input('semestre'),
                'materia_id' => $request->input('materia_id'),
            ], fn($v) => $v !== null && $v !== ''))
            ->with('success', 'Departamento actualizado exitosamente.');
    }

    /**
     * Ver detalle de la clase (carrera, materia, docente, alumnos inscritos).
     */
    public function show(HorarioClase $clase)
    {
        $clase->load(['carrera', 'materia', 'user', 'aula', 'franjas', 'alumnos.academicProfile']);
        return view('layouts.ControlAdmin.Clases.show', compact('clase'));
    }

    /**
     * Editar alumnos de la clase (marcar/desmarcar inscritos).
     */
    public function edit(HorarioClase $clase)
    {
        $clase->load(['carrera', 'materia', 'alumnos']);
        $carreraId = $clase->career_id;
        $semestreClase = $clase->materia->semestre ?? null;

        $alumnosDisponibles = User::query()
            ->whereHas('roles', fn($q) => $q->where('name', 'estudiante'))
            ->whereHas('academicProfile', function ($q) use ($carreraId, $semestreClase) {
                $q->where('status', 'Alumno Activo')->where('career_id', $carreraId);
                if ($semestreClase !== null && $semestreClase !== '') {
                    $q->where('semestre', $semestreClase);
                }
            })
            ->with('academicProfile')
            ->orderBy('nombre')
            ->orderBy('apellido_paterno')
            ->get();

        $alumnosInscritos = $clase->alumnos->pluck('id')->toArray();

        return view('layouts.ControlAdmin.Clases.edit', compact(
            'clase',
            'alumnosDisponibles',
            'alumnosInscritos'
        ));
    }

    /**
     * Actualizar lista de alumnos inscritos (sync).
     */
    public function update(Request $request, HorarioClase $clase)
    {
        $request->validate([
            'alumnos' => 'nullable|array',
            'alumnos.*' => 'exists:users,id',
        ]);

        $clase->alumnos()->sync($request->input('alumnos', []));

        return redirect()
            ->route('control.classes.show', $clase->id)
            ->with('success', 'Alumnos de la clase actualizados correctamente.');
    }

    /**
     * Eliminar la clase (horario/clase). Las franjas y asignaciones se eliminan en cascada.
     */
    public function destroy(HorarioClase $clase)
    {
        try {
            $clase->delete();
            return redirect()
                ->route('control.classes.index')
                ->with('success', 'Clase eliminada correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('control.classes.index')
                ->with('error', 'No se pudo eliminar la clase.');
        }
    }

    /**
     * Inscribir a todos los alumnos elegibles (Alumno Activo, misma carrera y semestre).
     */
    public function inscribirTodos(HorarioClase $clase)
    {
        $clase->load('materia');
        $carreraId = $clase->career_id;
        $semestreClase = $clase->materia->semestre ?? null;

        $ids = User::query()
            ->whereHas('roles', fn($q) => $q->where('name', 'estudiante'))
            ->whereHas('academicProfile', function ($q) use ($carreraId, $semestreClase) {
                $q->where('status', 'Alumno Activo')->where('career_id', $carreraId);
                if ($semestreClase !== null && $semestreClase !== '') {
                    $q->where('semestre', $semestreClase);
                }
            })
            ->pluck('id');

        $clase->alumnos()->syncWithoutDetaching($ids);

        return redirect()
            ->route('control.classes.show', $clase->id)
            ->with('success', 'Se inscribieron todos los alumnos elegibles.');
    }
}
