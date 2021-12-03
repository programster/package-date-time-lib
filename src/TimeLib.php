<?php

/*
 * A library to help with performing date/time operations that we can't just use brick/time directly for.
 */

declare(strict_types = 1);
namespace Programster\DateTime;


final class TimeLib extends \Exception
{
    /**
     * Returns the largest set of contiguous dates within the provided dates
     * @param bool $includeDuplicates - whether to include any duplicate dates in the subset or not. If false, then the
     * returned subset will always contain unique dates.
     * This will sort the dates chronologically before performing the scan.
     * @param \Brick\DateTime\LocalDate $dates
     * @return int
     */
    public static function getLargestContiguousDatesSubset(
        bool $includeDuplicates,
        \Brick\DateTime\LocalDate ...$dates
    ) : LocalDateCollection
    {
        $largestSet = new LocalDateCollection();

        if (count($dates) > 0)
        {
            $sorter = function(\Brick\DateTime\LocalDate $a, \Brick\DateTime\LocalDate $b) {
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
     * @param \Brick\DateTime\LocalDate $dates
     * @return LocalDateCollection
     */
    public static function convertDatesToContiguousDateRanges(\Brick\DateTime\LocalDate ...$dates) : array
    {
        $ranges = [];
        $sets = [];

        if (count($dates) > 0)
        {
            $sorter = function(\Brick\DateTime\LocalDate $a, \Brick\DateTime\LocalDate $b) {
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
            // convert the set to a localdaterange
            $ranges[] = \Brick\DateTime\LocalDateRange::of($set[0], $set[(count($set) - 1)]);
        }

        return $ranges;
    }


    /**
     * Given a set of dates (at least 2), return all the weekend dates that fall within that set's range. This will include
     * dates that are not necessarily in the set itself.
     * If you want the dates that are in the set that are on the weekend, use the getWeekendDatesInSet() method.
     * @param Brick\DateTime\LocalDate $dates - the dates to get the weekend dates between.
     * @param array $weekendDays - the days of the week that are considered the weekend. [6, 7] represents
     * Saturday and Sunday, but you may wish to set others: https://bit.ly/3Ih474Y
     */
    public static function getWeekendsBetweenDates(array $weekendDays = [6, 7], Brick\DateTime\LocalDate ...$dates)
    {
        $min = \Brick\DateTime\LocalDate::minOf($dates);
        $max = \Brick\DateTime\LocalDate::minOf($dates);

        $weekendDates = [];

        for ($i = $min->toEpochDay(); $i <= $max->toEpochDay(); $i++)
        {
            $date = \Brick\DateTime\LocalDate::ofEpochDay($i);

            if (in_array($date->getDayOfWeek(), $weekendDays))
            {
                $weekendDates[] = $date;
            }
        }

        return $weekendDates;
    }


    /**
     * Returns the dates within the set that fall on the weekend. This is similar to getWeekendsBetweenDates()
     * except that this won't return any dates that are not actually in the set.
     * @param Brick\DateTime\LocalDate $dates
     * @param array $weekendDays
     */
    public static function getWeekendDatesInSet(array $weekendDays = [6, 7], \Brick\DateTime\LocalDate ...$dates)
    {
        $weekendDates = [];

        foreach ($dates as $date)
        {
            if (in_array($date->getDayOfWeek(), $weekendDays))
            {
                $weekendDates[] = $date;
            }
        }

        return $weekendDates;
    }


    /**
     * Returns the dates (in a collection), that are in all of the provided collections of dates.
     * This is basically array_intersect for LocalDate.
     * @param LocalDateCollection $localDateSets
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
                /* @var $localDate \Brick\DateTime\LocalDate */
                $epochsForSet[] = $localDate->toEpochDay();
            }

            $epochSets[] = $epochsForSet;
        }

        $intersectingEpochs = array_intersect(...$epochSets);

        foreach ($intersectingEpochs as $epochDay)
        {
            $intersectionCollection[] = \Brick\DateTime\LocalDate::ofEpochDay($epochDay);
        }

        return $intersectionCollection;
    }
}
