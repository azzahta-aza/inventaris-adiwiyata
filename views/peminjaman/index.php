<?php

include_once '../../layout/header.php';

// Ambil data peminjaman dengan JOIN ke barang dan users
$data_peminjaman = select_peminjaman("SELECT p.*, b.kode_barang, b.nama_barang, b.satuan, u.nama AS nama_petugas 
                                      FROM peminjaman p 
                                      LEFT JOIN barang b ON p.id_barang = b.id_barang 
                                      LEFT JOIN users u ON p.id_user = u.id_user 
                                      ORDER BY p.id_pinjam DESC");

?>

<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Data Peminjaman Barang</h1>
            <p class="text-muted">Kelola transaksi peminjaman dan pengembalian barang inventaris</p>
        </div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Pinjam Barang
        </a>
    </div>
    <hr>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Peminjam</th>
                            <th>Barang</th>
                            <th width="8%">Jumlah</th>
                            <th width="12%">Tgl Pinjam</th>
                            <th width="12%">Tgl Kembali</th>
                            <th width="10%">Status</th>
                            <th>Kondisi Kembali</th>
                            <th width="18%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data_peminjaman)) : ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Belum ada data peminjaman barang.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($data_peminjaman as $pinjam) : ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($pinjam['nama_peminjam']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($pinjam['kode_barang'] ?? ''); ?></span>
                                        <strong><?= htmlspecialchars($pinjam['nama_barang'] ?? 'Barang Dihapus'); ?></strong>
                                    </td>
                                    <td class="text-center fw-bold"><?= $pinjam['jumlah']; ?> <?= htmlspecialchars($pinjam['satuan'] ?? ''); ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($pinjam['tanggal_pinjam'])); ?></td>
                                    <td class="text-center">
                                        <?= !empty($pinjam['tanggal_kembali']) ? date('d/m/Y', strtotime($pinjam['tanggal_kembali'])) : '<span class="text-muted">-</span>'; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($pinjam['status'] === 'dipinjam') : ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i> Dipinjam</span>
                                        <?php else : ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Dikembalikan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?= !empty($pinjam['kondisi_kembali']) ? htmlspecialchars($pinjam['kondisi_kembali']) : '<span class="text-muted">-</span>'; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($pinjam['status'] === 'dipinjam') : ?>
                                            <a href="pengembalian.php?id_pinjam=<?= $pinjam['id_pinjam']; ?>" class="btn btn-sm btn-info text-white me-1" title="Proses Pengembalian">
                                                <i class="bi bi-box-arrow-in-left"></i> Kembali
                                            </a>
                                        <?php endif; ?>
                                        <a href="update.php?id_pinjam=<?= $pinjam['id_pinjam']; ?>" class="btn btn-sm btn-success me-1" title="Edit Data">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="hapus.php?id_pinjam=<?= $pinjam['id_pinjam']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data peminjaman ini?');" title="Hapus Data">
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
