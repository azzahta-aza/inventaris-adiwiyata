<?php

include_once '../../layout/header.php';

$data_lokasi = select_lokasi("SELECT l.*, COUNT(b.id_barang) as total_barang, COALESCE(SUM(b.stok), 0) as total_stok 
                              FROM lokasi l 
                              LEFT JOIN barang b ON l.id_lokasi = b.id_lokasi 
                              GROUP BY l.id_lokasi 
                              ORDER BY l.nama_lokasi ASC");

?>

<!-- html2pdf.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Laporan Master Lokasi</h1>
            <p class="text-muted">Rekapitulasi lokasi ruangan inventaris beserta total jenis barang dan stok</p>
        </div>
        <div>
            <button type="button" onclick="exportLaporanPDF()" class="btn btn-danger shadow-sm">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export to PDF
            </button>
        </div>
    </div>
    <hr>

    <div class="card shadow-sm" id="area-laporan">
        <div class="card-body p-4">
            <div class="text-center mb-4 d-none" id="kop-laporan">
                <h3 class="fw-bold mb-1">LAPORAN REKAPITULASI LOKASI RUANGAN</h3>
                <p class="text-muted mb-0">Sistem Manajemen & Pengelolaan Sarana Prasarana</p>
                <small>Tanggal Export: <?= date('d F Y, H:i'); ?> WIB</small>
                <hr style="border-top: 2px solid #000; margin-top: 10px;">
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Lokasi / Ruangan</th>
                            <th>Total Jenis Barang</th>
                            <th>Total Stok Fisik</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php if (empty($data_lokasi)) : ?>
                            <tr>
                                <td colspan="4" class="text-muted py-4">Belum ada data lokasi.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($data_lokasi as $l) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td class="text-start fw-semibold"><?= htmlspecialchars($l['nama_lokasi']); ?></td>
                                    <td><?= $l['total_barang']; ?> Jenis</td>
                                    <td class="fw-bold"><?= $l['total_stok']; ?> Unit</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tanda Tangan Khusus PDF -->
            <div class="row mt-4 d-none" id="ttd-laporan">
                <div class="col-6"></div>
                <div class="col-6 text-center">
                    <p class="mb-4">Mengetahui,<br><strong>Kepala Bagian Sarana & Prasarana</strong></p>
                    <p class="mt-4 fw-bold text-decoration-underline">( .................................................. )</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportLaporanPDF() {
    const kop = document.getElementById('kop-laporan');
    const ttd = document.getElementById('ttd-laporan');
    kop.classList.remove('d-none');
    ttd.classList.remove('d-none');

    const element = document.getElementById('area-laporan');
    const opt = {
        margin:       10,
        filename:     'Laporan_Lokasi_<?= date("Ymd_His"); ?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save().then(() => {
        kop.classList.add('d-none');
        ttd.classList.add('d-none');
    });
}
</script>

<?php include_once '../../layout/footer.php'; ?>
