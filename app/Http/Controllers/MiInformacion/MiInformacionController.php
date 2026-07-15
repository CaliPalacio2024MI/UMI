<?php

namespace App\Http\Controllers\MiInformacion;

use App\Http\Controllers\Controller;
use App\Models\AdmonCont\HorarioClase;
use App\Models\AdmonCont\HorarioClaseOculta;
use App\Models\AdmonCont\ClaseAsistencia;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

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
        if ($user->hasActiveRole('docente')) {
            $clases = \App\Models\AdmonCont\HorarioClase::query()
                ->with(['materia', 'carrera', 'user', 'aula', 'franjas'])
                ->where('user_id', $user->id)
                ->get();

            return view('layouts.MiInformacion.clases', compact('user', 'clases'));
        }

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

        // --- DOCENTE: sus clases asignadas (mismo criterio que Control Académico) ---
        if ($user->hasActiveRole('docente')) {
            $horarios = HorarioClase::query()
                ->with(['carrera', 'materia', 'aula', 'franjas'])
                ->where('user_id', $user->id)
                ->get();

            return view('layouts.MiInformacion.horario', [
                'user' => $user,
                'horarios' => $horarios,
                'esAlumno' => false,
                'materiaLabels' => [],
                'horarioResumenPorClase' => [],
            ]);
        }

        // --- ALUMNO: mismo criterio que Control Escolar (pivot + "cajita") ---
        // Etiquetas y resumen de horario guardados en Control → Clases (cajita).
        $cajitaRows = HorarioClaseOculta::query()
            ->where('alumno_id', (int) $user->id)
            ->whereNotNull('horario_clase_id')
            ->orderByDesc('id')
            ->get()
            ->unique('horario_clase_id');

        $materiaLabels = [];
        $horarioResumenPorClase = [];
        foreach ($cajitaRows as $row) {
            $hid = (int) $row->horario_clase_id;
            if ($hid > 0 && $row->materia_nombre !== null && $row->materia_nombre !== '') {
                $materiaLabels[$hid] = $row->materia_nombre;
            }
            if ($hid > 0 && $row->horario_resumen !== null && trim((string) $row->horario_resumen) !== '') {
                $horarioResumenPorClase[$hid] = $row->horario_resumen;
            }
        }

        // Inscripciones (pivot).
        $horarios = $user->horarioClases()
            ->with(['carrera', 'materia', 'aula', 'franjas'])
            ->get();

        // Clases de la cajita que no estén en el pivot.
        $idsExtra = $cajitaRows->pluck('horario_clase_id')
            ->map(fn ($x) => (int) $x)
            ->filter()
            ->unique()
            ->diff($horarios->pluck('id'))
            ->values()
            ->all();

        if ($idsExtra !== []) {
            $extras = HorarioClase::query()
                ->with(['carrera', 'materia', 'aula', 'franjas'])
                ->whereIn('id', $idsExtra)
                ->get();
            $horarios = $horarios->merge($extras)->unique('id')->values();
        }

        // Respaldo por carrera/semestre cuando aún no hay pivot ni cajita.
        if ($horarios->isEmpty() && $user->academicProfile?->career_id) {
            $careerId = (int) $user->academicProfile->career_id;
            $semestre = $user->academicProfile->semestre;

            $horarios = HorarioClase::query()
                ->with(['carrera', 'materia', 'aula', 'franjas'])
                ->where('career_id', $careerId)
                ->when($semestre !== null && $semestre !== '', function ($q) use ($semestre) {
                    $q->whereHas('materia', fn ($mq) => $mq->where('semestre', $semestre));
                })
                ->orderBy('materia_id')
                ->get();
        }

        return view('layouts.MiInformacion.horario', [
            'user' => $user,
            'horarios' => $horarios,
            'esAlumno' => true,
            'materiaLabels' => $materiaLabels,
            'horarioResumenPorClase' => $horarioResumenPorClase,
        ]);
    }

    /**
     * Muestra el Historial Académico con calificaciones reales agrupadas por
     * periodo (solo consulta). El periodo vigente y los anteriores se muestran
     * igual; el filtro se aplica en la propia boleta.
     */
    public function showHistorial(Request $request)
    {
        $user = Auth::user();
        $user->load('academicProfile.career');

        // Todas las calificaciones del alumno con su clase/materia.
        $calificaciones = \App\Models\AdmonCont\Calificacion::query()
            ->where('user_id', $user->id)
            ->with(['horarioClase.materia', 'horarioClase.user'])
            ->get();

        // Periodos con calificaciones, ordenados por fecha (recientes primero).
        $periodIds = $calificaciones->pluck('period_id')->filter()->unique()->all();
        $periodos = \App\Models\Users\Period::query()
            ->whereIn('id', $periodIds ?: [0])
            ->orderByDesc('start_date')
            ->get()
            ->keyBy('id');

        $mesesCortos = [1=>'ENE',2=>'FEB',3=>'MAR',4=>'ABR',5=>'MAY',6=>'JUN',7=>'JUL',8=>'AGO',9=>'SEP',10=>'OCT',11=>'NOV',12=>'DIC'];

        // Agrupamos por periodo y, dentro, por materia (clase).
        $porPeriodo = $calificaciones->groupBy('period_id');
        $semestres = [];
        $numero = $porPeriodo->count();

        foreach ($periodos as $pid => $periodo) {
            $notasPeriodo = $porPeriodo->get($pid, collect());
            $porClase = $notasPeriodo->groupBy('horario_clase_id');

            $materias = [];
            $sumaFinal = 0;
            $countFinal = 0;
            foreach ($porClase as $notas) {
                $clase = $notas->first()->horarioClase;
                $capturadas = $notas->filter(fn ($n) => $n->calificacion !== null);
                $final = $capturadas->isNotEmpty() ? (int) round($capturadas->avg('calificacion')) : null;
                if ($final !== null) { $sumaFinal += $final; $countFinal++; }

                $confirmada = $notas->every(fn ($n) => $n->confirmada);
                $materias[] = (object) [
                    'nombre' => $clase->materia->nombre ?? 'Materia',
                    'creditos' => $clase->materia->creditos ?? '—',
                    'calificacion' => $final ?? '--',
                    'evaluacion' => $confirmada ? 'ORD' : 'PREL',
                    'observaciones' => $confirmada ? '-' : 'Preliminar',
                ];
            }

            $inicio = $periodo->start_date;
            $fin = $periodo->end_date;
            $periodoTxt = $inicio && $fin
                ? ($mesesCortos[(int)$inicio->format('n')].' '.$inicio->format('Y')."\n".$mesesCortos[(int)$fin->format('n')].' '.$fin->format('Y'))
                : $periodo->name;

            $semestres[] = (object) [
                'numero' => $numero--,
                'periodo' => $periodoTxt,
                'promedio' => $countFinal > 0 ? round($sumaFinal / $countFinal, 1) : '--',
                'materias' => $materias,
            ];
        }

        // Promedio general y créditos acumulados.
        $todasFinales = collect($semestres)->flatMap(fn ($s) => collect($s->materias)->pluck('calificacion'))
            ->filter(fn ($c) => is_numeric($c));
        $promedioFinal = $todasFinales->isNotEmpty() ? round($todasFinales->avg(), 1) : '--';

        return view('layouts.MiInformacion.historial', compact('user', 'semestres', 'promedioFinal'));
    }

    /**
     * Verifica que el usuario (docente dueño o alumno inscrito/de la misma
     * carrera-semestre) tenga acceso a la clase indicada.
     */
    private function tieneAccesoAClase($user, HorarioClase $clase): bool
    {
        if ($user->hasActiveRole('docente')) {
            return $clase->user_id === $user->id;
        }

        if ($clase->alumnos()->where('users.id', $user->id)->exists()) {
            return true;
        }

        return $user->academicProfile?->career_id
            && (int) $user->academicProfile->career_id === (int) $clase->career_id;
    }

    /**
     * Alumnos que deben aparecer en la lista de asistencia de una clase
     * (asignación explícita o, en su defecto, por carrera/semestre de la materia).
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
                ->get();
        }

        return $alumnos;
    }

    /**
     * El docente actualiza los % de "Rasgos a evaluar" y sube el archivo del temario.
     */
    public function actualizarContenidoMateria(Request $request, HorarioClase $clase)
    {
        $user = Auth::user();
        $clase->load('materia');
        abort_unless($user->hasActiveRole('docente') && $clase->user_id === $user->id, 403);

        $data = $request->validate([
            'peso_tareas' => 'required|integer|min:0|max:100',
            'peso_evaluaciones' => 'required|integer|min:0|max:100',
            'peso_asistencias' => 'required|integer|min:0|max:100',
            'temario_archivo' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $materia = $clase->materia;

        $updateData = [
            'peso_tareas' => $data['peso_tareas'],
            'peso_evaluaciones' => $data['peso_evaluaciones'],
            'peso_asistencias' => $data['peso_asistencias'],
        ];

        if ($request->hasFile('temario_archivo')) {
            if ($materia->temario_archivo) {
                Storage::disk('public')->delete($materia->temario_archivo);
            }
            $updateData['temario_archivo'] = $request->file('temario_archivo')->store('temarios', 'public');
        }

        $materia->update($updateData);

        return redirect()->route('MiInformacion.clases')->with('success', 'Contenido de la materia actualizado.');
    }

    /**
     * Genera y descarga el temario (contenido) de la materia. Si el docente
     * subió un archivo, se descarga tal cual; si no, se genera un PDF a
     * partir del temario en texto (lista de temas).
     */
    public function descargarTemario(HorarioClase $clase)
    {
        $user = Auth::user();
        $user->load('academicProfile');
        $clase->load('materia');

        abort_unless($this->tieneAccesoAClase($user, $clase), 403);

        $materia = $clase->materia;

        if ($materia->temario_archivo && Storage::disk('public')->exists($materia->temario_archivo)) {
            return Storage::disk('public')->download($materia->temario_archivo);
        }

        $temario = is_array($materia->temario) ? $materia->temario : [];

        $pdf = Pdf::loadView('layouts.MiInformacion.temario_pdf', compact('materia', 'temario'));
        $pdf->setPaper('a4', 'portrait');

        $filename = 'Temario_' . str_replace(' ', '_', $materia->nombre ?? 'Materia') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Lista de alumnos de la clase con su estado de asistencia del día.
     */
    public function showAsistencia(HorarioClase $clase)
    {
        $user = Auth::user();
        $clase->load('materia');
        abort_unless($user->hasActiveRole('docente') && $clase->user_id === $user->id, 403);

        $alumnos = $this->alumnosDeClase($clase)->load('academicProfile.career');
        $hoy = now()->toDateString();

        $asistencias = ClaseAsistencia::where('horario_clase_id', $clase->id)
            ->where('fecha', $hoy)
            ->get()
            ->keyBy('user_id');

        return view('layouts.MiInformacion.asistencia', compact('user', 'clase', 'alumnos', 'asistencias', 'hoy'));
    }

    /**
     * Guarda la asistencia del día para todos los alumnos de la clase.
     */
    public function guardarAsistencia(Request $request, HorarioClase $clase)
    {
        $user = Auth::user();
        abort_unless($user->hasActiveRole('docente') && $clase->user_id === $user->id, 403);

        $presentes = collect($request->input('presentes', []))->map(fn ($id) => (int) $id)->all();
        $hoy = now()->toDateString();

        foreach ($this->alumnosDeClase($clase) as $alumno) {
            ClaseAsistencia::updateOrCreate(
                ['horario_clase_id' => $clase->id, 'user_id' => $alumno->id, 'fecha' => $hoy],
                ['presente' => in_array($alumno->id, $presentes, true)]
            );
        }

        return redirect()->route('MiInformacion.clases.asistencia', $clase)->with('success', 'Asistencia guardada correctamente.');
    }

    /**
     * Exporta la lista de alumnos y su asistencia del día en CSV.
     */
    public function exportAsistencia(HorarioClase $clase)
    {
        $user = Auth::user();
        $clase->load('materia');
        abort_unless($user->hasActiveRole('docente') && $clase->user_id === $user->id, 403);

        $alumnos = $this->alumnosDeClase($clase)->load('academicProfile.career');
        $hoy = now()->toDateString();
        $asistencias = ClaseAsistencia::where('horario_clase_id', $clase->id)->where('fecha', $hoy)->get()->keyBy('user_id');

        $filename = 'Asistencia_' . str_replace(' ', '_', $clase->materia->nombre ?? 'Materia') . '_' . $hoy . '.csv';

        $callback = function () use ($alumnos, $asistencias) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Carrera', 'Nombre', 'Apellido Paterno', 'Apellido Materno', 'Semestre', 'Presente']);
            foreach ($alumnos as $alumno) {
                $asistencia = $asistencias->get($alumno->id);
                fputcsv($handle, [
                    $alumno->academicProfile?->career?->name ?? '—',
                    $alumno->nombre,
                    $alumno->apellido_paterno,
                    $alumno->apellido_materno,
                    $alumno->academicProfile?->semestre ?? '—',
                    $asistencia && $asistencia->presente ? 'Sí' : 'No',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}