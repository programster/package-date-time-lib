<?php


declare(strict_types = 1);
namespace Programster\DateTime;


use ArrayObject;
use Brick\DateTime\DayOfWeek;
use Brick\DateTime\LocalDate;
use DateTimeImmutable;
use InvalidArgumentException;
use TypeError;


final class LocalDateCollection extends ArrayObject
{
    public function __construct(LocalDate ...$dates)
    {
        parent::__construct($dates);
    }


    /**
     * Create one of these collections from an array of dates in string format.
     * Each string must exactly match the format, and be a real date (e.g. 2020-02-30 is rejected, rather than
     * being rolled over to the 1st of March).
     *
     * @param string[] $dates - the array of dates in string format. E.g. ['2020-12-25', '2020-12-26']
     *
     * @param string $format - the format the string dates are expected to be in, using the same characters as
     * DateTime::createFromFormat(). Defaults to Y-m-d, e.g. 2020-12-25 for Christmas.
     *
     * @return LocalDateCollection - a collection of the dates, in the same order.
     *
     * @throws InvalidArgumentException - if any of the dates isn't a string, or isn't a valid date in that format.
     */
    public static function fromArrayOfStringDates(
        array  $dates,
        string $format = 'Y-m-d'
    ) : LocalDateCollection
    {
        $localDates = [];

        foreach ($dates as $dateString)
        {
            if (!is_string($dateString))
            {
                $type = get_debug_type($dateString);
                throw new InvalidArgumentException("Dates must be given as strings, {$type} given.");
            }

            // The ! resets any fields not in the format, rather than filling them in from the current time.
            $datetime = DateTimeImmutable::createFromFormat("!{$format}", $dateString);
            $errors = DateTimeImmutable::getLastErrors();

            $isValid = (
                   $datetime !== false
                && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            );

            if (!$isValid)
            {
                $message = "\"{$dateString}\" is not a valid date in the format \"{$format}\".";
                throw new InvalidArgumentException($message);
            }

            $localDates[] = LocalDate::fromNativeDateTime($datetime);
        }

        return new LocalDateCollection(...$localDates);
    }


    /**
     * Adds a date to the end of the collection.
     * @param mixed $value - the date to add. Must be a LocalDate.
     * @return void
     * @throws TypeError - if $value isn't a LocalDate.
     */
    public function append(mixed $value) : void
    {
        self::assertIsLocalDate($value);
        parent::append($value);
    }


    /**
     * Sets the date at the given key. This is what's used for $collection[] = $date and $collection[$key] = $date.
     * @param mixed $key - the key to set, or null to add the date to the end.
     * @param mixed $value - the date to set. Must be a LocalDate.
     * @return void
     * @throws TypeError - if $value isn't a LocalDate.
     */
    public function offsetSet(mixed $key, mixed $value) : void
    {
        self::assertIsLocalDate($value);
        parent::offsetSet($key, $value);
    }


    /**
     * Replaces all the dates in this collection.
     *
     * @param array|object $array - the new dates, as an array of LocalDate objects or another LocalDateCollection.
     *
     * @return array - the dates that were in the collection before.
     *
     * @throws TypeError - if $array isn't an array or LocalDateCollection, or contains anything but LocalDate objects.
     */
    public function exchangeArray(array|object $array) : array
    {
        if ($array instanceof LocalDateCollection)
        {
            $newDates = $array->getArrayCopy();
        }
        elseif (is_array($array))
        {
            $newDates = $array;
        }
        else
        {
            $type = get_debug_type($array);
            throw new TypeError("A " . __CLASS__ . " can only be given an array of LocalDate objects, {$type} given.");
        }

        foreach ($newDates as $date)
        {
            self::assertIsLocalDate($date);
        }

        return parent::exchangeArray($newDates);
    }


    /**
     * Gets a copy of this object without any weekend dates in it.
     *
     * @param DayOfWeek[] $weekendDays - optionally override the days of the week that count as weekends. The
     * default is for the weekend to be on Saturday/Sunday.
     *
     * @return LocalDateCollection - a copy of this object, but without any weekend dates.
     *
     * @throws InvalidArgumentException - if $weekendDays contains anything but DayOfWeek cases.
     */
    public function withoutWeekends(
        array $weekendDays = [DayOfWeek::SATURDAY, DayOfWeek::SUNDAY]
    ) : LocalDateCollection
    {
        return $this->withoutDatesInCollection(TimeLib::getWeekendDatesInSet($this, $weekendDays));
    }


    /**
     * Get a copy of this collection, but one that does not contain the dates in the collection provided.
     * @param LocalDateCollection $dates - the collection of dates we do not wish to contain.
     * @return LocalDateCollection - the new (modified) copy of the collection
     */
    public function withoutDatesInCollection(LocalDateCollection $dates) : LocalDateCollection
    {
        $excludedEpochDays = [];
        $remainingDates = [];

        foreach ($dates as $date)
        {
            /* @var $date LocalDate */
            $excludedEpochDays[$date->toEpochDay()] = true;
        }

        foreach ($this->getArrayCopy() as $date)
        {
            /* @var $date LocalDate */
            if (!isset($excludedEpochDays[$date->toEpochDay()]))
            {
                $remainingDates[] = $date;
            }
        }

        return new LocalDateCollection(...$remainingDates);
    }


    /**
     * Get a copy of this collection, but one that does not contain the dates provided.
     * @param LocalDate ...$dates - the dates we do not wish to contain.
     * @return LocalDateCollection - the new (modified) copy of the collection
     */
    public function withoutDates(LocalDate ...$dates) : LocalDateCollection
    {
        return $this->withoutDatesInCollection(new LocalDateCollection(...$dates));
    }


    /**
     * Convert this collection of LocalDate objects into an array of string dates.
     * @param string $format - optionally change the format of the strings. Default is Y-m-d to match MySQL and PgSQL
     * @return string[] - the dates as strings, in the same order as the collection.
     */
    public function getArrayOfStringsForm(string $format = 'Y-m-d') : array
    {
        $newForm = [];
        $dates = $this->getArrayCopy();

        foreach ($dates as $date)
        {
            /* @var $date LocalDate */
            $newForm[] = $date->toNativeDateTime()->format($format);
        }

        return $newForm;
    }


    /**
     * Checks that a value being put into the collection is a LocalDate.
     * @param mixed $value - the value to check.
     * @return void
     * @throws TypeError - if it isn't a LocalDate.
     */
    private static function assertIsLocalDate(mixed $value) : void
    {
        if (!($value instanceof LocalDate))
        {
            $type = get_debug_type($value);
            throw new TypeError("A " . __CLASS__ . " can only contain LocalDate objects, {$type} given.");
        }
    }
}
