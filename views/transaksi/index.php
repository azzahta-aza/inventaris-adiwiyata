<?php

include_once '../../app/app.php';
check_role(['admin', 'petugas']);

$transaction_status = $_GET['status'] ?? null;
$delete_status = $_GET['delete_status'] ?? null;

$transaction_message = null;
$transaction_message_type = 'success';

if ($transaction_status === 'success') {
    $transaction_message = 'Transaksi berhasil dicatat dan stok barang berhasil diperbarui.';
} elseif ($delete_status === 'success') {
    $transaction_message = 'Data transaksi berhasil dihapus.';
} elseif ($delete_status === 'failed') {
    $transaction_message = 'Data transaksi gagal dihapus.';
    $transaction_message_type = 'warning';
}

include_once '../../layout/header.php';

// Pencarian dan filter transaksi
$keyword = trim($_GET['keyword'] ?? '');
$jenis_filter = $_GET['jenis_filter'] ?? '';

$keyword = substr($keyword, 0, 50);

if (!in_array($jenis_filter, ['', 'masuk', 'keluar'], true)) {
    $jenis_filter = '';
}

$sql_where = '';

if ($keyword !== '') {
    $keyword_sql = mysqli_real_escape_string($db, $keyword);
    $sql_where .= " AND (
        b.nama_barang LIKE '%$keyword_sql%'
        OR b.kode_barang LIKE '%$keyword_sql%'
        OR t.keterangan LIKE '%$keyword_sql%'
    )";
}

if ($jenis_filter !== '') {
    $jenis_sql = mysqli_real_escape_string($db, $jenis_filter);
    $sql_where .= " AND t.jenis_transaksi = '$jenis_sql'";
}

// Pengaturan pagination
$limit = 10;

$current_page = filter_input(
    INPUT_GET,
    'page',
    FILTER_VALIDATE_INT
);

// Hitung data transaksi sesuai pencarian dan filter
$result_count = mysqli_query(
    $db,
    "SELECT COUNT(*) AS total
     FROM transaksi t
     LEFT JOIN barang b ON t.id_barang = b.id_barang
     WHERE 1=1 $sql_where"
);

if (!$result_count) {
    die("Query jumlah transaksi gagal: " . mysqli_error($db));
}

$total_data = (int) mysqli_fetch_assoc($result_count)['total'];

// Hitung jumlah halaman
$total_pages = max(1, (int) ceil($total_data / $limit));

// Pastikan halaman valid
$current_page = ($current_page !== false && $current_page !== null)
    ? max(1, min($current_page, $total_pages))
    : 1;

// Hitung data yang dilewati
$offset = ($current_page - 1) * $limit;

// Ambil transaksi sesuai halaman dan filter
$data_transaksi = select_transaksi(
    "SELECT
        t.*,
        b.kode_barang,
        b.nama_barang,
        b.satuan,
        u.nama AS nama_petugas
     FROM transaksi t
     LEFT JOIN barang b ON t.id_barang = b.id_barang
     LEFT JOIN users u ON t.id_user = u.id_user
     WHERE 1=1 $sql_where
     ORDER BY t.id_transaksi DESC
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
     <?php if ($transaction_message !== null) : ?>
        <div class="alert alert-<?= htmlspecialchars($transaction_message_type, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($transaction_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Riwayat Transaksi Barang</h1>
            <p class="text-muted small mb-0">Pencatatan mutasi barang masuk (pembelian/restock) dan barang keluar</p>
        </div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Catat Transaksi
        </a>
    </div>
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <form action="index.php" method="GET" class="row g-2 align-items-end">
                <div class="col-lg-5 col-md-6">
                    <label for="keyword" class="form-label fw-bold">Pencarian Transaksi</label>
                    <input
                        type="text"
                        class="form-control"
                        id="keyword"
                        name="keyword"
                        placeholder="Cari nama barang, kode, atau keterangan..."
                        value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </div>
                <div class="col-lg-5 col-md-6">
                    <label for="jenis_filter" class="form-label fw-bold">Filter Jenis</label>
                    <select class="form-select" id="jenis_filter" name="jenis_filter">
                        <option value="">-- Semua Jenis --</option>
                        <option value="masuk" <?= $jenis_filter === 'masuk' ? 'selected' : ''; ?>>
                            <i class="bi bi-arrow-down-circle"></i> Barang Masuk
                        </option>
                        <option value="keluar" <?= $jenis_filter === 'keluar' ? 'selected' : ''; ?>>
                            <i class="bi bi-arrow-up-circle"></i> Barang Keluar
                        </option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-12 d-flex gap-2">
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
                            <th width="5%">No</th>
                            <th width="12%">Tanggal</th>
                            <th>Nama Barang</th>
                            <th width="15%">Jenis Transaksi</th>
                            <th width="10%">Jumlah</th>
                            <th>Keterangan</th>
                            <th width="15%">Petugas</th>
                            <th width="8%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php if (empty($data_transaksi)) : ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada data transaksi barang.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = $offset + 1; ?>
                            <?php foreach ($data_transaksi as $tr) : ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($tr['tanggal'])); ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($tr['kode_barang'] ?? ''); ?></span>
                                        <strong><?= htmlspecialchars($tr['nama_barang'] ?? 'Barang Dihapus'); ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <?php if (strtolower($tr['jenis_transaksi']) === 'masuk') : ?>
                                            <span class="badge bg-success"><i class="bi bi-arrow-down-circle me-1"></i> Barang Masuk</span>
                                        <?php else : ?>
                                            <span class="badge bg-danger"><i class="bi bi-arrow-up-circle me-1"></i> Barang Keluar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center fw-bold">
                                        <?= (strtolower($tr['jenis_transaksi']) === 'masuk' ? '+' : '-') . $tr['jumlah']; ?> <?= htmlspecialchars($tr['satuan'] ?? ''); ?>
                                    </td>
                                    <td><?= htmlspecialchars($tr['keterangan']); ?></td>
                                    <td><?= htmlspecialchars($tr['nama_petugas'] ?? 'Petugas'); ?></td>
                                    <td class="text-center text-nowrap">
                                        <form action="hapus.php" method="POST" class="d-inline"
                                            onsubmit="return confirm('Yakin ingin menghapus riwayat transaksi ini? Mutasi stok akan dibatalkan.');">
                                            <input type="hidden" name="id_transaksi"
                                                value="<?= (int) $tr['id_transaksi']; ?>">
                                            <input type="hidden" name="csrf_token"
                                                value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus Transaksi">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
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
