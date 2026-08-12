<?php

namespace App\Http\Controllers\SchoolarCont;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequirement;
use App\Models\Schoolar\ModuloAsignacion;
use App\Models\SubmittedDocument;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ModuloExpedienteController extends Controller
{
    private const MODULOS = [
        'titulacion' => [
            'titulo' => 'Titulación',
            'proceso' => 'titulacion',
            'vista' => 'layouts.ControlEsc.Modulos.index',
            'css' => 'resources/css/ControlEsc/becas.css',
        ],
        'servicio_social' => [
            'titulo' => 'Servicio Social',
            'proceso' => 'servicio_social',
            'vista' => 'layouts.ControlEsc.Modulos.index',
            'css' => 'resources/css/ControlEsc/becas.css',
        ],
        'practicas_profesionales' => [
            'titulo' => 'Prácticas Profesionales',
            'proceso' => 'practicas_profesionales',
            'vista' => 'layouts.ControlEsc.Modulos.index',
            'css' => 'resources/css/ControlEsc/becas.css',
        ],
    ];

    public function index(Request $request, string $modulo)
    {
        $cfg = self::MODULOS[$modulo] ?? abort(404);

        $alumnos = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'estudiante'))
            ->orderBy('nombre')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno']);

        $expedienteConfig = DocumentRequirement::query()->forActiveProcess($cfg['proceso'])->get();

        $submittedDocs = SubmittedDocument::query()
            ->with([
                'user:id,nombre,apellido_paterno,apellido_materno',
                'requirement:id,nombre',
            ])
            ->whereIn('document_requirement_id', $expedienteConfig->pluck('id'))
            ->latest()
            ->get();

        $institutionId = (int) session('active_institution_id', 4);
        $asignaciones = ModuloAsignacion::query()
            ->where('institution_id', $institutionId)
            ->where('tipo', $modulo)
            ->latest()
            ->get()
            ->keyBy('user_id');

        return view($cfg['vista'], compact('alumnos', 'expedienteConfig', 'submittedDocs', 'asignaciones') + [
            'modulo' => $modulo,
            'titulo' => $cfg['titulo'],
            'cssFile' => $cfg['css'],
        ]);
    }

    public function toggleAsignacion(Request $request, string $modulo)
    {
        abort_unless(isset(self::MODULOS[$modulo]), 404);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'status' => ['required', 'in:activa,inactiva,pendiente'],
        ]);

        $this->ensureStudent((int) $validated['user_id']);
        $institutionId = (int) session('active_institution_id', 4);

        $asignacion = ModuloAsignacion::updateOrCreate(
            ['user_id' => $validated['user_id'], 'institution_id' => $institutionId, 'tipo' => $modulo],
            [
                'status' => $validated['status'],
                'assigned_by' => Auth::id(),
                'activated_at' => $validated['status'] === 'activa' ? now() : null,
            ]
        );

        $labels = ['activa' => 'activado', 'inactiva' => 'desactivado', 'pendiente' => 'en pendiente'];

        return response()->json([
            'ok' => true,
            'message' => self::MODULOS[$modulo]['titulo'] . ' ' . ($labels[$asignacion->status] ?? '') . ' correctamente.',
            'status' => $asignacion->status,
        ]);
    }

    private function ensureStudent(int $userId): void
    {
        $ok = User::query()
            ->where('id', $userId)
            ->whereHas('roles', fn ($q) => $q->where('name', 'estudiante'))
            ->exists();

        abort_unless($ok, 422, 'El usuario indicado no corresponde a un alumno.');
    }
}
