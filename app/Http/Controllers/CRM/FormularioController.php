<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\FormularioCampo;
use App\Models\PublicFormConfig;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FormularioController extends Controller
{
    public function index()
    {
        $campos = FormularioCampo::orderBy('orden')->orderBy('id')->get();
        $registros = Lead::whereNotNull('datos_adicionales')
            ->where('datos_adicionales', '!=', 'null')
            ->orderByDesc('id')
            ->get();
        $camposBase = PublicFormConfig::orderBy('seccion')->orderBy('orden')->get()->groupBy('seccion');
        return view('layouts.CRM.Formulario.index', compact('campos', 'registros', 'camposBase'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'etiqueta'     => ['required', 'string', 'max:200'],
            'nombre_campo' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/', Rule::unique('formulario_campos')],
            'tipo'         => ['required', 'in:text,email,tel,date,select,file,textarea,checkbox'],
            'opciones'     => ['nullable', 'string'],
            'placeholder'  => ['nullable', 'string', 'max:200'],
            'obligatorio'  => ['nullable', 'boolean'],
            'activo'       => ['nullable', 'boolean'],
            'orden'        => ['nullable', 'integer', 'min:0'],
            'seccion'      => ['nullable', 'in:tutor,postulante'],
            'despues_de'   => ['nullable', 'string', 'max:100'],
        ]);

        $validated['obligatorio'] = $request->boolean('obligatorio');
        $validated['activo']      = $request->boolean('activo', true);
        $validated['orden']       = (FormularioCampo::max('orden') ?? 0) + 1;
        $validated['seccion']     = $request->input('seccion') ?: null;
        $validated['despues_de']  = $request->input('despues_de') ?: null;

        FormularioCampo::create($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => 'Campo agregado correctamente.']);
        }
        return back()->with('success', 'Campo agregado correctamente.');
    }

    public function show(int $id)
    {
        return response()->json(['data' => FormularioCampo::findOrFail($id)]);
    }

    public function update(Request $request, int $id)
    {
        $campo = FormularioCampo::findOrFail($id);

        $validated = $request->validate([
            'etiqueta'     => ['required', 'string', 'max:200'],
            'nombre_campo' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/', Rule::unique('formulario_campos')->ignore($id)],
            'tipo'         => ['required', 'in:text,email,tel,date,select,file,textarea,checkbox'],
            'opciones'     => ['nullable', 'string'],
            'placeholder'  => ['nullable', 'string', 'max:200'],
            'obligatorio'  => ['nullable', 'boolean'],
            'activo'       => ['nullable', 'boolean'],
            'orden'        => ['nullable', 'integer', 'min:0'],
            'seccion'      => ['nullable', 'in:tutor,postulante'],
            'despues_de'   => ['nullable', 'string', 'max:100'],
        ]);

        $validated['obligatorio'] = $request->boolean('obligatorio');
        $validated['activo']      = $request->boolean('activo', true);
        $validated['orden']       = $validated['orden'] ?? 0;
        $validated['seccion']     = $request->input('seccion') ?: null;
        $validated['despues_de']  = $request->input('despues_de') ?: null;

        $campo->update($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => 'Campo actualizado correctamente.']);
        }
        return back()->with('success', 'Campo actualizado correctamente.');
    }

    public function toggle(Request $request, int $id)
    {
        $campo = FormularioCampo::findOrFail($id);
        $field = $request->input('field');
        if (!in_array($field, ['activo', 'obligatorio'])) {
            return response()->json(['ok' => false, 'message' => 'Campo no permitido.'], 422);
        }
        $campo->$field = !$campo->$field;
        $campo->save();
        return response()->json(['ok' => true, 'value' => (bool) $campo->$field]);
    }

    public function reorder(Request $request, int $id)
    {
        $campo = FormularioCampo::findOrFail($id);
        $direction = $request->input('direction');

        $campos = FormularioCampo::orderBy('orden')->orderBy('id')->get();

        // Normalizar orden para garantizar valores únicos y consecutivos
        foreach ($campos as $i => $c) {
            $c->orden = $i + 1;
            $c->save();
        }

        $campos = FormularioCampo::orderBy('orden')->get();
        $campo->refresh();

        $idx = $campos->search(fn ($c) => $c->id === $campo->id);
        $swapIdx = $direction === 'up' ? $idx - 1 : $idx + 1;

        if ($swapIdx < 0 || $swapIdx >= $campos->count()) {
            return response()->json(['ok' => false]);
        }

        $swap = $campos->get($swapIdx);
        [$campo->orden, $swap->orden] = [$swap->orden, $campo->orden];
        $campo->save();
        $swap->save();

        return response()->json(['ok' => true]);
    }

    public function destroy(int $id)
    {
        FormularioCampo::findOrFail($id)->delete();
        return back()->with('success', 'Campo eliminado correctamente.');
    }

    public function updateBase(Request $request, string $campo)
    {
        $config = PublicFormConfig::where('campo', $campo)->firstOrFail();
        $config->etiqueta = $request->validate(['etiqueta' => 'required|string|max:120'])['etiqueta'];
        $config->save();
        return response()->json(['ok' => true, 'etiqueta' => $config->etiqueta]);
    }

    public function toggleBase(Request $request, string $campo)
    {
        $config = PublicFormConfig::where('campo', $campo)->firstOrFail();

        if ($config->siempre_activo) {
            return response()->json(['ok' => false, 'message' => 'Este campo no puede desactivarse.'], 422);
        }

        $field = $request->input('field', 'activo');
        if (!in_array($field, ['activo', 'obligatorio'])) {
            return response()->json(['ok' => false], 422);
        }

        $config->$field = !$config->$field;
        $config->save();

        return response()->json(['ok' => true, 'value' => (bool) $config->$field]);
    }
}
