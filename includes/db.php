<?php

/**
 * Database functions for MaxStack
 *
 * Provides SQLite database operations, CRUD functions, and migration management.
 */

/**
 * Gets a PDO database connection
 *
 * @return PDO|null The database connection or null on failure
 */
function db(): ?PDO
{
    global $dbFile;
    try {
        $pdo = new PDO('sqlite:' . $dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (Throwable $e) {
        outlog("DB connection failed: {$e->getMessage()}");
        return null;
    }
}

/**
 * Seeds the database with SQL from a file
 *
 * @param string $file Path to the SQL seed file
 * @return void
 */
function db_seed(string $file): void
{
    $pdo = db();
    if (!$pdo) return;
    $sql = file_get_contents($file);
    $pdo->exec($sql);
}

/**
 * Inserts a new record into the database
 *
 * @param string $table The table name
 * @param array $data Associative array of column => value pairs
 * @return int|null The inserted record ID or null on failure
 */
function db_insert(string $table, array $data): ?int
{
    $pdo = db();
    if (!$pdo) return null;

    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));

    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

    $stmt = $pdo->prepare($sql);
    if ($stmt->execute(array_values($data))) {
        return (int) $pdo->lastInsertId();
    }

    return null;
}

/**
 * Updates records in the database
 *
 * @param string $table The table name
 * @param array $data Associative array of column => value pairs to update
 * @param array $where Associative array of column => value pairs for WHERE clause
 * @return bool True on success, false on failure
 */
function db_update(string $table, array $data, array $where): bool
{
    $pdo = db();
    if (!$pdo) return false;

    $setParts = [];
    $whereParts = [];
    $values = [];

    foreach ($data as $column => $value) {
        $setParts[] = "$column = ?";
        $values[] = $value;
    }

    foreach ($where as $column => $value) {
        $whereParts[] = "$column = ?";
        $values[] = $value;
    }

    $sql = "UPDATE $table SET " . implode(', ', $setParts) . " WHERE " . implode(' AND ', $whereParts);

    $stmt = $pdo->prepare($sql);
    return $stmt->execute($values);
}

/**
 * Deletes records from the database
 *
 * @param string $table The table name
 * @param array $where Associative array of column => value pairs for WHERE clause
 * @return bool True on success, false on failure
 */
function db_delete(string $table, array $where): bool
{
    $pdo = db();
    if (!$pdo) return false;

    $whereParts = [];
    $values = [];

    foreach ($where as $column => $value) {
        $whereParts[] = "$column = ?";
        $values[] = $value;
    }

    $sql = "DELETE FROM $table WHERE " . implode(' AND ', $whereParts);

    $stmt = $pdo->prepare($sql);
    return $stmt->execute($values);
}

/**
 * Selects records from the database
 *
 * @param string $table The table name
 * @param array $where Associative array of column => value pairs for WHERE clause
 * @param string $orderBy ORDER BY clause (optional)
 * @param int $limit Maximum number of records to return (0 for no limit)
 * @return array Array of associative arrays representing the records
 */
function db_select(string $table, array $where = [], string $orderBy = '', int $limit = 0): array
{
    $pdo = db();
    if (!$pdo) return [];

    $whereParts = [];
    $values = [];

    if (!empty($where)) {
        foreach ($where as $column => $value) {
            $whereParts[] = "$column = ?";
            $values[] = $value;
        }
    }

    $sql = "SELECT * FROM $table";
    if (!empty($whereParts)) {
        $sql .= " WHERE " . implode(' AND ', $whereParts);
    }
    if ($orderBy) {
        $sql .= " ORDER BY $orderBy";
    }
    if ($limit > 0) {
        $sql .= " LIMIT $limit";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Selects a single record from the database
 *
 * @param string $table The table name
 * @param array $where Associative array of column => value pairs for WHERE clause
 * @return array|null The record as an associative array or null if not found
 */
function db_select_one(string $table, array $where = []): ?array
{
    $results = db_select($table, $where, '', 1);
    return $results[0] ?? null;
}

/**
 * Runs database migrations from a directory
 *
 * @param string $migrationDir Directory containing migration files
 * @return void
 */
function db_migrate(string $migrationDir): void
{
    $pdo = db();
    if (!$pdo) return;

    // Create migrations table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        migration VARCHAR(255) NOT NULL UNIQUE,
        executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Get executed migrations
    $stmt = $pdo->query("SELECT migration FROM migrations");
    $executed = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Find migration files
    $files = glob("$migrationDir/*.sql");
    sort($files);

    foreach ($files as $file) {
        $migrationName = basename($file);

        if (in_array(pathinfo($migrationName, PATHINFO_FILENAME), $executed)) {
            echo str_pad($migrationName, 40) . " skipped\n";
        } else {
            $sql = file_get_contents($file);
            $pdo->exec($sql);

            // Record migration
            $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
            $stmt->execute([pathinfo($migrationName, PATHINFO_FILENAME)]);

            echo str_pad($migrationName, 40) . " applied\n";
        }
    }
}


/**
 * Creates a new migration file with template content
 *
 * @param string $name Migration name (will be converted to snake_case)
 * @param string $migrationDir Directory to create the migration in
 * @return string The path to the created migration file
 */
function db_create_migration(string $name, string $migrationDir): string
{
    $timestamp = date('Y_m_d_H_i_s');
    $filename = "{$timestamp}_{$name}.sql";
    $filepath = "$migrationDir/$filename";

    $template = "-- Migration: $name
-- Created: " . date('Y-m-d H:i:s') . "
-- Write your SQL statements below this line
";

    file_put_contents($filepath, $template);
    return $filepath;
}
