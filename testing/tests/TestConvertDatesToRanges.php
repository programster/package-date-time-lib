<?php

/*
 * Test that convertDatesToContiguousDateRanges() groups unordered dates into the right contiguous ranges.
 */

declare(strict_types = 1);

use Brick\DateTime\LocalDate;
use Brick\DateTime\TimeZone;
use Programster\DateTime\TimeLib;

final class TestConvertDatesToRanges implements TestInterface
{
    private bool $m_passed;


    public function __construct()
    {
        $this->m_passed = false;
    }


    public function run() : void
    {
        $dayBeforeLeapDay = LocalDate::of(2000, 2, 28);

        // 29th of february is the next leap day.
        $leapDay = LocalDate::of(2000, 2, 29);

        $dayAfterLeapDay = LocalDate::of(2000, 3, 1);
        $today = LocalDate::now(TimeZone::utc());
        $yesterday = $today->minusDays(1);

        $ranges = TimeLib::convertDatesToContiguousDateRanges(
            $leapDay,
            $today,
            $dayBeforeLeapDay,
            $dayAfterLeapDay,
            $yesterday,
        );

        $this->m_passed = (
               count($ranges) === 2
            && $ranges[0]->getStart()->isEqualTo($dayBeforeLeapDay)
            && $ranges[0]->getEnd()->isEqualTo($dayAfterLeapDay)
            && $ranges[1]->getStart()->isEqualTo($yesterday)
            && $ranges[1]->getEnd()->isEqualTo($today)
        );
    }


    # Accessors
    public function getPassed() : bool { return $this->m_passed; }
}
