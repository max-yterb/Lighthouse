<?php
// Check if user is logged in
if (!auth_user()) {
    header('Location: /login');
    exit;
}

// Get user data
$user = db_select_one('users', ['id' => auth_user()]);
?>

<h1>Welcome to your Dashboard</h1>

<p>Hello, <?= htmlspecialchars($user['email']) ?>!</p>

<p>This is your personal dashboard. You are successfully logged in.</p>

<div>
    <a href="/logout">Logout</a>
</div>
