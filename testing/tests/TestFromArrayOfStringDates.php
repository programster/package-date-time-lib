<?php

/*
 * Test that fromArrayOfStringDates() parses valid dates, and rejects ones that don't exist or don't match the format,
 * rather than rolling them over or failing with a TypeError.
 */

declare(strict_types = 1);

use Brick\DateTime\LocalDate;
use Programster\DateTime\LocalDateCollection;

final class TestFromArrayOfStringDates implements TestInterface
{
    private bool $m_passed;


    public function __construct()
    {
        $this->m_passed = false;
    }


    public function run() : void
    {
        $dates = LocalDateCollection::fromArrayOfStringDates(['2020-12-25', '2024-02-29']);
        $ukDates = LocalDateCollection::fromArrayOfStringDates(['25/12/2020'], 'd/m/Y');

        $this->m_passed = (
               count($dates) === 2
            && $dates[0]->isEqualTo(LocalDate::of(2020, 12, 25))
            && $dates[1]->isEqualTo(LocalDate::of(2024, 2, 29))
            && $ukDates[0]->isEqualTo(LocalDate::of(2020, 12, 25))
            && $this->isRejected(['2020-02-30'])
            && $this->isRejected(['2023-02-29'])
            && $this->isRejected(['25/12/2020'])
            && $this->isRejected(['2020-12-25 extra'])
            && $this->isRejected([20201225])
        );
    }


    /**
     * Checks that fromArrayOfStringDates() throws an InvalidArgumentException for the dates given.
     * @param array $dates - the dates to try to create a collection from, in Y-m-d format.
     * @return bool - true if they were rejected.
     */
    private function isRejected(array $dates) : bool
    {
        try
        {
            LocalDateCollection::fromArrayOfStringDates($dates);
            $rejected = false;
        }
        catch (InvalidArgumentException)
        {
            $rejected = true;
        }

        return $rejected;
    }


    # Accessors
    public function getPassed() : bool { return $this->m_passed; }
}
