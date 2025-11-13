<?php

/**
 * Authentication functions for Lighthouse
 *
 * Provides session-based authentication with password hashing and user management.
 */

/**
 * Logs in a user by storing their ID in the session
 *
 * @param int $userId The user ID to log in
 * @return void
 */
function auth_login(int $userId): void
{
    $_SESSION['user_id'] = $userId;
}

/**
 * Logs out the current user by destroying the session
 *
 * @return void
 */
function auth_logout(): void
{
    session_destroy();
    $_SESSION = [];
}

/**
 * Gets the current logged-in user ID from session
 *
 * @return int|null The user ID if logged in, null otherwise
 */
function auth_user(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Hashes a password using bcrypt
 *
 * @param string $password The plain text password
 * @return string The hashed password
 */
function auth_hash_password(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verifies a password against a hash
 *
 * @param string $password The plain text password
 * @param string $hash The hashed password
 * @return bool True if password matches, false otherwise
 */
function auth_verify_password(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}
