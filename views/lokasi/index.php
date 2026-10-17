<?php

include_once '../../app/app.php';
include '../../layout/pagination.php';

// Pesan status tambah lokasi.
$location_status = $_GET['status'] ?? null;
$location_message = null;
$location_message_type = 'success';

if ($location_status === 'success') {
    $location_message = 'Data lokasi berhasil ditambahkan.';
} elseif ($location_status === 'failed') {
    $location_message = 'Data lokasi gagal ditambahkan.';
}

//pesan status edit lokasi.
$edit_status = $_GET['edit_status'] ?? null;
$edit_message = null;
$edit_message_type = 'success';

if ($edit_status === 'success') {
    $edit_message = 'Data lokasi berhasil diperbarui.';
} elseif ($edit_status === 'duplicate') {
    $edit_message = 'Nama lokasi sudah terdaftar.';
    $edit_message_type = 'warning';
} elseif ($edit_status === 'failed') {
    $edit_message = 'Data lokasi tidak berubah atau gagal diperbarui.';
    $edit_message_type = 'danger';
} elseif (($_GET['status'] ?? null) === 'not_found') {
    $edit_message = 'Data lokasi tidak ditemukan.';
    $edit_message_type = 'warning';
}

include '../../layout/header.php';

$data_lokasi = select_lokasi("SELECT * FROM lokasi");

// Pesan status penghapusan lokasi.
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
        <?php if ($location_message !== null) : ?>
            <div class="alert <?= $location_status === 'success' ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($location_message, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
    <?php endif; ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Data Lokasi</h1>
            <p class="text-muted small mb-0">Kelola master data lokasi ruangan penyimpanan</p>
        </div>
        <?php if (has_role(['admin', 'petugas'])) : ?>
        <a href="./tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Lokasi
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
                            <th>Nama Lokasi</th>
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
                            <?php $no = 1; ?>
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
</div>

<?php include '../../layout/footer.php'; ?>
