<?php

namespace App\Http\Controllers\AdmonCont;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Users\Career;
use App\Models\Users\User;
use App\Models\AdmonCont\Materia;
use App\Models\AdmonCont\HorarioClase;
use App\Models\AdmonCont\HorarioFranja;
use App\Models\AdmonCont\Facility;
use App\Support\AulaHorarioPresenter;

class HorarioController extends Controller
{
    /**
     * Aulas de infraestructura que coinciden con carrera + materia (pivot o legacy).
     */
    public function aulasDisponibles(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'materia_id' => 'required|integer|exists:materias,id',
            'career_id' => 'nullable|integer|exists:careers,id',
        ]);

        $materia = Materia::query()->findOrFail((int) $validated['materia_id']);
        $careerIdEsperado = (int) $materia->career_id;
        $careerId = isset($validated['career_id']) && $validated['career_id'] !== null && $validated['career_id'] !== ''
            ? (int) $validated['career_id']
            : $careerIdEsperado;
        if ($careerId !== $careerIdEsperado) {
            return response()->json(['aulas' => []]);
        }

        $aulas = Facility::query()
            ->where(function ($q) use ($careerIdEsperado, $materia) {
                // Buscar por tablas pivote (nuevo)
                $q->where(function ($q2) use ($careerIdEsperado, $materia) {
                    $q2->whereHas('careers', function ($sq) use ($careerIdEsperado) {
                        $sq->where('careers.id', $careerIdEsperado);
                    })->whereHas('materias', function ($sq) use ($materia) {
                        $sq->where('materias.id', $materia->id);
                    });
                })
                // O por campos legacy (compatibilidad con aulas antiguas)
                ->orWhere(function ($q2) use ($careerIdEsperado, $materia) {
                    $q2->where('career_id', $careerIdEsperado)
                       ->where('tipo_materia', trim((string) $materia->nombre));
                });
            })
            ->orderBy('nombre_aula')
            ->orderBy('id')
            ->get();

        return response()->json([
            'aulas' => $aulas->map(fn ($f) => [
                'id' => $f->id,
                'label' => AulaHorarioPresenter::selectOptionSoloSeccion($f),
            ])->values(),
        ]);
    }

    private function assertAulaCoincideCarreraMateria(Request $request): void
    {
        if (! $request->filled('aula_id')) {
            return;
        }

        $materia = Materia::query()->findOrFail((int) $request->materia_id);

        $ok = Facility::query()
            ->where('id', (int) $request->aula_id)
            ->where(function ($q) use ($request, $materia) {
                $q->where(function ($q2) use ($request, $materia) {
                    $q2->whereHas('careers', function ($sq) use ($request) {
                        $sq->where('careers.id', (int) $request->carrera_id);
                    })->whereHas('materias', function ($sq) use ($materia) {
                        $sq->where('materias.id', $materia->id);
                    });
                })
                ->orWhere(function ($q2) use ($request, $materia) {
                    $q2->where('career_id', (int) $request->carrera_id)
                       ->where('tipo_materia', trim((string) $materia->nombre));
                });
            })
            ->exists();

        if (! $ok) {
            throw ValidationException::withMessages([
                'aula_id' => ['El aula no corresponde a la carrera y materia seleccionadas.'],
            ]);
        }
    }

    public function index(Request $request)
    {
        $carreras = Career::with('classification')->get();
        $aulas = Facility::orderedForHorarios()->get();
        $periodoActivo = DB::table('periods')->orderByDesc('id')->first();
        $query = HorarioClase::with(['carrera.classification', 'materia', 'user', 'aula', 'franjas'])
            ->where('period_id', $periodoActivo?->id);
        $search = $request->search_query;

        $docentes = User::with(['academicProfile', 'teachingCareers'])->whereHas('roles', function ($query) {
            $query->where('name', 'docente');
        })->get();

        $materias = Materia::all();

        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('materia', function ($sq) use ($search) {
                    $sq->where('nombre', 'LIKE', '%' . $search . '%');
                })
                ->orWhereHas('carrera', function ($sq) use ($search) {
                    $sq->where('name', 'LIKE', '%' . $search . '%');
                })
                ->orWhereHas('carrera.classification', function ($sq) use ($search) {
                    $sq->where('name', 'LIKE', '%' . $search . '%');
                })
                ->orWhereHas('user', function ($sq) use ($search) {
                    $sq->where('nombre', 'LIKE', '%' . $search . '%');
                });
            });
        }

        $horarios = $query->get();

        return view('layouts.ControlAdmin.Horarios.index', [
            'carreras' => $carreras,
            'aulas' => $aulas,
            'materias' => $materias,
            'docentes' => $docentes,
            'horarios' => $horarios,
        ]);
    }

    public function show(Request $request, HorarioClase $horario)
    {
        $horario->load(['carrera.classification', 'materia', 'user', 'aula', 'franjas']);

        if ($request->wantsJson() || $request->ajax()) {
            $diasNombres = ['1' => 'Lunes', '2' => 'Martes', '3' => 'Miércoles', '4' => 'Jueves', '5' => 'Viernes', '6' => 'Sábado', '7' => 'Domingo'];
            $franjas = $horario->franjas->map(function ($f) use ($diasNombres) {
                $dias = is_array($f->dias_semana) ? $f->dias_semana : [$f->dias_semana];
                $diaStr = implode(', ', array_map(fn($d) => $diasNombres[(string)$d] ?? 'Día ' . $d, $dias));
                return [
                    'dia_str' => $diaStr,
                    'hora_inicio' => \Carbon\Carbon::parse($f->hora_inicio)->format('h:i A'),
                    'hora_fin' => \Carbon\Carbon::parse($f->hora_fin)->format('h:i A'),
                ];
            })->toArray();

            return response()->json([
                'carrera' => $horario->carrera->name ?? '—',
                'clasificacion' => $horario->carrera?->classification?->name ?? '—',
                'materia' => $horario->materia->nombre ?? '—',
                'docente' => $horario->user->nombre ?? '—',
                'aula' => $horario->aula
                    ? AulaHorarioPresenter::selectOptionSoloSeccion($horario->aula)
                    : '—',
                'aula_info' => AulaHorarioPresenter::toApiArray($horario->aula),
                'franjas' => $franjas,
            ]);
        }

        return redirect()->route('control.schedules.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'materia_id' => 'required|exists:materias,id',
            'carrera_id' => 'required|exists:careers,id',
            'docente_id' => 'required|exists:users,id',
            'aula_id'    => 'nullable|exists:facilities,id',
            'franjas_json' => 'required|json',
        ], [
            'docente_id.required' => 'Seleccione un docente.',
            'materia_id.required' => 'Seleccione una materia.',
            'carrera_id.required' => 'Seleccione una carrera.',
            'franjas_json.required' => '',
        ]);

        $this->assertAulaCoincideCarreraMateria($request);

        // Validar que no exista un horario duplicado (misma carrera + materia + docente)
        $duplicado = HorarioClase::where('career_id', $request->carrera_id)
            ->where('materia_id', $request->materia_id)
            ->where('user_id', $request->docente_id)
            ->where('period_id', DB::table('periods')->orderByDesc('id')->value('id'))
            ->exists();
        if ($duplicado) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Ya existe un horario con esta combinación de carrera, materia y docente.'], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Ya existe un horario con esta combinación de carrera, materia y docente.']);
        }

        $franjasData = json_decode($request->franjas_json, true);

        if (empty($franjasData)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Añada al menos una franja horaria.',
                ], 422);
            }
            return redirect()->back()->withErrors(['franjas_json' => '']);
        }

        DB::beginTransaction();
        try {
            $horarioClase = HorarioClase::create([
                'materia_id' => $request->materia_id,
                'career_id'  => $request->carrera_id,
                'user_id'    => $request->docente_id,
                'aula_id'    => $request->aula_id ?: null,
                'period_id'  => DB::table('periods')->orderByDesc('id')->value('id'),
            ]);

            $franjasAGuardar = [];
            foreach ($franjasData as $franja) {
                foreach ($franja['dias_semana'] as $dia) {
                    $franjasAGuardar[] = [
                        'dias_semana'  => $dia,
                        'hora_inicio' => $franja['hora_inicio'],
                        'hora_fin'    => $franja['hora_fin'],
                    ];
                }
            }

            $horarioClase->franjas()->createMany($franjasAGuardar);

            DB::commit();

            $message = 'Horario registrado correctamente.';
            if ($request->expectsJson()) {
                return response()->json(['ok' => true, 'message' => $message]);
            }
            return redirect()
                ->route('control.schedules.index', ['modal' => 'success'])
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Error al guardar el horario: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Error al guardar el horario: ' . $e->getMessage()]);
        }
    }

    public function destroy(HorarioClase $horario)
    {
        $request = request();

        try {
            $horario->delete();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => true,
                    'success' => true,
                    'message' => 'Horario eliminado correctamente.',
                ]);
            }

            return redirect()
                ->route('control.schedules.index', ['modal' => 'success'], 303)
                ->with('success', 'Horario eliminado correctamente.');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => false,
                    'success' => false,
                    'message' => 'No se pudo eliminar el horario.',
                ], 500);
            }

            return redirect()
                ->route('control.schedules.index')
                ->withErrors(['error' => 'No se pudo eliminar el horario.']);
        }
    }

    public function editData(HorarioClase $horario)
    {
        $horario->load('franjas');
        $franjas = $horario->franjas->map(function ($f) {
            $dias = is_array($f->dias_semana) ? $f->dias_semana : [$f->dias_semana];
            return [
                'dias_semana' => array_map('intval', $dias),
                'hora_inicio' => \Carbon\Carbon::parse($f->hora_inicio)->format('H:i'),
                'hora_fin'    => \Carbon\Carbon::parse($f->hora_fin)->format('H:i'),
            ];
        })->toArray();

        return response()->json([
            'career_id'  => $horario->career_id,
            'materia_id' => $horario->materia_id,
            'user_id'    => $horario->user_id,
            'aula_id'    => $horario->aula_id,
            'franjas'    => $franjas,
            'update_url' => route('control.schedules.update', $horario->id),
        ]);
    }

    public function edit(Request $request, HorarioClase $horario)
    {
        $horario->load(['franjas', 'aula']);
        $carreras = Career::with('classification')->get();
        $docentes = User::with(['academicProfile', 'teachingCareers'])->whereHas('roles', function ($q) {
            $q->where('name', 'docente');
        })->get();
        $materias = Materia::all();
        $data = compact('carreras', 'materias', 'docentes', 'horario');
        if ($request->ajax() || $request->wantsJson()) {
            return view('layouts.ControlAdmin.Horarios.edit_partial', $data);
        }
        return view('layouts.ControlAdmin.Horarios.edit', $data);
    }

    public function update(Request $request, HorarioClase $horario)
    {
        $request->validate([
            'materia_id' => 'required|exists:materias,id',
            'carrera_id' => 'required|exists:careers,id',
            'docente_id' => 'required|exists:users,id',
            'aula_id'    => 'nullable|exists:facilities,id',
            'franjas_json' => 'required|json',
        ], [
            'docente_id.required' => 'Seleccione un docente.',
            'materia_id.required' => 'Seleccione una materia.',
            'carrera_id.required' => 'Seleccione una carrera.',
            'franjas_json.required' => '',
        ]);

        $this->assertAulaCoincideCarreraMateria($request);

        // Validar que no exista un horario duplicado (excluyendo el actual)
        $duplicado = HorarioClase::where('career_id', $request->carrera_id)
            ->where('materia_id', $request->materia_id)
            ->where('user_id', $request->docente_id)
            ->where('id', '!=', $horario->id)
            ->exists();
        if ($duplicado) {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'message' => 'Ya existe un horario con esta combinación de carrera, materia y docente.'], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Ya existe un horario con esta combinación de carrera, materia y docente.']);
        }

        $franjasData = json_decode($request->franjas_json, true);

        if (empty($franjasData)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Añada al menos una franja horaria.',
                ], 422);
            }
            return redirect()->back()->withInput()->withErrors(['franjas_json' => '']);
        }

        DB::beginTransaction();
        try {
            $horario->update([
                'materia_id' => $request->materia_id,
                'career_id'  => $request->carrera_id,
                'user_id'    => $request->docente_id,
                'aula_id'    => $request->aula_id ?: null,
            ]);

            $franjasAGuardar = [];
            foreach ($franjasData as $franja) {
                foreach ($franja['dias_semana'] as $dia) {
                    $franjasAGuardar[] = [
                        'dias_semana' => $dia,
                        'hora_inicio' => $franja['hora_inicio'],
                        'hora_fin'    => $franja['hora_fin'],
                    ];
                }
            }

            $horario->franjas()->delete();
            $horario->franjas()->createMany($franjasAGuardar);

            DB::commit();

            $message = 'Horario actualizado correctamente.';
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => true, 'success' => true, 'message' => $message]);
            }
            return redirect()
                ->route('control.schedules.index', ['modal' => 'success'])
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'success' => false, 'message' => 'Error al actualizar el horario.'], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Error al actualizar el horario.']);
        }
    }
}