<?php

include_once '../../app/app.php';
include '../../layout/pagination.php';

// Notifikasi tambah kategori
$category_status = $_GET['status'] ?? null;
$category_message = null;
$category_message_type = 'success';

if ($category_status === 'success') {
    $category_message = 'Data kategori berhasil ditambahkan.';
} elseif ($category_status === 'failed') {
    $category_message = 'Data kategori gagal ditambahkan.';
}

// Notifikasi edit kategori
$edit_status = $_GET['edit_status'] ?? null;
$edit_message = null;
$edit_message_type = 'success';

if ($edit_status === 'success') {
    $edit_message = 'Data kategori berhasil diperbarui.';
} elseif ($edit_status === 'duplicate') {
    $edit_message = 'Nama kategori sudah terdaftar.';
    $edit_message_type = 'warning';
} elseif ($edit_status === 'failed') {
    $edit_message = 'Data kategori tidak berubah atau gagal diperbarui.';
    $edit_message_type = 'danger';
} elseif ($category_status === 'not_found') {
    $edit_message = 'Data kategori tidak ditemukan.';
    $edit_message_type = 'warning';
}

$delete_status = $_GET['delete_status'] ?? null;
$delete_message = null;

if ($delete_status === 'success') {
    $delete_message = 'Data kategori berhasil dihapus.';
} elseif ($delete_status === 'in_use') {
    $delete_message = 'Kategori tidak dapat dihapus karena masih digunakan oleh barang.';
} elseif ($delete_status === 'invalid') {
    $delete_message = 'Permintaan penghapusan tidak valid. Silakan coba kembali.';
} elseif ($delete_status === 'failed') {
    $delete_message = 'Data kategori gagal dihapus.';
}

include '../../layout/header.php';

$data_kategori = select_kategori("SELECT * FROM kategori");

?>
<link rel="icon" type="image/png" href="../../img/logo_white.png">
<div class="container-fluid py-2">
    <?php if ($delete_message !== null) : ?>
        <div class="alert <?= $delete_status === 'success' ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($delete_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
    <?php if ($category_message !== null) : ?>
        <div class="alert <?= $category_status === 'success' ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($category_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
    <?php if ($edit_message !== null) : ?>
        <div class="alert alert-<?= htmlspecialchars($edit_message_type, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($edit_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Data Kategori</h1>
            <p class="text-muted small mb-0">Kelola master data kategori inventaris</p>
        </div>
        <?php if (has_role(['admin', 'petugas'])) : ?>
        <a href="./tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Kategori
        </a>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-header-green text-center">
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama Kategori</th>
                            <?php if (has_role(['admin', 'petugas'])) : ?>
                            <th style="width: 180px;" class="text-nowrap">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php if (empty($data_kategori)) : ?>
                            <tr>
                                <td colspan="<?= has_role(['admin', 'petugas']) ? '3' : '2'; ?>" class="text-center py-4 text-muted">Belum ada data kategori.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($data_kategori as $kategori) : ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($kategori['nama_kategori']); ?></td>
                                    <?php if (has_role(['admin', 'petugas'])) : ?>
                                    <td class="text-center text-nowrap">
                                        <a href="./update.php?id_kategori=<?= $kategori['id_kategori']; ?>" class="btn btn-success btn-sm me-1">
                                            <i class="bi bi-pencil-square me-1"></i> Edit
                                        </a>                        
                                        <form action="./hapus.php" method="POST" class="d-inline"
                                            onsubmit="return confirm('Yakin ingin menghapus kategori ini?');">
                                            <input type="hidden" name="id_kategori" value="<?= (int) $kategori['id_kategori']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
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
</div>
                         
<?php include '../../layout/footer.php'; ?>
