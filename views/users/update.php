<?php

include_once '../../app/app.php';
check_role('admin');

$id_user = filter_input(INPUT_GET, 'id_user', FILTER_VALIDATE_INT);

$id_user = filter_input(INPUT_GET, 'id_user', FILTER_VALIDATE_INT);

if ($id_user === false || $id_user === null || $id_user <= 0) {
    header('Location: index.php?edit_status=invalid');
    exit;
}

$user_data = select_users(
    "SELECT * FROM users WHERE id_user = $id_user"
);

if (empty($user_data)) {
    header('Location: index.php?edit_status=not_found');
    exit;
}

$user = $user_data[0];

if (isset($_POST['ubah'])) {
    $token_sesi = $_SESSION['csrf_token'] ?? '';
    $token_form = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token_sesi) ||
        !is_string($token_form) ||
        $token_sesi === '' ||
        !hash_equals($token_sesi, $token_form)
    ) {
        http_response_code(403);
        exit('Permintaan tidak valid. Silakan muat ulang halaman.');
    }

    $result = update_user($_POST);

    if ($result > 0) {
        header('Location: index.php?edit_status=success');
        exit;
    } elseif ($result === -1) {
        header('Location: index.php?edit_status=duplicate');
        exit;
    } elseif ($result === -2) {
        header('Location: index.php?edit_status=last_admin');
        exit;
    } else {
        header('Location: index.php?edit_status=failed');
        exit;
    }
}

include_once '../../layout/header.php';

?>

<div class="container-fluid py-2">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h4 class="card-title mb-0"><i class="bi bi-person-gear me-2"></i>Edit Data Pengguna</h4>
        </div>
        <div class="card-body">
            <form action="" method="post">
                <input type="hidden" name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                >
                <input type="hidden" name="id_user" value="<?= $user['id_user']; ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama" class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($user['nama']); ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label fw-bold">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label fw-bold">Password Baru <small class="text-muted">(Kosongkan jika tidak ingin mengubah)</small></label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password baru jika ingin diganti...">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label fw-bold">Hak Akses (Role)</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="petugas" <?= ($user['role'] === 'petugas') ? 'selected' : ''; ?>>Petugas (Operasional Stok & Peminjaman)</option>
                            <option value="admin" <?= ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin (Akses Penuh)</option>
                            <option value="kepala" <?= ($user['role'] === 'kepala') ? 'selected' : ''; ?>>Kepala (Monitoring & Laporan)</option>
                        </select>
                    </div>
                </div>

                <hr>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                    <button type="submit" name="ubah" class="btn btn-success"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../../layout/footer.php'; ?>
