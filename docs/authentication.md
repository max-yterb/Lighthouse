# Authentication

Lighthouse provides session-based authentication with secure password hashing and rate limiting. Users are authenticated using PHP sessions, with passwords hashed using bcrypt.

## Overview

Authentication in Lighthouse includes:
- **Session-based login** - User ID stored in `$_SESSION['user_id']`
- **Secure password hashing** - bcrypt with PHP's `password_hash()` function
- **CSRF protection** - Token validation on all forms
- **Rate limiting** - Prevent brute-force login attempts
- **Protected routes** - Check if user is logged in before accessing

## Core Functions

### Logging In Users

Use `auth_login()` to start a user session:

```php
function auth_login(int $userId): void
```

**Example:**

```php
// Verify credentials and log in
$user = db_select_one('users', ['email' => $email]);

if ($user && auth_verify_password($password, $user['password'])) {
    auth_login($user['id']);
    header('Location: /dashboard');
    exit;
}
```

The user ID is stored in `$_SESSION['user_id']`.

### Logging Out Users

Use `auth_logout()` to destroy the session:

```php
function auth_logout(): void
```

**Example:**

```php
route('/logout', function() {
    auth_logout();
    header('Location: /login');
    exit;
});
```

### Getting Current User

Use `auth_user()` to get the logged-in user's ID:

```php
function auth_user(): ?int
```

**Returns:** User ID if logged in, `null` if not authenticated

**Example:**

```php
$userId = auth_user();

if ($userId) {
    $user = db_select_one('users', ['id' => $userId]);
    echo "Welcome, " . htmlspecialchars($user['email']);
} else {
    echo "Not logged in";
}
```

### Password Hashing

Use `auth_hash_password()` to securely hash passwords for storage:

```php
function auth_hash_password(string $password): string
```

**Returns:** bcrypt-hashed password

**Example:**

```php
$hashedPassword = auth_hash_password($_POST['password']);

db_insert('users', [
    'email' => $email,
    'password' => $hashedPassword
]);
```

### Password Verification

Use `auth_verify_password()` to check if a password matches a hash:

```php
function auth_verify_password(string $password, string $hash): bool
```

**Returns:** `true` if password matches, `false` otherwise

**Example:**

```php
$user = db_select_one('users', ['email' => $email]);

if ($user && auth_verify_password($password, $user['password'])) {
    // Correct password
    auth_login($user['id']);
}
```

## Protecting Routes

Check authentication before rendering protected content:

### Redirect Unauthenticated Users

```php
route('/dashboard', function() {
    if (!auth_user()) {
        header('Location: /login');
        exit;
    }

    $user = db_select_one('users', ['id' => auth_user()]);
    return view('dashboard.php', ['user' => $user]);
});
```

### Return 403 Forbidden

```php
route('/api/profile', function() {
    if (!auth_user()) {
        http_response_code(403);
        return 'Unauthorized';
    }

    header('Content-Type: application/json');
    $user = db_select_one('users', ['id' => auth_user()]);
    return json_encode($user);
});
```

### Check Resource Ownership

```php
route('/posts/{id}/edit', function($id) {
    $post = db_select_one('posts', ['id' => $id]);

    if (!$post) {
        http_response_code(404);
        return view('404.php');
    }

    // Ensure user owns this post
    if ($post['user_id'] !== auth_user()) {
        http_response_code(403);
        return 'You cannot edit this post';
    }

    return view('edit_post.php', ['post' => $post]);
});
```

## User Registration

### Complete Registration Flow

```php
route('/register', function() {
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF token
        if (!validate_csrf($_POST['csrf_token'] ?? '')) {
            $errors[] = 'Invalid request';
        } else {
            $email = sanitize_email($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validate inputs
            if (!validate_email($email)) {
                $errors[] = 'Invalid email address';
            } elseif (db_select_one('users', ['email' => $email])) {
                $errors[] = 'Email already registered';
            }

            if (!validate_min_length($password, 8)) {
                $errors[] = 'Password must be at least 8 characters';
            } elseif ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }

            // Create user if no errors
            if (empty($errors)) {
                $userId = db_insert('users', [
                    'email' => $email,
                    'password' => auth_hash_password($password),
                ]);

                if ($userId) {
                    auth_login($userId);
                    header('Location: /dashboard');
                    exit;
                } else {
                    $errors[] = 'Registration failed';
                }
            }
        }
    }

    return view('register.php', ['errors' => $errors]);
});
```

### Registration Form Template

```php
<!-- views/register.php -->
<div class="lighthouse-auth-container">
    <div class="lighthouse-card">
        <h1 style="text-align: center;">Create Account</h1>

        <?php if (!empty($errors)): ?>
            <div class="lighthouse-alert error">
                <ul style="margin: 0; padding-left: 1rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="/register">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   required placeholder="your@email.com">

            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   required minlength="8" placeholder="At least 8 characters">

            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password"
                   required minlength="8" placeholder="Confirm password">

            <?= csrf_field() ?>

            <button type="submit" style="width: 100%; margin-top: 1rem;">
                Create Account
            </button>
        </form>

        <div style="text-align: center; margin-top: 2rem;">
            <p><a href="/login">Already have an account? Sign in</a></p>
        </div>
    </div>
</div>
```

## User Login

### Complete Login Flow

```php
route('/login', function() {
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF token
        if (!validate_csrf($_POST['csrf_token'] ?? '')) {
            $errors[] = 'Invalid request';
        } else {
            $email = sanitize_email($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // Validate inputs
            if (!validate_email($email)) {
                $errors[] = 'Invalid email address';
            } elseif (empty($password)) {
                $errors[] = 'Password is required';
            }

            // Check rate limiting
            if (empty($errors) && !check_rate_limit($_SERVER['REMOTE_ADDR'] . ':login')) {
                $errors[] = 'Too many login attempts. Try again in 5 minutes.';
            }

            // Verify credentials
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

    return view('login.php', ['errors' => $errors]);
});
```

### Login Form Template

```php
<!-- views/login.php -->
<div class="lighthouse-auth-container">
    <div class="lighthouse-card">
        <h1 style="text-align: center;">Welcome Back</h1>

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
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   required placeholder="your@email.com">

            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   required placeholder="Your password">

            <?= csrf_field() ?>

            <button type="submit" style="width: 100%;">Sign In</button>
        </form>

        <div style="text-align: center; margin-top: 2rem; border-top: 1px solid var(--border-color);">
            <p><a href="/register">Don't have an account? Create one</a></p>
            <p><a href="/forgot-password">Forgot your password?</a></p>
        </div>
    </div>
</div>
```

## Rate Limiting

Prevent brute-force attacks by limiting failed login attempts:

### Rate Limit Functions

```php
// Check if within rate limit
function check_rate_limit(string $key, int $maxRequests = 5, int $windowSeconds = 300): bool

// Get remaining requests
function get_rate_limit_remaining(string $key): int
```

### Rate Limiting Example

```php
// In login route - limit to 5 attempts per 5 minutes per IP
if (!check_rate_limit($_SERVER['REMOTE_ADDR'] . ':login', 5, 300)) {
    $errors[] = 'Too many login attempts. Please try again later.';
}

// Get remaining attempts for UI feedback
$remaining = get_rate_limit_remaining($_SERVER['REMOTE_ADDR'] . ':login');
echo "Attempts remaining: $remaining";
```

Rate limiting data is stored in `logs/rate_limit.json` and persists across requests.

## CSRF Protection

Always validate CSRF tokens on state-changing requests (POST, PUT, DELETE):

### Adding CSRF Token to Forms

```php
<!-- Use csrf_field() helper to add hidden input -->
<form method="POST" action="/update">
    <?= csrf_field() ?>

    <input type="text" name="username" required>
    <button type="submit">Update</button>
</form>
```

### Validating CSRF Tokens

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request';
    } else {
        // Process form...
    }
}
```

The CSRF token is automatically generated on session start and available via:

```php
$token = csrf_token();  // Get token string
echo csrf_field();      // Get HTML input element
```

## Password Reset (Email-based)

### Password Reset Flow

```php
route('/forgot-password', function() {
    $errors = [];
    $success = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!validate_csrf($_POST['csrf_token'] ?? '')) {
            $errors[] = 'Invalid request';
        } else {
            $email = sanitize_email($_POST['email'] ?? '');

            if (!validate_email($email)) {
                $errors[] = 'Invalid email address';
            } else {
                $user = db_select_one('users', ['email' => $email]);

                if ($user) {
                    // Generate reset token
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

                    // Store reset token
                    db_update('users', [
                        'reset_token' => $token,
                        'reset_expires' => $expires,
                    ], ['id' => $user['id']]);

                    // Send email with reset link
                    $resetLink = "https://yourapp.com/reset-password?token=$token";
                    mail($email, 'Password Reset Request',
                         "Click here to reset your password: $resetLink");

                    $success = true;
                } else {
                    // Don't reveal if email exists (security)
                    $success = true;
                }
            }
        }
    }

    return view('forgot_password.php', [
        'errors' => $errors,
        'success' => $success,
    ]);
});
```

### Password Reset Token Verification

```php
route('/reset-password', function() {
    $token = $_GET['token'] ?? null;
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!validate_csrf($_POST['csrf_token'] ?? '')) {
            $errors[] = 'Invalid request';
        } else {
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            $token = $_POST['token'] ?? null;

            // Validate password
            if (!validate_min_length($password, 8)) {
                $errors[] = 'Password must be at least 8 characters';
            } elseif ($password !== $confirm) {
                $errors[] = 'Passwords do not match';
            }

            // Verify token
            if (empty($errors)) {
                $user = db_select_one('users', ['reset_token' => $token]);

                if (!$user || strtotime($user['reset_expires']) < time()) {
                    $errors[] = 'Reset token expired or invalid';
                } else {
                    // Update password and clear token
                    db_update('users', [
                        'password' => auth_hash_password($password),
                        'reset_token' => null,
                        'reset_expires' => null,
                    ], ['id' => $user['id']]);

                    header('Location: /login?reset=success');
                    exit;
                }
            }
        }
    }

    return view('reset_password.php', [
        'token' => $token,
        'errors' => $errors,
    ]);
});
```

### Database Migration for Reset Tokens

```sql
-- Migration: add_password_reset_to_users
CREATE TABLE IF NOT EXISTS users_new (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    reset_token VARCHAR(255),
    reset_expires DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users_new SELECT id, email, password, NULL, NULL, created_at FROM users;
DROP TABLE users;
ALTER TABLE users_new RENAME TO users;
```

## Conditional Layout Rendering

Show different navigation based on authentication status:

```php
<!-- views/_layout.php -->
<nav>
    <ul>
        <li><a href="/">Home</a></li>
    </ul>

    <?php if (auth_user()): ?>
        <ul>
            <li><a href="/dashboard">Dashboard</a></li>
            <li><a href="/profile">Profile</a></li>
            <li><a href="/logout" class="contrast">Logout</a></li>
        </ul>
    <?php else: ?>
        <ul>
            <li><a href="/login" class="contrast">Login</a></li>
            <li><a href="/register" class="contrast">Register</a></li>
        </ul>
    <?php endif; ?>
</nav>
```

## Best Practices

### 1. Always Hash Passwords

```php
// ✓ Good
$hashedPassword = auth_hash_password($password);
db_insert('users', ['email' => $email, 'password' => $hashedPassword]);

// ✗ Bad
db_insert('users', ['email' => $email, 'password' => $password]);
```

### 2. Validate CSRF on All POST Requests

```php
// ✓ Good
if (!validate_csrf($_POST['csrf_token'] ?? '')) {
    die('Invalid request');
}

// ✗ Bad
// Process POST without CSRF validation
```

### 3. Use Generic Error Messages

```php
// ✓ Good - doesn't reveal if email exists
$errors[] = 'Invalid email or password';

// ✗ Bad - reveals if user exists
if (!$user) {
    $errors[] = 'Email not found';
}
```

### 4. Implement Rate Limiting

```php
// ✓ Good - prevent brute force
if (!check_rate_limit($key, 5, 300)) {
    $errors[] = 'Too many attempts';
}

// ✗ Bad - allows unlimited attempts
```

### 5. Sanitize and Validate Email

```php
// ✓ Good
$email = sanitize_email($_POST['email']);
if (!validate_email($email)) {
    $errors[] = 'Invalid email';
}

// ✗ Bad
$email = $_POST['email'];
```

### 6. Use HTTPS in Production

Always use HTTPS when transmitting authentication credentials. Session cookies should have secure and httponly flags:

```php
// Add to bootstrap.php for production
if ($_ENV['APP_ENV'] === 'production') {
    session_set_cookie_params([
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
```

### 7. Keep Sessions Secure

```php
// Regenerate session ID after login to prevent session fixation
session_regenerate_id(true);
auth_login($userId);
```

## Troubleshooting

### Session Not Persisting

Ensure `session_start()` is called in `bootstrap.php` before any output.

### Forgot Password Emails Not Sending

Test with `mail()` function or configure an SMTP provider:

```php
// Using PHPMailer or similar
// $mail->send() instead of mail()
```

### CSRF Token Mismatch

Verify the token is generated and validated:

```php
// Check token is in session
var_dump($_SESSION['csrf_token']);

// Verify form includes csrf_field()
// <?= csrf_field() ?>
```

## See Also

- [Validation](validation.md) - Validate user input before login
- [Database](database.md) - Store user credentials securely
- [Views & Templates](views.md) - Create auth UI templates
- [Routing](routing.md) - Create auth routes and protect endpoints
