<?php
include '../../app/app.php';

// Dashboard boleh dilihat oleh guest.
// Jika sudah login, tetap cek role.
if (is_logged_in()) {
    check_role(['admin', 'petugas', 'kepala']);
}

$title = 'Dashboard';

// Ambil data dashboard dari controller (Defense-in-depth)
$summary = get_dashboard_summary();
$latest_masuk = get_dashboard_latest_transaksi('masuk', 5);
$latest_keluar = get_dashboard_latest_transaksi('keluar', 5);
$latest_pinjam = get_dashboard_latest_peminjaman(5);
$low_stock = get_dashboard_low_stock(5, 5);
$chart_data = get_dashboard_chart_data(7);

// Tentukan target link & permission berdasarkan role
$user_role = strtolower($_SESSION['role'] ?? '');
$is_admin = ($user_role === 'admin');
$is_petugas = ($user_role === 'petugas');
$is_kepala = ($user_role === 'kepala');
$is_guest = !$is_admin && !$is_petugas && !$is_kepala;

$role_label = 'Pengguna';
$role_badge_class = 'bg-secondary';
$role_icon = 'bi-person';

if ($is_admin) {
    $role_label = 'Administrator';
    $role_badge_class = 'bg-primary';
    $role_icon = 'bi-shield-check';
} elseif ($is_petugas) {
    $role_label = 'Petugas Operasional';
    $role_badge_class = 'bg-success';
    $role_icon = 'bi-person-badge';
} elseif ($is_kepala) {
    $role_label = 'Kepala Bagian';
    $role_badge_class = 'bg-warning text-dark';
    $role_icon = 'bi-person-check';
}

$link_transaksi = $is_guest
    ? '#'
    : (($is_kepala) ? '../laporan/transaksi.php' : '../transaksi/index.php');

$link_peminjaman = $is_guest
    ? '#'
    : (($is_kepala) ? '../laporan/peminjaman.php' : '../peminjaman/index.php');

$link_barang = $is_guest
    ? '#'
    : (($is_admin) ? '../barang/index.php' : (($is_kepala) ? '../laporan/barang.php' : '#'));

// Hitung persentase kondisi barang
$tot_brg = (int)($summary['total_jenis_barang'] ?? 0);
$layak_cnt = (int)($summary['kondisi_layak'] ?? 0);
$perbaikan_cnt = (int)($summary['kondisi_perbaikan'] ?? 0);
$tidak_layak_cnt = (int)($summary['kondisi_tidak_layak'] ?? 0);

$layak_pct = $tot_brg > 0 ? round(($layak_cnt / $tot_brg) * 100) : 0;
$perbaikan_pct = $tot_brg > 0 ? round(($perbaikan_cnt / $tot_brg) * 100) : 0;
$tidak_layak_pct = $tot_brg > 0 ? round(($tidak_layak_cnt / $tot_brg) * 100) : 0;

// Format waktu & tanggal Indonesia
$hour = (int)date('H');
if ($hour >= 4 && $hour < 11) {
    $salam = 'Selamat Pagi';
} elseif ($hour >= 11 && $hour < 15) {
    $salam = 'Selamat Siang';
} elseif ($hour >= 15 && $hour < 18) {
    $salam = 'Selamat Sore';
} else {
    $salam = 'Selamat Malam';
}

$hari_arr = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$bulan_arr = ['01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'];
$nama_hari = $hari_arr[date('l')] ?? date('l');
$tgl_num = date('d');
$bln_num = $bulan_arr[date('m')] ?? date('M');
$thn_num = date('Y');
$tanggal_formatted = "$nama_hari, $tgl_num $bln_num $thn_num";

include '../../layout/header.php';
?>
<link rel="icon" type="image/png" href="../../img/logo_white.png">
<style>
  .stat-card {
    border: 1px solid #edf2f7;
    border-radius: 12px;
    background: #ffffff;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    height: 100%;
  }
  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
  }
  .stat-icon-box {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    flex-shrink: 0;
  }
  .welcome-card {
    background: #ffffff;
    border: 1px solid #edf2f7;
    border-radius: 12px;
  }
  .card-table-header {
    background-color: #f8fafc;
    border-bottom: 1px solid #edf2f7;
  }
  .dashboard-table thead th {
    background-color: #f8fafc;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    font-weight: 600;
    border-top: none;
    border-bottom: 1px solid #e2e8f0;
    padding-top: 0.65rem;
    padding-bottom: 0.65rem;
  }
  .chart-wrapper {
    position: relative;
    height: 270px;
    width: 100%;
  }
  @media (max-width: 576px) {
    .stat-card .card-body {
      padding: 0.9rem;
    }
    .chart-wrapper {
      height: 220px;
    }
  }
</style>
<link rel="icon" type="image/png" href="../../img/logo_white.png">
<div class="container-fluid px-0">

  <!-- 1. WELCOME & QUICK ACTIONS HEADER CARD -->
  <div class="card welcome-card shadow-sm border-0 mb-4">
    <div class="card-body p-3 p-md-4">
      <div class="row align-items-center g-3">
        <!-- Title & Role Info -->
        <div class="col-12 col-xl-7">
          <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-semibold">
              <i class="bi <?= $role_icon; ?> me-1"></i><?= htmlspecialchars($role_label); ?>
            </span>
            <span class="text-muted small">
              <i class="bi bi-calendar3 me-1"></i><?= $tanggal_formatted; ?>
            </span>
          </div>
          <h4 class="fw-bold text-dark mb-1">
            Selamat Datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Pengguna'); ?>
          </h4>
          <p class="text-muted small mb-0">
            Ringkasan data inventaris, ketersediaan stok barang, dan aktivitas transaksi operasional.
          </p>
        </div>

        <!-- Quick Action Buttons -->
        <div class="col-12 col-xl-5 text-xl-end">
          <div class="d-flex flex-wrap gap-2 justify-content-start justify-content-xl-end">
            <?php if ($is_admin) : ?>
              <a href="../barang/tambah.php" class="btn btn-primary btn-sm px-3 fw-medium shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Barang
              </a>
              <a href="../transaksi/tambah.php" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-arrow-down-left me-1"></i> Catat Transaksi
              </a>
              <a href="../peminjaman/tambah.php" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-arrow-repeat me-1"></i> Catat Peminjaman
              </a>
              <a href="../laporan/barang.php" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-file-earmark-text me-1"></i> Laporan
              </a>
            <?php elseif ($is_petugas) : ?>
              <a href="../transaksi/tambah.php" class="btn btn-primary btn-sm px-3 fw-medium shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Catat Transaksi
              </a>
              <a href="../peminjaman/tambah.php" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-plus-circle me-1"></i> Catat Peminjaman
              </a>
              <a href="../peminjaman/index.php" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-arrow-repeat me-1"></i> Kelola Pinjam
              </a>
            <?php elseif ($is_kepala) : ?>
              <a href="../laporan/barang.php" class="btn btn-primary btn-sm px-3 fw-medium shadow-sm">
                <i class="bi bi-box-seam me-1"></i> Lap. Barang
              </a>
              <a href="../laporan/transaksi.php" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-journal-text me-1"></i> Lap. Transaksi
              </a>
              <a href="../laporan/peminjaman.php" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-arrow-repeat me-1"></i> Lap. Pinjam
              </a>
              <a href="../laporan/cetakpdf.php" target="_blank" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-printer me-1"></i> Cetak PDF
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

<!-- 2. DASHBOARD SUMMARY -->
<div class="row g-4 mb-4">
  <!-- KIRI : KONDISI & STOK INVENTARIS (CARD BESAR) -->
    <div class="col-12 col-lg-7">
    <div class="card shadow-sm border-0 rounded-3 h-100">
    <div class="card-header card-table-header py-3">
            <h6 class="fw-bold mb-0 text-dark">
       Kondisi & Stok Inventaris
            </h6>
            <span class="text-muted small">
       Distribusi kelayakan & ketersediaan barang
            </span>
            </div>
            <div class="card-body p-3">

      <!-- Layak -->
        <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1 small">
             <span class="text-muted">Layak</span>
             <span class="fw-semibold text-dark">
             <?= $layak_cnt; ?> jenis (<?= $layak_pct; ?>%)
             </span>
             </div>

        <div class="progress" style="height:7px;">
             <div class="progress-bar bg-success"
              style="width:<?= $layak_pct; ?>%">
             </div>
             </div>
             </div>

      <!-- Perbaikan -->
        <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1 small">
             <span class="text-muted">Perbaikan</span>
             <span class="fw-semibold text-dark">
             <?= $perbaikan_cnt; ?> jenis (<?= $perbaikan_pct; ?>%)
             </span>
             </div>
         <div class="progress" style="height:7px;">
             <div class="progress-bar bg-warning"
              style="width:<?= $perbaikan_pct; ?>%">
             </div>
             </div>
             </div>

       <!-- Tidak Layak -->
         <div class="mb-3">
         <div class="d-flex justify-content-between align-items-center mb-1 small">
             <span class="text-muted">Tidak Layak</span>
             <span class="fw-semibold text-dark">
             <?= $tidak_layak_cnt; ?> jenis (<?= $tidak_layak_pct; ?>%)
             </span>
             </div>
         <div class="progress" style="height:7px;">
             <div class="progress-bar bg-danger"
              style="width:<?= $tidak_layak_pct; ?>%">
             </div>
             </div>
             </div>

         <hr class="my-3 text-muted opacity-25">
         <div class="d-flex justify-content-between align-items-center mb-2">
             <span class="fw-semibold text-dark small">
                Peringatan Stok Rendah (≤5)
             </span>
             </div>

         <?php if (!empty($low_stock)) : ?>
          <div class="list-group list-group-flush border-0">

         <?php foreach ($low_stock as $item) : ?>
          <div class="list-group-item px-0 py-2 border-0 d-flex justify-content-between align-items-center">
          <div class="me-2 text-truncate">
          <span class="fw-semibold text-dark d-block text-truncate small">
          <?= htmlspecialchars($item['nama_barang']); ?>
          </span>

          <span class="text-muted extra-small">
         <?= htmlspecialchars($item['kode_barang']); ?>
          </span>
          </div>
          <span class="badge <?= ((int)$item['stok'] === 0) ? 'bg-danger' : 'bg-warning text-dark'; ?> rounded-pill">
         <?= (int)$item['stok']; ?> <?= htmlspecialchars($item['satuan']); ?>
          </span>
          </div>

         <?php endforeach; ?>
         </div>
         <?php else : ?>
          <div class="alert alert-success py-2 px-3 mb-0 rounded-3 small">
            Semua stok barang dalam kondisi aman.
          </div>

         <?php endif; ?>
           </div>
        </div>
    </div>

    <!-- KANAN : 4 CARD KPI (2x2) -->
     <div class="col-12 col-lg-5">
     <div class="row g-3">

    <!--  Card 1  -->
     <div class="col-6">
     <div class="card stat-card shadow-sm border h-100">
     <div class="card-body p-3">

    <div class="d-flex justify-content-between align-items-center">
    <span class="text-muted small fw-semibold">

    Transaksi Keluar
    </span>
    <div class="stat-icon-box">
    <i class="bi bi-box-arrow-up-right"></i>
    </div>
    </div>

    <div class="mt-3 mb-1">
    <span class="fs-3 fw-bold text-dark">
    <?= number_format((int)($summary['count_keluar'] ?? 0)); ?>
    </span>
    </div>
    <span class="text-muted small">transaksi</span>
    <hr>
    <small class="text-muted">Total unit</small><br>
    <strong>
     <?= number_format((int)($summary['qty_keluar'] ?? 0)); ?> unit
    </strong>
    </div>
    </div>
    </div>

   <!--  Card 2 -->
            <div class="col-6">

                <div class="card stat-card shadow-sm border h-100">

                    <div class="card-body p-3">

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small fw-semibold">
                                Transaksi Masuk
                            </span>

                            <div class="stat-icon-box">
                                <i class="bi bi-box-arrow-in-down"></i>
                            </div>
                        </div>

                        <div class="mt-3 mb-1">
                            <span class="fs-3 fw-bold text-dark">
                                <?= number_format((int)($summary['count_masuk'] ?? 0)); ?>
                            </span>
                        </div>

                        <span class="text-muted small">transaksi</span>

                        <hr>

                        <small class="text-muted">Total unit</small><br>

                        <strong>
                            <?= number_format((int)($summary['qty_masuk'] ?? 0)); ?> unit
                        </strong>

                    </div>

                </div>

            </div>

            <!--  Card 3  -->
            <div class="col-6">

                <div class="card stat-card shadow-sm border h-100">

                    <div class="card-body p-3">

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small fw-semibold">
                                Total Barang
                            </span>

                            <div class="stat-icon-box">
                                <i class="bi bi-boxes"></i>
                            </div>
                        </div>

                        <div class="mt-3 mb-1">
                            <span class="fs-3 fw-bold text-dark">
                                <?= number_format((int)($summary['total_jenis_barang'] ?? 0)); ?>
                            </span>
                        </div>
                        <span class="text-muted small">jenis barang</span>
                        <hr>
                        <small class="text-muted">Total stok</small><br>
                        <strong>
                            <?= number_format((int)($summary['total_stok_barang'] ?? 0)); ?> unit
                        </strong>
                    </div>
                </div>
            </div>

            <!--  Card 4  -->
            <div class="col-6">
                <div class="card stat-card shadow-sm border h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small fw-semibold">
                                Peminjaman Aktif
                            </span>
                            <div class="stat-icon-box">
                                <i class="bi bi-clock-history"></i>
                            </div>
                        </div>
                        <div class="mt-3 mb-1">
                            <span class="fs-3 fw-bold text-dark">
                                <?= number_format((int)($summary['peminjaman_aktif'] ?? 0)); ?>
                            </span>
                        </div>
                        <span class="text-muted small">peminjaman</span>
                        <hr>
                        <small class="text-muted">Sedang dipinjam</small><br>
                        <strong>
                            <?= number_format((int)($summary['qty_dipinjam'] ?? 0)); ?> unit
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

  <!-- 3. RECENT TRANSACTIONS (MASUK & KELUAR) -->
  <div class="row g-3 g-md-4 mb-4">

    <!-- Transaksi Masuk Terkini -->
    <div class="col-12 col-lg-6">
      <div class="card shadow-sm border-0 rounded-3 h-100">
        <div class="card-header card-table-header d-flex align-items-center justify-content-between py-3">
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-circle p-2 d-inline-flex">
              <i class="bi bi-box-arrow-in-down fs-6"></i>
            </span>
            <div>
              <h6 class="fw-bold mb-0 text-dark">Transaksi Masuk Terkini</h6>
              <span class="text-muted small">5 aktivitas barang masuk terbaru</span>
            </div>
          </div>
          <?php if (!$is_guest) : ?>
            <a href="<?= $link_transaksi; ?>" class="btn btn-outline-success btn-sm rounded-pill px-3 py-1">
              Lihat Semua <i class="bi bi-arrow-right"></i>
            </a>
          <?php endif; ?>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 dashboard-table">
              <thead>
                <tr>
                  <th class="ps-3">Tanggal</th>
                  <th>Barang</th>
                  <th class="text-center">Jumlah</th>
                  <th class="pe-3">Keterangan</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($latest_masuk)) : ?>
                  <?php foreach ($latest_masuk as $tx) : ?>
                    <tr>
                      <td class="ps-3 text-nowrap text-muted small">
                        <i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y', strtotime($tx['tanggal'])); ?>
                      </td>
                      <td>
                        <strong class="text-dark d-block text-truncate small" style="max-width: 170px;">
                          <?= htmlspecialchars($tx['nama_barang'] ?? '-'); ?>
                        </strong>
                        <span class="text-muted extra-small"><?= htmlspecialchars($tx['kode_barang'] ?? ''); ?></span>
                      </td>
                      <td class="text-center text-nowrap">
                        <span class="badge bg-success">
                          +<?= number_format((int)$tx['jumlah']); ?> <?= htmlspecialchars($tx['satuan'] ?? ''); ?>
                        </span>
                      </td>
                      <td class="pe-3 text-muted small text-truncate" style="max-width: 130px;">
                        <?= htmlspecialchars($tx['keterangan'] ?? '-'); ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else : ?>
                  <tr>
                    <td colspan="4" class="text-center py-4 text-muted">
                      <i class="bi bi-inbox fs-2 d-block mb-1 text-black-50"></i>
                      <span class="small">Belum ada transaksi barang masuk tercatat</span>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Transaksi Keluar Terkini -->
    <div class="col-12 col-lg-6">
      <div class="card shadow-sm border-0 rounded-3 h-100">
        <div class="card-header card-table-header d-flex align-items-center justify-content-between py-3">
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-circle p-2 d-inline-flex">
              <i class="bi bi-box-arrow-up-right fs-6"></i>
            </span>
            <div>
              <h6 class="fw-bold mb-0 text-dark">Transaksi Keluar Terkini</h6>
              <span class="text-muted small">5 aktivitas barang keluar terbaru</span>
            </div>
          </div>
          <a href="<?= $link_transaksi; ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1">
            Lihat Semua <i class="bi bi-arrow-right"></i>
          </a>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 dashboard-table">
              <thead>
                <tr>
                  <th class="ps-3">Tanggal</th>
                  <th>Barang</th>
                  <th class="text-center">Jumlah</th>
                  <th class="pe-3">Keterangan</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($latest_keluar)) : ?>
                  <?php foreach ($latest_keluar as $tx) : ?>
                    <tr>
                      <td class="ps-3 text-nowrap text-muted small">
                        <i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y', strtotime($tx['tanggal'])); ?>
                      </td>
                      <td>
                        <strong class="text-dark d-block text-truncate small" style="max-width: 170px;">
                          <?= htmlspecialchars($tx['nama_barang'] ?? '-'); ?>
                        </strong>
                        <span class="text-muted extra-small"><?= htmlspecialchars($tx['kode_barang'] ?? ''); ?></span>
                      </td>
                      <td class="text-center text-nowrap">
                        <span class="badge bg-danger">
                          -<?= number_format((int)$tx['jumlah']); ?> <?= htmlspecialchars($tx['satuan'] ?? ''); ?>
                        </span>
                      </td>
                      <td class="pe-3 text-muted small text-truncate" style="max-width: 130px;">
                        <?= htmlspecialchars($tx['keterangan'] ?? '-'); ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else : ?>
                  <tr>
                    <td colspan="4" class="text-center py-4 text-muted">
                      <i class="bi bi-inbox fs-2 d-block mb-1 text-black-50"></i>
                      <span class="small">Belum ada transaksi barang keluar tercatat</span>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- 4. RECENT LOANS (PEMINJAMAN TERKINI) -->
  <div class="row g-3 g-md-4 mb-4">
    <div class="col-12">
      <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-table-header d-flex align-items-center justify-content-between py-3">
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-circle p-2 d-inline-flex">
              <i class="bi bi-arrow-repeat fs-6"></i>
            </span>
            <div>
              <h6 class="fw-bold mb-0 text-dark">Aktivitas Peminjaman Terkini</h6>
              <span class="text-muted small">5 data peminjaman barang terakhir</span>
            </div>
          </div>
          <a href="<?= $link_peminjaman; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1">
            Lihat Peminjaman <i class="bi bi-arrow-right"></i>
          </a>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 dashboard-table">
              <thead>
                <tr>
                  <th class="ps-3">Tgl Pinjam</th>
                  <th>Peminjam</th>
                  <th>Barang</th>
                  <th class="text-center">Jumlah</th>
                  <th>Status</th>
                  <th class="pe-3">Tgl Kembali</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($latest_pinjam)) : ?>
                  <?php foreach ($latest_pinjam as $pinjam) : ?>
                    <?php
                      $is_dipinjam = strtolower($pinjam['status'] ?? '') === 'dipinjam';
                    ?>
                    <tr>
                      <td class="ps-3 text-nowrap text-muted small">
                        <i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y', strtotime($pinjam['tanggal_pinjam'])); ?>
                      </td>
                      <td>
                        <strong class="text-dark d-block small">
                          <?= htmlspecialchars($pinjam['nama_peminjam'] ?? '-'); ?>
                        </strong>
                      </td>
                      <td>
                        <strong class="text-dark d-block text-truncate small" style="max-width: 220px;">
                          <?= htmlspecialchars($pinjam['nama_barang'] ?? '-'); ?>
                        </strong>
                        <span class="text-muted extra-small"><?= htmlspecialchars($pinjam['kode_barang'] ?? ''); ?></span>
                      </td>
                      <td class="text-center text-nowrap">
                        <span class="badge bg-secondary">
                          <?= number_format((int)$pinjam['jumlah']); ?> <?= htmlspecialchars($pinjam['satuan'] ?? ''); ?>
                        </span>
                      </td>
                      <td>
                        <?php if ($is_dipinjam) : ?>
                          <span class="badge bg-warning text-dark">
                            <i class="bi bi-clock me-1"></i> Dipinjam
                          </span>
                        <?php else : ?>
                          <span class="badge bg-success">
                            <i class="bi bi-check2-circle me-1"></i> Dikembalikan
                          </span>
                        <?php endif; ?>
                      </td>
                      <td class="pe-3 text-nowrap text-muted small">
                        <?= !empty($pinjam['tanggal_kembali']) ? date('d/m/Y', strtotime($pinjam['tanggal_kembali'])) : '<span class="text-black-50">-</span>'; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else : ?>
                  <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                      <i class="bi bi-inbox fs-2 d-block mb-1 text-black-50"></i>
                      <span class="small">Belum ada data peminjaman barang tercatat</span>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('txChart');
    if (!ctx) return;

    var chartLabels = <?= json_encode($chart_data['labels'] ?? []); ?>;
    var dataMasuk = <?= json_encode($chart_data['masuk'] ?? []); ?>;
    var dataKeluar = <?= json_encode($chart_data['keluar'] ?? []); ?>;

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: chartLabels,
        datasets: [
          {
            label: 'Barang Masuk',
            data: dataMasuk,
            backgroundColor: 'rgba(25, 135, 84, 0.85)',
            borderColor: '#198754',
            borderWidth: 1,
            borderRadius: 4,
            maxBarThickness: 32
          },
          {
            label: 'Barang Keluar',
            data: dataKeluar,
            backgroundColor: 'rgba(220, 53, 69, 0.85)',
            borderColor: '#dc3545',
            borderWidth: 1,
            borderRadius: 4,
            maxBarThickness: 32
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index',
          intersect: false
        },
        plugins: {
          legend: {
            position: 'top',
            align: 'end',
            labels: {
              boxWidth: 12,
              boxHeight: 12,
              usePointStyle: true,
              pointStyle: 'circle',
              font: {
                size: 12,
                weight: '500'
              }
            }
          },
          tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.9)',
            titleFont: { size: 13 },
            bodyFont: { size: 12 },
            padding: 10,
            cornerRadius: 8,
            callbacks: {
              label: function(context) {
                return ' ' + context.dataset.label + ': ' + context.parsed.y + ' unit';
              }
            }
          }
        },
        scales: {
          x: {
            grid: {
              display: false
            },
            ticks: {
              font: { size: 11 }
            }
          },
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            },
            ticks: {
              precision: 0,
              font: { size: 11 }
            }
          }
        }
      }
    });
  });
</script>

<?php include '../../layout/footer.php'; ?>
