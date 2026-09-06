<?php
/**
 * Database Seeder Script - Sistem Inventory Adiwiyata
 * 
 * Dapat dijalankan melalui CLI:
 *   php seed.php
 * Atau via browser:
 *   http://localhost/inventory_test/seed.php
 */

require_once __DIR__ . '/app/koneksi.php';

$is_cli = (php_sapi_name() === 'cli');

function log_msg($msg, $is_cli) {
    if ($is_cli) {
        echo strip_tags($msg) . PHP_EOL;
    } else {
        echo "<p style='margin: 4px 0; font-family: monospace;'>{$msg}</p>";
    }
}

if (!$is_cli) {
    echo "<!DOCTYPE html><html><head><title>Database Seeder</title>";
    echo "<style>body{font-family:sans-serif;padding:30px;background:#f8fafc;color:#1e293b;} .card{background:#fff;padding:24px;border-radius:10px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);max-width:600px;margin:auto;}</style></head><body><div class='card'>";
    echo "<h2 style='margin-top:0;color:#0f172a;'>🌱 Database Seeder - Inventory Adiwiyata</h2><hr>";
} else {
    echo "=================================================\n";
    echo "  🌱 DATABASE SEEDER - INVENTORY ADIWIYATA       \n";
    echo "=================================================\n";
}

// 1. SEED DATA USERS
log_msg("<strong>[1/4] Seeding Akun Pengguna (Users)...</strong>", $is_cli);
$default_users = [
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

foreach ($default_users as $u) {
    $nama          = mysqli_real_escape_string($db, $u['nama']);
    $username      = mysqli_real_escape_string($db, $u['username']);
    $role          = mysqli_real_escape_string($db, $u['role']);
    $password_hash = password_hash($u['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($db, "SELECT id_user FROM users WHERE username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        $update = "UPDATE users SET nama = '$nama', password = '$password_hash', role = '$role' WHERE username = '$username'";
        mysqli_query($db, $update);
        log_msg("  ✔ Akun <strong>{$username}</strong> ({$role}) berhasil diperbarui.", $is_cli);
    } else {
        $insert = "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$password_hash', '$role')";
        mysqli_query($db, $insert);
        log_msg("  ✔ Akun baru <strong>{$username}</strong> ({$role}) berhasil dibuat.", $is_cli);
    }
}

// 2. SEED KATEGORI CONTOH (Jika kosong)
log_msg("\n<strong>[2/4] Memeriksa Master Kategori...</strong>", $is_cli);
$check_kat = mysqli_query($db, "SELECT COUNT(*) as total FROM kategori");
$total_kat = mysqli_fetch_assoc($check_kat)['total'] ?? 0;

if ($total_kat == 0) {
    $sample_kategori = ['Elektronik & IT', 'Alat Tulis Kantor', 'Peralatan Medis & P3K', 'Sarana Olahraga', 'Kebersihan'];
    foreach ($sample_kategori as $kat) {
        $kat_esc = mysqli_real_escape_string($db, $kat);
        mysqli_query($db, "INSERT INTO kategori (nama_kategori) VALUES ('$kat_esc')");
    }
    log_msg("  ✔ Menambahkan " . count($sample_kategori) . " kategori contoh.", $is_cli);
} else {
    log_msg("  ℹ Data kategori sudah ada ({$total_kat} kategori). Lewati.", $is_cli);
}

// 3. SEED LOKASI CONTOH (Jika kosong)
log_msg("\n<strong>[3/4] Memeriksa Master Lokasi Ruangan...</strong>", $is_cli);
$check_lok = mysqli_query($db, "SELECT COUNT(*) as total FROM lokasi");
$total_lok = mysqli_fetch_assoc($check_lok)['total'] ?? 0;

if ($total_lok == 0) {
    $sample_lokasi = ['Gudang Utama Lt. 1', 'Lab Komputer 1', 'Ruang Guru & Tata Usaha', 'Ruang UKS', 'Perpustakaan'];
    foreach ($sample_lokasi as $lok) {
        $lok_esc = mysqli_real_escape_string($db, $lok);
        mysqli_query($db, "INSERT INTO lokasi (nama_lokasi) VALUES ('$lok_esc')");
    }
    log_msg("  ✔ Menambahkan " . count($sample_lokasi) . " lokasi ruangan contoh.", $is_cli);
} else {
    log_msg("  ℹ Data lokasi sudah ada ({$total_lok} lokasi). Lewati.", $is_cli);
}

// 4. SEED SAMPLE BARANG (Jika kosong)
log_msg("\n<strong>[4/4] Memeriksa Master Barang...</strong>", $is_cli);
$check_brg = mysqli_query($db, "SELECT COUNT(*) as total FROM barang");
$total_brg = mysqli_fetch_assoc($check_brg)['total'] ?? 0;

if ($total_brg == 0) {
    $first_kat = mysqli_fetch_assoc(mysqli_query($db, "SELECT id_kategori FROM kategori LIMIT 1"))['id_kategori'] ?? 1;
    $first_lok = mysqli_fetch_assoc(mysqli_query($db, "SELECT id_lokasi FROM lokasi LIMIT 1"))['id_lokasi'] ?? 1;

    $sample_barang = [
        ['kode' => 'BRG-001', 'nama' => 'Laptop Asus Core i5', 'stok' => 15, 'satuan' => 'Unit', 'kondisi' => 'layak', 'ket' => 'Inventaris Lab Komputer'],
        ['kode' => 'BRG-002', 'nama' => 'Proyektor Epson EB-X400', 'stok' => 4, 'satuan' => 'Unit', 'kondisi' => 'layak', 'ket' => 'Inventaris Multimedia'],
        ['kode' => 'BRG-003', 'nama' => 'Kabel Roll 15 Meter', 'stok' => 2, 'satuan' => 'Pcs', 'kondisi' => 'perbaikan', 'ket' => 'Kabel gulung ruang rapat'],
        ['kode' => 'BRG-004', 'nama' => 'Printer Canon IP2770', 'stok' => 1, 'satuan' => 'Unit', 'kondisi' => 'tidak_layak', 'ket' => 'Head buntu']
    ];

    foreach ($sample_barang as $sb) {
        $k = mysqli_real_escape_string($db, $sb['kode']);
        $n = mysqli_real_escape_string($db, $sb['nama']);
        $s = (int)$sb['stok'];
        $u = mysqli_real_escape_string($db, $sb['satuan']);
        $c = mysqli_real_escape_string($db, $sb['kondisi']);
        $e = mysqli_real_escape_string($db, $sb['ket']);
        mysqli_query($db, "INSERT INTO barang (id_kategori, id_lokasi, kode_barang, nama_barang, stok, satuan, kondisi, keterangan) VALUES ($first_kat, $first_lok, '$k', '$n', $s, '$u', '$c', '$e')");
    }
    log_msg("  ✔ Menambahkan " . count($sample_barang) . " barang contoh.", $is_cli);
} else {
    log_msg("  ℹ Data barang sudah ada ({$total_brg} barang). Lewati.", $is_cli);
}

log_msg("\n<span style='color:green;'>🎉 <strong>Seeding database selesai dengan sukses!</strong></span>", $is_cli);
log_msg("Kredensial Default: Password seluruh akun adalah: <code>admin123</code>", $is_cli);

if (!$is_cli) {
    echo "<br><a href='views/auth/login.php' style='display:inline-block;padding:10px 18px;background:#2563eb;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;'>Menuju Halaman Login &rarr;</a>";
    echo "</div></body></html>";
}
