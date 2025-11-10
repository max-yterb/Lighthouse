<?php
require_once __DIR__ . '/bootstrap.php';


// ----------------------------
// DATABASE TESTS
// ----------------------------

run_test('DB connection', function () {
    $pdo = db();
    assert_not_null($pdo, 'Database connection should be established');
    assert_instance_of(PDO::class, $pdo, 'Should return PDO instance');
});


// ----------------------------
// SETUP TEST TABLES
// ----------------------------

$pdo = db();
$pdo->exec("DROP TABLE IF EXISTS test_users");
$pdo->exec("CREATE TABLE test_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$pdo->exec("DROP TABLE IF EXISTS migrations");
$pdo->exec("CREATE TABLE migrations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    migration VARCHAR(255) NOT NULL UNIQUE,
    executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");


// ----------------------------
// DB FUNCTIONALITY TESTS
// ----------------------------

run_test('DB insert', function () {
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS test_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $data = ['name' => 'John Doe', 'email' => 'john@example.com'];
    $id = db_insert('test_users', $data);

    assert_not_null($id, 'Insert should return an ID');
    assert_is_int($id, 'ID should be an integer');

    $record = db_select_one('test_users', ['id' => $id]);
    assert_not_null($record, 'Record should exist');
    assert_equals('John Doe', $record['name'], 'Name should match');
    assert_equals('john@example.com', $record['email'], 'Email should match');
});

run_test('DB update', function () {
    $id = db_insert('test_users', ['name' => 'Jane Doe', 'email' => 'jane@example.com']);
    $updateData = ['name' => 'Jane Smith'];
    $where = ['id' => $id];

    $result = db_update('test_users', $updateData, $where);
    assert_true($result, 'Update should succeed');

    $record = db_select_one('test_users', ['id' => $id]);
    assert_equals('Jane Smith', $record['name'], 'Name should be updated');
    assert_equals('jane@example.com', $record['email'], 'Email should remain unchanged');
});

run_test('DB delete', function () {
    $id = db_insert('test_users', ['name' => 'Bob Wilson', 'email' => 'bob@example.com']);
    $result = db_delete('test_users', ['id' => $id]);
    assert_true($result, 'Delete should succeed');

    $record = db_select_one('test_users', ['id' => $id]);
    assert_null($record, 'Record should be deleted');
});

run_test('DB select', function () {
    $pdo = db();
    $pdo->exec("DELETE FROM test_users");

    db_insert('test_users', ['name' => 'Alice', 'email' => 'alice@example.com']);
    db_insert('test_users', ['name' => 'Bob', 'email' => 'bob@example.com']);
    db_insert('test_users', ['name' => 'Charlie', 'email' => 'charlie@example.com']);

    $allRecords = db_select('test_users');
    assert_count(3, $allRecords, 'Should return all records');

    $aliceRecords = db_select('test_users', ['name' => 'Alice']);
    assert_count(1, $aliceRecords, 'Should return one record');
    assert_equals('Alice', $aliceRecords[0]['name'], 'Should return correct record');

    $limitedRecords = db_select('test_users', [], 'name ASC', 2);
    assert_count(2, $limitedRecords, 'Should respect limit');
    assert_equals('Alice', $limitedRecords[0]['name'], 'Should be ordered correctly');
});

run_test('DB select_one', function () {
    $id = db_insert('test_users', ['name' => 'David', 'email' => 'david@example.com']);

    $record = db_select_one('test_users', ['id' => $id]);
    assert_not_null($record, 'Should return a record');
    assert_equals('David', $record['name'], 'Should return correct record');

    $nonExistent = db_select_one('test_users', ['id' => 99999]);
    assert_null($nonExistent, 'Should return null for non-existent record');
});

run_test('DB create migration', function () {
    $migrationDir = __DIR__ . '/../database/migrations';
    $filename = db_create_migration('test_migration', $migrationDir);

    assert_string_contains($filename, 'test_migration', 'Filename should contain migration name');
    assert_string_ends_with('.sql', $filename, 'Should create .sql file');
    assert_file_exists($filename, 'Migration file should be created');

    $content = file_get_contents($filename);
    assert_string_contains($content, '-- Migration: test_migration', 'Should contain migration header');

    unlink($filename);
});

run_test('Migration tracking', function () {
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        migration VARCHAR(255) NOT NULL UNIQUE,
        executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='migrations'");
    $result = $stmt->fetch();
    assert_not_null($result, 'Migrations table should exist');

    $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
    $stmt->execute(['test_migration_123']);

    $stmt = $pdo->query("SELECT migration FROM migrations WHERE migration = 'test_migration_123'");
    $result = $stmt->fetch();
    assert_not_null($result, 'Migration should be tracked');
});

run_test('db_migrate applies migrations', function () {
    $migrationDir = __DIR__ . '/tmp_migrations';
    if (!is_dir($migrationDir)) mkdir($migrationDir, 0777, true);

    $file1 = "$migrationDir/2025_11_10_test1.sql";
    file_put_contents($file1, "CREATE TABLE IF NOT EXISTS test1 (id INTEGER PRIMARY KEY);");

    $file2 = "$migrationDir/2025_11_10_test2.sql";
    file_put_contents($file2, "CREATE TABLE IF NOT EXISTS test2 (id INTEGER PRIMARY KEY);");

    db_migrate($migrationDir);

    $tables = db_select("sqlite_master", ['type' => 'table']);
    assert_true(in_array('test1', array_column($tables, 'name')), 'test1 table should exist');
    assert_true(in_array('test2', array_column($tables, 'name')), 'test2 table should exist');

    $applied = array_column(db_select('migrations'), 'migration');
    assert_true(in_array('2025_11_10_test1', $applied), 'test1 migration should be applied');
    assert_true(in_array('2025_11_10_test2', $applied), 'test2 migration should be applied');

    // Clean up
    unlink($file1);
    unlink($file2);
    rmdir($migrationDir);
});
