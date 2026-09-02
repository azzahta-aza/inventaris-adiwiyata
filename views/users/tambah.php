<?php

include_once '../../layout/header.php';

if (isset($_POST['tambah'])) {
    $result = create_user($_POST);
    if ($result > 0) {
        echo "<script> 
                alert('Pengguna baru berhasil ditambahkan.');
                document.location.href = 'index.php';
              </script>";
    } elseif ($result === -1) {
        echo "<script> 
                alert('Username sudah digunakan! Silakan gunakan username lain.');
              </script>";
    } else {
        echo "<script> 
                alert('Pengguna gagal ditambahkan.');
                document.location.href = 'tambah.php';
              </script>";
    }
}

?>

<div class="container mt-5 mb-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="card-title mb-0"><i class="bi bi-person-plus me-2"></i>Tambah Pengguna Baru</h4>
        </div>
        <div class="card-body">
            <form action="" method="post">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama" class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama lengkap..." required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label fw-bold">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Contoh: petugas1" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label fw-bold">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan kata sandi..." required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label fw-bold">Hak Akses (Role)</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="Petugas">Petugas (Operasional Stok & Peminjaman)</option>
                            <option value="Admin">Admin (Akses Penuh)</option>
                            <option value="Kepala">Kepala (Monitoring & Laporan)</option>
                        </select>
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                    <button type="submit" name="tambah" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../../layout/footer.php'; ?>
