<?php

include_once '../../layout/header.php';

// Ambil data barang yang stoknya > 0 dan kondisinya layak
$data_barang = select_barang("SELECT * FROM barang WHERE stok > 0 AND kondisi = 'layak' ORDER BY nama_barang ASC");
$data_users  = select_users("SELECT * FROM users ORDER BY nama ASC");

if (isset($_POST['pinjam'])) {
    $result = create_peminjaman($_POST);
    if ($result > 0) {
        echo "<script> 
                alert('Peminjaman barang berhasil dicatat dan stok barang telah dikurangi');
                document.location.href = 'index.php';
              </script>";
    } elseif ($result === -1) {
        echo "<script> 
                alert('Gagal! Jumlah barang yang dipinjam melebihi stok yang tersedia.');
              </script>";
    } else {
        echo "<script> 
                alert('Peminjaman barang gagal dicatat.');
                document.location.href = 'tambah.php';
              </script>";
    }
}

?>

<div class="container mt-5 mb-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="card-title mb-0"><i class="bi bi-box-arrow-right me-2"></i>Form Tambah Peminjaman Barang</h4>
        </div>
        <div class="card-body">
            <form action="" method="post">
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
                        <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" placeholder="1" required>
                    </div>

                    <!-- Dropdown Petugas (Opsional jika ada users) -->
                    <div class="col-md-4 mb-3">
                        <label for="id_user" class="form-label fw-bold">Petugas Pencatat</label>
                        <select class="form-select" id="id_user" name="id_user">
                            <?php if (empty($data_users)) : ?>
                                <option value="1">Petugas Default</option>
                            <?php else : ?>
                                <?php foreach ($data_users as $user) : ?>
                                    <option value="<?= $user['id_user']; ?>">
                                        <?= htmlspecialchars($user['nama']); ?> (<?= htmlspecialchars($user['role']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                    <button type="submit" name="pinjam" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> Proses Peminjaman</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../../layout/footer.php'; ?>
