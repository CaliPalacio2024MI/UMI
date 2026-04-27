<?php

namespace App\Http\Controllers\AdmonCont;

use App\Http\Controllers\Controller;
use App\Models\Users\Career;
use App\Models\Users\CareerClassification;
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
     * Index: filtros Clasificación, Carrera, Materia, Semestre (académico del alumno), Horario.
     * El semestre del filtro restringe la tabla de alumnos (perfil académico), no la materia en retícula.
     * Si hay clase seleccionada: lista de alumnos disponibles (Alumno Activo) y panel de inscritos.
     */
    public function index(Request $request)
    {
        $classificationId = $request->filled('career_classification_id') ? (int) $request->career_classification_id : null;
        $clasificaciones = CareerClassification::orderBy('name')->get();

        $carreras = Career::query()
            ->when($classificationId, fn ($q) => $q->where('career_classification_id', $classificationId))
            ->orderBy('name')
            ->get();

        $carreraId = $request->filled('carrera_id') ? (int) $request->carrera_id : null;
        if ($carreraId && !$carreras->firstWhere('id', $carreraId)) {
            $carreraId = null;
        }

        $semestre = $request->filled('semestre') ? $request->semestre : null;
        $materiaId = $request->filled('materia_id') ? (int) $request->materia_id : null;
        if (!$carreraId) {
            $materiaId = null;
        }

        $semestresCarrera = range(1, 8);

        $materias = Materia::query()
            ->when($carreraId, fn($q) => $q->where('career_id', $carreraId))
            ->orderBy('nombre')
            ->get();

        if ($materiaId && $carreraId) {
            $materiaOk = Materia::query()
                ->where('id', $materiaId)
                ->where('career_id', $carreraId)
                ->exists();
            if (!$materiaOk) {
                $materiaId = null;
            }
        }

        $clase = null;
        $alumnosDisponibles = collect();
        $alumnosInscritos = [];
        $claseIds = $request->has('clase_id')
            ? array_values(array_unique(array_map('intval', (array) $request->clase_id)))
            : [];
        $rawAlumnoCtx = $request->input('alumno_context_id');
        $alumnoContextIds = [];
        if (is_array($rawAlumnoCtx)) {
            $alumnoContextIds = array_values(array_unique(array_map('intval', $rawAlumnoCtx)));
        } elseif ($rawAlumnoCtx !== null && $rawAlumnoCtx !== '') {
            $alumnoContextIds = [(int) $rawAlumnoCtx];
        }

        if ($carreraId && $materiaId) {
            $query = HorarioClase::where('career_id', $carreraId)
                ->where('materia_id', $materiaId)
                ->with(['carrera', 'materia', 'user', 'aula', 'alumnos']);
            $clase = !empty($claseIds)
                ? $query->find($claseIds[0])
                : $query->first();

            if ($clase) {
                $alumnosInscritos = $clase->alumnos->pluck('id')->toArray();

                $alumnosDisponibles = User::query()
                    ->whereHas('roles', fn($q) => $q->where('name', 'estudiante'))
                    ->whereHas('academicProfile', function ($q) use ($carreraId, $semestre) {
                        $q->where('status', 'Alumno Activo')->where('career_id', $carreraId);
                        if ($semestre !== null && $semestre !== '') {
                            $q->where('semestre', $semestre);
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
            ->when(!$materiaId && $semestre !== null && $semestre !== '', function ($q) use ($semestre) {
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

        // Ocultar filas guardadas: registro con alumno_id = solo esa fila (alumno + horario);
        // registro con alumno_id null = ocultar todo el horario_clase (compatibilidad / sesión antigua).
        $user = $request->user();
        $clasesOcultas = [];
        $filasOcultasKeys = []; // "horarioId_alumnoId" => true
        if ($user) {
            $user->load('horarioClaseOcultas');
            foreach ($user->horarioClaseOcultas as $o) {
                $hid = (int) $o->horario_clase_id;
                if ($o->alumno_id) {
                    $filasOcultasKeys[$hid . '_' . (int) $o->alumno_id] = true;
                } elseif ($hid > 0) {
                    $clasesOcultas[] = $hid;
                }
            }
            $prevSession = array_map('intval', (array) $request->session()->get('clases_ocultas', []));
            if (!empty($prevSession)) {
                foreach ($prevSession as $hid) {
                    if ($hid > 0) {
                        HorarioClaseOculta::updateOrCreate(
                            [
                                'user_id' => $user->id,
                                'horario_clase_id' => $hid,
                                'alumno_id' => null,
                            ],
                            []
                        );
                    }
                }
                $request->session()->forget('clases_ocultas');
                $clasesOcultas = [];
                $filasOcultasKeys = [];
                $user->unsetRelation('horarioClaseOcultas');
                $user->load('horarioClaseOcultas');
                foreach ($user->horarioClaseOcultas as $o) {
                    $hid = (int) $o->horario_clase_id;
                    if ($o->alumno_id) {
                        $filasOcultasKeys[$hid . '_' . (int) $o->alumno_id] = true;
                    } elseif ($hid > 0) {
                        $clasesOcultas[] = $hid;
                    }
                }
            }
        }
        $clasesOcultas = array_values(array_unique(array_filter($clasesOcultas, fn($v) => $v > 0)));
        $clasesParaTabla = $clases->filter(fn($hc) => !in_array((int) $hc->id, $clasesOcultas, true))->values();

        // Tabla superior: una fila por alumno (no una por carrera), para que varios alumnos de la misma carrera aparezcan todos.
        // Carreras según todos los horarios que cumplen filtros (incl. ocultos): si solo usáramos clasesParaTabla,
        // al guardar/ocultar el único horario de una carrera desaparecerían el resto de alumnos con esa misma carrera.
        $careerIds = $clases->pluck('career_id')->filter()->unique()->values();
        $alumnosParaTabla = collect();
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

            $alumnosParaTabla = $alumnosQuery->get();

            if ($semestre === null || $semestre === '') {
                $careersWithStudent = $alumnosParaTabla->map(fn ($u) => (int) ($u->academicProfile->career_id ?? 0))->unique();
                $careerIdsSinAlumno = $careerIds->filter(fn ($id) => !$careersWithStudent->contains((int) $id))->values();

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
                        if ($items->isNotEmpty() && !$careersWithStudent->contains($cid)) {
                            $alumnosParaTabla->push($items->first());
                            $careersWithStudent->push($cid);
                        }
                    }
                }
            }

            $alumnosParaTabla = $alumnosParaTabla->sortBy(function ($u) {
                return [
                    strtolower((string) ($u->nombre ?? '')),
                    strtolower((string) ($u->apellido_paterno ?? '')),
                    strtolower((string) ($u->apellido_materno ?? '')),
                ];
            })->values();
        }

        return view('layouts.ControlAdmin.Clases.index', compact(
            'clasificaciones',
            'classificationId',
            'carreras',
            'materias',
            'clase',
            'alumnosDisponibles',
            'alumnosInscritos',
            'clases',
            'clasesParaTabla',
            'alumnosParaTabla',
            'carreraId',
            'semestre',
            'materiaId',
            'claseIds',
            'alumnoContextIds',
            'semestresCarrera',
            'diaSemana',
            'clasesParaSelect',
            'filasOcultasKeys'
        ));
    }

    /**
     * Crear asignación: redirige al index con filtros para elegir clase y alumnos.
     */
    public function create(Request $request)
    {
        return redirect()->route('control.classes.index', $request->only([
            'career_classification_id',
            'carrera_id',
            'semestre',
            'materia_id',
        ]));
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

        $clase->loadMissing('carrera');

        return redirect()
            ->route('control.classes.index', array_filter([
                'career_classification_id' => $clase->carrera->career_classification_id,
                'carrera_id' => $clase->career_id,
                'semestre' => $clase->materia->semestre,
                'materia_id' => $clase->materia_id,
            ], fn ($v) => $v !== null && $v !== ''))
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
            'cajita_items' => 'nullable|array',
            'cajita_items.*.horario_clase_id' => 'required|integer|exists:horario_clases,id',
            'cajita_items.*.alumno_id' => 'required|integer|exists:users,id',
            'cajita_items.*.carrera_nombre' => 'nullable|string|max:255',
            'cajita_items.*.semestre' => 'nullable|string|max:32',
            'cajita_items.*.matricula' => 'nullable|string|max:64',
            'cajita_items.*.materia_nombre' => 'nullable|string|max:255',
            'cajita_items.*.horario_resumen' => 'nullable|string|max:2000',
            'cajita_items.*.alumno_nombre' => 'nullable|string|max:255',
            'career_classification_id' => 'nullable|integer|exists:career_classifications,id',
            'carrera_id' => 'nullable|integer',
            'semestre' => 'nullable',
            'materia_id' => 'nullable|integer',
        ]);

        $user = $request->user();

        if ($user) {
            $prevSession = array_map('intval', (array) $request->session()->get('clases_ocultas', []));
            foreach ($prevSession as $horarioClaseId) {
                if ($horarioClaseId > 0) {
                    HorarioClaseOculta::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'horario_clase_id' => $horarioClaseId,
                            'alumno_id' => null,
                        ],
                        []
                    );
                }
            }

            foreach ($request->input('cajita_items', []) as $item) {
                $hcId = (int) ($item['horario_clase_id'] ?? 0);
                $alId = (int) ($item['alumno_id'] ?? 0);
                if ($hcId <= 0 || $alId <= 0) {
                    continue;
                }
                HorarioClaseOculta::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'horario_clase_id' => $hcId,
                        'alumno_id' => $alId,
                    ],
                    [
                        'carrera_nombre' => $item['carrera_nombre'] ?? null,
                        'semestre' => $item['semestre'] ?? null,
                        'matricula' => $item['matricula'] ?? null,
                        'materia_nombre' => $item['materia_nombre'] ?? null,
                        'horario_resumen' => $item['horario_resumen'] ?? null,
                        'alumno_nombre' => $item['alumno_nombre'] ?? null,
                    ]
                );
            }

            // Compat: solo ids de horario sin alumno (oculta el bloque completo)
            foreach (array_unique(array_map('intval', $request->input('clase_ids', []))) as $horarioClaseId) {
                if ($horarioClaseId <= 0) {
                    continue;
                }
                HorarioClaseOculta::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'horario_clase_id' => $horarioClaseId,
                        'alumno_id' => null,
                    ],
                    []
                );
            }
        }

        $request->session()->forget('clases_agregadas_cajita');
        $request->session()->forget('clases_ocultas');

        return redirect()
            ->route('control.classes.index')
            ->with('success', 'Selección guardada correctamente.');
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
        $alumnosDisponibles = User::query()
            ->whereHas('roles', fn($q) => $q->where('name', 'estudiante'))
            ->whereHas('academicProfile', function ($q) use ($carreraId) {
                $q->where('status', 'Alumno Activo')->where('career_id', $carreraId);
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
        $ids = User::query()
            ->whereHas('roles', fn($q) => $q->where('name', 'estudiante'))
            ->whereHas('academicProfile', function ($q) use ($carreraId) {
                $q->where('status', 'Alumno Activo')->where('career_id', $carreraId);
            })
            ->pluck('id');

        $clase->alumnos()->syncWithoutDetaching($ids);

        return redirect()
            ->route('control.classes.show', $clase->id)
            ->with('success', 'Se inscribieron todos los alumnos elegibles.');
    }
}
