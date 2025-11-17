# Database

Lighthouse uses SQLite for data persistence. SQLite is a lightweight, file-based database that requires no server setup, making it perfect for applications that don't require a traditional database server.

## Getting Started

### SQLite Basics

SQLite stores data in a single file, by default at `database/database.sqlite`. The database is automatically initialized when you first connect to it.

The database path is defined in `bootstrap.php`:

```php
$dbFile = "$root/database/database.sqlite";
```

### Connecting to the Database

The `db()` function returns a PDO connection to your SQLite database:

```php
$pdo = db();
if ($pdo) {
    // Connection successful
}
```

The connection is configured with exception mode for better error handling.

## Core CRUD Operations

Lighthouse provides simple helper functions for common database operations. All functions use parameterized queries to prevent SQL injection.

### Creating Records (Insert)

Use `db_insert()` to add new records:

```php
function db_insert(string $table, array $data): ?int
```

**Parameters:**
- `$table` - Table name
- `$data` - Associative array of `column => value` pairs

**Returns:** The inserted record's ID or `null` on failure

**Example:**

```php
$userId = db_insert('users', [
    'email' => 'user@example.com',
    'password' => auth_hash_password('password123'),
]);

if ($userId) {
    echo "User created with ID: $userId";
}
```

### Reading Records (Select)

#### Select Multiple Records

Use `db_select()` to fetch multiple records:

```php
function db_select(
    string $table,
    array $where = [],
    string $orderBy = '',
    int $limit = 0
): array
```

**Parameters:**
- `$table` - Table name
- `$where` - Associative array for WHERE conditions (all conditions are AND'd)
- `$orderBy` - ORDER BY clause (e.g., `'created_at DESC'`)
- `$limit` - Maximum records to return (0 = no limit)

**Returns:** Array of associative arrays (empty array if no results)

**Examples:**

```php
// Get all users
$users = db_select('users');

// Get users with specific email
$admins = db_select('users', ['role' => 'admin']);

// Get with ordering and limit
$recent = db_select(
    'posts',
    ['published' => 1],
    'created_at DESC',
    10
);

// Multiple WHERE conditions (AND)
$active_admins = db_select(
    'users',
    ['role' => 'admin', 'active' => 1]
);
```

#### Select Single Record

Use `db_select_one()` to fetch a single record:

```php
function db_select_one(string $table, array $where = []): ?array
```

**Returns:** A single record as an associative array or `null` if not found

**Examples:**

```php
// Get user by email
$user = db_select_one('users', ['email' => 'user@example.com']);

// Get post by ID
$post = db_select_one('posts', ['id' => 42]);

if ($post) {
    echo "Found: " . htmlspecialchars($post['title']);
}
```

### Updating Records

Use `db_update()` to modify existing records:

```php
function db_update(string $table, array $data, array $where): bool
```

**Parameters:**
- `$table` - Table name
- `$data` - Associative array of columns to update
- `$where` - WHERE conditions for matching records

**Returns:** `true` on success, `false` on failure

**Examples:**

```php
// Update a user's email
db_update('users',
    ['email' => 'newemail@example.com'],
    ['id' => 5]
);

// Update multiple records
db_update(
    'posts',
    ['published' => 1],
    ['status' => 'ready']
);

// Increment a counter (note: must be done in PHP)
$post = db_select_one('posts', ['id' => 1]);
db_update('posts',
    ['views' => $post['views'] + 1],
    ['id' => 1]
);
```

### Deleting Records

Use `db_delete()` to remove records:

```php
function db_delete(string $table, array $where): bool
```

**Parameters:**
- `$table` - Table name
- `$where` - WHERE conditions for matching records

**Returns:** `true` on success, `false` on failure

**Examples:**

```php
// Delete a specific user
db_delete('users', ['id' => 5]);

// Delete with multiple conditions
db_delete('sessions', [
    'user_id' => 10,
    'expired' => 1
]);
```

## Migrations

Migrations allow you to version-control your database schema and apply changes consistently across environments.

### Creating Migrations

Use `db_create_migration()` to generate a new migration file:

```php
function db_create_migration(string $name, string $migrationDir): string
```

**Example:**

```php
$filepath = db_create_migration('add_posts_table', __DIR__ . '/../database/migrations');
// Creates: database/migrations/2025_11_17_14_23_45_add_posts_table.sql
```

Migration files are automatically timestamped and stored in the migrations directory.

### Writing Migration SQL

Edit the generated migration file with your SQL:

```sql
-- Migration: add_posts_table
-- Created: 2025-11-17 14:23:45
-- Write your SQL statements below this line

CREATE TABLE posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    title VARCHAR(255) NOT NULL,
    body TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE INDEX posts_user_id ON posts(user_id);
```

### Running Migrations

Use `db_migrate()` to execute pending migrations:

```php
function db_migrate(string $migrationDir): void
```

**Example:**

```php
// Run all pending migrations
db_migrate(__DIR__ . '/../database/migrations');
```

Lighthouse automatically:
- Tracks executed migrations in a `migrations` table
- Skips already-executed migrations
- Executes pending migrations in chronological order
- Logs which migrations were applied or skipped

### Creating the Initial Users Table

A sample migration is included. Run migrations during setup:

```php
// In your setup script or route
db_migrate(__DIR__ . '/database/migrations');
```

The default migration creates the users table:

```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

## Data Seeding

Use `db_seed()` to populate tables with initial data:

```php
function db_seed(string $file): void
```

**Example - Create a seed file `database/seeds/initial_data.sql`:**

```sql
-- Seed initial data
INSERT INTO users (email, password) VALUES
('admin@example.com', '$2y$10$...hashed_password_here...'),
('demo@example.com', '$2y$10$...hashed_password_here...');

INSERT INTO posts (user_id, title, body) VALUES
(1, 'First Post', 'Welcome to my blog!'),
(1, 'Second Post', 'Another interesting post.');
```

**Run the seed:**

```php
db_seed(__DIR__ . '/database/seeds/initial_data.sql');
```

## Practical Examples

### User Registration

```php
route('/register', function() {
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        // Validation
        if (!validate_email($email)) {
            $errors[] = 'Invalid email';
        } elseif (db_select_one('users', ['email' => $email])) {
            $errors[] = 'Email already registered';
        }

        if (!validate_min_length($password, 8)) {
            $errors[] = 'Password must be at least 8 characters';
        } elseif ($password !== $confirm) {
            $errors[] = 'Passwords do not match';
        }

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

    return view('register.php', ['errors' => $errors]);
});
```

### Blog Post Listing

```php
route('/blog', function() {
    $posts = db_select(
        'posts',
        ['published' => 1],
        'created_at DESC',
        20
    );

    return view('blog_list.php', [
        'posts' => $posts,
        'title' => 'Blog Posts',
    ]);
});
```

### Fetching Related Data

Since Lighthouse is lightweight without ORM, you may need to join data manually:

```php
route('/blog/{slug}', function($slug) {
    $post = db_select_one('posts', ['slug' => $slug]);

    if (!$post) {
        http_response_code(404);
        return view('404.php');
    }

    // Fetch author separately
    $author = db_select_one('users', ['id' => $post['user_id']]);

    // Fetch comments
    $comments = db_select(
        'comments',
        ['post_id' => $post['id'], 'approved' => 1],
        'created_at DESC'
    );

    return view('blog_post.php', [
        'post' => $post,
        'author' => $author,
        'comments' => $comments,
    ]);
});
```

### Updating User Profile

```php
route('/profile', function() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId = auth_user();
        if (!$userId) {
            http_response_code(403);
            return 'Unauthorized';
        }

        $name = sanitize_string($_POST['name'] ?? '');
        $bio = sanitize_string($_POST['bio'] ?? '');

        $success = db_update(
            'users',
            ['name' => $name, 'bio' => $bio],
            ['id' => $userId]
        );

        if ($success) {
            header('Location: /profile?updated=1');
            exit;
        }
    }

    $user = db_select_one('users', ['id' => auth_user()]);
    return view('profile.php', ['user' => $user]);
});
```

### Deleting With Confirmation

```php
route('/posts/{id}', function($id) {
    $post = db_select_one('posts', ['id' => $id]);

    if (!$post || $post['user_id'] !== auth_user()) {
        http_response_code(403);
        return 'Unauthorized';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        db_delete('posts', ['id' => $id]);
        header('Location: /posts');
        exit;
    }

    return view('post_detail.php', ['post' => $post]);
});
```

## Schema Design Best Practices

### Use Appropriate Data Types

```sql
-- ✓ Good
CREATE TABLE products (
    id INTEGER PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price REAL NOT NULL,
    stock INTEGER DEFAULT 0,
    active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ✗ Bad - All VARCHAR
CREATE TABLE products (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255),
    price VARCHAR(255),
    stock VARCHAR(255)
);
```

### Add Constraints

```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    title VARCHAR(255) NOT NULL,
    body TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Index Frequently Queried Columns

```sql
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_posts_user_id ON posts(user_id);
CREATE INDEX idx_posts_created_at ON posts(created_at DESC);
```

### Use Timestamps

```sql
-- Store both created and updated timestamps
CREATE TABLE articles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(255),
    body TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

## Query Limitations

Lighthouse's helper functions support basic operations. For complex queries, use raw SQL with PDO:

```php
// Complex query - use PDO directly
$pdo = db();
$sql = "SELECT u.id, u.email, COUNT(p.id) as post_count
        FROM users u
        LEFT JOIN posts p ON u.id = p.user_id
        WHERE u.active = 1
        GROUP BY u.id
        ORDER BY post_count DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

## Debugging

### View Database File

The SQLite database is a regular file you can inspect:

```bash
# Check if database exists
ls -lh database/database.sqlite

# Use sqlite3 CLI (if installed)
sqlite3 database/database.sqlite ".tables"
sqlite3 database/database.sqlite ".schema users"
```

### Enable Error Logging

Database errors are logged to `logs/error_log.txt`:

```php
// Errors are automatically logged via outlog()
// Check the log file to debug failed queries
tail -f logs/error_log.txt
```

## Performance Considerations

### SQLite Limitations

SQLite is excellent for small to medium applications but has limitations:

- **Concurrency:** Limited write concurrency (only one writer at a time)
- **Scale:** Best for < 1GB databases
- **Performance:** Not optimized for high-throughput scenarios

For high-traffic applications, consider migrating to PostgreSQL or MySQL.

### Optimization Tips

1. **Add indexes** for frequently filtered columns
2. **Use LIMIT** in queries that don't need all results
3. **Cache results** for expensive queries
4. **Avoid N+1 queries** - fetch related data efficiently

## See Also

- [Validation](validation.md) - Validate data before inserting
- [Authentication](authentication.md) - Store user data securely
- [Routing](routing.md) - Create database-driven routes
- [Views & Templates](views.md) - Display database data
