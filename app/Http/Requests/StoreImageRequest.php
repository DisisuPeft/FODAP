<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreImageRequest extends FormRequest
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
            'file.required' => 'El archivo debe ser de tipo jpg, png',
        ];
    }
    public function rules(): array
    {
        return [
            'file' => ['required', 'mimes:jpg,jpeg,png'],
        ];
    }

}
