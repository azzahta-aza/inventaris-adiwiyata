<?php

include_once '../../app/app.php';
check_role(['admin', 'kepala']);

include_once '../../layout/header.php';

$data_kategori = select_kategori("SELECT k.*, COUNT(b.id_barang) as total_barang, COALESCE(SUM(b.stok), 0) as total_stok
                                  FROM kategori k
                                  LEFT JOIN barang b ON k.id_kategori = b.id_kategori
                                  GROUP BY k.id_kategori
                                  ORDER BY k.nama_kategori ASC");

?>
<link rel="icon" type="image/png" href="../../img/logo_white.png">
<div class="container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Laporan Master Kategori</h1>
            <p class="text-muted small mb-0">Rekapitulasi kategori barang inventaris beserta total jenis barang dan stok</p>
        </div>
        <div>
            <a href="cetakpdf.php?tipe=kategori" target="_blank" class="btn btn-danger shadow-sm">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export to PDF
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-white text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Kategori</th>
                            <th>Total Jenis Barang</th>
                            <th>Total Stok Fisik</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php if (empty($data_kategori)) : ?>
                            <tr>
                                <td colspan="4" class="text-muted py-4">Belum ada data kategori.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($data_kategori as $k) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($k['nama_kategori']); ?></td>
                                    <td><?= $k['total_barang']; ?> Jenis</td>
                                    <td class="fw-bold"><?= $k['total_stok']; ?> Unit</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// PDF handled by cetakpdf.php
</script>

<?php include_once '../../layout/footer.php'; ?>
