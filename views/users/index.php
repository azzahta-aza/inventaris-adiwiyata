<?php

include_once '../../app/app.php';
check_role('admin');

include_once '../../layout/header.php';

$data_users = select_users("SELECT * FROM users ORDER BY id_user DESC");

?>

<div class="container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Manajemen Pengguna (Users)</h1>
            <p class="text-muted small mb-0">Kelola hak akses dan akun pengguna sistem inventaris</p>
        </div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> Tambah Pengguna
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle">
                    <thead class="table-dark text-center">
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
                            <?php $no = 1; ?>
                            <?php foreach ($data_users as $user) : ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
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
                                    <td class="text-center text-nowrap">
                                        <a href="update.php?id_user=<?= $user['id_user']; ?>" class="btn btn-sm btn-success me-1">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <a href="hapus.php?id_user=<?= $user['id_user']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus akun user <?= htmlspecialchars($user['nama']); ?>?');">
                                            <i class="bi bi-trash"></i> Hapus
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

<?php include_once '../../layout/footer.php'; ?>
