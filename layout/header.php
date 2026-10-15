<?php
include_once __DIR__ . '/../app/app.php';

// Memuat file utama aplikasi agar fungsi dan konfigurasi dapat digunakan

// Mengecek autentikasi login
$current_path = str_replace('\\', '/', $_SERVER['PHP_SELF'] ?? '');

// Menentukan halaman yang dapat diakses tanpa login
$is_public_page = (
    strpos($current_path, '/views/dashboard/') !== false ||
    strpos($current_path, '/views/barang/index.php') !== false ||
    strpos($current_path, '/views/kategori/index.php') !== false ||
    strpos($current_path, '/views/lokasi/index.php') !== false
);

// Jika halaman bukan halaman autentikasi, error, atau halaman publik,      
// maka pengguna harus melakukan login terlebih dahulu
if (
    strpos($current_path, '/views/auth/') === false &&
    strpos($current_path, '/views/error/') === false &&
    !$is_public_page
) {
    check_login();
}

// Mengambil nama pengguna dari session
// Jika belum login, gunakan nama "Pengunjung"
$user_nama = $_SESSION['nama'] ?? 'Pengunjung';

// Mengambil username pengguna dari session
// Jika belum login, gunakan username "Guest"
$user_username = $_SESSION['username'] ?? 'Guest';

// Mengambil role pengguna yang sedang aktif
$user_role = get_user_role();

// Menentukan alamat halaman utama/dashboard
$brand_url = '../dashboard/index.php';


// Menentukan warna badge berdasarkan role pengguna
$badge_class = 'bg-secondary';

if ($user_role === 'admin') {
    // Badge berwarna merah untuk Admin
    $badge_class = 'bg-danger';

} elseif ($user_role === 'petugas') {
    // Badge berwarna biru untuk Petugas
    $badge_class = 'bg-primary';

} elseif ($user_role === 'kepala') {
    // Badge berwarna hijau untuk Kepala
    $badge_class = 'bg-success';
}


// Menentukan menu yang sedang aktif berdasarkan alamat halaman
$is_dashboard  = strpos($current_path, '/views/dashboard/') !== false;
$is_barang     = strpos($current_path, '/views/barang/') !== false;
$is_kategori   = strpos($current_path, '/views/kategori/') !== false;
$is_lokasi     = strpos($current_path, '/views/lokasi/') !== false;
$is_peminjaman = strpos($current_path, '/views/peminjaman/') !== false;
$is_transaksi  = strpos($current_path, '/views/transaksi/') !== false;
$is_laporan    = strpos($current_path, '/views/laporan/') !== false;
$is_users      = strpos($current_path, '/views/users/') !== false;


// Menentukan submenu laporan yang sedang aktif
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
    <!-- Mengatur tampilan agar menyesuaikan ukuran layar perangkat -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Judul halaman yang ditampilkan pada tab browser -->
    <title>SI Inventory Barang Adiwiyata</title>
    <!-- Memuat Bootstrap CSS untuk membantu membuat tampilan antarmuka -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- Memuat Bootstrap Icons untuk menggunakan ikon pada menu dan tombol -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script>
      // Mengembalikan kondisi sidebar yang sebelumnya disimpan di browser
      try {
        if (window.innerWidth >= 992 && localStorage.getItem('sidebar_collapsed') === 'true') {
          document.documentElement.classList.add('sidebar-collapsed');
        }
      } catch (e) {}
    </script>

    <style>
      /* Variabel utama yang digunakan untuk mengatur ukuran dan warna sidebar */
      :root {
        --sidebar-width: 260px;
        --sidebar-bg: #ffffff;
        --sidebar-color: #64748b;
        --sidebar-color-active: #2e7d32;
        --sidebar-active-bg: #e8f5e9;
        --sidebar-hover-bg: #f1f8f2;
        --topbar-height: 60px;
      }

      /* Mencegah halaman melebar secara horizontal */
      html, body {
        max-width: 100%;
        overflow-x: hidden;
      }

      /* Mengatur tampilan dasar halaman */
      body {
        background-color: #f8f9fa;
        color: #212529;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        margin: 0;
        padding: 0;
      }

      /* Mencegah halaman utama digeser ketika sidebar mobile sedang terbuka */
      body.sidebar-open {
        overflow: hidden;
      }
      
        /* Warna baris tabel selang-seling */
        .table.table-striped > tbody > tr:nth-of-type(odd) > * {
            --bs-table-bg: #fbfffb;
        }

        .table.table-striped > tbody > tr:nth-of-type(even) > * {
            --bs-table-bg: #ffffff;
        }

        /* Warna ketika kursor diarahkan ke baris */
        .table.table-hover > tbody > tr:hover > * {
            --bs-table-bg: #eefdf2;
        }

      /* Mengatur layout utama aplikasi agar sidebar dan konten berada dalam satu struktur */
      .app-layout {
        display: flex;
        min-height: 100vh;
        width: 100%;
        position: relative;
      }

      /* Mengatur tampilan sidebar */
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

      /* Mengatur scrollbar pada sidebar */
      .app-sidebar::-webkit-scrollbar {
        width: 4px;
      }
      .app-sidebar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
      }

      /* Mengatur bagian atas sidebar yang berisi logo dan nama aplikasi */
      .sidebar-header {
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #e2e8f0;
        background-color: #ffffff;
      }

      /* Mengatur tampilan logo dan nama aplikasi */
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

      /* Mengubah warna nama aplikasi ketika kursor diarahkan ke sana */
      .sidebar-brand:hover {
        color: #2e7d32;
      }

      /* Mengatur area daftar menu sidebar */
      .sidebar-nav {
        padding: 1rem 0.75rem;
        flex: 1 1 auto;
        list-style: none;
        margin: 0;
      }

      /* Mengatur judul kelompok menu seperti Menu Utama dan Data Master */
      .sidebar-heading {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #94a3b8;
        padding: 0.75rem 0.75rem 0.35rem 0.75rem;
        margin-top: 0.35rem;
      }

      /* Memberikan jarak antar menu */
      .sidebar-item {
        margin-bottom: 0.25rem;
      }

      /* Mengatur tampilan link/menu utama pada sidebar */
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

      /* Mengatur ukuran dan posisi ikon pada menu sidebar */
      .sidebar-link i {
        font-size: 1.15rem;
        width: 22px;
        text-align: center;
        flex-shrink: 0;
        color: #64748b;
        transition: color 0.2s ease;
      }

      /* Mengatur tampilan menu ketika kursor diarahkan ke menu */
      .sidebar-link:hover {
        color: #0f172a;
        background-color: var(--sidebar-hover-bg);
      }

      /* Mengubah warna ikon ketika menu disentuh kursor */
      .sidebar-link:hover i {
        color: #0f172a;
      }

      /* Mengatur tampilan menu yang sedang aktif */
      .sidebar-link.active {
        color: #2e7d32;
        background-color: #e8f5e9;
        font-weight: 600;
      }

      /* Mengubah warna ikon pada menu yang sedang aktif */
      .sidebar-link.active i {
        color: #2e7d32;
      }

      /* Mengatur posisi dan animasi ikon panah pada submenu */
      .sidebar-link .dropdown-arrow {
        margin-left: auto;
        transition: transform 0.2s ease;
        font-size: 0.85rem;
      }

      /* Memutar panah ketika submenu sedang terbuka */
      .sidebar-link[aria-expanded="true"] .dropdown-arrow {
        transform: rotate(180deg);
      }

      /* Mengatur daftar submenu */
      .sidebar-submenu {
        list-style: none;
        padding-left: 1.5rem;
        margin: 0.25rem 0;
      }

      /* Mengatur tampilan link pada submenu */
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

      /* Mengatur tampilan submenu ketika kursor diarahkan ke sana */
      .sidebar-sublink:hover {
        color: #0f172a;
        background-color: var(--sidebar-hover-bg);
      }

      /* Mengatur tampilan submenu yang sedang aktif */
      .sidebar-sublink.active {
        color: #2e7d32;
        background-color: #e8f5e9;
        font-weight: 600;
      }

      /* Mengatur bagian bawah sidebar yang berisi informasi pengguna dan tombol login/logout */
      .sidebar-footer {
        padding: 1rem;
        border-top: 1px solid #e2e8f0;
        background-color: #f8fafc;
      }

      /* Membuat lapisan gelap ketika sidebar dibuka pada perangkat kecil */
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

      /* Menampilkan lapisan gelap ketika sidebar sedang aktif */
      .sidebar-backdrop.show {
        opacity: 1;
        visibility: visible;
      }

      /* Mengatur area utama aplikasi di sebelah sidebar */
      .app-main {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        min-width: 0;
        min-height: 100vh;
        transition: margin-left 0.28s ease-in-out, width 0.28s ease-in-out;
      }

      /* Mengatur bagian atas halaman atau topbar */
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

      /* Mengatur bagian kiri topbar */
      .topbar-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
      }

      /* Mengatur tombol untuk membuka atau menutup sidebar */
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

      /* Mengubah tampilan tombol sidebar ketika diarahkan kursor atau dipilih */
      .topbar-toggler:hover,
      .topbar-toggler:focus {
        background-color: #e8f5e9;
        color: #2e7d32;
      }

      /* Mengatur judul pada topbar */
      .topbar-title {
        font-size: 1rem;
        font-weight: 600;
        color: #343a40;
        margin: 0;
      }

      /* Mengatur bagian kanan topbar */
      .topbar-right {
        display: flex;
        align-items: center;
        gap: 0.5rem;
      }

      /* Mengatur area utama tempat isi setiap halaman ditampilkan */
      .app-content {
        flex: 1 1 auto;
        width: 100%;
        padding: 1.25rem 1rem 2rem 1rem;
      }

      /* Mengatur jarak dan lebar container di dalam area konten */
      .app-content > .container,
      .app-content > .container-fluid {
        margin-top: 0.25rem !important;
        margin-bottom: 1rem !important;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
        max-width: 100%;
      }

      /* Mencegah tombol aksi dalam tabel turun ke baris berikutnya */
      .table-actions {
        white-space: nowrap;
      }

      /* Mengatur warna header tabel menjadi hijau */
      .table-header-green {
        background-color: #1b5e20 !important;
        color: #ffffff !important;
      }

      /* Mengatur warna teks dan latar belakang setiap kolom header tabel */
      .table-header-green th {
        background-color: #1b5e20 !important;
        color: #ffffff !important;
      }

      /* Mengatur bagian footer aplikasi */
      .app-footer {
        padding: 0.85rem 1.25rem;
        background-color: #ffffff;
        border-top: 1px solid #dee2e6;
        color: #6c757d;
        font-size: 0.85rem;
      }

      /* Tampilan untuk layar desktop dan laptop dengan lebar minimal 992px */
      @media (min-width: 992px) {
        /* Sidebar selalu terlihat pada layar besar */
        .app-sidebar {
          transform: translateX(0);
        }

        /* Memberikan ruang untuk sidebar pada konten utama */
        .app-main {
          margin-left: var(--sidebar-width);
          width: calc(100% - var(--sidebar-width));
        }

        /* Menutup sidebar ketika mode sidebar tertutup di desktop */
        html.sidebar-collapsed body .app-sidebar,
        body.sidebar-collapsed .app-sidebar {
          transform: translateX(-100%) !important;
        }

        /* Membuat konten utama memenuhi layar ketika sidebar ditutup */
        html.sidebar-collapsed body .app-main,
        body.sidebar-collapsed .app-main {
          margin-left: 0 !important;
          width: 100% !important;
        }

        /* Backdrop tidak digunakan pada desktop */
        .sidebar-backdrop {
          display: none !important;
        }

        /* Tombol sidebar tetap ditampilkan pada desktop */
        .topbar-toggler {
          display: inline-flex !important;
        }

        /* Memberikan jarak konten yang lebih besar pada layar desktop */
        .app-content {
          padding: 1.5rem 1.75rem 2.5rem 1.75rem;
        }

        /* Mengatur padding container pada desktop */
        .app-content > .container,
        .app-content > .container-fluid {
          padding-left: 1rem;
          padding-right: 1rem;
        }
      }

      /* Tampilan untuk tablet dan HP dengan lebar kurang dari 992px */
      @media (max-width: 991.98px) {
        /* Sidebar disembunyikan terlebih dahulu pada layar kecil */
        .app-sidebar {
          transform: translateX(-100%);
        }

        /* Menampilkan sidebar ketika tombol menu ditekan */
        .app-sidebar.show {
          transform: translateX(0);
        }

        /* Tombol sidebar ditampilkan pada layar kecil */
        .topbar-toggler {
          display: inline-flex;
        }

        /* Konten utama menggunakan seluruh lebar layar */
        .app-main {
          margin-left: 0;
          width: 100%;
        }

        /* Mengurangi jarak konten pada layar kecil */
        .app-content {
          padding: 0.75rem 0.5rem 1.5rem 0.5rem;
        }
      }

      /* Tampilan khusus untuk HP dengan lebar maksimal 576px */
      @media (max-width: 576px) {
        /* Mengatur lebar sidebar pada HP */
        :root {
          --sidebar-width: 270px;
        }

        /* Mengurangi jarak kiri dan kanan topbar pada HP */
        .app-topbar {
          padding: 0 0.75rem;
        }

        /* Mengecilkan ukuran judul topbar pada HP */
        .topbar-title {
          font-size: 0.925rem;
        }

        /* Mengecilkan ukuran badge pada HP */
        .badge {
          font-size: 0.75rem;
        }

        /* Mengecilkan ukuran tombol kecil pada HP */
        .btn-sm {
          font-size: 0.775rem;
          padding: 0.25rem 0.5rem;
        }
      }
    </style>
  </head>

  <body>
    <div class="app-layout">
      <!-- Sidebar navigasi utama aplikasi -->
      <aside class="app-sidebar" id="appSidebar">
        <!-- Bagian atas sidebar yang berisi logo dan nama aplikasi -->
        <div class="sidebar-header">
          <a class="sidebar-brand" href="<?= $brand_url; ?>" title="Halaman Utama">
            <img src="../../img/logo-adiwiyata.png" alt="Logo" width="34" height="34" class="d-inline-block align-text-top">
            <span>Sistem Inventory</span>
          </a>
        </div>

        <!-- Daftar menu navigasi sidebar -->
        <ul class="sidebar-nav">
          <!-- Kelompok menu utama yang dapat diakses -->
          <li class="sidebar-heading">Menu Utama</li>
          <li class="sidebar-item">
            <a class="sidebar-link <?= $is_dashboard ? 'active' : ''; ?>" href="../dashboard/index.php">
              <i class="bi bi-speedometer2"></i> <span>Beranda</span>
            </a>
          </li>

          <!-- Kelompok menu yang berisi data master -->
          <li class="sidebar-heading">Data Master</li>
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

          <?php if (has_role(['admin', 'petugas'])) : ?>
            <!-- Menu operasional hanya dapat digunakan oleh Admin dan Petugas -->
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
            <!-- Menu laporan hanya dapat digunakan oleh Admin dan Kepala -->
            <li class="sidebar-heading">Laporan</li>
            <li class="sidebar-item">
              <a class="sidebar-link <?= $is_laporan ? 'active' : ''; ?>"
                 data-bs-toggle="collapse"
                 href="#collapseLaporan"
                 role="button"
                 aria-expanded="<?= $is_laporan ? 'true' : 'false'; ?>"
                 aria-controls="collapseLaporan">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Laporan</span>
                <i class="bi bi-chevron-down dropdown-arrow"></i>
              </a>

              <!-- Submenu laporan -->
              <div class="collapse <?= $is_laporan ? 'show' : ''; ?>" id="collapseLaporan">
                <ul class="sidebar-submenu">

                  <!-- Menu laporan barang -->
                  <li>
                    <a class="sidebar-sublink <?= $is_lap_barang ? 'active' : ''; ?>" href="../laporan/barang.php">
                      <i class="bi bi-box"></i> <span>Laporan Barang</span>
                    </a>
                  </li>

                  <!-- Menu laporan peminjaman -->
                  <li>
                    <a class="sidebar-sublink <?= $is_lap_peminjaman ? 'active' : ''; ?>" href="../laporan/peminjaman.php">
                      <i class="bi bi-arrow-repeat"></i> <span>Laporan Peminjaman</span>
                    </a>
                  </li>

                  <!-- Menu laporan transaksi -->
                  <li>
                    <a class="sidebar-sublink <?= $is_lap_transaksi ? 'active' : ''; ?>" href="../laporan/transaksi.php">
                      <i class="bi bi-journal-check"></i> <span>Laporan Transaksi</span>
                    </a>
                  </li>

                  <!-- Menu rekap kategori -->
                  <li>
                    <a class="sidebar-sublink <?= $is_lap_kategori ? 'active' : ''; ?>" href="../laporan/kategori.php">
                      <i class="bi bi-tags"></i> <span>Rekap Kategori</span>
                    </a>
                  </li>

                  <!-- Menu rekap lokasi -->
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
            <!-- Menu pengguna hanya dapat digunakan oleh Admin -->
            <li class="sidebar-heading">Sistem</li>
            <li class="sidebar-item">
              <a class="sidebar-link <?= $is_users ? 'active' : ''; ?>" href="../users/index.php">
                <i class="bi bi-people"></i> <span>Pengguna</span>
              </a>
            </li>
          <?php endif; ?>
        </ul>

        <!-- Bagian bawah sidebar untuk menampilkan informasi pengguna -->
        <div class="sidebar-footer">
          <div class="d-flex align-items-center justify-content-between">

            <!-- Menampilkan username dan role pengguna -->
            <div class="text-truncate me-2">
              <div class="text-dark fw-semibold small text-truncate">
                <?= htmlspecialchars($user_username); ?>
              </div>
              <span class="badge <?= $badge_class; ?> py-1 px-2 text-capitalize">
                <?= htmlspecialchars($user_role ?: 'Publik'); ?>
              </span>
            </div>

            <!-- Menampilkan tombol Logout jika sudah login -->
            <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true) : ?>
              <a href="../auth/logout.php"
                 class="btn btn-outline-danger btn-sm"
                 title="Logout"
                 onclick="return confirm('Apakah Anda yakin ingin keluar?');">
                <i class="bi bi-box-arrow-right"></i>
              </a>
            <?php else : ?>

              <!-- Menampilkan tombol Login jika belum login -->
              <a href="../auth/login.php"
                 class="btn btn-primary btn-sm"
                 title="Login">
                <i class="bi bi-box-arrow-in-right"></i>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </aside>

      <!-- Lapisan gelap untuk perangkat tablet dan HP ketika sidebar dibuka -->
      <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

      <!-- Struktur utama aplikasi yang berisi topbar dan konten -->
      <div class="app-main">

        <!-- Bagian atas aplikasi -->
        <header class="app-topbar">

          <!-- Bagian kiri topbar -->
          <div class="topbar-left">

            <!-- Tombol untuk membuka atau menutup sidebar -->
            <button class="topbar-toggler sidebar-toggle-btn"
                    id="sidebarToggle"
                    type="button"
                    aria-label="Toggle navigation"
                    title="Buka / Tutup Sidebar">
              <i class="bi bi-list"></i>
            </button>
          </div>

          <!-- Bagian kanan topbar -->
          <div class="topbar-right">

            <!-- Jika pengguna sudah login -->
            <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true) : ?>

              <!-- Menampilkan username dan role pengguna -->
              <span class="badge <?= $badge_class; ?> py-2 px-3 d-none d-sm-inline-flex align-items-center">
                <i class="bi bi-person-circle me-1"></i>
                <?= htmlspecialchars($user_username); ?>
                (<?= htmlspecialchars(ucfirst($user_role ?: 'Publik')); ?>)
              </span>

              <!-- Tombol logout -->
              <a href="../auth/logout.php"
                 class="btn btn-outline-danger btn-sm d-inline-flex align-items-center"
                 onclick="return confirm('Apakah Anda yakin ingin keluar?');">
                <i class="bi bi-box-arrow-right me-1"></i>
                <span class="d-none d-sm-inline">Logout</span>
              </a>
            <?php else : ?>

              <!-- Tombol login jika pengguna belum login -->
              <a href="../auth/login.php"
                 class="btn btn-success btn-sm">
                <i class="bi bi-box-arrow-in-right me-1"></i>
                Login
              </a>
            <?php endif; ?>
          </div>
        </header>

        <!-- Area tempat isi halaman ditampilkan -->
        <main class="app-content">
