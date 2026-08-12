<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Users\Institution;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // 1. Get the institution ID DIRECTLY from the session.
        $institutionId = session('active_institution_id');
        $institution = $institutionId ? Institution::find($institutionId) : null;

        // 2. Define the rule for credits based on the session's institution.
        $creditsRule = 'nullable|integer|min:0'; // Default: optional
        if ($institution && $institution->name === 'Universidad Mundo Imperial') {
            $creditsRule = 'required|integer|min:0'; // Required only for UMI
        }

        // 3. Reglas base
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructor_name' => 'nullable|string|max:255',
            'institution_id' => 'required|exists:institutions,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
            'credits' => $creditsRule,
            'modality' => 'required|in:presencial,virtual,hibrida',
            'hours' => 'nullable|integer|min:0', // nullable porque híbrido lo calcula automático
            'workstation_id' => 'nullable|exists:workstations,id',
            'guide_material' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:40960',
            'cert_bg_image' => 'nullable|image|mimes:jpeg,png,jpg|max:20480',
            'cert_sig_1_image' => 'nullable|image|mimes:png|max:10240',
            'cert_sig_2_image' => 'nullable|image|mimes:png|max:10240',
            'cert_sig_1_name' => 'nullable|string|max:100',
            'cert_sig_2_name' => 'nullable|string|max:100',

            //  CAMPOS PARA HÍBRIDOS
            'selected_courses' => 'nullable|array|required_if:modality,hibrida',
            'selected_courses.*' => 'exists:courses,id',
            'virtual_percentage' => 'nullable|integer|min:0|max:100|required_if:modality,hibrida',
            'presencial_percentage' => 'nullable|integer|min:0|max:100|required_if:modality,hibrida',

            //  DEPARTAMENTOS Y PUESTOS (múltiples)
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
            'workstation_ids' => 'nullable|array',
            'workstation_ids.*' => 'exists:workstations,id',
            'career_id' => 'nullable|exists:careers,id',

            // GRUPOS
            'groups' => 'nullable|array',
            'groups.*.name' => 'required|string|max:255',
            'groups.*.type' => 'in:abierto,cerrado',
            'groups.*.min' => 'nullable|integer|min:1',
            'groups.*.max' => 'nullable|integer|min:1',
            'groups.*.departments' => 'nullable|array',
            'groups.*.workstations' => 'nullable|array',

            //  PLANTILLAS
            'template_topics' => 'nullable|array',
            'template_topics.*' => 'exists:topic_templates,id',
            'template_subtopics' => 'nullable|array',
            'template_subtopics.*' => 'exists:subtopic_templates,id',
        ];

        return $rules;
    }

    /**
     * Get the validation messages that apply to the request.
     */
    public function messages(): array
    {
        return [
            // Híbridos
            'selected_courses.required_if' => 'Debes seleccionar al menos un curso para la modalidad híbrida',
            'selected_courses.*.exists' => 'Uno de los cursos seleccionados no existe',
            'virtual_percentage.required_if' => 'El porcentaje virtual es requerido para cursos híbridos',
            'presencial_percentage.required_if' => 'El porcentaje presencial es requerido para cursos híbridos',
            'virtual_percentage.integer' => 'El porcentaje virtual debe ser un número entero',
            'presencial_percentage.integer' => 'El porcentaje presencial debe ser un número entero',
            'virtual_percentage.min' => 'El porcentaje virtual no puede ser menor a 0',
            'presencial_percentage.min' => 'El porcentaje presencial no puede ser menor a 0',
            'virtual_percentage.max' => 'El porcentaje virtual no puede ser mayor a 100',
            'presencial_percentage.max' => 'El porcentaje presencial no puede ser mayor a 100',

            // Grupos
            'groups.*.name.required' => 'El nombre del grupo es requerido',
            'groups.*.type.in' => 'El tipo de grupo debe ser "abierto" o "cerrado"',
            'groups.*.min.min' => 'El mínimo de participantes debe ser al menos 1',
            'groups.*.max.min' => 'El máximo de participantes debe ser al menos 1',

            // Archivos
            'image.image' => 'La imagen del curso debe ser un archivo de imagen válido',
            'image.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg, gif, webp',
            'image.max' => 'La imagen no debe superar los 20MB',
            'guide_material.file' => 'El material de guía debe ser un archivo válido',
            'guide_material.mimes' => 'El material de guía debe ser PDF, Word o PowerPoint',
            'guide_material.max' => 'El material de guía no debe superar los 40MB',
            'cert_bg_image.image' => 'El fondo del certificado debe ser una imagen',
            'cert_bg_image.mimes' => 'El fondo del certificado debe ser JPG, JPEG o PNG',
            'cert_sig_1_image.mimes' => 'La firma debe ser en formato PNG',
            'cert_sig_2_image.mimes' => 'La firma debe ser en formato PNG',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Si es híbrido y no tiene horas, las calcularemos después
        if ($this->modality === 'hibrida') {
            $this->merge([
                'hours' => null // Se calculará automáticamente en el controlador
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validación para híbridos: la suma de porcentajes debe ser 100%
            if ($this->modality === 'hibrida') {
                $virtualPercent = (int) $this->virtual_percentage;
                $presencialPercent = (int) $this->presencial_percentage;

                if ($virtualPercent + $presencialPercent != 100) {
                    $validator->errors()->add(
                        'virtual_percentage',
                        'La suma de los porcentajes virtual y presencial debe ser 100%'
                    );
                }
            }

            // Validación para presenciales y virtuales: hours es requerido
            if (in_array($this->modality, ['presencial', 'virtual'])) {
                if (empty($this->hours) || $this->hours <= 0) {
                    $validator->errors()->add(
                        'hours',
                        'Las horas son requeridas para cursos presenciales y virtuales'
                    );
                }
            }

            // Validación de créditos para UMI
            $institutionId = session('active_institution_id');
            $institution = $institutionId ? Institution::find($institutionId) : null;

            if ($institution && $institution->name === 'Universidad Mundo Imperial') {
                if (empty($this->credits) && $this->credits !== 0) {
                    $validator->errors()->add(
                        'credits',
                        'Los créditos son obligatorios para la Universidad Mundo Imperial'
                    );
                }
            }
        });
    }
}
