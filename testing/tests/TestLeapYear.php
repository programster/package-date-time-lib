<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

declare(strict_types = 1);

class TestLeapYear implements TestInterface
{
    private bool $m_passed;


    public function __construct()
    {
        $this->m_passed = false;
    }


    public function run()
    {
        try
        {
            // 29th of february 2024 is the next leap day.
            $leapDay = Brick\DateTime\LocalDate::of(2024, 2, 29);
            $this->m_passed = true;
        }
        catch (Brick\DateTime\DateTimeException $ex)
        {
            $this->m_passed = false;
        }

        if ($this->m_passed)
        {
            try
            {
                // 29th of february in 2023 is NOT a leap year, should throw exception
                $leapDay = Brick\DateTime\LocalDate::of(2023, 2, 29);
                $this->m_passed = false;
            }
            catch (Brick\DateTime\DateTimeException $ex)
            {
                $this->m_passed = true;
            }
        }
    }


    public function getPassed(): bool
    {
        return $this->m_passed;
    }
}
