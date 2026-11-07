<?php

/*
 * Test that the weekend methods default to Saturday and Sunday, accept a custom weekend, and reject weekend days given
 * as plain integers.
 */

declare(strict_types = 1);

use Brick\DateTime\DayOfWeek;
use Brick\DateTime\LocalDate;
use Programster\DateTime\LocalDateCollection;
use Programster\DateTime\TimeLib;

final class TestWeekendDays implements TestInterface
{
    private bool $m_passed;


    public function __construct()
    {
        $this->m_passed = false;
    }


    public function run() : void
    {
        // Monday 1st to Wednesday 31st January 2024, which has 8 weekend days, 4 of which are Saturdays.
        $monthEnds = new LocalDateCollection(LocalDate::of(2024, 1, 31), LocalDate::of(2024, 1, 1));
        $saturday = LocalDate::of(2024, 1, 6);
        $setWithASaturday = new LocalDateCollection(LocalDate::of(2024, 1, 5), $saturday, LocalDate::of(2024, 1, 8));
        $weekendsInSet = TimeLib::getWeekendDatesInSet($setWithASaturday);

        $this->m_passed = (
               count(TimeLib::getWeekendsBetweenDates($monthEnds)) === 8
            && count(TimeLib::getWeekendsBetweenDates($monthEnds, [DayOfWeek::SATURDAY])) === 4
            && count($weekendsInSet) === 1
            && $weekendsInSet[0]->isEqualTo($saturday)
            && $this->throwsInvalidArgument(TimeLib::getWeekendsBetweenDates(...), $monthEnds, [6, 7])
            && $this->throwsInvalidArgument(TimeLib::getWeekendDatesInSet(...), $monthEnds, [6, 7])
            && $this->throwsInvalidArgument($monthEnds->withoutWeekends(...), [6, 7])
            && $this->throwsInvalidArgument(TimeLib::getWeekendsBetweenDates(...), new LocalDateCollection())
        );
    }


    /**
     * Checks that calling the method with the arguments given throws an InvalidArgumentException.
     * @param Closure $method - the method to call.
     * @param mixed ...$arguments - the arguments to call it with.
     * @return bool - true if it threw an InvalidArgumentException.
     */
    private function throwsInvalidArgument(Closure $method, mixed ...$arguments) : bool
    {
        try
        {
            $method(...$arguments);
            $threw = false;
        }
        catch (InvalidArgumentException)
        {
            $threw = true;
        }

        return $threw;
    }


    # Accessors
    public function getPassed() : bool { return $this->m_passed; }
}
