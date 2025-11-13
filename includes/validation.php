<?php

/**
 * Validation functions for Lighthouse
 *
 * Provides input validation functions for various data types and constraints.
 */

/**
 * Validates that a value is not empty
 *
 * @param mixed $value The value to validate
 * @return bool True if the value is not empty, false otherwise
 */
function validate_required(mixed $value): bool
{
    return !empty($value) || $value === '0';
}

/**
 * Validates that a value is a valid email address
 *
 * @param mixed $value The value to validate
 * @return bool True if the value is a valid email, false otherwise
 */
function validate_email(mixed $value): bool
{
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validates that a string value meets minimum length requirement
 *
 * @param mixed $value The value to validate
 * @param int $length The minimum required length
 * @return bool True if the value meets the minimum length, false otherwise
 */
function validate_min_length(mixed $value, int $length): bool
{
    return strlen((string)$value) >= $length;
}

/**
 * Validates that a string value does not exceed maximum length
 *
 * @param mixed $value The value to validate
 * @param int $length The maximum allowed length
 * @return bool True if the value does not exceed the maximum length, false otherwise
 */
function validate_max_length(mixed $value, int $length): bool
{
    return strlen((string)$value) <= $length;
}

/**
 * Validates that a value is numeric
 *
 * @param mixed $value The value to validate
 * @return bool True if the value is numeric, false otherwise
 */
function validate_numeric(mixed $value): bool
{
    return is_numeric($value);
}

/**
 * Validates that a value is an integer
 *
 * @param mixed $value The value to validate
 * @return bool True if the value is an integer, false otherwise
 */
function validate_integer(mixed $value): bool
{
    return filter_var($value, FILTER_VALIDATE_INT) !== false;
}

/**
 * Validates that a value contains only alphabetic characters
 *
 * @param mixed $value The value to validate
 * @return bool True if the value contains only alphabetic characters, false otherwise
 */
function validate_alphabetic(mixed $value): bool
{
    return ctype_alpha((string)$value);
}

/**
 * Validates that a value contains only alphanumeric characters
 *
 * @param mixed $value The value to validate
 * @return bool True if the value contains only alphanumeric characters, false otherwise
 */
function validate_alpha_numeric(mixed $value): bool
{
    return ctype_alnum((string)$value);
}

/**
 * Validates that a value is a valid URL
 *
 * @param mixed $value The value to validate
 * @return bool True if the value is a valid URL, false otherwise
 */
function validate_url(mixed $value): bool
{
    return filter_var($value, FILTER_VALIDATE_URL) !== false;
}

/**
 * Validates that a value is a valid IP address
 *
 * @param mixed $value The value to validate
 * @return bool True if the value is a valid IP address, false otherwise
 */
function validate_ip(mixed $value): bool
{
    return filter_var($value, FILTER_VALIDATE_IP) !== false;
}

/**
 * Validates that a value matches a date format
 *
 * @param mixed $value The value to validate
 * @param string $format The expected date format (default: Y-m-d)
 * @return bool True if the value matches the date format, false otherwise
 */
function validate_date(mixed $value, string $format = 'Y-m-d'): bool
{
    $d = DateTime::createFromFormat($format, (string)$value);
    return $d && $d->format($format) === (string)$value;
}

/**
 * Validates that a value is in an array of allowed values
 *
 * @param mixed $value The value to validate
 * @param array $array The array of allowed values
 * @return bool True if the value is in the array, false otherwise
 */
function value_in_array(mixed $value, array $array): bool
{
    return in_array($value, $array, true);
}
