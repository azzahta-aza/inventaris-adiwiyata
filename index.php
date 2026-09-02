<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header('Location: views/barang/index.php');
} else {
    header('Location: views/auth/login.php');
}
exit;
