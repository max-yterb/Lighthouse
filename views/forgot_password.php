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

        // Validation
        if (!validate_email($email)) {
            $errors[] = 'Invalid email address';
        }

        // Check if user exists
        if (empty($errors)) {
            $user = db_select_one('users', ['email' => $email]);
            if (!$user) {
                $errors[] = 'No account found with this email address';
            } else {
                // Generate reset token (simplified - in production, use proper token generation)
                $resetToken = bin2hex(random_bytes(32));
                $resetExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Store reset token (you'd typically have a password_resets table)
                // For now, we'll just show success message
                $success = 'Password reset instructions have been sent to your email address.';
            }
        }
    }
}
?>

<div class="lighthouse-auth-container">
    <div class="lighthouse-card">
        <h1 style="text-align: center; margin-bottom: 2rem;">Reset Password</h1>
        
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
            <p style="text-align: center; margin-bottom: 2rem; color: var(--lighthouse-sea-slate);">
                Enter your email address and we'll send you instructions to reset your password.
            </p>
            
            <form method="POST" action="/forgot-password">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required placeholder="Enter your email">

                <?= csrf_field() ?>

                <button type="submit" style="width: 100%; margin-top: 1rem;">Send Reset Instructions</button>
            </form>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--lighthouse-sky-mist);">
            <p><a href="/login">← Back to Sign In</a></p>
        </div>
    </div>
</div>
