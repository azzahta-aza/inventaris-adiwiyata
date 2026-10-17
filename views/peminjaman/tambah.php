<?php

include_once '../../app/app.php';
check_role(['admin', 'petugas']);

// Ambil data barang yang stoknya > 0 dan kondisinya layak
$data_barang = select_barang("SELECT * FROM barang WHERE stok > 0 AND kondisi = 'layak' ORDER BY nama_barang ASC");
$data_users  = has_role('admin') ? select_users("SELECT * FROM users ORDER BY nama ASC") : [];

if (isset($_POST['pinjam'])) {
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

    $result = create_peminjaman($_POST);

    if ($result > 0) {
        header('Location: index.php?status=success');
        exit;
    } elseif ($result === -1) {
        header('Location: tambah.php?status=stok_kurang');
        exit;
    } else {
        header('Location: tambah.php?status=failed');
        exit;
    }
}

include_once '../../layout/header.php';

?>

<div class="container-fluid py-2">
    <?php
    $tambah_status = $_GET['status'] ?? null;
    $tambah_message = null;
    $tambah_message_type = 'danger';

    if ($tambah_status === 'stok_kurang') {
        $tambah_message = 'Jumlah barang yang dipinjam melebihi stok yang tersedia.';
    } elseif ($tambah_status === 'failed') {
        $tambah_message = 'Peminjaman barang gagal dicatat.';
    }
    ?>
<link rel="icon" type="image/png" href="../../img/logo_white.png">
    <?php if ($tambah_message !== null) : ?>
        <div class="alert alert-<?= htmlspecialchars($tambah_message_type, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($tambah_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="card-title mb-0"><i class="bi bi-box-arrow-right me-2"></i>Form Tambah Peminjaman Barang</h4>
        </div>
        <div class="card-body">
            <form action="" method="post">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                >
                <div class="row">
                    <!-- Dropdown Pilih Barang -->
                    <div class="col-md-6 mb-3">
                        <label for="id_barang" class="form-label fw-bold">Pilih Barang</label>
                        <select class="form-select" id="id_barang" name="id_barang" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php foreach ($data_barang as $b) : ?>
                                <option value="<?= $b['id_barang']; ?>">
                                    [<?= htmlspecialchars($b['kode_barang']); ?>] <?= htmlspecialchars($b['nama_barang']); ?> - (Tersedia: <?= $b['stok']; ?> <?= htmlspecialchars($b['satuan']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Hanya menampilkan barang berstatus layak dan memiliki stok.</div>
                    </div>

                    <!-- Input Nama Peminjam -->
                    <div class="col-md-6 mb-3">
                        <label for="nama_peminjam" class="form-label fw-bold">Nama Peminjam</label>
                        <input type="text" class="form-control" id="nama_peminjam" name="nama_peminjam" placeholder="Masukkan nama peminjam (Siswa/Guru/Karyawan)..." required>
                    </div>
                </div>

                <div class="row">
                    <!-- Input Tanggal Pinjam -->
                    <div class="col-md-4 mb-3">
                        <label for="tanggal_pinjam" class="form-label fw-bold">Tanggal Pinjam</label>
                        <input type="date" class="form-control" id="tanggal_pinjam" name="tanggal_pinjam" value="<?= date('Y-m-d'); ?>" required>
                    </div>

                    <!-- Input Jumlah Pinjam -->
                    <div class="col-md-4 mb-3">
                        <label for="jumlah" class="form-label fw-bold">Jumlah Pinjam</label>
                        <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" required>
                    </div>

                    <!-- Petugas Pencatat -->
                    <div class="col-md-4 mb-3">
                        <label for="id_user" class="form-label fw-bold">Petugas Pencatat</label>
                            <?php if (has_role('admin') && !empty($data_users)) : ?>
                            <select class="form-select" id="id_user" name="id_user">
                                <?php foreach ($data_users as $user) : ?>
                                    <?php if (in_array(strtolower($user['role']), ['admin', 'petugas'])) : ?>
                                        <option value="<?= $user['id_user']; ?>" <?= ($user['id_user'] == ($_SESSION['id_user'] ?? 1)) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($user['nama']); ?> (<?= htmlspecialchars($user['role']); ?>)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        <?php else : ?>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['nama'] ?? 'Petugas'); ?> (<?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Petugas')); ?>)" readonly>
                            <input type="hidden" name="id_user" value="<?= (int)($_SESSION['id_user'] ?? 1); ?>">
                        <?php endif; ?>
                    </div>
                </div>

                <hr>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                    <button type="submit" name="pinjam" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> Proses Peminjaman</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../../layout/footer.php'; ?>
