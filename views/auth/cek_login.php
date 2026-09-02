<?php

include_once '../../app/app.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = login($_POST);
    if ($res === 1) {
        header('Location: ../barang/index.php');
        exit;
    } elseif ($res === -1) {
        echo "<script>alert('Maaf, Login Gagal, Password anda tidak sesuai!'); document.location='login.php';</script>";
        exit;
    } else {
        echo "<script>alert('Maaf, Login Gagal, Username atau Role tidak sesuai!'); document.location='login.php';</script>";
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
