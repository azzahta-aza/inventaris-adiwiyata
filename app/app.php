<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/koneksi.php';
include_once __DIR__ . '/controller.php';
