<?php
require_once __DIR__ . '/bootstrap.php';

run_test(
    'Ovverrides from Environment',
    function () {
        putenv('APP_NAME=FromEnv');
        putenv('APP_DEBUG=false');
        putenv('APP_URL=http://example.test');

        $config = require 'includes/config.php';
        assert_equals('FromEnv', $config['APP_NAME']);
        assert_false($config['APP_DEBUG']);
        assert_equals('http://example.test', $config['APP_URL']);
    }
);

run_test(
    'Boolean Cohercion',
    function () {
        putenv('APP_DEBUG=0');
        $config = require 'includes/config.php';
        assert_false($config['APP_DEBUG']);
    }
);

run_test(
    'Dotenv Fallback',
    function () {
        // Clear environment first
        putenv('APP_NAME');
        putenv('APP_URL');

        $env = getcwd() . '/.env';
        $backupEnv = null;
        if (file_exists($env)) {
            $backupEnv = file_get_contents($env);
            unlink($env);
        }

        file_put_contents($env, "APP_NAME=FromDotEnv\nAPP_URL=http://dotenv.test\n");

        // Clear any cached config
        $cache_path = sys_get_temp_dir() . '/maxstack_env_cache.php';
        if (file_exists($cache_path)) unlink($cache_path);

        $config = require 'includes/config.php';
        assert_equals('FromDotEnv', $config['APP_NAME']);
        assert_equals('http://dotenv.test', $config['APP_URL']);

        unlink($env);

        // Restore original .env if it existed
        if ($backupEnv !== null) {
            file_put_contents($env, $backupEnv);
        }
    }
);

run_test(
    'Loader Idempotence',
    function () {
        // Test that config can be reloaded with different values
        // by using a separate process for each load

        // First load
        $cmd1 = "cd " . getcwd() . " && APP_NAME=First php -r \"putenv('APP_NAME=First'); \\\$config = require 'includes/config.php'; echo \\\$config['APP_NAME'];\"";
        exec($cmd1, $out1);

        // Second load with different env
        $cmd2 = "cd " . getcwd() . " && APP_NAME=Second php -r \"putenv('APP_NAME=Second'); \\\$config = require 'includes/config.php'; echo \\\$config['APP_NAME'];\"";
        exec($cmd2, $out2);

        assert_equals('First', $out1[0] ?? '');
        assert_equals('Second', $out2[0] ?? '');
    }
);
