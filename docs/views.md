# Views & Templates

Lighthouse uses simple PHP templates for rendering HTML. Views are procedural PHP files that receive variables and output HTML content.

## Overview

Views in Lighthouse are straightforward PHP files stored in the `views/` directory. Each view:
- Receives variables passed from route handlers
- Uses the layout system for consistent structure
- Accesses global metadata for page titles and descriptions
- Can use helper functions for security and output

## The View Function

The `view()` function renders a template file and returns HTML content:

```php
view(string $file, array $view_vars = [], ?string $useLayout = null): string|false
```

### Parameters

- **`$file`** - Template filename in `views/` directory
- **`$view_vars`** - Associative array of variables to pass to the template
- **`$useLayout`** - Optional layout override (default: `_layout.php`)

### Basic Usage

In your route handler:

```php
route('/posts/{id}', function($id) {
    $post = db_select_one('posts', ['id' => $id]);

    return view('post.php', [
        'post' => $post,
        'title' => $post['title'],
        'description' => $post['excerpt']
    ]);
});
```

In your template (`views/post.php`):

```php
<article>
    <h1><?= htmlspecialchars($post['title']) ?></h1>
    <p><?= htmlspecialchars($post['content']) ?></p>
</article>
```

## Metadata & SEO

Page metadata (title, description, etc.) is managed globally and customizable per view. Metadata is accessible via the `$meta` array in layouts.

### Available Metadata Fields

```php
$meta = [
    'title'       => string,  // Page title
    'description' => string,  // Meta description
    'author'      => string,  // Author name
    'keywords'    => string,  // SEO keywords
    'charset'     => string,  // Character encoding
    'canonical'   => string,  // Canonical URL
];
```

### Setting Metadata in Routes

Pass metadata as variables when calling `view()`:

```php
route('/about', function() {
    return view('about.php', [
        'title' => 'About Us',
        'description' => 'Learn more about our company',
        'keywords' => 'about, company, team'
    ]);
});
```

The metadata variables override the defaults automatically:

```php
// In bootstrap.php, defaults are set
$meta = [
    'title'       => config('DEFAULT_TITLE'),
    'description' => config('DEFAULT_DESC'),
    'author'      => config('DEFAULT_AUTHOR'),
    'keywords'    => config('DEFAULT_KEYWORDS'),
    'charset'     => config('DEFAULT_CHARSET'),
    'canonical'   => config('DEFAULT_CANONICAL'),
];
```

## Layouts

Layouts provide consistent HTML structure across pages. The default layout is `_layout.php`.

### Default Layout Structure

The default `_layout.php` includes:
- HTML5 doctype and head
- Meta tags from `$meta` array
- CSS stylesheets (Pico.css theme + custom styles)
- HTMX script
- Navigation bar with auth links
- Footer with copyright

The layout renders the `$content` variable where your view's HTML is inserted.

### Custom Layouts

Use an alternative layout by passing it as the third parameter:

```php
return view('plain.php', [], 'minimal.php');
```

Create a custom layout (`views/minimal.php`):

```php
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($meta['title']) ?></title>
</head>
<body>
    <?= $content ?>
</body>
</html>
```

### No Layout (HTMX Partials)

For HTMX requests that return fragments, bypass the layout:

```php
route('/api/user-card', function() {
    return view('user_card.php', ['user' => auth_user()], false);
});
```

Or use the `$hxRequest` global to conditionally skip layout:

```php
// In your layout or view
if ($hxRequest) {
    echo $content;
} else {
    // Full page with layout
}
```

## Template Variables

Variables passed to `view()` are automatically extracted and available in the template:

```php
return view('product.php', [
    'product' => $product,
    'quantity' => 5,
    'inStock' => true
]);
```

In `views/product.php`:

```php
<h1><?= htmlspecialchars($product['name']) ?></h1>
<p>Quantity: <?= (int)$quantity ?></p>
<?php if ($inStock): ?>
    <button>Add to Cart</button>
<?php endif; ?>
```

## Global Variables

Several variables are always available in templates:

- **`$meta`** - Metadata array (title, description, etc.)
- **`$content`** - Current page content (used by layouts)
- **`$hxRequest`** - Boolean, true if request came from HTMX
- **`$viewDir`** - Path to views directory

## Security & Output Escaping

Always escape user input to prevent XSS attacks:

### Escaping HTML

```php
<!-- Safe: escapes HTML entities -->
<h1><?= htmlspecialchars($user['name']) ?></h1>
```

### Escaping Attributes

```php
<!-- Safe: escapes for HTML attributes -->
<img src="<?= htmlspecialchars($image, ENT_QUOTES) ?>" alt="photo">
```

### Escaping URLs

```php
<!-- Safe: escapes for URLs -->
<a href="<?= htmlspecialchars($url, ENT_QUOTES) ?>">Link</a>
```

Use the sanitization helpers for cleaner code:

```php
// In your route
$email = sanitize_email($_POST['email']);
$name = sanitize_string($_POST['name']);

return view('profile.php', ['email' => $email, 'name' => $name]);
```

## Practical Examples

### Simple Page Template

```php
<!-- views/blog.php -->
<div class="container">
    <h1><?= htmlspecialchars($title) ?></h1>
    <article>
        <?= $content ?>
    </article>
</div>
```

Route handler:

```php
route('/blog/{slug}', function($slug) {
    $post = db_select_one('posts', ['slug' => $slug]);
    if (!$post) {
        http_response_code(404);
        return view('404.php');
    }

    return view('blog.php', [
        'title' => $post['title'],
        'content' => $post['body'],
        'description' => $post['excerpt']
    ]);
});
```

### Form with CSRF Protection

```php
<!-- views/contact.php -->
<form method="post">
    <?= csrf_field() ?>

    <label for="name">Name</label>
    <input type="text" id="name" name="name" required>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" required>

    <label for="message">Message</label>
    <textarea id="message" name="message" required></textarea>

    <button type="submit">Send</button>
</form>
```

Route handler:

```php
route('/contact', function() {
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF token
        if (!validate_csrf($_POST['csrf_token'] ?? '')) {
            $errors[] = 'Invalid request';
        }
        // Validate input
        if (empty($_POST['email']) || !validate_email($_POST['email'])) {
            $errors[] = 'Valid email required';
        }

        if (empty($errors)) {
            // Process form...
            header('Location: /contact?success=1');
            exit;
        }
    }

    return view('contact.php', ['errors' => $errors]);
});
```

### Conditional Rendering Based on Auth

```php
<!-- views/dashboard.php -->
<h1>Dashboard</h1>

<?php if (auth_user()): ?>
    <p>Welcome, User <?= auth_user() ?></p>
    <a href="/logout">Logout</a>
<?php else: ?>
    <p><a href="/login">Login</a> to access the dashboard</p>
<?php endif; ?>
```

### Lists and Iteration

```php
<!-- views/user_list.php -->
<h1>Users</h1>

<?php if (empty($users)): ?>
    <p>No users found.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= (int)$user['id'] ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
```

### HTMX Partial Response

```php
<!-- views/notification_item.php -->
<!-- This template is for HTMX, no layout wrapper -->
<div class="notification" hx-target="this" hx-swap="outerHTML swap:1s">
    <p><?= htmlspecialchars($message) ?></p>
    <button hx-delete="/notifications/<?= (int)$id ?>" hx-confirm="Delete?">
        Dismiss
    </button>
</div>
```

Route handler:

```php
route('/notifications/{id}', function($id) {
    $notif = db_select_one('notifications', ['id' => $id]);
    return view('notification_item.php', ['notification' => $notif], false);
});
```

## Best Practices

### 1. Always Escape Output

```php
<!-- ✓ Good -->
<h1><?= htmlspecialchars($title) ?></h1>

<!-- ✗ Bad -->
<h1><?= $title ?></h1>
```

### 2. Use Type Hints in View Files

```php
<?php
declare(strict_types=1);

/** @var string $title */
/** @var array<array{id: int, name: string}> $items */
```

### 3. Keep Views Thin

Move business logic to route handlers:

```php
// ✓ Good: Logic in route handler
route('/products', function() {
    $products = db_select('products', [], 'name ASC', 10);
    $featured = array_filter($products, fn($p) => $p['featured']);
    return view('products.php', ['products' => $featured]);
});

// ✗ Bad: Logic in view
// view('products.php') with complex queries
```

### 4. Organize Views by Feature

```
views/
├── _layout.php
├── home.php
├── auth/
│   ├── login.php
│   ├── register.php
│   └── reset_password.php
├── products/
│   ├── list.php
│   └── detail.php
└── errors/
    ├── 404.php
    └── 500.php
```

### 5. Use Consistent Naming

- Use underscores for partial templates: `_header.php`, `_sidebar.php`
- Use descriptive names: `blog_list.php`, `user_profile.php`
- Match feature paths: `auth_routes.php` routes use `views/auth/`

### 6. Separate Presentation from Data

```php
// ✓ Good: Template handles presentation only
return view('price.php', ['amount' => 19.99, 'currency' => 'USD']);

// views/price.php
<p><?= htmlspecialchars($currency) ?> <?= number_format($amount, 2) ?></p>

// ✗ Bad: Formatting in route
$formatted = "$" . number_format($price, 2);
return view('price.php', ['price' => $formatted]);
```

## See Also

- [Routing](routing.md) - Learn how to create routes that render views
- [Database](database.md) - Query data to pass to views
- [Frontend](frontend.md) - Style views with Pico.css and HTMX
- [Validation](validation.md) - Validate user input before rendering
