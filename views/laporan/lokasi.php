<?php

include_once '../../app/app.php';
check_role(['admin', 'kepala']);

include_once '../../layout/header.php';

$sql_lokasi_base = "SELECT l.*, COUNT(b.id_barang) as total_barang, COALESCE(SUM(b.stok), 0) as total_stok
                    FROM lokasi l
                    LEFT JOIN barang b ON l.id_lokasi = b.id_lokasi
                    GROUP BY l.id_lokasi";

// Hitung total data
$sql_count_lokasi = "SELECT COUNT(*) AS total FROM ($sql_lokasi_base) AS sub";
$result_count_lokasi = mysqli_query($db, $sql_count_lokasi);
$total_data_lokasi = 0;
if ($result_count_lokasi) {
    $row_count_lokasi = mysqli_fetch_assoc($result_count_lokasi);
    $total_data_lokasi = (int) ($row_count_lokasi['total'] ?? 0);
}

// Pengaturan pagination
$limit = 10;
$total_pages_lokasi = max(1, (int) ceil($total_data_lokasi / $limit));

$current_page_lokasi = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$current_page_lokasi = ($current_page_lokasi !== false && $current_page_lokasi !== null)
    ? max(1, min($current_page_lokasi, $total_pages_lokasi))
    : 1;

$offset_lokasi = ($current_page_lokasi - 1) * $limit;

$pagination = [
    'page' => $current_page_lokasi,
    'total_pages' => $total_pages_lokasi,
    'total' => $total_data_lokasi,
    'from' => $total_data_lokasi > 0 ? $offset_lokasi + 1 : 0,
    'to' => min($offset_lokasi + $limit, $total_data_lokasi)
];

$pagination_params = [];

$data_lokasi = select_lokasi("SELECT l.*, COUNT(b.id_barang) as total_barang, COALESCE(SUM(b.stok), 0) as total_stok
                              FROM lokasi l
                              LEFT JOIN barang b ON l.id_lokasi = b.id_lokasi
                              GROUP BY l.id_lokasi
                              ORDER BY l.nama_lokasi ASC
                              LIMIT $limit OFFSET $offset_lokasi");

?>
<link rel="icon" type="image/png" href="../../img/logo_white.png">
<div class="container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Laporan Master Lokasi</h1>
            <p class="text-muted small mb-0">Rekapitulasi lokasi ruangan inventaris beserta total jenis barang dan stok</p>
        </div>
        <div>
            <a href="cetakpdf.php?tipe=lokasi" target="_blank" class="btn btn-danger shadow-sm">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export to PDF
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-white text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Lokasi / Ruangan</th>
                            <th>Total Jenis Barang</th>
                            <th>Total Stok Fisik</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php if (empty($data_lokasi)) : ?>
                            <tr>
                                <td colspan="4" class="text-muted py-4">Belum ada data lokasi.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = $offset_lokasi + 1; ?>
                            <?php foreach ($data_lokasi as $l) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($l['nama_lokasi']); ?></td>
                                    <td><?= $l['total_barang']; ?> Jenis</td>
                                    <td class="fw-bold"><?= $l['total_stok']; ?> Unit</td>
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
