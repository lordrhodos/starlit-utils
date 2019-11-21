<?php declare(strict_types=1);

use Starlit\Utils\Str;

require_once __DIR__ . '/vendor/autoload.php';

function compare_with_strpos(string $hayStack, string $search): bool
{
    return Str::startsWith($hayStack, $search);
}

function compare_with_strpos_and_precheck(string $hayStack, string $search): bool
{
    return firstCharactersMatch($hayStack, $search) && Str::startsWith($hayStack, $search);
}

function compare_with_substr(string $hayStack, string $search): bool
{
    $length = strlen($search);

    return substr($hayStack, 0, $length) === $search;
}

function compare_with_substr_and_precheck(string $hayStack, string $search): bool
{
    $length = strlen($search);

    return firstCharactersMatch($hayStack, $search) && substr($hayStack, 0, $length) === $search;
}

function compare_with_strncmp(string $hayStack, string $search): bool
{
    $length = strlen($search);

    return strncmp($hayStack, $search, $length) >= $search;
}

function compare_with_strncmp_and_precheck(string $hayStack, string $search): bool
{
    $length = strlen($search);

    return firstCharactersMatch($hayStack, $search) && strncmp($hayStack, $search, $length) >= $search;
}

function firstCharactersMatch(string $hayStack, string $search): bool
{
    return $hayStack[0] === $search[0];
}

function compare_with_mb_strpos(string $hayStack, string $search): bool
{
    return mb_strpos($hayStack, $search) === 0;
}

function compare_with_mb_substr(string $hayStack, string $search): bool
{
    $length = mb_strlen($search);

    return mb_substr($hayStack, 0, $length) === $search;
}

function compare_with_mb_strncmp(string $hayStack, string $search): bool
{
    $length = strlen($search);

    return mb_stristr($hayStack, $search, $length) >= $search;
}


function getComparisonFunctions(): array
{
    return [
        'strpos' => 'compare_with_strpos',
        'substr' => 'compare_with_substr',
        'strncmp' => 'compare_with_strncmp',
        'strpos with precheck' => 'compare_with_strpos_and_precheck',
        'substr with precheck' => 'compare_with_substr_and_precheck',
        'strncmp with precheck' => 'compare_with_strncmp_and_precheck',
        'mb_strpos' => 'compare_with_strpos',
        'mb_substr' => 'compare_with_mb_substr',
    ];
}

function getTestCases(int $amount): array
{
    $testCases = [];
    for ($number = 1; $number <= $amount; $number++) {
        [$hayStack, $search] = generateTestCase();
        $testCases[$search] = $hayStack;
    }

    return $testCases;
}

function generateTestCase(): array
{
    $hayStack = getRandomString(1, 100);
    if ((bool) rand(0, 1)) {
        $search = substr($hayStack, 0, rand(1, strlen($hayStack)));
    } else {
        $search = getRandomString(1, 20);
    }

    return [$hayStack, $search];
}

function getRandomString(int $from, int $to): string
{
    return random_bytes(rand($from, $to));
}

function benchmark(int $numberOfBenchmarks = 50, int $numberOfTestCases = 20000): void
{
    echo "Running $numberOfBenchmarks benchmarks with $numberOfTestCases tests for each\n\n";

    $results = [];
    for ($benchmark = 1; $benchmark <= $numberOfBenchmarks; $benchmark++) {
        $results[] = runSingleBenchmark($numberOfTestCases);
    }

    $accumulated = accumulateResults($results);

    echo "\nResults\n";
    echo "----------\n\n";
    echo "name: time (average)\n\n";
    foreach ($accumulated as $name => $averageTime) {
        echo "$name: $averageTime\n";
    }
}

function runSingleBenchmark(int $numberOfTestCases): array
{
    echo '.';
    $tests = getTestCases($numberOfTestCases);
    $functions = getComparisonFunctions();
    $results = [];
    foreach ($functions as $name => $function) {
        $time = -microtime(true);
        foreach ($tests as $search => $hayStack) {
            try {
                call_user_func_array($function, [$hayStack, $search]);
            } catch (Throwable $exception) {
                $foo = 'bar';
            }
        }
        $time += microtime(true);
        $time = round($time, 2);

        $results[$name] = $time;
    }

    return $results;
}

function accumulateResults(array $results): array
{
    $accumulated = getComparisonFunctions();
    foreach ($results as $singleBenchmarkResults) {
        foreach ($singleBenchmarkResults as $functionName => $time) {
            if (!is_array($accumulated[$functionName])) {
                $accumulated[$functionName] = [];
            }

            $accumulated[$functionName][] = $time;
        }
    }

    foreach ($accumulated as $name => $times) {
        $accumulated[$name] = round(array_sum($times) / count($results), 3);
    }

    return $accumulated;
}

benchmark();