<?php
require_once __DIR__ . '../../../../app/koneksi.php';

// Data akun seed yang akan dibuat
$nama = 'Admin';
$username = 'admin';
$password = 'admin123'; // Kata sandi asli
$role     = 'admin';

// Enkripsi password secara aman
$password_terenkripsi = password_hash($password, PASSWORD_BCRYPT);

// Cek apakah username sudah ada agar tidak duplikat
$cek_user = mysqli_query($db, "SELECT id_user FROM users WHERE username = '$username'");

if (mysqli_num_rows($cek_user) > 0) {
    echo "Gagal: Akun seed dengan username '$username' sudah ada!";
} else {
    // Query insert data
    $query = "INSERT INTO users (nama, username, password, role) 
              VALUES ('$nama','$username', '$password', '$role')";

    if (mysqli_query($db, $query)) {
        echo "Sukses: Akun seed berhasil dibuat!<br>";
        echo "Username: " . $username . "<br>";
        echo "Password: " . $password;
    } else {
        echo "Gagal membuat akun: " . mysqli_error($db);
    }
}

// Tutup koneksi
mysqli_close($db);
