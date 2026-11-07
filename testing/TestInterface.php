<?php

/*
 * An interface all tests must implement.
 */

declare(strict_types = 1);

interface TestInterface
{
    public function run() : void;
    public function getPassed() : bool;
}
