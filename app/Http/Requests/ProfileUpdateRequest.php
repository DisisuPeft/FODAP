<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['email', 'max:255', 'unique:users'],
        ];
    }

    public function message(): array
    {
        return [
            'email.email' => 'No es valido el correo institucional',
            'email.unique' => 'El correo ya se encuentra registrado',
        ];
    }
}
