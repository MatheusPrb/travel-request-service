<?php

namespace App\Http\Requests;

use App\Enums\TravelOrderStatus;

class ListTravelOrdersRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', 'in:' . implode(',', TravelOrderStatus::values())],
            'destination' => ['sometimes', 'string', 'max:255'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'travel_start_date' => ['sometimes', 'date'],
            'travel_end_date' => ['sometimes', 'date', 'after_or_equal:travel_start_date'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}

