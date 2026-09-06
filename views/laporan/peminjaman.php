<?php

include_once '../../app/app.php';
check_role(['admin', 'kepala']);

include_once '../../layout/header.php';

$tgl_mulai  = isset($_GET['tgl_mulai']) && $_GET['tgl_mulai'] !== '' ? mysqli_real_escape_string($db, $_GET['tgl_mulai']) : '';
$tgl_sampai = isset($_GET['tgl_sampai']) && $_GET['tgl_sampai'] !== '' ? mysqli_real_escape_string($db, $_GET['tgl_sampai']) : '';
$status     = isset($_GET['status']) && $_GET['status'] !== '' ? mysqli_real_escape_string($db, $_GET['status']) : '';

$where_clauses = [];
if ($tgl_mulai !== '' && $tgl_sampai !== '') {
    $where_clauses[] = "p.tanggal_pinjam BETWEEN '$tgl_mulai' AND '$tgl_sampai'";
} elseif ($tgl_mulai !== '') {
    $where_clauses[] = "p.tanggal_pinjam >= '$tgl_mulai'";
} elseif ($tgl_sampai !== '') {
    $where_clauses[] = "p.tanggal_pinjam <= '$tgl_sampai'";
}

if ($status !== '') {
    $where_clauses[] = "p.status = '$status'";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

$data_peminjaman = select_peminjaman("SELECT p.*, b.kode_barang, b.nama_barang, b.satuan, u.nama AS nama_petugas 
                                      FROM peminjaman p 
                                      LEFT JOIN barang b ON p.id_barang = b.id_barang 
                                      LEFT JOIN users u ON p.id_user = u.id_user 
                                      $where_sql 
                                      ORDER BY p.tanggal_pinjam DESC");

?>

<!-- html2pdf.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<div class="container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Laporan Peminjaman Barang</h1>
            <p class="text-muted small mb-0">Rekapitulasi riwayat peminjaman dan pengembalian barang</p>
        </div>
        <div>
            <button type="button" onclick="exportLaporanPDF()" class="btn btn-danger shadow-sm">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export to PDF
            </button>
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
                    <label class="form-label fw-bold">Status Peminjaman</label>
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <option value="dipinjam" <?= ($status === 'dipinjam') ? 'selected' : ''; ?>>Dipinjam</option>
                        <option value="dikembalikan" <?= ($status === 'dikembalikan') ? 'selected' : ''; ?>>Dikembalikan</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-filter me-1"></i> Filter</button>
                    <a href="peminjaman.php" class="btn btn-secondary"><i class="bi bi-arrow-clockwise me-1"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Exportable Area -->
    <div class="card shadow-sm" id="area-laporan">
        <div class="card-body p-4">
            <div class="text-center mb-4 d-none" id="kop-laporan">
                <h3 class="fw-bold mb-1">LAPORAN PEMINJAMAN BARANG</h3>
                <p class="text-muted mb-0">Sistem Manajemen & Pengelolaan Sarana Prasarana</p>
                <small>Tanggal Export: <?= date('d F Y, H:i'); ?> WIB</small>
                <hr style="border-top: 2px solid #000; margin-top: 10px;">
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Peminjam</th>
                            <th>Barang</th>
                            <th>Jumlah</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                            <th>Kondisi Kembali</th>
                            <th>Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php if (empty($data_peminjaman)) : ?>
                            <tr>
                                <td colspan="9" class="text-muted py-4">Tidak ada data peminjaman yang cocok.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($data_peminjaman as $p) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td class="text-start fw-semibold"><?= htmlspecialchars($p['nama_peminjam']); ?></td>
                                    <td class="text-start">[<?= htmlspecialchars($p['kode_barang']); ?>] <?= htmlspecialchars($p['nama_barang']); ?></td>
                                    <td class="fw-bold"><?= $p['jumlah']; ?> <?= htmlspecialchars($p['satuan']); ?></td>
                                    <td><?= date('d/m/Y', strtotime($p['tanggal_pinjam'])); ?></td>
                                    <td><?= !empty($p['tanggal_kembali']) ? date('d/m/Y', strtotime($p['tanggal_kembali'])) : '-'; ?></td>
                                    <td>
                                        <?= ($p['status'] === 'dipinjam') ? '<span class="badge bg-warning text-dark">Dipinjam</span>' : '<span class="badge bg-success">Dikembalikan</span>'; ?>
                                    </td>
                                    <td><?= htmlspecialchars($p['kondisi_kembali'] ?? '-'); ?></td>
                                    <td><?= htmlspecialchars($p['nama_petugas'] ?? '-'); ?></td>
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
        filename:     'Laporan_Peminjaman_<?= date("Ymd_His"); ?>.pdf',
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
