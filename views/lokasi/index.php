<?php

include_once '../../app/app.php';
check_role('admin');

include '../../layout/header.php';

$data_lokasi = select_lokasi("SELECT * FROM lokasi");

?>

<div class="container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Data Lokasi</h1>
            <p class="text-muted small mb-0">Kelola master data lokasi ruangan penyimpanan</p>
        </div>
        <a href="./tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Lokasi
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama Lokasi</th>
                            <th style="width: 180px;" class="text-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data_lokasi)) : ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">Belum ada data lokasi.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($data_lokasi as $lokasi) : ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($lokasi['nama_lokasi']); ?></td>
                                    <td class="text-center text-nowrap">
                                        <a href="update.php?id_lokasi=<?= $lokasi['id_lokasi']; ?>" class="btn btn-success btn-sm me-1">
                                            <i class="bi bi-pencil-square me-1"></i> Edit
                                        </a>
                                        <a href="hapus.php?id_lokasi=<?= $lokasi['id_lokasi']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin Data Lokasi Akan Dihapus?');">
                                            <i class="bi bi-trash me-1"></i> Hapus
                                        </a>
                                    </td>
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