<?php

include_once __DIR__ . '/app/app.php';

if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    $destination = get_landing_url('views');
    header("Location: $destination");
} else {
    header('Location: views/auth/login.php');
}
exit;
