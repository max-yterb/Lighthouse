<?php
require_once __DIR__ . '/bootstrap.php';

run_test(
    'Password Hash',
    function () {
        $password = 'testpassword';
        $hash = auth_hash_password($password);

        assert_not_empty($hash, 'Password hash should not be empty');
        assert_true(password_verify($password, $hash), 'Password should verify correctly');
    }
);

run_test(
    'Password Verify',
    function () {
        $password = 'testpassword';
        $hash = password_hash($password, PASSWORD_DEFAULT);

        assert_true(auth_verify_password($password, $hash), 'Password verification should succeed');
        assert_false(auth_verify_password('wrongpassword', $hash), 'Wrong password should fail verification');
    }
);

run_test(
    'Login',
    function () {
        $userId = 123;
        auth_login($userId);

        assert_equals($userId, auth_user(), 'User should be logged in');
    }
);

run_test(
    'Logout',
    function () {
        $userId = 123;
        auth_login($userId);
        assert_equals($userId, auth_user(), 'User should be logged in');

        auth_logout();
        assert_null(auth_user(), 'User should be logged out');
    }
);
