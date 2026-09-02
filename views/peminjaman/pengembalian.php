<?php

include_once '../../layout/header.php';

$id_pinjam = (int)$_GET['id_pinjam'];

$peminjaman_data = select_peminjaman("SELECT p.*, b.kode_barang, b.nama_barang, b.satuan 
                                      FROM peminjaman p 
                                      JOIN barang b ON p.id_barang = b.id_barang 
                                      WHERE p.id_pinjam = $id_pinjam AND p.status = 'dipinjam'");

if (empty($peminjaman_data)) {
    echo "<script>
            alert('Data peminjaman tidak ditemukan atau barang sudah dikembalikan.');
            document.location.href = 'index.php';
          </script>";
    exit;
}

$pinjam = $peminjaman_data[0];

if (isset($_POST['proses_kembali'])) {
    if (kembalikan_peminjaman($_POST) > 0) {
        echo "<script> 
                alert('Pengembalian barang berhasil diproses dan stok barang telah dikembalikan.');
                document.location.href = 'index.php';
              </script>";
    } else {
        echo "<script> 
                alert('Gagal memproses pengembalian barang.');
              </script>";
    }
}

?>

<div class="container mt-5 mb-5">
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h4 class="card-title mb-0"><i class="bi bi-box-arrow-in-left me-2"></i>Form Pengembalian Barang</h4>
        </div>
        <div class="card-body">
            <div class="alert alert-light border">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Nama Peminjam:</strong> <?= htmlspecialchars($pinjam['nama_peminjam']); ?><br>
                        <strong>Nama Barang:</strong> [<?= htmlspecialchars($pinjam['kode_barang']); ?>] <?= htmlspecialchars($pinjam['nama_barang']); ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Jumlah Dipinjam:</strong> <?= $pinjam['jumlah']; ?> <?= htmlspecialchars($pinjam['satuan']); ?><br>
                        <strong>Tanggal Pinjam:</strong> <?= date('d/m/Y', strtotime($pinjam['tanggal_pinjam'])); ?>
                    </div>
                </div>
            </div>

            <form action="" method="post">
                <input type="hidden" name="id_pinjam" value="<?= $pinjam['id_pinjam']; ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_kembali" class="form-label fw-bold">Tanggal Pengembalian</label>
                        <input type="date" class="form-control" id="tanggal_kembali" name="tanggal_kembali" value="<?= date('Y-m-d'); ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="kondisi_kembali" class="form-label fw-bold">Kondisi Barang Saat Kembali</label>
                        <select class="form-select" id="kondisi_kembali" name="kondisi_kembali" required>
                            <option value="Baik / Layak">Baik / Layak</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                            <option value="Perlu Perbaikan">Perlu Perbaikan</option>
                        </select>
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                    <button type="submit" name="proses_kembali" class="btn btn-success"><i class="bi bi-check2-circle me-1"></i> Konfirmasi Pengembalian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../../layout/footer.php'; ?>
