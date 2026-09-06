<?php

include_once '../../app/app.php';
check_role(['admin', 'petugas']);

include_once '../../layout/header.php';

// Ambil data transaksi dengan JOIN ke barang dan users
$data_transaksi = select_transaksi("SELECT t.*, b.kode_barang, b.nama_barang, b.satuan, u.nama AS nama_petugas 
                                    FROM transaksi t 
                                    LEFT JOIN barang b ON t.id_barang = b.id_barang 
                                    LEFT JOIN users u ON t.id_user = u.id_user 
                                    ORDER BY t.id_transaksi DESC");

?>

<div class="container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Riwayat Transaksi Barang</h1>
            <p class="text-muted small mb-0">Pencatatan mutasi barang masuk (pembelian/restock) dan barang keluar</p>
        </div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Catat Transaksi
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle">
                    <thead class="table-dark text-center">
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
                    <tbody>
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
                                        <a href="hapus.php?id_transaksi=<?= $tr['id_transaksi']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus riwayat transaksi ini? Mutasi stok akan dibatalkan.');" title="Hapus Transaksi">
                                            <i class="bi bi-trash"></i>
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
