<?php

declare(strict_types=1);

// 1. Bootstrap environment and core
require __DIR__ . '/../bootstrap.php';

// 2. Routing
route('/', function () {
    return view('home.php', [
        'title' => 'Home Max',
        'description' => 'My Home Page',
    ]);
});

// HTMX partial route – skip layout
route('/htmx', function () {
    return '<p>Hello from HTMX</p>';
});

require_once __DIR__ . '/../auth_routes.php';

// Dispatch the route
dispatch($route, $_SERVER['REQUEST_METHOD']);

// 3. Rendering happens at shutdown (in shutdown_handler)
