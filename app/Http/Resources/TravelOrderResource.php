<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'requester_name' => $this->userName,
            'destination' => $this->destination,
            'departure_date' => DateHelper::formatDate($this->departureDate),
            'return_date' => DateHelper::formatDate($this->returnDate),
            'status' => $this->status,
            'created_at' => DateHelper::formatDate($this->createdAt),
            'updated_at' => DateHelper::formatDate($this->updatedAt),
        ];
    }
}
