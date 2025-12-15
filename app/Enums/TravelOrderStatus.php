<?php

namespace App\Enums;

enum TravelOrderStatus: string
{
    case REQUESTED = 'solicitado';
    case APPROVED = 'aprovado';
    case CANCELED = 'cancelado';

    public function canUpdateTo(self $newStatus): bool
    {
        if ($this === $newStatus) {
            return false;
        }

        if ($this === self::REQUESTED) {
            return $newStatus === self::APPROVED
                || $newStatus === self::CANCELED;
        }

        return false;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toArray(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
