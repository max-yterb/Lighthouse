<?php

declare(strict_types=1);

// 1. Bootstrap environment and core
require __DIR__ . '/../bootstrap.php';

// 2. Routing
require_once __DIR__ . '/../app_routes.php';
require_once __DIR__ . '/../auth_routes.php';

// Dispatch the route
dispatch($route, $_SERVER['REQUEST_METHOD']);

// 3. Rendering happens at shutdown (in shutdown_handler)
