<?php
session_start();

require_once __DIR__ . '/assertions.php';

$testFiles = glob(__DIR__ . '/*_test.php');

echo "\n\n";

foreach ($testFiles as $file) {
    echo "Running tests in $file...\n\n";
    require $file;
    echo "\n\n";
}

$results = get_test_results();

echo "\n===== Test Summary =====\n";
echo "Total: {$results['total']}\n";
echo "Passed: {$results['passed']}\n";
echo "Failed: {$results['failed']}\n";
echo "========================\n";

exit($results['failed'] > 0 ? 1 : 0);
