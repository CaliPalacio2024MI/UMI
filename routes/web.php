<?php

use Illuminate\Support\Facades\Route;

// --- Controladores de Autenticación y Globales ---
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Users\ContextController;
use App\Http\Controllers\MiInformacion\MiInformacionController;
use App\Http\Controllers\Ajustes\AjustesController;
use App\Http\Controllers\ExternalDataController;

// --- Controladores LMS (Cursos) ---
use App\Http\Controllers\Cursos\CourseController;
use App\Http\Controllers\Cursos\TopicsController;
use App\Http\Controllers\Cursos\SubtopicsController;
use App\Http\Controllers\Cursos\ActivitiesController;
use App\Http\Controllers\Cursos\CompletionController;
use App\Http\Controllers\Cursos\ProgressController;
use App\Http\Controllers\Cursos\CoursePeriodsController;

// --- Controladores de Facturación ---
use App\Http\Controllers\Facturacion\BillingController;
use App\Http\Controllers\Facturacion\PaymentController;
use App\Http\Controllers\Facturacion\BillingConceptController;

// --- Controladores Administrativos y Escolares ---
use App\Http\Controllers\Control_admin\ControlAdministrativoController;
use App\Http\Controllers\AdmonCont\HorarioController;
use App\Http\Controllers\AdmonCont\ClaseController;
use App\Http\Controllers\AdmonCont\FacilityController;
use App\Http\Controllers\AdmonCont\store\studentController;
use App\Http\Controllers\AdmonCont\store\careerController;
use App\Http\Controllers\AdmonCont\MateriaController;
use App\Http\Controllers\AdmonCont\store\teacherController;
use App\Http\Controllers\SchoolarCont\InscripcionController;
use App\Http\Controllers\SchoolarCont\MatriculaController;
use App\Http\Controllers\SchoolarCont\BoletaCalificacionController;
use App\Http\Controllers\SchoolarCont\BecasController;
use App\Http\Controllers\SchoolarCont\TitulacionController;

// --- Controladores CRM ---
use App\Http\Controllers\CRM\CRMController;

// --- Formulario CRM ---
use App\Http\Controllers\Public\LeadPublicController;

//Grupos
use App\Http\Controllers\GroupsController;
use App\Http\Controllers\Api\GroupDataController;
use App\Http\Controllers\WorkstationController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\Cursos\CourseSessionController;
use App\Http\Controllers\AttendanceController;








// ==========================================================================
// 1. ACCESO PÚBLICO
// ==========================================================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::redirect('/', '/registro-publico');

// FORMULARIO PÚBLICO

Route::get('/registro-publico', [LeadPublicController::class, 'create'])->name('public.inscripcion.create');

Route::post('/registro-publico', [LeadPublicController::class, 'store'])
    ->middleware('throttle:3,15')
    ->name('public.inscripcion.store');


// ==========================================================================
// 2. PLATAFORMA GENERAL (Usuarios Autenticados)
// ==========================================================================
Route::middleware(['auth', 'ajax', 'spa'])->group(function () {

    // --- Contexto (Cambio de Rol/Institución) ---
    Route::match(['get', 'post'], '/set-context', [ContextController::class, 'setContext'])->name('context.set');
    Route::match(['get', 'post'], '/context/switch/{institutionId}/{roleId}', [ContextController::class, 'setContext'])->name('context.switch');

    // --- Dashboard ---
    Route::get('/bienvenido', function () { return view('Dashboard.index'); })->name('dashboard');

    // --- Módulo: Mi Información (Perfil) ---
    Route::prefix('mi-informacion')->name('MiInformacion.')->group(function () {
        Route::get('/', [MiInformacionController::class, 'index'])->name('index');

        // Submódulos para Alumnos y Docentes
        Route::middleware(['role:estudiante,docente,master'])->group(function () {
            Route::get('/clases', [MiInformacionController::class, 'showClases'])->name('clases');
            Route::get('/horario', [MiInformacionController::class, 'showHorario'])->name('horario');
            Route::get('/historial', [MiInformacionController::class, 'showHistorial'])->name('historial');
        });
    });

    // --- Módulo: Facturación (solo contexto Universidad Mundo Imperial) ---
    Route::middleware(['university.institution'])->group(function () {
        Route::get('/facturacion', [BillingController::class, 'index'])->name('Facturacion.index');
    });

    // ======================================================================
    // 3. GESTIÓN ACADÉMICA (Docentes y Master)
    // ¡IMPORTANTE! Definir esto ANTES de las rutas genéricas de cursos
    // para evitar que /cursos/{course} capture /cursos/crear
    // ======================================================================
    Route::middleware(['role:master,docente'])->group(function () {
        // Gestión de Cursos
        Route::get('/cursos/crear', [CourseController::class, 'create'])->name('courses.create');
        Route::post('/cursos', [CourseController::class, 'store'])->name('courses.store');
        Route::get('/cursos/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('/cursos/{course}', [CourseController::class, 'update'])->name('courses.update');
        Route::delete('/cursos/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');

        // Lista de asistencia
        Route::get('/cursos/{course}/asistencia', [CourseController::class, 'attendance'])->name('courses.attendance')->middleware(['role:master,docente,gerente_capacitacion']);
        Route::get('/cursos/{course}/asistencia/pdf', [CourseController::class, 'exportAttendancePDF'])->name('courses.attendance.pdf')->middleware(['role:master,docente,gerente_capacitacion']);

        // Gestión de períodos
        Route::prefix('cursos/{course}/periodos')->name('courses.periods.')->group(function () {
        Route::get('/', [CoursePeriodsController::class, 'index'])->name('index');
        Route::post('/', [CoursePeriodsController::class, 'store'])->name('store');
        Route::put('/{period}', [CoursePeriodsController::class, 'update'])->name('update');
        Route::delete('/{period}', [CoursePeriodsController::class, 'destroy'])->name('destroy');
        Route::patch('/{period}/toggle', [CoursePeriodsController::class, 'toggle'])->name('toggle');

        // Gestión de usuarios del período
        Route::get('/{period}/usuarios', [CoursePeriodsController::class, 'users'])->name('users');
        Route::post('/{period}/usuarios/toggle', [CoursePeriodsController::class, 'toggleUser'])->name('toggleUser');

        // Asistencia del período
        Route::get('/{period}/asistencia', [CoursePeriodsController::class, 'attendance'])->name('attendance');})->middleware(['role:master,docente,gerente_capacitacion']);

        //Biblioteca de Temas (Plantillas)
        Route::get('/biblioteca-temas', [\App\Http\Controllers\TopicTemplateController::class, 'index'])->name('templates.index');
        Route::get('/biblioteca-temas/crear', [\App\Http\Controllers\TopicTemplateController::class, 'create'])->name('templates.create');
        Route::post('/biblioteca-temas', [\App\Http\Controllers\TopicTemplateController::class, 'store'])->name('templates.store');
        //Ruta para mostrar el formulario de edicion
        Route::get('/biblioteca-temas/{id}/editar',[\App\Http\Controllers\TopicTemplateController::class, 'edit'])->name('templates.edit');
        //Ruta para procesar la actualizacion
        Route::put('/biblioteca-temas/{id}', [\App\Http\Controllers\TopicTemplateController::class, 'update'])->name('templates.update');
        //Ruta para eliminar
        Route::delete('/biblioteca-temas/{id}', [\App\Http\Controllers\TopicTemplateController::class, 'destroy'])->name('templates.destroy');


        //Biblioteca de Subtemas (Plantillas)
        Route::get('/biblioteca-subtemas', [\App\Http\Controllers\SubtopicTemplateController::class, 'index'])->name('subtopics_template.index');
        Route::get('/biblioteca-subtemas/crear', [\App\Http\Controllers\SubtopicTemplateController::class, 'create'])->name('subtopics_template.create');
        Route::post('/biblioteca-subtemas', [\App\Http\Controllers\SubtopicTemplateController::class, 'store'])->name('subtopics_template.store');
        //Ruta para mostrar el formulario de edicion
        Route::get('/biblioteca-subtemas/{id}/editar',[\App\Http\Controllers\SubtopicTemplateController::class, 'edit'])->name('subtopics_template.edit');
        //Ruta para procesar la actualizacion (usa PUT o PATCH)
        Route::put('/biblioteca-subtemas/{id}', [\App\Http\Controllers\SubtopicTemplateController::class, 'update'])->name('subtopics_template.update');
        //Ruta para eliminar
        Route::delete('/biblioteca-subtemas/{id}', [\App\Http\Controllers\SubtopicTemplateController::class, 'destroy'])->name('subtopics_template.destroy');

        // Temas y Subtemas
        Route::get('/cursos/{course}/temas/crear', [TopicsController::class, 'create'])->name('course.topic.create');
        Route::post('/temas', [TopicsController::class, 'store'])->name('topics.store');
        Route::get('/temas/{topic}/edit', [TopicsController::class, 'edit'])->name('topics.edit');
        Route::put('/temas/{topic}', [TopicsController::class, 'update'])->name('topics.update');
        Route::delete('/temas/{topic}', [TopicsController::class, 'destroy'])->name('topics.destroy');

        // routes/web.php
        Route::post('/cursos/{course}/welcome', [CourseController::class, 'updateWelcome'])->name('course.update.welcome')->middleware('auth');

        Route::resource('topics.subtopics', SubtopicsController::class);
        Route::delete('/subtopics/{subtopic}', [SubtopicsController::class, 'destroy'])->name('subtopics.destroy');
        Route::post('/topics/update-order', [TopicsController::class, 'updateOrder'])->name('topics.updateOrder');
        Route::post('/activities/update-order', [ActivitiesController::class, 'updateOrder'])->name('activities.updateOrder');

        // Actividades (Gestión)
        Route::post('/actividades', [ActivitiesController::class, 'store'])->name('activities.store');
        Route::delete('/actividades/{activity}', [ActivitiesController::class, 'destroy'])->name('activities.destroy');

        //Lector QR
        Route::post('/scan-qr', [AttendanceController::class, 'scanQr']);
        Route::post('/sessions/{session}/attendance/qr', [CourseSessionController::class, 'storeQrAttendance'])->name('sessions.attendance.qr');

        // Grupos
        Route::post('/api/workstations-by-departments', [WorkstationController::class, 'byDepartments']);
        Route::post('/groups', [GroupsController::class, 'store'])->name('groups.store');
        Route::delete('/groups/{group}', [GroupsController::class, 'destroy'])->name('groups.destroy');
        Route::get('/groups/{group}/edit', [GroupsController::class, 'edit'])->name('groups.edit');
        Route::put('/groups/{group}', [GroupsController::class, 'update'])->name('groups.update');
        Route::get('/session/{id}/participants', function ($id) {$group = \App\Models\Group::whereHas('sessions', function ($q) use ($id) {$q->where('course_session_id', $id);})->with('participants')->first();return response()->json($group ? $group->participants : []);});
        Route::post('/groups/add-participants', [App\Http\Controllers\GroupsController::class, 'addParticipants'])->name('groups.addParticipants');
        Route::post('/sessions/group/store', [CourseSessionController::class, 'storeGroup'])->name('sessions.group.store');
        Route::post('/groups/participants-by-filters', [GroupsController::class, 'getParticipantsByFilters'])->name('get.participants.by.filters');
        Route::get('/sync-properties', [ExternalDataController::class, 'syncProperties']);
        Route::get('/sync-departments/{id}', [ExternalDataController::class, 'syncDepartments']);
        Route::get('/sync-positions/{property}/{department}', [ExternalDataController::class, 'syncPositions']);
        Route::get('/groups/{session}/export-pdf',[GroupsController::class, 'exportPdf'])->name('groups.export.pdf');

        //Virtual grupos
        Route::get('/cursos/{course}/periodos/{period}/usuarios',[CoursePeriodsController::class, 'usersIndex'])->name('courses.periods.users.index');
        Route::post('/cursos/periodos/usuarios/guardar',[CoursePeriodsController::class, 'usersStore'])->name('courses.periods.users.store');
        Route::get('/cursos/{course}/asistencias/virtual/pdf', [CoursePeriodsController::class, 'attendancePdf'])->name('courses.attendance.virtual.pdf');
        // Ruta para mostrar el formulario de creación de grupos
        Route::get('/sessions/{sessionId}/groups/create', [GroupsController::class, 'create'])->name('groups.create');
        Route::get('/sync-properties', [ExternalDataController::class, 'syncProperties']);
        Route::get('/sync-departments/{id}', [ExternalDataController::class, 'syncDepartments']);
        Route::get('/sync-positions/{property}/{department}', [ExternalDataController::class, 'syncPositions']);

        // Vista principal
        Route::get('/groups', [GroupsController::class, 'index'])->name('groups.index');
        // Ruta para obtener participantes filtrados (AJAX)
        Route::post('/get-participants-by-filters', [GroupsController::class, 'getParticipantsByFilters'])->name('get.participants.by.filters');

        // Ruta para ver los detalles del grupo
        Route::get('/groups/{group}/session/{sessionId}', [GroupsController::class, 'show'])->name('groups.show');

        // Ruta alternativa para ver grupo sin especificar sesión
        Route::get('/groups/{group}/details', [GroupsController::class, 'show'])->name('groups.details');

        //  AJAX
        Route::get('/departments/{id}/workstations', [GroupDataController::class, 'workstations']);
        Route::get('/workstations/{id}/participants', [GroupDataController::class, 'participants']);
        Route::get('/practicantes', [PractitionerController::class, 'filter']) ->name('practicantes.filter');
        Route::get('/get-practicantes', [PractitionerController::class, 'getPracticantes']) ->name('get.practicantes');
        Route::get('/sessions/{id}/group-data', [CourseSessionController::class, 'getGroupData']);
        Route::get('/sessions/{session}/groups', [CourseSessionController::class, 'groups'])->name('sessions.groups');

    });
    // ===============================
    // HORARIOS (SESIONES PRESENCIALES)
    // ===============================
    Route::get('/cursos/{course}/horarios', [\App\Http\Controllers\Cursos\CourseSessionController::class, 'index'])->name('courses.sessions.index');
    Route::post('/cursos/{course}/horarios', [\App\Http\Controllers\Cursos\CourseSessionController::class, 'store'])->name('courses.sessions.store');
    Route::delete('/courses/{course}/sessions/{session}',[CourseSessionController::class, 'destroy'])->name('courses.sessions.destroy');
    Route::patch('/cursos/{course}/horarios/{session}/toggle',[\App\Http\Controllers\Cursos\CourseSessionController::class, 'toggle'])->name('courses.sessions.toggle');
    Route::put('/cursos/{course}/horarios/{session}',[\App\Http\Controllers\Cursos\CourseSessionController::class, 'update'])->name('courses.sessions.update');
    Route::resource('courses', CourseController::class);

    // --- Módulo: Cursos (Vista y Realización - Alumnos y General) ---
    // Estas rutas atrapan {course}, por eso van AL FINAL de la sección de cursos
    Route::get('/cursos', [CourseController::class, 'index'])->name('Cursos.index');
    Route::get('/cursos/{course}', [CourseController::class, 'show'])->name('course.show');
    Route::get('/cursos/{course}/certificado', [CourseController::class, 'showCertificate'])->name('courses.certificate');
    Route::get('/mis-certificados', [CourseController::class, 'myCertificates'])->name('courses.certificates.index');
    // Guardar progreso
    Route::post('/cursos/{course}/save-progress', [CourseController::class, 'saveProgress'])->name('courses.saveProgress');
    Route::post('/activities/{activity}/submit', [ActivitiesController::class, 'submit'])->name('activities.submit');




    // Acciones del Alumno
    Route::post('/cursos/{course}/inscribir', [CourseController::class, 'enroll'])->name('courses.enroll');
    Route::post('/cursos/{course}/desinscribir', [CourseController::class, 'unenroll'])->name('courses.unenroll');
    Route::post('/completions/mark', [CompletionController::class, 'mark'])->name('completions.mark');
    Route::post('/actividades/{activity}/submit', [ActivitiesController::class, 'submit'])->name('activities.submit');



    // ======================================================================
    // 4. ZONA ADMINISTRATIVA (Master y Control Administrativo)
    // ======================================================================
    Route::middleware(['role:master,control_administrativo'])->group(function () {

    Route::middleware(['university.institution'])->group(function () {
        // --- Gestión de Facturación (Cobros y Pagos) ---
        Route::post('/facturacion', [BillingController::class, 'store'])->name('Facturacion.store');
        Route::delete('/facturacion/{billing}', [BillingController::class, 'destroy'])->name('Facturacion.destroy');
        Route::post('facturacion/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/facturacion/export', [BillingController::class, 'exportCsv'])->name('Facturacion.export');
        // Grupo de rutas para Conceptos de Facturación
        Route::prefix('facturacion/conceptos')->name('facturacion.conceptos.')->group(function () {
            Route::get('/', [BillingConceptController::class, 'index'])->name('index');
            Route::post('/', [BillingConceptController::class, 'store'])->name('store');
            Route::put('/{id}', [BillingConceptController::class, 'update'])->name('update');
            Route::delete('/{id}', [BillingConceptController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/toggle-status', [BillingConceptController::class, 'toggleStatus'])->name('toggleStatus');
        });
    });
        // ------------------------------------------------------------
        // A. CONTROL ESCOLAR (Flujo de Ingreso)
        // ------------------------------------------------------------
        Route::prefix('control-escolar')->name('escolar.')->group(function () {

            Route::get('/inicio', [ControlAdministrativoController::class, 'showEscolar'])->name('dashboard');

            // 1. Inscripción / Reinscripción
            // NOTA: Las rutas de Inscripción están registradas abajo en un grupo aparte
            // para permitir también el rol activo "estudiante".

            // 2. Lista de Alumnos (Gestión y Contraseña)
            Route::get('/lista-alumnos', [studentController::class, 'index'])->name('students.index');
            Route::get('/lista-alumnos/export', [studentController::class, 'export'])->name('students.export');
            Route::get('/lista-alumnos/{id}/horarios', [studentController::class, 'horarios'])->name('students.horarios');
            Route::get('/lista-alumnos/{id}/edit', [studentController::class, 'edit'])->name('students.edit');
            Route::put('/lista-alumnos/{id}', [studentController::class, 'update'])->name('students.update');
            Route::delete('/lista-alumnos/{id}', [studentController::class, 'destroy'])->name('students.destroy');
            Route::post('/lista-alumnos/aspirantes/{lead}/aceptar', [studentController::class, 'acceptAspirante'])->name('students.acceptAspirante');
            Route::post('/lista-alumnos/leads/{lead}/alumno-email', [studentController::class, 'syncAlumnoEmailFromModal'])->name('students.syncAlumnoEmail');
            Route::post('/lista-alumnos/leads/{lead}/expediente', [studentController::class, 'updateLeadExpediente'])->name('students.updateLeadExpediente');

            // 3. Matrículas
            Route::get('/matriculas', [MatriculaController::class, 'index'])->name('matriculas.index');
            Route::get('/matriculas/{id}', [MatriculaController::class, 'show'])->name('matriculas.show');
            Route::post('/matriculas/{id}/asignar', [MatriculaController::class, 'store'])->name('matriculas.store');
            Route::put('/matriculas/{id}', [MatriculaController::class, 'update'])->name('matriculas.update');
            Route::delete('/matriculas/{id}', [MatriculaController::class, 'destroy'])->name('matriculas.destroy');
            Route::post('/Matriculas/{id}/upload', [MatriculaController::class, 'uploadDocumento'])->name('documentacion.upload');

            // 4. Futuros Módulos (Becas, Titulación...)
            Route::get('/becas', [BecasController::class, 'index'])->name('becas.index');
            Route::post('/becas/documentos', [BecasController::class, 'store'])->name('becas.store');
            Route::get('/becas/documentos/{id}', [BecasController::class, 'show'])->name('becas.show');
            Route::put('/becas/documentos/{id}', [BecasController::class, 'update'])->name('becas.update');
            Route::delete('/becas/documentos/{id}', [BecasController::class, 'destroy'])->name('becas.destroy');
            Route::get('/becas/documentos/{id}/download', [BecasController::class, 'download'])->name('becas.download');

            Route::get('/titulacion', [TitulacionController::class, 'index'])->name('titulacion.index');
            Route::post('/titulacion/documentos', [TitulacionController::class, 'store'])->name('titulacion.store');
            Route::get('/titulacion/documentos/{id}', [TitulacionController::class, 'show'])->name('titulacion.show');
            Route::put('/titulacion/documentos/{id}', [TitulacionController::class, 'update'])->name('titulacion.update');
            Route::delete('/titulacion/documentos/{id}', [TitulacionController::class, 'destroy'])->name('titulacion.destroy');
            Route::get('/titulacion/documentos/{id}/download', [TitulacionController::class, 'download'])->name('titulacion.download');

            Route::get('/boletas-calificaciones', [BoletaCalificacionController::class, 'index'])->name('boletas.index');
            Route::get('/boletas-calificaciones/export', [BoletaCalificacionController::class, 'export'])->name('boletas.export');
        });


        // ------------------------------------------------------------
        // B. CONTROL ADMINISTRATIVO / ACADÉMICO (Infraestructura)
        // ------------------------------------------------------------
        Route::prefix('control-administrativo')->name('control.')->group(function () {

            Route::get('/academico', [ControlAdministrativoController::class, 'showAcademico'])->name('academico');
            Route::get('/planeacion', [ControlAdministrativoController::class, 'showPlaneacion'])->name('planeacion');

            // Docentes
            Route::get('/lista-docentes', [teacherController::class, 'index'])->name('teachers.index');
            Route::get('/lista-docentes/export', [teacherController::class, 'export'])->name('teachers.export');
            Route::get('/lista-docentes/registro', [teacherController::class, 'form'])->name('teachers.form');
            Route::get('/lista-docentes/list-for-register', [teacherController::class, 'listForRegister'])->name('teachers.list-for-register');
            Route::post('/lista-docentes/create', [teacherController::class, 'store'])->name('teachers.store');
            Route::get('/lista-docentes/{id}/horarios', [teacherController::class, 'horarios'])->name('teachers.horarios');
            Route::get('/lista-docentes/{id}', [teacherController::class, 'show'])->name('teachers.show');
            Route::get('/lista-docentes/{id}/edit', [teacherController::class, 'edit'])->name('teachers.edit');
            Route::put('/lista-docentes/{id}', [teacherController::class, 'update'])->name('teachers.update');
            Route::delete('/lista-docentes/{id}', [teacherController::class, 'destroy'])->name('teachers.destroy');

            // Carreras y Materias
            Route::get('/carreras', [careerController::class, 'index'])->name('careers.index');
            Route::get('/carreras/create',[careerController::class,'create'])->name('careers.create');
            Route::get('/carreras/{carrera}/reticula', [careerController::class, 'reticula'])->name('careers.reticula');
            Route::post('/carreras', [careerController::class, 'store'])->name('careers.store');
            Route::post('/carreras/clasificaciones', [careerController::class, 'storeClassification'])->name('careers.classifications.store');
            Route::delete('/carreras/clasificaciones/{careerClassification}', [careerController::class, 'destroyClassification'])->name('careers.classifications.destroy');
            Route::put('/carreras/{carrera}', [careerController::class, 'update'])->name('careers.update');
            Route::delete('/carreras/{carrera}', [careerController::class, 'destroy'])->name('careers.destroy');

            Route::get('/listas/materias', [MateriaController::class, 'index'])->name('subjects.index');
            Route::post('/listas/materias/create', [MateriaController::class, 'store'])->name('subjects.store');
            Route::put('/listas/materias/{registro}', [MateriaController::class, 'update'])->name('subjects.update');
            Route::delete('/listas/materias/{registro}', [MateriaController::class, 'destroy'])->name('subjects.destroy');

            // Aulas y Horarios
            Route::get('/aulas', [FacilityController::class, 'index'])->name('facilities.index');
            Route::get('/aulas/crear', [FacilityController::class, 'createForm'])->name('facilities.create');
            Route::post('/aulas', [FacilityController::class, 'store'])->name('facilities.store');
            Route::get('/aulas/materias-por-carrera', [FacilityController::class, 'materiasPorCarrera'])->name('facilities.materiasPorCarrera');
            Route::get('/aulas/{facility}/edit', [FacilityController::class, 'edit'])->name('facilities.edit');
            Route::put('/aulas/{facility}', [FacilityController::class, 'update'])->name('facilities.update');
            Route::get('/aulas/{facility}', [FacilityController::class, 'show'])->name('facilities.show');
            Route::delete('/aulas/{facility}', [FacilityController::class, 'destroy'])->name('facilities.destroy');

            Route::get('/horarios/{horario}/edit-data', [HorarioController::class, 'editData'])->name('schedules.editData');
            Route::get('/horarios/aulas-disponibles', [HorarioController::class, 'aulasDisponibles'])->name('schedules.aulasDisponibles');
            Route::resource('horarios', HorarioController::class)->names('schedules');

            // Clases (asignar alumnos a horarios) — Control Académico
            Route::get('/clases', [ClaseController::class, 'index'])->name('classes.index');
            Route::get('/clases/crear', [ClaseController::class, 'create'])->name('classes.create');
            Route::post('/clases', [ClaseController::class, 'store'])->name('classes.store');
            Route::post('/clases/guardar-cajita', [ClaseController::class, 'guardarCajita'])->name('classes.guardar-cajita');
            Route::get('/clases/{clase}', [ClaseController::class, 'show'])->name('classes.show');
            Route::get('/clases/{clase}/editar', [ClaseController::class, 'edit'])->name('classes.edit');
            Route::put('/clases/{clase}', [ClaseController::class, 'update'])->name('classes.update');
            Route::delete('/clases/{clase}', [ClaseController::class, 'destroy'])->name('classes.destroy');
            Route::post('/clases/{clase}/inscribir-todos', [ClaseController::class, 'inscribirTodos'])->name('classes.inscribir-todos');

            // Espejo de Alumnos para Admin
            Route::get('/lista-estudiantes', [studentController::class, 'index'])->name('students.index');
            Route::get('/lista-estudiantes/export', [studentController::class, 'export'])->name('students.export');
            Route::get('/lista-estudiantes/{id}/horarios', [studentController::class, 'horarios'])->name('students.horarios');
            Route::get('/lista-estudiantes/{id}/edit', [studentController::class, 'edit'])->name('students.edit');
            Route::put('/lista-estudiantes/{id}', [studentController::class, 'update'])->name('students.update');
            Route::delete('/lista-estudiantes/{id}', [studentController::class, 'destroy'])->name('students.destroy');
            Route::post('/lista-estudiantes/leads/{lead}/alumno-email', [studentController::class, 'syncAlumnoEmailFromModal'])->name('students.syncAlumnoEmailControl');
            Route::post('/lista-estudiantes/leads/{lead}/expediente', [studentController::class, 'updateLeadExpediente'])->name('students.updateLeadExpedienteControl');
        });




        // ------------------------------------------------------------
        // C. AJUSTES DEL SISTEMA
        // ------------------------------------------------------------
        Route::prefix('ajustes')->name('ajustes.')->group(function () {
            // Acciones Específicas (Toggles)
            Route::post('users/{id}/toggle-status', [AjustesController::class, 'toggleUserStatus'])->name('users.toggleStatus');
            Route::post('periods/{id}/toggle-status', [AjustesController::class, 'togglePeriodStatus'])->name('periods.toggleStatus');

            // CRUD Dinámico (Usuarios, Periodos, Departamentos, Puestos)
            Route::get('/{seccion}/create-form', [AjustesController::class, 'getCreateForm'])->name('getCreateForm');
            Route::get('/{seccion}/{id}/edit-form', [AjustesController::class, 'getEditForm'])->name('getEditForm');
            Route::post('/{seccion}', [AjustesController::class, 'store'])->name('store');
            Route::put('/{seccion}/{id}', [AjustesController::class, 'update'])->name('update');
            Route::delete('/{seccion}/{id}', [AjustesController::class, 'destroy'])->name('destroy');

            // Vista General (Al final por el comodín {seccion})
            Route::get('/{seccion}', [AjustesController::class, 'show'])->name('show');
        });

    }); // Fin Middleware Administrativo

}); // Fin Middleware Auth + Ajax + SPA


    // Endpoint para consumir la API externa
    Route::middleware(['auth', 'ajax', 'spa'])->group(function () {

        Route::get('/external-data',[ExternalDataController::class, 'index'])->name('external-data.index');

    });
    // ------------------------------------------------------------
    // Rutas de Inscripción accesibles también para Estudiante
    // (para que al entrar a su cuenta vea primero "Nuevo Registro de Aspirante")
    // ------------------------------------------------------------
    Route::middleware(['role:master,control_administrativo,estudiante'])
        ->prefix('control-escolar')->name('escolar.')->group(function () {
            Route::get('/inscripcion', [InscripcionController::class, 'index'])->name('inscripcion.index');
            Route::get('/inscripcion/nuevo', [InscripcionController::class, 'create'])->name('inscripcion.create');
            Route::post('/inscripcion/nuevo', [InscripcionController::class, 'store'])->name('inscripcion.store');
        });

            // =======================
            // MÓDULO CRM (INDEPENDIENTE)
            // =======================

            Route::prefix('crm')
            ->name('crm.')
            ->middleware(['role:master,coordinador_ctp,ctp,control_administrativo'])
            ->group(function () {

                // LEADS → todos
                Route::get('/leads', [CRMController::class, 'leads'])->name('leads');

                Route::post('/leads/{lead}/seguimiento', [CRMController::class, 'guardarSeguimiento']);

                // ESTADÍSTICAS → TODOS
                Route::get('/estadisticas', [CRMController::class, 'estadisticas'])->name('estadisticas');


                // SOLO Master y Coordinador
                Route::middleware(['role:master,coordinador_ctp,control_administrativo'])->group(function () {

                    Route::get('/prospectos', [CRMController::class, 'prospectos'])->name('prospectos');

                        // Comisiones: solo Master (ya está dentro del middleware master,coordinador_ctp,
                        // pero la vista solo la usa master
                        Route::middleware(['role:master,coordinador_ctp,control_administrativo'])->group(function () {
                            Route::get('/comisiones', [CRMController::class, 'comisiones'])->name('comisiones');
                            Route::post('/comisiones', [CRMController::class, 'storeComision'])->name('comisiones.store');
                            Route::put('/comisiones/{id}', [CRMController::class, 'updateComision'])->name('comisiones.update');
                            Route::delete('/comisiones/{id}', [CRMController::class, 'destroyComision'])->name('comisiones.destroy');
                            Route::get('/comisiones/filtrar', [CRMController::class, 'filtrarComisiones'])->name('comisiones.filtrar');
                            Route::get('/comisiones/{ctpId}/detalle', [CRMController::class, 'detalleComision'])->name('comisiones.detalle');
                        });

                    // ASIGNAR CTP
                    Route::post('/leads/{lead}/asignar-ctp', [CRMController::class, 'asignarCTP'])
                        ->name('leads.asignar_ctp');

                    Route::put('/leads/{lead}', [CRMController::class, 'update'])->name('leads.update');
                    Route::delete('/leads/{lead}', [CRMController::class, 'destroy'])
                        ->name('leads.destroy');

                });

                Route::get('/crm/estadisticas/data', [EstadisticasController::class, 'data'])->name('crm.estadisticas.data');

                Route::get('/estadisticas/exportar', [CRMController::class, 'exportar'])->name('estadisticas.exportar');

            });

