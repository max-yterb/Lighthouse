<?php

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
    return view('dashboard.php', [], '_dashboard.php');
});

route('/logout', function () {
    auth_logout();
    header('Location: /login');
    exit;
});
