<?php

include_once '../../app/app.php';
check_role(['admin', 'kepala']);

include_once '../../layout/header.php';

$filter_kategori = isset($_GET['id_kategori']) && $_GET['id_kategori'] !== '' ? (int)$_GET['id_kategori'] : '';
$filter_kondisi  = isset($_GET['kondisi']) && $_GET['kondisi'] !== '' ? mysqli_real_escape_string($db, $_GET['kondisi']) : '';
$filter_lokasi = isset($_GET['id_lokasi']) && $_GET['id_lokasi'] !== '' ? (int)$_GET['id_lokasi'] : '';

$where_clauses = [];
if ($filter_kategori !== '') {
    $where_clauses[] = "b.id_kategori = $filter_kategori";
}
if ($filter_kondisi !== '') {
    $where_clauses[] = "b.kondisi = '$filter_kondisi'";
}
if ($filter_lokasi !== '') {
    $where_clauses[] = "b.id_lokasi = $filter_lokasi";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Hitung total data
$sql_count_barang = "SELECT COUNT(*) AS total
                     FROM barang b
                     LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                     LEFT JOIN lokasi l ON b.id_lokasi = l.id_lokasi
                     $where_sql";
$result_count_barang = mysqli_query($db, $sql_count_barang);
$total_data_barang = 0;
if ($result_count_barang) {
    $row_count_barang = mysqli_fetch_assoc($result_count_barang);
    $total_data_barang = (int) ($row_count_barang['total'] ?? 0);
}

// Pengaturan pagination
$limit = 10;
$total_pages_barang = max(1, (int) ceil($total_data_barang / $limit));

$current_page_barang = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$current_page_barang = ($current_page_barang !== false && $current_page_barang !== null)
    ? max(1, min($current_page_barang, $total_pages_barang))
    : 1;

$offset_barang = ($current_page_barang - 1) * $limit;

$pagination = [
    'page' => $current_page_barang,
    'total_pages' => $total_pages_barang,
    'total' => $total_data_barang,
    'from' => $total_data_barang > 0 ? $offset_barang + 1 : 0,
    'to' => min($offset_barang + $limit, $total_data_barang)
];

$pagination_params = [];
if ($filter_kategori !== '') {
    $pagination_params['id_kategori'] = $filter_kategori;
}
if ($filter_kondisi !== '') {
    $pagination_params['kondisi'] = $filter_kondisi;
}
if ($filter_lokasi !== '') {
    $pagination_params['id_lokasi'] = $filter_lokasi;
}

$data_barang = select_barang("SELECT b.*, k.nama_kategori, l.nama_lokasi
                              FROM barang b
                              LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                              LEFT JOIN lokasi l ON b.id_lokasi = l.id_lokasi
                              $where_sql
                              ORDER BY b.nama_barang ASC
                              LIMIT $limit OFFSET $offset_barang");

$data_kategori = select_kategori("SELECT * FROM kategori ORDER BY nama_kategori ASC");

$query_lokasi = mysqli_query( $db, "SELECT id_lokasi, nama_lokasi FROM lokasi ORDER BY nama_lokasi ASC");

$data_lokasi = [];
while ($row = mysqli_fetch_assoc($query_lokasi)) {
    $data_lokasi[] = $row;
}

?>
<link rel="icon" type="image/png" href="../../img/logo_white.png">
<div class="container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Laporan Data Barang</h1>
            <p class="text-muted small mb-0">Rekapitulasi inventaris seluruh barang, stok, dan kondisinya</p>
        </div>
        <div>
            <!-- Tombol Utama Export to PDF -->
            <a href="cetakpdf.php?tipe=barang&id_kategori=<?= $filter_kategori; ?>&kondisi=<?= urlencode($filter_kondisi); ?>&id_lokasi=<?= $filter_lokasi; ?>" target="_blank" class="btn btn-danger shadow-sm">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export to PDF
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form action="" method="get" class="row g-2 align-items-end">
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <label class="form-label fw-bold">Filter Lokasi</label>
                    <select name="id_lokasi" class="form-select">
                        <option value="">-- Semua Lokasi --</option>
                        <?php foreach ($data_lokasi as $l) : ?>
                            <option value="<?= $l['id_lokasi']; ?>" <?= ($filter_lokasi !== '' && $filter_lokasi == $l['id_lokasi']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($l['nama_lokasi']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Filter Kondisi</label>
                    <select name="kondisi" class="form-select">
                        <option value="">-- Semua Kondisi --</option>
                        <option value="layak" <?= ($filter_kondisi === 'layak') ? 'selected' : ''; ?>>Layak</option>
                        <option value="tidak_layak" <?= ($filter_kondisi === 'tidak_layak') ? 'selected' : ''; ?>>Tidak Layak</option>
                        <option value="perbaikan" <?= ($filter_kondisi === 'perbaikan') ? 'selected' : ''; ?>>Perbaikan</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
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
                            <?php $no = $offset_barang + 1; $total_stok = 0; ?>
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
                                <td colspan="5" class="text-end">Total Unit Stok Halaman Ini:</td>
                                <td colspan="3" class="text-start"><?= $total_stok; ?> Unit</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php include '../../layout/pagination.php'; ?>
        </div>
    </div>
</div>

<?php include_once '../../layout/footer.php'; ?>
