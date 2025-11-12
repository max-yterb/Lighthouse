<?php
$errors = [];
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

<h1>Register</h1>

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
    <form method="POST" action="/register">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="8">

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">

        <?= csrf_field() ?>

        <button type="submit">Register</button>
    </form>
<?php endif; ?>

<p><a href="/login">Already have an account? Login</a></p>
