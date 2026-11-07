<?php

/*
 * Runs every test in the tests folder, and exits with 0 if they all passed, or 1 if any failed or threw.
 */

declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

$classDirs = [__DIR__];
$autoloader = new \Programster\Autoloader\Autoloader($classDirs);
$testPaths = glob(__DIR__ . '/tests/*.php');

if ($testPaths === false)
{
    throw new RuntimeException("Failed to list the test files in " . __DIR__ . "/tests");
}

$testFiles = array_map('basename', $testPaths);
$failedCount = 0;


foreach ($testFiles as $testFilename)
{
    $testClassName = substr($testFilename, 0, -4);
    $errorDetails = "";

    try
    {
        include_once __DIR__ . "/tests/{$testFilename}";

        /* @var $test TestInterface */
        $test = new $testClassName();
        $test->run();
        $passed = $test->getPassed();
    }
    catch (Throwable $e)
    {
        // Count a test that throws as a failure, and carry on with the others.
        $passed = false;
        $errorDetails = " (threw " . get_class($e) . ": {$e->getMessage()})";
    }

    if ($passed)
    {
        $testStatusString = "\e[1m\e[32mPASSED\e[0m";
    }
    else
    {
        $failedCount++;
        $testStatusString = "\e[1m\e[31mFAILED\e[0m{$errorDetails}";
    }

    print "{$testFilename} : {$testStatusString}" . PHP_EOL;
}

$testCount = count($testFiles);
print PHP_EOL . ($testCount - $failedCount) . " of {$testCount} tests passed." . PHP_EOL;
exit($failedCount === 0 ? 0 : 1);
