<?php

/*
 * Test that the LocalDateCollection withoutDates method works.
 */

declare(strict_types = 1);

use Brick\DateTime\LocalDate;
use Programster\DateTime\LocalDateCollection;

final class TestWithoutDatesInCollection implements TestInterface
{
    private bool $m_passed;


    public function __construct()
    {
        $this->m_passed = false;
    }


    public function run() : void
    {
        $undesiredDate1 = LocalDate::of(2020, 12, 25);
        $undesiredDate2 = LocalDate::of(2020, 12, 28);

        $myDateCollection = new LocalDateCollection(
            LocalDate::of(2020, 12, 26),
            $undesiredDate1,
            $undesiredDate2,
            LocalDate::of(2020, 12, 28),
        );

        $collectionWithoutUndesiredDates = $myDateCollection->withoutDates($undesiredDate1, $undesiredDate2);
        $this->m_passed = (count($collectionWithoutUndesiredDates) === 1);

        foreach ($collectionWithoutUndesiredDates as $dateToCheck)
        {
            /* @var $dateToCheck LocalDate */
            if ($dateToCheck->isEqualTo($undesiredDate1) || $dateToCheck->isEqualTo($undesiredDate2))
            {
                $this->m_passed = false;
            }
        }
    }


    # Accessors
    public function getPassed() : bool { return $this->m_passed; }
}
