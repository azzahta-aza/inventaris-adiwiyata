<?php

// Zona waktu Indonesia (WIB). Sesuaikan dengan zona waktu server jika perlu.
date_default_timezone_set('Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include_once __DIR__ . '/koneksi.php';
include_once __DIR__ . '/controller.php';
