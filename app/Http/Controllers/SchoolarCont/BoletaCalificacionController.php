<?php

namespace App\Http\Controllers\SchoolarCont;

use App\Http\Controllers\Controller;
use App\Models\AdmonCont\Materia;
use App\Models\Users\Career;
use App\Models\Users\Period;
use App\Models\Users\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BoletaCalificacionController extends Controller
{
    /** Marcador visual cuando aún no hay captura de calificaciones. */
    private const PLACEHOLDER = '----';

    /**
     * Lista alumnos de la institución (lista tipo matrículas) con columnas de calificación en espera.
     */
    public function index(Request $request): View
    {
        $institutionId = (int) session('active_institution_id', 0);
        $careerIds = Career::query()
            ->where('institution_id', $institutionId)
            ->pluck('id');

        $search = trim((string) $request->get('search', ''));
        $materiaId = $request->filled('materia_id') ? (int) $request->get('materia_id') : null;
        $periodoId = $request->filled('periodo_id') ? (int) $request->get('periodo_id') : null;

        $materias = Materia::query()
            ->whereIn('career_id', $careerIds)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'clave']);

        $periodos = Period::query()
            ->where('institution_id', $institutionId)
            ->orderByDesc('start_date')
            ->get(['id', 'name', 'start_date', 'end_date']);

        $materiaNombre = $materiaId ? ($materias->firstWhere('id', $materiaId)?->nombre) : null;

        $estudiantes = $this->queryEstudiantesParaBoletas($request, $careerIds);
        $rows = $this->mapEstudiantesABoletaRows($estudiantes, $materiaNombre);

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

        return view('layouts.ControlEsc.Boletas.index', [
            'rows' => $paginator,
            'search' => $search,
            'materias' => $materias,
            'periodos' => $periodos,
            'materiaId' => $materiaId,
            'periodoId' => $periodoId,
            'periodoSeleccionado' => $periodoSeleccionado,
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
        $materiaNombre = $materiaId ? ($materias->firstWhere('id', $materiaId)?->nombre) : null;

        $estudiantes = $this->queryEstudiantesParaBoletas($request, $careerIds);
        $rows = $this->mapEstudiantesABoletaRows($estudiantes, $materiaNombre);

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
            fputcsv($handle, [
                'Alumno',
                'RFC',
                'CURP',
                'Carrera',
                'Materia',
                'Docente',
                'Parciales',
                'Calificación final',
                'Evaluación',
                'Observaciones',
                'Periodo (filtro)',
            ], ';');
            foreach ($rows as $r) {
                fputcsv($handle, [
                    $r->alumno_nombre,
                    $r->alumno_rfc,
                    $r->alumno_curp,
                    $r->carrera,
                    $r->materia,
                    $r->docente,
                    $r->parciales,
                    $r->final,
                    $r->evaluacion,
                    $r->observaciones,
                    $periodoLabel,
                ], ';');
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
            ->with(['academicProfile.career'])
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
        $materiaEtiqueta = $materiaNombreFiltro ?? '—';

        return $estudiantes->map(function (User $u) use ($materiaEtiqueta, $materiaNombreFiltro) {
            $nombre = trim(
                $u->nombre.' '.$u->apellido_paterno.' '.($u->apellido_materno ?? '')
            );

            return (object) [
                'alumno_nombre' => $nombre !== '' ? $nombre : '—',
                'alumno_rfc' => $u->RFC ?? '',
                'alumno_curp' => $u->curp ?? '',
                'carrera' => $u->academicProfile?->career?->name ?? '—',
                'materia' => $materiaNombreFiltro ? $materiaEtiqueta : '—',
                'docente' => self::PLACEHOLDER,
                'parciales' => self::PLACEHOLDER,
                'final' => self::PLACEHOLDER,
                'evaluacion' => self::PLACEHOLDER,
                'observaciones' => self::PLACEHOLDER,
            ];
        });
    }
}
