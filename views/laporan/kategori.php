<?php

include_once '../../app/app.php';
check_role(['admin', 'kepala']);

include_once '../../layout/header.php';

$sql_kategori_base = "SELECT k.*, COUNT(b.id_barang) as total_barang, COALESCE(SUM(b.stok), 0) as total_stok
                      FROM kategori k
                      LEFT JOIN barang b ON k.id_kategori = b.id_kategori
                      GROUP BY k.id_kategori";

// Hitung total data
$sql_count_kategori = "SELECT COUNT(*) AS total FROM ($sql_kategori_base) AS sub";
$result_count_kategori = mysqli_query($db, $sql_count_kategori);
$total_data_kategori = 0;
if ($result_count_kategori) {
    $row_count_kategori = mysqli_fetch_assoc($result_count_kategori);
    $total_data_kategori = (int) ($row_count_kategori['total'] ?? 0);
}

// Pengaturan pagination
$limit = 10;
$total_pages_kategori = max(1, (int) ceil($total_data_kategori / $limit));

$current_page_kategori = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$current_page_kategori = ($current_page_kategori !== false && $current_page_kategori !== null)
    ? max(1, min($current_page_kategori, $total_pages_kategori))
    : 1;

$offset_kategori = ($current_page_kategori - 1) * $limit;

$pagination = [
    'page' => $current_page_kategori,
    'total_pages' => $total_pages_kategori,
    'total' => $total_data_kategori,
    'from' => $total_data_kategori > 0 ? $offset_kategori + 1 : 0,
    'to' => min($offset_kategori + $limit, $total_data_kategori)
];

$pagination_params = [];

$data_kategori = select_kategori("SELECT k.*, COUNT(b.id_barang) as total_barang, COALESCE(SUM(b.stok), 0) as total_stok
                                  FROM kategori k
                                  LEFT JOIN barang b ON k.id_kategori = b.id_kategori
                                  GROUP BY k.id_kategori
                                  ORDER BY k.nama_kategori ASC
                                  LIMIT $limit OFFSET $offset_kategori");

?>
<link rel="icon" type="image/png" href="../../img/logo_white.png">
<div class="container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Laporan Master Kategori</h1>
            <p class="text-muted small mb-0">Rekapitulasi kategori barang inventaris beserta total jenis barang dan stok</p>
        </div>
        <div>
            <a href="cetakpdf.php?tipe=kategori" target="_blank" class="btn btn-danger shadow-sm">
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
                            <th>Nama Kategori</th>
                            <th>Total Jenis Barang</th>
                            <th>Total Stok Fisik</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php if (empty($data_kategori)) : ?>
                            <tr>
                                <td colspan="4" class="text-muted py-4">Belum ada data kategori.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = $offset_kategori + 1; ?>
                            <?php foreach ($data_kategori as $k) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($k['nama_kategori']); ?></td>
                                    <td><?= $k['total_barang']; ?> Jenis</td>
                                    <td class="fw-bold"><?= $k['total_stok']; ?> Unit</td>
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
