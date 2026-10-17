<?php

include_once '../../app/app.php';
check_role(['admin', 'petugas']);

$data_barang = select_barang("SELECT * FROM barang ORDER BY nama_barang ASC");
$data_users  = has_role('admin') ? select_users("SELECT * FROM users ORDER BY nama ASC") : [];
$transaction_message = null;
$transaction_message_type = 'danger';

if (isset($_POST['simpan_transaksi'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $session_token = $_SESSION['csrf_token'] ?? '';

    if (
        !is_string($csrf_token) ||
        !is_string($session_token) ||
        $session_token === '' ||
        !hash_equals($session_token, $csrf_token)
    ) {
        http_response_code(403);
        exit('Permintaan tidak valid. Silakan muat ulang halaman dan coba lagi.');
    }

    $result = create_transaksi($_POST);

    if ($result === 1) {
        header('Location: index.php?status=success');
        exit;
    }

    $transaction_message = 'Transaksi gagal dicatat. Periksa kembali data yang dimasukkan.';
    $transaction_message_type = 'danger';
}

include_once '../../layout/header.php';

?>
<link rel="icon" type="image/png" href="../../img/logo_white.png">
<div class="container-fluid py-2">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="card-title mb-0"><i class="bi bi-journal-plus me-2"></i>Form Transaksi Barang (Masuk / Keluar)</h4>
        </div>
        <div class="card-body">
            <?php if ($transaction_message !== null) : ?>
                <div class="alert alert-<?= htmlspecialchars($transaction_message_type); ?>" role="alert">
                    <?= htmlspecialchars($transaction_message); ?>
                </div>
            <?php endif; ?>
            <?php if (empty($data_barang)) : ?>
                <div class="alert alert-danger" role="alert">
                    Belum ada barang yang dapat ditransaksikan. Tambahkan data barang terlebih dahulu.
                </div>
            <?php endif; ?>
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
                                    [<?= htmlspecialchars($b['kode_barang']); ?>] <?= htmlspecialchars($b['nama_barang']); ?> - (Stok Saat Ini: <?= $b['stok']; ?> <?= htmlspecialchars($b['satuan']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Jenis Transaksi: Masuk / Keluar -->
                    <div class="col-md-6 mb-3">
                        <label for="jenis_transaksi" class="form-label fw-bold">Jenis Transaksi</label>
                        <select class="form-select" id="jenis_transaksi" name="jenis_transaksi" required>
                            <option value="Masuk">Barang Masuk (Penambahan Stok / Pembelian)</option>
                            <option value="Keluar">Barang Keluar (Pengurangan Stok / Pemakaian / Afkir)</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="tanggal" class="form-label fw-bold">Tanggal Transaksi</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= date('Y-m-d'); ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="jumlah" class="form-label fw-bold">Jumlah</label>
                        <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" required>
                    </div>

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

                <div class="mb-3">
                    <label for="keterangan" class="form-label fw-bold">Keterangan / Alasan Mutasi</label>
                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3" maxlength="100" placeholder="Contoh: Pengadaan barang tahun 2026 / Pemakaian ATK divisi logistik..."></textarea>
                </div>

                <hr>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                    <button type="submit" name="simpan_transaksi" class="btn btn-primary" <?= empty($data_barang) ? 'disabled' : ''; ?>><i class="bi bi-save me-1"></i> Catat Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../../layout/footer.php'; ?>
