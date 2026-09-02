<?php

include_once '../../layout/header.php';

$data_barang = select_barang("SELECT * FROM barang ORDER BY nama_barang ASC");
$data_users  = select_users("SELECT * FROM users ORDER BY nama ASC");

if (isset($_POST['simpan_transaksi'])) {
    $result = create_transaksi($_POST);
    if ($result > 0) {
        echo "<script> 
                alert('Transaksi barang berhasil dicatat dan stok berhasil diperbarui.');
                document.location.href = 'index.php';
              </script>";
    } elseif ($result === -1) {
        echo "<script> 
                alert('Gagal! Stok barang tidak mencukupi untuk transaksi barang keluar.');
              </script>";
    } else {
        echo "<script> 
                alert('Transaksi barang gagal dicatat.');
                document.location.href = 'tambah.php';
              </script>";
    }
}

?>

<div class="container mt-5 mb-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="card-title mb-0"><i class="bi bi-journal-plus me-2"></i>Form Transaksi Barang (Masuk / Keluar)</h4>
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
                        <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" placeholder="1" required>
                    </div>

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

                <div class="mb-3">
                    <label for="keterangan" class="form-label fw-bold">Keterangan / Alasan Mutasi</label>
                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Contoh: Pengadaan barang tahun 2026 / Pemakaian ATK divisi logistik..."></textarea>
                </div>

                <hr>
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                    <button type="submit" name="simpan_transaksi" class="btn btn-primary"><i class="bi bi-save me-1"></i> Catat Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../../layout/footer.php'; ?>
