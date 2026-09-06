<?php
/**
 * Automated Deployment Webhook Endpoint
 * Sistem Inventory Adiwiyata
 * 
 * Endpoint ini menerima webhook dari GitHub Actions atau GitHub Webhook
 * untuk secara otomatis menarik kode terbaru (git fetch & reset) ke server.
 * 
 * Keamanan:
 * - Wajib menggunakan DEPLOY_SECRET (via .env atau environment variable)
 * - Mendukung validasi HMAC SHA-256 (Header: X-Hub-Signature-256)
 * - Mendukung token secret (Header: X-Deploy-Secret atau param GET/POST ?secret=...)
 * - Dilengkapi Lock file untuk mencegah concurrent deployment race conditions
 */

header('Content-Type: application/json; charset=UTF-8');

// 1. Muat Environment Variables dari .env jika ada
$envFile = __DIR__ . '/.env';
if (file_exists($envFile) && is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim(trim($val), "\"'");
            if (getenv($key) === false) {
                putenv("{$key}={$val}");
                $_ENV[$key] = $val;
            }
        }
    }
}

$deploySecret = getenv('DEPLOY_SECRET') ?: '';
$deployBranch = getenv('DEPLOY_BRANCH') ?: 'main';
$logFile      = __DIR__ . '/deploy.log';
$lockFile     = sys_get_temp_dir() . '/inventory_deploy.lock';

// Helper response JSON
function send_json_response($status_code, $data) {
    http_response_code($status_code);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// 2. Validasi Konfigurasi Secret
if (empty($deploySecret) || $deploySecret === 'change_this_to_a_secure_random_secret_string_32_chars') {
    send_json_response(500, [
        'status'  => 'error',
        'message' => 'DEPLOY_SECRET belum dikonfigurasi pada server. Buat file .env dan atur DEPLOY_SECRET demi keamanan.'
    ]);
}

// 3. Baca Payload dan Header
$rawPayload  = file_get_contents('php://input');
$headers     = function_exists('getallheaders') ? getallheaders() : [];
$headerLower = [];
foreach ($headers as $k => $v) {
    $headerLower[strtolower($k)] = $v;
}

// 4. Validasi Autentikasi / Signature
$isAuthenticated = false;

// Opsi A: HMAC SHA-256 (GitHub Webhook standard)
$hubSignature = $headerLower['x-hub-signature-256'] ?? $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
if (!empty($hubSignature)) {
    $expectedSignature = 'sha256=' . hash_hmac('sha256', $rawPayload, $deploySecret);
    if (hash_equals($expectedSignature, $hubSignature)) {
        $isAuthenticated = true;
    }
}

// Opsi B: Custom Header X-Deploy-Secret
$customSecretHeader = $headerLower['x-deploy-secret'] ?? $_SERVER['HTTP_X_DEPLOY_SECRET'] ?? '';
if (!empty($customSecretHeader) && hash_equals($deploySecret, $customSecretHeader)) {
    $isAuthenticated = true;
}

// Opsi C: Query Param atau Post Param ?secret=
$paramSecret = $_GET['secret'] ?? $_POST['secret'] ?? '';
if (!empty($paramSecret) && hash_equals($deploySecret, $paramSecret)) {
    $isAuthenticated = true;
}

if (!$isAuthenticated) {
    send_json_response(403, [
        'status'  => 'error',
        'message' => 'Unauthorized: Signature atau secret token tidak valid.'
    ]);
}

// 5. Concurrency Guard (Lock File)
$lockHandle = fopen($lockFile, 'c+');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    send_json_response(429, [
        'status'  => 'error',
        'message' => 'Deployment lain sedang berjalan. Silakan tunggu beberapa saat.'
    ]);
}

// 6. Eksekusi Deployment
@set_time_limit(300);
$startTime = microtime(true);
$logs      = [];
$isSuccess = true;

function run_command_exec($command, &$logs, &$isSuccess) {
    $output = [];
    $returnCode = 0;
    exec($command . ' 2>&1', $output, $returnCode);
    $outputStr = implode("\n", $output);
    $logs[] = [
        'command' => $command,
        'status'  => ($returnCode === 0) ? 'success' : 'failed',
        'code'    => $returnCode,
        'output'  => $outputStr
    ];
    if ($returnCode !== 0) {
        $isSuccess = false;
    }
    return $returnCode === 0;
}

// Jalankan git fetch dan reset
run_command_exec("git fetch origin {$deployBranch}", $logs, $isSuccess);

if ($isSuccess) {
    run_command_exec("git reset --hard origin/{$deployBranch}", $logs, $isSuccess);
}

// Jalankan deploy.sh jika ada dan di lingkungan Unix/Linux
$deployScript = __DIR__ . '/deploy.sh';
if ($isSuccess && DIRECTORY_SEPARATOR === '/' && file_exists($deployScript)) {
    @chmod($deployScript, 0755);
    run_command_exec("bash " . escapeshellarg($deployScript), $logs, $isSuccess);
}

$duration = round(microtime(true) - $startTime, 2);

// Catat ke deploy.log
$logEntry = sprintf(
    "[%s] IP: %s | Status: %s | Duration: %ss\nCommands:\n%s\n----------------------------------------\n",
    date('Y-m-d H:i:s'),
    $_SERVER['REMOTE_ADDR'] ?? 'CLI',
    $isSuccess ? 'SUCCESS' : 'FAILED',
    $duration,
    json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);
@file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

// Lepas lock
flock($lockHandle, LOCK_UN);
fclose($lockHandle);

// 7. Berikan Response
$statusCode = $isSuccess ? 200 : 500;
send_json_response($statusCode, [
    'status'    => $isSuccess ? 'success' : 'failed',
    'message'   => $isSuccess ? 'Deployment berhasil dilakukan.' : 'Deployment mengalami error.',
    'branch'    => $deployBranch,
    'duration'  => $duration . 's',
    'timestamp' => date('c'),
    'execution' => $logs
]);
