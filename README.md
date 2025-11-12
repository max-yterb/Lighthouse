# Lighthouse

### Install the Lighthouse CLI

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

Create a new project afterward:

```bash
lighthouse new my-app
```

### Run in development
```bash
php -S localhost:8000 -t public/
```
or
```bash
frankenphp php-server --root=public --listen=127.0.0.1:8000 --watch='./**/*.{php,css,js,env}'
```

### Using the CLI

* Version:

```bash
lighthouse version
```

* Create a new project in a folder NAME:

```bash
lighthouse new NAME
```

* Testing:

```bash
lighthouse test run
```

* Create an empty migration file:

```bash
lighthouse db make:migration NAME
```

* Apply the pending migrations:

```bash
lighthouse db migrate
```
