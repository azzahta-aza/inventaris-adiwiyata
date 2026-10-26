<?php

include_once '../../app/app.php';

// Notifikasi tambah barang
$barang_status = $_GET['status'] ?? null;
$barang_message = null;

if ($barang_status === 'success') {
    $barang_message = 'Data barang berhasil ditambahkan.';
} elseif ($barang_status === 'failed') {
    $barang_message = 'Data barang gagal ditambahkan.';
}

// Notifikasi edit barang
$edit_status = $_GET['edit_status'] ?? null;
$edit_message = null;
$edit_message_type = 'success';

if ($edit_status === 'success') {
    $edit_message = 'Data barang berhasil diperbarui.';
} elseif ($edit_status === 'duplicate') {
    $edit_message = 'Kode atau nama barang sudah terdaftar.';
    $edit_message_type = 'warning';
} elseif ($edit_status === 'failed') {
    $edit_message = 'Data barang tidak berubah atau gagal diperbarui.';
    $edit_message_type = 'danger';
} elseif ($barang_status === 'not_found') {
    $edit_message = 'Data barang tidak ditemukan.';
    $edit_message_type = 'warning';
}

// Notifikasi hapus barang
$delete_status = $_GET['delete_status'] ?? null;
$delete_message = null;
$delete_message_type = 'warning';

if ($delete_status === 'success') {
    $delete_message = 'Data barang berhasil dihapus.';
    $delete_message_type = 'success';
} elseif ($delete_status === 'failed') {
    $delete_message = 'Data barang gagal dihapus.';
    $delete_message_type = 'danger';
} elseif ($delete_status === 'invalid') {
    $delete_message = 'ID barang tidak valid.';
} elseif ($delete_status === 'has_history') {
    $delete_message = 'Barang tidak dapat dihapus karena memiliki riwayat peminjaman atau transaksi.';
}

include_once '../../layout/header.php';

// Ambil nilai pencarian dan filter
$keyword = trim($_GET['keyword'] ?? '');
$filter_kategori = $_GET['kategori'] ?? '';
$filter_lokasi = $_GET['lokasi'] ?? '';

$keyword = substr($keyword, 0, 100);


//Query dasar barang
$sql_barang = "SELECT b.*, k.nama_kategori, l.nama_lokasi
    FROM barang b
    LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
    LEFT JOIN lokasi l ON b.id_lokasi = l.id_lokasi
    WHERE 1=1";

if ($keyword !== '') {
    $keyword_sql = mysqli_real_escape_string($db, $keyword);
    $sql_barang .= " AND (
        b.kode_barang LIKE '%$keyword_sql%'
        OR b.nama_barang LIKE '%$keyword_sql%'
    )";
}

if ($filter_kategori !== '' && ctype_digit($filter_kategori)) {
    $sql_barang .= " AND b.id_kategori = " . (int) $filter_kategori;
}

if ($filter_lokasi !== '' && ctype_digit($filter_lokasi)) {
    $sql_barang .= " AND b.id_lokasi = " . (int) $filter_lokasi;
}

//Hitung seluruh data sesuai pencarian dan filter
$sql_count = preg_replace(
    '/^SELECT b\.\*, k\.nama_kategori, l\.nama_lokasi\s+FROM barang b/i',
    'SELECT COUNT(*) AS total FROM barang b',
    $sql_barang
);

$sql_count = str_replace(
    [
        "LEFT JOIN kategori k ON b.id_kategori = k.id_kategori\n    ",
        "LEFT JOIN lokasi l ON b.id_lokasi = l.id_lokasi\n    "
    ],
    '',
    $sql_count
);

$result_count = mysqli_query($db, $sql_count);
$total_data = (int) mysqli_fetch_assoc($result_count)['total'];

//Pengaturan pagination
$limit = 10;
$total_pages = max(1, (int) ceil($total_data / $limit));

$current_page = filter_input(
    INPUT_GET,
    'page',
    FILTER_VALIDATE_INT
);

$current_page = ($current_page !== false && $current_page !== null)
    ? max(1, min($current_page, $total_pages))
    : 1;

$offset = ($current_page - 1) * $limit;

//Data untuk tampilan pagination
$pagination = [
    'page' => $current_page,
    'total_pages' => $total_pages,
    'total' => $total_data,
    'from' => $total_data > 0 ? $offset + 1 : 0,
    'to' => min($offset + $limit, $total_data)
];

//Pertahankan pencarian dan filter saat pindah halaman
$pagination_params = [];

if ($keyword !== '') {
    $pagination_params['keyword'] = $keyword;
}

if ($filter_kategori !== '' && ctype_digit($filter_kategori)) {
    $pagination_params['kategori'] = $filter_kategori;
}

if ($filter_lokasi !== '' && ctype_digit($filter_lokasi)) {
    $pagination_params['lokasi'] = $filter_lokasi;
}

// Ambil hanya data untuk halaman aktif
$sql_barang .= " ORDER BY b.id_barang ASC
    LIMIT $limit OFFSET $offset";

$data_barang = select_barang($sql_barang);

// Ambil daftar kategori dan lokasi untuk pilihan filter
$data_kategori = select_kategori(
    "SELECT * FROM kategori ORDER BY nama_kategori ASC"
);

$data_lokasi = select_lokasi(
    "SELECT * FROM lokasi ORDER BY nama_lokasi ASC"
);

?>

<link rel="icon" type="image/png" href="../../img/logo_white.png">

<div class="container-fluid py-2">
    <?php if ($delete_message !== null) : ?>
        <div class="alert <?= $delete_status === 'success' ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
            <i class="bi <?= $delete_status === 'success' ?> me-2"></i>
            <?= htmlspecialchars($delete_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
    <?php if ($barang_message !== null) : ?>
        <div class="alert <?= $barang_status === 'success' ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($barang_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
    <?php if ($edit_message !== null) : ?>
        <div class="alert <?= $edit_message_type === 'success' ? 'alert-success' : ($edit_message_type === 'warning' ? 'alert-danger' : 'alert-danger'); ?> alert-dismissible fade show" role="alert">
            <i class="bi <?= $edit_message_type === 'success' ?> me-2"></i>
            <?= htmlspecialchars($edit_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Data Master Barang</h1>
            <p class="text-muted small mb-0">Kelola data barang inventaris beserta kategori dan lokasi</p>
        </div>
        <?php if (has_role(['admin', 'petugas'])) : ?>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Barang
        </a>
        <?php endif; ?>
    </div>
    
    <!-- Card Pencarian dan Filter -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <form action="index.php" method="GET" class="row g-2 align-items-end">

                <div class="col-lg-4 col-md-6">
                    <label for="keyword" class="form-label fw-bold">
                        Pencarian Barang
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        id="keyword"
                        name="keyword"
                        placeholder="nama barang..."
                        value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </div>

                <div class="col-lg-3 col-md-6">
                    <label for="kategori" class="form-label fw-bold">
                        Filter Kategori
                    </label>
                    <select class="form-select" id="kategori" name="kategori">
                        <option value="">-- Semua Kategori --</option>
                        <?php foreach ($data_kategori as $kategori) : ?>
                            <option
                                value="<?= (int) $kategori['id_kategori']; ?>"
                                <?= $filter_kategori === (string) $kategori['id_kategori'] ? 'selected' : ''; ?>
                            >
                                <?= htmlspecialchars($kategori['nama_kategori'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label for="lokasi" class="form-label fw-bold">
                        Filter Lokasi
                    </label>
                    <select class="form-select" id="lokasi" name="lokasi">
                        <option value="">-- Semua Lokasi --</option>
                        <?php foreach ($data_lokasi as $lokasi) : ?>
                            <option
                                value="<?= (int) $lokasi['id_lokasi']; ?>"
                                <?= $filter_lokasi === (string) $lokasi['id_lokasi'] ? 'selected' : ''; ?>
                            >
                                <?= htmlspecialchars($lokasi['nama_lokasi'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-lg-2 col-md-6 d-flex gap-2">
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

    <!-- Card Tabel Barang -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-7">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-header-green text-center">
                        <tr>
                            <th>No</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Stok</th>
                            <th>Satuan</th>
                            <th>Kondisi</th>
                            <?php if (has_role(['admin', 'petugas'])) : ?>
                            <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php if (empty($data_barang)) : ?>
                            <tr>
                                <td colspan="<?= has_role(['admin', 'petugas']) ? '9' : '8'; ?>" class="text-center text-muted py-4">Belum ada data barang.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = $offset + 1; ?>
                            <?php foreach ($data_barang as $barang) : ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($barang['kode_barang']); ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($barang['nama_barang']); ?></div>
                                        <?php if (!empty($barang['keterangan'])) : ?>
                                            <small class="text-muted"><?= htmlspecialchars($barang['keterangan']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <!-- Menampilkan Nama Kategori dari Tabel Kategori -->
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            <?= htmlspecialchars($barang['nama_kategori'] ?? 'Tidak Terkategori'); ?>
                                        </span>
                                    </td>
                                    <!-- Menampilkan Nama Lokasi dari Tabel Lokasi -->
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= htmlspecialchars($barang['nama_lokasi'] ?? 'Belum Ditentukan'); ?>
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold"><?= $barang['stok']; ?></td>
                                    <td class="text-center"><?= htmlspecialchars($barang['satuan']); ?></td>
                                    <td class="text-center">
                                        <?php if ($barang['kondisi'] === 'layak') : ?>
                                            <span class="badge bg-success">Layak</span>
                                        <?php elseif ($barang['kondisi'] === 'perbaikan') : ?>
                                            <span class="badge bg-warning text-dark">Perbaikan</span>
                                        <?php else : ?>
                                            <span class="badge bg-danger">Tidak Layak</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if (has_role(['admin', 'petugas'])) : ?>
                                    <td class="text-center text-nowrap">
                                        <a href="update.php?id_barang=<?= $barang['id_barang']; ?>" class="btn btn-sm btn-success">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <form action="hapus.php" method="POST" class="d-inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus data barang <?= htmlspecialchars($barang['nama_barang'], ENT_QUOTES, 'UTF-8'); ?>?');">
                                            <input type="hidden" name="id_barang" value="<?= (int) $barang['id_barang']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i> Hapus
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
<?php include_once '../../layout/footer.php'; ?>
