<?php

/*
 * Test that the getDatesIntersection() method works, including with no collections and with duplicate dates.
 */

declare(strict_types = 1);

use Brick\DateTime\LocalDate;
use Programster\DateTime\LocalDateCollection;
use Programster\DateTime\TimeLib;

final class TestDatesIntersection implements TestInterface
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

        $intersectionCollection = TimeLib::getDatesIntersection(
            new LocalDateCollection($dayBefore, $leapDay, $dayAfter),
            new LocalDateCollection($leapDay, $dayBefore),
            new LocalDateCollection($dayBefore, $dayAfter),
        );

        $duplicatesIntersection = TimeLib::getDatesIntersection(
            new LocalDateCollection($leapDay, $leapDay, $dayAfter),
            new LocalDateCollection($leapDay),
        );

        $this->m_passed = (
               count($intersectionCollection) === 1
            && $intersectionCollection[0]->isEqualTo($dayBefore)
            && count($duplicatesIntersection) === 1
            && $duplicatesIntersection[0]->isEqualTo($leapDay)
            && count(TimeLib::getDatesIntersection()) === 0
        );
    }


    # Accessors
    public function getPassed() : bool { return $this->m_passed; }
}
