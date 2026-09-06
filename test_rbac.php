<?php

// Test Suite RBAC Sistem Inventory
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

$tests_run = 0;
$tests_passed = 0;
$tests_failed = 0;

function assert_test($description, $condition) {
    global $tests_run, $tests_passed, $tests_failed;
    $tests_run++;
    if ($condition) {
        $tests_passed++;
        echo "[PASS] $description\n";
    } else {
        $tests_failed++;
        echo "[FAIL] $description\n";
    }
}

// 1. Uji Login Function
require_once __DIR__ . '/app/app.php';

echo "=== 1. TEST AUTHENTICATION & LOGIN ===\n";
$login_admin = login(['username' => 'admin', 'password' => 'admin123']);
assert_test("Login admin berhasil (1)", $login_admin === 1);
assert_test("Session role admin tercatat", strtolower($_SESSION['role']) === 'admin');

$login_petugas = login(['username' => 'petugas', 'password' => 'admin123']);
assert_test("Login petugas berhasil (1)", $login_petugas === 1);
assert_test("Session role petugas tercatat", strtolower($_SESSION['role']) === 'petugas');

$login_kepala = login(['username' => 'kepala', 'password' => 'admin123']);
assert_test("Login kepala berhasil (1)", $login_kepala === 1);
assert_test("Session role kepala tercatat", strtolower($_SESSION['role']) === 'kepala');

$login_invalid = login(['username' => 'petugas', 'password' => 'wrongpass']);
assert_test("Login password salah ditolak (-1)", $login_invalid === -1);

$login_unknown = login(['username' => 'nonexistent', 'password' => 'test']);
assert_test("Login username tidak ada ditolak (0)", $login_unknown === 0);


echo "\n=== 2. TEST HELPER RBAC & ACCESS MATRIX ===\n";
// Sesi Petugas
$_SESSION['login'] = true;
$_SESSION['role']  = 'Petugas';
assert_test("has_role('petugas') untuk Petugas", has_role('petugas') === true);
assert_test("has_role(['admin', 'petugas']) untuk Petugas", has_role(['admin', 'petugas']) === true);
assert_test("has_role('admin') untuk Petugas (harus FALSE)", has_role('admin') === false);
assert_test("has_role('kepala') untuk Petugas (harus FALSE)", has_role('kepala') === false);
assert_test("Landing URL Petugas", get_landing_url('..') === '../transaksi/index.php');

// Sesi Kepala
$_SESSION['role']  = 'Kepala';
assert_test("has_role('kepala') untuk Kepala", has_role('kepala') === true);
assert_test("has_role(['admin', 'kepala']) untuk Kepala", has_role(['admin', 'kepala']) === true);
assert_test("has_role('admin') untuk Kepala (harus FALSE)", has_role('admin') === false);
assert_test("has_role('petugas') untuk Kepala (harus FALSE)", has_role('petugas') === false);
assert_test("Landing URL Kepala", get_landing_url('..') === '../laporan/barang.php');

// Sesi Admin
$_SESSION['role']  = 'Admin';
assert_test("has_role('admin') untuk Admin", has_role('admin') === true);
assert_test("has_role(['admin', 'petugas']) untuk Admin", has_role(['admin', 'petugas']) === true);
assert_test("has_role(['admin', 'kepala']) untuk Admin", has_role(['admin', 'kepala']) === true);
assert_test("Landing URL Admin", get_landing_url('..') === '../barang/index.php');

// Sesi Tidak Dikenal (Unknown Role)
$_SESSION['role']  = 'Hacker';
assert_test("has_role('admin') untuk Unknown (Deny by default)", has_role('admin') === false);
assert_test("has_role('petugas') untuk Unknown (Deny by default)", has_role('petugas') === false);
assert_test("has_role('kepala') untuk Unknown (Deny by default)", has_role('kepala') === false);


echo "\n=== 3. TEST BACKEND / CONTROLLER DEFENSE-IN-DEPTH ===\n";
// Uji aksi Admin saat login sebagai Petugas
$_SESSION['role'] = 'Petugas';
assert_test("Petugas panggil create_barang ditolak (return 0)", create_barang(['id_kategori' => 1, 'id_lokasi' => 1, 'kode_barang' => 'T1', 'nama_barang' => 'T', 'stok' => 1, 'satuan' => 'U', 'kondisi' => 'layak', 'keterangan' => '']) === 0);
assert_test("Petugas panggil create_kategori ditolak (return 0)", create_kategori(['nama' => 'Test']) === 0);
assert_test("Petugas panggil create_lokasi ditolak (return 0)", create_lokasi(['nama' => 'Test']) === 0);
assert_test("Petugas panggil create_user ditolak (return 0)", create_user(['nama' => 'Test', 'username' => 't', 'password' => 't', 'role' => 'Petugas']) === 0);
assert_test("Petugas panggil select_laporan ditolak (return [])", select_laporan("SELECT * FROM barang") === []);

// Uji aksi saat login sebagai Kepala
$_SESSION['role'] = 'Kepala';
assert_test("Kepala panggil create_barang ditolak (return 0)", create_barang(['id_kategori' => 1, 'id_lokasi' => 1, 'kode_barang' => 'T2', 'nama_barang' => 'T', 'stok' => 1, 'satuan' => 'U', 'kondisi' => 'layak', 'keterangan' => '']) === 0);
assert_test("Kepala panggil create_user ditolak (return 0)", create_user(['nama' => 'Test', 'username' => 't', 'password' => 't', 'role' => 'Petugas']) === 0);
assert_test("Kepala panggil create_transaksi ditolak (return 0)", create_transaksi(['id_barang' => 1, 'tanggal' => '2026-01-01', 'jenis_transaksi' => 'Masuk', 'jumlah' => 1, 'keterangan' => '']) === 0);
assert_test("Kepala panggil create_peminjaman ditolak (return 0)", create_peminjaman(['id_barang' => 1, 'nama_peminjam' => 'X', 'tanggal_pinjam' => '2026-01-01', 'jumlah' => 1]) === 0);


echo "\n=== 4. TEST DIRECT URL & PAGE GUARDS SIMULATION ===\n";
function simulate_page_access($file_path, $role) {
    $cmd = "php run_request.php \"$file_path\" \"$role\"";
    $output = shell_exec($cmd);
    preg_match('/HTTP_CODE:(\d+)/', $output, $matches);
    $code = isset($matches[1]) ? (int)$matches[1] : 200;
    return ['code' => $code, 'output' => $output];
}

// 4.1 Guest (Belum Login)
$guest = simulate_page_access("views/barang/index.php", "");
assert_test("Guest akses views/barang/index.php diarahkan ke login (302)", $guest['code'] === 302);

// 4.2 Petugas
$pet_trans = simulate_page_access("views/transaksi/index.php", "Petugas");
assert_test("Petugas akses views/transaksi/index.php diizinkan (200)", $pet_trans['code'] === 200);

$pet_pinjam = simulate_page_access("views/peminjaman/index.php", "Petugas");
assert_test("Petugas akses views/peminjaman/index.php diizinkan (200)", $pet_pinjam['code'] === 200);

$pet_barang = simulate_page_access("views/barang/index.php", "Petugas");
assert_test("Petugas akses views/barang/index.php DITOLAK (403)", $pet_barang['code'] === 403);

$pet_kategori = simulate_page_access("views/kategori/index.php", "Petugas");
assert_test("Petugas akses views/kategori/index.php DITOLAK (403)", $pet_kategori['code'] === 403);

$pet_lokasi = simulate_page_access("views/lokasi/index.php", "Petugas");
assert_test("Petugas akses views/lokasi/index.php DITOLAK (403)", $pet_lokasi['code'] === 403);

$pet_users = simulate_page_access("views/users/index.php", "Petugas");
assert_test("Petugas akses views/users/index.php DITOLAK (403)", $pet_users['code'] === 403);

$pet_lap = simulate_page_access("views/laporan/barang.php", "Petugas");
assert_test("Petugas akses views/laporan/barang.php DITOLAK (403)", $pet_lap['code'] === 403);

$pet_pdf = simulate_page_access("views/laporan/cetakpdf.php", "Petugas");
assert_test("Petugas akses views/laporan/cetakpdf.php DITOLAK (403)", $pet_pdf['code'] === 403);

$pet_del_brg = simulate_page_access("views/barang/hapus.php", "Petugas");
assert_test("Petugas direct action views/barang/hapus.php DITOLAK (403)", $pet_del_brg['code'] === 403);

$pet_del_usr = simulate_page_access("views/users/hapus.php", "Petugas");
assert_test("Petugas direct action views/users/hapus.php DITOLAK (403)", $pet_del_usr['code'] === 403);

// 4.3 Kepala
$kep_lap1 = simulate_page_access("views/laporan/barang.php", "Kepala");
assert_test("Kepala akses views/laporan/barang.php diizinkan (200)", $kep_lap1['code'] === 200);

$kep_lap2 = simulate_page_access("views/laporan/peminjaman.php", "Kepala");
assert_test("Kepala akses views/laporan/peminjaman.php diizinkan (200)", $kep_lap2['code'] === 200);

$kep_lap3 = simulate_page_access("views/laporan/transaksi.php", "Kepala");
assert_test("Kepala akses views/laporan/transaksi.php diizinkan (200)", $kep_lap3['code'] === 200);

$kep_lap4 = simulate_page_access("views/laporan/kategori.php", "Kepala");
assert_test("Kepala akses views/laporan/kategori.php diizinkan (200)", $kep_lap4['code'] === 200);

$kep_lap5 = simulate_page_access("views/laporan/lokasi.php", "Kepala");
assert_test("Kepala akses views/laporan/lokasi.php diizinkan (200)", $kep_lap5['code'] === 200);

$kep_pdf = simulate_page_access("views/laporan/cetakpdf.php", "Kepala");
assert_test("Kepala akses views/laporan/cetakpdf.php diizinkan (200)", $kep_pdf['code'] === 200);

$kep_brg = simulate_page_access("views/barang/index.php", "Kepala");
assert_test("Kepala akses views/barang/index.php DITOLAK (403)", $kep_brg['code'] === 403);

$kep_usr = simulate_page_access("views/users/index.php", "Kepala");
assert_test("Kepala akses views/users/index.php DITOLAK (403)", $kep_usr['code'] === 403);

$kep_trans = simulate_page_access("views/transaksi/index.php", "Kepala");
assert_test("Kepala akses views/transaksi/index.php DITOLAK (403)", $kep_trans['code'] === 403);

$kep_pinjam = simulate_page_access("views/peminjaman/index.php", "Kepala");
assert_test("Kepala akses views/peminjaman/index.php DITOLAK (403)", $kep_pinjam['code'] === 403);

// 4.4 Admin
$adm_brg = simulate_page_access("views/barang/index.php", "Admin");
assert_test("Admin akses views/barang/index.php diizinkan (200)", $adm_brg['code'] === 200);

$adm_kat = simulate_page_access("views/kategori/index.php", "Admin");
assert_test("Admin akses views/kategori/index.php diizinkan (200)", $adm_kat['code'] === 200);

$adm_lok = simulate_page_access("views/lokasi/index.php", "Admin");
assert_test("Admin akses views/lokasi/index.php diizinkan (200)", $adm_lok['code'] === 200);

$adm_usr = simulate_page_access("views/users/index.php", "Admin");
assert_test("Admin akses views/users/index.php diizinkan (200)", $adm_usr['code'] === 200);

$adm_trans = simulate_page_access("views/transaksi/index.php", "Admin");
assert_test("Admin akses views/transaksi/index.php diizinkan (200)", $adm_trans['code'] === 200);

$adm_pinjam = simulate_page_access("views/peminjaman/index.php", "Admin");
assert_test("Admin akses views/peminjaman/index.php diizinkan (200)", $adm_pinjam['code'] === 200);

$adm_lap = simulate_page_access("views/laporan/barang.php", "Admin");
assert_test("Admin akses views/laporan/barang.php diizinkan (200)", $adm_lap['code'] === 200);

$adm_pdf = simulate_page_access("views/laporan/cetakpdf.php", "Admin");
assert_test("Admin akses views/laporan/cetakpdf.php diizinkan (200)", $adm_pdf['code'] === 200);


echo "\n=== 5. TEST NAVBAR MENU FILTERING HTML ===\n";
function get_navbar_html($role) {
    return shell_exec("php run_request.php layout/header.php \"$role\"");
}

$nav_petugas = get_navbar_html('Petugas');
assert_test("Navbar Petugas menampilkan Peminjaman", strpos($nav_petugas, 'Peminjaman') !== false);
assert_test("Navbar Petugas menampilkan Transaksi", strpos($nav_petugas, 'Transaksi') !== false);
assert_test("Navbar Petugas TIDAK menampilkan Barang", strpos($nav_petugas, 'Barang') === false);
assert_test("Navbar Petugas TIDAK menampilkan Kategori", strpos($nav_petugas, 'Kategori') === false);
assert_test("Navbar Petugas TIDAK menampilkan Lokasi", strpos($nav_petugas, 'Lokasi') === false);
assert_test("Navbar Petugas TIDAK menampilkan Laporan", strpos($nav_petugas, 'Laporan') === false);
assert_test("Navbar Petugas TIDAK menampilkan Pengguna", strpos($nav_petugas, 'Pengguna') === false);

$nav_kepala = get_navbar_html('Kepala');
assert_test("Navbar Kepala menampilkan Laporan", strpos($nav_kepala, 'Laporan') !== false);
assert_test("Navbar Kepala menampilkan Laporan Barang", strpos($nav_kepala, 'Laporan Barang') !== false);
assert_test("Navbar Kepala menampilkan Rekap Kategori", strpos($nav_kepala, 'Rekap Kategori') !== false);
assert_test("Navbar Kepala TIDAK menampilkan Peminjaman", strpos($nav_kepala, 'href="../peminjaman/index.php"') === false);
assert_test("Navbar Kepala TIDAK menampilkan Transaksi", strpos($nav_kepala, 'href="../transaksi/index.php"') === false);
assert_test("Navbar Kepala TIDAK menampilkan Master Barang", strpos($nav_kepala, 'href="../barang/index.php"') === false);
assert_test("Navbar Kepala TIDAK menampilkan Pengguna", strpos($nav_kepala, 'Pengguna') === false);

$nav_admin = get_navbar_html('Admin');
assert_test("Navbar Admin menampilkan Barang", strpos($nav_admin, 'Barang') !== false);
assert_test("Navbar Admin menampilkan Kategori", strpos($nav_admin, 'Kategori') !== false);
assert_test("Navbar Admin menampilkan Lokasi", strpos($nav_admin, 'Lokasi') !== false);
assert_test("Navbar Admin menampilkan Peminjaman", strpos($nav_admin, 'Peminjaman') !== false);
assert_test("Navbar Admin menampilkan Transaksi", strpos($nav_admin, 'Transaksi') !== false);
assert_test("Navbar Admin menampilkan Laporan", strpos($nav_admin, 'Laporan') !== false);
assert_test("Navbar Admin menampilkan Pengguna", strpos($nav_admin, 'Pengguna') !== false);


echo "\n=========================================\n";
echo "TEST SUMMARY: Total: $tests_run | Passed: $tests_passed | Failed: $tests_failed\n";
echo "=========================================\n";

if ($tests_failed === 0) {
    echo "ALL TESTS PASSED SUCCESSFULLY!\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED!\n";
    exit(1);
}
