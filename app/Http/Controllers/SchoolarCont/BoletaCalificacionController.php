<?php

namespace App\Http\Controllers\SchoolarCont;

use App\Http\Controllers\Controller;
use App\Models\AdmonCont\Materia;
use App\Models\Users\Career;
use App\Models\Users\Period;
use App\Models\Users\User;
use App\Models\Users\CareerClassification;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BoletaCalificacionController extends Controller
{
    /** Marcador visual cuando aún no hay captura de calificaciones. */
    private const PLACEHOLDER = '----';
    private const PARCIALES_PLACEHOLDER = "----  ----  ----";

    /**
     * Lista alumnos de la institución (lista tipo matrículas) con columnas de calificación en espera.
     */
    public function index(Request $request)
    {
        $institutionId = (int) session('active_institution_id', 0);
        $clasificacionId = $request->filled('clasificacion_id') ? (int) $request->get('clasificacion_id') : null;
        $carreraId = $request->filled('carrera_id') ? (int) $request->get('carrera_id') : null;
        $materiaId = $request->filled('materia_id') ? (int) $request->get('materia_id') : null;
        $periodoId = $request->filled('periodo_id') ? (int) $request->get('periodo_id') : null;
        $search = trim((string) $request->get('search', ''));

        $clasificaciones = CareerClassification::query()
            ->where('institution_id', $institutionId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $careerIds = Career::query()
            ->where('institution_id', $institutionId)
            ->when($clasificacionId, fn($q) => $q->where('career_classification_id', $clasificacionId))
            ->when($carreraId, fn($q) => $q->where('id', $carreraId))
            ->pluck('id');

        $materias = Materia::query()
            ->whereIn('career_id', $careerIds)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'clave', 'num_parciales']);

        $materiaSeleccionada = $materiaId ? $materias->firstWhere('id', $materiaId) : null;

        $periodos = Period::query()
            ->where('institution_id', $institutionId)
            ->orderByDesc('start_date')
            ->get(['id', 'name', 'start_date', 'end_date']);

        $materiaNombre = $materiaId ? ($materias->firstWhere('id', $materiaId)?->nombre) : null;

        $estudiantes = $this->queryEstudiantesParaBoletas($request, $careerIds);
        $rows = $this->mapEstudiantesABoletaRows($estudiantes, $materiaNombre);
        $rows = $this->filterRowsBySearch($rows, $search);

        $page = max(1, (int) $request->get('page', 1));
        $perPage = 30;
        $total = $rows->count();
        $slice = $rows->forPage($page, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $periodoSeleccionado = $periodoId ? $periodos->firstWhere('id', $periodoId) : null;

        $numParciales = $materiaSeleccionada ? $materiaSeleccionada->num_parciales : 3;

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'tbody' => view('layouts.ControlEsc.Boletas.partials.table_rows', ['rows' => $paginator, 'numParciales' => $numParciales])->render(),
                'pagination' => $paginator->hasPages() ? (string) $paginator->withQueryString()->links() : '',
                'numParciales' => $numParciales,
                'materiaNombre' => $materiaSeleccionada->nombre ?? null,
            ]);
        }

        return view('layouts.ControlEsc.Boletas.index', [
            'rows' => $paginator,
            'search' => $search,
            'clasificaciones' => $clasificaciones,
            'materias' => $materias,
            'periodos' => $periodos,
            'clasificacionId' => $clasificacionId,
            'carreraId' => $carreraId,
            'materiaId' => $materiaId,
            'periodoId' => $periodoId,
            'periodoSeleccionado' => $periodoSeleccionado,
            'materiaSeleccionada' => $materiaSeleccionada,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $institutionId = (int) session('active_institution_id', 0);
        $careerIds = Career::query()
            ->where('institution_id', $institutionId)
            ->pluck('id');

        $materias = Materia::query()
            ->whereIn('career_id', $careerIds)
            ->get(['id', 'nombre']);

        $materiaId = $request->filled('materia_id') ? (int) $request->get('materia_id') : null;
        $search = trim((string) $request->get('search', ''));
        $materiaNombre = $materiaId ? ($materias->firstWhere('id', $materiaId)?->nombre) : null;

        $estudiantes = $this->queryEstudiantesParaBoletas($request, $careerIds);
        $rows = $this->mapEstudiantesABoletaRows($estudiantes, $materiaNombre);
        $rows = $this->filterRowsBySearch($rows, $search);

        $periodoLabel = '';
        if ($request->filled('periodo_id')) {
            $periodoLabel = (string) (Period::query()
                ->where('institution_id', $institutionId)
                ->whereKey((int) $request->get('periodo_id'))
                ->value('name') ?? '');
        }

        $filename = 'Boletas_calificaciones_'.date('Y-m-d_H-i').'.csv';

        return new StreamedResponse(function () use ($rows, $periodoLabel): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fwrite($handle, "sep=,\r\n");
            fputcsv($handle, [
                'Alumno',
                'CURP',
                'Carrera',
                'Materia',
                'Docente',
                'Parciales',
                'Calificación final',
                'Periodo (filtro)',
            ], ',');
            foreach ($rows as $r) {
                fputcsv($handle, [
                    $r->alumno_nombre,
                    $r->alumno_curp,
                    $r->carrera,
                    $r->materia,
                    $r->docente,
                    $r->parciales,
                    $r->final,
                    $periodoLabel,
                ], ',');
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Estudiantes con rol alumno y perfil en carreras de la institución; opcionalmente inscritos en la materia (horario).
     *
     * @param  Collection<int, int>  $careerIds
     */
    private function queryEstudiantesParaBoletas(Request $request, Collection $careerIds): EloquentCollection
    {
        if ($careerIds->isEmpty()) {
            return new EloquentCollection();
        }

        $search = trim((string) $request->get('search', ''));
        $materiaId = $request->filled('materia_id') ? (int) $request->get('materia_id') : null;

        return User::query()
            ->whereHas('roles', static fn ($q) => $q->where('name', 'estudiante'))
            ->whereHas('academicProfile', static function ($q) use ($careerIds) {
                $q->whereIn('career_id', $careerIds);
            })
            ->with(['academicProfile.career', 'horarioClases.materia'])
            ->when($materiaId, static function ($q) use ($materiaId, $careerIds) {
                $q->whereHas('horarioClases', static function ($hc) use ($materiaId, $careerIds) {
                    $hc->where('materia_id', $materiaId)
                        ->whereIn('career_id', $careerIds);
                });
            })
            ->when($search !== '', static function ($q) use ($search): void {
                $term = '%'.$search.'%';
                $q->where(static function ($w) use ($term): void {
                    $w->where('nombre', 'like', $term)
                        ->orWhere('apellido_paterno', 'like', $term)
                        ->orWhere('apellido_materno', 'like', $term)
                        ->orWhere('RFC', 'like', $term)
                        ->orWhere('curp', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    private function mapEstudiantesABoletaRows(EloquentCollection $estudiantes, ?string $materiaNombreFiltro): Collection
    {
        $materiaEtiqueta = $materiaNombreFiltro ?? '-';

        return $estudiantes->map(function (User $u) use ($materiaEtiqueta, $materiaNombreFiltro) {
            $nombre = trim(
                $u->nombre.' '.$u->apellido_paterno.' '.($u->apellido_materno ?? '')
            );
            $materiasAlumno = $u->horarioClases
                ->pluck('materia.nombre')
                ->filter()
                ->unique()
                ->values();
            $materiaTexto = $materiaNombreFiltro
                ? $materiaEtiqueta
                : ($materiasAlumno->isNotEmpty() ? $materiasAlumno->implode(' / ') : '-');

            return (object) [
                'alumno_nombre' => $nombre !== '' ? $nombre : '—',
                'alumno_rfc' => $u->RFC ?? '',
                'alumno_curp' => $u->curp ?? '',
                'carrera' => $u->academicProfile?->career?->name ?? '—',
                'materia' => $materiaTexto,
                'docente' => self::PLACEHOLDER,
                'parciales' => self::PARCIALES_PLACEHOLDER,
                'parciales_arr' => [],
                'final' => self::PLACEHOLDER,
                'evaluacion' => self::PLACEHOLDER,
                'observaciones' => self::PLACEHOLDER,
            ];
        });
    }

    /**
     * Filtra por campos visibles de la boleta: alumno, final, evaluacion y observaciones.
     *
     * @param  Collection<int, object>  $rows
     * @return Collection<int, object>
     */
    private function filterRowsBySearch(Collection $rows, string $search): Collection
    {
        $search = trim(mb_strtolower($search));
        if ($search === '') {
            return $rows;
        }

        return $rows->filter(static function ($row) use ($search): bool {
            $haystack = mb_strtolower(implode(' ', [
                (string) ($row->alumno_nombre ?? ''),
                (string) ($row->final ?? ''),
                (string) ($row->evaluacion ?? ''),
                (string) ($row->observaciones ?? ''),
            ]));

            return str_contains($haystack, $search);
        })->values();
    }

    /**
     * AJAX: devuelve carreras filtradas por clasificación.
     */
    public function carrerasPorClasificacion(Request $request)
    {
        $institutionId = (int) session('active_institution_id', 0);
        $clasificacionId = $request->filled('clasificacion_id') ? (int) $request->get('clasificacion_id') : null;

        $carreras = Career::query()
            ->where('institution_id', $institutionId)
            ->when($clasificacionId, fn($q) => $q->where('career_classification_id', $clasificacionId))
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($carreras);
    }

    /**
     * AJAX: devuelve materias filtradas por carrera.
     */
    public function materiasPorCarrera(Request $request)
    {
        $carreraId = $request->filled('carrera_id') ? (int) $request->get('carrera_id') : null;

        if (!$carreraId) {
            return response()->json([]);
        }

        $materias = Materia::query()
            ->where('career_id', $carreraId)
            ->orderBy('semestre')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'clave', 'semestre', 'num_parciales']);

        return response()->json($materias);
    }
}
