<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

declare(strict_types = 1);

class TestCreateInvalidDate implements TestInterface
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
            $nonExistentDate = Brick\DateTime\LocalDate::of(2024, 2, 30);
            $this->m_passed = false;
        }
        catch (Brick\DateTime\DateTimeException $ex)
        {
            $this->m_passed = true;
        }
    }


    public function getPassed(): bool
    {
        return $this->m_passed;
    }
}
