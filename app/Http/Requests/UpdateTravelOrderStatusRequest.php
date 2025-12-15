<?php

namespace App\Http\Requests;

use App\Enums\TravelOrderStatus;

class UpdateTravelOrderStatusRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:' . implode(',', TravelOrderStatus::values())],
        ];
    }
}
