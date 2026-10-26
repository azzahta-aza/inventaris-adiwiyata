<?php

include_once '../../app/app.php';

check_role('admin');

$delete_status = $_GET['delete_status'] ?? null;
$status = $_GET['status'] ?? null;
$edit_status = $_GET['edit_status'] ?? null;

$notification_message = null;
$notification_type = 'success';

// Pesan status tambah pengguna
$user_status = $_GET['status'] ?? null;
$user_message = null;

if ($user_status === 'success') {
    $user_message = 'Pengguna berhasil ditambahkan.';
} elseif ($user_status === 'username_used') {
    $user_message = 'Username sudah digunakan. Silakan gunakan username lain.';
} elseif ($user_status === 'failed') {
    $user_message = 'Pengguna gagal ditambahkan.';
}

 // Pesan status edit pengguna
$edit_status = $_GET['edit_status'] ?? null;
$edit_message = null;
$edit_message_type = 'success';

if ($edit_status === 'success') {
    $edit_message = 'Pengguna berhasil diperbarui.';
} elseif ($edit_status === 'duplicate') {
    $edit_message = 'Username sudah digunakan.';
    $edit_message_type = 'warning';
} elseif ($edit_status === 'last_admin') {
    $edit_message = 'Role Admin terakhir tidak dapat diubah.';
    $edit_message_type = 'warning';
} elseif ($edit_status === 'invalid') {
    $edit_message = 'ID pengguna tidak valid.';
    $edit_message_type = 'warning';
} elseif ($edit_status === 'not_found' || $status === 'not_found') {
    $edit_message = 'Pengguna tidak ditemukan.';
    $edit_message_type = 'warning';
} elseif ($edit_status === 'failed') {
    $edit_message = 'Data pengguna tidak berubah atau gagal diperbarui.';
    $edit_message_type = 'danger';
}

// Pesan status hapus pengguna
$delete_status = $_GET['delete_status'] ?? null;
$delete_message = null;

if ($delete_status === 'success') {
    $delete_message = 'Pengguna berhasil dihapus.';
} elseif ($delete_status === 'in_use') {
    $delete_message = 'Akun pengguna tidak dapat dihapus karena masih digunakan.';
} elseif ($delete_status === 'last_admin') {
    $delete_message = 'Akun Admin terakhir tidak dapat dihapus.';
} elseif ($delete_status === 'invalid') {
    $delete_message = 'Permintaan penghapusan tidak valid. Silakan coba kembali.';
} elseif ($delete_status === 'failed') {
    $delete_message = 'Pengguna gagal dihapus.';
}

 // Ambil kata kunci pencarian dari URL
$keyword = trim($_GET['keyword'] ?? '');

// Pengaturan pagination
$limit = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));

// Kondisi filter SQL untuk pencarian
$where_clause = "";
if ($keyword !== '') {
    $keyword_esc = mysqli_real_escape_string($db, $keyword);
    $where_clause = "WHERE nama LIKE '%$keyword_esc%' OR username LIKE '%$keyword_esc%'";
}

// Hitung seluruh data pengguna (dengan filter pencarian)
$result_count = mysqli_query(
    $db,
    "SELECT COUNT(*) AS total FROM users $where_clause"
);

if (!$result_count) {
    die("Query jumlah pengguna gagal: " . mysqli_error($db));
}

$total_data = (int) mysqli_fetch_assoc($result_count)['total'];
$total_pages = (int) ceil($total_data / $limit);

// Pastikan halaman masih valid
$page = min($page, max(1, $total_pages));
$offset = ($page - 1) * $limit;

// Ambil data pengguna sesuai halaman dan pencarian
$data_users = select_users(
    "SELECT id_user, nama, username, role
     FROM users
     $where_clause
     ORDER BY id_user DESC
     LIMIT $limit OFFSET $offset"
);

// Data untuk komponen pagination agar kata kunci tidak hilang saat pindah halaman
$pagination = [
    'current_page' => $page,
    'total_pages' => $total_pages,
];

$pagination_params = [];

if ($keyword !== '') {
    $pagination_params['keyword'] = $keyword;
}

include_once '../../layout/header.php';

?>
<link rel="icon" type="image/png" href="../../img/logo_white.png">
<div class="container-fluid py-2">
    <?php if ($delete_message !== null) : ?>
        <div class="alert <?= $delete_status === 'success' ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($delete_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
    <?php if ($edit_message !== null) : ?>
        <div class="alert alert-<?= htmlspecialchars($edit_message_type, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($edit_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
    <?php if ($user_message !== null) : ?>
        <div class="alert <?= $user_status === 'success' ? 'alert-success' : ($user_status === 'username_used' ? 'alert-warning' : 'alert-danger'); ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($user_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Manajemen Pengguna (Users)</h1>

            <p class="text-muted small mb-2">
                Kelola hak akses dan akun pengguna sistem inventaris
            </p>
        </div>
        <!-- TOMBOL TAMBAH PENGGUNA -->
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> Tambah Pengguna
        </a>
    </div>

    <!-- Card Pencarian Pengguna -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <form action="index.php" method="GET" class="row g-2 align-items-end">

                <div class="col-lg-10 col-md-8">
                    <label for="keyword" class="form-label fw-bold">
                        Pencarian Pengguna
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        id="keyword"
                        name="keyword"
                        placeholder="nama atau username..."
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

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-header-green text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th width="15%">Role</th>
                            <th width="18%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data_users)) : ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Belum ada data pengguna.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = $offset + 1; ?>
                            <?php foreach ($data_users as $user) : ?>
                                <tr class="text-center">
                                    <td><?= $no++; ?></td>
                                    <td class="fw-semibold">
                                        <i class="bi bi-person-circle text-secondary me-2"></i>
                                        <?= htmlspecialchars($user['nama']); ?>
                                    </td>
                                    <td><code><?= htmlspecialchars($user['username']); ?></code></td>
                                    <td class="text-center">
                                        <?php if ($user['role'] === 'Admin') : ?>
                                            <span class="badge bg-danger">Admin</span>
                                        <?php elseif ($user['role'] === 'Petugas') : ?>
                                            <span class="badge bg-primary">Petugas</span>
                                        <?php else : ?>
                                            <span class="badge bg-success">Kepala</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="update.php?id_user=<?= $user['id_user']; ?>" class="btn btn-sm btn-success me-1">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>                                
                                        <form action="hapus.php" method="POST" class="d-inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun user <?= htmlspecialchars($user['nama'], ENT_QUOTES, 'UTF-8'); ?>?');">
                                            <input type="hidden" name="id_user" value="<?= (int) $user['id_user']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i> Hapus
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