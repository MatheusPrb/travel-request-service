<?php

namespace App\Helpers;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;

class DateHelper
{
    public static function formatDate(mixed $value, string $format = 'd/m/Y'): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->format($format);
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->format($format);
        }

        return Carbon::parse((string) $value)->format($format);
    }
}
