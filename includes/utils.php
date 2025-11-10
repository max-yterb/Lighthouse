<?php
$routes = [];

function route(string $pattern, callable $handler): void
{
    global $routes;
    $routes[] = [
        'pattern' => $pattern,
        'handler' => $handler,
    ];
}

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


function outlog($msg)
{
    global $logDir;
    file_put_contents($logDir . 'error_log.txt', date('[Y-m-d H:i:s] ') . $msg . PHP_EOL, FILE_APPEND);
}

function view($file, $view_vars = [], $useLayout = null)
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

function exception_handler($e)
{
    global $content;
    outlog("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
    if (!headers_sent()) {
        http_response_code(500);
    }
    $content = "An unexpected error occurred.";
}

function shutdown_handler()
{
    global $content, $hxRequest, $viewDir, $layout, $meta;
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR])) {
        log("{$err['message']} in {$err['file']}:{$err['line']}");
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

function sanitize_string(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function sanitize_email(string $email): string
{
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

function sanitize_int($input): int
{
    return (int) filter_var($input, FILTER_SANITIZE_NUMBER_INT);
}

function sanitize_float($input): float
{
    return (float) filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

function sanitize_url(string $url): string
{
    return filter_var(trim($url), FILTER_SANITIZE_URL);
}

function csrf_token(): string
{
    return $_SESSION['csrf_token'] ?? '';
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function validate_csrf(string $token): bool
{
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

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
