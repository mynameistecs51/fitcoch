<?php

declare(strict_types=1);

/**
 * Temporary deploy diagnostics — delete after go-live.
 * Open: /~fitcoch/public/check-deploy.php
 */

header('Content-Type: text/plain; charset=UTF-8');

$root = dirname(__DIR__);
$checks = [];

$checks[] = 'PHP version: ' . PHP_VERSION . (version_compare(PHP_VERSION, '8.1.0', '>=') ? ' OK' : ' FAIL (need >= 8.1)');

$envPath = $root . '/.env';
$checks[] = '.env file: ' . (is_file($envPath) ? 'found' : 'MISSING — upload .env to project root');

$vendorPath = $root . '/vendor/autoload.php';
$checks[] = 'vendor/autoload.php: ' . (is_file($vendorPath) ? 'found' : 'MISSING — run composer install or upload vendor/');

$storageLogs = $root . '/storage/logs';
$checks[] = 'storage/logs writable: ' . (is_dir($storageLogs) && is_writable($storageLogs) ? 'yes' : 'no — chmod 755 or 775');

if (is_file($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}

$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
$dbName = $_ENV['DB_DATABASE'] ?? '';
$dbUser = $_ENV['DB_USERNAME'] ?? '';
$dbPass = $_ENV['DB_PASSWORD'] ?? '';

if ($dbName === '' || $dbUser === '') {
    $checks[] = 'Database config: incomplete in .env';
} else {
    try {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $dbHost, $dbName);
        new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $checks[] = 'Database connection: OK';
    } catch (Throwable $e) {
        $checks[] = 'Database connection: FAIL — ' . $e->getMessage();
        $checks[] = 'Hint: import database/migrations/all.sql in phpMyAdmin';
    }
}

$checks[] = 'APP_URL (if set): ' . ($_ENV['APP_URL'] ?? '(not set)');

echo "FitCoch deploy check\n";
echo str_repeat('=', 40) . "\n";
foreach ($checks as $line) {
    echo $line . "\n";
}
echo str_repeat('=', 40) . "\n";
echo "Delete this file after fixing issues.\n";
