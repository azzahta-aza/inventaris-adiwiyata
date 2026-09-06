<?php

include_once '../../app/app.php';
check_role(['admin', 'kepala']);

$tipe = $_GET['tipe'] ?? 'barang';

// Ambil data sesuai tipe laporan
if ($tipe === 'peminjaman') {
    $judul_laporan = 'LAPORAN PEMINJAMAN BARANG';
    $tgl_mulai  = isset($_GET['tgl_mulai']) && $_GET['tgl_mulai'] !== '' ? mysqli_real_escape_string($db, $_GET['tgl_mulai']) : '';
    $tgl_sampai = isset($_GET['tgl_sampai']) && $_GET['tgl_sampai'] !== '' ? mysqli_real_escape_string($db, $_GET['tgl_sampai']) : '';
    $status     = isset($_GET['status']) && $_GET['status'] !== '' ? mysqli_real_escape_string($db, $_GET['status']) : '';

    $where_clauses = [];
    if ($tgl_mulai !== '' && $tgl_sampai !== '') {
        $where_clauses[] = "p.tanggal_pinjam BETWEEN '$tgl_mulai' AND '$tgl_sampai'";
    }
    if ($status !== '') {
        $where_clauses[] = "p.status = '$status'";
    }
    $where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

    $data = select_peminjaman("SELECT p.*, b.kode_barang, b.nama_barang, b.satuan, u.nama AS nama_petugas 
                              FROM peminjaman p 
                              LEFT JOIN barang b ON p.id_barang = b.id_barang 
                              LEFT JOIN users u ON p.id_user = u.id_user 
                              $where_sql 
                              ORDER BY p.tanggal_pinjam DESC");
} elseif ($tipe === 'transaksi') {
    $judul_laporan = 'LAPORAN MUTASI TRANSAKSI BARANG';
    $tgl_mulai  = isset($_GET['tgl_mulai']) && $_GET['tgl_mulai'] !== '' ? mysqli_real_escape_string($db, $_GET['tgl_mulai']) : '';
    $tgl_sampai = isset($_GET['tgl_sampai']) && $_GET['tgl_sampai'] !== '' ? mysqli_real_escape_string($db, $_GET['tgl_sampai']) : '';
    $jenis      = isset($_GET['jenis']) && $_GET['jenis'] !== '' ? mysqli_real_escape_string($db, $_GET['jenis']) : '';

    $where_clauses = [];
    if ($tgl_mulai !== '' && $tgl_sampai !== '') {
        $where_clauses[] = "t.tanggal BETWEEN '$tgl_mulai' AND '$tgl_sampai'";
    }
    if ($jenis !== '') {
        $where_clauses[] = "LOWER(t.jenis_transaksi) = '" . strtolower($jenis) . "'";
    }
    $where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

    $data = select_transaksi("SELECT t.*, b.kode_barang, b.nama_barang, b.satuan, u.nama AS nama_petugas 
                            FROM transaksi t 
                            LEFT JOIN barang b ON t.id_barang = b.id_barang 
                            LEFT JOIN users u ON t.id_user = u.id_user 
                            $where_sql 
                            ORDER BY t.tanggal DESC");
} elseif ($tipe === 'kategori') {
    $judul_laporan = 'LAPORAN REKAPITULASI KATEGORI BARANG';
    $data = select_kategori("SELECT k.*, COUNT(b.id_barang) as total_barang, COALESCE(SUM(b.stok), 0) as total_stok 
                            FROM kategori k 
                            LEFT JOIN barang b ON k.id_kategori = b.id_kategori 
                            GROUP BY k.id_kategori 
                            ORDER BY k.nama_kategori ASC");
} elseif ($tipe === 'lokasi') {
    $judul_laporan = 'LAPORAN REKAPITULASI LOKASI RUANGAN';
    $data = select_lokasi("SELECT l.*, COUNT(b.id_barang) as total_barang, COALESCE(SUM(b.stok), 0) as total_stok 
                          FROM lokasi l 
                          LEFT JOIN barang b ON l.id_lokasi = b.id_lokasi 
                          GROUP BY l.id_lokasi 
                          ORDER BY l.nama_lokasi ASC");
} else {
    // Default: Barang
    $judul_laporan = 'LAPORAN INVENTARIS BARANG';
    $filter_kategori = isset($_GET['id_kategori']) && $_GET['id_kategori'] !== '' ? (int)$_GET['id_kategori'] : '';
    $filter_kondisi  = isset($_GET['kondisi']) && $_GET['kondisi'] !== '' ? mysqli_real_escape_string($db, $_GET['kondisi']) : '';

    $where_clauses = [];
    if ($filter_kategori !== '') {
        $where_clauses[] = "b.id_kategori = $filter_kategori";
    }
    if ($filter_kondisi !== '') {
        $where_clauses[] = "b.kondisi = '$filter_kondisi'";
    }
    $where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

    $data = select_barang("SELECT b.*, k.nama_kategori, l.nama_lokasi 
                          FROM barang b 
                          LEFT JOIN kategori k ON b.id_kategori = k.id_kategori 
                          LEFT JOIN lokasi l ON b.id_lokasi = l.id_lokasi 
                          $where_sql 
                          ORDER BY b.nama_barang ASC");
}

?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $judul_laporan; ?> - PDF Export</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- html2pdf.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .page-container {
            max-width: 900px;
            margin: 20px auto;
            background: #fff;
            padding: 35px 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-radius: 8px;
        }
        .header-kop {
            border-bottom: 3px double #000;
            margin-bottom: 20px;
            padding-bottom: 12px;
        }
        @media (max-width: 768px) {
            .page-container {
                margin: 10px;
                padding: 18px 15px;
            }
        }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .page-container { box-shadow: none; margin: 0; padding: 0; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="container no-print mt-3 text-center">
    <div class="d-flex flex-wrap justify-content-center gap-2">
        <button onclick="downloadPDF()" class="btn btn-danger shadow-sm">
            <i class="bi bi-file-earmark-pdf me-2"></i>Download PDF Sekarang
        </button>
        <button onclick="window.print()" class="btn btn-outline-dark shadow-sm">
            <i class="bi bi-printer me-2"></i>Cetak / Print
        </button>
        <button onclick="window.close()" class="btn btn-secondary shadow-sm">
            <i class="bi bi-x-circle me-1"></i>Tutup
        </button>
    </div>
</div>

<div class="page-container" id="pdf-content">
    <div class="text-center header-kop">
        <h3 class="mb-0 fw-bold text-uppercase"><?= $judul_laporan; ?></h3>
        <p class="mb-0 text-muted">Sistem Manajemen & Pengelolaan Sarana Prasarana Inventory</p>
        <small class="text-muted">Tanggal Cetak: <?= date('d F Y, H:i'); ?> WIB</small>
    </div>

    <?php if ($tipe === 'peminjaman') : ?>
        <table class="table table-bordered table-sm align-middle">
            <thead class="text-center table-light">
                <tr>
                    <th>No</th>
                    <th>Nama Peminjam</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Tgl Pinjam</th>
                    <th>Tgl Kembali</th>
                    <th>Status</th>
                    <th>Kondisi Kembali</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)) : ?>
                    <tr><td colspan="8" class="text-center py-3 text-muted">Tidak ada data.</td></tr>
                <?php else : ?>
                    <?php $no = 1; foreach ($data as $p) : ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td><?= htmlspecialchars($p['nama_peminjam']); ?></td>
                            <td>[<?= htmlspecialchars($p['kode_barang']); ?>] <?= htmlspecialchars($p['nama_barang']); ?></td>
                            <td class="text-center fw-bold"><?= $p['jumlah']; ?> <?= htmlspecialchars($p['satuan']); ?></td>
                            <td class="text-center"><?= date('d/m/Y', strtotime($p['tanggal_pinjam'])); ?></td>
                            <td class="text-center"><?= !empty($p['tanggal_kembali']) ? date('d/m/Y', strtotime($p['tanggal_kembali'])) : '-'; ?></td>
                            <td class="text-center"><?= ucfirst($p['status']); ?></td>
                            <td class="text-center"><?= htmlspecialchars($p['kondisi_kembali'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

    <?php elseif ($tipe === 'transaksi') : ?>
        <table class="table table-bordered table-sm align-middle">
            <thead class="text-center table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Barang</th>
                    <th>Jenis Transaksi</th>
                    <th>Jumlah</th>
                    <th>Keterangan</th>
                    <th>Petugas</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)) : ?>
                    <tr><td colspan="7" class="text-center py-3 text-muted">Tidak ada data.</td></tr>
                <?php else : ?>
                    <?php $no = 1; foreach ($data as $t) : ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td class="text-center"><?= date('d/m/Y', strtotime($t['tanggal'])); ?></td>
                            <td>[<?= htmlspecialchars($t['kode_barang']); ?>] <?= htmlspecialchars($t['nama_barang']); ?></td>
                            <td class="text-center fw-bold"><?= ucfirst($t['jenis_transaksi']); ?></td>
                            <td class="text-center"><?= (strtolower($t['jenis_transaksi']) === 'masuk' ? '+' : '-') . $t['jumlah']; ?> <?= htmlspecialchars($t['satuan']); ?></td>
                            <td><?= htmlspecialchars($t['keterangan']); ?></td>
                            <td><?= htmlspecialchars($t['nama_petugas'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

    <?php elseif ($tipe === 'kategori' || $tipe === 'lokasi') : ?>
        <table class="table table-bordered table-sm align-middle">
            <thead class="text-center table-light">
                <tr>
                    <th>No</th>
                    <th><?= ($tipe === 'kategori') ? 'Nama Kategori' : 'Nama Lokasi'; ?></th>
                    <th>Total Jenis Barang</th>
                    <th>Total Stok Fisik</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)) : ?>
                    <tr><td colspan="4" class="text-center py-3 text-muted">Tidak ada data.</td></tr>
                <?php else : ?>
                    <?php $no = 1; foreach ($data as $row) : ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['nama_' . $tipe]); ?></td>
                            <td class="text-center"><?= $row['total_barang']; ?> Jenis</td>
                            <td class="text-center fw-bold"><?= $row['total_stok']; ?> Unit</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

    <?php else : ?>
        <!-- Default Barang -->
        <table class="table table-bordered table-sm align-middle">
            <thead class="text-center table-light">
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Lokasi</th>
                    <th>Stok</th>
                    <th>Satuan</th>
                    <th>Kondisi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)) : ?>
                    <tr><td colspan="8" class="text-center py-3 text-muted">Tidak ada data barang.</td></tr>
                <?php else : ?>
                    <?php $no = 1; $total_stok = 0; foreach ($data as $b) : $total_stok += (int)$b['stok']; ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td class="text-center"><?= htmlspecialchars($b['kode_barang']); ?></td>
                            <td><?= htmlspecialchars($b['nama_barang']); ?></td>
                            <td><?= htmlspecialchars($b['nama_kategori'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($b['nama_lokasi'] ?? '-'); ?></td>
                            <td class="text-center fw-bold"><?= $b['stok']; ?></td>
                            <td class="text-center"><?= htmlspecialchars($b['satuan']); ?></td>
                            <td class="text-center"><?= ucfirst($b['kondisi']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="fw-bold table-light">
                        <td colspan="5" class="text-end">Total Seluruh Stok:</td>
                        <td colspan="3"><?= $total_stok; ?> Unit</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Tanda Tangan Resmi -->
    <div class="row mt-5">
        <div class="col-6"></div>
        <div class="col-6 text-center">
            <p class="mb-5">Mengetahui,<br><strong>Kepala Bagian Sarana & Prasarana</strong></p>
            <p class="mt-4 fw-bold text-decoration-underline">( .................................................. )</p>
        </div>
    </div>
</div>

<script>
function downloadPDF() {
    const element = document.getElementById('pdf-content');
    const opt = {
        margin:       10,
        filename:     '<?= str_replace(" ", "_", $judul_laporan); ?>_<?= date("Ymd_His"); ?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: '<?= ($tipe === "peminjaman" || $tipe === "transaksi") ? "landscape" : "portrait"; ?>' }
    };
    html2pdf().set(opt).from(element).save();
}
</script>

</body>
</html>
