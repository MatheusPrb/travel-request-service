<?php

namespace Tests\Unit;

use App\Helpers\DateHelper;
use Carbon\Carbon;
use DateTimeImmutable;
use Tests\TestCase;

class DateHelperTest extends TestCase
{
    public function test_returns_null_for_null_or_empty_values(): void
    {
        $this->assertNull(DateHelper::formatDate(null));
        $this->assertNull(DateHelper::formatDate(''));
    }

    public function test_formats_carbon_instance(): void
    {
        $date = Carbon::create(2026, 1, 20, 14, 30, 0);

        $this->assertSame('20/01/2026', DateHelper::formatDate($date, 'd/m/Y'));
    }

    public function test_formats_datetime_interface_instance(): void
    {
        $date = new DateTimeImmutable('2026-01-20 14:30:00');

        $this->assertSame('20/01/2026', DateHelper::formatDate($date, 'd/m/Y'));
    }

    public function test_parses_and_formats_string_date(): void
    {
        $this->assertSame(
            '20/01/2026',
            DateHelper::formatDate('2026-01-20 14:30:00', 'd/m/Y')
        );
    }
}
