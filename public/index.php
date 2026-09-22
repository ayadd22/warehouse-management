<?php

declare(strict_types=1);



require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\DatabaseConnection;

$config = require __DIR__ . '/../config/database.php';

try {
    $db  = new DatabaseConnection(
        host:     $config['host'],
        port:     $config['port'],
        database: $config['database'],
        username: $config['username'],
        password: $config['password'],
        charset:  $config['charset'],
    );

    $pdo = $db->getConnection();

    echo 'Warehouse Management System — database connection OK.' . PHP_EOL;
    echo 'PDO driver : ' . $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) . PHP_EOL;
    echo 'Run tests  : php tests/run_tests.php' . PHP_EOL;

} catch (\RuntimeException $e) {
    // Safe message — no credentials or DSN details are exposed.
    echo 'Database connection failed.' . PHP_EOL;
    echo 'Reason: ' . $e->getMessage() . PHP_EOL;
    echo 'Check that environment variables are set (see .env.example).' . PHP_EOL;
}
