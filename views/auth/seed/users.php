<?php
require_once __DIR__ . '/../../../app/koneksi.php';

// Data akun seed yang akan dibuat (Default password: admin123)
$seed_users = [
    [
        'nama'     => 'Administrator',
        'username' => 'admin',
        'password' => 'admin123',
        'role'     => 'Admin'
    ],
    [
        'nama'     => 'Petugas Gudang',
        'username' => 'petugas',
        'password' => 'admin123',
        'role'     => 'Petugas'
    ],
    [
        'nama'     => 'Kepala Bagian',
        'username' => 'kepala',
        'password' => 'admin123',
        'role'     => 'Kepala'
    ]
];

echo "<h3>Seeding Users...</h3>";

foreach ($seed_users as $user) {
    $nama     = mysqli_real_escape_string($db, $user['nama']);
    $username = mysqli_real_escape_string($db, $user['username']);
    $password_hash = password_hash($user['password'], PASSWORD_DEFAULT);
    $role     = mysqli_real_escape_string($db, $user['role']);

    // Cek apakah username sudah ada
    $cek_user = mysqli_query($db, "SELECT id_user FROM users WHERE username = '$username'");

    if (mysqli_num_rows($cek_user) > 0) {
        // Update password & role jika sudah ada
        $update_query = "UPDATE users SET nama = '$nama', password = '$password_hash', role = '$role' WHERE username = '$username'";
        if (mysqli_query($db, $update_query)) {
            echo "Sukses update akun: <strong>$username</strong> (Role: $role)<br>";
        } else {
            echo "Gagal update akun $username: " . mysqli_error($db) . "<br>";
        }
    } else {
        // Query insert data
        $insert_query = "INSERT INTO users (nama, username, password, role) 
                         VALUES ('$nama', '$username', '$password_hash', '$role')";

        if (mysqli_query($db, $insert_query)) {
            echo "Sukses tambah akun: <strong>$username</strong> (Role: $role)<br>";
        } else {
            echo "Gagal membuat akun $username: " . mysqli_error($db) . "<br>";
        }
    }
}

// Tutup koneksi
mysqli_close($db);
echo "<p>Seeding selesai!</p>";
