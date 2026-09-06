<?php

include_once __DIR__ . '/../app/app.php';

// Cek autentikasi login (jika bukan di halaman auth atau error)
$current_path = str_replace('\\', '/', $_SERVER['PHP_SELF'] ?? '');
if (strpos($current_path, '/views/auth/') === false && strpos($current_path, '/views/error/') === false) {
    check_login();
}

$user_nama     = $_SESSION['nama'] ?? 'Guest';
$user_username = $_SESSION['username'] ?? ($_SESSION['nama'] ?? 'Guest');
$user_role     = get_user_role();
$brand_url     = '../dashboard/index.php';

// Tentukan warna badge role
$badge_class = 'bg-secondary';
if ($user_role === 'admin') {
    $badge_class = 'bg-danger';
} elseif ($user_role === 'petugas') {
    $badge_class = 'bg-primary';
} elseif ($user_role === 'kepala') {
    $badge_class = 'bg-success';
}

// Deteksi status menu aktif berdasarkan URL
$is_dashboard  = strpos($current_path, '/views/dashboard/') !== false;
$is_barang     = strpos($current_path, '/views/barang/') !== false;
$is_kategori   = strpos($current_path, '/views/kategori/') !== false;
$is_lokasi     = strpos($current_path, '/views/lokasi/') !== false;
$is_peminjaman = strpos($current_path, '/views/peminjaman/') !== false;
$is_transaksi  = strpos($current_path, '/views/transaksi/') !== false;
$is_laporan    = strpos($current_path, '/views/laporan/') !== false;
$is_users      = strpos($current_path, '/views/users/') !== false;

// Submenu laporan aktif
$is_lap_barang     = strpos($current_path, '/views/laporan/barang.php') !== false;
$is_lap_peminjaman = strpos($current_path, '/views/laporan/peminjaman.php') !== false;
$is_lap_transaksi  = strpos($current_path, '/views/laporan/transaksi.php') !== false;
$is_lap_kategori   = strpos($current_path, '/views/laporan/kategori.php') !== false;
$is_lap_lokasi     = strpos($current_path, '/views/laporan/lokasi.php') !== false;

?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Informasi Inventory</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script>
      // Pulihkan preferensi buka/tutup sidebar desktop sebelum render body
      try {
        if (window.innerWidth >= 992 && localStorage.getItem('sidebar_collapsed') === 'true') {
          document.documentElement.classList.add('sidebar-collapsed');
        }
      } catch (e) {}
    </script>
    <style>
      :root {
        --sidebar-width: 260px;
        --sidebar-bg: #ffffff;
        --sidebar-color: #64748b;
        --sidebar-color-active: #0d6efd;
        --sidebar-active-bg: #eff6ff;
        --sidebar-hover-bg: #f8fafc;
        --topbar-height: 60px;
      }

      html, body {
        max-width: 100%;
        overflow-x: hidden;
      }

      body {
        background-color: #f8f9fa;
        color: #212529;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        margin: 0;
        padding: 0;
      }

      body.sidebar-open {
        overflow: hidden;
      }

      .app-layout {
        display: flex;
        min-height: 100vh;
        width: 100%;
        position: relative;
      }

      /* SIDEBAR STYLING (MODERN WHITE) */
      .app-sidebar {
        width: var(--sidebar-width);
        background-color: var(--sidebar-bg);
        color: var(--sidebar-color);
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        z-index: 1045;
        display: flex;
        flex-direction: column;
        transition: transform 0.28s ease-in-out;
        overflow-y: auto;
        overflow-x: hidden;
        border-right: 1px solid #e2e8f0;
        box-shadow: 2px 0 12px rgba(0, 0, 0, 0.04);
      }

      .app-sidebar::-webkit-scrollbar {
        width: 4px;
      }
      .app-sidebar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
      }

      .sidebar-header {
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #e2e8f0;
        background-color: #ffffff;
      }

      .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #0f172a;
        text-decoration: none;
        font-weight: 700;
        font-size: 1.1rem;
        letter-spacing: 0.5px;
      }
      .sidebar-brand:hover {
        color: #0d6efd;
      }

      .sidebar-nav {
        padding: 1rem 0.75rem;
        flex: 1 1 auto;
        list-style: none;
        margin: 0;
      }

      .sidebar-heading {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #94a3b8;
        padding: 0.75rem 0.75rem 0.35rem 0.75rem;
        margin-top: 0.35rem;
      }

      .sidebar-item {
        margin-bottom: 0.25rem;
      }

      .sidebar-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.65rem 0.85rem;
        color: var(--sidebar-color);
        text-decoration: none;
        border-radius: 0.5rem;
        font-weight: 500;
        font-size: 0.925rem;
        transition: all 0.2s ease-in-out;
      }

      .sidebar-link i {
        font-size: 1.15rem;
        width: 22px;
        text-align: center;
        flex-shrink: 0;
        color: #64748b;
        transition: color 0.2s ease;
      }

      .sidebar-link:hover {
        color: #0f172a;
        background-color: var(--sidebar-hover-bg);
      }
      .sidebar-link:hover i {
        color: #0f172a;
      }

      .sidebar-link.active {
        color: #0d6efd;
        background-color: var(--sidebar-active-bg);
        font-weight: 600;
      }
      .sidebar-link.active i {
        color: #0d6efd;
      }

      .sidebar-link .dropdown-arrow {
        margin-left: auto;
        transition: transform 0.2s ease;
        font-size: 0.85rem;
      }
      .sidebar-link[aria-expanded="true"] .dropdown-arrow {
        transform: rotate(180deg);
      }

      .sidebar-submenu {
        list-style: none;
        padding-left: 1.5rem;
        margin: 0.25rem 0;
      }

      .sidebar-sublink {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.75rem;
        color: #64748b;
        text-decoration: none;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        margin-bottom: 0.15rem;
      }

      .sidebar-sublink:hover {
        color: #0f172a;
        background-color: var(--sidebar-hover-bg);
      }

      .sidebar-sublink.active {
        color: #0d6efd;
        background-color: var(--sidebar-active-bg);
        font-weight: 600;
      }

      .sidebar-footer {
        padding: 1rem;
        border-top: 1px solid #e2e8f0;
        background-color: #f8fafc;
      }

      /* BACKDROP */
      .sidebar-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1040;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.28s ease-in-out, visibility 0.28s ease-in-out;
      }

      .sidebar-backdrop.show {
        opacity: 1;
        visibility: visible;
      }

      /* APP MAIN */
      .app-main {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        min-width: 0;
        min-height: 100vh;
        transition: margin-left 0.28s ease-in-out, width 0.28s ease-in-out;
      }

      /* TOPBAR */
      .app-topbar {
        height: var(--topbar-height);
        background-color: #ffffff;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 1.25rem;
        position: sticky;
        top: 0;
        z-index: 1030;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
      }

      .topbar-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
      }

      .topbar-toggler {
        background: none;
        border: none;
        color: #495057;
        font-size: 1.45rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        line-height: 1;
        cursor: pointer;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
      }

      .topbar-toggler:hover,
      .topbar-toggler:focus {
        background-color: #f1f3f5;
        color: #0d6efd;
      }

      .topbar-title {
        font-size: 1rem;
        font-weight: 600;
        color: #343a40;
        margin: 0;
      }

      .topbar-right {
        display: flex;
        align-items: center;
        gap: 0.5rem;
      }

      /* CONTENT */
      .app-content {
        flex: 1 1 auto;
        width: 100%;
        padding: 1.25rem 1rem 2rem 1rem;
      }

      /* Override nested page margins */
      .app-content > .container,
      .app-content > .container-fluid {
        margin-top: 0.25rem !important;
        margin-bottom: 1rem !important;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
        max-width: 100%;
      }

      /* Responsive action table helpers */
      .table-actions {
        white-space: nowrap;
      }

      /* FOOTER */
      .app-footer {
        padding: 0.85rem 1.25rem;
        background-color: #ffffff;
        border-top: 1px solid #dee2e6;
        color: #6c757d;
        font-size: 0.85rem;
      }

      /* DESKTOP & LAPTOP (>= 992px) */
      @media (min-width: 992px) {
        .app-sidebar {
          transform: translateX(0);
        }
        .app-main {
          margin-left: var(--sidebar-width);
          width: calc(100% - var(--sidebar-width));
        }

        /* Desktop Collapse State: Sidebar Tertutup */
        html.sidebar-collapsed body .app-sidebar,
        body.sidebar-collapsed .app-sidebar {
          transform: translateX(-100%) !important;
        }
        html.sidebar-collapsed body .app-main,
        body.sidebar-collapsed .app-main {
          margin-left: 0 !important;
          width: 100% !important;
        }

        .sidebar-backdrop {
          display: none !important;
        }
        .topbar-toggler {
          display: inline-flex !important;
        }
        .app-content {
          padding: 1.5rem 1.75rem 2.5rem 1.75rem;
        }
        .app-content > .container,
        .app-content > .container-fluid {
          padding-left: 1rem;
          padding-right: 1rem;
        }
      }

      /* TABLET & MOBILE (< 992px) */
      @media (max-width: 991.98px) {
        .app-sidebar {
          transform: translateX(-100%);
        }
        .app-sidebar.show {
          transform: translateX(0);
        }
        .topbar-toggler {
          display: inline-flex;
        }
        .app-main {
          margin-left: 0;
          width: 100%;
        }
        .app-content {
          padding: 0.75rem 0.5rem 1.5rem 0.5rem;
        }
      }

      @media (max-width: 576px) {
        :root {
          --sidebar-width: 270px;
        }
        .app-topbar {
          padding: 0 0.75rem;
        }
        .topbar-title {
          font-size: 0.925rem;
        }
        .badge {
          font-size: 0.75rem;
        }
        .btn-sm {
          font-size: 0.775rem;
          padding: 0.25rem 0.5rem;
        }
      }
    </style>
  </head>
  <body>
    <div class="app-layout">
      <!-- Sidebar Navigation -->
      <aside class="app-sidebar" id="appSidebar">
        <div class="sidebar-header">
          <a class="sidebar-brand" href="<?= $brand_url; ?>" title="Halaman Utama">
            <img src="../../img/logo-adiwiyata.png" alt="Logo" width="34" height="34" class="d-inline-block align-text-top">
            <span>Sistem Inventory</span>
          </a>
        </div>

        <ul class="sidebar-nav">
          <!-- Menu Utama (Semua Role) -->
          <li class="sidebar-heading">Menu Utama</li>
          <li class="sidebar-item">
            <a class="sidebar-link <?= $is_dashboard ? 'active' : ''; ?>" href="../dashboard/index.php">
              <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
            </a>
          </li>

          <?php if (has_role('admin')) : ?>
            <!-- Menu Master Data (Khusus Admin) -->
            <li class="sidebar-heading">Master Data</li>
            <li class="sidebar-item">
              <a class="sidebar-link <?= $is_barang ? 'active' : ''; ?>" href="../barang/index.php">
                <i class="bi bi-box-seam"></i> <span>Barang</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link <?= $is_kategori ? 'active' : ''; ?>" href="../kategori/index.php">
                <i class="bi bi-tags"></i> <span>Kategori</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link <?= $is_lokasi ? 'active' : ''; ?>" href="../lokasi/index.php">
                <i class="bi bi-geo-alt"></i> <span>Lokasi</span>
              </a>
            </li>
          <?php endif; ?>

          <?php if (has_role(['admin', 'petugas'])) : ?>
            <!-- Menu Operasional (Petugas & Admin) -->
            <li class="sidebar-heading">Operasional</li>
            <li class="sidebar-item">
              <a class="sidebar-link <?= $is_peminjaman ? 'active' : ''; ?>" href="../peminjaman/index.php">
                <i class="bi bi-arrow-repeat"></i> <span>Peminjaman</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link <?= $is_transaksi ? 'active' : ''; ?>" href="../transaksi/index.php">
                <i class="bi bi-journal-text"></i> <span>Transaksi</span>
              </a>
            </li>
          <?php endif; ?>

          <?php if (has_role(['admin', 'kepala'])) : ?>
            <!-- Menu Laporan (Kepala & Admin) -->
            <li class="sidebar-heading">Laporan</li>
            <li class="sidebar-item">
              <a class="sidebar-link <?= $is_laporan ? 'active' : ''; ?>" data-bs-toggle="collapse" href="#collapseLaporan" role="button" aria-expanded="<?= $is_laporan ? 'true' : 'false'; ?>" aria-controls="collapseLaporan">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Laporan</span>
                <i class="bi bi-chevron-down dropdown-arrow"></i>
              </a>
              <div class="collapse <?= $is_laporan ? 'show' : ''; ?>" id="collapseLaporan">
                <ul class="sidebar-submenu">
                  <li>
                    <a class="sidebar-sublink <?= $is_lap_barang ? 'active' : ''; ?>" href="../laporan/barang.php">
                      <i class="bi bi-box"></i> <span>Laporan Barang</span>
                    </a>
                  </li>
                  <li>
                    <a class="sidebar-sublink <?= $is_lap_peminjaman ? 'active' : ''; ?>" href="../laporan/peminjaman.php">
                      <i class="bi bi-arrow-repeat"></i> <span>Laporan Peminjaman</span>
                    </a>
                  </li>
                  <li>
                    <a class="sidebar-sublink <?= $is_lap_transaksi ? 'active' : ''; ?>" href="../laporan/transaksi.php">
                      <i class="bi bi-journal-check"></i> <span>Laporan Transaksi</span>
                    </a>
                  </li>
                  <li>
                    <a class="sidebar-sublink <?= $is_lap_kategori ? 'active' : ''; ?>" href="../laporan/kategori.php">
                      <i class="bi bi-tags"></i> <span>Rekap Kategori</span>
                    </a>
                  </li>
                  <li>
                    <a class="sidebar-sublink <?= $is_lap_lokasi ? 'active' : ''; ?>" href="../laporan/lokasi.php">
                      <i class="bi bi-geo"></i> <span>Rekap Lokasi</span>
                    </a>
                  </li>
                </ul>
              </div>
            </li>
          <?php endif; ?>

          <?php if (has_role('admin')) : ?>
            <!-- Menu Pengguna (Khusus Admin) -->
            <li class="sidebar-heading">Sistem</li>
            <li class="sidebar-item">
              <a class="sidebar-link <?= $is_users ? 'active' : ''; ?>" href="../users/index.php">
                <i class="bi bi-people"></i> <span>Pengguna</span>
              </a>
            </li>
          <?php endif; ?>
        </ul>

        <div class="sidebar-footer">
          <div class="d-flex align-items-center justify-content-between">
            <div class="text-truncate me-2">
              <div class="text-dark fw-semibold small text-truncate"><?= htmlspecialchars($user_username); ?></div>
              <span class="badge <?= $badge_class; ?> py-1 px-2 text-capitalize"><?= htmlspecialchars($user_role ?: 'User'); ?></span>
            </div>
            <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true) : ?>
              <a href="../auth/logout.php" class="btn btn-outline-danger btn-sm" title="Logout" onclick="return confirm('Apakah Anda yakin ingin keluar?');">
                <i class="bi bi-box-arrow-right"></i>
              </a>
            <?php else : ?>
              <a href="../auth/login.php" class="btn btn-primary btn-sm" title="Login">
                <i class="bi bi-box-arrow-in-right"></i>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </aside>

      <!-- Sidebar Backdrop for Mobile/Tablet -->
      <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

      <!-- Main Application Shell -->
      <div class="app-main">
        <!-- Topbar Header -->
        <header class="app-topbar">
          <div class="topbar-left">
            <button class="topbar-toggler sidebar-toggle-btn" id="sidebarToggle" type="button" aria-label="Toggle navigation" title="Buka / Tutup Sidebar">
              <i class="bi bi-list"></i>
            </button>
          </div>
          <div class="topbar-right">
            <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true) : ?>
              <span class="badge <?= $badge_class; ?> py-2 px-3 d-none d-sm-inline-flex align-items-center">
                <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($user_username); ?> (<?= htmlspecialchars(ucfirst($user_role ?: 'User')); ?>)
              </span>
              <a href="../auth/logout.php" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center" onclick="return confirm('Apakah Anda yakin ingin keluar?');">
                <i class="bi bi-box-arrow-right me-1"></i> <span class="d-none d-sm-inline">Logout</span>
              </a>
            <?php else : ?>
              <a href="../auth/login.php" class="btn btn-primary btn-sm">
                <i class="bi bi-box-arrow-in-right me-1"></i> Login
              </a>
            <?php endif; ?>
          </div>
        </header>

        <!-- Main Content Area -->
        <main class="app-content">