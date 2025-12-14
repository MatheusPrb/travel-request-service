<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromoteUserToAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'string',
                'uuid',
                Rule::exists('users', 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'O ID do usuário é obrigatório.',
            'user_id.uuid' => 'O ID do usuário deve ser um UUID válido.',
            'user_id.exists' => 'Usuário não encontrado.',
        ];
    }
}
