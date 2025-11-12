<?php
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

<h1>Login</h1>

<?php if (!empty($errors)): ?>
    <div style="color: red;">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="/login">
    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required>

    <?= csrf_field() ?>

    <button type="submit">Login</button>
</form>

<p><a href="/forgot-password">Forgot password?</a></p>
<p><a href="/register">Don't have an account? Register</a></p>
