<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalificacionesRequest extends FormRequest
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
        return [
            'calificacion' => 'required',
            'docente_id' => 'required',
            'curso_id' => 'required',
        ];
    }
    public function messages(): array
    {
        return  [
            'calificacion.required' => 'El campo calificacion es obligatorio.',
            'docente_id.required' => 'El ID del docente es obligatorio.',
            'curso_id.required' => 'El ID del curso es obligatorio.',

        ];
    }
}
