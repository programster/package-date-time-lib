<?php

/*
 * Test that withoutWeekends() removes the weekend dates, using the default weekend and a custom one.
 */

declare(strict_types = 1);

use Brick\DateTime\DayOfWeek;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateRange;
use Programster\DateTime\TimeLib;

final class TestRemoveWeekendsFromLocalDateCollection implements TestInterface
{
    private bool $m_passed;


    public function __construct()
    {
        $this->m_passed = false;
    }


    public function run() : void
    {
        // July 2022 has 31 days, 10 of which are Saturdays or Sundays, and 5 of which are Fridays.
        $dateRange = LocalDateRange::of(LocalDate::of(2022, 7, 1), LocalDate::of(2022, 7, 31));
        $dates = TimeLib::convertDateRangeToDateCollection($dateRange);
        $datesWithoutWeekends = $dates->withoutWeekends();
        $datesWithoutFridays = $dates->withoutWeekends([DayOfWeek::FRIDAY]);
        $this->m_passed = (count($datesWithoutWeekends) === 21 && count($datesWithoutFridays) === 26);

        foreach ($datesWithoutWeekends as $localDate)
        {
            /* @var $localDate LocalDate */
            if ($localDate->getDayOfWeek()->isWeekend())
            {
                $this->m_passed = false;
            }
        }
    }


    # Accessors
    public function getPassed() : bool { return $this->m_passed; }
}
