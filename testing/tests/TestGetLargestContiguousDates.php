<?php

/*
 * Test that getLargestContiguousDatesSubset() finds the largest run of consecutive dates, with and without duplicates.
 */

declare(strict_types = 1);

use Brick\DateTime\LocalDate;
use Programster\DateTime\TimeLib;

final class TestGetLargestContiguousDates implements TestInterface
{
    private bool $m_passed;


    public function __construct()
    {
        $this->m_passed = false;
    }


    public function run() : void
    {
        $dayBefore = LocalDate::of(2024, 2, 28);

        // 29th of february is the next leap day.
        $leapDay = LocalDate::of(2024, 2, 29);

        $dayAfter = LocalDate::of(2024, 3, 1);
        $noDuplicates = TimeLib::getLargestContiguousDatesSubset(false, $leapDay, $dayAfter, $dayBefore);

        $duplicatesExcluded = TimeLib::getLargestContiguousDatesSubset(
            false,
            $leapDay,
            $dayAfter,
            $dayBefore,
            $dayAfter,
            $leapDay
        );

        $duplicatesIncluded = TimeLib::getLargestContiguousDatesSubset(
            true,
            $leapDay,
            $dayAfter,
            $dayBefore,
            $dayAfter,
            $leapDay
        );

        $withGap = TimeLib::getLargestContiguousDatesSubset(false, $dayBefore, $dayAfter);

        $this->m_passed = (
               count($noDuplicates) === 3
            && count($duplicatesExcluded) === 3
            && count($duplicatesIncluded) === 5
            && count($withGap) === 1
            && count(TimeLib::getLargestContiguousDatesSubset(false)) === 0
        );
    }


    # Accessors
    public function getPassed() : bool { return $this->m_passed; }
}
