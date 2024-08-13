<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FichaTecnicaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'id_curso.required' => 'El id curso no viene en el formulario.',
            'introduccion.required' => 'La introduccion es requerida.',
            'justificacion.required' => 'La justificacion es requerida.',
            'objetivo_general.required' => 'El objetivo general es requerido.',
            'elementos_didacticos.required' => 'Los elementos didacticos es requerido.',
            'competencias_desarrollar.required' => 'La competencia a desarrollar es requerida.',
            'fuentes_informacion.required' => 'Las fuentes de información es requerida.',
            'duracion.required' => 'La duración del curso es requerida.',
            'tipo_servicio.required' => 'El tipo de servicio es requerido.',
        ];
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'id_curso' => 'required',
            'introduccion' => 'required',
            'justificacion' => 'required',
            'objetivo_general' => 'required',
            'elementos_didacticos' => 'required',
            'competencias_desarrollar' => 'required',
            'fuentes_informacion' => 'required',
            'duracion' => 'required',
            'tipo_servicio' => 'required',
        ];
    }

}
