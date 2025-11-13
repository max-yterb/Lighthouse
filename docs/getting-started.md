# Getting Started with Lighthouse

Welcome to Lighthouse! This guide will help you get up and running with the framework in minutes.

## 📋 Requirements

- **PHP 8.0+** (8.1+ recommended)
- **Web server** (Apache, Nginx, or PHP built-in server)
- **SQLite** (usually included with PHP)
- **Composer** (optional, for dependencies)

## 🚀 Installation

### Option 1: Using the CLI (Recommended)

```bash
# Install the Lighthouse CLI
bash -c "$(curl -fsSL https://raw.githubusercontent.com/max-yterb/Lighthouse/main/scripts/install.sh)"

# Add to PATH if needed
export PATH="$HOME/.local/bin:$PATH"

# Create a new project
lighthouse new my-app
cd my-app
```

### Option 2: Manual Installation

```bash
# Clone the repository
git clone https://github.com/max-yterb/Lighthouse.git my-app
cd my-app

# Copy environment file
cp .env.example .env

# Set up permissions
chmod -R 755 public/
chmod -R 777 logs/ database/
```

## 🔧 Configuration

### Environment Setup

Edit your `.env` file:

```env
APP_NAME=MyApp
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Security
APP_KEY=your-secret-key-here
```

### Directory Permissions

Ensure these directories are writable:
- `logs/` - For error logs
- `database/` - For SQLite database
- `public/uploads/` - If you plan to handle file uploads

## 🌐 Development Server

### PHP Built-in Server

```bash
php -S localhost:8000 -t public/
```

### With FrankenPHP (Hot Reloading)

```bash
frankenphp php-server --root=public --listen=127.0.0.1:8000 --watch='./**/*.{php,css,js,env}'
```

### Apache Configuration

```apache
<VirtualHost *:80>
    DocumentRoot /path/to/your/app/public
    ServerName myapp.local
    
    <Directory /path/to/your/app/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name myapp.local;
    root /path/to/your/app/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 🎯 Your First Application

### 1. Create a Route

Edit `routes.php`:

```php
<?php

// Simple route
route('/', function() {
    return view('welcome.php', [
        'title' => 'Welcome to Lighthouse!',
        'message' => 'Your app is ready to sail! ⛵'
    ]);
});

// Route with parameters
route('/user/{id}', function($id) {
    $user = db_select_one('users', ['id' => $id]);
    return view('user.php', ['user' => $user]);
});

// API route
route('/api/users', function() {
    $users = db_select('users');
    header('Content-Type: application/json');
    return json_encode($users);
});
```

### 2. Create a View

Create `views/welcome.php`:

```php
<div class="lighthouse-hero">
    <div class="container">
        <h1><?= htmlspecialchars($title) ?></h1>
        <p><?= htmlspecialchars($message) ?></p>
    </div>
</div>

<div class="container">
    <div class="lighthouse-card">
        <h2>🎉 Congratulations!</h2>
        <p>Your Lighthouse application is running successfully.</p>
        
        <h3>Next Steps:</h3>
        <ul>
            <li>📖 Read the <a href="https://github.com/max-yterb/Lighthouse/tree/main/docs">documentation</a></li>
            <li>🛣️ Learn about <a href="routing.md">routing</a></li>
            <li>🗄️ Set up your <a href="database.md">database</a></li>
            <li>🔐 Add <a href="authentication.md">authentication</a></li>
        </ul>
    </div>
</div>
```

### 3. Database Setup

```php
<?php
// Create a simple users table
$sql = "
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

db_exec($sql);

// Insert a test user
db_insert('users', [
    'email' => 'test@example.com',
    'password' => auth_hash_password('password123')
]);
```

## 🔍 Testing Your Setup

Visit `http://localhost:8000` in your browser. You should see:
- ✅ Welcome page loads
- ✅ Lighthouse branding and styling
- ✅ No PHP errors in logs

## 🆘 Troubleshooting

### Common Issues

**"Permission denied" errors:**
```bash
chmod -R 755 public/
chmod -R 777 logs/ database/
```

**"Class not found" errors:**
- Check that all files in `includes/` are present
- Verify `bootstrap.php` is loading correctly

**Database errors:**
- Ensure `database/` directory exists and is writable
- Check SQLite is installed: `php -m | grep sqlite`

**Routing not working:**
- Verify `.htaccess` file exists in `public/`
- Check web server URL rewriting is enabled

### Getting Help

- 💬 [GitHub Discussions](https://github.com/max-yterb/Lighthouse/discussions)
- 🐛 [Report Issues](https://github.com/max-yterb/Lighthouse/issues)
- 📧 [Email Support](mailto:max@example.com)

## 🎯 What's Next?

Now that you have Lighthouse running, explore these topics:

1. **[Routing](routing.md)** - Learn about URL routing and parameters
2. **[Views](views.md)** - Master templates and layouts
3. **[Database](database.md)** - Work with data and migrations
4. **[Authentication](authentication.md)** - Secure your application
5. **[Frontend](frontend.md)** - Use HTMX and modern CSS

Happy coding! 🚨⚡
