# Contributing to Lighthouse

🎉 Thank you for your interest in contributing to Lighthouse! We welcome contributions from everyone.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [How to Contribute](#how-to-contribute)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Pull Request Process](#pull-request-process)
- [Issue Guidelines](#issue-guidelines)

## 📜 Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## 🚀 Getting Started

1. Fork the repository on GitHub
2. Clone your fork locally
3. Create a new branch for your feature or bugfix
4. Make your changes
5. Test your changes
6. Submit a pull request

## 🤝 How to Contribute

### 🐛 Reporting Bugs

- Use the [GitHub Issues](https://github.com/max-yterb/Lighthouse/issues) page
- Search existing issues first to avoid duplicates
- Use the bug report template
- Include as much detail as possible:
  - PHP version
  - Operating system
  - Steps to reproduce
  - Expected vs actual behavior
  - Error messages or logs

### 💡 Suggesting Features

- Use [GitHub Discussions](https://github.com/max-yterb/Lighthouse/discussions) for feature ideas
- Explain the use case and benefits
- Consider backward compatibility
- Be open to feedback and discussion

### 📝 Documentation

- Documentation improvements are always welcome
- Fix typos, improve clarity, add examples
- Documentation files are in the `docs/` directory
- Update README.md if needed

### 💻 Code Contributions

- Bug fixes
- New features (discuss first in GitHub Discussions)
- Performance improvements
- Code quality improvements

## 🛠️ Development Setup

```bash
# Clone your fork
git clone https://github.com/YOUR_USERNAME/Lighthouse.git
cd Lighthouse

# Copy environment file
cp .env.example .env

# Start development server
php -S localhost:8000 -t public

# Or with FrankenPHP for hot reloading
frankenphp php-server --root=public --listen=127.0.0.1:8000 --watch='./**/*.{php,css,js,env}'
```

## 📏 Coding Standards

### PHP Standards

- **PHP 8.0+** features encouraged
- **PSR-12** coding standard
- **Type hints** for all function parameters and return types
- **DocBlocks** for all functions and classes
- **Strict types** declaration: `declare(strict_types=1);`

### Code Style

```php
<?php

declare(strict_types=1);

/**
 * Example function with proper type hints and documentation
 *
 * @param string $name The user's name
 * @param int $age The user's age
 * @return array<string, mixed> User data array
 */
function create_user(string $name, int $age): array
{
    return [
        'name' => $name,
        'age' => $age,
        'created_at' => date('Y-m-d H:i:s'),
    ];
}
```

### File Organization

- **Core functions** go in `includes/`
- **Views** go in `views/`
- **Public assets** go in `public/`
- **Tests** go in `tests/` (when implemented)
- **Documentation** goes in `docs/`

## 🧪 Testing

- Write tests for new features
- Ensure existing tests pass
- Test on multiple PHP versions if possible
- Manual testing on different environments

```bash
# Run tests (when implemented)
lighthouse test run
```

## 🔄 Pull Request Process

1. **Create a branch** from `main`:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes** following coding standards

3. **Test your changes** thoroughly

4. **Commit with clear messages**:
   ```bash
   git commit -m "Add user authentication middleware"
   ```

5. **Push to your fork**:
   ```bash
   git push origin feature/your-feature-name
   ```

6. **Create a Pull Request** with:
   - Clear title and description
   - Reference any related issues
   - Screenshots if UI changes
   - List of changes made

### PR Requirements

- ✅ Follows coding standards
- ✅ Includes tests (when applicable)
- ✅ Updates documentation if needed
- ✅ No breaking changes (or clearly documented)
- ✅ Passes all existing tests

## 📋 Issue Guidelines

### Bug Reports

```markdown
**Describe the bug**
A clear description of what the bug is.

**To Reproduce**
1. Go to '...'
2. Click on '....'
3. See error

**Expected behavior**
What you expected to happen.

**Environment:**
- PHP version: [e.g. 8.2]
- OS: [e.g. Ubuntu 22.04]
- Lighthouse version: [e.g. 1.0.0]
```

### Feature Requests

```markdown
**Is your feature request related to a problem?**
A clear description of what the problem is.

**Describe the solution you'd like**
A clear description of what you want to happen.

**Additional context**
Any other context about the feature request.
```

## 🏷️ Labels

- `bug` - Something isn't working
- `enhancement` - New feature or request
- `documentation` - Improvements to documentation
- `good first issue` - Good for newcomers
- `help wanted` - Extra attention is needed
- `question` - Further information is requested

## 🎯 Areas for Contribution

- **Core Framework**: Routing, database, authentication
- **CLI Tools**: Command improvements and new features
- **Documentation**: Guides, examples, API docs
- **Testing**: Unit tests, integration tests
- **Performance**: Optimization and benchmarking
- **Security**: Security audits and improvements
- **Examples**: Sample applications and tutorials

## 💬 Getting Help

- [GitHub Discussions](https://github.com/max-yterb/Lighthouse/discussions) - General questions
- [GitHub Issues](https://github.com/max-yterb/Lighthouse/issues) - Bug reports
- [Email](mailto:max.yterb@gmail.com) - Direct contact

## 🙏 Recognition

Contributors will be:
- Listed in the project's contributors
- Mentioned in release notes for significant contributions
- Given credit in documentation they help create

---

**Thank you for contributing to Lighthouse!** 🚨⚡
