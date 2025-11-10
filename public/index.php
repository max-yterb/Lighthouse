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

route('/register', function () {
    return view('register.php');
});

route('/login', function () {
    return view('login.php');
});

route('/forgot-password', function () {
    return view('forgot_password.php');
});

route('/reset-password', function () {
    return view('reset_password.php');
});

route('/dashboard', function () {
    return view('dashboard.php', [], '_dashboard');
});

route('/logout', function () {
    auth_logout();
    header('Location: /');
    exit;
});

// Dispatch the route
dispatch($route, $_SERVER['REQUEST_METHOD']);

// 3. Rendering happens at shutdown (in shutdown_handler)
