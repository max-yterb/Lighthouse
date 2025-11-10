<?php
require_once __DIR__ . '/bootstrap.php';

run_test(
    'Required',
    function () {
        assert_true(validate_required('test'), 'Non-empty string should be required');
        assert_true(validate_required('0'), 'String "0" should be required');
        assert_false(validate_required(''), 'Empty string should not be required');
        assert_false(validate_required(null), 'Null should not be required');
    }
);

run_test(
    'Email',
    function () {
        assert_true(validate_email('test@example.com'), 'Valid email should pass');
        assert_false(validate_email('invalid-email'), 'Invalid email should fail');
        assert_false(validate_email(''), 'Empty email should fail');
    }
);

run_test(
    'Min Lenght',
    function () {
        assert_true(validate_min_length('hello', 3), 'String longer than min should pass');
        assert_true(validate_min_length('hi', 2), 'String equal to min should pass');
        assert_false(validate_min_length('hi', 3), 'String shorter than min should fail');
    }
);

run_test(
    'Max Length',
    function () {
        assert_true(validate_max_length('hi', 3), 'String shorter than max should pass');
        assert_true(validate_max_length('hi', 2), 'String equal to max should pass');
        assert_false(validate_max_length('hello', 3), 'String longer than max should fail');
    }
);

run_test(
    'Numeric',
    function () {
        assert_true(validate_numeric('123'), 'Numeric string should pass');
        assert_true(validate_numeric(123), 'Integer should pass');
        assert_true(validate_numeric(123.45), 'Float should pass');
        assert_false(validate_numeric('abc'), 'Non-numeric string should fail');
    }
);

run_test(
    'Integer',
    function () {
        assert_true(validate_integer(123), 'Integer should pass');
        assert_true(validate_integer('123'), 'Numeric string should pass');
        assert_false(validate_integer(123.45), 'Float should fail');
        assert_false(validate_integer('abc'), 'Non-numeric string should fail');
    }
);

run_test(
    'Alphabetic',
    function () {
        assert_true(validate_alphabetic('abc'), 'Letters should pass');
        assert_false(validate_alphabetic('abc123'), 'Letters with numbers should fail');
        assert_false(validate_alphabetic('abc!'), 'Letters with symbols should fail');
    }
);

run_test(
    'Alphanumeric',
    function () {
        assert_true(validate_alpha_numeric('abc123'), 'Letters and numbers should pass');
        assert_false(validate_alpha_numeric('abc!'), 'Letters with symbols should fail');
    }
);
run_test(
    'URL',
    function () {
        assert_true(validate_url('https://example.com'), 'Valid URL should pass');
        assert_false(validate_url('not-a-url'), 'Invalid URL should fail');
    }
);

run_test(
    'IP Address',
    function () {
        assert_true(validate_ip('192.168.1.1'), 'Valid IP should pass');
        assert_false(validate_ip('999.999.999.999'), 'Invalid IP should fail');
    }
);

run_test(
    'Date',
    function () {
        assert_true(validate_date('2023-12-25', 'Y-m-d'), 'Valid date should pass');
        assert_false(validate_date('invalid-date', 'Y-m-d'), 'Invalid date should fail');
    }
);

run_test(
    'In Array',
    function () {
        $array = ['a', 'b', 'c'];
        assert_true(value_in_array('a', $array), 'Existing value should pass');
        assert_false(value_in_array('d', $array), 'Non-existing value should fail');
    }
);
