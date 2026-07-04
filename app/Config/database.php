<?php
/**
 * Database connection configuration.
 */

require_once __DIR__ . '/constants.php';

$host = (string) env('DB_HOST', 'localhost');
$port = (string) env('DB_PORT', '3306');
$database = (string) env('DB_NAME', 'turbohostmw');
$charset = (string) env('DB_CHARSET', 'utf8mb4');

return [
    'driver' => 'mysql',
    'host' => $host,
    'port' => $port,
    'database' => $database,
    'username' => (string) env('DB_USER', ''),
    'password' => (string) env('DB_PASS', ''),
    'charset' => $charset,
    'dsn' => sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $host,
        $port,
        $database,
        $charset
    ),
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
