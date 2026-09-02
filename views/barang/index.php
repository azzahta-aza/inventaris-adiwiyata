<?php

include_once '../../layout/header.php';

// Ambil data barang dengan LEFT JOIN agar nama_kategori dan nama_lokasi tampil
$data_barang = select_barang("SELECT b.*, k.nama_kategori, l.nama_lokasi 
                              FROM barang b 
                              LEFT JOIN kategori k ON b.id_kategori = k.id_kategori 
                              LEFT JOIN lokasi l ON b.id_lokasi = l.id_lokasi 
                              ORDER BY b.id_barang DESC");

?>

<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Data Master Barang</h1>
            <p class="text-muted">Kelola data barang inventaris beserta kategori dan lokasi</p>
        </div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Barang
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
                            <th width="12%">Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th width="8%">Stok</th>
                            <th width="8%">Satuan</th>
                            <th width="10%">Kondisi</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data_barang)) : ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Belum ada data barang. Silakan klik tombol "Tambah Barang".</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($data_barang as $barang) : ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($barang['kode_barang']); ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($barang['nama_barang']); ?></div>
                                        <?php if (!empty($barang['keterangan'])) : ?>
                                            <small class="text-muted"><?= htmlspecialchars($barang['keterangan']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <!-- Menampilkan Nama Kategori dari Tabel Kategori -->
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            <?= htmlspecialchars($barang['nama_kategori'] ?? 'Tidak Terkategori'); ?>
                                        </span>
                                    </td>
                                    <!-- Menampilkan Nama Lokasi dari Tabel Lokasi -->
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= htmlspecialchars($barang['nama_lokasi'] ?? 'Belum Ditentukan'); ?>
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold"><?= $barang['stok']; ?></td>
                                    <td class="text-center"><?= htmlspecialchars($barang['satuan']); ?></td>
                                    <td class="text-center">
                                        <?php if ($barang['kondisi'] === 'layak') : ?>
                                            <span class="badge bg-success">Layak</span>
                                        <?php elseif ($barang['kondisi'] === 'perbaikan') : ?>
                                            <span class="badge bg-warning text-dark">Perbaikan</span>
                                        <?php else : ?>
                                            <span class="badge bg-danger">Tidak Layak</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="update.php?id_barang=<?= $barang['id_barang']; ?>" class="btn btn-sm btn-success">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <a href="hapus.php?id_barang=<?= $barang['id_barang']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data barang <?= htmlspecialchars($barang['nama_barang']); ?>?');">
                                            <i class="bi bi-trash"></i> Hapus
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
