<?php

declare(strict_types=1);

/** @var array<string> $errors */
$errors = [];
/** @var string $success */
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF
    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request';
    } else {
        $email = sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        if (!validate_email($email)) {
            $errors[] = 'Invalid email address';
        }
        if (validate_min_length($password, 8) === false) {
            $errors[] = 'Password must be at least 8 characters';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }

        // Check if user already exists
        if (empty($errors)) {
            $existingUser = db_select_one('users', ['email' => $email]);
            if ($existingUser) {
                $errors[] = 'Email already registered';
            } else {
                // Create user
                $userId = db_insert('users', [
                    'email' => $email,
                    'password' => auth_hash_password($password)
                ]);

                if ($userId) {
                    header('Location: /dashboard');
                } else {
                    $errors[] = 'Registration failed. Please try again.';
                }
            }
        }
    }
}
?>

<div class="lighthouse-auth-container">
    <div class="lighthouse-card">
        <h1 style="text-align: center; margin-bottom: 2rem;">Create Account</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="lighthouse-alert error">
                <ul style="margin: 0; padding-left: 1rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="lighthouse-alert success">
                <p style="margin: 0;"><?= htmlspecialchars($success) ?></p>
            </div>
        <?php else: ?>
            <form method="POST" action="/register">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required placeholder="Enter your email">

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="8" placeholder="At least 8 characters">

                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8" placeholder="Confirm your password">

                <?= csrf_field() ?>

                <button type="submit" style="width: 100%; margin-top: 1rem;">Create Account</button>
            </form>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--lighthouse-sky-mist);">
            <p><a href="/login">Already have an account? Sign in</a></p>
        </div>
    </div>
</div>
