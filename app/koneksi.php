<?php

// Auto-load .env file if it exists in the project root
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath) && is_readable($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($envKey, $envVal) = explode('=', $line, 2);
            $envKey = trim($envKey);
            $envVal = trim(trim($envVal), "\"'");
            if (getenv($envKey) === false) {
                putenv("{$envKey}={$envVal}");
                $_ENV[$envKey] = $envVal;
                $_SERVER[$envKey] = $envVal;
            }
        }
    }
}

$db_host = getenv('DB_HOST') ?: '127.0.0.1';
$db_port = (int)(getenv('DB_PORT') ?: 3306);
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'inventory';

$db = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

if (!$db) {
    error_log('[UKKinventory] DB connection failed: ' . mysqli_connect_error());
    http_response_code(503);
    die("Sistem sedang dalam perbaikan. Silakan coba beberapa saat lagi.");
}
