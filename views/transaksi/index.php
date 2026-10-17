<?php

include_once '../../app/app.php';
include '../../layout/pagination.php';
check_role(['admin', 'petugas']);

$transaction_status = $_GET['status'] ?? null;
$delete_status = $_GET['delete_status'] ?? null;

$transaction_message = null;
$transaction_message_type = 'success';

if ($transaction_status === 'success') {
    $transaction_message = 'Transaksi berhasil dicatat dan stok barang berhasil diperbarui.';
} elseif ($delete_status === 'success') {
    $transaction_message = 'Data transaksi berhasil dihapus.';
} elseif ($delete_status === 'failed') {
    $transaction_message = 'Data transaksi gagal dihapus.';
    $transaction_message_type = 'warning';
}

include_once '../../layout/header.php';

// Ambil data transaksi dengan JOIN ke barang dan users
$data_transaksi = select_transaksi("SELECT t.*, b.kode_barang, b.nama_barang, b.satuan, u.nama AS nama_petugas
                                    FROM transaksi t
                                    LEFT JOIN barang b ON t.id_barang = b.id_barang
                                    LEFT JOIN users u ON t.id_user = u.id_user
                                    ORDER BY t.id_transaksi DESC");

?>
<link rel="icon" type="image/png" href="../../img/logo_white.png">
<div class="container-fluid py-2">
     <?php if ($transaction_message !== null) : ?>
        <div class="alert alert-<?= htmlspecialchars($transaction_message_type, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($transaction_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Riwayat Transaksi Barang</h1>
            <p class="text-muted small mb-0">Pencatatan mutasi barang masuk (pembelian/restock) dan barang keluar</p>
        </div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Catat Transaksi
        </a>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-header-green text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th width="12%">Tanggal</th>
                            <th>Nama Barang</th>
                            <th width="15%">Jenis Transaksi</th>
                            <th width="10%">Jumlah</th>
                            <th>Keterangan</th>
                            <th width="15%">Petugas</th>
                            <th width="8%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php if (empty($data_transaksi)) : ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada data transaksi barang.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($data_transaksi as $tr) : ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($tr['tanggal'])); ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($tr['kode_barang'] ?? ''); ?></span>
                                        <strong><?= htmlspecialchars($tr['nama_barang'] ?? 'Barang Dihapus'); ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <?php if (strtolower($tr['jenis_transaksi']) === 'masuk') : ?>
                                            <span class="badge bg-success"><i class="bi bi-arrow-down-circle me-1"></i> Barang Masuk</span>
                                        <?php else : ?>
                                            <span class="badge bg-danger"><i class="bi bi-arrow-up-circle me-1"></i> Barang Keluar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center fw-bold">
                                        <?= (strtolower($tr['jenis_transaksi']) === 'masuk' ? '+' : '-') . $tr['jumlah']; ?> <?= htmlspecialchars($tr['satuan'] ?? ''); ?>
                                    </td>
                                    <td><?= htmlspecialchars($tr['keterangan']); ?></td>
                                    <td><?= htmlspecialchars($tr['nama_petugas'] ?? 'Petugas'); ?></td>
                                    <td class="text-center text-nowrap">
                                        <form action="hapus.php" method="POST" class="d-inline"
                                            onsubmit="return confirm('Yakin ingin menghapus riwayat transaksi ini? Mutasi stok akan dibatalkan.');">
                                            <input type="hidden" name="id_transaksi"
                                                value="<?= (int) $tr['id_transaksi']; ?>">
                                            <input type="hidden" name="csrf_token"
                                                value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus Transaksi">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
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
