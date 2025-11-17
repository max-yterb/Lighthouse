# Routing in Lighthouse

Lighthouse provides a simple yet powerful routing system that makes it easy to define URL patterns and handle HTTP requests.

## 📋 Table of Contents

- [Basic Routing](#basic-routing)
- [Route Parameters](#route-parameters)
- [HTTP Methods](#http-methods)
- [Route Groups](#route-groups)
- [Middleware](#middleware)
- [Route Caching](#route-caching)

## 🛣️ Basic Routing

Routes are defined using the `route()` function in two dedicated files:

- **`app_routes.php`** - Your application routes (home, about, products, etc.)
- **`auth_routes.php`** - Authentication routes (login, register, logout, dashboard, etc.)

Both files are included from `public/index.php`:

```php
<?php
// public/index.php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../app_routes.php';    // App routes
require_once __DIR__ . '/../auth_routes.php';   // Auth routes
dispatch($route, $_SERVER['REQUEST_METHOD']);
```

### Simple Routes in app_routes.php

```php
<?php
// app_routes.php

// Simple route
route('/', function() {
    return view('home.php');
});

// Route with metadata
route('/about', function() {
    return view('about.php', [
        'title' => 'About Us',
        'description' => 'Learn more about our company'
    ]);
});

// Route returning inline content
route('/status', function() {
    return '<p>Server is running</p>';
});
```

### Authentication Routes in auth_routes.php

```php
<?php
// auth_routes.php

route('/login', function() {
    return view('login.php');
});

route('/register', function() {
    return view('register.php');
});

route('/logout', function() {
    auth_logout();
    header('Location: /login');
    exit;
});

route('/dashboard', function() {
    return view('dashboard.php');
});
```

## 🎯 Route Parameters

### Single Parameters

```php
// User profile route
route('/user/{id}', function($id) {
    $user = db_select_one('users', ['id' => $id]);
    if (!$user) {
        http_response_code(404);
        return view('404.php');
    }
    return view('user.php', ['user' => $user]);
});

// Blog post route
route('/blog/{slug}', function($slug) {
    $post = db_select_one('posts', ['slug' => $slug]);
    return view('blog/post.php', ['post' => $post]);
});
```

### Multiple Parameters

```php
// Category and product route
route('/category/{category}/product/{id}', function($category, $id) {
    $product = db_select_one('products', [
        'id' => $id,
        'category' => $category
    ]);
    return view('product.php', ['product' => $product]);
});

// Date-based archive
route('/archive/{year}/{month}', function($year, $month) {
    $posts = db_select('posts', [
        'created_at' => "LIKE '$year-$month%'"
    ]);
    return view('archive.php', ['posts' => $posts, 'year' => $year, 'month' => $month]);
});
```

### Optional Parameters

```php
// Optional page parameter
route('/blog/{page?}', function($page = 1) {
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $posts = db_select('posts', [], 'created_at DESC', $limit, $offset);
    return view('blog.php', ['posts' => $posts, 'page' => $page]);
});
```

## 🌐 HTTP Methods & Form Handling

Lighthouse supports two approaches for handling forms and HTTP methods:

### 🎯 **Approach 1: Logic in Views (Lighthouse Way - Recommended)**

This is the **preferred Lighthouse approach** - keep your routes simple and handle form logic directly in the view files, following traditional PHP patterns.

#### Simple Route Definition

```php
// app_routes.php - Keep it simple!
route('/home', function() {
    return view('home.php');
});

route('/about', function() {
    return view('about.php');
});

route('/contact', function() {
    return view('contact.php');
});

// auth_routes.php
route('/login', function() {
    return view('login.php');
});

route('/register', function() {
    return view('register.php');
});
```

#### View with Embedded Logic

```php
<?php
// views/login.php - Handle logic directly in the view

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

<!-- HTML form here -->
<div class="lighthouse-auth-container">
    <div class="lighthouse-card">
        <h1>Welcome Back</h1>

        <?php if (!empty($errors)): ?>
            <div class="lighthouse-alert error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="/login">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <?= csrf_field() ?>
            <button type="submit">Sign In</button>
        </form>
    </div>
</div>
```

**Benefits of this approach:**
- ✅ **Traditional PHP style** - familiar to PHP developers
- ✅ **Self-contained** - logic and presentation in one place
- ✅ **Simple routing** - routes stay clean and minimal
- ✅ **Easy debugging** - everything related to a page is in one file
- ✅ **Fast development** - no need to jump between route and view files

### 🔄 **Approach 2: Logic in Routes (Alternative)**

This approach handles all logic in the route definition before passing data to views.

#### Route with Embedded Logic

```php
route('/contact', function() {
    $errors = [];
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle form submission
        $name = sanitize_string($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $message = sanitize_string($_POST['message']);

        // Validate
        if (!validate_required($name)) $errors[] = 'Name is required';
        if (!validate_email($email)) $errors[] = 'Valid email is required';
        if (!validate_required($message)) $errors[] = 'Message is required';

        if (empty($errors)) {
            // Save to database
            db_insert('contacts', [
                'name' => $name,
                'email' => $email,
                'message' => $message
            ]);

            $success = 'Message sent successfully!';
        }
    }

    return view('contact.php', [
        'errors' => $errors,
        'success' => $success
    ]);
});
```

#### Simple View (Logic-free)

```php
<?php
// views/contact.php - Pure presentation

/** @var array<string> $errors */
/** @var string $success */
?>

<div class="lighthouse-auth-container">
    <div class="lighthouse-card">
        <h1>Contact Us</h1>

        <?php if (!empty($errors)): ?>
            <div class="lighthouse-alert error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="lighthouse-alert success">
                <p><?= htmlspecialchars($success) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="/contact">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="message">Message</label>
            <textarea id="message" name="message" required></textarea>

            <?= csrf_field() ?>
            <button type="submit">Send Message</button>
        </form>
    </div>
</div>
```

**Benefits of this approach:**
- ✅ **Separation of concerns** - logic separate from presentation
- ✅ **Reusable views** - views can be used with different data sources
- ✅ **Testable logic** - easier to unit test route logic
- ✅ **MVC pattern** - follows traditional MVC architecture

### 📊 **When to Use Each Approach**

| Use Case | Recommended Approach | Reason |
|----------|---------------------|---------|
| **Simple forms** (login, register, contact) | **Logic in Views** | Faster development, self-contained |
| **Complex business logic** | **Logic in Routes** | Better separation, easier testing |
| **API endpoints** | **Logic in Routes** | No HTML rendering needed |
| **HTMX partials** | **Logic in Views** | Simple, direct response |
| **Admin panels** | **Logic in Views** | Rapid development |
| **Multi-step forms** | **Logic in Routes** | Better state management |

### 🎯 **Lighthouse Philosophy**

Lighthouse embraces **pragmatic PHP development**:

- **Start simple** - Use logic in views for rapid development
- **Refactor when needed** - Move to route-based logic as complexity grows
- **Choose what fits** - Both approaches are valid and supported
- **Stay productive** - Don't over-engineer simple forms

### GET Routes (Default)

Both approaches work the same for simple GET routes:

```php
route('/products', function() {
    $products = db_select('products');
    return view('products.php', ['products' => $products]);
});
```

### API Routes with Different Methods

```php
// RESTful API routes
route('/api/users', function() {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $users = db_select('users');
            header('Content-Type: application/json');
            return json_encode($users);

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = db_insert('users', [
                'name' => $data['name'],
                'email' => $data['email']
            ]);
            header('Content-Type: application/json');
            return json_encode(['id' => $userId]);

        case 'DELETE':
            // Handle deletion
            break;

        default:
            http_response_code(405);
            return 'Method Not Allowed';
    }
});
```

## 🔒 Authentication Routes

```php
// Protected route
route('/dashboard', function() {
    if (!auth_user()) {
        header('Location: /login');
        exit;
    }

    $user = db_select_one('users', ['id' => auth_user()]);
    return view('dashboard.php', ['user' => $user], '_dashboard.php');
});

// Admin-only route
route('/admin', function() {
    $user_id = auth_user();
    if (!$user_id) {
        header('Location: /login');
        exit;
    }

    $user = db_select_one('users', ['id' => $user_id]);
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        return view('403.php');
    }

    return view('admin.php');
});
```

## 📁 Route Organization

Lighthouse uses a simple two-file routing structure:

### Standard Structure

```
app_routes.php       # Regular application routes
auth_routes.php      # Authentication-related routes
public/index.php     # Entry point that includes both
```

### How It Works

Routes from both files are loaded in `public/index.php`:

```php
<?php
// public/index.php
require_once __DIR__ . '/../bootstrap.php';

// Load all routes
require_once __DIR__ . '/../app_routes.php';
require_once __DIR__ . '/../auth_routes.php';

// Dispatch the matched route
dispatch($route, $_SERVER['REQUEST_METHOD']);
```

### When to Use Each File

**app_routes.php** - Public application features:
- Home page (`/`)
- About, contact, products
- Blog posts, archives
- Search, galleries
- Any customer-facing routes

**auth_routes.php** - User authentication:
- Login (`/login`)
- Register (`/register`)
- Logout (`/logout`)
- Dashboard (`/dashboard`)
- Password reset
- Profile management
- Any auth-protected routes

This separation keeps your routing organized and makes it easy to find related functionality.

## 🎨 Route Helpers

### Redirects

```php
route('/old-page', function() {
    header('Location: /new-page', true, 301);
    exit;
});
```

### Download Routes

```php
route('/download/{file}', function($file) {
    $filepath = __DIR__ . '/downloads/' . basename($file);

    if (!file_exists($filepath)) {
        http_response_code(404);
        return 'File not found';
    }

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
    exit;
});
```

### HTMX Routes

```php
route('/htmx/users', function() {
    $users = db_select('users');

    // Check if it's an HTMX request
    if (isset($_SERVER['HTTP_HX_REQUEST'])) {
        // Return partial HTML
        return view('partials/users-list.php', ['users' => $users]);
    }

    // Return full page
    return view('users.php', ['users' => $users]);
});
```

## 🔍 Route Debugging

### List All Routes

```php
// Add this to a debug route
route('/debug/routes', function() {
    if (!config('APP_DEBUG')) {
        http_response_code(404);
        return 'Not found';
    }

    global $routes;
    echo '<h1>Registered Routes</h1>';
    echo '<ul>';
    foreach ($routes as $route) {
        echo '<li>' . htmlspecialchars($route['pattern']) . '</li>';
    }
    echo '</ul>';
});
```

## 📝 Best Practices

### 1. Keep Routes Simple

```php
// Good - simple and clear
route('/users/{id}', function($id) {
    $user = get_user($id);
    return view('user.php', ['user' => $user]);
});

// Avoid - too much logic in route
route('/complex', function() {
    // 50 lines of business logic...
});
```

### 2. Use Descriptive URLs

```php
// Good
route('/blog/category/{category}', function($category) { ... });
route('/user/{id}/profile', function($id) { ... });

// Avoid
route('/p/{id}', function($id) { ... });
route('/x/{a}/{b}', function($a, $b) { ... });
```

### 3. Validate Parameters

```php
route('/user/{id}', function($id) {
    // Validate parameter
    if (!is_numeric($id) || $id <= 0) {
        http_response_code(400);
        return 'Invalid user ID';
    }

    $user = db_select_one('users', ['id' => $id]);
    // ...
});
```

### 4. Handle Errors Gracefully

```php
route('/api/user/{id}', function($id) {
    try {
        $user = db_select_one('users', ['id' => $id]);
        if (!$user) {
            http_response_code(404);
            return json_encode(['error' => 'User not found']);
        }

        header('Content-Type: application/json');
        return json_encode($user);

    } catch (Exception $e) {
        http_response_code(500);
        return json_encode(['error' => 'Internal server error']);
    }
});
```

## 🚀 Advanced Patterns

### Route Caching

For better performance, you can cache route matching:

```php
// In your bootstrap or config
$route_cache = [];

function cached_route($pattern, $handler) {
    global $route_cache;
    $route_cache[$pattern] = $handler;
    route($pattern, $handler);
}
```

### Dynamic Route Loading

```php
// Load routes based on modules
$modules = ['blog', 'shop', 'forum'];

foreach ($modules as $module) {
    $route_file = "modules/{$module}/routes.php";
    if (file_exists($route_file)) {
        require_once $route_file;
    }
}
```

## 🔗 Related Documentation

- [Views & Templates](views.md) - Learn about rendering views
- [Database](database.md) - Working with data in routes
- [Authentication](authentication.md) - Protecting routes
- [Frontend](frontend.md) - HTMX integration with routes

---

**Next:** Learn about [Views & Templates](views.md) to render beautiful pages for your routes.
