<?php
declare(strict_types=1);

/**
 * Database connection helper.
 * Returns a singleton PDO instance connected to the SQLite database.
 */
function get_db(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dbPath = __DIR__ . '/olives_lemon.db';
    $isNew  = !file_exists($dbPath);

    $pdo = new PDO('sqlite:' . $dbPath, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA foreign_keys = ON');

    if ($isNew) {
        $schema = file_get_contents(__DIR__ . '/schema.sql');
        $pdo->exec($schema);

        // Auto-seed on first run if migrate.php exists
        $migratePath = __DIR__ . '/migrate.php';
        if (file_exists($migratePath)) {
            // Run migrate in a subprocess to seed data
            $php = PHP_BINARY ?: 'php';
            exec($php . ' ' . escapeshellarg($migratePath) . ' 2>&1', $output, $code);
        }
    }

    return $pdo;
}
