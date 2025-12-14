<?php

namespace App\Http\Requests;

class UpdateTravelOrderStatusRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:solicitado,aprovado,cancelado'],
        ];
    }
}
