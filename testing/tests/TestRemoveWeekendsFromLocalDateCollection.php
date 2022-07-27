<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

declare(strict_types = 1);

class TestRemoveWeekendsFromLocalDateCollection implements TestInterface
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
            $this->m_passed = true;
            $startDate = \Brick\DateTime\LocalDate::of(2022, 07, 1);
            $endDate = \Brick\DateTime\LocalDate::of(2022, 07, 31);
            $dateRange = \Brick\DateTime\LocalDateRange::of($startDate, $endDate);
            $dates = \Programster\DateTime\TimeLib::convertDateRangeToDateCollection($dateRange);
            $datesWithoutWeekends = $dates->withoutWeekends();
            $arrayOfDates = $datesWithoutWeekends->getArrayCopy();

            foreach ($arrayOfDates as $index => $localDate)
            {
                /* @var $localDate \Brick\DateTime\LocalDate */
                if ($localDate->getDayOfWeek()->getValue() === 6 || $localDate->getDayOfWeek()->getValue() === 7)
                {
                    $this->m_passed = false;
                    break;
                }
            }
        }
        catch (Brick\DateTime\DateTimeException $ex)
        {
            $this->m_passed = false;
        }
    }


    public function getPassed(): bool
    {
        return $this->m_passed;
    }
}
