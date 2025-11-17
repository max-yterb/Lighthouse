# Frontend

Lighthouse provides a modern frontend stack with HTMX for dynamic interactions and Pico.css for styling. This combination gives you a lightweight, accessible, and responsive user interface without heavy JavaScript frameworks.

## Overview

### Technology Stack

- **HTMX** - Lightweight library for accessing AJAX, WebSockets, and Server-Sent Events directly in HTML
- **Pico.css** - Minimal CSS framework using the YohnTheme fork for better customization
- **Lighthouse Brand Colors** - Sea-inspired color palette for consistent theming
- **No Build Tools** - CSS and JavaScript work out of the box, no compilation needed

### Key Benefits

- ⚡ **Fast** - No heavy frameworks, minimal JavaScript
- 📦 **Small** - Total bundle under 100KB (HTMX + Pico.css)
- 🎨 **Themeable** - Extensive CSS variables for customization
- ♿ **Accessible** - Built-in semantic HTML and WCAG compliance
- 🚀 **Interactive** - Dynamic updates without page reloads

## HTMX Basics

HTMX allows you to access AJAX, WebSockets, and Server-Sent Events directly in HTML using simple attributes.

### What is HTMX?

HTMX lets you use any HTTP method in HTML (not just GET/POST) and dramatically simplifies making AJAX requests. Instead of writing JavaScript, you add attributes to HTML elements.

### Core HTMX Attributes

#### `hx-get` - Fetch and Replace

Fetch content from a URL and insert it into the DOM:

```html
<!-- Click to load user info -->
<button hx-get="/api/user/profile" hx-target="#profile">
    Load Profile
</button>

<div id="profile"></div>
```

#### `hx-post` - Send Data

Send form data to a URL:

```html
<form hx-post="/contact" hx-target="#result">
    <input type="text" name="name" required>
    <textarea name="message" required></textarea>
    <button type="submit">Send</button>
</form>

<div id="result"></div>
```

#### `hx-target` - Where to Insert

Specify where to put the response:

```html
<!-- Insert into this element -->
<button hx-get="/partial" hx-target="#output">Load</button>
<div id="output"></div>

<!-- Replace the entire button -->
<button hx-post="/action" hx-target="this">Update</button>

<!-- Replace previous sibling -->
<button hx-get="/content" hx-target="previous">Prev</button>

<!-- Insert into parent -->
<button hx-get="/sibling" hx-target="closest div">Parent</button>
```

#### `hx-swap` - How to Insert

Control how content is inserted (default: `innerHTML`):

```html
<!-- Replace entire element -->
<div hx-get="/new" hx-swap="outerHTML">Old content</div>

<!-- Insert before element -->
<div hx-get="/before" hx-swap="beforebegin">Content</div>

<!-- Insert after element -->
<div hx-get="/after" hx-swap="afterend">Content</div>

<!-- Swap with transition duration -->
<div hx-get="/fade" hx-swap="innerHTML swap:1s">Content</div>
```

#### `hx-trigger` - When to Request

Specify what triggers the request:

```html
<!-- Trigger on input (not just button click) -->
<input type="text" name="search"
       hx-post="/search"
       hx-trigger="input changed delay:500ms"
       hx-target="#results">

<!-- Trigger on change -->
<select name="category" hx-post="/filter" hx-trigger="change">
    <option>All</option>
    <option>Featured</option>
</select>

<!-- Trigger on multiple events -->
<div hx-get="/data" hx-trigger="click, keyup">Multiple</div>

<!-- Poll server every 5 seconds -->
<div hx-get="/status" hx-trigger="every 5s">Status</div>
```

#### `hx-confirm` - Confirmation Dialog

Show a confirmation before making the request:

```html
<!-- Confirm before deleting -->
<button hx-delete="/posts/123"
        hx-confirm="Are you sure you want to delete this post?"
        hx-target="closest .post">
    Delete
</button>
```

### Form Submission with HTMX

Simplify forms with HTMX:

```html
<!-- Traditional form with HTMX -->
<form hx-post="/contact" hx-target="#result">
    <label for="name">Name</label>
    <input type="text" id="name" name="name" required>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" required>

    <label for="message">Message</label>
    <textarea id="message" name="message" required></textarea>

    <!-- CSRF protection -->
    <?= csrf_field() ?>

    <button type="submit">Send Message</button>
</form>

<div id="result"></div>
```

### Response Handling

HTMX automatically handles different response types:

```html
<!-- Return HTML fragment -->
<button hx-post="/api/item/create" hx-target="#list" hx-swap="beforeend">
    Add Item
</button>

<!-- Handle errors gracefully -->
<button hx-post="/action"
        hx-target="#error"
        hx-on::response-error="alert('Something went wrong')">
    Action
</button>

<!-- Clear input after success -->
<form hx-post="/subscribe"
      hx-on::after-request="if(event.detail.xhr.status === 200) this.reset()">
    <input type="email" name="email" required>
    <button>Subscribe</button>
</form>
```

### HTMX Events

Listen to HTMX lifecycle events:

```html
<!-- Before request -->
<div hx-get="/data"
     hx-on::before-request="console.log('Loading...')">
    Content
</div>

<!-- After request -->
<div hx-post="/save"
     hx-on::after-request="console.log('Saved')">
    Save
</div>

<!-- Swap complete -->
<div hx-get="/page"
     hx-on::after-swap="console.log('Swapped')">
    Page
</div>
```

### Practical HTMX Examples

#### Live Search

```html
<!-- views/search.php -->
<input type="text" name="query"
       placeholder="Search users..."
       hx-post="/search/users"
       hx-trigger="input changed delay:300ms"
       hx-target="#results"
       autocomplete="off">

<div id="results"></div>
```

Route to handle search:

```php
// app_routes.php
route('/search/users', function() {
    $query = sanitize_string($_POST['query'] ?? '');

    if (strlen($query) < 2) {
        return '<div class="alert">Type at least 2 characters</div>';
    }

    $results = db_select('users', []);
    $filtered = array_filter($results, function($user) use ($query) {
        return stripos($user['email'], $query) !== false;
    });

    if (empty($filtered)) {
        return '<div class="alert">No users found</div>';
    }

    return view('search_results.php', ['results' => $filtered], false);
});
```

#### Inline Editing

```html
<!-- views/user_row.php -->
<tr id="user-<?= $user['id'] ?>">
    <td><?= htmlspecialchars($user['name']) ?></td>
    <td>
        <button hx-get="/users/<?= $user['id'] ?>/edit-form"
                hx-target="this"
                hx-swap="outerHTML">
            Edit
        </button>
    </td>
</tr>
```

Edit form endpoint:

```php
// app_routes.php
route('/users/{id}/edit-form', function($id) {
    $user = db_select_one('users', ['id' => $id]);
    return view('user_edit_form.php', ['user' => $user], false);
});
```

#### Delete with Confirmation

```html
<button hx-delete="/posts/<?= $post['id'] ?>"
        hx-confirm="Delete this post permanently?"
        hx-target="closest article"
        hx-swap="outerHTML swap:1s"
        class="btn-danger">
    Delete Post
</button>
```

Delete handler:

```php
// app_routes.php
route('/posts/{id}', function($id) {
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $post = db_select_one('posts', ['id' => $id]);

        if ($post && $post['user_id'] === auth_user()) {
            db_delete('posts', ['id' => $id]);
            return '';  // Return empty - HTMX will remove the element
        }

        http_response_code(403);
        return 'Unauthorized';
    }
});
```

## Pico.css Styling

Lighthouse uses the YohnTheme fork of Pico.css, which provides better customization out of the box.

### What is Pico.css?

Pico.css is a minimal CSS framework designed for semantic HTML with no class names. It provides:

- 🎯 **Classless** - Style based on HTML elements
- 🎨 **Customizable** - Extensive CSS variables
- ♿ **Accessible** - Built-in semantic HTML support
- 📱 **Responsive** - Mobile-first design
- 🌓 **Dark Mode** - Automatic dark mode support

### Available Themes

Lighthouse includes 22 color themes from the Pico.css community:

```
pico.amber.min.css       - Warm amber colors
pico.azure.min.css       - Cool blue tones
pico.blue.min.css        - Classic blue
pico.cyan.min.css        - Cyan accents
pico.fuchsia.min.css     - Vibrant magenta
pico.green.min.css       - Natural green
pico.grey.min.css        - Neutral grey
pico.indigo.min.css      - Deep indigo
pico.jade.min.css        - Jade green
pico.lime.min.css        - Lime green
pico.min.css             - Default theme
pico.orange.min.css      - Warm orange
pico.pink.min.css        - Soft pink
pico.pumpkin.min.css     - Harvest orange
pico.purple.min.css      - Royal purple
pico.red.min.css         - Bright red
pico.sand.min.css        - Sandy beige
pico.slate.min.css       - Slate grey
pico.violet.min.css      - Violet purple
pico.yellow.min.css      - Bright yellow
pico.zinc.min.css        - Dark zinc
```

### Switching Themes

Themes are configured in `.env`:

```bash
# .env
THEME=pico.blue.min.css
```

The layout automatically loads the configured theme:

```php
<!-- views/_layout.php -->
<link rel="stylesheet" href="/css/<?= htmlspecialchars(config('THEME')) ?>">
```

Switch themes at runtime:

```html
<form action="/theme" method="POST">
    <select name="theme" onchange="this.form.submit()">
        <option value="pico.blue.min.css">Blue</option>
        <option value="pico.green.min.css">Green</option>
        <option value="pico.purple.min.css">Purple</option>
    </select>
</form>
```

### Customizing with CSS Variables

Override Pico.css variables in `public/css/style.css`:

```css
:root {
    /* Brand colors */
    --lighthouse-beacon-red: #E63946;
    --lighthouse-sea-slate: #1D3557;
    --lighthouse-fog-white: #F1FAEE;
    --lighthouse-sky-mist: #A8DADC;
    --lighthouse-signal-blue: #457B9D;

    /* Override Pico colors */
    --pico-primary-500: var(--lighthouse-signal-blue);
    --pico-background-color: var(--lighthouse-fog-white);
    --pico-color: var(--lighthouse-sea-slate);
    --pico-form-element-border-color: var(--lighthouse-sky-mist);
    --pico-border-color: var(--lighthouse-sky-mist);
}
```

### Common CSS Variables

```css
:root {
    /* Colors */
    --pico-primary-500: #0066cc;      /* Primary color */
    --pico-background-color: #ffffff; /* Page background */
    --pico-card-background-color: #f8f9fa; /* Card background */
    --pico-color: #333333;            /* Text color */

    /* Typography */
    --pico-font-size: 16px;
    --pico-line-height: 1.5;
    --pico-font-family: system-ui, -apple-system, sans-serif;

    /* Spacing */
    --pico-spacing: 1rem;
    --pico-form-spacing: 0.5rem;

    /* Forms */
    --pico-form-element-border-color: #ccc;
    --pico-form-element-focus-color: #0066cc;
    --pico-input-background-color: #fff;

    /* Buttons */
    --pico-primary-background: #0066cc;
    --pico-primary-border: #0066cc;
    --pico-primary-color: #fff;

    /* Transitions */
    --pico-transition: 0.2s;
}
```

### Using Semantic HTML

Pico.css styles semantic HTML elements directly:

```html
<!-- Headings -->
<h1>Main Title</h1>
<h2>Section Title</h2>
<h3>Subsection</h3>

<!-- Text elements -->
<p>Paragraph text</p>
<strong>Bold text</strong>
<em>Emphasized text</em>

<!-- Lists -->
<ul>
    <li>List item</li>
    <li>Another item</li>
</ul>

<!-- Forms -->
<form>
    <label for="name">Name</label>
    <input type="text" id="name" name="name" required>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" required>

    <button type="submit">Submit</button>
</form>

<!-- Tables -->
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>John</td>
            <td>john@example.com</td>
        </tr>
    </tbody>
</table>
```

## Lighthouse Brand Colors

The framework comes with a beautiful sea-inspired color palette:

```css
--lighthouse-beacon-red: #E63946;   /* Accent color for CTAs */
--lighthouse-sea-slate: #1D3557;    /* Primary text/background */
--lighthouse-fog-white: #F1FAEE;    /* Light background */
--lighthouse-sky-mist: #A8DADC;     /* Borders and accents */
--lighthouse-signal-blue: #457B9D;  /* Buttons and links */
```

These colors are already integrated into `style.css` and override Pico.css defaults.

## Form Handling with HTMX

### Basic Form

```html
<form hx-post="/submit" hx-target="#result">
    <label for="email">Email</label>
    <input type="email" id="email" name="email" required>

    <label for="message">Message</label>
    <textarea id="message" name="message" required></textarea>

    <?= csrf_field() ?>

    <button>Send</button>
</form>

<div id="result"></div>
```

### Form with Validation

```html
<form hx-post="/register"
      hx-target="#result"
      hx-on::response-error="alert('Registration failed')">

    <label for="email">Email</label>
    <input type="email" id="email" name="email" required
           hx-post="/check-email"
           hx-trigger="blur"
           hx-target="#email-error">
    <div id="email-error"></div>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required
           minlength="8">

    <?= csrf_field() ?>

    <button>Register</button>
</form>

<div id="result"></div>
```

## HTMX Partials

Return HTML fragments (no layout) for HTMX responses:

```php
// app_routes.php
route('/notifications', function() {
    $notifications = db_select('notifications', ['user_id' => auth_user()]);

    // Return without layout for HTMX
    return view('notification_list.php',
                ['notifications' => $notifications],
                false);  // No layout
});
```

In the view, you can check if it's an HTMX request:

```php
<?php
// views/notification_list.php

foreach ($notifications as $notif):
?>
    <div class="notification" hx-target="this" hx-swap="outerHTML">
        <p><?= htmlspecialchars($notif['message']) ?></p>
        <button hx-delete="/notifications/<?= $notif['id'] ?>">
            Dismiss
        </button>
    </div>
<?php endforeach; ?>
```

## Best Practices

### HTMX Best Practices

1. **Keep responses small** - Return only the HTML you need to update
2. **Use proper HTTP methods** - DELETE for deletion, PUT for updates, POST for creation
3. **Include CSRF tokens** - Always validate CSRF on state-changing requests
4. **Provide user feedback** - Show loading states and confirmations
5. **Handle errors gracefully** - Return appropriate HTTP status codes

### CSS Best Practices

1. **Override variables, not classes** - Use CSS variables instead of !important
2. **Keep style.css minimal** - Extend Pico.css, don't replace it
3. **Use semantic HTML** - Let Pico.css handle the styling
4. **Test dark mode** - Check how your colors look in dark mode
5. **Mobile first** - Pico.css is responsive out of the box

## Troubleshooting

### HTMX Requests Not Working

```html
<!-- Make sure HTMX is loaded -->
<script src="/js/htmx.min.js"></script>

<!-- Check browser console for errors -->
<!-- Verify the endpoint returns valid HTML -->
<!-- Ensure CSRF token is included in POST requests -->
```

### Styling Issues

```css
/* If Pico.css variables aren't overriding */
:root {
    /* Use !important if needed */
    --pico-primary: #color !important;
}

/* Check for specificity issues */
/* Verify no conflicting CSS rules */
```

## Resources

- **HTMX Documentation** - https://htmx.org/docs/
- **Pico.css Documentation** - https://picocss.com/docs/
- **YohnTheme Fork** - https://yohn.github.io/PicoCSS/
- **Lighthouse Brand Colors** - See docs/index.md

## See Also

- [Views & Templates](views.md) - Create HTML templates
- [Routing](routing.md) - Create HTMX endpoints
- [Validation](validation.md) - Validate form data
- [Authentication](authentication.md) - Protect routes
