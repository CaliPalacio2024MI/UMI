<?php

namespace App\Http\Controllers\Ajustes; 

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


// --- IMPORTA TODOS LOS MODELOS ---
use App\Models\Users\Institution;
use App\Models\Users\Department;
use App\Models\Users\Workstation;
use App\Models\Users\User;
use App\Models\Users\Role;
use App\Models\Users\Period;
use App\Models\Users\AcademicProfile;
use App\Models\Users\CorporateProfile;
use App\Models\Lead;
use App\Models\DocumentRequirement;

class AjustesController extends Controller
{

    /**
     * Muestra la vista principal de la sección (index.blade.php).
     */
    public function show(Request $request, $seccion)
    {
      $search = $request->get('search');
        $query = $this->getQueryForSeccion($seccion, $search);

        if (!$query) {
            abort(404, 'Sección no encontrada');
        }

        
        $universityName = 'Universidad Mundo Imperial';
        $activeInstitutionId = (int) session('active_institution_id', 0);
        $activeInstitution = $activeInstitutionId > 0
            ? Institution::find($activeInstitutionId)
            : null;
        $isUniversity = (bool) ($activeInstitution?->is_universidad ?? false);
        // Fallback por compatibilidad con registros/sesiones antiguos.
        if (! $isUniversity) {
            $isUniversity = (session('active_institution_name') === $universityName);
        }

        

        if ($isUniversity) {
            
            if (in_array($seccion, ['institutions', 'workstations'])) {
                abort(403, 'No tienes permiso para ver esta sección.');
            }
        } else {

            // 'expediente' (documentos requeridos por proceso) es exclusivo de la Universidad.
            if (in_array($seccion, ['periods', 'expediente'])) {
                abort(403, 'No tienes permiso para ver esta sección.');
            }
        }
        
        
        $this->applyAuthFilters($query, $seccion);

        // Usuarios: sin paginación; lista completa con scroll en la vista (tabla responsiva).
        if ($seccion === 'users') {
            $data = $query->get();
        } else {
            $data = $query->paginate(15)->withQueryString();
        } 
        
        $titles = $this->getSectionTitles($seccion);
        $page_title = $titles['plural'];
        $singular_title = $titles['singular'];

        if ($request->ajax() && $seccion === 'users') {
            return response()->view('layouts.Ajustes.partials.users_table_body', compact('data', 'seccion', 'page_title'));
        }

        return view('layouts.Ajustes.index', compact('data', 'seccion', 'page_title', 'singular_title'));
    }

  
    public function getCreateForm($seccion)
    {
        
        $data = $this->getFormData($seccion);

        
        if (!isset($data['item'])) {
            $data['item'] = null;
        }

        
        return view("layouts.Ajustes.forms._{$seccion}", $data);
    }

    /**
     * Devuelve el HTML del formulario de EDICIÓN con datos para inyectar en el modal.
     */
    public function getEditForm($seccion, $id)
    {
       
        $data = $this->getFormData($seccion, $id);

        if (!isset($data['item'])) {
            return response('Recurso no encontrado o no autorizado', 404);
        }

        
        return view("layouts.Ajustes.forms._{$seccion}", $data);
    }


    // -----------------------------------------------------------------
    // --- MÉTODOS CRUD (STORE, UPDATE, DESTROY) ---
    // -----------------------------------------------------------------

    public function store(Request $request, $seccion)
    {
        $activeInstitutionId = session('active_institution_id');
        $data = $request->all();
        
        
        if (!isset($data['institution_id'])) {
             if ($seccion === 'workstations') {
                $department = Department::find($request->department_id);
                $data['institution_id'] = $department ? $department->institution_id : $activeInstitutionId;
             } else if ($seccion !== 'institutions') {
                $data['institution_id'] = $activeInstitutionId;
             }
        }

        
        $message = 'Registro guardado exitosamente.';
        $clear_spa_cache = false;

        switch ($seccion) {
            case 'institutions':
                $request->validate(['name' => 'required|string|max:255|unique:institutions']);
                $data = $request->all();
                $data['is_administrativo'] = $request->boolean('is_administrativo');
                $data['is_universidad'] = $request->boolean('is_universidad');

                if ($request->hasFile('logo_path')) {
                    $data['logo_path'] = $request->file('logo_path')->store('logos/institutions', 'public');
                }
                
                $institution = Institution::create($data);
                
                try {
                    $user = Auth::user(); 
                    $masterRole = Role::where('name', 'master')->first(); 

                    if ($user && $masterRole) {
                        $user->roles()->attach($masterRole->id, [
                            'institution_id' => $institution->id
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Fallo al auto-asignar master a nueva institución: ' . $e->getMessage());
                }
                
                $message = 'Institución creada exitosamente.';
                break;
            
            case 'departments':
                $request->validate([
                    'name' => 'required|string|max:255',
                    'institution_id' => 'required|exists:institutions,id'
                ]);
                Department::create($data);
                $message = 'Departamento creado exitosamente.';
                break;

            case 'workstations':
                 $request->validate([
                    'name' => 'required|string|max:255',
                    'department_id' => 'required|exists:departments,id'
                ]);
                Workstation::create($data);
                $message = 'Puesto creado exitosamente.';
                break;

            case 'periods':
                $validatedData = $request->validate([
                    'start_date' => 'required|date_format:Y-m',
                    'end_date' => 'required|date_format:Y-m|after_or_equal:start_date',
                    'institution_id' => 'required|exists:institutions,id',
                    'monthly_payments_count' => 'nullable|integer|min:1', 
                    'payment_dates' => 'nullable|array',
                    'payment_dates.*' => 'date',
                ]);
                
               
                $data = [];
                $data['institution_id'] = $validatedData['institution_id'];
                $data['start_date'] = Carbon::parse($validatedData['start_date'])->startOfMonth();
                $data['end_date'] = Carbon::parse($validatedData['end_date'])->endOfMonth();
                $data['name'] = strtoupper($data['start_date']->format('M Y') . ' - ' . $data['end_date']->format('M Y'));
                $data['is_active'] = true;

                // LÓGICA DE CONTEO Y FECHAS
                if ($request->has('payment_dates')) {
                    // Guardamos el array (el modelo lo convierte a JSON solo)
                    $data['payment_dates'] = $request->payment_dates;
                    // El conteo es exacto a la cantidad de fechas enviadas
                    $data['monthly_payments_count'] = count($request->payment_dates);
                } else {
                    // Fallback por si acaso
                    $data['monthly_payments_count'] = $data['start_date']->diffInMonths($data['end_date']) + 1;
                }

                // Desactivar otros periodos de la misma institución
                Period::where('institution_id', $data['institution_id'])->update(['is_active' => false]);
                
                Period::create($data);
                break;

            case 'expediente':
                $payload = $this->validateExpediente($request);
                $payload['institution_id'] = $activeInstitutionId;
                DocumentRequirement::create($payload);
                $message = 'Documento agregado al expediente.';
                break;

            case 'users':

            $this->resolveCorporateCatalogIdsFromApiNames($request);
           
            $tipoCreacion = $request->input('tipo_usuario_creacion', 'normal');
            $rfcMax = 18;
            $rfcMin = 12;

            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
                'apellido_paterno' => 'required|string|max:255',
                'apellido_materno' => 'nullable|string|max:255',
                'RFC' => [
                    'required',
                    'string',
                    'min:'.$rfcMin,
                    'max:'.$rfcMax,
                    function ($attribute, $value, $fail) {
                        $v = strtoupper(trim((string) $value));
                        $isCurp = (bool) preg_match('/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[A-Z0-9][0-9]$/', $v);
                        $isRfc = (bool) preg_match('/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/', $v);
                        if (! $isCurp && ! $isRfc) {
                            $fail('Capture un RFC o CURP válido.');
                        }
                    },
                ],
                'role_id' => 'required|exists:roles,id',
                'institution_id' => 'required|exists:institutions,id',
                'department_id' => 'nullable|exists:departments,id',
                'workstation_id' => 'nullable|exists:workstations,id',
                'tipo_usuario_creacion' => 'nullable|in:normal,alumno',
               
            ]);
            $validatedData['RFC'] = strtoupper(trim($validatedData['RFC']));

            // Se permite crear usuarios en la unidad seleccionada desde el formulario.
 
            $selectedRole = Role::find($validatedData['role_id']);
            if ($tipoCreacion === 'alumno') {
                $rolEstudiante = Role::where('name', 'estudiante')->first();
                if (! $rolEstudiante || (int) $validatedData['role_id'] !== (int) $rolEstudiante->id) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'role_id' => 'Si marcas Alumno, el rol principal debe ser Alumno.',
                    ]);
                }
            }
 
           
           
           
            if ($selectedRole && $selectedRole->name === 'control_administrativo') {
                if (!$request->has('modules_enabled') || empty($request->input('modules_enabled'))) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'modules_enabled' => 'Para el rol de Control Administrativo, debes seleccionar al menos un permiso.',
                    ]);
                }
            }
 
           
            if ($selectedRole && $selectedRole->name === 'anfitrion') {
                $errors = [];
                if (empty($request->input('department_id'))) {
                    $errors['department_id'] = 'El Departamento es obligatorio para el rol de Anfitrión.';
                }
                if (empty($request->input('workstation_id'))) {
                    $errors['workstation_id'] = 'El Puesto es obligatorio para el rol de Anfitrión.';
                }
                if (!empty($errors)) {
                    throw \Illuminate\Validation\ValidationException::withMessages($errors);
                }
            }
 
           
            $u = $validatedData['RFC'];
            $existingUser = User::withTrashed()
                ->where(function ($q) use ($u) {
                    $q->where('RFC', $u)->orWhere('curp', $u);
                })
                ->first();
 
            if ($existingUser) {
               
               
               
                if ($existingUser->trashed()) {
                    $existingUser->restore();
                }
 
               
                $hasRole = $existingUser->roles()
                    ->where('role_id', $validatedData['role_id'])
                    ->wherePivot('institution_id', $validatedData['institution_id'])
                    ->exists();
 
                if ($hasRole) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'RFC' => 'Este usuario ya tiene el rol de ' . $selectedRole->name . ' asignado en esta institución.',
                    ]);
                }
 
               
                $user = $existingUser;
                $message = 'Rol agregado al usuario existente';
 
               
 
            } else {
                if (User::where('RFC', $u)->orWhere('curp', $u)->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'RFC' => 'Este RFC o CURP ya está registrado.',
                    ]);
                }

                $request->validate([
                    'password' => [
                        'required',
                        'string',
                        'min:8',
                        'regex:/[a-z]/',
                        'regex:/[A-Z]/',
                        'regex:/[0-9]/',
                        'regex:/[^A-Za-z0-9]/',
                    ],
                ], [
                    'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                    'password.regex' => 'La contraseña debe incluir mayúscula, minúscula, número y símbolo.',
                ]);
 
               
                $userData = [
                    'nombre' => $validatedData['nombre'],
                    'apellido_paterno' => $validatedData['apellido_paterno'],
                    'apellido_materno' => $validatedData['apellido_materno'],
                    'RFC' => $u,
                    'curp' => preg_match('/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[A-Z0-9][0-9]$/', $u) ? $u : null,
                    'email' => null,
                    'password' => Hash::make($request->password),
                    'department_id' => $validatedData['department_id'],
                    'workstation_id' => $validatedData['workstation_id'],
                    'institution_id' => $validatedData['institution_id'],
                    'role_id' => $validatedData['role_id'],
                ];
 
                $user = User::create($userData);
                $message = 'Nuevo usuario creado';
            }
 
       
           
            $institution_id_to_add = $validatedData['institution_id'];
            $role_id_to_add = $validatedData['role_id'];
 
           
            $user->institutions()->syncWithoutDetaching([$institution_id_to_add]);
 
         
            $user->roles()->syncWithoutDetaching([
                $role_id_to_add => ['institution_id' => $institution_id_to_add, 'is_active' => true],
            ]);

            $user->update([
                'role_id' => $role_id_to_add,
                'institution_id' => $institution_id_to_add,
            ]);

            // Propiedades extra habilitadas para el usuario (además de la principal).
            $this->syncExtraUserInstitutions($request, $user, (int) $institution_id_to_add, (int) $role_id_to_add);

            $message .= ' y asignado correctamente.';
 
           
           
            $roleName = $selectedRole->name;
            $academicRoles = ['docente', 'control_escolar', 'control_administrativo', 'estudiante'];
 
            if (in_array($roleName, $academicRoles)) {
             
                $profileData = [];
                if ($roleName === 'control_administrativo') {
                    $profileData['modules'] = $request->input('modules_enabled', []);
                    $message .= ' Módulos guardados.';
                }
               
               
                $user->academicProfile()->create($profileData);
 
            } else {
               
                if (!$user->corporateProfile) {
                    $user->corporateProfile()->create();
                }
            }
 
            $clear_spa_cache = true;
                break;
        } 

        
        return redirect()->route('ajustes.show', ['seccion' => $seccion])
                         ->with('success', $message)
                         ->with('clear_spa_cache', $clear_spa_cache);
       
    }

    /**
     * Actualiza un registro existente desde el modal.
     */
public function update(Request $request, $seccion, $id)
    {
        $item = $this->findItem($seccion, $id);
        if (!$item) return back()->with('error', 'Registro no encontrado.');

        switch ($seccion) {
            case 'institutions':
                $data = $request->all();
                $data['is_administrativo'] = $request->boolean('is_administrativo');
                $data['is_universidad'] = $request->boolean('is_universidad');
                $request->validate(['name' => 'required|string|max:255|unique:institutions,name,' . $id]);
                if ($request->hasFile('logo_path')) {
                    if ($item->logo_path) Storage::disk('public')->delete($item->logo_path);
                    $data['logo_path'] = $request->file('logo_path')->store('logos/institutions', 'public');
                }
                $item->update($data);
                break;
            
            case 'departments':
                $item->update($request->validate([
                    'name' => 'required', 
                    'institution_id' => 'required'
                ]));
                break;
            
            case 'workstations':
                $validatedData = $request->validate([
                    'name' => 'required', 
                    'department_id' => 'required'
                ]);
                $department = Department::find($validatedData['department_id']);
                $validatedData['institution_id'] = $department ? $department->institution_id : $item->institution_id;
                $item->update($validatedData);
                break;

            case 'periods':
                $validatedData = $request->validate([
                    'start_date' => 'required|date_format:Y-m',
                    'end_date' => 'required|date_format:Y-m|after_or_equal:start_date',
                    // Validamos que payment_dates sea un array si existe
                    'payment_dates' => 'nullable|array',
                    'payment_dates.*' => 'date',
                ]);

                $updateData = [];
                $startDate = Carbon::parse($validatedData['start_date'])->startOfMonth();
                $endDate = Carbon::parse($validatedData['end_date'])->endOfMonth();

                $updateData['start_date'] = $startDate;
                $updateData['end_date'] = $endDate;
                $updateData['name'] = strtoupper($startDate->format('M Y') . ' - ' . $endDate->format('M Y'));

                // LÓGICA DE FECHAS Y CONTEO
                if ($request->has('payment_dates')) {
                    // Guardamos el array de fechas
                    $updateData['payment_dates'] = $request->payment_dates;
                    // El conteo es igual a la cantidad de fechas recibidas
                    $updateData['monthly_payments_count'] = count($request->payment_dates);
                } else {
                    // Fallback por si no se enviaron fechas
                    $updateData['monthly_payments_count'] = $startDate->diffInMonths($endDate) + 1;
                }

                $item->update($updateData);
                break;
            // ====================================================
            
            case 'users':
           
                // 1. Buscamos el usuario
                $item = $this->findItem($seccion, $id);
                if (!$item) return back()->with('error', 'Registro no encontrado.');

                $this->resolveCorporateCatalogIdsFromApiNames($request);

                // 2. Validación Principal (QUITAMOS PASSWORD DE AQUÍ)
                $tipoCreacion = $request->input('tipo_usuario_creacion', 'normal');
                $rfcMax = 18;
                $rfcMin = 12;

                $validatedData = $request->validate([
                    'nombre' => 'required|string|max:255',
                    'apellido_paterno' => 'required|string|max:255',
                    'apellido_materno' => 'nullable|string|max:255',
                    'RFC' => [
                        'required',
                        'string',
                        'min:'.$rfcMin,
                        'max:'.$rfcMax,
                        function ($attribute, $value, $fail) {
                            $v = strtoupper(trim((string) $value));
                            $isCurp = (bool) preg_match('/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[A-Z0-9][0-9]$/', $v);
                            $isRfc = (bool) preg_match('/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/', $v);
                            if (! $isCurp && ! $isRfc) {
                                $fail('Capture un RFC o CURP válido.');
                            }
                        },
                    ],
                    'role_id' => 'required|exists:roles,id',
                    'institution_id' => 'required|exists:institutions,id',
                    'department_id' => 'nullable|exists:departments,id',
                    'workstation_id' => 'nullable|exists:workstations,id',
                    'tipo_usuario_creacion' => 'nullable|in:normal,alumno',
                ]);

                $validatedData['RFC'] = strtoupper(trim($validatedData['RFC']));
                $u = $validatedData['RFC'];

                // Se permite asignar usuarios a la unidad seleccionada desde el formulario.

                if (User::where('id', '!=', $item->id)
                    ->where(function ($q) use ($u) {
                        $q->where('RFC', $u)->orWhere('curp', $u);
                    })
                    ->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'RFC' => 'Este RFC o CURP ya pertenece a otro usuario.',
                    ]);
                }

                $validatedData['curp'] = preg_match('/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[A-Z0-9][0-9]$/', $u) ? $u : null;

                // 3. Validación de Contraseña (AQUÍ SÍ VA)
                if (!empty($request->password)) {
                    $request->validate([
                        'password' => [
                            'string',
                            'min:8',
                            'regex:/[a-z]/',
                            'regex:/[A-Z]/',
                            'regex:/[0-9]/',
                            'regex:/[^A-Za-z0-9]/',
                        ]
                    ], [
                        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                        'password.regex' => 'La contraseña debe incluir mayúscula, minúscula, número y símbolo.',
                    ]);
                    
                    $validatedData['password'] = Hash::make($request->password);
                } else {
                    unset($validatedData['password']);
                }
                
                // 4. Validación Extra (Módulos de Admin)
                $selectedRole = Role::find($validatedData['role_id']);

                if ($tipoCreacion === 'alumno') {
                    $rolEstudiante = Role::where('name', 'estudiante')->first();
                    if (! $rolEstudiante || (int) $validatedData['role_id'] !== (int) $rolEstudiante->id) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'role_id' => 'Si marcas Alumno, el rol principal debe ser Alumno.',
                        ]);
                    }
                }

                if ($selectedRole && $selectedRole->name === 'control_administrativo') {
                    if (!$request->has('modules_enabled') || empty($request->input('modules_enabled'))) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'modules_enabled' => 'Debes mantener al menos un permiso activo para este rol.',
                        ]);
                    }
                }

                if ($selectedRole && $selectedRole->name === 'anfitrion') {
                    $errors = [];
                    if (empty($request->input('department_id'))) {
                        $errors['department_id'] = 'El Departamento es obligatorio para el rol de Anfitrión.';
                    }
                    if (empty($request->input('workstation_id'))) {
                        $errors['workstation_id'] = 'El Puesto es obligatorio para el rol de Anfitrión.';
                    }
                    if (!empty($errors)) {
                        throw \Illuminate\Validation\ValidationException::withMessages($errors);
                    }
                }

               
                $item->update($validatedData);
                
                
                $institution_id = $validatedData['institution_id'];
                $role_id = $validatedData['role_id'];

               
                $item->institutions()->syncWithoutDetaching([$institution_id]);

              
                $item->roles()->wherePivot('institution_id', $institution_id)->detach();
                $item->roles()->attach($role_id, ['institution_id' => $institution_id]);

                // Propiedades extra habilitadas para el usuario (además de la principal).
                $this->syncExtraUserInstitutions($request, $item, (int) $institution_id, (int) $role_id);

                
                $roleName = $selectedRole->name;
                $academicRoles = ['docente', 'control_escolar', 'control_administrativo', 'estudiante']; 

                if (in_array($roleName, $academicRoles)) {
                    // Crear perfil si no existe
                    if (!$item->academicProfile) { 
                        $profileData = [];
                        if ($roleName === 'control_administrativo') {
                            $profileData['modules'] = $request->input('modules_enabled', []);
                        }
                        $item->academicProfile()->create($profileData); 
                    } else { 
                        // Actualizar perfil existente
                        if ($roleName === 'control_administrativo') {
                            $modulesToSave = $request->input('modules_enabled', []);
                            $item->academicProfile->update(['modules' => $modulesToSave]);
                        } else {
                            // Limpiar módulos si ya no es admin
                            $item->academicProfile->update(['modules' => null]);
                        }
                    }
                }
                

                $clear_spa_cache = true;

                break;

            case 'expediente':
                $item->update($this->validateExpediente($request));
                break;

            default:
                return back()->with('error', 'Sección no válida.');
        }

        return redirect()->route('ajustes.show', ['seccion' => $seccion])
                         ->with('success', 'Registro actualizado exitosamente.');
    }
    
    // Helper temporal si no tienes el método updateUserLogic, pega tu código de users dentro del case.
    private function updateUserLogic($request, $item) {
         // ... Pega aquí tu lógica de actualización de usuarios del código anterior ...
    }

    /**
     * Elimina un registro.
     */
    public function destroy($seccion, $id)
    {
        $item = $this->findItem($seccion, $id);
        if (!$item) return back()->with('error', 'Registro no encontrado.');
        
        try {
            if ($seccion === 'institutions' && $item->logo_path) {
                Storage::disk('public')->delete($item->logo_path);
            }
            if ($seccion === 'users') {
                $item->institutions()->detach();
                $item->roles()->detach();
            }

            $item->delete();
            
            return redirect()->route('ajustes.show', ['seccion' => $seccion])
                             ->with('success', 'Registro eliminado exitosamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo eliminar el registro. Puede estar en uso.');
        }
    }


    // -----------------------------------------------------------------
    // --- MÉTODOS PRIVADOS DE AYUDA ---
    // -----------------------------------------------------------------

    /**
     * Obtiene el Query Builder base para una sección, aplicando búsqueda.
     */
    private function getQueryForSeccion($seccion, $search = null)
    {
        $activeInstitutionId = session('active_institution_id');
        $query = null;
        switch ($seccion) {
            case 'institutions':
                $query = Institution::query();
                if ($search) $query->where('name', 'like', "%{$search}%");
                break;
            case 'departments':
                $query = Department::with('institution');
                if ($search) $query->where('name', 'like', "%{$search}%");
                break;
            case 'workstations':
                $query = Workstation::with('department');
                if ($search) $query->where('name', 'like', "%{$search}%");
                break;
            case 'periods':
                $query = Period::query();
                if ($search) $query->where('name', 'like', "%{$search}%");
               $query->orderBy('id', 'asc');
                break;
            case 'expediente':
                // Documentos requeridos, filtrados por el proceso seleccionado en las pestañas.
                $proceso = $this->currentProceso();
                $query = DocumentRequirement::query()->where('proceso', $proceso);
                if ($search) $query->where('nombre', 'like', "%{$search}%");
                $query->orderBy('orden')->orderBy('id');
                break;
            case 'users':
                $query = User::with([
                    'institutions',
                    'roles'
                ]); 
                if ($search) {
                    $term = "%{$search}%";
                    $query->where(function($q) use ($term) {
                        $q->where('nombre', 'like', $term)
                          ->orWhere('apellido_paterno', 'like', $term)
                          ->orWhere('apellido_materno', 'like', $term)
                          ->orWhere('RFC', 'like', $term)
                          ->orWhereHas('institutions', function($sub) use ($term) {
                              $sub->where('institutions.name', 'like', $term);
                          })
                          ->orWhereHas('roles', function($sub) use ($term) {
                              $sub->where('roles.display_name', 'like', $term);
                          });
                    });
                }
                $query->orderBy('id', 'desc');
                break;
        }
        return $query;
    }

    private function resolveCorporateCatalogIdsFromApiNames(Request $request): void
    {
        $institutionId = (int) ($request->input('institution_id') ?: session('active_institution_id'));
        if ($institutionId <= 0) {
            return;
        }

        $departmentId = $request->input('department_id');
        $workstationId = $request->input('workstation_id');
        $departmentApiName = trim((string) $request->input('department_api_name', ''));
        $workstationApiName = trim((string) $request->input('workstation_api_name', ''));

        if (empty($departmentId) && $departmentApiName !== '') {
            $department = Department::query()
                ->where('institution_id', $institutionId)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($departmentApiName, 'UTF-8')])
                ->first();

            if (! $department) {
                $department = Department::create([
                    'name' => $departmentApiName,
                    'institution_id' => $institutionId,
                ]);
            }

            $departmentId = (string) $department->id;
            $request->merge(['department_id' => $departmentId]);
        }

        if (empty($workstationId) && ! empty($departmentId) && $workstationApiName !== '') {
            $workstation = Workstation::query()
                ->where('institution_id', $institutionId)
                ->where('department_id', (int) $departmentId)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($workstationApiName, 'UTF-8')])
                ->first();

            if (! $workstation) {
                $workstation = Workstation::create([
                    'name' => $workstationApiName,
                    'department_id' => (int) $departmentId,
                    'institution_id' => $institutionId,
                ]);
            }

            $request->merge(['workstation_id' => (string) $workstation->id]);
        }
    }

    /**
     * Valida y normaliza el formulario de un documento del expediente.
     * Devuelve el arreglo listo para create()/update().
     */
    private function validateExpediente(Request $request): array
    {
        $validated = $request->validate([
            'proceso'        => ['required', 'string', 'in:' . implode(',', array_keys(DocumentRequirement::PROCESOS))],
            'nombre'         => ['required', 'string', 'max:255'],
            'descripcion'    => ['nullable', 'string', 'max:1000'],
            'tipos_archivo'  => ['required', 'array', 'min:1'],
            'tipos_archivo.*' => ['string', 'in:' . implode(',', array_keys(DocumentRequirement::TIPOS_ARCHIVO))],
            'cantidad'       => ['required', 'integer', 'min:1', 'max:20'],
            'orden'          => ['nullable', 'integer', 'min:0', 'max:999'],
        ], [
            'tipos_archivo.required' => 'Selecciona al menos un tipo de archivo permitido.',
            'tipos_archivo.min'      => 'Selecciona al menos un tipo de archivo permitido.',
        ]);

        return [
            'proceso'       => $validated['proceso'],
            'nombre'        => $validated['nombre'],
            'descripcion'   => $validated['descripcion'] ?? null,
            'tipos_archivo' => implode(',', $validated['tipos_archivo']),
            'cantidad'      => $validated['cantidad'],
            'obligatorio'   => $request->boolean('obligatorio'),
            'orden'         => $validated['orden'] ?? 0,
            'activo'        => $request->boolean('activo', true),
        ];
    }

    /**
     * Proceso activo para la sección "expediente" (pestañas). Valida contra la
     * lista de procesos permitidos y cae al primero por defecto.
     */
    private function currentProceso(): string
    {
        $proceso = (string) request()->query('proceso', '');
        $validos = array_keys(DocumentRequirement::PROCESOS);
        return in_array($proceso, $validos, true) ? $proceso : $validos[0];
    }

    /**
     * Encuentra un item por ID para 'update' o 'destroy'.
     */
    private function findItem($seccion, $id)
    {
        switch ($seccion) {
            case 'institutions': return Institution::find($id);
            case 'departments': return Department::find($id);
            case 'workstations': return Workstation::find($id);
            case 'periods': return Period::find($id);
            case 'users': return User::find($id);
            case 'expediente': return DocumentRequirement::find($id);
            default: return null;
        }
    }

    /**
     * Aplica filtros de autorización y de institución activa al query.
     */
    private function applyAuthFilters($query, $seccion)
    {
        $user = Auth::user();
        $activeInstitutionId = session('active_institution_id');

        if ($seccion === 'users') {
            // Master puede ver usuarios de todas las unidades.
            // El resto de perfiles se mantiene filtrado por unidad activa.
            if (! $user->hasActiveRole('master')) {
                $query->whereHas('institutions', function($q) use ($activeInstitutionId) {
                    $q->where('institutions.id', $activeInstitutionId);
                });
            }
        } elseif ($seccion !== 'institutions') {
            $query->where('institution_id', $activeInstitutionId);
        }

        
        if ($user->hasActiveRole('control_administrativo')) {
            if (in_array($seccion, ['institutions', 'workstations'])) {
                abort(403, 'No tienes permiso para ver esta sección.');
            }
            
            if ($seccion === 'users') {
                $query->whereHas('roles', function($q) {
                    $q->whereIn('name', ['control_escolar', 'docente']);
                });
            }
        }
    }

    /**
     * Habilita propiedades EXTRA a un usuario (además de la principal).
     * Recibe institution_access[] del formulario. En contexto de propiedad nunca
     * permite la universidad. Es aditivo (no quita accesos existentes).
     */
    private function syncExtraUserInstitutions(Request $request, User $user, int $primaryInstitutionId, int $roleId): void
    {
        $extra = $request->input('institution_access', []);
        if (! is_array($extra)) {
            return;
        }

        $extraIds = array_values(array_unique(array_filter(array_map('intval', $extra))));
        if (empty($extraIds)) {
            return;
        }

        // En contexto de propiedad no se puede habilitar la universidad.
        $activeInstitution = Institution::find(session('active_institution_id'));
        $isUniversityContext = (bool) ($activeInstitution?->is_universidad ?? false)
            || (optional($activeInstitution)->name === 'Universidad Mundo Imperial');

        $query = Institution::whereIn('id', $extraIds);
        if (! $isUniversityContext) {
            $query->where('is_universidad', false)
                  ->where('name', '!=', 'Universidad Mundo Imperial');
        }
        $allowedIds = $query->pluck('id')->all();

        foreach ($allowedIds as $instId) {
            $instId = (int) $instId;
            if ($instId === $primaryInstitutionId) {
                continue; // la principal ya se asignó
            }

            $user->institutions()->syncWithoutDetaching([$instId]);

            $already = $user->roles()
                ->wherePivot('institution_id', $instId)
                ->wherePivot('role_id', $roleId)
                ->exists();
            if (! $already) {
                $user->roles()->attach($roleId, ['institution_id' => $instId, 'is_active' => true]);
            }
        }
    }

    /**
     * Obtiene los datos necesarios para los formularios (dropdowns, etc.)
     * y el item a editar (si se provee $id).
     */
    private function getFormData($seccion, $id = null)
    {
       $data = [];
        $activeInstitutionId = session('active_institution_id');
        $user = Auth::user();

        
        $data['universityName'] = 'Universidad Mundo Imperial'; 
        $data['adminRoleName'] = 'control_administrativo';   

       
        $activeInstitution = Institution::find($activeInstitutionId);
        $data['activeInstitutionName'] = $activeInstitution ? $activeInstitution->name : null;
        
        
        $data['isActiveInstitutionUniversity'] = (bool) ($activeInstitution?->is_universidad ?? false);
        if (! $data['isActiveInstitutionUniversity']) {
            $data['isActiveInstitutionUniversity'] = ($data['activeInstitutionName'] === $data['universityName']);
        }

        if ($id) {
            $item = $this->findItem($seccion, $id);
            if (!$item) return ['item' => null];
            
            $data['item'] = $item;

            
            if ($seccion === 'users' && $item->academicProfile) {
                $data['enabled_modules'] = $item->academicProfile->modules ?? [];
            } else {
                $data['enabled_modules'] = [];
            }
        }
        
        switch ($seccion) {
            case 'departments':

                 $data['institutions'] = Institution::where('id', $activeInstitutionId)->get();
                break;
            case 'expediente':
                $data['procesos'] = DocumentRequirement::PROCESOS;
                $data['tiposArchivo'] = DocumentRequirement::TIPOS_ARCHIVO;
                // Proceso por defecto: el de la pestaña activa (al crear) o el del item (al editar).
                $data['currentProceso'] = isset($data['item'])
                    ? $data['item']->proceso
                    : $this->currentProceso();
                break;
            case 'workstations':
                
                $data['departments'] = Department::where('institution_id', $activeInstitutionId)->get();
                break;
            case 'periods':
                 
                 $data['institutions'] = Institution::where('id', $activeInstitutionId)->get();
                break;
                
            case 'users':
                // Máster de propiedad: solo puede asignar/habilitar PROPIEDADES, nunca la universidad.
                // (Los usuarios de la escuela solo los da de alta el máster de la universidad.)
                $institutionsQuery = Institution::query()->orderBy('name');
                if (! $data['isActiveInstitutionUniversity']) {
                    $institutionsQuery->where('is_universidad', false)
                                      ->where('name', '!=', $data['universityName']);
                }
                $data['all_institutions'] = $institutionsQuery->get();

                if ($user->hasActiveRole('master') && $data['isActiveInstitutionUniversity']) {
                    $data['institutions'] = Institution::where('id', $activeInstitutionId)->get();
                } else {
                    $data['institutions'] = $data['all_institutions'];
                }

                // Instituciones que el usuario ya tiene (para precargar los checkboxes al editar).
                $data['user_institution_ids'] = isset($data['item'])
                    ? $data['item']->institutions->pluck('id')->map(fn ($i) => (int) $i)->all()
                    : [];
                
               
                $data['all_roles'] = Role::orderBy('display_name')->get(); 
                
                
                $data['departments'] = Department::where('institution_id', $activeInstitutionId)->orderBy('name')->get();
                $data['workstations'] = Workstation::where('institution_id', $activeInstitutionId)->orderBy('name')->get();

                // Aspirantes CRM (id + datos para autocompletar apellidos y CURP en el modal de usuario)
                $data['aspirantes_crm_list'] = Lead::queryBaseAspirantesControlEscolar()
                    ->whereNotNull('alumno_nombre')
                    ->where('alumno_nombre', '!=', '')
                    ->orderBy('alumno_nombre')
                    ->orderBy('alumno_paterno')
                    ->get(['id', 'alumno_nombre', 'alumno_paterno', 'alumno_materno', 'alumno_curp']);

                
                if ($user->hasActiveRole('master')) {
                    
                } elseif ($user->hasActiveRole('control_administrativo')) {
                    
                } else {
                    
                }
                break;
        }
        return $data;
    }
    
public function toggleUserStatus($id)
    {
        
      $user = User::find($id);
    if (!$user) {
        return back()->with('error', 'Usuario no encontrado.');
    }

    $activeInstitutionId = session('active_institution_id');

    
    $pivotRow = DB::table('user_roles_institution')
                    ->where('user_id', $id)
                    ->where('institution_id', $activeInstitutionId)
                    ->first();

    if (!$pivotRow) {
        return back()->with('error', 'El usuario no tiene un rol en esta institución.');
    }

    
    $newStatus = !$pivotRow->is_active;

   
    DB::table('user_roles_institution')
        ->where('user_id', $id)
        ->where('institution_id', $activeInstitutionId)
        ->update(['is_active' => $newStatus]);

    $message = $newStatus 
        ? 'Usuario HABILITADO para esta institución.' 
        : 'Usuario DESHABILITADO para esta institución.';

    return redirect()->route('ajustes.show', ['seccion' => 'users'])
                     ->with('success', $message)
                     ->with('clear_spa_cache', true); 
    }

public function togglePeriodStatus($id)
    {
        $period = Period::find($id);

        if (!$period) {
            return back()->with('error', 'Periodo no encontrado.');
        }

        if ($period->is_active) {
            
            $period->is_active = false;
            $message = 'Periodo desactivado exitosamente.';
            $period->save();

        } else {
            
            Period::where('institution_id', $period->institution_id)
                  ->update(['is_active' => false]);
            
            
            $period->is_active = true;
            $message = 'Periodo activado exitosamente.';
            $period->save();
        }

        return redirect()->route('ajustes.show', ['seccion' => 'periods'])
                     ->with('success', $message); 
    }

    private function getSectionTitles($seccion)
{
    $titles = [
        'institutions' => [
            'plural' => 'Unidades de Negocio',
            'singular' => 'Unidad de Negocio'
        ],
        'departments' => [
            'plural' => 'Departamentos',
            'singular' => 'Departamento'
        ],
        'workstations' => [
            'plural' => 'Puestos',
            'singular' => 'Puesto'
        ],
        'periods' => [
            'plural' => 'Periodos',
            'singular' => 'Periodo'
        ],
        'users' => [
            'plural' => 'Usuarios',
            'singular' => 'Usuario'
        ],
        'expediente' => [
            'plural' => 'Expediente',
            'singular' => 'Documento'
        ],
    ];

    return $titles[$seccion] ?? [
        'plural' => Str::title(str_replace('_', ' ', $seccion)),
        'singular' => Str::singular(Str::title(str_replace('_', ' ', $seccion)))
    ];
}

}