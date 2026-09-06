<?php

include_once '../../app/app.php';
check_role('admin');

include_once '../../layout/header.php';

// Ambil list semua kategori dan lokasi untuk opsi dropdown
$data_kategori = select_kategori("SELECT * FROM kategori ORDER BY nama_kategori ASC");
$data_lokasi   = select_lokasi("SELECT * FROM lokasi ORDER BY nama_lokasi ASC");

// Cek apakah tombol tambah ditekan
if (isset($_POST['tambah'])) {
    if (create_barang($_POST) > 0) {
        echo "<script> 
                alert('Data Barang Berhasil Ditambahkan');
                document.location.href = 'index.php';
              </script>";
    } else {
        echo "<script> 
                alert('Data Barang Gagal Ditambahkan');
                document.location.href = 'tambah.php';
              </script>";
    }
}

?>

<div class="container-fluid py-2">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="card-title mb-0"><i class="bi bi-plus-circle me-2"></i>Tambah Data Barang</h4>
        </div>
        <div class="card-body">
            <form action="" method="post">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="kode_barang" class="form-label fw-bold">Kode Barang</label>
                        <input type="text" class="form-control" id="kode_barang" name="kode_barang" placeholder="Contoh: BRG-001" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nama_barang" class="form-label fw-bold">Nama Barang</label>
                        <input type="text" class="form-control" id="nama_barang" name="nama_barang" placeholder="Masukkan nama barang..." required>
                    </div>
                </div>

                <div class="row">
                    <!-- Dropdown Kategori: Menampilkan nama_kategori dari tabel kategori -->
                    <div class="col-md-6 mb-3">
                        <label for="id_kategori" class="form-label fw-bold">Kategori Barang</label>
                        <select class="form-select" id="id_kategori" name="id_kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($data_kategori as $kategori) : ?>
                                <option value="<?= $kategori['id_kategori']; ?>">
                                    <?= htmlspecialchars($kategori['nama_kategori']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Pilih kategori barang dari data yang tersedia di master kategori.</div>
                    </div>

                    <!-- Dropdown Lokasi: Menampilkan nama_lokasi dari tabel lokasi -->
                    <div class="col-md-6 mb-3">
                        <label for="id_lokasi" class="form-label fw-bold">Lokasi Barang</label>
                        <select class="form-select" id="id_lokasi" name="id_lokasi" required>
                            <option value="">-- Pilih Lokasi --</option>
                            <?php foreach ($data_lokasi as $lokasi) : ?>
                                <option value="<?= $lokasi['id_lokasi']; ?>">
                                    <?= htmlspecialchars($lokasi['nama_lokasi']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Pilih lokasi penempatan barang dari master lokasi.</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="stok" class="form-label fw-bold">Stok Awal</label>
                        <input type="number" class="form-control" id="stok" name="stok" min="0" placeholder="0" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="satuan" class="form-label fw-bold">Satuan</label>
                        <input type="text" class="form-control" id="satuan" name="satuan" placeholder="Contoh: Unit, Pcs, Box, Set" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="kondisi" class="form-label fw-bold">Kondisi Barang</label>
                        <select class="form-select" id="kondisi" name="kondisi" required>
                            <option value="layak">Layak</option>
                            <option value="tidak_layak">Tidak Layak</option>
                            <option value="perbaikan">Perbaikan</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label fw-bold">Keterangan</label>
                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Catatan spesifikasi / detail barang..."></textarea>
                </div>

                <hr>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                    <button type="submit" name="tambah" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Barang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../../layout/footer.php'; ?>
