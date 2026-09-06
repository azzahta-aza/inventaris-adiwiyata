<?php

include_once '../../app/app.php';
check_role(['admin', 'petugas']);

include_once '../../layout/header.php';

// Ambil data barang yang stoknya > 0 dan kondisinya layak
$data_barang = select_barang("SELECT * FROM barang WHERE stok > 0 AND kondisi = 'layak' ORDER BY nama_barang ASC");
$data_users  = has_role('admin') ? select_users("SELECT * FROM users ORDER BY nama ASC") : [];

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

<div class="container-fluid py-2">
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

                    <!-- Petugas Pencatat -->
                    <div class="col-md-4 mb-3">
                        <label for="id_user" class="form-label fw-bold">Petugas Pencatat</label>
                        <?php if (has_role('admin') && !empty($data_users)) : ?>
                            <select class="form-select" id="id_user" name="id_user">
                                <?php foreach ($data_users as $user) : ?>
                                    <option value="<?= $user['id_user']; ?>" <?= ($user['id_user'] == ($_SESSION['id_user'] ?? 1)) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($user['nama']); ?> (<?= htmlspecialchars($user['role']); ?>)
                                    </option>
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
