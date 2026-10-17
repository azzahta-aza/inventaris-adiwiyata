<?php

include_once '../../app/app.php';
include '../../layout/pagination.php';
check_role(['admin', 'petugas']);

include_once '../../layout/header.php';

//Ambil data peminjaman dengan JOIN ke barang dan users

$query_peminjaman = "SELECT
    p.*,
    b.kode_barang,
    b.nama_barang,
    b.satuan,
    u.nama AS nama_petugas
FROM peminjaman p
LEFT JOIN barang b ON p.id_barang = b.id_barang
LEFT JOIN users u ON p.id_user = u.id_user
ORDER BY p.id_pinjam DESC";

$result_peminjaman = mysqli_query($db, $query_peminjaman);
$data_peminjaman = [];

if ($result_peminjaman) {
    while ($row = mysqli_fetch_assoc($result_peminjaman)) {
        $data_peminjaman[] = $row;
    }
} else {
    die("Query peminjaman gagal: " . mysqli_error($db));
}

//Notifikasi tambah peminjaman 
$loan_status = $_GET['status'] ?? null;
$loan_message = null;
$loan_message_type = 'success';

if ($loan_status === 'success') {
    $loan_message = 'Data peminjaman berhasil ditambahkan.';
} elseif ($loan_status === 'failed') {
    $loan_message = 'Data peminjaman gagal ditambahkan.';
}

// Notifikasi edit peminjaman
$edit_status = $_GET['edit_status'] ?? null;
$edit_message = null;
$edit_message_type = 'success';

if ($edit_status === 'success') {
    $edit_message = 'Data peminjaman berhasil diperbarui.';
} elseif ($edit_status === 'failed') {
    $edit_message = 'Data peminjaman tidak berubah atau gagal diperbarui.';
} elseif ($edit_status === 'not_found' || $loan_status === 'not_found') {
    $edit_message = 'Data peminjaman tidak ditemukan.';
    $edit_message_type = 'warning';
}

// Notifikasi hapus peminjaman 
$delete_status = $_GET['delete_status'] ?? null;
$delete_message = null;
$delete_message_type = 'warning';

if ($delete_status === 'success') {
    $delete_message = 'Data peminjaman berhasil dihapus.';
    $delete_message_type = 'success';
} elseif ($delete_status === 'in_use') {
    $delete_message = 'Peminjaman tidak dapat dihapus karena barang belum dikembalikan.';
} elseif ($delete_status === 'failed') {
    $delete_message = 'Data peminjaman gagal dihapus.';
    $delete_message_type = 'danger';
} elseif ($delete_status === 'invalid') {
    $delete_message = 'Permintaan penghapusan tidak valid.';
} elseif ($delete_status === 'not_found') {
    $delete_message = 'Data peminjaman tidak ditemukan.';
}

//Notifikasi pengembalian peminjaman
$return_status = $_GET['return_status'] ?? null;
$return_message = null;
$return_message_type = 'success';

if ($return_status === 'success') {
    $return_message = 'Barang berhasil dikembalikan.';
} elseif ($return_status === 'already_returned') {
    $return_message = 'Barang sudah dikembalikan.';
} elseif ($return_status === 'failed') {
    $return_message = 'Pengembalian barang gagal diproses.';
    $return_message_type = 'danger';
}

?>
<link rel="icon" type="image/png" href="../../img/logo_white.png">
<div class="container-fluid py-2"> 
    <?php if ($return_message !== null) : ?>
        <div class="alert alert-<?= htmlspecialchars($return_message_type, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($return_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
    <?php if ($delete_message !== null) : ?>
       <div class="alert alert-<?= htmlspecialchars($delete_message_type, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($delete_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
    <?php if ($loan_message !== null) : ?>
        <div class="alert alert-<?= htmlspecialchars($loan_message_type, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($loan_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
    <?php if ($edit_message !== null) : ?>
        <div class="alert alert-<?= htmlspecialchars($edit_message_type, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($edit_message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Data Peminjaman Barang</h1>
            <p class="text-muted small mb-0">Kelola transaksi peminjaman dan pengembalian barang inventaris</p>
        </div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Pinjam Barang
        </a>
    </div>

     <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-header-green text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Peminjam</th>
                            <th>Barang</th>
                            <th width="8%">Jumlah</th>
                            <th width="12%">Tgl Pinjam</th>
                            <th width="12%">Tgl Kembali</th>
                            <th width="10%">Status</th>
                            <th>Kondisi Kembali</th>
                            <th width="18%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php if (empty($data_peminjaman)) : ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Belum ada data peminjaman barang.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($data_peminjaman as $pinjam) : ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($pinjam['nama_peminjam']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($pinjam['kode_barang'] ?? ''); ?></span>
                                        <strong><?= htmlspecialchars($pinjam['nama_barang'] ?? 'Barang Dihapus'); ?></strong>
                                    </td>
                                    <td class="text-center fw-bold"><?= $pinjam['jumlah']; ?> <?= htmlspecialchars($pinjam['satuan'] ?? ''); ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($pinjam['tanggal_pinjam'])); ?></td>
                                    <td class="text-center">
                                        <?= !empty($pinjam['tanggal_kembali']) ? date('d/m/Y', strtotime($pinjam['tanggal_kembali'])) : '<span class="text-muted">-</span>'; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($pinjam['status'] === 'dipinjam') : ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i> Dipinjam</span>
                                        <?php else : ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Dikembalikan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?= !empty($pinjam['kondisi_kembali']) ? htmlspecialchars($pinjam['kondisi_kembali']) : '<span class="text-muted">-</span>'; ?>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <?php if ($pinjam['status'] === 'dipinjam') : ?>
                                            <a href="pengembalian.php?id_pinjam=<?= $pinjam['id_pinjam']; ?>" class="btn btn-sm btn-info text-white me-1" title="Proses Pengembalian">
                                                <i class="bi bi-box-arrow-in-left"></i> Kembali
                                            </a>
                                        <?php endif; ?>
                                        <a href="update.php?id_pinjam=<?= $pinjam['id_pinjam']; ?>" class="btn btn-sm btn-success me-1" title="Edit Data">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="hapus.php" method="POST" class="d-inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus data peminjaman ini?');">
                                            <input type="hidden" name="id_pinjam"
                                                value="<?= (int)$pinjam['id_pinjam']; ?>">
                                            <input type="hidden" name="csrf_token"
                                                value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    title="Hapus Data">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../layout/footer.php'; ?>
