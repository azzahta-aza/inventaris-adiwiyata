<?php

include_once '../../layout/header.php';

$id_barang = (int)$_GET['id_barang'];

$barang_data = select_barang("SELECT * FROM barang WHERE id_barang = $id_barang");
if (empty($barang_data)) {
    echo "<script>
            alert('Data Barang tidak ditemukan');
            document.location.href = 'index.php';
          </script>";
    exit;
}
$barang = $barang_data[0];

// Ambil list semua kategori dan lokasi untuk dropdown
$data_kategori = select_kategori("SELECT * FROM kategori ORDER BY nama_kategori ASC");
$data_lokasi   = select_lokasi("SELECT * FROM lokasi ORDER BY nama_lokasi ASC");

// Cek apakah tombol ubah ditekan
if (isset($_POST['ubah'])) {
    if (update_barang($_POST) > 0) {
        echo "<script> 
                alert('Data Barang Berhasil Diperbarui');
                document.location.href = 'index.php';
              </script>";
    } else {
        echo "<script> 
                alert('Data Barang Tidak Berubah atau Gagal Diperbarui');
                document.location.href = 'index.php';
              </script>";
    }
}

?>

<div class="container mt-5 mb-5">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h4 class="card-title mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Data Barang</h4>
        </div>
        <div class="card-body">
            <form action="" method="post">
                <input type="hidden" name="id_barang" value="<?= $barang['id_barang']; ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="kode_barang" class="form-label fw-bold">Kode Barang</label>
                        <input type="text" class="form-control" id="kode_barang" name="kode_barang" value="<?= htmlspecialchars($barang['kode_barang']); ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nama_barang" class="form-label fw-bold">Nama Barang</label>
                        <input type="text" class="form-control" id="nama_barang" name="nama_barang" value="<?= htmlspecialchars($barang['nama_barang']); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <!-- Dropdown Kategori: Menampilkan nama_kategori dan menandai yang selected -->
                    <div class="col-md-6 mb-3">
                        <label for="id_kategori" class="form-label fw-bold">Kategori Barang</label>
                        <select class="form-select" id="id_kategori" name="id_kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($data_kategori as $kategori) : ?>
                                <option value="<?= $kategori['id_kategori']; ?>" <?= ($kategori['id_kategori'] == $barang['id_kategori']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($kategori['nama_kategori']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Pilih kategori barang dari master kategori.</div>
                    </div>

                    <!-- Dropdown Lokasi: Menampilkan nama_lokasi dan menandai yang selected -->
                    <div class="col-md-6 mb-3">
                        <label for="id_lokasi" class="form-label fw-bold">Lokasi Barang</label>
                        <select class="form-select" id="id_lokasi" name="id_lokasi" required>
                            <option value="">-- Pilih Lokasi --</option>
                            <?php foreach ($data_lokasi as $lokasi) : ?>
                                <option value="<?= $lokasi['id_lokasi']; ?>" <?= ($lokasi['id_lokasi'] == $barang['id_lokasi']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($lokasi['nama_lokasi']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Pilih lokasi penempatan barang dari master lokasi.</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="stok" class="form-label fw-bold">Stok</label>
                        <input type="number" class="form-control" id="stok" name="stok" min="0" value="<?= $barang['stok']; ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="satuan" class="form-label fw-bold">Satuan</label>
                        <input type="text" class="form-control" id="satuan" name="satuan" value="<?= htmlspecialchars($barang['satuan']); ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="kondisi" class="form-label fw-bold">Kondisi Barang</label>
                        <select class="form-select" id="kondisi" name="kondisi" required>
                            <option value="layak" <?= ($barang['kondisi'] === 'layak') ? 'selected' : ''; ?>>Layak</option>
                            <option value="tidak_layak" <?= ($barang['kondisi'] === 'tidak_layak') ? 'selected' : ''; ?>>Tidak Layak</option>
                            <option value="perbaikan" <?= ($barang['kondisi'] === 'perbaikan') ? 'selected' : ''; ?>>Perbaikan</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label fw-bold">Keterangan</label>
                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?= htmlspecialchars($barang['keterangan']); ?></textarea>
                </div>

                <hr>
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                    <button type="submit" name="ubah" class="btn btn-success"><i class="bi bi-save me-1"></i> Perbarui Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../../layout/footer.php'; ?>
