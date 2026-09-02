<?php

include '../../layout/header.php';

$data_kategori = select_kategori("SELECT * FROM kategori");

?>

<div class="container mt-5">
    <h1>Data kategori</h1>
    <hr>

    <a href="./tambah.php" class="btn btn-primary mb-1">Tambah</a>

    <table class="table table-bordered table-striped mt-3">
        <thead class="text-center">
            <tr>
                <th>No</th>
                <th>Nama Kategori</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody class="text-center">
            <?php $no = 1; ?>
            <?php foreach ($data_kategori as $kategori) : ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= $kategori['nama_kategori']; ?></td>
                    <td width="20%" class="text-center">
                        <a href="./update.php?id_kategori=<?= $kategori['id_kategori']; ?>" class="btn btn-success">Update</a>
                        <a href="./hapus.php?id_kategori=<?= $kategori['id_kategori']; ?>" class="btn btn-danger" onclick="return confirm('Yakin Data Barang Akan Dihapus');">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../../layout/footer.php'; ?>