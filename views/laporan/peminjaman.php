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


$query_peminjaman = "SELECT
    p.*,
    b.kode_barang,
    b.nama_barang,
    b.satuan,
    u.nama AS nama_petugas
FROM peminjaman p
LEFT JOIN barang b ON p.id_barang = b.id_barang
LEFT JOIN users u ON p.id_user = u.id_user
$where_sql
ORDER BY p.tanggal_pinjam DESC";

$result_peminjaman = mysqli_query($db, $query_peminjaman);
$data_peminjaman = [];

if ($result_peminjaman) {
    while ($row = mysqli_fetch_assoc($result_peminjaman)) {
        $data_peminjaman[] = $row;
    }
} else {
    die("Query laporan peminjaman gagal: " . mysqli_error($db));
}

?>

<div class="container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Laporan Peminjaman Barang</h1>
            <p class="text-muted small mb-0">Rekapitulasi riwayat peminjaman dan pengembalian barang</p>
        </div>
        <div>
            <a href="cetakpdf.php?tipe=peminjaman&tgl_mulai=<?= urlencode($tgl_mulai); ?>&tgl_sampai=<?= urlencode($tgl_sampai); ?>&status=<?= urlencode($status); ?>" target="_blank" class="btn btn-danger shadow-sm">
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
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-white text-center">
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
                    <tbody>
                        <?php if (empty($data_peminjaman)) : ?>
                            <tr>
                                <td colspan="9" class="text-muted py-4">Tidak ada data peminjaman yang cocok.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($data_peminjaman as $p) : ?>
                                <tr class="text-center">
                                    <td><?= $no++; ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($p['nama_peminjam']); ?></td>
                                    <td>[<?= htmlspecialchars($p['kode_barang']); ?>] <?= htmlspecialchars($p['nama_barang']); ?></td>
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
        </div>
    </div>
</div>

<script>
// PDF handled by cetakpdf.php
</script>

<?php include_once '../../layout/footer.php'; ?>
