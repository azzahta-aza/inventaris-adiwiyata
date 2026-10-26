<?php

include_once '../../app/app.php';
check_role(['admin', 'kepala']);

include_once '../../layout/header.php';

$tgl_mulai  = isset($_GET['tgl_mulai']) && $_GET['tgl_mulai'] !== '' ? mysqli_real_escape_string($db, $_GET['tgl_mulai']) : '';
$tgl_sampai = isset($_GET['tgl_sampai']) && $_GET['tgl_sampai'] !== '' ? mysqli_real_escape_string($db, $_GET['tgl_sampai']) : '';
$jenis      = isset($_GET['jenis']) && $_GET['jenis'] !== '' ? mysqli_real_escape_string($db, $_GET['jenis']) : '';

$where_clauses = [];
if ($tgl_mulai !== '' && $tgl_sampai !== '') {
    $where_clauses[] = "t.tanggal BETWEEN '$tgl_mulai' AND '$tgl_sampai'";
} elseif ($tgl_mulai !== '') {
    $where_clauses[] = "t.tanggal >= '$tgl_mulai'";
} elseif ($tgl_sampai !== '') {
    $where_clauses[] = "t.tanggal <= '$tgl_sampai'";
}

if ($jenis !== '') {
    $where_clauses[] = "LOWER(t.jenis_transaksi) = '" . strtolower($jenis) . "'";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Hitung total data
$sql_count_transaksi = "SELECT COUNT(*) AS total
                        FROM transaksi t
                        LEFT JOIN barang b ON t.id_barang = b.id_barang
                        LEFT JOIN users u ON t.id_user = u.id_user
                        $where_sql";
$result_count_transaksi = mysqli_query($db, $sql_count_transaksi);
$total_data_transaksi = 0;
if ($result_count_transaksi) {
    $row_count_transaksi = mysqli_fetch_assoc($result_count_transaksi);
    $total_data_transaksi = (int) ($row_count_transaksi['total'] ?? 0);
}

// Pengaturan pagination
$limit = 10;
$total_pages_transaksi = max(1, (int) ceil($total_data_transaksi / $limit));

$current_page_transaksi = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$current_page_transaksi = ($current_page_transaksi !== false && $current_page_transaksi !== null)
    ? max(1, min($current_page_transaksi, $total_pages_transaksi))
    : 1;

$offset_transaksi = ($current_page_transaksi - 1) * $limit;

$pagination = [
    'page' => $current_page_transaksi,
    'total_pages' => $total_pages_transaksi,
    'total' => $total_data_transaksi,
    'from' => $total_data_transaksi > 0 ? $offset_transaksi + 1 : 0,
    'to' => min($offset_transaksi + $limit, $total_data_transaksi)
];

$pagination_params = [];
if ($tgl_mulai !== '') {
    $pagination_params['tgl_mulai'] = $tgl_mulai;
}
if ($tgl_sampai !== '') {
    $pagination_params['tgl_sampai'] = $tgl_sampai;
}
if ($jenis !== '') {
    $pagination_params['jenis'] = $jenis;
}

$data_transaksi = select_transaksi("SELECT t.*, b.kode_barang, b.nama_barang, b.satuan, u.nama AS nama_petugas
                                    FROM transaksi t
                                    LEFT JOIN barang b ON t.id_barang = b.id_barang
                                    LEFT JOIN users u ON t.id_user = u.id_user
                                    $where_sql
                                    ORDER BY t.tanggal DESC, t.id_transaksi DESC
                                    LIMIT $limit OFFSET $offset_transaksi");

?>
<link rel="icon" type="image/png" href="../../img/logo_white.png">
<div class="container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Laporan Transaksi Barang</h1>
            <p class="text-muted small mb-0">Rekapitulasi mutasi barang masuk (pembelian/restock) dan barang keluar</p>
        </div>
        <div>
            <a href="cetakpdf.php?tipe=transaksi&tgl_mulai=<?= urlencode($tgl_mulai); ?>&tgl_sampai=<?= urlencode($tgl_sampai); ?>&jenis=<?= urlencode($jenis); ?>" target="_blank" class="btn btn-danger shadow-sm">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export to PDF
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form action="" method="get" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Dari Tanggal</label>
                    <input type="date" name="tgl_mulai" class="form-control" value="<?= htmlspecialchars($tgl_mulai); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Sampai Tanggal</label>
                    <input type="date" name="tgl_sampai" class="form-control" value="<?= htmlspecialchars($tgl_sampai); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Jenis Transaksi</label>
                    <select name="jenis" class="form-select">
                        <option value="">-- Semua Jenis --</option>
                        <option value="Masuk" <?= (strtolower($jenis) === 'masuk') ? 'selected' : ''; ?>>Barang Masuk</option>
                        <option value="Keluar" <?= (strtolower($jenis) === 'keluar') ? 'selected' : ''; ?>>Barang Keluar</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-filter me-1"></i> Filter</button>
                    <a href="transaksi.php" class="btn btn-secondary"><i class="bi bi-arrow-clockwise me-1"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Exportable Area -->
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-white text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th>Tanggal</th>
                            <th>Nama Barang</th>
                            <th>Jenis Transaksi</th>
                            <th>Jumlah</th>
                            <th>Keterangan</th>
                            <th>Petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data_transaksi)) : ?>
                            <tr>
                                <td colspan="7" class="text-muted py-4">Tidak ada data transaksi yang cocok.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = $offset_transaksi + 1; ?>
                            <?php foreach ($data_transaksi as $t) : ?>
                                <tr class="text-center">
                                    <td><?= $no++; ?></td>
                                    <td><?= date('d/m/Y', strtotime($t['tanggal'])); ?></td>
                                    <td>[<?= htmlspecialchars($t['kode_barang']); ?>] <?= htmlspecialchars($t['nama_barang']); ?></td>
                                    <td>
                                        <?= (strtolower($t['jenis_transaksi']) === 'masuk') ? '<span class="badge bg-success">Masuk</span>' : '<span class="badge bg-danger">Keluar</span>'; ?>
                                    </td>
                                    <td class="fw-bold">
                                        <?= (strtolower($t['jenis_transaksi']) === 'masuk' ? '+' : '-') . $t['jumlah']; ?> <?= htmlspecialchars($t['satuan']); ?>
                                    </td>
                                    <td><?= htmlspecialchars($t['keterangan']); ?></td>
                                    <td><?= htmlspecialchars($t['nama_petugas'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php include '../../layout/pagination.php'; ?>
        </div>
    </div>
</div>

<script>
// PDF handled by cetakpdf.php
</script>

<?php include_once '../../layout/footer.php'; ?>
