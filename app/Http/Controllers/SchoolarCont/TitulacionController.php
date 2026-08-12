<?php

namespace App\Http\Controllers\SchoolarCont;

use App\Http\Controllers\Controller;
use App\Models\Schoolar\TitulacionDocumento;
use App\Models\DocumentRequirement;
use App\Models\SubmittedDocument;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TitulacionController extends Controller
{
    public function index(Request $request)
    {
        $query = TitulacionDocumento::query()
            ->with(['alumno:id,nombre,apellido_paterno,apellido_materno,email', 'subidoPor:id,nombre,apellido_paterno'])
            ->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nombre_documento', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%")
                    ->orWhereRaw("DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("DATE_FORMAT(created_at, '%d/%m/%Y') LIKE ?", ["%{$search}%"])
                    ->orWhereHas('alumno', function ($aq) use ($search) {
                        $aq->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido_paterno', 'like', "%{$search}%")
                            ->orWhere('apellido_materno', 'like', "%{$search}%");
                    });
            });
        }

        $dataList = $query->paginate(15)->appends($request->query());
        $alumnos = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'estudiante'))
            ->orderBy('nombre')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno']);

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'tbody' => view('layouts.ControlEsc.Titulacion.partials.table_rows', ['dataList' => $dataList])->render(),
                'pagination' => (string) $dataList->appends($request->query())->links(),
            ]);
        }

        // Documentos requeridos configurados en Ajustes → Expediente (proceso Titulación).
        $expedienteConfig = DocumentRequirement::query()->forActiveProcess('titulacion')->get();

        // Docs subidos por alumnos desde su expediente (proceso Titulación).
        $submittedDocs = SubmittedDocument::query()
            ->with([
                'user:id,nombre,apellido_paterno,apellido_materno',
                'requirement:id,nombre',
            ])
            ->whereIn('document_requirement_id', $expedienteConfig->pluck('id'))
            ->latest()
            ->get();

        return view('layouts.ControlEsc.Titulacion.index', compact('dataList', 'alumnos', 'expedienteConfig', 'submittedDocs'));
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
        $path = $archivo->store("documentos/{$validated['user_id']}/titulacion", 'public');

        TitulacionDocumento::create([
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
                'message' => 'Documento de titulación agregado correctamente.',
            ]);
        }

        return back()->with('success', 'Documento de titulación agregado correctamente.');
    }

    public function show(int $id)
    {
        $data = TitulacionDocumento::with(['alumno:id,nombre,apellido_paterno,apellido_materno,email'])->findOrFail($id);
        return response()->json(['data' => $data]);
    }

    public function update(Request $request, int $id)
    {
        $item = TitulacionDocumento::findOrFail($id);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'nombre_documento' => ['required', 'string', 'max:180'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $this->ensureStudent((int) $validated['user_id']);

        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $nuevoPath = $archivo->store("documentos/{$validated['user_id']}/titulacion", 'public');
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
                'message' => 'Documento de titulación actualizado correctamente.',
            ]);
        }

        return back()->with('success', 'Documento de titulación actualizado correctamente.');
    }

    public function destroy(int $id)
    {
        $item = TitulacionDocumento::findOrFail($id);
        if (! empty($item->archivo_path)) {
            Storage::disk('public')->delete($item->archivo_path);
        }
        $item->delete();

        return back()->with('success', 'Documento de titulación eliminado correctamente.');
    }

    public function download(int $id)
    {
        $item = TitulacionDocumento::findOrFail($id);
        abort_unless(Storage::disk('public')->exists($item->archivo_path), 404, 'Archivo no encontrado.');
        return Storage::disk('public')->download($item->archivo_path, basename($item->archivo_path));
    }

    private function ensureStudent(int $userId): void
    {
        $ok = User::query()
            ->where('id', $userId)
            ->whereHas('roles', fn ($q) => $q->where('roles.id', 7))
            ->exists();

        abort_unless($ok, 422, 'El usuario indicado no corresponde a un alumno.');
    }
}
