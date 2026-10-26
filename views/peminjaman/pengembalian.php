<?php

include_once '../../app/app.php';

check_role(['admin', 'petugas']);

$id_pinjam = (int)($_GET['id_pinjam'] ?? 0);

$peminjaman_data = select_peminjaman(
    "SELECT p.*, b.kode_barang, b.nama_barang, b.satuan
    FROM peminjaman p
    JOIN barang b ON p.id_barang = b.id_barang
    WHERE p.id_pinjam = $id_pinjam AND p.status = 'dipinjam'"
);

if (empty($peminjaman_data)) {
    header('Location: index.php?return_status=already_returned');
    exit;
}

$pinjam = $peminjaman_data[0];

if (isset($_POST['proses_kembali'])) {
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

    $result = kembalikan_peminjaman($_POST);

    if ($result === 1) {
        header('Location: index.php?return_status=success');
        exit;
    } else {
        header('Location: index.php?return_status=failed');
        exit;
    }
}

include_once '../../layout/header.php';

?>
<link rel="icon" type="image/png" href="../../img/logo_white.png">
<div class="container-fluid py-2">
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h4 class="card-title mb-0"><i class="bi bi-box-arrow-in-left me-2"></i>Form Pengembalian Barang</h4>
        </div>
        <div class="card-body">
            <div class="alert alert-light border">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Nama Peminjam:</strong> <?= htmlspecialchars($pinjam['nama_peminjam']); ?><br>
                        <strong>Nama Barang:</strong> [<?= htmlspecialchars($pinjam['kode_barang']); ?>] <?= htmlspecialchars($pinjam['nama_barang']); ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Jumlah Dipinjam:</strong> <?= $pinjam['jumlah']; ?> <?= htmlspecialchars($pinjam['satuan']); ?><br>
                        <strong>Tanggal Pinjam:</strong> <?= date('d/m/Y', strtotime($pinjam['tanggal_pinjam'])); ?><br>
                        <strong>Tanggal Kembali (Rencana):</strong> <?= !empty($pinjam['tanggal_kembali']) ? date('d/m/Y', strtotime($pinjam['tanggal_kembali'])) : '-'; ?>
                    </div>
                </div>
            </div>

            <form action="" method="post">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                >
                <input type="hidden" name="id_pinjam" value="<?= $pinjam['id_pinjam']; ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_kembali" class="form-label fw-bold">
                                
                        </label>

                    <input
                        type="date"
                        class="form-control"
                        id="tanggal_kembali"
                        name="tanggal_kembali"
                        value="<?= !empty($pinjam['tanggal_kembali']) ? htmlspecialchars($pinjam['tanggal_kembali'], ENT_QUOTES, 'UTF-8') : date('Y-m-d'); ?>"
                        min="<?= htmlspecialchars($pinjam['tanggal_pinjam'], ENT_QUOTES, 'UTF-8'); ?>"
                        required
                    >
                    <div class="form-text">Tanggal saat barang dikembalikan (minimal tanggal pinjam). Bisa diubah sesuai kenyataan.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="kondisi_kembali" class="form-label fw-bold">Kondisi Barang Saat Kembali</label>
                        <select class="form-select" id="kondisi_kembali" name="kondisi_kembali" required>
                            <option value="Baik / Layak">Baik / Layak</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                            <option value="Perlu Perbaikan">Perlu Perbaikan</option>
                        </select>
                    </div>
                </div>

                <hr>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                    <button type="submit" name="proses_kembali" class="btn btn-success"><i class="bi bi-check2-circle me-1"></i> Konfirmasi Pengembalian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../../layout/footer.php'; ?>