<?php

include_once '../../app/app.php';

// Ambil kata kunci pencarian dari URL
$keyword = trim($_GET['keyword'] ?? '');

$session_notifikasi = $_SESSION['notifikasi'] ?? null;
unset($_SESSION['notifikasi']);

// Notifikasi tambah lokasi
$location_status = $_GET['status'] ?? null;
$location_message = null;

if ($location_status === 'success') {
    $location_message = 'Data tempat berhasil ditambahkan.';
} elseif ($location_status === 'failed') {
    $location_message = 'Data tempat gagal ditambahkan.';
}

// Notifikasi edit lokasi
$edit_status = $_GET['edit_status'] ?? null;
$edit_message = null;
$edit_message_type = 'success';

if ($edit_status === 'success') {
    $edit_message = 'Data tempat berhasil diperbarui.';
} elseif ($edit_status === 'duplicate') {
    $edit_message = 'Nama tempat sudah terdaftar.';
    $edit_message_type = 'warning';
} elseif ($edit_status === 'failed') {
    $edit_message = 'Data tempat tidak berubah atau gagal diperbarui.';
    $edit_message_type = 'danger';
} elseif ($location_status === 'not_found') {
    $edit_message = 'Data tempat tidak ditemukan.';
    $edit_message_type = 'warning';
}

$delete_status = $_GET['delete_status'] ?? null;
$delete_message = null;

if ($delete_status === 'success') {
    $delete_message = 'Data lokasi berhasil dihapus.';
} elseif ($delete_status === 'in_use') {
    $delete_message = 'Lokasi tidak dapat dihapus karena masih digunakan oleh barang.';
} elseif ($delete_status === 'invalid') {
    $delete_message = 'Permintaan penghapusan tidak valid. Silakan coba kembali.';
} elseif ($delete_status === 'failed') {
    $delete_message = 'Data lokasi gagal dihapus.';
}

include '../../layout/header.php';

// Kondisi filter SQL untuk pencarian berdasarkan nama lokasi
$where_clause = "";

if ($keyword !== '') {
    $keyword_esc = mysqli_real_escape_string($db, $keyword);
    $where_clause = "WHERE nama_lokasi LIKE '%$keyword_esc%'";
}

// Pengaturan pagination
$limit = 10;

$current_page = filter_input(
    INPUT_GET,
    'page',
    FILTER_VALIDATE_INT
);

// Hitung jumlah seluruh data lokasi sesuai pencarian
$result_count = mysqli_query(
    $db,
    "SELECT COUNT(*) AS total FROM lokasi $where_clause"
);

$total_data = (int) mysqli_fetch_assoc($result_count)['total'];

// Hitung jumlah halaman
$total_pages = max(1, (int) ceil($total_data / $limit));

// Pastikan halaman valid
$current_page = ($current_page !== false && $current_page !== null)
    ? max(1, min($current_page, $total_pages))
    : 1;

// Hitung data yang dilewati
$offset = ($current_page - 1) * $limit;

// Ambil data sesuai halaman dan pencarian
$data_lokasi = select_lokasi(
    "SELECT * FROM lokasi
     $where_clause
     ORDER BY id_lokasi ASC
     LIMIT $limit OFFSET $offset"
);

// Data untuk komponen pagination
$pagination = [
    'page' => $current_page,
    'total_pages' => $total_pages,
    'total' => $total_data,
    'from' => $total_data > 0 ? $offset + 1 : 0,
    'to' => min($offset + $limit, $total_data)
];

// Pertahankan pencarian saat pindah halaman
$pagination_params = [];

if ($keyword !== '') {
    $pagination_params['keyword'] = $keyword;
}

// Hitung jumlah halaman
$total_pages = max(1, (int) ceil($total_data / $limit));

// Pastikan halaman valid
$current_page = ($current_page !== false && $current_page !== null)
    ? max(1, min($current_page, $total_pages))
    : 1;

// Hitung data yang dilewati
$offset = ($current_page - 1) * $limit;

// Ambil data sesuai halaman
$data_lokasi = select_lokasi(
    "SELECT * FROM lokasi
     ORDER BY id_lokasi ASC
     LIMIT $limit OFFSET $offset"
);

// Data untuk komponen pagination
$pagination = [
    'page' => $current_page,
    'total_pages' => $total_pages,
    'total' => $total_data,
    'from' => $total_data > 0 ? $offset + 1 : 0,
    'to' => min($offset + $limit, $total_data)
];

$pagination_params = [];

?>

<link rel="icon" type="image/png" href="../../img/logo_white.png">
<div class="container-fluid py-2">
    <?php if (!empty($session_notifikasi)) : ?>
        <div class="alert alert-<?= htmlspecialchars(
            $session_notifikasi['jenis'] ?? 'warning',
            ENT_QUOTES,
            'UTF-8'
        ); ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars(
                $session_notifikasi['pesan'] ?? '',
                ENT_QUOTES,
                'UTF-8'
            ); ?>
            <button type="button" class="btn-close"
                data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

    <?php if ($delete_message !== null) : ?>
        <div class="alert <?= $delete_status === 'success' ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($delete_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close"
                data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

    <?php if ($location_message !== null) : ?>
        <div class="alert <?= $location_status === 'success' ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($location_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close"
                data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

    <?php if ($edit_message !== null) : ?>
        <div class="alert alert-<?= htmlspecialchars($edit_message_type, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($edit_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close"
                data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Data Tempat Penyimpanan</h1>
            <p class="text-muted small mb-0">Kelola master data tempat penyimpanan barang</p>
        </div>
        <?php if (has_role(['admin', 'petugas'])) : ?>
        <a href="./tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Tempat
        </a>
        <?php endif; ?>
    </div>

    <!-- Card Pencarian Lokasi -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <form action="index.php" method="GET" class="row g-2 align-items-end">

                <div class="col-lg-10 col-md-8">
                    <label for="keyword" class="form-label fw-bold">
                        Pencarian Tempat Penyimpanan
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        id="keyword"
                        name="keyword"
                        placeholder="nama tempat penyimpanan..."
                        value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </div>

                <div class="col-lg-2 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>

                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reset
                    </a>
                </div>

            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-header-green text-center">
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama Tempat</th>
                            <?php if (has_role(['admin', 'petugas'])) : ?>
                            <th style="width: 180px;" class="text-nowrap">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php if (empty($data_lokasi)) : ?>
                            <tr>
                                <td colspan="<?= has_role(['admin', 'petugas']) ? '3' : '2'; ?>" class="text-center py-4 text-muted">Belum ada data lokasi.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = $offset + 1; ?>
                            <?php foreach ($data_lokasi as $lokasi) : ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($lokasi['nama_lokasi']); ?></td>
                                    <?php if (has_role(['admin', 'petugas'])) : ?>
                                    <td class="text-center text-nowrap">
                                        <a href="update.php?id_lokasi=<?= $lokasi['id_lokasi']; ?>" class="btn btn-success btn-sm me-1">
                                            <i class="bi bi-pencil-square me-1"></i> Edit
                                        </a>
                                        <form action="hapus.php" method="POST" class="d-inline"
                                            onsubmit="return confirm('Yakin ingin menghapus lokasi ini?');">
                                            <input type="hidden" name="id_lokasi"
                                                value="<?= (int) $lokasi['id_lokasi']; ?>">
                                            <input type="hidden" name="csrf_token"
                                                value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash me-1"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php include '../../layout/pagination.php'; ?>
</div>

<?php include '../../layout/footer.php'; ?>
