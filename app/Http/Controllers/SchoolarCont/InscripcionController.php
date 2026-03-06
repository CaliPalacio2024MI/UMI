<?php

namespace App\Http\Controllers\SchoolarCont;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

// Modelos Necesarios
use App\Models\Users\User;
use App\Models\Users\Address;
use App\Models\Users\Career; 
use App\Models\Users\AcademicProfile;
use App\Models\Users\Enrollment; 
use App\Models\Users\Period;
use App\Models\Facturacion\Billing;
use App\Models\Facturacion\BillingConcept; 

class InscripcionController extends Controller
{
    // =========================================================================
    // 1. MOSTRAR FORMULARIO (INDEX / CREATE)
    // =========================================================================
    
    public function index(){
        $periodoActivo = Period::where('is_active', 1)->first();
        // Obtenemos todos los periodos para el select del formulario (aunque se pre-seleccione el activo)
        $periods = Period::all(); 

        if (!$periodoActivo) {
            session()->flash('error', '⛔ NO SE PUEDE INSCRIBIR: No hay un Periodo Académico activo configurado.');
        }

        $carreras = Career::all();

        // Obtener Conceptos de Facturación Disponibles
        $conceptosDisponibles = BillingConcept::all();

        return view('layouts.ControlEsc.Inscripcion.index', compact(
            'carreras',
            'periodoActivo',
            'periods',
            'conceptosDisponibles'
        ));
    }

    public function create()
    {
        return $this->index(); 
    }

    // =========================================================================
    // 2. GUARDAR NUEVO ASPIRANTE (STORE)
    // =========================================================================
    public function store(Request $request)
    {   
        $periodoActivo = Period::where('is_active', 1)->first();
        if (!$periodoActivo) {
            return back()->with('error', 'Error crítico: El periodo se cerró durante el proceso.');
        }

        // --- VALIDACIONES ---
        $rules = [
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'carrera_id' => 'required|exists:careers,id', 
            'calle' => 'required',
            'colonia' => 'required',
            'telefono' => 'required|string',
            'fecha_nacimiento' => 'required|date',
        ];

        if (!$request->filled('existing_user_id')) {
            $rules['email'] = 'required|email|unique:users,email';
            $rules['curp'] = 'required|string|size:18|unique:users,curp';
        } else {
            $rules['email'] = 'required|email';
            $rules['curp'] = 'required|string|size:18|unique:users,curp,' . $request->existing_user_id;
        }

        // Factura obligatoria para inscripción
        if (!$request->has('generar_factura')) {
            $redirect = $request->filled('modal')
                ? redirect()->route('escolar.inscripcion.create', ['modal' => 1])
                : back();
            return $redirect->withInput()->withErrors([
                'generar_factura' => 'La ficha de pago/factura es obligatoria. Por favor, marque la casilla.',
            ]);
        }

        if ($request->has('generar_factura')) {
            $rules['period_id'] = 'required';
            $rules['concepto'] = 'required';
            $rules['monto'] = 'required';
            $rules['status'] = 'required';
        }

        $messages = [
            'curp.required' => 'El campo CURP es obligatorio.',
            'curp.size' => 'La CURP debe tener exactamente 18 caracteres.',
            'curp.unique' => 'Esta CURP ya está registrada.',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $redirect = $request->filled('modal')
                ? redirect()->route('escolar.inscripcion.create', ['modal' => 1])
                : back();
            return $redirect->withInput()->withErrors($validator);
        }

        DB::beginTransaction();

        try {
            $user = null;
            
            // A. GESTIÓN DE USUARIO
            if ($request->filled('existing_user_id')) {
                $user = User::findOrFail($request->existing_user_id);
                $user->update([
                    'telefono' => $request->telefono,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'edad' => $request->edad,
                ]);

                if($user->address) {
                    $user->address->update([
                        'calle' => $request->calle, 'colonia' => $request->colonia,
                        'ciudad' => $request->ciudad, 'estado' => $request->estado,
                        'codigo_postal' => $request->codigo_postal,
                    ]);
                } else {
                    $address = Address::create([
                        'calle' => $request->calle, 'colonia' => $request->colonia,
                        'ciudad' => $request->ciudad, 'estado' => $request->estado,
                        'codigo_postal' => $request->codigo_postal,
                    ]);
                    $user->address_id = $address->id;
                    $user->save();
                }

            } else {
                // Nuevo Usuario
                $rfcFinal = $request->RFC ?? ('XAXX010101000' . rand(100, 999));
                $address = Address::create([
                    'calle' => $request->calle, 'colonia' => $request->colonia,
                    'ciudad' => $request->ciudad, 'estado' => $request->estado,
                    'codigo_postal' => $request->codigo_postal,
                ]);

                $user = User::create([
                    'nombre' => $request->nombre,
                    'apellido_paterno' => $request->apellido_paterno,
                    'apellido_materno' => $request->apellido_materno,
                    'email' => $request->email,
                    'password' => Hash::make('TMP_' . uniqid()), 
                    'RFC' => $rfcFinal,
                    'curp' => $request->curp,
                    'telefono' => $request->telefono,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'edad' => $request->edad,
                    'address_id' => $address->id,
                    'institution_id' => 4,
                    'department_id' => null,
                    'workstation_id' => null,
                    'role_id' => 7,
                    'is_active' => 1
                ]);
            }

            // B. ROLES
            $yaEsEstudiante = $user->roles()
                                   ->where('roles.id', 7)
                                   ->wherePivot('institution_id', 4)
                                   ->exists();

            if (!$yaEsEstudiante) {
                $user->roles()->attach(7, ['institution_id' => 4, 'is_active' => 1]);
            }

            // C. PERFIL Y DOCUMENTOS
            // 1. Subir archivos
            $rutasDocs = $this->subirDocumentos($request, $user->id);

            // 2. Preparar datos base
            $datosPerfil = [
                'career_id' => $request->carrera_id, 
                'semestre' => 1,
                'status' => 'Alumno Inactivo', 
                'is_anfitrion' => false,
            ];

            // 3. Fusionar para guardar en BD
            $datosPerfil = array_merge($datosPerfil, $rutasDocs);

            AcademicProfile::updateOrCreate(
                ['user_id' => $user->id],
                $datosPerfil
            );

            Enrollment::create([
                'user_id' => $user->id,
                'career_id' => $request->carrera_id,
                'semestre' => 1,
                'periodo' => $periodoActivo->id,
                'status' => 'Pendiente',
                'doc_acta_nacimiento' => $rutasDocs['doc_acta_nacimiento'] ?? null,
                'doc_certificado_prepa' => $rutasDocs['doc_certificado_prepa'] ?? null,
                'doc_curp' => $rutasDocs['doc_curp'] ?? null,
                'doc_ine' => $rutasDocs['doc_ine'] ?? null,
            ]);

            // D. FACTURACIÓN DINÁMICA
            $mensajeExtra = "";
            if ($request->has('generar_factura')) {
                
                $fechaHoy = now()->format('Ymd'); 
                $baseUid = 'INS-' . $fechaHoy;   

                $ultimo = Billing::withTrashed()->where('factura_uid', 'like', $baseUid . '%')->orderBy('id', 'desc')->first();
                $consecutivo = $ultimo ? intval(substr($ultimo->factura_uid, -6)) + 1 : 1;
                $uidFinal = $baseUid . str_pad($consecutivo, 6, '0', STR_PAD_LEFT);

                $billingPaths = $this->subirArchivosFactura($request, $user->id);

                Billing::create([
                    'factura_uid'       => $uidFinal,
                    'user_id'           => $user->id,
                    'period_id'         => $request->period_id ?? $periodoActivo->id,
                    'concepto'          => $request->concepto,
                    'monto'             => $request->monto,
                    'fecha_vencimiento' => Carbon::now(), 
                    'status'            => $request->status,
                    'concept_type'      => 'INS',
                    'archivo_path'      => $billingPaths['archivo'] ?? null,
                    'xml_path'          => $billingPaths['archivo_xml'] ?? null,
                ]);
                $mensajeExtra = " Ficha de pago generada (Folio: $uidFinal).";
            }

            DB::commit();
            if ($request->filled('modal')) {
                return redirect()->route('escolar.inscripcion.create', ['modal' => 1, 'success' => 1])
                    ->with('success', 'Aspirante registrado correctamente.' . $mensajeExtra);
            }
            return redirect()->route('escolar.students.index')
                ->with('success', 'Aspirante registrado correctamente.' . $mensajeExtra);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // UPDATE (REINSCRIPCIÓN)
    // =========================================================================
    public function update(Request $request, string $id)
    {
        $periodoActivo = Period::where('is_active', 1)->first();
        if (!$periodoActivo) return back()->with('error', 'No hay periodo activo.');

        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            $perfil = $user->academicProfile;
            
            if (!$perfil) {
                $perfil = AcademicProfile::create(['user_id' => $user->id, 'career_id' => $request->carrera_id, 'semestre' => 0]);
            }

            // 1. Actualizar Datos Personales
            $user->update([
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'RFC' => $request->filled('RFC') ? $request->RFC : $user->RFC,
                'curp' => $request->curp,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'edad' => $request->edad,
            ]);
            
            if ($user->address) {
                $user->address->update([
                    'calle' => $request->calle,
                    'colonia' => $request->colonia,
                    'ciudad' => $request->ciudad,
                    'estado' => $request->estado,
                    'codigo_postal' => $request->codigo_postal,
                ]);
            }

            $nuevoSemestre = $perfil->semestre + 1;
            $nuevaCarreraId = $request->carrera_id ?? $perfil->career_id;

            // 2. LOGICA DE DOCUMENTOS (Aquí está la corrección)
            $nuevosDocs = $this->subirDocumentos($request, $user->id);
            
            // b) Preparamos el array final para Enrollment:
            //    Si existe archivo nuevo, úsalo. Si no, mantén el existente del perfil.
            $docsFinales = [
                'doc_acta_nacimiento' => $nuevosDocs['doc_acta_nacimiento'] ?? $perfil->doc_acta_nacimiento,
                'doc_certificado_prepa' => $nuevosDocs['doc_certificado_prepa'] ?? $perfil->doc_certificado_prepa,
                'doc_curp' => $nuevosDocs['doc_curp'] ?? $perfil->doc_curp,
                'doc_ine' => $nuevosDocs['doc_ine'] ?? $perfil->doc_ine,
            ];

            // 3. Actualizar Perfil
            // Usamos array_filter($nuevosDocs) para evitar sobrescribir con null si no se envió archivo
            $perfil->update(array_merge(array_filter($nuevosDocs), [
                'semestre' => $nuevoSemestre,
                'career_id' => $nuevaCarreraId,
                'status' => 'Inactivo', 
                'is_anfitrion' => false,
            ]));

            // 4. Crear Historial (Enrollment) con la foto completa de documentos
            Enrollment::create(array_merge($docsFinales, [
                'user_id' => $user->id,
                'career_id' => $nuevaCarreraId,
                'semestre' => $nuevoSemestre,
                'periodo' => $periodoActivo->id,
                'status' => 'Pendiente',
            ]));

            // 5. FACTURACIÓN (Reinscripción)
            $mensajeExtra = "";
            if ($request->has('generar_cobro_reinscripcion')) {
                
                $fechaHoy = now()->format('Ymd'); 
                $baseUid = 'RE-' . $fechaHoy;   
                $ultimo = Billing::withTrashed()->where('factura_uid', 'like', $baseUid . '%')->orderBy('id', 'desc')->first();
                $consecutivo = $ultimo ? intval(substr($ultimo->factura_uid, -6)) + 1 : 1;
                $uidFinal = $baseUid . str_pad($consecutivo, 6, '0', STR_PAD_LEFT);

                Billing::create([
                    'factura_uid'       => $uidFinal,
                    'user_id'           => $user->id,
                    'period_id'         => $request->period_id ?? $periodoActivo->id,
                    'concepto'          => $request->concepto,
                    'monto'             => $request->monto,
                    'fecha_vencimiento' => Carbon::now(), 
                    'status'            => $request->status,
                    'concept_type'      => 'RE',
                    'archivo_path'      => $billingPaths['archivo'] ?? null,
                    'xml_path'          => $billingPaths['archivo_xml'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('escolar.students.index')->with('success', 'Reinscripción procesada.' . $mensajeExtra);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $periodoActivo = Period::where('is_active', 1)->first();
        $periods = Period::all(); // También enviamos periods aquí
        $user = User::with(['address', 'academicProfile'])->findOrFail($id);
        $carreras = Career::all();
        $historialInscripciones = Enrollment::where('user_id', $id)->orderBy('created_at', 'desc')->get();
        $conceptosDisponibles = BillingConcept::all();

        return view('layouts.ControlEsc.Inscripcion.index', [
            'alumno' => $user,
            'carreras' => $carreras,
            'periodoActivo' => $periodoActivo,
            'periods' => $periods,
            'historialInscripciones' => $historialInscripciones,
            'conceptosDisponibles' => $conceptosDisponibles
        ]);
    }

    public function destroy($id) {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('escolar.students.index')->with('success', 'Usuario eliminado.');
    }

    private function subirDocumentos($request, $userId) {
        $rutas = [];
        $campos = ['doc_acta_nacimiento', 'doc_certificado_prepa', 'doc_curp', 'doc_ine'];
        foreach ($campos as $campo) {
            if ($request->hasFile($campo)) {
                $rutas[$campo] = $request->file($campo)->store("documentos/{$userId}/expediente", 'public');
            }
        }
        return $rutas;
    }

    // Helper para subir archivos de facturación (PDF y XML)
    private function subirArchivosFactura($request, $userId) {
        $rutas = [];
        if ($request->hasFile('archivo')) {
            $rutas['archivo'] = $request->file('archivo')->store("facturas/{$userId}", 'public');
        }
        if ($request->hasFile('archivo_xml')) {
            $rutas['archivo_xml'] = $request->file('archivo_xml')->store("facturas_xml/{$userId}", 'public');
        }
        return $rutas;
    }
}