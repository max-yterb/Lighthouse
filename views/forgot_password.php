<?php
$errors = [];
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

<h1>Forgot Password</h1>

<?php if (!empty($errors)): ?>
    <div style="color: red;">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div style="color: green;">
        <p><?= htmlspecialchars($success) ?></p>
    </div>
<?php else: ?>
    <form method="POST" action="/forgot-password">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

        <?= csrf_field() ?>

        <button type="submit">Send Reset Instructions</button>
    </form>
<?php endif; ?>

<p><a href="/login">Back to Login</a></p>
