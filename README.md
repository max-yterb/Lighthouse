# 🚨 Lighthouse PHP Framework

> A minimal, predictable PHP micro-framework for building modern web applications

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![GitHub Stars](https://img.shields.io/github/stars/max-yterb/Lighthouse?style=social)](https://github.com/max-yterb/Lighthouse)

## ✨ Features

- **⚡ Lightning Fast** - Built for speed and performance with minimal overhead
- **🎯 Simple & Focused** - Clean, predictable API that gets out of your way
- **🔒 Secure by Default** - Built-in CSRF protection, input validation, and authentication
- **📱 Modern Stack** - PHP 8+, HTMX, Pico.css, and SQLite
- **🚀 Deploy Anywhere** - Simple deployment, works on any server with PHP 8+
- **🛠️ Developer Friendly** - Type hints, modern PHP features, and comprehensive documentation

## 🚀 Quick Start

### Installation

Requirements: bash, PHP 8.x, curl or wget

Install the standalone CLI (defaults to ~/.local/bin):

```bash
bash -c "$(curl -fsSL https://raw.githubusercontent.com/max-yterb/Lighthouse/main/scripts/install.sh)"
```

or with wget:

```bash
bash -c "$(wget -qO- https://raw.githubusercontent.com/max-yterb/Lighthouse/main/scripts/install.sh)"
```

If ~/.local/bin is not on your PATH, add it to your shell profile:

```bash
export PATH="$HOME/.local/bin:$PATH"
```

### Create Your First Project

```bash
# Create a new project
lighthouse new my-app
cd my-app

# Start development server
php -S localhost:8000 -t public/
```

Or with FrankenPHP (for production parity during development):

```bash
frankenphp php-server --root=public --listen=127.0.0.1:8000 --watch='./**/*.{php,css,js,env}'
```

### Your First Route

```php
<?php
// app_routes.php
route('/', function() {
    return view('home.php', ['title' => 'Welcome']);
});

route('/about', function() {
    return view('about.php', ['title' => 'About Us']);
});
```

Logic lives in your views—the route just renders templates. This keeps things simple and predictable.

## 🏗️ Project Structure

```
lighthouse/
├── includes/           # Core framework files
│   ├── auth.php       # Authentication helpers
│   ├── config.php     # Configuration loader
│   ├── db.php         # Database helpers
│   ├── utils.php      # Utility functions
│   └── validation.php # Validation functions
├── views/             # View templates
│   ├── _layout.php    # Main layout
│   └── *.php          # Page templates
├── public/            # Web root
│   ├── css/           # Stylesheets
│   ├── js/            # JavaScript files
│   └── index.php      # Entry point
├── database/          # Database files
├── logs/              # Log files
└── .env               # Environment configuration
```

## 🛠️ CLI Commands

```bash
# Check version
lighthouse version

# Create a new project
lighthouse new PROJECT_NAME

# Run tests
lighthouse test run

# Database migrations
lighthouse db make:migration MIGRATION_NAME
lighthouse db migrate
```

## 🎨 Brand Colors

Lighthouse comes with a beautiful, sea-inspired color palette:

| Color Name | Hex Code | Usage |
|------------|----------|-------|
| Beacon Red | `#E63946` | Accent / Logo / Links |
| Sea Slate  | `#1D3557` | Primary background or text |
| Fog White  | `#F1FAEE` | Page background |
| Sky Mist   | `#A8DADC` | Borders / secondary background |
| Signal Blue| `#457B9D` | Buttons / code highlights |

## 📚 Documentation

- [📖 **Getting Started**](docs/getting-started.md) - Installation and basic setup
- [🛣️ **Routing**](docs/routing.md) - URL routing and parameters
- [👁️ **Views & Templates**](docs/views.md) - Rendering views and layouts
- [🗄️ **Database**](docs/database.md) - Database operations and migrations
- [🔐 **Authentication**](docs/authentication.md) - User authentication and sessions
- [✅ **Validation**](docs/validation.md) - Input validation and sanitization
- [🎨 **Frontend**](docs/frontend.md) - Working with HTMX and Pico.css
- [🚀 **Deployment**](docs/deployment.md) - Production deployment guide

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup

```bash
# Clone the repository
git clone https://github.com/max-yterb/Lighthouse.git
cd Lighthouse

# Copy environment file
cp .env.example .env

# Start development server
php -S localhost:8000 -t public
```

## 💬 Community

- [💬 **Discussions**](https://github.com/max-yterb/Lighthouse/discussions) - Ask questions and share ideas
- [🐛 **Issues**](https://github.com/max-yterb/Lighthouse/issues) - Report bugs and request features
- [📧 **Email**](mailto:max.yterb@gmail.com) - Direct contact

## 💖 Support Lighthouse

Lighthouse is free and open-source. If you're using it in production or want to support its development:

- ⭐ **Star the repository** - Help others discover Lighthouse
- 💝 **[Become a GitHub Sponsor](https://github.com/sponsors/max-yterb)** - Support ongoing development
- 🏢 **[Enterprise Support](mailto:max.yterb@gmail.com?subject=Lighthouse%20Enterprise%20Support)** - Priority support and custom features
- 📢 **Share your success story** - Tell others about your Lighthouse projects

### 🚀 Need Custom Features?

For mission-critical features or custom development:
- **Priority Development** - Get your features built first
- **Custom Integrations** - Tailored solutions for your business
- **Training & Consulting** - Expert guidance for your team
- **Code Audits** - Security and performance reviews

[Contact us for enterprise solutions →](mailto:max.yterb@gmail.com?subject=Lighthouse%20Custom%20Development)

## 📄 License

Lighthouse is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Acknowledgments

- Built with ❤️ by [Massimiliano Bertinetti](https://github.com/max-yterb)
- Inspired by modern PHP frameworks and minimalist design principles
- Special thanks to the PHP community

---

<div align="center">
  <strong>Ready to build something amazing?</strong><br>
  <a href="https://github.com/max-yterb/Lighthouse/discussions">Join the community</a> •
  <a href="docs/getting-started.md">Read the docs</a> •
  <a href="https://github.com/max-yterb/Lighthouse/issues/new">Report a bug</a>
</div>
