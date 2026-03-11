<?php

namespace App\Http\Controllers\AdmonCont;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Users\Career;
use App\Models\Users\User;
use App\Models\AdmonCont\Materia;
use App\Models\AdmonCont\HorarioClase;
use App\Models\AdmonCont\HorarioFranja;
use App\Models\AdmonCont\Facility;


class HorarioController extends Controller
{
    public function index(Request $request)
    {
        // 1. Obtener los datos necesarios para los desplegables
        $carreras = Career::all();
        $aulas = Facility::all();
        $query = HorarioClase::with(['carrera', 'materia', 'user', 'aula', 'franjas']);
        $search = $request->search_query;

        // 💡 Importante: Filtramos los usuarios para que solo sean docentes.
        // Asumiendo que tienes un campo 'role' o una tabla de roles
        $docentes = User::with('academicProfile')->whereHas('roles', function ($query) {
            $query->where('name', 'docente'); // Asumiendo que el campo 'name' del Role es 'docente'
        })->get(); 
        
        // Las materias se cargan normalmente. 
        // Nota: Si dependes de la carrera seleccionada, esta lista se cargará inicialmente vacía o con AJAX.
        $materias = Materia::all(); 
        
        // 💡 CONDICIÓN CORREGIDA: Solo aplicamos el filtro si hay contenido útil.
        if ($search !== null && $search !== '') { 
            
            // Usamos una Cláusula WHERE principal para agrupar todas las condiciones OR
            $query->where(function ($q) use ($search) {
                
                // 1. Buscar por Materia
                $q->whereHas('materia', function ($sq) use ($search) {
                    // Usamos el método where(columna, operador, valor) para mayor claridad
                    $sq->where('nombre', 'LIKE', '%' . $search . '%');
                })
                
                // 2. Buscar por Carrera
                ->orWhereHas('carrera', function ($sq) use ($search) {
                    $sq->where('name', 'LIKE', '%' . $search . '%');
                })
                
                // 3. Buscar por Docente
                ->orWhereHas('user', function ($sq) use ($search) {
                    $sq->where('nombre', 'LIKE', '%' . $search . '%');
                });
            });
        }

        $horarios = $query->get();
        // 2. ¿Qué necesitamos hacer ahora con estos datos ($carreras, $aulas, $docentes, $materias)?
        return view('layouts.ControlAdmin.Horarios.index', [
            // Aquí van tus variables
            // 1. carreras
            'carreras' => $carreras,
            // 2. materias
            'materias' => $materias,
            // 3. docentes
            'docentes' => $docentes,
            // 4. aulas
            'aulas' => $aulas,
            // 5. Horarios
            'horarios' => $horarios,
        ]);
    }

    /**
     * Visualizar un horario (carrera, materia, docente, aula y franjas).
     * Si la petición pide JSON (p. ej. para el modal), devuelve los datos.
     */
    public function show(Request $request, HorarioClase $horario)
    {
        $horario->load(['carrera', 'materia', 'user', 'aula', 'franjas']);

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
                'materia' => $horario->materia->nombre ?? '—',
                'docente' => $horario->user->nombre ?? '—',
                'aula' => $horario->aula->numero_aula ?? '—',
                'franjas' => $franjas,
            ]);
        }

        // Solo se usa el modal desde el índice; acceso directo a la URL redirige a la lista.
        return redirect()->route('control.schedules.index');
    }
    
    public function store(Request $request){
        // 1. VALIDACIÓN (aula_id opcional por el momento)
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
        
        // Decodificar las franjas (el array temporal de JS)
        $franjasData = json_decode($request->franjas_json, true);
        
        //dd($request->all(), $franjasData);
        // ¡Validación crítica! Asegurar que se haya añadido al menos una franja de tiempo
        if (empty($franjasData)) {
            return redirect()->back()->withErrors(['franjas_json' => '']);
        }

        // Usamos una transacción para asegurar que, si falla el guardado de una franja, 
        // se revierta el guardado del registro maestro.
        DB::beginTransaction();
        try {
            // 2. CREAR REGISTRO MAESTRO (HorarioClase)
            $horarioClase = HorarioClase::create([
                'materia_id' => $request->materia_id,
                'career_id'  => $request->carrera_id,
                'user_id'    => $request->docente_id,
                'aula_id'    => $request->aula_id ?: null,
            ]);
            
            // 3. PREPARAR Y GUARDAR FRANJAS RELACIONADAS
            $franjasAGuardar = [];

            foreach ($franjasData as $franja) {
                // Generamos un registro individual en la tabla horario_franjas por cada día
                foreach ($franja['dias_semana'] as $dia) {
                    $franjasAGuardar[] = [
                        'dias_semana'  => $dia,
                        'hora_inicio' => $franja['hora_inicio'],
                        'hora_fin'    => $franja['hora_fin'],
                    ];
                }
            }

            // Usamos saveMany() para guardar todas las filas de franjas de golpe
            $horarioClase->franjas()->createMany($franjasAGuardar); 
            
            DB::commit();

            return redirect()->route('control.schedules.index');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'Error al guardar el horario: ' . $e->getMessage()]);
        }
    }
    public function destroy(HorarioClase $horario){
        // 💡 El Route Model Binding pasa directamente el objeto HorarioClase
        
        try {
            $horario->delete(); // Elimina el registro maestro
            
            // La configuración 'onDelete('cascade')' en tu migración se encarga 
            // de borrar automáticamente todas las filas de horario_franjas relacionadas.

            return redirect()->route('control.schedules.index');
            
        } catch (\Exception $e) {
            // Maneja cualquier error de base de datos
            return redirect()->route('control.schedules.index')->withErrors(['error' => 'No se pudo eliminar el horario.']);
        }
    }
    /**
     * Devuelve en JSON los datos del horario para edición (modal o AJAX).
     */
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
        $horario->load('franjas');
        $carreras = Career::all();
        $aulas = Facility::all();
        $docentes = User::with('academicProfile')->whereHas('roles', function ($q) {
            $q->where('name', 'docente');
        })->get();
        $materias = Materia::all();
        $data = compact('carreras', 'materias', 'docentes', 'aulas', 'horario');
        if ($request->ajax() || $request->wantsJson()) {
            return view('layouts.ControlAdmin.Horarios.edit_partial', $data);
        }
        return view('layouts.ControlAdmin.Horarios.edit', $data);
    }
    public function update(Request $request, HorarioClase $horario){
    // 1. VALIDACIÓN
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
    
    // Decodificar las franjas (el array temporal de JS)
    $franjasData = json_decode($request->franjas_json, true);
    
    if (empty($franjasData)) {
        return redirect()->back()->withInput()->withErrors(['franjas_json' => '']);
    }

    DB::beginTransaction();
    try {
        // 2. ACTUALIZAR REGISTRO MAESTRO (HorarioClase)
        $horario->update([
            'materia_id' => $request->materia_id,
            'career_id'  => $request->carrera_id,
            'user_id'    => $request->docente_id,
            'aula_id'    => $request->aula_id ?: null,
        ]);
        
        // 3. PREPARAR Y SINCRONIZAR FRANJAS HORARIAS
        $franjasAGuardar = [];

        foreach ($franjasData as $franja) {
            // Generamos un registro individual por cada día en el array del frontend
            foreach ($franja['dias_semana'] as $dia) {
                $franjasAGuardar[] = [
                    'dias_semana' => $dia,
                    'hora_inicio' => $franja['hora_inicio'],
                    'hora_fin'    => $franja['hora_fin'],
                ];
            }
        }

        // 🚨 Sincronización: Eliminar las franjas antiguas y crear las nuevas. 🚨
        $horario->franjas()->delete(); // Borra todas las franjas_horarias relacionadas (CASCADE)
        $horario->franjas()->createMany($franjasAGuardar); // Crea las nuevas

        DB::commit();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('control.schedules.index');

    } catch (\Exception $e) {
        DB::rollBack();
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar el horario.'], 422);
        }
        return redirect()->back()->withInput()->withErrors(['error' => 'Error al actualizar el horario.']);
    }
}
}
