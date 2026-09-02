<?php

include '../../layout/header.php';

$data_lokasi = select_lokasi("SELECT * FROM lokasi");

?>

<div class="container mt-5">
    <h1>Data lokasi</h1>
    <hr>

    <a href="./tambah.php" class="btn btn-primary mb-1">Tambah</a>

    <table class="table table-bordered table-striped mt-3">
        <thead class="text-center">
            <tr>
                <th>No</th>
                <th>Nama Lokasi</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody class="text-center"> 
            <?php $no = 1; ?>
            <?php foreach ($data_lokasi as $lokasi) : ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= $lokasi['nama_lokasi']; ?></td>
                    <td width="20%" class="text-center">
                        <a href="update.php?id_lokasi=<?= $lokasi['id_lokasi']; ?>" class="btn btn-success">Update</a>
                        <a href="hapus.php?id_lokasi=<?= $lokasi['id_lokasi']; ?>" class="btn btn-danger" onclick="return confirm('Yakin Data Barang Akan Dihapus');">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../../layout/footer.php'; ?>