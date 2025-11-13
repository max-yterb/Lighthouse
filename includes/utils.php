<?php

/**
 * Global routes array for storing application routes
 * @var array<array{pattern: string, handler: callable}>
 */
$routes = [];

/**
 * Registers a route with a pattern and handler
 *
 * @param string $pattern URL pattern (supports {param} syntax)
 * @param callable $handler Function to handle the route
 * @return void
 */
function route(string $pattern, callable $handler): void
{
    global $routes;
    $routes[] = [
        'pattern' => $pattern,
        'handler' => $handler,
    ];
}

/**
 * Dispatches a request to the appropriate route handler
 *
 * @param string $requestUri The URI to match against registered routes
 * @return void
 */
function dispatch(string $requestUri): void
{
    global $routes, $content;

    foreach ($routes as $route) {
        // Simple pattern matching (supports {param} syntax)
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route['pattern']);
        $pattern = str_replace('/', '\/', $pattern);

        if (preg_match("/^$pattern$/", $requestUri, $matches)) {
            array_shift($matches); // Remove full match
            $content = call_user_func_array($route['handler'], $matches);
            return;
        }
    }

    // No route matched - 404
    if (!defined('TESTING')) {
        http_response_code(404);
    }
    $content = view('404.php');
}


/**
 * Logs a message to the error log file
 *
 * @param string $msg The message to log
 * @return void
 */
function outlog(string $msg): void
{
    global $logDir;
    file_put_contents($logDir . 'error_log.txt', date('[Y-m-d H:i:s] ') . $msg . PHP_EOL, FILE_APPEND);
}

/**
 * Renders a view file with optional variables and layout
 *
 * @param string $file The view file to render
 * @param array<string, mixed> $view_vars Variables to pass to the view
 * @param string|null $useLayout Optional layout override
 * @return string|false The rendered view content or false on failure
 */
function view(string $file, array $view_vars = [], ?string $useLayout = null): string|false
{
    global $viewDir, $layout, $meta;
    if ($useLayout !== null) $layout = $useLayout;

    // Handle individual meta overrides (shorthand) - set defaults from global meta
    $metaKeys = ['title', 'description', 'author', 'keywords', 'charset', 'canonical'];

    foreach ($metaKeys as $key) {
        if (isset($view_vars[$key])) {
            $meta[$key] = $view_vars[$key];
        }
    }

    ob_start();
    require $viewDir . $file;
    return ob_get_clean();
}

/**
 * Handles uncaught exceptions
 *
 * @param Throwable $e The exception to handle
 * @return void
 */
function exception_handler(Throwable $e): void
{
    global $content;
    outlog("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
    if (!headers_sent()) {
        http_response_code(500);
    }
    $content = "An unexpected error occurred.";
}

/**
 * Handles shutdown and fatal errors
 *
 * @return void
 */
function shutdown_handler(): void
{
    global $content, $hxRequest, $viewDir, $layout, $meta;
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR])) {
        outlog("{$err['message']} in {$err['file']}:{$err['line']}");
        if (!defined('TESTING')) {
            http_response_code(500);
        }
        $content = "Critical failure.";
    }
    if (defined('TESTING')) {
        // Don't output anything during testing
        return;
    }
    if ($hxRequest) {
        echo $content;
    } else {
        require $viewDir . $layout;
    }
}

/**
 * Sanitizes a string by trimming and escaping HTML characters
 *
 * @param mixed $input The input to sanitize
 * @return string The sanitized string
 */
function sanitize_string(mixed $input): string
{
    return htmlspecialchars(trim((string)$input), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitizes an email address
 *
 * @param mixed $email The email to sanitize
 * @return string The sanitized email
 */
function sanitize_email(mixed $email): string
{
    return filter_var(trim((string)$email), FILTER_SANITIZE_EMAIL);
}

/**
 * Sanitizes input to an integer
 *
 * @param mixed $input The input to sanitize
 * @return int The sanitized integer
 */
function sanitize_int(mixed $input): int
{
    return (int) filter_var($input, FILTER_SANITIZE_NUMBER_INT);
}

/**
 * Sanitizes input to a float
 *
 * @param mixed $input The input to sanitize
 * @return float The sanitized float
 */
function sanitize_float(mixed $input): float
{
    return (float) filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

/**
 * Sanitizes a URL
 *
 * @param mixed $url The URL to sanitize
 * @return string The sanitized URL
 */
function sanitize_url(mixed $url): string
{
    return filter_var(trim((string)$url), FILTER_SANITIZE_URL);
}

/**
 * Gets the CSRF token from the session
 *
 * @return string The CSRF token or empty string if not set
 */
function csrf_token(): string
{
    return $_SESSION['csrf_token'] ?? '';
}

/**
 * Generates a hidden CSRF token input field
 *
 * @return string The HTML input field
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Validates a CSRF token
 *
 * @param string $token The token to validate
 * @return bool Whether the token is valid
 */
function validate_csrf(string $token): bool
{
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Checks if a request is within rate limits
 *
 * @param string $key The rate limit key (e.g., IP address)
 * @param int $maxRequests Maximum number of requests allowed
 * @param int $windowSeconds Time window in seconds
 * @return bool True if within limits, false if rate limited
 */
function check_rate_limit(string $key, int $maxRequests = 5, int $windowSeconds = 300): bool
{
    global $rateLimitFile;

    $data = json_decode(file_get_contents($rateLimitFile), true) ?: [];
    $now = time();

    if (!isset($data[$key])) {
        $data[$key] = ['count' => 1, 'reset' => $now + $windowSeconds];
    } else {
        if ($now > $data[$key]['reset']) {
            $data[$key] = ['count' => 1, 'reset' => $now + $windowSeconds];
        } else {
            $data[$key]['count']++;
        }
    }

    file_put_contents($rateLimitFile, json_encode($data));

    return $data[$key]['count'] <= $maxRequests;
}

/**
 * Gets the number of remaining requests for a rate limit key
 *
 * @param string $key The rate limit key (e.g., IP address)
 * @return int Number of remaining requests
 */
function get_rate_limit_remaining(string $key): int
{
    global $rateLimitFile;

    $data = json_decode(file_get_contents($rateLimitFile), true) ?: [];
    $now = time();

    if (!isset($data[$key]) || $now > $data[$key]['reset']) {
        return 5; // Default max requests
    }

    return max(0, 5 - $data[$key]['count']); // Assuming 5 max requests
}
