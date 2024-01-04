<?php


declare(strict_types = 1);
namespace Programster\DateTime;


use ArrayObject;
use Brick\DateTime\LocalDate;
use DateTime;
use Exception;


final class LocalDateCollection extends ArrayObject
{
    public function __construct(LocalDate ...$dates)
    {
        parent::__construct($dates);
    }


    /**
     * Create one of these collections from an array of dates in string format.
     * @param array $dates - the array of dates in string format. E.g. ['2020-12-25', '2020-12-26']
     * @param $format - the format the string dates are expected to be in. E.g. Y-m-d for 2020-12-25 for Christmas.
     * @return LocalDateCollection
     */
    public static function fromArrayOfStringDates(
        array  $dates,
        string $format = 'Y-m-d'
    ) : LocalDateCollection
    {
        $localDates = [];

        foreach ($dates as $dateString)
        {
            $datetime = DateTime::createFromFormat($format, $dateString);
            $localDates[] = LocalDate::fromNativeDateTime($datetime);
        }

        return new LocalDateCollection(...$localDates);
    }


    public function append($value): void
    {
        if ($value instanceof LocalDate)
        {
            parent::append($value);
        }
        else
        {
            throw new Exception("Cannot append non LocalDate to a " . __CLASS__);
        }
    }


    public function offsetSet($key, $value): void
    {
        if ($value instanceof LocalDate)
        {
            parent::offsetSet($key, $value);
        }
        else
        {
            throw new Exception("Cannot add a non LocalDate value to a " . __CLASS__);
        }
    }


    /**
     * Gets a copy of this object without any weekend dates in it.
     * @param array $weekendDays - optionally override the days of the week that count as weekends. The
     * default is for the weekend to be on Saturday/Sunday.
     * @return LocalDateCollection - a copy of this object, but without any weekend dates.
     */
    public function withoutWeekends(
        array $weekendDays = [6, 7]
    ) : LocalDateCollection
    {
        $dates = $this->getArrayCopy();

        foreach ($dates as $index => $date)
        {
            /* @var $date LocalDate */
            if (in_array($date->getDayOfWeek()->value, $weekendDays))
            {
                unset($dates[$index]);
            }
        }

        return new LocalDateCollection(...$dates);
    }


    /**
     * Get a copy of this collection, but one that does not contain the dates in the collection provided.
     * @param LocalDateCollection $dates - the collection of dates we do not wish to contain.
     * @return LocalDateCollection - the new (modified) copy of the collection
     */
    public function withoutDatesInCollection(LocalDateCollection $dates) : LocalDateCollection
    {
        $thisObjectsDates = $this->getArrayOfStringsForm();
        $otherObjectsDates = $dates->getArrayOfStringsForm();
        $remainingDates = array_diff($thisObjectsDates, $otherObjectsDates);
        return LocalDateCollection::fromArrayOfStringDates($remainingDates);
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
     * @param $format - optionally change the format of the strings. Default is Y-m-d to match MySQL and PgSQL
     * @return array
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
}