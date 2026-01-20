<?php

namespace App\DTO;

final class TravelOrderDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $status,
        public readonly ?string $userId,
        public readonly ?string $destination,
        public readonly ?string $departureDate,
        public readonly ?string $returnDate,
        public readonly ?string $userEmail = null,
        public readonly ?string $userName = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}
}
