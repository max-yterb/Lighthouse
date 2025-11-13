<?php
// core/config.php
declare(strict_types=1);

/**
 * Lighthouse Config Loader
 * - Loads from environment first
 * - Falls back to .env
 * - Optionally caches parsed values in production
 */

if (!function_exists('load_dotenv')) {
    /**
     * Loads environment variables from a .env file
     *
     * @param string $path Path to the .env file
     * @return array<string, string> Parsed environment variables
     */
    function load_dotenv(string $path): array
    {
        if (!file_exists($path)) return [];

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $parsed = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;

            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) continue;

            $name  = trim($parts[0]);
            $value = trim($parts[1]);

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            if (getenv($name) === false) {
                putenv("$name=$value");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }

            $parsed[$name] = $value;
        }

        return $parsed;
    }
}

if (!function_exists('normalize_url')) {
    /**
     * Normalizes a URL by trimming and removing trailing slashes
     *
     * @param string $url The URL to normalize
     * @return string The normalized URL
     */
    function normalize_url(string $url): string
    {
        $url = trim($url);
        return $url === '' ? $url : rtrim($url, '/');
    }
}

if (!function_exists('env_or')) {
    /**
     * Gets an environment variable with a default fallback
     *
     * @param string $name The environment variable name
     * @param mixed $default The default value if not found
     * @return mixed The environment variable value or default
     */
    function env_or(string $name, mixed $default = null): mixed
    {
        $v = getenv($name);
        return $v === false ? $default : $v;
    }
}

if (!function_exists('bool_from_env')) {
    /**
     * Converts an environment variable value to a boolean
     *
     * @param mixed $value The value to convert
     * @return bool The boolean representation
     */
    function bool_from_env(mixed $value): bool
    {
        if (is_bool($value)) return $value;
        $value = strtolower((string)$value);
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }
}

// --- main logic ---
$root = realpath(__DIR__ . '/../') ?: __DIR__ . '/..';
$dotenv_path = $root . '/.env';
$cache_path  = sys_get_temp_dir() . '/lighthouse_env_cache.php';

$app_env = getenv('APP_ENV') ?: 'local';
$use_cache = false;

// Use cache only in production, if valid
if ($app_env === 'production' && file_exists($cache_path) && file_exists($dotenv_path)) {
    $cache_mtime = filemtime($cache_path);
    $env_mtime   = filemtime($dotenv_path);
    if ($cache_mtime >= $env_mtime) {
        $use_cache = true;
    }
}

$env_values = [];
if ($use_cache) {
    $env_values = require $cache_path;
    foreach ($env_values as $k => $v) {
        if (getenv($k) === false) {
            putenv("$k=$v");
            $_ENV[$k] = $v;
            $_SERVER[$k] = $v;
        }
    }
} else {
    $env_values = load_dotenv($dotenv_path);
    $app_env = getenv('APP_ENV') ?: ($env_values['APP_ENV'] ?? 'local');
    if ($app_env === 'production' && !empty($env_values)) {
        file_put_contents(
            $cache_path,
            "<?php\nreturn " . var_export($env_values, true) . ";\n"
        );
    }
}

// --- validate required environment variables ---
$required_vars = [
    'APP_NAME',
    'APP_ENV',
    'APP_DEBUG',
    'APP_URL',
    'TIMEZONE',
    'LOG_FILE',
    'THEME',
    'DEFAULT_TITLE',
    'DEFAULT_DESC',
    'DEFAULT_CANONICAL',
    'DEFAULT_AUTHOR',
    'DEFAULT_KEYWORDS',
    'DEFAULT_CHARSET'
];

$missing_vars = [];
foreach ($required_vars as $var) {
    $value = env_or($var, null);
    if ($value === null || $value === '') {
        $missing_vars[] = $var;
    }
}

if (!empty($missing_vars)) {
    $error_msg = "Missing required environment variables: " . implode(', ', $missing_vars) . "\n";
    $error_msg .= "Please set these in your .env file or environment.\n";
    if (!defined('TESTING')) {
        die($error_msg);
    } else {
        // In tests, just log the error but continue
        error_log($error_msg);
    }
}

// --- build config ---
$config = [
    'APP_NAME'      => env_or('APP_NAME', 'Lighthouse'),
    'APP_ENV'       => env_or('APP_ENV', 'local'),
    'APP_DEBUG'     => bool_from_env(env_or('APP_DEBUG', (env_or('APP_ENV', 'local') === 'local'))),
    'APP_URL'       => normalize_url(env_or('APP_URL', 'http://localhost:8000')),
    'TIMEZONE'      => env_or('TIMEZONE', 'UTC'),
    'LOG_FILE'      => env_or('LOG_FILE', $root . '/logs/error_log.txt'),
    'THEME'         => env_or('THEME', 'pico.blue.min.css'),
    'DEFAULT_TITLE' => env_or('DEFAULT_TITLE', 'Welcome to Lighthouse'),
    'DEFAULT_DESC'  => env_or('DEFAULT_DESC', 'A minimal, predictable PHP micro-stack.'),
    'DEFAULT_CANONICAL' => env_or('DEFAULT_CANONICAL', env_or('APP_URL', 'http://localhost:8000')),
    'DEFAULT_AUTHOR' => env_or('DEFAULT_AUTHOR', 'Max'),
    'DEFAULT_KEYWORDS' => env_or('DEFAULT_KEYWORDS', 'php, microstack, htmx, pico.css'),
    'DEFAULT_CHARSET' => env_or('DEFAULT_CHARSET', 'utf-8'),
    'APP_VERSION'  => (file_exists($root . '/VERSION') ? trim(file_get_contents($root . '/VERSION')) : 'dev'),
    '_CACHE_USED'   => $use_cache,
    '_CACHE_PATH'   => $cache_path,
];

// Optional helper for global access (if desired)
if (!function_exists('config')) {
    /**
     * Gets a configuration value by key or all configuration if no key provided
     *
     * @param string|null $key The configuration key to retrieve
     * @return mixed The configuration value or all configuration array
     */
    function config(?string $key = null): mixed
    {
        global $config;
        return $key ? ($config[$key] ?? null) : $config;
    }
}

return $config;
