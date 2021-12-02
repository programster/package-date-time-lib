<?php

/*
 * Test that the getDatesIntersection() method works.
 */

declare(strict_types = 1);

class TestDatesIntersection implements TestInterface
{
    private bool $m_passed;


    public function __construct()
    {
        $this->m_passed = false;
    }


    public function run()
    {
        $dayBefore = Brick\DateTime\LocalDate::of(2024, 2, 28);

        // 29th of february is the next leap day.
        $leapDay = Brick\DateTime\LocalDate::of(2024, 2, 29);

        $dayAfter = Brick\DateTime\LocalDate::of(2024, 3, 1);

        $intersectionCollection = Programster\DateTime\TimeLib::getDatesIntersection(
            new \Programster\DateTime\LocalDateCollection($dayBefore, $leapDay, $dayAfter),
            new \Programster\DateTime\LocalDateCollection($leapDay, $dayBefore),
            new \Programster\DateTime\LocalDateCollection($dayBefore, $dayAfter),
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
