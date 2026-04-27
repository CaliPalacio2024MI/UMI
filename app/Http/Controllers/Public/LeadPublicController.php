<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use App\Models\Users\Career;
use App\Models\Users\CareerClassification;

class LeadPublicController extends Controller
{
    // Muestra el formulario público
    public function create()
    {
        $carreras = Career::orderBy('name')->get();
        $clasificaciones = CareerClassification::orderBy('name')->get();

        return view('public.inscripcion', compact('carreras', 'clasificaciones'));
    }

    // Guarda el lead que viene del formulario
public function store(Request $request)
{
    $request->validate([
        'tutor_curp' => 'required|string|max:18',
        'tutor_nombre' => 'required|string',
        'tutor_paterno' => 'required|string',
        'tutor_materno' => 'required|string',
        'telefono1' => 'required|numeric',
        'telefono2' => 'nullable|numeric',
        'tutor_email' => 'nullable|email',

        'alumno_curp' => 'required|string|max:18',
        'alumno_nombre' => 'required|string',
        'alumno_paterno' => 'required|string',
        'alumno_materno' => 'required|string',
        'carrera_id' => 'required|exists:careers,id',
    ]);

    $normalizeCurp = fn (?string $value): string => strtoupper(preg_replace('/\s+/', '', (string) $value));
    $alumnoCurpN = $normalizeCurp($request->alumno_curp);

    $payload = [
        'tutor_curp' => strtoupper($request->tutor_curp),
        'tutor_nombre' => $request->tutor_nombre,
        'tutor_paterno' => $request->tutor_paterno,
        'tutor_materno' => $request->tutor_materno,
        'telefono1' => $request->telefono1,
        'telefono2' => $request->telefono2,
        'tutor_email' => $request->tutor_email,
        'alumno_curp' => $alumnoCurpN,
        'alumno_nombre' => $request->alumno_nombre,
        'alumno_paterno' => $request->alumno_paterno,
        'alumno_materno' => $request->alumno_materno,
        'carrera_id' => $request->carrera_id,
    ];

    // Mismo aspirante (misma CURP): actualizar el lead existente, no duplicar fila.
    $existente = Lead::query()
        ->whereRaw('UPPER(REPLACE(TRIM(IFNULL(alumno_curp, \'\')), \' \', \'\')) = ?', [$alumnoCurpN])
        ->orderByDesc('id')
        ->first();

    if ($existente) {
        $existente->update($payload);

        return redirect()->back()->with('success', 'Registro actualizado correctamente.');
    }

    $lead = Lead::create(array_merge($payload, [
        'origen' => 'formulario_publico',
        'clasificacion' => 'Prospecto',
    ]));

    $lead->seguimientos()->create([
        'estado' => 'Prospecto',
        'fecha' => now()->toDateString(),
        'hora' => now()->toTimeString(),
    ]);

    return redirect()->back()->with('success', 'Registro enviado correctamente');
}

    // Muestra la página de inicio (Landing Page)
    public function landing()
    {
        return view('public.landing');
    }

    public function campus()
    {
        return view('public.campus');
    }

    public function programa($slug)
    {
        $programas = [
            'gastronomía' => [
                'nombre'      => 'Gastronomía',
                'descripcion' => 'Forma profesionales con visión empresarial en la industria gastronómica de lujo.',
                'duracion'    => '4 años',
                'modalidad'   => 'Presencial',
                'imagen'      => 'images/foto1.jpg',
                'competencias' => [
                    ['titulo' => 'Cocina profesional',    'descripcion' => 'Técnicas culinarias nacionales e internacionales de alto nivel.'],
                    ['titulo' => 'Gestión de restaurantes', 'descripcion' => 'Administración y operación de establecimientos gastronómicos.'],
                    ['titulo' => 'Enología y maridaje',   'descripcion' => 'Conocimiento de vinos y su armonización con platillos.'],
                    ['titulo' => 'Emprendimiento',        'descripcion' => 'Herramientas para crear y gestionar tu propio negocio gastronómico.'],
                ],
            ],
            'administracion-hotelera' => [
                'nombre'      => 'Administración Hotelera',
                'descripcion' => 'Prepara líderes para la industria hotelera...',
                'duracion'    => '4 años',
                'modalidad'   => 'Presencial',
                'imagen'      => 'images/hotelera.jpg',
            ],

            'negocios-internacionales' => [
                'nombre'      => 'Negocios Internacionales',
                'descripcion' => 'Prepara profesionales para competir en mercados globales.',
                'duracion'    => '4 años',
                'modalidad'   => 'Presencial',
                'imagen'      => 'images/foto6.jpg',
                'competencias' => [
                    ['titulo' => 'Comercio exterior',        'descripcion' => 'Operaciones de importación y exportación.'],
                    ['titulo' => 'Negociación',              'descripcion' => 'Técnicas de negociación intercultural.'],
                    ['titulo' => 'Finanzas internacionales', 'descripcion' => 'Mercados financieros globales.'],
                    ['titulo' => 'Idiomas',                  'descripcion' => 'Dominio del inglés y un segundo idioma.'],
                ],
            ],
            'turismo-de-lujo' => [
                'nombre'      => 'Turismo de Lujo',
                'descripcion' => 'Especialización en experiencias turísticas de alto nivel.',
                'duracion'    => '4 años',
                'modalidad'   => 'Presencial',
                'imagen'      => 'images/Princess-17.jpg',
                'competencias' => [
                    ['titulo' => 'Diseño de experiencias', 'descripcion' => 'Creación de viajes exclusivos y personalizados.'],
                    ['titulo' => 'Marketing turístico',    'descripcion' => 'Estrategias para el sector turístico de lujo.'],
                    ['titulo' => 'Gestión cultural',       'descripcion' => 'Patrimonio cultural y turismo sostenible.'],
                    ['titulo' => 'Hospitalidad',           'descripcion' => 'Estándares de servicio premium.'],
                ],
            ],
            // agrega más programas aquí
        ];

        if (!isset($programas[$slug])) {
            abort(404);
        }

        return view('public.programa', ['programa' => $programas[$slug]]);

    }
}

