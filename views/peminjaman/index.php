<?php

include_once '../../app/app.php';

// NOTIFIKASI TAMBAH PEMINJAMAN
$loan_add_status = $_GET['status'] ?? null;
$loan_add_message = null;
$loan_add_type = 'success';

if ($loan_add_status === 'success') {
    $loan_add_message = 'Data peminjaman berhasil ditambahkan.';
} elseif ($loan_add_status === 'failed') {
    $loan_add_message = 'Data peminjaman gagal ditambahkan.';
}

// NOTIFIKASI EDIT PEMINJAMAN
$edit_status = $_GET['edit_status'] ?? null;
$edit_message = null;
$edit_message_type = 'success';

if ($edit_status === 'success') {
    $edit_message = 'Data peminjaman berhasil diperbarui.';
} elseif ($edit_status === 'failed') {
    $edit_message = 'Data peminjaman tidak berubah atau gagal diperbarui.';
    $edit_message_type = 'danger';
} elseif ($edit_status === 'not_found') {
    $edit_message = 'Data peminjaman tidak ditemukan.';
    $edit_message_type = 'warning';
}

// NOTIFIKASI HAPUS PEMINJAMAN
$delete_status = $_GET['delete_status'] ?? null;
$delete_message = null;
$delete_message_type = 'warning';

if ($delete_status === 'success') {
    $delete_message = 'Data peminjaman berhasil dihapus.';
    $delete_message_type = 'success';
} elseif ($delete_status === 'in_use') {
    $delete_message = 'Data peminjaman tidak dapat dihapus karena masih digunakan.';
} elseif ($delete_status === 'failed') {
    $delete_message = 'Data peminjaman gagal dihapus.';
    $delete_message_type = 'danger';
} elseif ($delete_status === 'invalid') {
    $delete_message = 'ID peminjaman tidak valid.';
} elseif ($delete_status === 'not_found') {
    $delete_message = 'Data peminjaman tidak ditemukan.';
}

// NOTIFIKASI PENGEMBALIAN
$return_status = $_GET['return_status'] ?? null;
$return_message = null;
$return_message_type = 'success';

if ($return_status === 'success') {
    $return_message = 'Barang berhasil dikembalikan.';
} elseif ($return_status === 'already_returned') {
    $return_message = 'Barang sudah dikembalikan sebelumnya.';
    $return_message_type = 'warning';
} elseif ($return_status === 'failed') {
    $return_message = 'Pengembalian barang gagal diproses.';
    $return_message_type = 'danger';
}

// PENCARIAN DAN FILTER PEMINJAMAN
$keyword = trim($_GET['keyword'] ?? '');
$filter_status = $_GET['status_filter'] ?? '';

$keyword = substr($keyword, 0, 50);

// Validasi filter status
if (!in_array($filter_status, ['', 'dipinjam', 'dikembalikan'], true)) {
    $filter_status = '';
}

// Query dasar peminjaman
$sql_peminjaman = "SELECT
        p.*,
        b.kode_barang,
        b.nama_barang,
        b.satuan,
        u.nama AS nama_petugas
    FROM peminjaman p
    LEFT JOIN barang b ON p.id_barang = b.id_barang
    LEFT JOIN users u ON p.id_user = u.id_user
    WHERE 1=1";

// Pencarian berdasarkan nama peminjam, kode barang, atau nama barang
if ($keyword !== '') {
    $keyword_sql = mysqli_real_escape_string($db, $keyword);

    $sql_peminjaman .= " AND (
        p.nama_peminjam LIKE '%$keyword_sql%'
        OR b.kode_barang LIKE '%$keyword_sql%'
        OR b.nama_barang LIKE '%$keyword_sql%'
    )";
}

// Filter status peminjaman
if ($filter_status !== '') {
    $status_sql = mysqli_real_escape_string($db, $filter_status);
    $sql_peminjaman .= " AND p.status = '$status_sql'";
}

 // Pengaturan pagination
$limit = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));

// Query dasar untuk menghitung jumlah data sesuai pencarian dan filter
$sql_count = "SELECT COUNT(*) AS total
    FROM peminjaman p
    LEFT JOIN barang b ON p.id_barang = b.id_barang
    LEFT JOIN users u ON p.id_user = u.id_user
    WHERE 1=1";

// Terapkan pencarian yang sama
if ($keyword !== '') {
    $keyword_sql = mysqli_real_escape_string($db, $keyword);

    $sql_count .= " AND (
        p.nama_peminjam LIKE '%$keyword_sql%'
        OR b.kode_barang LIKE '%$keyword_sql%'
        OR b.nama_barang LIKE '%$keyword_sql%'
    )";
}

// Terapkan filter status yang sama
if ($filter_status !== '') {
    $status_sql = mysqli_real_escape_string($db, $filter_status);
    $sql_count .= " AND p.status = '$status_sql'";
}

// Hitung total data
$result_count = mysqli_query($db, $sql_count);

if (!$result_count) {
    die("Query jumlah peminjaman gagal: " . mysqli_error($db));
}

$total_data = (int) mysqli_fetch_assoc($result_count)['total'];
$total_pages = (int) ceil($total_data / $limit);

// Pastikan halaman valid
$page = min($page, max(1, $total_pages));
$offset = ($page - 1) * $limit;

// Ambil data pada halaman aktif
$sql_peminjaman .= " ORDER BY p.id_pinjam DESC LIMIT $limit OFFSET $offset";

$result_peminjaman = mysqli_query($db, $sql_peminjaman);

$data_peminjaman = [];

if ($result_peminjaman) {
    while ($row = mysqli_fetch_assoc($result_peminjaman)) {
        $data_peminjaman[] = $row;
    }
} else {
    die("Query peminjaman gagal: " . mysqli_error($db));
}

// Data untuk tampilan pagination
$pagination = [
    'page' => $page,
    'total_pages' => $total_pages,
    'total' => $total_data,
    'from' => $total_data > 0 ? $offset + 1 : 0,
    'to' => min($offset + $limit, $total_data)
];

// Pertahankan pencarian dan filter saat pindah halaman
$pagination_params = [];

if ($keyword !== '') {
    $pagination_params['keyword'] = $keyword;
}

if ($filter_status !== '') {
    $pagination_params['status_filter'] = $filter_status;
}

include_once '../../layout/header.php';

?>

<link rel="icon" type="image/png" href="../../img/logo_white.png">
<div class="container-fluid py-2">

<!--NOTIFIKASI-->
    <?php if ($loan_add_message !== null) : ?>
        <div class="alert alert-<?= $loan_add_type; ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($loan_add_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

    <?php if ($edit_message !== null) : ?>
        <div class="alert alert-<?= $edit_message_type === 'warning' ? 'warning' : $edit_message_type; ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($edit_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

    <?php if ($delete_message !== null) : ?>
        <div class="alert alert-<?= $delete_message_type; ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($delete_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

    <?php if ($return_message !== null) : ?>
        <div class="alert alert-<?= $return_message_type === 'warning' ? 'warning' : $return_message_type; ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($return_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

<!--JUDUL HALAMAN-->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Data Peminjaman</h1>
            <p class="text-muted small mb-0">
                Kelola data peminjaman dan pengembalian barang inventaris
            </p>
        </div>

        <?php if (has_role(['admin', 'petugas'])) : ?>
            <a href="tambah.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Tambah Peminjaman
            </a>
        <?php endif; ?>
    </div>

<!-- CARD PENCARIAN DAN FILTER-->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <form action="index.php" method="GET" class="row g-2 align-items-end">

                <div class="col-lg-5 col-md-6">
                    <label for="keyword" class="form-label fw-bold">
                        Pencarian Peminjaman
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="keyword"
                        name="keyword"
                        placeholder="Cari nama peminjam, kode, atau nama barang..."
                        value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </div>

                <div class="col-lg-5 col-md-6">
                    <label for="status_filter" class="form-label fw-bold">
                        Filter Status
                    </label>

                    <select
                        class="form-select"
                        id="status_filter"
                        name="status_filter"
                    >
                        <option value="">-- Semua Status --</option>

                        <option
                            value="dipinjam"
                            <?= $filter_status === 'dipinjam' ? 'selected' : ''; ?>
                        >
                            Dipinjam
                        </option>

                        <option
                            value="dikembalikan"
                            <?= $filter_status === 'dikembalikan' ? 'selected' : ''; ?>
                        >
                            Dikembalikan
                        </option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-6 d-flex gap-2 justify-content-start">
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

<!--TABEL PEMINJAMAN -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-3">
            <div class="table-responsive">

                <table class="table table-hover table-striped mb-0 align-middle">

                    <thead class="table-header-green text-center">
                        <tr>
                            <th>No</th>
                            <th>Nama Peminjam</th>
                            <th>Barang</th>
                            <th>Jumlah</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                            <th>Kondisi Kembali</th>
                            <th>Pencatat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="text-center">

                        <?php if (empty($data_peminjaman)) : ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    Data peminjaman tidak ditemukan.
                                </td>
                            </tr>

                        <?php else : ?>

                            <?php $no = $offset + 1; ?>

                            <?php foreach ($data_peminjaman as $peminjaman) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $peminjaman['nama_peminjam'] ?? '-',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>

                                    <td>
                                        <div class="fw-bold text-primary">
                                            <?= htmlspecialchars(
                                                $peminjaman['kode_barang'] ?? '-',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </div>

                                        <div>
                                            <?= htmlspecialchars(
                                                $peminjaman['nama_barang'] ?? '-',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </div>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) ($peminjaman['jumlah'] ?? 0),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>

                                        <?= htmlspecialchars(
                                            $peminjaman['satuan'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $peminjaman['tanggal_pinjam'] ?? '-',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($peminjaman['tanggal_kembali'])) : ?>
                                            <?= htmlspecialchars(
                                                $peminjaman['tanggal_kembali'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        <?php else : ?>
                                            <span class="text-muted fst-italic">-</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if (($peminjaman['status'] ?? '') === 'dipinjam') : ?>
                                            <span class="badge bg-warning text-dark">
                                                Dipinjam
                                            </span>
                                        <?php elseif (($peminjaman['status'] ?? '') === 'dikembalikan') : ?>
                                            <span class="badge bg-success">
                                                Dikembalikan
                                            </span>
                                        <?php else : ?>
                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars(
                                                    $peminjaman['status'] ?? '-',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($peminjaman['kondisi_kembali'])) : ?>
                                            <?= htmlspecialchars(
                                                $peminjaman['kondisi_kembali'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        <?php else : ?>
                                            <span class="text-muted fst-italic">Belum dikembalikan</span>
                                        <?php endif; ?>

                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $peminjaman['nama_petugas'] ?? '-',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>

                                    <td class="text-center text-nowrap">

                                        <?php if (has_role(['admin', 'petugas'])) : ?>
                                            <a
                                                href="update.php?id_pinjam=<?= (int) $peminjaman['id_pinjam']; ?>"
                                                class="btn btn-sm btn-success mb-1"
                                            >
                                                <i class="bi bi-pencil-square"></i>
                                                Edit
                                            </a>

                                            <form
                                                action="hapus.php"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus data peminjaman ini?');"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="id_pinjam"
                                                    value="<?= (int) $peminjaman['id_pinjam']; ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= htmlspecialchars(
                                                        $_SESSION['csrf_token'] ?? '',
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ); ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-danger mb-1"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                    Hapus
                                                </button>

                                                <br>
                                                <?php if (($peminjaman['status'] ?? '') === 'dipinjam') : ?>
                                                    <a
                                                        href="pengembalian.php?id_pinjam=<?= (int) $peminjaman['id_pinjam']; ?>"
                                                        class="btn btn-sm btn-primary mb-1"
                                                    >
                                                        <i class="bi bi-arrow-return-left"></i>
                                                        Kembalikan
                                                    </a>
                                                <?php endif; ?>
                                            </form>
                                        <?php endif; ?>

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