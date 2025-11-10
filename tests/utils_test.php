<?php
require_once __DIR__ . '/bootstrap.php';

// ----------------------------
// ROUTE / DISPATCH TESTS
// ----------------------------

run_test('Route and dispatch simple path', function () {
    global $content;

    route('/hello', function () {
        return 'Hello World';
    });

    dispatch('/hello');
    assert_equals('Hello World', $content);
});

run_test('Route with parameter', function () {
    global $content;

    route('/user/{id}', function ($id) {
        return "User ID: $id";
    });

    dispatch('/user/42');
    assert_equals('User ID: 42', $content);
});

run_test('Dispatch unknown route returns 404', function () {
    global $content;

    dispatch('/unknown-page');
    assert_string_contains($content, '404'); // your 404.php should have "404" somewhere
});

// ----------------------------
// SANITIZATION TESTS
// ----------------------------

run_test('Sanitize string', function () {
    assert_equals('Hello &lt;script&gt;', sanitize_string(' Hello <script> '));
});

run_test('Sanitize email', function () {
    assert_equals('test@example.com', sanitize_email(' test@example.com '));
});

run_test('Sanitize int', function () {
    assert_equals(123, sanitize_int(' 123abc '));
});

run_test('Sanitize float', function () {
    assert_equals(12.34, sanitize_float(' 12.34abc '));
});

run_test('Sanitize URL', function () {
    assert_equals('https://example.com', sanitize_url(' https://example.com '));
});

// ----------------------------
// CSRF TESTS
// ----------------------------

run_test('CSRF token empty if not set', function () {
    unset($_SESSION['csrf_token']);
    assert_equals('', csrf_token());
});

run_test('CSRF field contains token', function () {
    $_SESSION['csrf_token'] = 'abc123';
    assert_string_contains(csrf_field(), 'abc123');
});

run_test('Validate CSRF token', function () {
    $_SESSION['csrf_token'] = 'abc123';
    assert_true(validate_csrf('abc123'));
    assert_false(validate_csrf('wrongtoken'));
});

// ----------------------------
// RATE LIMIT TESTS
// ----------------------------

$rateLimitFile = __DIR__ . '/test_rate_limit.json';

run_test('Rate limit allows first request', function () {
    global $rateLimitFile;
    // start fresh
    file_put_contents($rateLimitFile, '{}');

    assert_true(check_rate_limit('user1', 5, 300));
    assert_equals(4, get_rate_limit_remaining('user1')); // remaining should now be 4
});

run_test('Rate limit exceeds max requests', function () {
    global $rateLimitFile;

    // simulate 5 requests already
    file_put_contents($rateLimitFile, json_encode([
        'user1' => ['count' => 5, 'reset' => time() + 300]
    ]));

    assert_false(check_rate_limit('user1', 5, 300));
    assert_equals(0, get_rate_limit_remaining('user1'));
});

run_test('Clean up rate limit file', function () {
    global $rateLimitFile;
    if (file_exists($rateLimitFile)) unlink($rateLimitFile);
    assert_false(file_exists($rateLimitFile));
});
