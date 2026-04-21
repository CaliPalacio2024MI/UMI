<?php

namespace App\Http\Controllers\SchoolarCont;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

// Modelos Necesarios
use App\Models\Users\User;
use App\Models\Users\Address;
use App\Models\Users\Career; 
use App\Models\Users\AcademicProfile;
use App\Models\Users\Enrollment; 
use App\Models\Users\Period;
use App\Models\Facturacion\Billing;
use App\Models\Facturacion\BillingConcept;
use App\Models\Lead;

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

        // Si el usuario autenticado es estudiante, prellenamos el formulario
        // con sus datos actuales, pero manteniendo el modo "Nuevo Registro de Aspirante".
        $alumno = null;
        $modoReinscripcion = false;
        $bloqueadoPorAceptacion = false;
        if (Auth::check() && strtolower((string) session('active_role_name')) === 'estudiante') {
            $user = Auth::user()->loadMissing(['address', 'academicProfile']);
            $alumno = $user;

            // La vista espera campos "aplanados" sobre $alumno (calle/colonia/etc).
            if ($user->address) {
                $alumno->calle = $user->address->calle;
                $alumno->colonia = $user->address->colonia;
                $alumno->ciudad = $user->address->ciudad;
                $alumno->estado = $user->address->estado;
                $alumno->codigo_postal = $user->address->codigo_postal;
            }

            // La vista usa carrera_id y docs directamente en $alumno.
            if ($user->academicProfile) {
                $alumno->carrera_id = $user->academicProfile->career_id;
                $alumno->semestre = $user->academicProfile->semestre;
                $alumno->status = $user->academicProfile->status;
                $alumno->doc_acta_nacimiento = $user->academicProfile->doc_acta_nacimiento;
                $alumno->doc_certificado_prepa = $user->academicProfile->doc_certificado_prepa;
                $alumno->doc_curp = $user->academicProfile->doc_curp;
                $alumno->doc_ine = $user->academicProfile->doc_ine;
                $alumno->doc_acta_rechazado = $user->academicProfile->doc_acta_rechazado;
                $alumno->doc_certificado_rechazado = $user->academicProfile->doc_certificado_rechazado;
                $alumno->doc_curp_rechazado = $user->academicProfile->doc_curp_rechazado;
                $alumno->doc_ine_rechazado = $user->academicProfile->doc_ine_rechazado;
                $alumno->doc_ficha_pago = $user->academicProfile->doc_ficha_pago;
                $alumno->doc_factura_xml = $user->academicProfile->doc_factura_xml;
                $alumno->doc_ficha_pago_rechazado = $user->academicProfile->doc_ficha_pago_rechazado;
                $alumno->doc_factura_xml_rechazado = $user->academicProfile->doc_factura_xml_rechazado;
            }

            // Si el perfil aún no tiene carrera, usar la del lead CRM (misma CURP del aspirante).
            if (empty($alumno->carrera_id) && ! empty($user->curp)) {
                $normalize = fn (?string $value): string => strtoupper(preg_replace('/\s+/', '', (string) $value));
                $curpN = $normalize($user->curp);
                $leadCarrera = Lead::query()
                    ->whereRaw('UPPER(REPLACE(TRIM(IFNULL(alumno_curp, \'\')), \' \', \'\')) = ?', [$curpN])
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id')
                    ->first(['carrera_id', 'semestre']);

                if ($leadCarrera) {
                    if (! empty($leadCarrera->carrera_id)) {
                        $alumno->carrera_id = $leadCarrera->carrera_id;
                    }
                    if (empty($alumno->semestre) && $leadCarrera->semestre !== null) {
                        $alumno->semestre = $leadCarrera->semestre;
                    }
                }
            }

            $modoReinscripcion = false;

            // Bloqueo real de edición: si ya existe un registro en espera ("Pendiente")
            // mostramos un modal/overlay y deshabilitamos el formulario.
            $bloqueadoPorAceptacion = Enrollment::where('user_id', $user->id)
                ->where('status', 'Pendiente')
                ->exists();

            // Si Control Escolar rechazó algún documento, el aspirante debe poder subir archivos de nuevo:
            // no aplicar este bloqueo (sigue en Pendiente pero el formulario debe estar usable).
            if ($bloqueadoPorAceptacion && $user->academicProfile) {
                $p = $user->academicProfile;
                if (
                    ! empty($p->doc_acta_rechazado)
                    || ! empty($p->doc_certificado_rechazado)
                    || ! empty($p->doc_curp_rechazado)
                    || ! empty($p->doc_ine_rechazado)
                    || ! empty($p->doc_ficha_pago_rechazado)
                    || ! empty($p->doc_factura_xml_rechazado)
                ) {
                    $bloqueadoPorAceptacion = false;
                }
            }

            // Si el alumno ya fue aceptado (Enrollment en estado "Inscrito"),
            // ya no debe mostrarse el formulario de inscripción.
            $yaAceptado = Enrollment::where('user_id', $user->id)
                ->where('status', 'Inscrito')
                ->exists();

            if ($yaAceptado && !$bloqueadoPorAceptacion) {
                return redirect()->route('dashboard');
            }

            // Aspirante con envío pendiente y sin rechazos:
            // mostrar vista de espera (sin formulario).
            if ($bloqueadoPorAceptacion) {
                return view('layouts.ControlEsc.Inscripcion.espera');
            }
        }

        return view('layouts.ControlEsc.Inscripcion.index', compact(
            'carreras',
            'periodoActivo',
            'periods',
            'conceptosDisponibles',
            'alumno',
            'modoReinscripcion',
            'bloqueadoPorAceptacion'
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
            $rules['email'] = 'required|email|unique:users,email,' . $request->existing_user_id;
            $rules['curp'] = 'required|string|size:18|unique:users,curp,' . $request->existing_user_id;
        }

        $perfilPreCheck = $request->filled('existing_user_id')
            ? AcademicProfile::where('user_id', $request->existing_user_id)->first()
            : null;
        $soloCorregirFacturaRechazada = $perfilPreCheck && (
            ! empty($perfilPreCheck->doc_ficha_pago_rechazado) || ! empty($perfilPreCheck->doc_factura_xml_rechazado)
        );
        $subeArchivoFactura = $request->hasFile('archivo') || $request->hasFile('archivo_xml');

        // Factura obligatoria en inscripción nueva; si solo corrigen ficha o factura PDF rechazados, pueden enviar archivos sin nueva factura.
        if (! $request->has('generar_factura')) {
            if (! ($soloCorregirFacturaRechazada && $subeArchivoFactura)) {
                $redirect = $request->filled('modal')
                    ? redirect()->route('escolar.inscripcion.create', ['modal' => 1])
                    : back();

                return $redirect->withInput()->withErrors([
                    'generar_factura' => 'La ficha de pago/factura es obligatoria. Por favor, marque la casilla.',
                ]);
            }
        }

        if ($request->has('generar_factura')) {
            $rules['period_id'] = 'required';
            $rules['concepto'] = 'required';
            $rules['monto'] = 'required';
            $rules['status'] = 'required';
        }

        if ($request->hasFile('archivo')) {
            $rules['archivo'] = 'file|mimes:pdf|max:5120';
        }
        if ($request->hasFile('archivo_xml')) {
            $rules['archivo_xml'] = 'file|mimes:pdf|max:5120';
        }

        $messages = [
            'curp.required' => 'El campo CURP es obligatorio.',
            'curp.size' => 'La CURP debe tener exactamente 18 caracteres.',
            'curp.unique' => 'Esta CURP ya está registrada.',
            'monto.required' => 'No se pudo validar el monto de la inscripción. Revise carrera y concepto, o por favor reinténtelo más tarde.',
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
                    'nombre' => $request->nombre,
                    'apellido_paterno' => $request->apellido_paterno,
                    'apellido_materno' => $request->apellido_materno,
                    'email' => $request->email,
                    'curp' => $request->curp,
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
            $docRechazoPorCampo = [
                'doc_acta_nacimiento' => 'doc_acta_rechazado',
                'doc_certificado_prepa' => 'doc_certificado_rechazado',
                'doc_curp' => 'doc_curp_rechazado',
                'doc_ine' => 'doc_ine_rechazado',
                'doc_ficha_pago' => 'doc_ficha_pago_rechazado',
                'doc_factura_xml' => 'doc_factura_xml_rechazado',
            ];

            $perfilPrev = AcademicProfile::where('user_id', $user->id)->first();
            $rutasDocs = $this->subirDocumentos($request, $user->id);
            if (! $request->has('generar_factura')) {
                $rutasDocs = array_merge($rutasDocs, $this->subirDocumentosFacturacion($request, $user->id));
            }
            foreach ($rutasDocs as $campo => $nuevaRuta) {
                if ($perfilPrev && $perfilPrev->$campo && $perfilPrev->$campo !== $nuevaRuta) {
                    Storage::disk('public')->delete($perfilPrev->$campo);
                }
            }

            // 2. Preparar datos base
            // Inscripción / envío de formulario: perfil queda en Aspirante hasta que Control Escolar
            // acepte (studentController::acceptAspirante asigna status Alumno).
            $statusPerfil = 'Aspirante';

            $datosPerfil = [
                'career_id' => $request->carrera_id, 
                'semestre' => $perfilPrev?->semestre ?? 1,
                'status' => $statusPerfil,
                'is_anfitrion' => false,
            ];

            // 3. Conservar documentos y marcas no reemplazados en esta petición (re-subida parcial).
            if ($perfilPrev) {
                foreach ($docRechazoPorCampo as $campo => $flagCol) {
                    if (! array_key_exists($campo, $rutasDocs)) {
                        $datosPerfil[$campo] = $perfilPrev->$campo;
                        $datosPerfil[$flagCol] = $perfilPrev->$flagCol;
                    }
                }
            }

            $datosPerfil = array_merge($datosPerfil, $rutasDocs);
            foreach (array_keys($rutasDocs) as $campo) {
                if (isset($docRechazoPorCampo[$campo])) {
                    $datosPerfil[$docRechazoPorCampo[$campo]] = false;
                }
            }

            $perfil = AcademicProfile::updateOrCreate(
                ['user_id' => $user->id],
                $datosPerfil
            );

            $this->syncLeadFromStudentDocumentUpload($user, $rutasDocs, $docRechazoPorCampo);

            $this->upsertPendingEnrollmentForUser($user, (int) $request->carrera_id, $periodoActivo, $perfil);

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
                $facturaParaPerfil = array_filter([
                    'doc_ficha_pago' => $billingPaths['archivo'] ?? null,
                    'doc_factura_xml' => $billingPaths['archivo_xml'] ?? null,
                ], fn ($v) => $v !== null && $v !== '');
                if ($facturaParaPerfil !== []) {
                    $perfil->refresh();
                    $perfil->update(array_merge($facturaParaPerfil, [
                        'doc_ficha_pago_rechazado' => false,
                        'doc_factura_xml_rechazado' => false,
                    ]));
                    $this->syncLeadFromStudentDocumentUpload($user, $facturaParaPerfil, $docRechazoPorCampo);
                }
                $mensajeExtra = " Ficha de pago generada (Folio: $uidFinal).";
            }

            DB::commit();
            if ($request->filled('modal')) {
                return redirect()->route('escolar.inscripcion.create', ['modal' => 1, 'success' => 1])
                    ->with('success', 'Aspirante registrado correctamente.' . $mensajeExtra);
            }

            // Si el rol activo es "estudiante" (o se está actualizando un usuario existente),
            // evitamos redirigir a la lista de alumnos (403 para estudiante).
            $isEstudiante = strtolower((string) session('active_role_name')) === 'estudiante';
            if ($isEstudiante || $request->filled('existing_user_id')) {
                $toInscripcion = redirect()->route('escolar.inscripcion.create');
                // El estudiante ya ve el overlay de espera / estado en la misma vista; sin banner verde.
                if (!$isEstudiante) {
                    $toInscripcion->with('success', 'Información actualizada correctamente.' . $mensajeExtra);
                }
                return $toInscripcion;
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

    /**
     * Una sola fila de inscripción en espera por aspirante: actualiza la Pendiente existente
     * o crea una; elimina duplicados Pendiente previos (mismo user_id).
     */
    private function upsertPendingEnrollmentForUser(User $user, int $carreraId, Period $periodoActivo, AcademicProfile $perfil): void
    {
        $attrs = [
            'career_id' => $carreraId,
            'semestre' => $perfil->semestre ?? 1,
            'periodo' => $periodoActivo->id,
            'status' => 'Pendiente',
            'doc_acta_nacimiento' => $perfil->doc_acta_nacimiento,
            'doc_certificado_prepa' => $perfil->doc_certificado_prepa,
            'doc_curp' => $perfil->doc_curp,
            'doc_ine' => $perfil->doc_ine,
        ];

        $pendientes = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('status', 'Pendiente')
            ->orderBy('id')
            ->get();

        if ($pendientes->isEmpty()) {
            Enrollment::create(array_merge($attrs, ['user_id' => $user->id]));

            return;
        }

        $principal = $pendientes->first();
        $principal->update($attrs);

        foreach ($pendientes->skip(1) as $duplicado) {
            $duplicado->delete();
        }
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

    /**
     * Misma convención que expediente, en carpeta facturacion (PDF ficha + PDF factura).
     * Si ya se usó generar_factura + subirArchivosFactura, no llamar esto en el mismo request.
     */
    private function subirDocumentosFacturacion(Request $request, int $userId): array
    {
        $rutas = [];
        if ($request->hasFile('archivo')) {
            $rutas['doc_ficha_pago'] = $request->file('archivo')->store("documentos/{$userId}/facturacion", 'public');
        }
        if ($request->hasFile('archivo_xml')) {
            $rutas['doc_factura_xml'] = $request->file('archivo_xml')->store("documentos/{$userId}/facturacion", 'public');
        }

        return $rutas;
    }

    /**
     * Tras subir documentos desde inscripción, replica rutas en el lead CRM (misma CURP)
     * y limpia marcas de rechazo para que Control Escolar vea el archivo nuevo.
     */
    private function syncLeadFromStudentDocumentUpload(User $user, array $rutasDocs, array $docRechazoPorCampo): void
    {
        if ($rutasDocs === []) {
            return;
        }
        $normalize = fn (?string $value): string => strtoupper(preg_replace('/\s+/', '', (string) $value));
        $curpN = $normalize($user->curp ?? '');
        if ($curpN === '') {
            return;
        }

        $lead = Lead::query()
            ->whereRaw('UPPER(REPLACE(TRIM(IFNULL(alumno_curp, \'\')), \' \', \'\')) = ?', [$curpN])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if (! $lead) {
            return;
        }

        $lead->refresh();

        $final = [];
        foreach ($docRechazoPorCampo as $campo => $flagCol) {
            $final[$campo] = array_key_exists($campo, $rutasDocs)
                ? $rutasDocs[$campo]
                : $lead->$campo;
            $final[$flagCol] = array_key_exists($campo, $rutasDocs)
                ? false
                : (bool) $lead->$flagCol;
        }

        $lead->update($final);
    }

    /** PDF ficha y PDF factura (campo request archivo_xml; columna perfil doc_factura_xml). */
    private function subirArchivosFactura($request, $userId) {
        $rutas = [];
        if ($request->hasFile('archivo')) {
            $rutas['archivo'] = $request->file('archivo')->store("facturas/{$userId}", 'public');
        }
        if ($request->hasFile('archivo_xml')) {
            $rutas['archivo_xml'] = $request->file('archivo_xml')->store("facturas/{$userId}", 'public');
        }
        return $rutas;
    }
}