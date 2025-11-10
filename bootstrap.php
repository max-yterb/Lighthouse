<?php

declare(strict_types=1);

// Session management
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Define base paths
$root      = __DIR__;
$core      = "$root/includes";
$viewDir   = "$root/views/";
$logDir    = "$root/logs/";
$dbFile    = "$root/database/database.sqlite";

// Load configuration first
$config = require "$core/config.php";

// Request context
$route     = strtok($_SERVER['REQUEST_URI'], '?');
$hxRequest = isset($_SERVER['HTTP_HX_REQUEST']) && $_SERVER['HTTP_HX_REQUEST'] === 'true';
$layout    = '_layout.php';

// Default metadata
$meta = [
    'title'       => $config['DEFAULT_TITLE'],
    'description' => $config['DEFAULT_DESC'],
    'author'      => $config['DEFAULT_AUTHOR'],
    'keywords'    => $config['DEFAULT_KEYWORDS'],
    'charset'     => $config['DEFAULT_CHARSET'],
    'canonical'   => $config['DEFAULT_CANONICAL'],
];

// Core setup
require_once "$core/utils.php";
require_once "$core/db.php";
require_once "$core/auth.php";
require_once "$core/validation.php";

// Rate limiting setup
$rateLimitFile = "$logDir/rate_limit.json";
if (!file_exists($rateLimitFile)) {
    file_put_contents($rateLimitFile, json_encode([]));
}

// Error handling
set_exception_handler('exception_handler');

// Shutdown handler for rendering
register_shutdown_function('shutdown_handler');
