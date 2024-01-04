<?php

/*
 * A library to help with performing date/time operations that we can't just use brick/time directly for.
 */

declare(strict_types = 1);
namespace Programster\DateTime;


use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateRange;


final class TimeLib
{
    /**
     * Returns the largest set of contiguous dates within the provided dates
     * @param bool $includeDuplicates - whether to include any duplicate dates in the subset or not. If false, then the
     * returned subset will always contain unique dates.
     * This will sort the dates chronologically before performing the scan.
     * @param LocalDate ...$dates
     * @return LocalDateCollection
     */
    public static function getLargestContiguousDatesSubset(
        bool $includeDuplicates,
        LocalDate ...$dates
    ) : LocalDateCollection
    {
        $largestSet = new LocalDateCollection();

        if (count($dates) > 0)
        {
            $sorter = function(LocalDate $a, LocalDate $b) {
                return $a->compareTo($b);
            };

            usort($dates, $sorter);
            $previousDate = null;

            $currentSet = [];

            foreach ($dates as $loopDate)
            {
                if ($previousDate !== null)
                {
                    $difference = $loopDate->toEpochDay() - $previousDate->toEpochDay();

                    if ($difference === 0)
                    {
                        if ($includeDuplicates)
                        {
                            $currentSet[] = $loopDate;
                        }
                        else
                        {
                            // do nothing
                        }
                    }
                    elseif ($difference === 1)
                    {
                        $currentSet[] = $loopDate;
                    }
                    else
                    {
                        // difference is greater than 1.

                        // break in the set, reset
                        if (count($currentSet) > count($largestSet))
                        {
                            $largestSet = new LocalDateCollection(...$currentSet);
                        }

                        $currentSet = [$loopDate];
                    }
                }
                else
                {
                    $currentSet[] = $loopDate;
                }

                $previousDate = $loopDate;
            }

            if (count($currentSet) > count($largestSet))
            {
                $largestSet = new LocalDateCollection(...$currentSet);
            }
        }

        return $largestSet;
    }


    /**
     * Converts a given set of dates, into an array of LocalDateRange objects with each LocalDateRange representing
     * a contiguous range of dates that were provided.
     * @param LocalDate ...$dates
     * @return array
     */
    public static function convertDatesToContiguousDateRanges(LocalDate ...$dates) : array
    {
        $ranges = [];
        $sets = [];
        $currentSet = [];

        if (count($dates) > 0)
        {
            $sorter = function(LocalDate $a, LocalDate $b) {
                return $a->compareTo($b);
            };

            usort($dates, $sorter);
            $previousDate = null;
            $currentSet = [];

            foreach ($dates as $loopDate)
            {
                if ($previousDate !== null)
                {
                    $difference = $loopDate->toEpochDay() - $previousDate->toEpochDay();

                    if ($difference === 0)
                    {
                        // do nothing
                    }
                    elseif ($difference === 1)
                    {
                        $currentSet[] = $loopDate;
                    }
                    else
                    {
                        // difference is greater than 1.
                        $sets[] = new LocalDateCollection(...$currentSet);
                        $currentSet = [$loopDate];
                    }
                }
                else
                {
                    $currentSet[] = $loopDate;
                }

                $previousDate = $loopDate;
            }
        }

        if (count($currentSet) > 0)
        {
            $sets[] = new LocalDateCollection(...$currentSet);
        }

        foreach ($sets as $set)
        {
            // convert the set to a LocalDateRange
            $ranges[] = LocalDateRange::of($set[0], $set[(count($set) - 1)]);
        }

        return $ranges;
    }


    /**
     * Given a set of dates (at least 2), return all the weekend dates that fall within that set's range. This will include
     * dates that are not necessarily in the set itself.
     * If you want the dates that are in the set that are on the weekend, use the getWeekendDatesInSet() method.
     * @param array $weekendDays - the days of the week that are considered the weekend. [6, 7] represents
     * Saturday and Sunday, but you may wish to set others: https://bit.ly/3Ih474Y
     * @param LocalDate ...$dates - the dates to get the weekend dates between.
     * @return LocalDateCollection - A collection of LocalDate objects for the weekend dates that exist.
     */
    public static function getWeekendsBetweenDates(array $weekendDays = [6, 7], LocalDate ...$dates) : LocalDateCollection
    {
        $min = LocalDate::minOf(...$dates);
        $max = LocalDate::maxOf(...$dates);

        $weekendDates = new LocalDateCollection();

        for ($i = $min->toEpochDay(); $i <= $max->toEpochDay(); $i++)
        {
            $date = LocalDate::ofEpochDay($i);

            if (in_array($date->getDayOfWeek()->value, $weekendDays))
            {
                $weekendDates[] = $date;
            }
        }

        return $weekendDates;
    }


    /**
     * Returns the dates within the set that fall on the weekend. This is similar to getWeekendsBetweenDates()
     * except that this won't return any dates that are not actually in the set.
     * @param array $weekendDays
     * @param LocalDate ...$dates
     * @return LocalDateCollection
     */
    public static function getWeekendDatesInSet(array $weekendDays = [6, 7], LocalDate ...$dates) : LocalDateCollection
    {
        $weekendDates = new LocalDateCollection();

        foreach ($dates as $date)
        {
            if (in_array($date->getDayOfWeek()->value, $weekendDays))
            {
                $weekendDates[] = $date;
            }
        }

        return $weekendDates;
    }


    /**
     * Returns the dates (in a collection), that are in all the provided collections of dates.
     * This is basically array_intersect for LocalDate.
     * @param LocalDateCollection ...$localDateSets
     * @return LocalDateCollection
     */
    public static function getDatesIntersection(LocalDateCollection ...$localDateSets) : LocalDateCollection
    {
        $intersectionCollection = new LocalDateCollection();
        $epochSets = [];

        foreach ($localDateSets as $setOfDates)
        {
            $epochsForSet = [];

            foreach ($setOfDates as $localDate)
            {
                /* @var $localDate LocalDate */
                $epochsForSet[] = $localDate->toEpochDay();
            }

            $epochSets[] = $epochsForSet;
        }

        $intersectingEpochs = array_intersect(...$epochSets);

        foreach ($intersectingEpochs as $epochDay)
        {
            $intersectionCollection[] = LocalDate::ofEpochDay($epochDay);
        }

        return $intersectionCollection;
    }


    /**
     * Converts a LocalDateRange object into a LocalDateCollection which contains the full list of
     * dates between the start and end dates of the date range.
     * @param LocalDateRange $dateRange
     * @return LocalDateCollection
     */
    public static function convertDateRangeToDateCollection(LocalDateRange $dateRange) : LocalDateCollection
    {
        $dates = [];
        $iterator = $dateRange->getIterator();

        foreach ($iterator as $dateInRange)
        {
            $dates[] = $dateInRange;
        }

        return new LocalDateCollection(...$dates);
    }
}
