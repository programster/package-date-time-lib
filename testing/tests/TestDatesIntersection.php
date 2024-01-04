<?php

/*
 * Test that the getDatesIntersection() method works.
 */

declare(strict_types = 1);

use Brick\DateTime\LocalDate;
use Programster\DateTime\LocalDateCollection;
use Programster\DateTime\TimeLib;

class TestDatesIntersection implements TestInterface
{
    private bool $m_passed;


    public function __construct()
    {
        $this->m_passed = false;
    }


    public function run()
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

        if (count($intersectionCollection) === 1)
        {
            $this->m_passed = true;
        }
    }


    public function getPassed(): bool
    {
        return $this->m_passed;
    }
}
