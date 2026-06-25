<?php

namespace App\Http\Controllers\AdmonCont;

use App\Http\Controllers\Controller;
use App\Models\Users\Career;
use App\Models\AdmonCont\Materia;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MateriaController extends Controller
{
    //
    public function index(Request $request): View
    {
        $listType = 'materias'; // Definido explícitamente para esta vista

        // 1. Columnas a seleccionar del modelo Materia
        $materiaColums = [
            'id',
            'nombre',
            'creditos',
            'type',
            'semestre',
            'descripcion',
            'objetivo',
            'temario',
            'infografia',
            'career_id',
            'career_classification_id', // ¡IMPORTANTE! Clave foránea para la relación
        ];

        $carreras = Career::all(['id', 'name']);
        $clasificaciones = \App\Models\Users\CareerClassification::all(['id', 'name']); // ← agregar
        
        // Listado ordenado por nombre de materia; carrera con clasificación
        $dataList = Materia::query()
            ->select($materiaColums)
            ->with([
                'career' => function (Relation $query) {
                    $query->select('id', 'name', 'career_classification_id')
                        ->with(['classification' => function ($q) {
                            $q->select('id', 'name');
                        }]);

                },
                'classification',
            ])
            ->orderBy('nombre')
            ->orderBy('id')
            ->get();

        $viewPath = 'layouts.ControlAdmin.Listas.' . $listType . '.index';

        return view($viewPath, [
    'dataList' => $dataList,
    'carreras' => $carreras,
    'clasificaciones' => $clasificaciones, 
]);
        
       
    }

    // MateriaController.php

    public function store(Request $request)
    {
        // 1. VALIDACIÓN DE DATOS (nombre único por carrera, no global)
        $validatedData = $request->validate([
            'carrera_id' => ['required', 'integer', 'exists:careers,id'],
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('materias', 'nombre')->where('career_id', $request->input('carrera_id')),
            ],
            'creditos' => ['required', 'integer', 'min:1'],
            'semestre' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'in:Presencial,En linea'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'objetivo' => ['nullable', 'string', 'max:3000'],
            'temario' => ['nullable', 'array'],
            'temario.*' => ['nullable', 'string', 'max:500'],
            'infografia' => ['nullable', 'string', 'max:10000'],
            'career_classification_id' => ['nullable', 'integer', 'exists:career_classifications,id'],
        ], [
            'carrera_id.required' => 'Te falta un campo por rellenar.',
            'carrera_id.integer' => 'Te falta un campo por rellenar.',
            'carrera_id.exists' => 'Te falta un campo por rellenar.',
            'nombre.required' => 'Te falta un campo por rellenar.',
            'nombre.max' => 'Te falta un campo por rellenar.',
            'nombre.unique' => 'Ya existe una materia con ese nombre. Elija otro.',
            'creditos.required' => 'Te falta un campo por rellenar.',
            'creditos.min' => 'Te falta un campo por rellenar.',
            'semestre.required' => 'Te falta un campo por rellenar.',
            'semestre.min' => 'Te falta un campo por rellenar.',
            'type.required' => 'Te falta un campo por rellenar.',
            'type.in' => 'Te falta un campo por rellenar.',
        ]);

        // 2. PREPARAR DATOS (la tabla materias exige descripcion NOT NULL)
        $dataToSave = [
            'career_id' => $validatedData['carrera_id'],
            'nombre' => $validatedData['nombre'],
            'creditos' => $validatedData['creditos'],
            'semestre' => $validatedData['semestre'],
            'type' => $validatedData['type'],
            'descripcion' => $validatedData['descripcion'] ?? '',
            'objetivo' => $validatedData['objetivo'] ?? null,
            'temario' => array_values(array_filter($validatedData['temario'] ?? [])),
            'infografia' => $validatedData['infografia'] ?? null,
            'career_classification_id' => $validatedData['career_classification_id'] ?? null,
        ];
        
        // 3. CREACIÓN DEL REGISTRO
        // Asegúrate de que el modelo Materia tenga 'career_id' en $fillable
        Materia::create($dataToSave); 

        // 4. REDIRECCIÓN (modal global careerSuccessModal vía ?modal=success)
        return Redirect::route('control.subjects.index', ['modal' => 'success'])
            ->with('success', '¡Materia creada exitosamente!');
    }
    public function update(Request $request, Materia $registro)
{
    // 1. VALIDACIÓN (nombre único por carrera; mismo nombre permitido en otra carrera)
    $validator = Validator::make($request->all(), [
        'carrera_id' => ['required', 'integer', 'exists:careers,id'],
        'nombre' => [
            'required',
            'string',
            'max:100',
            Rule::unique('materias', 'nombre')
                ->where('career_id', $request->input('carrera_id'))
                ->ignore($registro->id),
        ],
        'creditos' => ['required', 'integer', 'min:1'],
        'semestre' => ['required', 'integer', 'min:1'],
        'type' => ['required', 'in:Presencial,En linea'],
        'descripcion' => ['nullable', 'string', 'max:2000'],
        'objetivo' => ['nullable', 'string', 'max:3000'],
        'temario' => ['nullable', 'array'],
        'temario.*' => ['nullable', 'string', 'max:500'],
        'infografia' => ['nullable', 'string', 'max:10000'],
        'career_classification_id' => ['nullable', 'integer', 'exists:career_classifications,id'],
    ], [
        'carrera_id.required' => 'Te falta un campo por rellenar.',
        'carrera_id.integer' => 'Te falta un campo por rellenar.',
        'carrera_id.exists' => 'Te falta un campo por rellenar.',
        'nombre.required' => 'Te falta un campo por rellenar.',
        'nombre.max' => 'Te falta un campo por rellenar.',
        'nombre.unique' => 'Ya existe una materia con ese nombre. Elija otro.',
        'creditos.required' => 'Te falta un campo por rellenar.',
        'creditos.min' => 'Te falta un campo por rellenar.',
        'semestre.required' => 'Te falta un campo por rellenar.',
        'semestre.min' => 'Te falta un campo por rellenar.',
        'type.required' => 'Te falta un campo por rellenar.',
        'type.in' => 'Te falta un campo por rellenar.',
    ]);

    if ($validator->fails()) {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'message' => $validator->errors()->first() ?? 'Error de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }
        return redirect()->back()
            ->withErrors($validator)
            ->withInput()
            ->with('edit_materia_id', $registro->id);
    }

    $validatedData = $validator->validated();

    // 2. MAPEO Y PRESERVACIÓN DE DATOS
    // El form envía 'carrera_id' pero la columna en DB es 'career_id'
    $validatedData['career_id'] = $validatedData['carrera_id'];
    unset($validatedData['carrera_id']);

    $validatedData['clave'] = $registro->clave ?? null;
    $validatedData['descripcion'] = $validatedData['descripcion'] ?? $registro->descripcion ?? '';
    $validatedData['objetivo'] = $validatedData['objetivo'] ?? null;
    $validatedData['temario'] = array_values(array_filter($validatedData['temario'] ?? []));
    $validatedData['infografia'] = $validatedData['infografia'] ?? null;

    // 3. ACTUALIZACIÓN
    $registro->update($validatedData);

    $registro->load([
        'career' => function ($q) {
            $q->select('id', 'name', 'career_classification_id')
                ->with(['classification' => function ($c) {
                    $c->select('id', 'name');
                }]);
        },
        'classification',
    ]);

    $message = '¡Materia actualizada exitosamente!';
    if ($request->expectsJson()) {
        return response()->json([
            'ok' => true,
            'message' => $message,
            'row' => [
                'nombre' => $registro->nombre,
                'classification_name' => $registro->classification?->name ?? '–',
                'career_name' => $registro->career?->name ?? 'Sin datos',
                'creditos' => (string) $registro->creditos,
                'semestre' => (string) $registro->semestre,
                'type' => $registro->type,
            ],
        ]);
    }

    return Redirect::route('control.subjects.index', ['modal' => 'success'])
        ->with('success', $message);
}

    public function destroy(Request $request, Materia $registro)
    {
        $registro->delete();
        $message = 'Materia eliminada correctamente.';
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }
        return Redirect::route('control.subjects.index', ['modal' => 'success'])
            ->with('success', $message);
    }
}
