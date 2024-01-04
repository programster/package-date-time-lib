<?php

/*
 * Test that the LocalDateCollection withoutDates method works.
 */

declare(strict_types = 1);

use Brick\DateTime\LocalDate;
use Programster\DateTime\LocalDateCollection;

class TestWithoutDatesInCollection implements TestInterface
{
    private bool $m_passed;


    public function __construct()
    {
        $this->m_passed = false;
    }


    public function run()
    {
        $this->m_passed = true;

        $undesiredDate1 = LocalDate::of(2020,12,25);
        $undesiredDate2 = LocalDate::of(2020,12,28);

        $myDateCollection = new LocalDateCollection(
            LocalDate::of(2020,12,26),
            $undesiredDate1,
            $undesiredDate2,
            LocalDate::of(2020,12,28),
        );

        $collectionWithoutUndesiredDates = $myDateCollection->withoutDates($undesiredDate1, $undesiredDate2);

        $copy = $collectionWithoutUndesiredDates->getArrayCopy();

        foreach ($copy as $dateToCheck)
        {
            /* @var $dateToCheck LocalDate */
            if ($dateToCheck->compareTo($undesiredDate1) === 0)
            {
                $this->m_passed = false;
                break;
            }

            if ($dateToCheck->compareTo($undesiredDate2) === 0)
            {
                $this->m_passed = false;
                break;
            }
        }
    }


    public function getPassed(): bool
    {
        return $this->m_passed;
    }
}
