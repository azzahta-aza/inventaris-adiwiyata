<?php

include_once __DIR__ . '/koneksi.php';

// ==========================================
// == 1. AUTHENTICATION ==
// ==========================================
function login($post)
{
    global $db;
    $username = mysqli_real_escape_string($db, trim($post['username']));
    $password = trim($post['password']);

    // Cari user berdasarkan username
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($db, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // Verifikasi password (password_hash, md5, atau plain text)
        if (password_verify($password, $user['password']) || $user['password'] === md5($password) || $user['password'] === $password) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['login']    = true;
            $_SESSION['id_user']  = $user['id_user'];
            $_SESSION['nama']     = $user['nama'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            return 1; // Sukses
        } else {
            return -1; // Password salah
        }
    }

    return 0; // Username tidak ditemukan
}

function get_user_role()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return strtolower(trim($_SESSION['role'] ?? ''));
}

function has_role($allowed_roles)
{
    $current_role = get_user_role();
    if (empty($current_role)) {
        return false;
    }

    if (is_string($allowed_roles)) {
        $allowed_roles = array_map('trim', explode(',', $allowed_roles));
    }

    $normalized_allowed = array_map('strtolower', (array)$allowed_roles);
    return in_array($current_role, $normalized_allowed, true);
}

function get_landing_url($prefix = '..')
{
    $role = get_user_role();
    if ($role === 'admin') {
        return "$prefix/barang/index.php";
    } elseif ($role === 'petugas') {
        return "$prefix/transaksi/index.php";
    } elseif ($role === 'kepala') {
        return "$prefix/laporan/barang.php";
    }
    return "$prefix/auth/login.php";
}

function check_login($redirect_path = null)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
        if ($redirect_path === null) {
            $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
            $redirect_path = (preg_match('#/views/[^/]+/#', $script)) ? '../auth/login.php' : 'views/auth/login.php';
        }
        http_response_code(302);
        header("Location: $redirect_path");
        exit;
    }
}

function check_role($allowed_roles)
{
    check_login();

    if (!has_role($allowed_roles)) {
        http_response_code(403);
        $error_file = __DIR__ . '/../views/error/403.php';
        if (file_exists($error_file)) {
            include $error_file;
        } else {
            echo "<h1>403 Forbidden</h1><p>Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.</p>";
        }
        exit;
    }
}


// ==========================================
// == 2. KATEGORI ==
// ==========================================
function select_kategori($query)
{
    global $db;
    $result = mysqli_query($db, $query);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function create_kategori($post)
{
    if (!has_role('admin')) {
        return 0;
    }
    global $db;
    $nama_kategori = mysqli_real_escape_string($db, $post['nama']);
    $query = "INSERT INTO kategori (nama_kategori) VALUES ('$nama_kategori')";
    mysqli_query($db, $query);
    return mysqli_affected_rows($db);
}

function update_kategori($post)
{
    if (!has_role('admin')) {
        return 0;
    }
    global $db;
    $id_kategori   = (int)$post['id_kategori'];
    $nama_kategori = mysqli_real_escape_string($db, $post['nama']);
    $query = "UPDATE kategori SET nama_kategori = '$nama_kategori' WHERE id_kategori = $id_kategori";
    mysqli_query($db, $query);
    return mysqli_affected_rows($db);
}

function delete_kategori($id_kategori)
{
    if (!has_role('admin')) {
        return 0;
    }
    global $db;
    $id_kategori = (int)$id_kategori;
    $query = "DELETE FROM kategori WHERE id_kategori = $id_kategori";
    mysqli_query($db, $query);
    return mysqli_affected_rows($db);
}


// ==========================================
// == 3. LOKASI ==
// ==========================================
function select_lokasi($query)
{
    global $db;
    $result = mysqli_query($db, $query);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function create_lokasi($post)
{
    if (!has_role('admin')) {
        return 0;
    }
    global $db;
    $nama_lokasi = mysqli_real_escape_string($db, $post['nama']);
    $query = "INSERT INTO lokasi (nama_lokasi) VALUES ('$nama_lokasi')";
    mysqli_query($db, $query);
    return mysqli_affected_rows($db);
}

function update_lokasi($post)
{
    if (!has_role('admin')) {
        return 0;
    }
    global $db;
    $id_lokasi   = (int)$post['id_lokasi'];
    $nama_lokasi = mysqli_real_escape_string($db, $post['nama']);
    $query = "UPDATE lokasi SET nama_lokasi = '$nama_lokasi' WHERE id_lokasi = $id_lokasi";
    mysqli_query($db, $query);
    return mysqli_affected_rows($db);
}

function delete_lokasi($id_lokasi)
{
    if (!has_role('admin')) {
        return 0;
    }
    global $db;
    $id_lokasi = (int)$id_lokasi;
    $query = "DELETE FROM lokasi WHERE id_lokasi = $id_lokasi";
    mysqli_query($db, $query);
    return mysqli_affected_rows($db);
}


// ==========================================
// == 4. BARANG ==
// ==========================================
function select_barang($query)
{
    global $db;
    $result = mysqli_query($db, $query);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function create_barang($post)
{
    if (!has_role('admin')) {
        return 0;
    }
    global $db;
    $id_kategori = (int)$post['id_kategori'];
    $id_lokasi   = (int)$post['id_lokasi'];
    $kode_barang = mysqli_real_escape_string($db, $post['kode_barang']);
    $nama_barang = mysqli_real_escape_string($db, $post['nama_barang']);
    $stok        = (int)$post['stok'];
    $satuan      = mysqli_real_escape_string($db, $post['satuan']);
    $kondisi     = mysqli_real_escape_string($db, $post['kondisi']);
    $keterangan  = mysqli_real_escape_string($db, $post['keterangan']);

    $query = "INSERT INTO barang (id_kategori, id_lokasi, kode_barang, nama_barang, stok, satuan, kondisi, keterangan) 
              VALUES ($id_kategori, $id_lokasi, '$kode_barang', '$nama_barang', $stok, '$satuan', '$kondisi', '$keterangan')";
    mysqli_query($db, $query);
    return mysqli_affected_rows($db);
}

function update_barang($post)
{
    if (!has_role('admin')) {
        return 0;
    }
    global $db;
    $id_barang   = (int)$post['id_barang'];
    $id_kategori = (int)$post['id_kategori'];
    $id_lokasi   = (int)$post['id_lokasi'];
    $kode_barang = mysqli_real_escape_string($db, $post['kode_barang']);
    $nama_barang = mysqli_real_escape_string($db, $post['nama_barang']);
    $stok        = (int)$post['stok'];
    $satuan      = mysqli_real_escape_string($db, $post['satuan']);
    $kondisi     = mysqli_real_escape_string($db, $post['kondisi']);
    $keterangan  = mysqli_real_escape_string($db, $post['keterangan']);

    $query = "UPDATE barang SET 
                id_kategori = $id_kategori,
                id_lokasi   = $id_lokasi,
                kode_barang = '$kode_barang',
                nama_barang = '$nama_barang',
                stok        = $stok,
                satuan      = '$satuan',
                kondisi     = '$kondisi',
                keterangan  = '$keterangan'
              WHERE id_barang = $id_barang";
    mysqli_query($db, $query);
    return mysqli_affected_rows($db);
}

function delete_barang($id_barang)
{
    if (!has_role('admin')) {
        return 0;
    }
    global $db;
    $id_barang = (int)$id_barang;
    $query = "DELETE FROM barang WHERE id_barang = $id_barang";
    mysqli_query($db, $query);
    return mysqli_affected_rows($db);
}


// ==========================================
// == 5. PEMINJAMAN ==
// ==========================================
function select_peminjaman($query)
{
    global $db;
    $result = mysqli_query($db, $query);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function create_peminjaman($post)
{
    if (!has_role(['admin', 'petugas'])) {
        return 0;
    }
    global $db;
    $id_barang       = (int)$post['id_barang'];
    $nama_peminjam   = mysqli_real_escape_string($db, $post['nama_peminjam']);
    $tanggal_pinjam  = mysqli_real_escape_string($db, $post['tanggal_pinjam']);
    $jumlah          = (int)$post['jumlah'];
    $status          = 'dipinjam';
    
    if (isset($_SESSION['id_user'])) {
        $id_user = (int)$_SESSION['id_user'];
    } elseif (isset($post['id_user']) && $post['id_user'] !== '') {
        $id_user = (int)$post['id_user'];
    } else {
        $id_user = 1;
    }

    // Cek ketersediaan stok barang
    $cek_stok = mysqli_query($db, "SELECT stok FROM barang WHERE id_barang = $id_barang");
    $data_barang = mysqli_fetch_assoc($cek_stok);

    if (!$data_barang || $data_barang['stok'] < $jumlah) {
        return -1; // Stok tidak mencukupi
    }

    // Insert peminjaman
    $query = "INSERT INTO peminjaman (id_barang, nama_peminjam, tanggal_pinjam, jumlah, status, id_user, tanggal_kembali, kondisi_kembali) 
              VALUES ($id_barang, '$nama_peminjam', '$tanggal_pinjam', $jumlah, '$status', $id_user, NULL, NULL)";
    $res = mysqli_query($db, $query);

    if ($res) {
        // Kurangi stok barang
        mysqli_query($db, "UPDATE barang SET stok = stok - $jumlah WHERE id_barang = $id_barang");
        return mysqli_affected_rows($db);
    }

    return 0;
}

function kembalikan_peminjaman($post)
{
    if (!has_role(['admin', 'petugas'])) {
        return 0;
    }
    global $db;
    $id_pinjam        = (int)$post['id_pinjam'];
    $tanggal_kembali  = mysqli_real_escape_string($db, $post['tanggal_kembali']);
    $kondisi_kembali  = mysqli_real_escape_string($db, $post['kondisi_kembali']);

    // Ambil data peminjaman untuk mengembalikan stok
    $pinjam_query = mysqli_query($db, "SELECT * FROM peminjaman WHERE id_pinjam = $id_pinjam AND status = 'dipinjam'");
    $pinjam = mysqli_fetch_assoc($pinjam_query);

    if (!$pinjam) {
        return 0;
    }

    $id_barang = (int)$pinjam['id_barang'];
    $jumlah    = (int)$pinjam['jumlah'];

    // Update status peminjaman
    $query = "UPDATE peminjaman SET 
                status = 'dikembalikan', 
                tanggal_kembali = '$tanggal_kembali', 
                kondisi_kembali = '$kondisi_kembali' 
              WHERE id_pinjam = $id_pinjam";
    $res = mysqli_query($db, $query);

    if ($res) {
        // Tambahkan kembali stok barang
        mysqli_query($db, "UPDATE barang SET stok = stok + $jumlah WHERE id_barang = $id_barang");
        return 1;
    }

    return 0;
}

function update_peminjaman($post)
{
    if (!has_role(['admin', 'petugas'])) {
        return 0;
    }
    global $db;
    $id_pinjam       = (int)$post['id_pinjam'];
    $nama_peminjam   = mysqli_real_escape_string($db, $post['nama_peminjam']);
    $tanggal_pinjam  = mysqli_real_escape_string($db, $post['tanggal_pinjam']);

    $query = "UPDATE peminjaman SET 
                nama_peminjam = '$nama_peminjam',
                tanggal_pinjam = '$tanggal_pinjam'
              WHERE id_pinjam = $id_pinjam";
    mysqli_query($db, $query);
    return mysqli_affected_rows($db);
}

function delete_peminjaman($id_pinjam)
{
    if (!has_role(['admin', 'petugas'])) {
        return 0;
    }
    global $db;
    $id_pinjam = (int)$id_pinjam;

    $cek = mysqli_query($db, "SELECT * FROM peminjaman WHERE id_pinjam = $id_pinjam");
    $data = mysqli_fetch_assoc($cek);
    if ($data && $data['status'] === 'dipinjam') {
        $id_barang = (int)$data['id_barang'];
        $jumlah    = (int)$data['jumlah'];
        mysqli_query($db, "UPDATE barang SET stok = stok + $jumlah WHERE id_barang = $id_barang");
    }

    $query = "DELETE FROM peminjaman WHERE id_pinjam = $id_pinjam";
    mysqli_query($db, $query);
    return mysqli_affected_rows($db);
}


// ==========================================
// == 6. TRANSAKSI ==
// ==========================================
function select_transaksi($query)
{
    global $db;
    $result = mysqli_query($db, $query);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function create_transaksi($post)
{
    if (!has_role(['admin', 'petugas'])) {
        return 0;
    }
    global $db;
    $id_barang       = (int)$post['id_barang'];
    $tanggal         = mysqli_real_escape_string($db, $post['tanggal']);
    $jenis_transaksi = mysqli_real_escape_string($db, $post['jenis_transaksi']);
    $jumlah          = (int)$post['jumlah'];
    $keterangan      = mysqli_real_escape_string($db, $post['keterangan']);

    if (isset($_SESSION['id_user'])) {
        $id_user = (int)$_SESSION['id_user'];
    } elseif (isset($post['id_user']) && $post['id_user'] !== '') {
        $id_user = (int)$post['id_user'];
    } else {
        $id_user = 1;
    }

    if (strtolower($jenis_transaksi) === 'keluar') {
        $cek_stok = mysqli_query($db, "SELECT stok FROM barang WHERE id_barang = $id_barang");
        $data_barang = mysqli_fetch_assoc($cek_stok);

        if (!$data_barang || $data_barang['stok'] < $jumlah) {
            return -1;
        }
    }

    $query = "INSERT INTO transaksi (id_barang, id_user, tanggal, jenis_transaksi, jumlah, keterangan) 
              VALUES ($id_barang, $id_user, '$tanggal', '$jenis_transaksi', $jumlah, '$keterangan')";
    $res = mysqli_query($db, $query);

    if ($res) {
        if (strtolower($jenis_transaksi) === 'masuk') {
            mysqli_query($db, "UPDATE barang SET stok = stok + $jumlah WHERE id_barang = $id_barang");
        } elseif (strtolower($jenis_transaksi) === 'keluar') {
            mysqli_query($db, "UPDATE barang SET stok = stok - $jumlah WHERE id_barang = $id_barang");
        }
        return mysqli_affected_rows($db);
    }

    return 0;
}

function delete_transaksi($id_transaksi)
{
    if (!has_role(['admin', 'petugas'])) {
        return 0;
    }
    global $db;
    $id_transaksi = (int)$id_transaksi;

    $cek = mysqli_query($db, "SELECT * FROM transaksi WHERE id_transaksi = $id_transaksi");
    $data = mysqli_fetch_assoc($cek);
    if ($data) {
        $id_barang = (int)$data['id_barang'];
        $jumlah    = (int)$data['jumlah'];
        if (strtolower($data['jenis_transaksi']) === 'masuk') {
            mysqli_query($db, "UPDATE barang SET stok = stok - $jumlah WHERE id_barang = $id_barang");
        } elseif (strtolower($data['jenis_transaksi']) === 'keluar') {
            mysqli_query($db, "UPDATE barang SET stok = stok + $jumlah WHERE id_barang = $id_barang");
        }
    }

    $query = "DELETE FROM transaksi WHERE id_transaksi = $id_transaksi";
    mysqli_query($db, $query);
    return mysqli_affected_rows($db);
}


// ==========================================
// == 7. MANAJEMEN USERS ==
// ==========================================
function select_users($query)
{
    global $db;
    $result = mysqli_query($db, $query);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function create_user($post)
{
    if (!has_role('admin')) {
        return 0;
    }
    global $db;
    $nama     = mysqli_real_escape_string($db, $post['nama']);
    $username = mysqli_real_escape_string($db, strtolower(trim($post['username'])));
    $password = password_hash(trim($post['password']), PASSWORD_DEFAULT);
    $role     = mysqli_real_escape_string($db, $post['role']);

    $cek = mysqli_query($db, "SELECT username FROM users WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        return -1;
    }

    $query = "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$password', '$role')";
    mysqli_query($db, $query);
    return mysqli_affected_rows($db);
}

function update_user($post)
{
    if (!has_role('admin')) {
        return 0;
    }
    global $db;
    $id_user  = (int)$post['id_user'];
    $nama     = mysqli_real_escape_string($db, $post['nama']);
    $username = mysqli_real_escape_string($db, strtolower(trim($post['username'])));
    $role     = mysqli_real_escape_string($db, $post['role']);

    $cek = mysqli_query($db, "SELECT id_user FROM users WHERE username = '$username' AND id_user != $id_user");
    if (mysqli_num_rows($cek) > 0) {
        return -1;
    }

    if (!empty($post['password'])) {
        $password = password_hash(trim($post['password']), PASSWORD_DEFAULT);
        $query = "UPDATE users SET nama = '$nama', username = '$username', password = '$password', role = '$role' WHERE id_user = $id_user";
    } else {
        $query = "UPDATE users SET nama = '$nama', username = '$username', role = '$role' WHERE id_user = $id_user";
    }

    mysqli_query($db, $query);
    return mysqli_affected_rows($db);
}

function delete_user($id_user)
{
    if (!has_role('admin')) {
        return 0;
    }
    global $db;
    $id_user = (int)$id_user;

    if (isset($_SESSION['id_user']) && $_SESSION['id_user'] == $id_user) {
        return -2;
    }

    $query = "DELETE FROM users WHERE id_user = $id_user";
    mysqli_query($db, $query);
    return mysqli_affected_rows($db);
}


// ==========================================
// == 8. LAPORAN ==
// ==========================================
function select_laporan($query)
{
    if (!has_role(['admin', 'kepala'])) {
        return [];
    }
    global $db;
    $result = mysqli_query($db, $query);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}


// ==========================================
// == 9. DASHBOARD AGGREGATION ==
// ==========================================
function get_dashboard_summary()
{
    if (!has_role(['admin', 'petugas', 'kepala'])) {
        return [];
    }
    global $db;

    // 1. Barang summary
    $q_barang = "SELECT 
                    COUNT(*) as total_jenis, 
                    COALESCE(SUM(stok), 0) as total_stok,
                    COALESCE(SUM(CASE WHEN kondisi = 'layak' THEN 1 ELSE 0 END), 0) as layak,
                    COALESCE(SUM(CASE WHEN kondisi = 'perbaikan' THEN 1 ELSE 0 END), 0) as perbaikan,
                    COALESCE(SUM(CASE WHEN kondisi = 'tidak_layak' THEN 1 ELSE 0 END), 0) as tidak_layak
                 FROM barang";
    $res_barang = mysqli_query($db, $q_barang);
    $b_data = $res_barang ? mysqli_fetch_assoc($res_barang) : [];

    // 2. Transaksi keluar
    $q_keluar = "SELECT 
                    COUNT(*) as count_keluar, 
                    COALESCE(SUM(jumlah), 0) as qty_keluar,
                    COALESCE(SUM(CASE WHEN MONTH(tanggal) = MONTH(CURRENT_DATE()) AND YEAR(tanggal) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END), 0) as count_keluar_bulan,
                    COALESCE(SUM(CASE WHEN MONTH(tanggal) = MONTH(CURRENT_DATE()) AND YEAR(tanggal) = YEAR(CURRENT_DATE()) THEN jumlah ELSE 0 END), 0) as qty_keluar_bulan
                 FROM transaksi WHERE LOWER(jenis_transaksi) = 'keluar'";
    $res_keluar = mysqli_query($db, $q_keluar);
    $k_data = $res_keluar ? mysqli_fetch_assoc($res_keluar) : [];

    // 3. Transaksi masuk
    $q_masuk = "SELECT 
                    COUNT(*) as count_masuk, 
                    COALESCE(SUM(jumlah), 0) as qty_masuk,
                    COALESCE(SUM(CASE WHEN MONTH(tanggal) = MONTH(CURRENT_DATE()) AND YEAR(tanggal) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END), 0) as count_masuk_bulan,
                    COALESCE(SUM(CASE WHEN MONTH(tanggal) = MONTH(CURRENT_DATE()) AND YEAR(tanggal) = YEAR(CURRENT_DATE()) THEN jumlah ELSE 0 END), 0) as qty_masuk_bulan
                 FROM transaksi WHERE LOWER(jenis_transaksi) = 'masuk'";
    $res_masuk = mysqli_query($db, $q_masuk);
    $m_data = $res_masuk ? mysqli_fetch_assoc($res_masuk) : [];

    // 4. Peminjaman
    $q_pinjam = "SELECT 
                    COUNT(*) as total_pinjam,
                    COALESCE(SUM(CASE WHEN status = 'dipinjam' THEN 1 ELSE 0 END), 0) as dipinjam,
                    COALESCE(SUM(CASE WHEN status = 'dikembalikan' THEN 1 ELSE 0 END), 0) as dikembalikan,
                    COALESCE(SUM(CASE WHEN status = 'dipinjam' THEN jumlah ELSE 0 END), 0) as qty_dipinjam
                 FROM peminjaman";
    $res_pinjam = mysqli_query($db, $q_pinjam);
    $p_data = $res_pinjam ? mysqli_fetch_assoc($res_pinjam) : [];

    return [
        'total_jenis_barang'    => (int)($b_data['total_jenis'] ?? 0),
        'total_stok_barang'     => (int)($b_data['total_stok'] ?? 0),
        'kondisi_layak'         => (int)($b_data['layak'] ?? 0),
        'kondisi_perbaikan'     => (int)($b_data['perbaikan'] ?? 0),
        'kondisi_tidak_layak'   => (int)($b_data['tidak_layak'] ?? 0),
        'count_keluar'          => (int)($k_data['count_keluar'] ?? 0),
        'qty_keluar'            => (int)($k_data['qty_keluar'] ?? 0),
        'count_keluar_bulan'    => (int)($k_data['count_keluar_bulan'] ?? 0),
        'qty_keluar_bulan'      => (int)($k_data['qty_keluar_bulan'] ?? 0),
        'count_masuk'           => (int)($m_data['count_masuk'] ?? 0),
        'qty_masuk'             => (int)($m_data['qty_masuk'] ?? 0),
        'count_masuk_bulan'     => (int)($m_data['count_masuk_bulan'] ?? 0),
        'qty_masuk_bulan'       => (int)($m_data['qty_masuk_bulan'] ?? 0),
        'total_pinjam'          => (int)($p_data['total_pinjam'] ?? 0),
        'peminjaman_aktif'      => (int)($p_data['dipinjam'] ?? 0),
        'peminjaman_kembali'    => (int)($p_data['dikembalikan'] ?? 0),
        'qty_dipinjam'          => (int)($p_data['qty_dipinjam'] ?? 0)
    ];
}

function get_dashboard_latest_transaksi($jenis, $limit = 5)
{
    if (!has_role(['admin', 'petugas', 'kepala'])) {
        return [];
    }
    global $db;
    $jenis_clean = mysqli_real_escape_string($db, strtolower($jenis));
    $limit = (int)$limit;
    $query = "SELECT t.*, b.kode_barang, b.nama_barang, b.satuan, u.nama AS nama_petugas 
              FROM transaksi t 
              LEFT JOIN barang b ON t.id_barang = b.id_barang 
              LEFT JOIN users u ON t.id_user = u.id_user 
              WHERE LOWER(t.jenis_transaksi) = '$jenis_clean' 
              ORDER BY t.tanggal DESC, t.id_transaksi DESC 
              LIMIT $limit";
    $result = mysqli_query($db, $query);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function get_dashboard_latest_peminjaman($limit = 5)
{
    if (!has_role(['admin', 'petugas', 'kepala'])) {
        return [];
    }
    global $db;
    $limit = (int)$limit;
    $query = "SELECT p.*, b.kode_barang, b.nama_barang, b.satuan, u.nama AS nama_petugas 
              FROM peminjaman p 
              LEFT JOIN barang b ON p.id_barang = b.id_barang 
              LEFT JOIN users u ON p.id_user = u.id_user 
              ORDER BY p.tanggal_pinjam DESC, p.id_pinjam DESC 
              LIMIT $limit";
    $result = mysqli_query($db, $query);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function get_dashboard_low_stock($threshold = 5, $limit = 5)
{
    if (!has_role(['admin', 'petugas', 'kepala'])) {
        return [];
    }
    global $db;
    $threshold = (int)$threshold;
    $limit = (int)$limit;
    $query = "SELECT b.*, k.nama_kategori, l.nama_lokasi 
              FROM barang b 
              LEFT JOIN kategori k ON b.id_kategori = k.id_kategori 
              LEFT JOIN lokasi l ON b.id_lokasi = l.id_lokasi 
              WHERE b.stok <= $threshold 
              ORDER BY b.stok ASC 
              LIMIT $limit";
    $result = mysqli_query($db, $query);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function get_dashboard_chart_data($days = 7)
{
    if (!has_role(['admin', 'petugas', 'kepala'])) {
        return ['labels' => [], 'masuk' => [], 'keluar' => []];
    }
    global $db;
    $days = (int)$days;
    if ($days <= 0) $days = 7;

    $dates = [];
    $labels = [];
    $data_masuk = [];
    $data_keluar = [];

    for ($i = $days - 1; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $dates[] = $d;
        $labels[] = date('d M', strtotime($d));
        $data_masuk[$d] = 0;
        $data_keluar[$d] = 0;
    }

    $start_date = $dates[0];
    $end_date = end($dates);

    $query = "SELECT tanggal, LOWER(jenis_transaksi) as jenis, SUM(jumlah) as total_qty 
              FROM transaksi 
              WHERE tanggal BETWEEN '$start_date' AND '$end_date' 
              GROUP BY tanggal, LOWER(jenis_transaksi)";
    $result = mysqli_query($db, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $tgl = $row['tanggal'];
            $jenis = $row['jenis'];
            $qty = (int)$row['total_qty'];
            if ($jenis === 'masuk' && isset($data_masuk[$tgl])) {
                $data_masuk[$tgl] = $qty;
            } elseif ($jenis === 'keluar' && isset($data_keluar[$tgl])) {
                $data_keluar[$tgl] = $qty;
            }
        }
    }

    return [
        'labels' => $labels,
        'masuk'  => array_values($data_masuk),
        'keluar' => array_values($data_keluar)
    ];
}

