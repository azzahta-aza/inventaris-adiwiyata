<?php
// Memuat file konfigurasi/app utama jika diperlukan
include_once __DIR__ . '/app/app.php';

// Langsung arahkan (redirect) ke halaman login saat pertama kali web dibuka
header('Location: views/auth/login.php');
exit();
?>