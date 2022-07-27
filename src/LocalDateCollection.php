<?php


declare(strict_types = 1);
namespace Programster\DateTime;


final class LocalDateCollection extends \ArrayObject
{
    public function __construct(\Brick\DateTime\LocalDate ...$dates)
    {
        parent::__construct($dates);
    }


    public function append($value)
    {
        if ($value instanceof \Brick\DateTime\LocalDate)
        {
            parent::append($value);
        }
        else
        {
            throw new Exception("Cannot append non LocalDate to a " . __CLASS__);
        }
    }


    public function offsetSet($index, $newval)
    {
        if ($newval instanceof \Brick\DateTime\LocalDate)
        {
            parent::offsetSet($index, $newval);
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
            if (in_array($date->getDayOfWeek()->getValue(), $weekendDays))
            {
                unset($dates[$index]);
            }
        }

        return new LocalDateCollection(...$dates);
    }
}