<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

declare(strict_types = 1);

class TestGetLargestContiguousDates implements TestInterface
{
    private $m_passed;


    public function __construct()
    {
        $this->m_passed = false;
    }


    public function run()
    {
        $this->m_passed = true;

        $dayBefore = Brick\DateTime\LocalDate::of(2024, 2, 28);

        // 29th of february is the next leap day.
        $leapDay = Brick\DateTime\LocalDate::of(2024, 2, 29);

        $dayAfter = Brick\DateTime\LocalDate::of(2024, 3, 1);


        if (count(Programster\DateTime\TimeLib::getLargestContiguousDatesSubset(false, $leapDay, $dayAfter, $dayBefore)) !== 3)
        {
            $this->m_passed = false;
        }

        // dupes 1
        if (count(Programster\DateTime\TimeLib::getLargestContiguousDatesSubset(false, $leapDay, $dayAfter, $dayBefore, $dayAfter, $leapDay)) !== 3)
        {
            $this->m_passed = false;
        }

        // dupes 2
        if (count(Programster\DateTime\TimeLib::getLargestContiguousDatesSubset(true, $leapDay, $dayAfter, $dayBefore, $dayAfter, $leapDay)) !== 5)
        {
            $this->m_passed = false;
        }

        // test a gap
        if (count(Programster\DateTime\TimeLib::getLargestContiguousDatesSubset(false, $dayBefore, $dayAfter)) !== 1)
        {
            $this->m_passed = false;
        }
    }


    public function getPassed(): bool
    {
        return $this->m_passed;
    }
}
