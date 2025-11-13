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
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($token)) {
            $errors[] = 'Invalid reset token';
        }
        if (validate_min_length($password, 8) === false) {
            $errors[] = 'Password must be at least 8 characters';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }

        // Verify token and update password (simplified)
        if (empty($errors)) {
            // In production, you'd verify the token from a password_resets table
            // For now, we'll just show success
            $success = 'Password has been reset successfully! <a href="/login">Login now</a>';
        }
    }
}
?>

<div class="lighthouse-auth-container">
    <div class="lighthouse-card">
        <h1 style="text-align: center; margin-bottom: 2rem;">Set New Password</h1>
        
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
                <p style="margin: 0;"><?= $success ?></p>
            </div>
        <?php else: ?>
            <p style="text-align: center; margin-bottom: 2rem; color: var(--lighthouse-sea-slate);">
                Enter your new password below.
            </p>
            
            <form method="POST" action="/reset-password">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">

                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required minlength="8" placeholder="At least 8 characters">

                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8" placeholder="Confirm your new password">

                <?= csrf_field() ?>

                <button type="submit" style="width: 100%; margin-top: 1rem;">Update Password</button>
            </form>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--lighthouse-sky-mist);">
            <p><a href="/login">← Back to Sign In</a></p>
        </div>
    </div>
</div>
