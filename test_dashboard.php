<?php
/**
 * Test Dashboard Inventory RBAC, Rendering & Role-Aware Components
 */

function simulate_dashboard_access($role = '')
{
    $cmd = "php run_request.php \"views/dashboard/index.php\" \"$role\"";
    $output = shell_exec($cmd);
    preg_match('/HTTP_CODE:(\d+)/', $output, $matches);
    $code = isset($matches[1]) ? (int)$matches[1] : 200;
    return ['code' => $code, 'output' => $output];
}

$tests_run = 0;
$tests_passed = 0;
$tests_failed = 0;

function assert_test($description, $condition)
{
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

echo "=== 1. TEST DASHBOARD ACCESS CONTROL (RBAC) ===\n";
// Guest
$guest = simulate_dashboard_access('');
assert_test("Guest akses dashboard diarahkan ke login (302)", $guest['code'] === 302);

// Petugas
$petugas = simulate_dashboard_access('Petugas');
assert_test("Petugas diizinkan akses dashboard (200)", $petugas['code'] === 200);

// Kepala
$kepala = simulate_dashboard_access('Kepala');
assert_test("Kepala diizinkan akses dashboard (200)", $kepala['code'] === 200);

// Admin
$admin = simulate_dashboard_access('Admin');
assert_test("Admin diizinkan akses dashboard (200)", $admin['code'] === 200);

// Role tidak dikenal
$unknown = simulate_dashboard_access('Superhacker');
assert_test("Role tidak dikenal ditolak akses dashboard (403)", $unknown['code'] === 403);


echo "\n=== 2. TEST DASHBOARD CONTENT & ROLE-AWARE COMPONENTS ===\n";
// Admin view tests
assert_test("Admin melihat badge Administrator", strpos($admin['output'], 'Administrator') !== false);
assert_test("Admin melihat tombol Tambah Barang", strpos($admin['output'], 'Tambah Barang') !== false);
assert_test("Admin melihat 4 Summary Cards", 
    strpos($admin['output'], 'Transaksi Keluar') !== false &&
    strpos($admin['output'], 'Transaksi Masuk') !== false &&
    strpos($admin['output'], 'Total Barang') !== false &&
    strpos($admin['output'], 'Peminjaman Aktif') !== false
);
assert_test("Admin melihat Chart.js Canvas", strpos($admin['output'], 'id="txChart"') !== false);
assert_test("Admin melihat Tabel Transaksi Masuk", strpos($admin['output'], 'Transaksi Masuk Terkini') !== false);
assert_test("Admin melihat Tabel Transaksi Keluar", strpos($admin['output'], 'Transaksi Keluar Terkini') !== false);
assert_test("Admin melihat Tabel Peminjaman Terkini", strpos($admin['output'], 'Aktivitas Peminjaman Terkini') !== false);
assert_test("Admin melihat widget Kondisi Inventaris", strpos($admin['output'], 'Kondisi & Stok Inventaris') !== false);
assert_test("Admin melihat widget Peringatan Stok Rendah", strpos($admin['output'], 'Peringatan Stok Rendah') !== false);

// Petugas view tests
assert_test("Petugas melihat badge Petugas Operasional", strpos($petugas['output'], 'Petugas Operasional') !== false);
assert_test("Petugas melihat shortcut Catat Transaksi", strpos($petugas['output'], 'Catat Transaksi') !== false);
assert_test("Petugas melihat shortcut Catat Peminjaman", strpos($petugas['output'], 'Catat Peminjaman') !== false);
assert_test("Petugas TIDAK melihat tombol Tambah Barang", strpos($petugas['output'], 'Tambah Barang') === false);
assert_test("Petugas melihat Chart.js Canvas", strpos($petugas['output'], 'id="txChart"') !== false);

// Kepala view tests
assert_test("Kepala melihat badge Kepala Bagian", strpos($kepala['output'], 'Kepala Bagian') !== false);
assert_test("Kepala melihat shortcut Laporan Transaksi", strpos($kepala['output'], 'Lap. Transaksi') !== false);
assert_test("Kepala melihat shortcut Laporan Barang", strpos($kepala['output'], 'Lap. Barang') !== false);
assert_test("Kepala melihat shortcut Cetak PDF", strpos($kepala['output'], 'Cetak PDF') !== false);
assert_test("Kepala TIDAK melihat shortcut Catat Transaksi", strpos($kepala['output'], 'Catat Transaksi') === false);
assert_test("Kepala diarahkan ke Laporan pada link Lihat Semua", strpos($kepala['output'], '../laporan/transaksi.php') !== false);

echo "\n=========================================\n";
echo "DASHBOARD TEST SUMMARY: Total: $tests_run | Passed: $tests_passed | Failed: $tests_failed\n";
echo "=========================================\n";

if ($tests_failed === 0) {
    echo "ALL DASHBOARD TESTS PASSED SUCCESSFULLY!\n";
    exit(0);
} else {
    exit(1);
}
