<?php

declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

$classDirs = array(__DIR__);
$autoloader = new \iRAP\Autoloader\Autoloader($classDirs);
$testFiles = Programster\CoreLibs\Filesystem::getDirContents(__DIR__ . '/tests', false, false, true);


foreach ($testFiles as $testFilename)
{
    $testClassName = substr($testFilename, 0, -4);
    include_once __DIR__ . "/tests/{$testFilename}";

    /* @var $test TestInterface */
    $test = new $testClassName();
    $test->run();

    if ($test->getPassed())
    {
        $testStatusString = "\e[1m\e[32mPASSED\e[0m";
    }
    else
    {
        $testStatusString = "\e[1m\e[31mFAILED\e[0m";
    }

    print "{$testFilename} : {$testStatusString}" . PHP_EOL;
}