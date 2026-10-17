<?php

include_once '../../app/app.php';
check_role('admin');

if (isset($_POST['tambah'])) {
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

    $result = create_user($_POST);

    if ($result > 0) {
        header('Location: index.php?status=success');
        exit;
    } elseif ($result === -1) {
        header('Location: index.php?status=username_used');
        exit;
    } else {
        header('Location: index.php?status=failed');
        exit;
    }
}

include_once '../../layout/header.php';

?>
<link rel="icon" type="image/png" href="../../img/logo_white.png">
<div class="container-fluid py-2">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="card-title mb-0"><i class="bi bi-person-plus me-2"></i>Tambah Pengguna Baru</h4>
        </div>
        <div class="card-body">
            <form action="" method="post">
                <input type="hidden" name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                >
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama" class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama lengkap..." required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label fw-bold">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Contoh: petugas1" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label fw-bold">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan kata sandi..." required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label fw-bold">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="petugas">petugas (Operasional Stok & Peminjaman)</option>
                            <option value="admin">admin (Akses Penuh)</option>
                            <option value="kepala">kepala (Monitoring & Laporan)</option>
                        </select>
                    </div>
                </div>

                <hr>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                    <button type="submit" name="tambah" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../../layout/footer.php'; ?>
