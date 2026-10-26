<?php

include_once '../../app/app.php';
check_role(['admin', 'petugas']);

$id_pinjam = (int)($_GET['id_pinjam'] ?? 0);

$peminjaman_data = select_peminjaman(
    "SELECT p.*, b.nama_barang, b.kode_barang,
    u.nama AS nama_petugas, u.role AS role_petugas
    FROM peminjaman p
    JOIN barang b ON p.id_barang = b.id_barang
    LEFT JOIN users u ON p.id_user = u.id_user
    WHERE p.id_pinjam = $id_pinjam"
);

if (empty($peminjaman_data)) {
    header('Location: index.php?edit_status=not_found');
    exit;
}

$pinjam = $peminjaman_data[0];

if (isset($_POST['ubah'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $session_token = $_SESSION['csrf_token'] ?? '';

    if (
        !is_string($csrf_token) ||
        !is_string($session_token) ||
        $session_token === '' ||
        !hash_equals($session_token, $csrf_token)
    ) {
        http_response_code(403);
        exit('Permintaan ditolak. Silakan muat ulang halaman dan coba lagi.');
    }

    $result = update_peminjaman($_POST);

    if ($result === 1) {
        header('Location: index.php?edit_status=success');
        exit;
    } else {
        header('Location: index.php?edit_status=failed');
        exit;
    }

}

include_once '../../layout/header.php';

?>
<link rel="icon" type="image/png" href="../../img/logo_white.png">

<div class="container-fluid py-2">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h4 class="card-title mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Data Peminjaman</h4>
        </div>
        <div class="card-body">
            <form action="" method="post">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                >
                <input type="hidden" name="id_pinjam" value="<?= $pinjam['id_pinjam']; ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Barang</label>
                        <input type="text" class="form-control" value="[<?= htmlspecialchars($pinjam['kode_barang']); ?>] <?= htmlspecialchars($pinjam['nama_barang']); ?>" disabled>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nama_peminjam" class="form-label fw-bold">Nama Peminjam</label>
                        <input type="text" class="form-control" id="nama_peminjam" name="nama_peminjam" value="<?= htmlspecialchars($pinjam['nama_peminjam']); ?>" maxlength="50" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_pinjam" class="form-label fw-bold">Tanggal Pinjam</label>
                        <input
                            type="date"
                            class="form-control"
                            id="tanggal_pinjam"
                            name="tanggal_pinjam"
                            value="<?= htmlspecialchars($pinjam['tanggal_pinjam']); ?>"
                            readonly
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Jumlah Pinjam</label>
                        <input type="number" class="form-control" value="<?= $pinjam['jumlah']; ?>" disabled>
                        <div class="form-text">Jumlah pinjam tidak dapat diubah setelah stok dikurangi.</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_kembali" class="form-label fw-bold">Tanggal Kembali</label>
                        <input
                            type="date"
                            class="form-control"
                            id="tanggal_kembali"
                            value="<?= !empty($pinjam['tanggal_kembali']) ? htmlspecialchars($pinjam['tanggal_kembali']) : ''; ?>"
                            placeholder="Belum dikembalikan"
                            readonly
                        >
                        <div class="form-text">Belum dikembalikan, diisi melalui menu Pengembalian.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Petugas Pencatat</label>
                        <input
                            type="text"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                ($pinjam['nama_petugas'] ?? '-') .
                                (isset($pinjam['role_petugas']) && $pinjam['role_petugas'] !== ''
                                    ? ' (' . $pinjam['role_petugas'] . ')'
                                    : ''),
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>"
                            readonly
                        >
                        <div class="form-text">Pencatat awal tidak dapat diubah.</div>
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
