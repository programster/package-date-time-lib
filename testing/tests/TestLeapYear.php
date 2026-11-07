<?php

/*
 * Test that Brick allows the 29th of February in a leap year, but not in other years.
 */

declare(strict_types = 1);

use Brick\DateTime\DateTimeException;
use Brick\DateTime\LocalDate;

final class TestLeapYear implements TestInterface
{
    private bool $m_passed;


    public function __construct()
    {
        $this->m_passed = false;
    }


    public function run() : void
    {
        try
        {
            // 2024 is a leap year, so the 29th of February exists.
            LocalDate::of(2024, 2, 29);
            $this->m_passed = true;
        }
        catch (DateTimeException)
        {
            $this->m_passed = false;
        }

        if ($this->m_passed)
        {
            try
            {
                // 2023 is NOT a leap year, so this should throw an exception.
                LocalDate::of(2023, 2, 29);
                $this->m_passed = false;
            }
            catch (DateTimeException)
            {
                $this->m_passed = true;
            }
        }
    }


    # Accessors
    public function getPassed() : bool { return $this->m_passed; }
}
