<?php
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
