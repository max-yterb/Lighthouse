<?php

// Test bootstrap
define('TESTING', true);

// Make sure viewDir is set for tests
global $viewDir, $dbFile, $logDir;
$viewDir = __DIR__ . '/../views/';
$dbFile  = __DIR__ . '/test.sqlite';
$logDir  = __DIR__ . '/';

// Load assertions
require_once __DIR__ . '/assertions.php';

// Autoload include files
foreach (glob(__DIR__ . '/../includes/*.php') as $file) {
    require_once $file;
}
