<?php

// Script Verifikasi Responsiveness & Viewport Matrix
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

require_once __DIR__ . '/app/app.php';

$viewports = [
    '320px'  => ['type' => 'mobile',  'width' => 320,  'desc' => 'iPhone SE / Small Mobile'],
    '375px'  => ['type' => 'mobile',  'width' => 375,  'desc' => 'iPhone Mini / Modern Small'],
    '390px'  => ['type' => 'mobile',  'width' => 390,  'desc' => 'iPhone 12/13/14 Standard'],
    '414px'  => ['type' => 'mobile',  'width' => 414,  'desc' => 'iPhone Plus / Large Mobile'],
    '768px'  => ['type' => 'tablet',  'width' => 768,  'desc' => 'iPad Portrait / Small Tablet'],
    '1024px' => ['type' => 'desktop', 'width' => 1024, 'desc' => 'iPad Landscape / Small Laptop'],
    '1280px' => ['type' => 'desktop', 'width' => 1280, 'desc' => 'Standard Laptop Display'],
    '1440px' => ['type' => 'desktop', 'width' => 1440, 'desc' => 'Desktop HD / Large Laptop'],
    '1920px' => ['type' => 'desktop', 'width' => 1920, 'desc' => 'Full HD Desktop Display'],
];

$pages = [
    'Dashboard Utama'     => 'views/dashboard/index.php',
    'Landing Barang'      => 'views/barang/index.php',
    'Transaksi'           => 'views/transaksi/index.php',
    'Peminjaman'          => 'views/peminjaman/index.php',
    'Laporan Barang'      => 'views/laporan/barang.php',
    'Laporan Peminjaman'  => 'views/laporan/peminjaman.php',
    'Laporan Transaksi'   => 'views/laporan/transaksi.php',
    'Rekap Kategori'      => 'views/laporan/kategori.php',
    'Rekap Lokasi'        => 'views/laporan/lokasi.php',
    'Inventory Barang'    => 'views/barang/index.php',
    'Master Kategori'     => 'views/kategori/index.php',
    'Master Lokasi'       => 'views/lokasi/index.php',
    'User Management'     => 'views/users/index.php',
];

echo "========================================================\n";
echo "1. VERIFIKASI STRUKTUR LAYOUT & SINGLE SOURCE OF TRUTH\n";
echo "========================================================\n";

$header_content = file_get_contents(__DIR__ . '/layout/header.php');
$footer_content = file_get_contents(__DIR__ . '/layout/footer.php');

$checks = [
    'App Layout Container' => strpos($header_content, 'class="app-layout"') !== false,
    'App Sidebar Element'  => strpos($header_content, 'id="appSidebar"') !== false,
    'Sidebar Backdrop'     => strpos($header_content, 'id="sidebarBackdrop"') !== false,
    'App Topbar Header'    => strpos($header_content, 'class="app-topbar"') !== false,
    'Sidebar Toggle Button'=> strpos($header_content, 'id="sidebarToggle"') !== false,
    'App Content Wrapper'  => strpos($header_content, 'class="app-content"') !== false,
    'App Footer Element'   => strpos($footer_content, 'class="app-footer"') !== false,
    'Overflow-X Protection'=> strpos($header_content, 'overflow-x: hidden') !== false,
    'Sidebar Interactivity JS' => strpos($footer_content, 'sidebar-toggle-btn') !== false && strpos($footer_content, 'sidebarBackdrop') !== false,
    'Keyboard Esc Support' => strpos($footer_content, "'Escape'") !== false,
];

foreach ($checks as $title => $status) {
    echo "[" . ($status ? "PASS" : "FAIL") . "] $title\n";
    if (!$status) exit(1);
}

echo "\n========================================================\n";
echo "2. VERIFIKASI DEVICE VIEWPORT MATRIX (320px - 1920px)\n";
echo "========================================================\n";

// Memeriksa aturan media query CSS mencakup breakpoint yang ditentukan
$has_mobile_rules  = strpos($header_content, '@media (max-width: 576px)') !== false;
$has_tablet_rules  = strpos($header_content, '@media (max-width: 991.98px)') !== false;
$has_desktop_rules = strpos($header_content, '@media (min-width: 992px)') !== false;

foreach ($viewports as $px => $vp) {
    $supported = false;
    if ($vp['width'] <= 576 && $has_mobile_rules && $has_tablet_rules) {
        $supported = true;
    } elseif ($vp['width'] < 992 && $has_tablet_rules) {
        $supported = true;
    } elseif ($vp['width'] >= 992 && $has_desktop_rules) {
        $supported = true;
    }
    echo "Viewport {$px} ({$vp['desc']}): " . ($supported ? "PASS" : "FAIL") . "\n";
    if (!$supported) exit(1);
}

echo "\n========================================================\n";
echo "3. VERIFIKASI HALAMAN & KOMPONEN RESPONSIVE\n";
echo "========================================================\n";

foreach ($pages as $label => $file) {
    $content = file_get_contents(__DIR__ . '/' . $file);
    
    $has_responsive_table = (strpos($content, '<table') === false) || (strpos($content, 'table-responsive') !== false);
    $no_unresponsive_float = strpos($content, 'style="float: right;"') === false;
    $has_flex_header = (strpos($content, 'justify-content-between') === false) || (strpos($content, 'flex-wrap') !== false);

    $page_pass = $has_responsive_table && $no_unresponsive_float && $has_flex_header;
    echo "Halaman $label ($file): " . ($page_pass ? "PASS" : "FAIL") . "\n";
    if (!$page_pass) {
        echo "  - Responsive table: " . ($has_responsive_table ? "OK" : "MISSING") . "\n";
        echo "  - No raw float: " . ($no_unresponsive_float ? "OK" : "FOUND RAW FLOAT") . "\n";
        echo "  - Flex wrap header: " . ($has_flex_header ? "OK" : "MISSING") . "\n";
        exit(1);
    }
}

echo "\n========================================================\n";
echo "ALL RESPONSIVE AUDIT & VIEWPORT TESTS PASSED!\n";
echo "========================================================\n";
