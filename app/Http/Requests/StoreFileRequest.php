<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFileRequest extends FormRequest
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
            'file' => 'required|mimes:docx|max:2048', // 2MB máximo
        ];
    }

    public function messages(): array{
        return [
            'file.required' => 'El archivo es requerido',
            'file.mimes' => 'El archivo debe ser de tipo Microsoft Word',
            'file.max' => 'Solo se permite subir un archivo de 2MB.',
        ];
    }
}
