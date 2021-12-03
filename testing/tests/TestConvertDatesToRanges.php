<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

declare(strict_types = 1);

class TestConvertDatesToRanges implements TestInterface
{
    private bool $m_passed;


    public function __construct()
    {
        $this->m_passed = false;
    }


    public function run()
    {
        $this->m_passed = false;

        $dayBeforeLeapDay = Brick\DateTime\LocalDate::of(2000, 2, 28);

        // 29th of february is the next leap day.
        $leapDay = Brick\DateTime\LocalDate::of(2000, 2, 29);

        $dayAfterLeapDay = Brick\DateTime\LocalDate::of(2000, 3, 1);

        $today = Brick\DateTime\LocalDate::now(\Brick\DateTime\TimeZone::utc());
        $yesterday = $today->minusDays(1);

        $ranges = Programster\DateTime\TimeLib::convertDatesToContiguousDateRanges(
            $leapDay,
            $today,
            $dayBeforeLeapDay,
            $dayAfterLeapDay,
            $yesterday,
        );

        /* @var $range \Brick\DateTime\LocalDateRange */
        if
        (
               count($ranges) === 2
            && $ranges[0]->getStart() === $dayBeforeLeapDay
            && $ranges[0]->getEnd() === $dayAfterLeapDay
            && $ranges[1]->getStart() === $yesterday
            && $ranges[1]->getEnd() === $today
        )
        {
            $this->m_passed = true;
        }
    }


    public function getPassed(): bool
    {
        return $this->m_passed;
    }
}
