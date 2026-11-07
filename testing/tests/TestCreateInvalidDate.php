<?php

/*
 * Test that Brick refuses to create a date that doesn't exist.
 */

declare(strict_types = 1);

use Brick\DateTime\DateTimeException;
use Brick\DateTime\LocalDate;

final class TestCreateInvalidDate implements TestInterface
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
            LocalDate::of(2024, 2, 30);
            $this->m_passed = false;
        }
        catch (DateTimeException)
        {
            $this->m_passed = true;
        }
    }


    # Accessors
    public function getPassed() : bool { return $this->m_passed; }
}
