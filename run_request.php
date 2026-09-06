<?php

// Runner script untuk simulasi request HTTP ke file view
$file = $argv[1] ?? '';
$role = $argv[2] ?? '';

if (empty($file) || !file_exists(__DIR__ . '/' . $file)) {
    echo "HTTP_CODE:404";
    exit(1);
}

register_shutdown_function(function () {
    echo "\nHTTP_CODE:" . http_response_code() . "\n";
});

if (!empty($role)) {
    session_start();
    $_SESSION['login']   = true;
    $_SESSION['id_user'] = ($role === 'Admin') ? 123 : (($role === 'Petugas') ? 124 : 125);
    $_SESSION['nama']    = $role . ' User';
    $_SESSION['username']= strtolower($role);
    $_SESSION['role']    = $role;
}

$_SERVER['SCRIPT_NAME'] = '/inventory_test/' . $file;
$_SERVER['PHP_SELF']    = '/inventory_test/' . $file;

chdir(dirname(__DIR__ . '/' . $file));
ob_start();
try {
    include basename($file);
} catch (Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage();
}
