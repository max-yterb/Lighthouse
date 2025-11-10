# AskAgainV2

### Run in development
```bash
php -S localhost:8000 -t public/
```
or
```bash
frankenphp php-server --root=public --listen=127.0.0.1:8000 --watch='./**/*.{php,css,js,env}'
```

### Using the CLI

* Testing:

```bash
./cli test run
```

* Create an empty migration file:

```bash
./cli db make:migration NAME
```

* Apply the pending migrations:

```bash
./cli db migrate
```
