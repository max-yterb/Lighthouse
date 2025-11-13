<?php

declare(strict_types=1);

/** @var array<string> $errors */
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF
    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request';
    } else {
        $email = sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Basic validation
        if (!validate_email($email)) {
            $errors[] = 'Invalid email address';
        }
        if (empty($password)) {
            $errors[] = 'Password is required';
        }

        // Check rate limiting
        if (empty($errors) && !check_rate_limit($_SERVER['REMOTE_ADDR'] . ':login')) {
            $errors[] = 'Too many login attempts. Please try again later.';
        }

        // Authenticate user
        if (empty($errors)) {
            $user = db_select_one('users', ['email' => $email]);

            if ($user && auth_verify_password($password, $user['password'])) {
                auth_login($user['id']);
                header('Location: /dashboard');
                exit;
            } else {
                $errors[] = 'Invalid email or password';
            }
        }
    }
}
?>

<div class="lighthouse-auth-container">
    <div class="lighthouse-card">
        <h1 style="text-align: center; margin-bottom: 2rem;">Welcome Back</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="lighthouse-alert error">
                <ul style="margin: 0; padding-left: 1rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="/login">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required placeholder="Enter your email">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Enter your password">

            <?= csrf_field() ?>

            <button type="submit" style="width: 100%; margin-top: 1rem;">Sign In</button>
        </form>

        <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--lighthouse-sky-mist);">
            <p><a href="/register">Don't have an account? Create one</a></p>
            <p><a href="/forgot-password">Forgot your password?</a></p>
        </div>
    </div>
</div>
