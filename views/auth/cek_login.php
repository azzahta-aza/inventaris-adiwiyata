<?php

include_once '../../app/app.php';

// File ini merupakan endpoint legacy/warisan.
// Seluruh proses autentikasi resmi ditangani langsung secara aman di login.php (dilengkapi CSRF & prepared statement).
// Mengalihkan secara aman ke login.php untuk mencegah potensi celah bypass login.
header('Location: login.php');
exit;
