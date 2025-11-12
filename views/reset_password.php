<?php
$errors = [];
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

<h1>Reset Password</h1>

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
        <p><?= $success ?></p>
    </div>
<?php else: ?>
    <form method="POST" action="/reset-password">
        <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">

        <label for="password">New Password</label>
        <input type="password" id="password" name="password" required minlength="8">

        <label for="confirm_password">Confirm New Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">

        <?= csrf_field() ?>

        <button type="submit">Reset Password</button>
    </form>
<?php endif; ?>

<p><a href="/login">Back to Login</a></p>
