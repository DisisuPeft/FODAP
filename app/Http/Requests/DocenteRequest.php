<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DocenteRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'rfc' => 'required',
            'curp' => 'required',
            'nombre' => 'required',
            'apellidoPat' => 'required',
            'apellidoMat' => 'required',
            'sexo' => 'required',
            'telefono' => 'required',
            'carrera_id' => 'required',
            'id_puesto' => 'required',
            'tipo_plaza' => 'required',
            'departamento_id' => 'required',
            'licenciatura' => 'required',
            'id_posgrado' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'rfc.required' => 'El campo RFC es obligatorio.',
            'curp.required' => 'El campo CURP es obligatorio.',
            'nombre.required' => 'El campo Nombre es obligatorio.',
            'apellidoPat.required' => 'El campo Apellido Paterno es obligatorio.',
            'apellidoMat.required' => 'El campo Apellido Materno es obligatorio.',
            'sexo.required' => 'El campo Sexo es obligatorio.',
            'telefono.required' => 'El campo Telefono es obligatorio.',
            'carrera_id.required' => 'El campo Carrera es obligatorio.',
            'id_puesto.required' => 'El campo Puesto es obligatorio.',
            'tipo_plaza.required' => 'El campo Tipo Plaza es obligatorio.',
            'departamento_id.required' => 'El campo Departamento es obligatorio.',
            'licenciatura.required' => 'El campo Licenciatura es obligatorio.',
            'id_posgrado.required' => 'El campo Id Posgrado es obligatorio.',
        ];
    }
}
