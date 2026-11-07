<?php

/*
 * Test that a LocalDateCollection accepts LocalDate objects, but throws a TypeError for anything else, however it is
 * added.
 */

declare(strict_types = 1);

use Brick\DateTime\LocalDate;
use Programster\DateTime\LocalDateCollection;

final class TestCollectionOnlyAcceptsDates implements TestInterface
{
    private bool $m_passed;


    public function __construct()
    {
        $this->m_passed = false;
    }


    public function run() : void
    {
        $collection = new LocalDateCollection();
        $collection[] = LocalDate::of(2024, 1, 1);
        $collection->append(LocalDate::of(2024, 1, 2));
        $collection->exchangeArray(new LocalDateCollection(LocalDate::of(2024, 1, 3)));

        $appendString = function () use ($collection) : void {
            $collection->append('2024-01-01');
        };

        $setString = function () use ($collection) : void {
            $collection[] = '2024-01-01';
        };

        $exchangeForStrings = function () use ($collection) : void {
            $collection->exchangeArray(['2024-01-01']);
        };

        $this->m_passed = (
               $this->throwsTypeError($appendString)
            && $this->throwsTypeError($setString)
            && $this->throwsTypeError($exchangeForStrings)
            && count($collection) === 1
            && $collection[0]->isEqualTo(LocalDate::of(2024, 1, 3))
        );
    }


    /**
     * Checks that calling the closure throws a TypeError.
     * @param Closure $action - the closure to call.
     * @return bool - true if it threw a TypeError.
     */
    private function throwsTypeError(Closure $action) : bool
    {
        try
        {
            $action();
            $threw = false;
        }
        catch (TypeError)
        {
            $threw = true;
        }

        return $threw;
    }


    # Accessors
    public function getPassed() : bool { return $this->m_passed; }
}
