<?php

namespace App\Http\Controllers\SchoolarCont;

use App\Http\Controllers\Controller;
use App\Models\Schoolar\Beca;
use App\Models\Schoolar\BecaAsignacion;
use App\Models\Schoolar\BecaDocumento;
use App\Models\DocumentRequirement;
use App\Models\SubmittedDocument;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BecasController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->filled('search') ? trim((string) $request->string('search')) : null;
        $institutionId = (int) session('active_institution_id', 4);

        // Documentos requeridos configurados en Ajustes → Expediente (proceso Becas).
        $expedienteConfig = DocumentRequirement::query()->forActiveProcess('becas')->get();

        // Asignaciones de beca activas para esta institución.
        $becaAsignaciones = BecaAsignacion::query()
            ->where('institution_id', $institutionId)
            ->with(['alumno:id,nombre,apellido_paterno,apellido_materno', 'beca:id,nombre,documentos_requeridos'])
            ->latest()
            ->get()
            ->keyBy('user_id');

        // Todos los alumnos (para el select del modal "Agregar alumno").
        $todosAlumnos = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'estudiante'))
            ->orderBy('nombre')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno']);

        // Alumnos con beca asignada, filtrados por búsqueda de nombre (para la tabla).
        $alumnosQuery = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'estudiante'))
            ->whereIn('id', $becaAsignaciones->keys())
            ->orderBy('nombre')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno');

        if ($search) {
            $alumnosQuery->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$search}%")
                  ->orWhere('apellido_materno', 'like', "%{$search}%");
            });
        }

        $alumnos = $alumnosQuery->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno']);

        // Docs subidos por alumnos desde su expediente (proceso Becas).
        $submittedDocs = SubmittedDocument::query()
            ->with([
                'user:id,nombre,apellido_paterno,apellido_materno',
                'requirement:id,nombre',
            ])
            ->whereIn('document_requirement_id', $expedienteConfig->pluck('id'))
            ->latest()
            ->get();

        $becasCatalogo = Beca::where('activo', true)
            ->where('institution_id', $institutionId)
            ->orderBy('nombre')
            ->get();

        $reqNombres = $expedienteConfig->pluck('nombre', 'id');

        // Agrupar docs por alumno filtrando solo los que requiere su beca asignada.
        $docsPorAlumnoJs = $submittedDocs->groupBy('user_id')->map(function ($docs, $userId) use ($reqNombres, $becaAsignaciones) {
            $asignacion = $becaAsignaciones->get($userId);
            $becaReqIds = array_map('intval', $asignacion?->beca?->documentos_requeridos ?? []);

            return $docs
                ->when(!empty($becaReqIds), fn($c) => $c->filter(fn($d) => in_array((int) $d->document_requirement_id, $becaReqIds)))
                ->map(fn($d) => [
                    'id'                => $d->id,
                    'nombre'            => $reqNombres[$d->document_requirement_id] ?? 'Documento',
                    'archivo_path'      => $d->archivo_path,
                    'nombre_original'   => $d->nombre_original,
                    'validation_status' => $d->validation_status ?? 'en_revision',
                ])->values();
        });

        $becasMap = $becasCatalogo->keyBy('id')->map(fn($b) => [
            'id'                    => $b->id,
            'nombre'                => $b->nombre,
            'tipo'                  => $b->tipo ?? '',
            'especificaciones'      => $b->especificaciones ?? '',
            'documentos_requeridos' => $b->documentos_requeridos ?? [],
        ]);

        return view('layouts.ControlEsc.Becas.index', compact(
            'alumnos', 'todosAlumnos', 'expedienteConfig', 'submittedDocs',
            'becaAsignaciones', 'becasCatalogo', 'becasMap', 'docsPorAlumnoJs'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'nombre_documento' => ['required', 'string', 'max:180'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $this->ensureStudent((int) $validated['user_id']);

        $archivo = $request->file('archivo');
        $path = $archivo->store("documentos/{$validated['user_id']}/becas", 'public');

        BecaDocumento::create([
            'user_id' => $validated['user_id'],
            'nombre_documento' => $validated['nombre_documento'],
            'descripcion' => $validated['descripcion'] ?? null,
            'archivo_path' => $path,
            'mime_type' => $archivo->getMimeType(),
            'tamano_bytes' => $archivo->getSize(),
            'uploaded_by' => Auth::id(),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'Documento de beca agregado correctamente.',
            ]);
        }

        return back()->with('success', 'Documento de beca agregado correctamente.');
    }

    public function show(int $id)
    {
        $data = BecaDocumento::with(['alumno:id,nombre,apellido_paterno,apellido_materno,email'])->findOrFail($id);
        return response()->json(['data' => $data]);
    }

    public function update(Request $request, int $id)
    {
        $item = BecaDocumento::findOrFail($id);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'nombre_documento' => ['required', 'string', 'max:180'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $this->ensureStudent((int) $validated['user_id']);

        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $nuevoPath = $archivo->store("documentos/{$validated['user_id']}/becas", 'public');
            if (! empty($item->archivo_path) && $item->archivo_path !== $nuevoPath) {
                Storage::disk('public')->delete($item->archivo_path);
            }
            $validated['archivo_path'] = $nuevoPath;
            $validated['mime_type'] = $archivo->getMimeType();
            $validated['tamano_bytes'] = $archivo->getSize();
            $validated['uploaded_by'] = Auth::id();
        }

        $item->update($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'Documento de beca actualizado correctamente.',
            ]);
        }

        return back()->with('success', 'Documento de beca actualizado correctamente.');
    }

    public function destroy(int $id)
    {
        $item = BecaDocumento::findOrFail($id);
        if (! empty($item->archivo_path)) {
            Storage::disk('public')->delete($item->archivo_path);
        }
        $item->delete();

        return back()->with('success', 'Documento de beca eliminado correctamente.');
    }

    public function download(int $id)
    {
        $item = BecaDocumento::findOrFail($id);
        abort_unless(Storage::disk('public')->exists($item->archivo_path), 404, 'Archivo no encontrado.');
        return Storage::disk('public')->download($item->archivo_path, basename($item->archivo_path));
    }

    public function toggleBeca(Request $request)
    {
        $validated = $request->validate([
            'user_id'               => ['required', 'integer', Rule::exists('users', 'id')],
            'beca_id'               => ['nullable', 'integer', Rule::exists('becas', 'id')],
            'status'                => ['required', 'in:activa,inactiva,pendiente'],
            'documentos_requeridos' => ['nullable', 'array'],
            'documentos_requeridos.*' => ['integer'],
        ]);

        $this->ensureStudent((int) $validated['user_id']);

        $institutionId = (int) session('active_institution_id', 4);

        $updateData = [
            'status' => $validated['status'],
            'assigned_by' => Auth::id(),
            'activated_at' => $validated['status'] === 'activa' ? now() : null,
        ];
        if (!empty($validated['beca_id'])) {
            $updateData['beca_id'] = $validated['beca_id'];
        }

        $asignacion = BecaAsignacion::updateOrCreate(
            ['user_id' => $validated['user_id'], 'institution_id' => $institutionId],
            $updateData
        );

        // Guardar docs seleccionados en la asignación (no en el catálogo global)
        if ($request->has('documentos_requeridos')) {
            $docs = array_map('intval', (array) $request->input('documentos_requeridos', []));
            $asignacion->documentos_requeridos = $docs;
            $asignacion->save();
        }

        if ($validated['status'] === 'pendiente') {
            $reqIds = \App\Models\DocumentRequirement::query()
                ->forActiveProcess('becas')
                ->pluck('id');

            \App\Models\SubmittedDocument::query()
                ->where('user_id', $validated['user_id'])
                ->whereIn('document_requirement_id', $reqIds)
                ->update(['validation_status' => null]);
        }

        return response()->json([
            'ok' => true,
            'message' => $validated['status'] === 'activa'
                ? 'Beca activada correctamente.'
                : ($validated['status'] === 'inactiva' ? 'Beca desactivada.' : 'Beca en pendiente.'),
            'status' => $asignacion->status,
        ]);
    }

    public function destroyAsignacion(int $userId)
    {
        $institutionId = (int) session('active_institution_id', 4);

        BecaAsignacion::where('user_id', $userId)
            ->where('institution_id', $institutionId)
            ->delete();

        return response()->json(['ok' => true, 'message' => 'Asignación eliminada.']);
    }

    private function ensureStudent(int $userId): void
    {
        $ok = User::query()
            ->where('id', $userId)
            ->whereHas('roles', fn ($q) => $q->where('roles.id', 9))
            ->exists();

        abort_unless($ok, 422, 'El usuario indicado no corresponde a un alumno.');
    }

    // ── CRUD Catálogo de Becas ──

    public function storeBeca(Request $request)
    {
        $validated = $request->validate([
            'nombre'               => ['required', 'string', 'max:200'],
            'tipo'                 => ['nullable', 'string', 'max:100'],
            'especificaciones'     => ['nullable', 'string'],
            'documentos_requeridos'=> ['nullable', 'array'],
            'documentos_requeridos.*' => ['integer'],
        ]);

        $institutionId = (int) session('active_institution_id', 4);

        Beca::create(array_merge($validated, [
            'institution_id' => $institutionId,
            'documentos_requeridos' => $validated['documentos_requeridos'] ?? [],
        ]));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => 'Beca creada correctamente.']);
        }
        return back()->with('success', 'Beca creada correctamente.');
    }

    public function updateBeca(Request $request, int $id)
    {
        $beca = Beca::findOrFail($id);

        $validated = $request->validate([
            'nombre'               => ['required', 'string', 'max:200'],
            'tipo'                 => ['nullable', 'string', 'max:100'],
            'especificaciones'     => ['nullable', 'string'],
            'documentos_requeridos'=> ['nullable', 'array'],
            'documentos_requeridos.*' => ['integer'],
        ]);

        $beca->update(array_merge($validated, [
            'documentos_requeridos' => $validated['documentos_requeridos'] ?? [],
        ]));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => 'Beca actualizada.']);
        }
        return back()->with('success', 'Beca actualizada.');
    }

    public function destroyBeca(Request $request, int $id)
    {
        Beca::findOrFail($id)->update(['activo' => false]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => 'Beca eliminada.']);
        }
        return back()->with('success', 'Beca eliminada.');
    }

    public function getBecaDocumentos(int $id)
    {
        $beca = Beca::findOrFail($id);
        return response()->json([
            'documentos_requeridos' => $beca->documentos_requeridos ?? [],
            'especificaciones'      => $beca->especificaciones,
            'tipo'                  => $beca->tipo,
        ]);
    }
}
