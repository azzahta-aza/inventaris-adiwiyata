<?php

include_once '../../app/app.php';

check_role(['admin', 'petugas']);

$id_lokasi = (int)($_GET['id_lokasi'] ?? 0);

$lokasi_data = select_lokasi(
    "SELECT * FROM lokasi WHERE id_lokasi = $id_lokasi"
);

if (empty($lokasi_data)) {
    header('Location: index.php?status=not_found');
    exit;
}

$lokasi = $lokasi_data[0];

// Cek apakah tombol ubah ditekan
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

    $hasil = update_lokasi($_POST);

    if ($hasil === 1) {
        header('Location: index.php?edit_status=success');
        exit;
    } elseif ($hasil === -2) {
        header('Location: index.php?edit_status=duplicate');
        exit;
    } else {
        header('Location: index.php?edit_status=failed');
        exit;
    }
}

include '../../layout/header.php';

?>
<link rel="icon" type="image/png" href="../../img/logo_white.png">
<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-pencil-square me-2"></i>Update Tempat Penyimpanan</h5>
                </div>
                <div class="card-body p-4">
                    <form action="" method="post">
                        <input type="hidden" name="csrf_token"
                            value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        >
                        <input type="hidden" name="id_lokasi" value="<?= $lokasi['id_lokasi']; ?>">
                        <div class="mb-3">
                            <label for="nama_lokasi" class="form-label fw-semibold">Nama Tempat<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_lokasi" name="nama" value="<?= htmlspecialchars($lokasi['nama_lokasi']); ?>" placeholder="Nama Lokasi..." required autofocus>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" name="ubah" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../layout/footer.php'; ?>
