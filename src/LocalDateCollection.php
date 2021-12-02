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
}