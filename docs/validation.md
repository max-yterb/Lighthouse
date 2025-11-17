# Validation

Lighthouse provides a comprehensive set of validation and sanitization functions to ensure data integrity and security. These functions are designed to work seamlessly with HTML forms and API requests.

## Overview

Validation in Lighthouse includes:
- **Input validation** - Check data meets requirements
- **Data sanitization** - Clean and escape user input
- **Type checking** - Validate data types (email, URL, IP, etc.)
- **Constraint checking** - Enforce length, format, and value rules

## Sanitization Functions

Sanitization functions clean and escape input to prevent security issues and database errors.

### Sanitize Strings

Use `sanitize_string()` to trim and escape HTML characters:

```php
function sanitize_string(mixed $input): string
```

**Example:**

```php
$name = sanitize_string($_POST['name']);
// "John Doe" → "John Doe"
// "<script>alert('xss')</script>" → "&lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;"
```

### Sanitize Email

Use `sanitize_email()` for email addresses:

```php
function sanitize_email(mixed $email): string
```

**Example:**

```php
$email = sanitize_email($_POST['email']);
// "  User@Example.COM  " → "user@example.com"
```

### Sanitize Integers

Use `sanitize_int()` to convert to integer:

```php
function sanitize_int(mixed $input): int
```

**Example:**

```php
$id = sanitize_int($_POST['id']);
// "42" → 42
// "42.99" → 42
// "abc" → 0
```

### Sanitize Floats

Use `sanitize_float()` to convert to float:

```php
function sanitize_float(mixed $input): float
```

**Example:**

```php
$price = sanitize_float($_POST['price']);
// "19.99" → 19.99
// "$20.50" → 20.5
```

### Sanitize URLs

Use `sanitize_url()` for URLs:

```php
function sanitize_url(mixed $url): string
```

**Example:**

```php
$website = sanitize_url($_POST['website']);
// "  https://example.com  " → "https://example.com"
```

## Validation Functions

Validation functions return boolean values indicating whether data meets requirements.

### Required/Not Empty

Use `validate_required()` to check a value is not empty:

```php
function validate_required(mixed $value): bool
```

**Example:**

```php
if (!validate_required($_POST['name'])) {
    $errors[] = 'Name is required';
}

// Note: Accepts '0' as valid (not empty)
validate_required('0');      // true
validate_required('');       // false
validate_required(null);     // false
```

### Email Validation

Use `validate_email()` to check email format:

```php
function validate_email(mixed $value): bool
```

**Example:**

```php
$email = $_POST['email'];

if (!validate_email($email)) {
    $errors[] = 'Invalid email address';
}

// Valid emails
validate_email('user@example.com');        // true
validate_email('test+tag@example.co.uk');  // true

// Invalid emails
validate_email('notanemail');              // false
validate_email('user@');                   // false
validate_email('@example.com');            // false
```

### Length Validation

#### Minimum Length

Use `validate_min_length()` to enforce minimum length:

```php
function validate_min_length(mixed $value, int $length): bool
```

**Example:**

```php
$password = $_POST['password'];

if (!validate_min_length($password, 8)) {
    $errors[] = 'Password must be at least 8 characters';
}
```

#### Maximum Length

Use `validate_max_length()` to enforce maximum length:

```php
function validate_max_length(mixed $value, int $length): bool
```

**Example:**

```php
$bio = $_POST['bio'];

if (!validate_max_length($bio, 500)) {
    $errors[] = 'Bio must not exceed 500 characters';
}
```

### Numeric Validation

Use `validate_numeric()` to check if value is numeric:

```php
function validate_numeric(mixed $value): bool
```

**Example:**

```php
$quantity = $_POST['quantity'];

if (!validate_numeric($quantity)) {
    $errors[] = 'Quantity must be a number';
}

validate_numeric('123');       // true
validate_numeric('45.67');     // true
validate_numeric('abc');       // false
```

### Integer Validation

Use `validate_integer()` to check if value is a whole number:

```php
function validate_integer(mixed $value): bool
```

**Example:**

```php
$count = $_POST['count'];

if (!validate_integer($count)) {
    $errors[] = 'Count must be a whole number';
}

validate_integer('42');        // true
validate_integer('42.5');      // false
validate_integer('-10');       // true
```

### Alphabetic Validation

Use `validate_alphabetic()` to check if only letters are present:

```php
function validate_alphabetic(mixed $value): bool
```

**Example:**

```php
$first_name = $_POST['first_name'];

if (!validate_alphabetic($first_name)) {
    $errors[] = 'First name must contain only letters';
}

validate_alphabetic('John');       // true
validate_alphabetic('John123');    // false
validate_alphabetic('Jean-Pierre');// false (dash)
```

### Alphanumeric Validation

Use `validate_alpha_numeric()` to allow letters and numbers:

```php
function validate_alpha_numeric(mixed $value): bool
```

**Example:**

```php
$username = $_POST['username'];

if (!validate_alpha_numeric($username)) {
    $errors[] = 'Username must contain only letters and numbers';
}

validate_alpha_numeric('User123');      // true
validate_alpha_numeric('user_name');    // false (underscore)
validate_alpha_numeric('user@name');    // false (special char)
```

### URL Validation

Use `validate_url()` to check URL format:

```php
function validate_url(mixed $value): bool
```

**Example:**

```php
$website = $_POST['website'];

if (!validate_url($website)) {
    $errors[] = 'Invalid URL format';
}

validate_url('https://example.com');     // true
validate_url('http://sub.example.co.uk');// true
validate_url('example.com');             // false (no protocol)
validate_url('not a url');               // false
```

### IP Address Validation

Use `validate_ip()` to check IP address format:

```php
function validate_ip(mixed $value): bool
```

**Example:**

```php
$ip = $_SERVER['REMOTE_ADDR'];

if (!validate_ip($ip)) {
    $errors[] = 'Invalid IP address';
}

validate_ip('192.168.1.1');      // true
validate_ip('2001:db8::1');      // true (IPv6)
validate_ip('999.999.999.999');  // false
validate_ip('not.an.ip');        // false
```

### Date Validation

Use `validate_date()` to check date format:

```php
function validate_date(mixed $value, string $format = 'Y-m-d'): bool
```

**Example:**

```php
$birthdate = $_POST['birthdate'];

if (!validate_date($birthdate, 'Y-m-d')) {
    $errors[] = 'Date must be in YYYY-MM-DD format';
}

// With custom format
if (!validate_date($birthdate, 'm/d/Y')) {
    $errors[] = 'Date must be in MM/DD/YYYY format';
}

validate_date('2025-01-15', 'Y-m-d');    // true
validate_date('01/15/2025', 'm/d/Y');    // true
validate_date('2025-13-01', 'Y-m-d');    // false (invalid month)
validate_date('not-a-date', 'Y-m-d');    // false
```

### In Array Validation

Use `value_in_array()` to check if value is in allowed list:

```php
function value_in_array(mixed $value, array $array): bool
```

**Example:**

```php
$role = $_POST['role'];
$allowed_roles = ['admin', 'moderator', 'user'];

if (!value_in_array($role, $allowed_roles)) {
    $errors[] = 'Invalid role selected';
}

value_in_array('admin', ['admin', 'user']);       // true
value_in_array('guest', ['admin', 'user']);       // false
```

## Practical Validation Examples

### User Registration Form

```php
route('/register', function() {
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF
        if (!validate_csrf($_POST['csrf_token'] ?? '')) {
            $errors[] = 'Invalid request';
        }

        // Sanitize and validate email
        $email = sanitize_email($_POST['email'] ?? '');
        if (!validate_email($email)) {
            $errors[] = 'Invalid email address';
        } elseif (db_select_one('users', ['email' => $email])) {
            $errors[] = 'Email already registered';
        }

        // Validate password
        $password = $_POST['password'] ?? '';
        if (!validate_min_length($password, 8)) {
            $errors[] = 'Password must be at least 8 characters';
        } elseif ($_POST['password'] !== $_POST['confirm_password']) {
            $errors[] = 'Passwords do not match';
        }

        // Create user if valid
        if (empty($errors)) {
            $userId = db_insert('users', [
                'email' => $email,
                'password' => auth_hash_password($password),
            ]);

            if ($userId) {
                auth_login($userId);
                header('Location: /dashboard');
                exit;
            }
        }
    }

    return view('register.php', ['errors' => $errors]);
});
```

### Product Form with Multiple Fields

```php
route('/products/create', function() {
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Product name
        $name = sanitize_string($_POST['name'] ?? '');
        if (!validate_required($name)) {
            $errors[] = 'Product name is required';
        } elseif (!validate_max_length($name, 255)) {
            $errors[] = 'Product name must not exceed 255 characters';
        }

        // Price
        $price = sanitize_float($_POST['price'] ?? '');
        if (!validate_numeric($price) || $price <= 0) {
            $errors[] = 'Price must be a positive number';
        }

        // Stock quantity
        $stock = sanitize_int($_POST['stock'] ?? '');
        if (!validate_integer($stock) || $stock < 0) {
            $errors[] = 'Stock must be a non-negative integer';
        }

        // Category
        $category = $_POST['category'] ?? '';
        $valid_categories = ['electronics', 'clothing', 'books', 'other'];
        if (!value_in_array($category, $valid_categories)) {
            $errors[] = 'Invalid category selected';
        }

        // Supplier URL (optional)
        if (!empty($_POST['supplier_url'])) {
            $supplier_url = sanitize_url($_POST['supplier_url']);
            if (!validate_url($supplier_url)) {
                $errors[] = 'Invalid supplier URL';
            }
        } else {
            $supplier_url = null;
        }

        // Create product if valid
        if (empty($errors)) {
            $productId = db_insert('products', [
                'name' => $name,
                'price' => $price,
                'stock' => $stock,
                'category' => $category,
                'supplier_url' => $supplier_url,
                'user_id' => auth_user(),
            ]);

            if ($productId) {
                header('Location: /products/' . $productId);
                exit;
            }
        }
    }

    return view('product_form.php', ['errors' => $errors]);
});
```

### Blog Post Form

```php
route('/blog/create', function() {
    if (!auth_user()) {
        http_response_code(403);
        return 'Unauthorized';
    }

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF
        if (!validate_csrf($_POST['csrf_token'] ?? '')) {
            $errors[] = 'Invalid request';
        }

        // Title
        $title = sanitize_string($_POST['title'] ?? '');
        if (!validate_required($title)) {
            $errors[] = 'Title is required';
        } elseif (!validate_min_length($title, 5) || !validate_max_length($title, 255)) {
            $errors[] = 'Title must be 5-255 characters';
        }

        // Body
        $body = sanitize_string($_POST['body'] ?? '');
        if (!validate_required($body)) {
            $errors[] = 'Body is required';
        } elseif (!validate_min_length($body, 50)) {
            $errors[] = 'Body must be at least 50 characters';
        }

        // Tags (comma-separated, optional)
        $tags = sanitize_string($_POST['tags'] ?? '');
        if (!empty($tags) && !validate_max_length($tags, 200)) {
            $errors[] = 'Tags must not exceed 200 characters';
        }

        // Publish date (optional)
        $published_at = $_POST['published_at'] ?? null;
        if (!empty($published_at)) {
            if (!validate_date($published_at, 'Y-m-d H:i')) {
                $errors[] = 'Invalid date format';
            }
        }

        // Create post if valid
        if (empty($errors)) {
            $postId = db_insert('posts', [
                'user_id' => auth_user(),
                'title' => $title,
                'body' => $body,
                'tags' => !empty($tags) ? $tags : null,
                'published_at' => $published_at,
            ]);

            if ($postId) {
                header('Location: /blog/' . $postId);
                exit;
            }
        }
    }

    return view('blog_form.php', ['errors' => $errors]);
});
```

### API Request Validation

```php
route('/api/users/{id}', function($id) {
    // Validate ID
    $id = sanitize_int($id);
    if (!validate_integer($id) || $id <= 0) {
        http_response_code(400);
        header('Content-Type: application/json');
        return json_encode(['error' => 'Invalid user ID']);
    }

    $user = db_select_one('users', ['id' => $id]);
    if (!$user) {
        http_response_code(404);
        header('Content-Type: application/json');
        return json_encode(['error' => 'User not found']);
    }

    // Update user
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        if ($user['id'] !== auth_user()) {
            http_response_code(403);
            header('Content-Type: application/json');
            return json_encode(['error' => 'Unauthorized']);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $errors = [];

        // Validate email if provided
        if (isset($data['email'])) {
            $email = sanitize_email($data['email']);
            if (!validate_email($email)) {
                $errors[] = 'Invalid email';
            }
        }

        // Validate name if provided
        if (isset($data['name'])) {
            $name = sanitize_string($data['name']);
            if (!validate_required($name)) {
                $errors[] = 'Name is required';
            }
        }

        if (!empty($errors)) {
            http_response_code(400);
            header('Content-Type: application/json');
            return json_encode(['errors' => $errors]);
        }

        // Update database
        db_update('users', [
            'email' => $email ?? $user['email'],
            'name' => $name ?? $user['name'],
        ], ['id' => $id]);

        header('Content-Type: application/json');
        return json_encode(['success' => true]);
    }

    header('Content-Type: application/json');
    return json_encode($user);
});
```

## Form Template with Validation

```php
<!-- views/contact_form.php -->
<form method="POST" action="/contact">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Please fix the following errors:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <label for="name">Name (required)</label>
        <input type="text" id="name" name="name"
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
               required placeholder="Your name">
    </div>

    <div class="form-group">
        <label for="email">Email (required)</label>
        <input type="email" id="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               required placeholder="your@email.com">
    </div>

    <div class="form-group">
        <label for="phone">Phone (optional)</label>
        <input type="tel" id="phone" name="phone"
               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
               placeholder="Your phone number">
    </div>

    <div class="form-group">
        <label for="message">Message (required, 10-500 characters)</label>
        <textarea id="message" name="message"
                  required minlength="10" maxlength="500"
                  placeholder="Your message here..."></textarea>
    </div>

    <?= csrf_field() ?>

    <button type="submit">Send Message</button>
</form>
```

## Best Practices

### 1. Sanitize First, Then Validate

```php
// ✓ Good - sanitize input, then validate
$email = sanitize_email($_POST['email']);
if (!validate_email($email)) {
    $errors[] = 'Invalid email';
}

// ✗ Bad - validate raw input
if (!validate_email($_POST['email'])) {
    $errors[] = 'Invalid email';
}
```

### 2. Validate on Server Side

```php
// ✓ Good - server-side validation (required)
if (!validate_email($email)) {
    $errors[] = 'Invalid email';
}

// Note: HTML5 validation is nice for UX but not secure
// Always validate on the server
```

### 3. Provide Clear Error Messages

```php
// ✓ Good - specific error
$errors[] = 'Password must be at least 8 characters';

// ✗ Bad - vague error
$errors[] = 'Invalid input';
```

### 4. Use Type-Specific Sanitization

```php
// ✓ Good
$email = sanitize_email($_POST['email']);
$count = sanitize_int($_POST['count']);
$price = sanitize_float($_POST['price']);

// ✗ Bad - generic sanitization
$email = sanitize_string($_POST['email']);
$count = sanitize_string($_POST['count']);
```

### 5. Check Multiple Constraints

```php
// ✓ Good - validate multiple rules
if (!validate_required($password)) {
    $errors[] = 'Password is required';
} elseif (!validate_min_length($password, 8)) {
    $errors[] = 'Password must be at least 8 characters';
} elseif (!validate_max_length($password, 128)) {
    $errors[] = 'Password is too long';
}

// ✗ Bad - only one check
if (!validate_email($email)) {
    $errors[] = 'Invalid email';
}
```

### 6. Validate Related Fields Together

```php
// ✓ Good - validate password match after basic checks
if (empty($errors)) {
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $errors[] = 'Passwords do not match';
    }
}

// ✗ Bad - check field before it's been validated
if ($_POST['password'] !== $_POST['confirm_password']) {
    $errors[] = 'Passwords do not match';
}
```

### 7. Whitelist, Don't Blacklist

```php
// ✓ Good - whitelist allowed values
$roles = ['admin', 'moderator', 'user'];
if (!value_in_array($_POST['role'], $roles)) {
    $errors[] = 'Invalid role';
}

// ✗ Bad - blacklist forbidden values
if ($_POST['role'] === 'superadmin') {
    $errors[] = 'Invalid role';
}
```

## See Also

- [Authentication](authentication.md) - Validate login credentials
- [Database](database.md) - Store validated data securely
- [Views & Templates](views.md) - Display validation errors in forms
- [Routing](routing.md) - Create validated endpoints
