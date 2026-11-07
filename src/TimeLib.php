<?php

/*
 * A library to help with performing date/time operations that we can't just use brick/time directly for.
 */

declare(strict_types = 1);
namespace Programster\DateTime;


use Brick\DateTime\DayOfWeek;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateRange;
use Brick\DateTime\TimeZone;
use InvalidArgumentException;


final class TimeLib
{
    /**
     * Creates a Brick library Timezone from the Programster TimeZoneEnum enum.
     * @param TimeZoneEnum $timezone - an enum for the timezone.
     * @return TimeZone
     * @throws \Exception
     */
    public static function brickTimeZone(TimeZoneEnum $timezone) : TimeZone
    {
        return TimeZone::fromNativeDateTimeZone($timezone->toDateTimeZone());
    }


    /**
     * Returns the largest set of contiguous dates within the provided dates.
     * This will sort the dates chronologically before performing the scan.
     *
     * @param bool $includeDuplicates - whether to include any duplicate dates in the subset or not. If false, then the
     * returned subset will always contain unique dates.
     *
     * @param LocalDate ...$dates - the dates to find the largest contiguous subset within.
     *
     * @return LocalDateCollection - the largest contiguous subset, in chronological order. This is empty if no dates
     * were provided.
     */
    public static function getLargestContiguousDatesSubset(
        bool $includeDuplicates,
        LocalDate ...$dates
    ) : LocalDateCollection
    {
        $largestSet = new LocalDateCollection();

        if (count($dates) > 0)
        {
            $sortedDates = self::sortDates($dates);
            $previousDate = null;
            $currentSet = [];

            foreach ($sortedDates as $loopDate)
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
     *
     * @param LocalDate ...$dates - the dates to convert. They don't need to be in order, and duplicates are ignored.
     *
     * @return LocalDateRange[] - the ranges, in chronological order. This is empty if no dates were provided.
     */
    public static function convertDatesToContiguousDateRanges(LocalDate ...$dates) : array
    {
        $ranges = [];
        $sets = [];
        $currentSet = [];

        if (count($dates) > 0)
        {
            $sortedDates = self::sortDates($dates);
            $previousDate = null;

            foreach ($sortedDates as $loopDate)
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
     * Given a set of dates, return all the weekend dates that fall within that set's range (from its earliest date
     * to its latest date). This will include dates that are not necessarily in the set itself.
     * If you want the dates that are in the set that are on the weekend, use the getWeekendDatesInSet() method.
     *
     * @param LocalDateCollection $dates - the dates to get the weekend dates between. Must contain at least one date.
     *
     * @param DayOfWeek[] $weekendDays - optionally, the days of the week that are considered the weekend. Defaults
     * to Saturday and Sunday, but you may wish to set others: https://bit.ly/3Ih474Y
     *
     * @return LocalDateCollection - A collection of LocalDate objects for the weekend dates that exist.
     *
     * @throws InvalidArgumentException - if $dates is empty, or $weekendDays contains anything but DayOfWeek cases.
     */
    public static function getWeekendsBetweenDates(
        LocalDateCollection $dates,
        array $weekendDays = [DayOfWeek::SATURDAY, DayOfWeek::SUNDAY]
    ) : LocalDateCollection
    {
        self::assertValidWeekendDays($weekendDays);

        if (count($dates) === 0)
        {
            throw new InvalidArgumentException("At least one date is needed to get the weekends between dates.");
        }

        $dateArray = $dates->getArrayCopy();
        $min = LocalDate::minOf(...$dateArray);
        $max = LocalDate::maxOf(...$dateArray);
        $weekendDates = new LocalDateCollection();

        for ($i = $min->toEpochDay(); $i <= $max->toEpochDay(); $i++)
        {
            $date = LocalDate::ofEpochDay($i);

            if (in_array($date->getDayOfWeek(), $weekendDays, true))
            {
                $weekendDates[] = $date;
            }
        }

        return $weekendDates;
    }


    /**
     * Returns the dates within the set that fall on the weekend. This is similar to getWeekendsBetweenDates()
     * except that this won't return any dates that are not actually in the set.
     *
     * @param LocalDateCollection $dates - the dates to pick the weekend dates out of.
     *
     * @param DayOfWeek[] $weekendDays - optionally, the days of the week that are considered the weekend. Defaults
     * to Saturday and Sunday.
     *
     * @return LocalDateCollection - the dates in $dates that fall on the weekend, in their original order.
     *
     * @throws InvalidArgumentException - if $weekendDays contains anything but DayOfWeek cases.
     */
    public static function getWeekendDatesInSet(
        LocalDateCollection $dates,
        array $weekendDays = [DayOfWeek::SATURDAY, DayOfWeek::SUNDAY]
    ) : LocalDateCollection
    {
        self::assertValidWeekendDays($weekendDays);
        $weekendDates = new LocalDateCollection();

        foreach ($dates as $date)
        {
            /* @var $date LocalDate */
            if (in_array($date->getDayOfWeek(), $weekendDays, true))
            {
                $weekendDates[] = $date;
            }
        }

        return $weekendDates;
    }


    /**
     * Returns the dates (in a collection), that are in all the provided collections of dates.
     * This is basically array_intersect for LocalDate, except that each date appears in the result only once.
     *
     * @param LocalDateCollection ...$localDateSets - the collections of dates to intersect.
     *
     * @return LocalDateCollection - the dates found in every collection, in the order they appear in the first one.
     * This is empty if no collections were provided.
     */
    public static function getDatesIntersection(LocalDateCollection ...$localDateSets) : LocalDateCollection
    {
        $intersectionCollection = new LocalDateCollection();

        if (count($localDateSets) > 0)
        {
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

            $intersectingEpochs = array_unique(array_intersect(...$epochSets));

            foreach ($intersectingEpochs as $epochDay)
            {
                $intersectionCollection[] = LocalDate::ofEpochDay($epochDay);
            }
        }

        return $intersectionCollection;
    }


    /**
     * Converts a LocalDateRange object into a LocalDateCollection which contains the full list of
     * dates between the start and end dates of the date range.
     *
     * @param LocalDateRange $dateRange - the range to convert.
     *
     * @return LocalDateCollection - every date in the range, including its start and end dates.
     */
    public static function convertDateRangeToDateCollection(LocalDateRange $dateRange) : LocalDateCollection
    {
        $dates = [];

        foreach ($dateRange as $dateInRange)
        {
            $dates[] = $dateInRange;
        }

        return new LocalDateCollection(...$dates);
    }


    /**
     * Sorts the dates chronologically.
     * @param LocalDate[] $dates - the dates to sort.
     * @return LocalDate[] - the sorted dates.
     */
    private static function sortDates(array $dates) : array
    {
        $sorter = function (LocalDate $a, LocalDate $b) : int {
            return $a->compareTo($b);
        };

        usort($dates, $sorter);
        return $dates;
    }


    /**
     * Checks that the weekend days provided are all DayOfWeek cases. Plain integers aren't accepted, because PHP's
     * own date('w') numbers Sunday as 0, whereas Brick numbers it as 7, making it easy to pick the wrong day.
     *
     * @param array $weekendDays - the weekend days to check.
     *
     * @return void
     *
     * @throws InvalidArgumentException - if any of them isn't a DayOfWeek.
     */
    private static function assertValidWeekendDays(array $weekendDays) : void
    {
        foreach ($weekendDays as $weekendDay)
        {
            if (!($weekendDay instanceof DayOfWeek))
            {
                $type = get_debug_type($weekendDay);

                throw new InvalidArgumentException(
                    "Weekend days must be " . DayOfWeek::class . " cases (e.g. DayOfWeek::SATURDAY), {$type} given."
                );
            }
        }
    }
}
