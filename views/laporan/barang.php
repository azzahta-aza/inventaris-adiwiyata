<?php

include_once '../../app/app.php';
check_role(['admin', 'kepala']);

include_once '../../layout/header.php';

$filter_kategori = isset($_GET['id_kategori']) && $_GET['id_kategori'] !== '' ? (int)$_GET['id_kategori'] : '';
$filter_kondisi  = isset($_GET['kondisi']) && $_GET['kondisi'] !== '' ? mysqli_real_escape_string($db, $_GET['kondisi']) : '';

$where_clauses = [];
if ($filter_kategori !== '') {
    $where_clauses[] = "b.id_kategori = $filter_kategori";
}
if ($filter_kondisi !== '') {
    $where_clauses[] = "b.kondisi = '$filter_kondisi'";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

$data_barang = select_barang("SELECT b.*, k.nama_kategori, l.nama_lokasi
                              FROM barang b
                              LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                              LEFT JOIN lokasi l ON b.id_lokasi = l.id_lokasi
                              $where_sql
                              ORDER BY b.nama_barang ASC");

$data_kategori = select_kategori("SELECT * FROM kategori ORDER BY nama_kategori ASC");

?>

<div class="container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Laporan Data Barang</h1>
            <p class="text-muted small mb-0">Rekapitulasi inventaris seluruh barang, stok, dan kondisinya</p>
        </div>
        <div>
            <!-- Tombol Utama Export to PDF -->
            <a href="cetakpdf.php?tipe=barang&id_kategori=<?= $filter_kategori; ?>&kondisi=<?= urlencode($filter_kondisi); ?>" target="_blank" class="btn btn-danger shadow-sm">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export to PDF
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form action="" method="get" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Filter Kategori</label>
                    <select name="id_kategori" class="form-select">
                        <option value="">-- Semua Kategori --</option>
                        <?php foreach ($data_kategori as $k) : ?>
                            <option value="<?= $k['id_kategori']; ?>" <?= ($filter_kategori == $k['id_kategori']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($k['nama_kategori']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Filter Kondisi</label>
                    <select name="kondisi" class="form-select">
                        <option value="">-- Semua Kondisi --</option>
                        <option value="layak" <?= ($filter_kondisi === 'layak') ? 'selected' : ''; ?>>Layak</option>
                        <option value="tidak_layak" <?= ($filter_kondisi === 'tidak_layak') ? 'selected' : ''; ?>>Tidak Layak</option>
                        <option value="perbaikan" <?= ($filter_kondisi === 'perbaikan') ? 'selected' : ''; ?>>Perbaikan</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-filter me-1"></i> Filter</button>
                    <a href="barang.php" class="btn btn-secondary"><i class="bi bi-arrow-clockwise me-1"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Printable & Exportable Area -->
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-white text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Stok</th>
                            <th>Satuan</th>
                            <th>Kondisi</th>
                        </tr>
                    </thead>    
                    <tbody class="text-center">
                        <?php if (empty($data_barang)) : ?>
                            <tr>
                                <td colspan="8" class="text-muted py-4">Data tidak ditemukan.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = 1; $total_stok = 0; ?>
                            <?php foreach ($data_barang as $b) : $total_stok += (int)$b['stok']; ?>
                                <tr class="text-center">
                                    <td><?= $no++; ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($b['kode_barang']); ?></td>
                                    <td><?= htmlspecialchars($b['nama_barang']); ?></td>
                                    <td><?= htmlspecialchars($b['nama_kategori'] ?? '-'); ?></td>
                                    <td><?= htmlspecialchars($b['nama_lokasi'] ?? '-'); ?></td>
                                    <td class="fw-bold"><?= $b['stok']; ?></td>
                                    <td><?= htmlspecialchars($b['satuan']); ?></td>
                                    <td><?= ucfirst($b['kondisi']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-light fw-bold">
                                <td colspan="5" class="text-end">Total Unit Stok:</td>
                                <td colspan="3" class="text-start"><?= $total_stok; ?> Unit</td>
                            </tr>
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
