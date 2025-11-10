<?php

// Test counters
$test_passed = 0;
$test_failed = 0;
$test_total = 0;

function assert_true($condition, $message = '')
{
    if (!$condition) {
        throw new Exception("❌ Assertion failed: $message");
    }
}

function assert_false($condition, $message = '')
{
    if ($condition) {
        throw new Exception("❌ Assertion failed: $message");
    }
}

function assert_equals($expected, $actual, $message = '')
{
    if ($expected !== $actual) {
        throw new Exception("❌ Assertion failed: expected '$expected', got '$actual'. $message");
    }
}

function assert_not_empty($value, $message = '')
{
    if (empty($value)) {
        throw new Exception("Assertion failed: value is empty. $message");
    }
}

function assert_contains($needle, $haystack, $message = '')
{
    if (!in_array($needle, $haystack)) {
        throw new Exception("❌ Assertion failed: '$needle' not found in array. $message");
    }
}

function assert_not_equals($expected, $actual, $message = '')
{
    if ($expected === $actual) {
        throw new Exception("❌ Assertion failed: values should not be equal. Expected not '$expected', got '$actual'. $message");
    }
}

function assert_string_contains($haystack, $needle, $message = '')
{
    if (strpos($haystack, $needle) === false) {
        throw new Exception("❌ Assertion failed: '$needle' not found in string. $message");
    }
}

function assert_not_null($value, $message = '')
{
    if ($value === null) {
        throw new Exception("❌ Assertion failed: value is null. $message");
    }
}

function assert_null($value, $message = '')
{
    if ($value !== null) {
        throw new Exception("❌ Assertion failed: value is not null. $message");
    }
}

function assert_instance_of($expectedClass, $actual, $message = '')
{
    if (!($actual instanceof $expectedClass)) {
        throw new Exception("❌ Assertion failed: expected instance of $expectedClass. $message");
    }
}

function assert_is_int($value, $message = '')
{
    if (!is_int($value)) {
        throw new Exception("❌ Assertion failed: value is not an integer. $message");
    }
}

function assert_count($expectedCount, $array, $message = '')
{
    if (!is_array($array) && !($array instanceof Countable)) {
        throw new Exception("❌ Assertion failed: value is not countable. $message");
    }
    $actualCount = count($array);
    if ($actualCount !== $expectedCount) {
        throw new Exception("❌ Assertion failed: expected count $expectedCount, got $actualCount. $message");
    }
}

function assert_string_ends_with($suffix, $string, $message = '')
{
    if (!str_ends_with($string, $suffix)) {
        throw new Exception("❌ Assertion failed: string does not end with '$suffix'. $message");
    }
}

function assert_file_exists($filename, $message = '')
{
    if (!file_exists($filename)) {
        throw new Exception("❌ Assertion failed: file '$filename' does not exist. $message");
    }
}

function run_test($description, $callback)
{
    global $test_passed, $test_failed, $test_total;
    $test_total++;

    try {
        $callback();
        echo "  ✅ $description\n";
        $test_passed++;
    } catch (Exception $e) {
        echo "  ❌ $description: " . $e->getMessage() . "\n";
        $test_failed++;
    }
}

function get_test_results()
{
    global $test_passed, $test_failed, $test_total;
    return [
        'total' => $test_total,
        'passed' => $test_passed,
        'failed' => $test_failed
    ];
}
