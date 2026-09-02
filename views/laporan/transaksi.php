<?php

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

$data_transaksi = select_transaksi("SELECT t.*, b.kode_barang, b.nama_barang, b.satuan, u.nama AS nama_petugas 
                                    FROM transaksi t 
                                    LEFT JOIN barang b ON t.id_barang = b.id_barang 
                                    LEFT JOIN users u ON t.id_user = u.id_user 
                                    $where_sql 
                                    ORDER BY t.tanggal DESC");

?>

<!-- html2pdf.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Laporan Transaksi Barang</h1>
            <p class="text-muted">Rekapitulasi mutasi barang masuk (pembelian/restock) dan barang keluar</p>
        </div>
        <div>
            <button type="button" onclick="exportLaporanPDF()" class="btn btn-danger shadow-sm">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export to PDF
            </button>
        </div>
    </div>
    <hr>

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
    <div class="card shadow-sm" id="area-laporan">
        <div class="card-body p-4">
            <div class="text-center mb-4 d-none" id="kop-laporan">
                <h3 class="fw-bold mb-1">LAPORAN MUTASI TRANSAKSI BARANG</h3>
                <p class="text-muted mb-0">Sistem Manajemen & Pengelolaan Sarana Prasarana</p>
                <small>Tanggal Export: <?= date('d F Y, H:i'); ?> WIB</small>
                <hr style="border-top: 2px solid #000; margin-top: 10px;">
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark text-center">
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
                    <tbody class="text-center">
                        <?php if (empty($data_transaksi)) : ?>
                            <tr>
                                <td colspan="7" class="text-muted py-4">Tidak ada data transaksi yang cocok.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($data_transaksi as $t) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= date('d/m/Y', strtotime($t['tanggal'])); ?></td>
                                    <td class="text-start">[<?= htmlspecialchars($t['kode_barang']); ?>] <?= htmlspecialchars($t['nama_barang']); ?></td>
                                    <td>
                                        <?= (strtolower($t['jenis_transaksi']) === 'masuk') ? '<span class="badge bg-success">Masuk</span>' : '<span class="badge bg-danger">Keluar</span>'; ?>
                                    </td>
                                    <td class="fw-bold">
                                        <?= (strtolower($t['jenis_transaksi']) === 'masuk' ? '+' : '-') . $t['jumlah']; ?> <?= htmlspecialchars($t['satuan']); ?>
                                    </td>
                                    <td class="text-start"><?= htmlspecialchars($t['keterangan']); ?></td>
                                    <td><?= htmlspecialchars($t['nama_petugas'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tanda Tangan Khusus PDF -->
            <div class="row mt-4 d-none" id="ttd-laporan">
                <div class="col-7"></div>
                <div class="col-5 text-center">
                    <p class="mb-4">Mengetahui,<br><strong>Kepala Bagian Sarana & Prasarana</strong></p>
                    <p class="mt-4 fw-bold text-decoration-underline">( .................................................. )</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportLaporanPDF() {
    const kop = document.getElementById('kop-laporan');
    const ttd = document.getElementById('ttd-laporan');
    kop.classList.remove('d-none');
    ttd.classList.remove('d-none');

    const element = document.getElementById('area-laporan');
    const opt = {
        margin:       10,
        filename:     'Laporan_Transaksi_<?= date("Ymd_His"); ?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
    };

    html2pdf().set(opt).from(element).save().then(() => {
        kop.classList.add('d-none');
        ttd.classList.add('d-none');
    });
}
</script>

<?php include_once '../../layout/footer.php'; ?>
