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

function check_login()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
        echo "<script>
                alert('Silakan login terlebih dahulu!');
                document.location.href = '../auth/login.php';
              </script>";
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
    global $db;
    $nama_kategori = mysqli_real_escape_string($db, $post['nama']);
    $query = "INSERT INTO kategori (nama_kategori) VALUES ('$nama_kategori')";
    mysqli_query($db, $query);
    return mysqli_affected_rows($db);
}

function update_kategori($post)
{
    global $db;
    $id_kategori   = (int)$post['id_kategori'];
    $nama_kategori = mysqli_real_escape_string($db, $post['nama']);
    $query = "UPDATE kategori SET nama_kategori = '$nama_kategori' WHERE id_kategori = $id_kategori";
    mysqli_query($db, $query);
    return mysqli_affected_rows($db);
}

function delete_kategori($id_kategori)
{
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
    global $db;
    $nama_lokasi = mysqli_real_escape_string($db, $post['nama']);
    $query = "INSERT INTO lokasi (nama_lokasi) VALUES ('$nama_lokasi')";
    mysqli_query($db, $query);
    return mysqli_affected_rows($db);
}

function update_lokasi($post)
{
    global $db;
    $id_lokasi   = (int)$post['id_lokasi'];
    $nama_lokasi = mysqli_real_escape_string($db, $post['nama']);
    $query = "UPDATE lokasi SET nama_lokasi = '$nama_lokasi' WHERE id_lokasi = $id_lokasi";
    mysqli_query($db, $query);
    return mysqli_affected_rows($db);
}

function delete_lokasi($id_lokasi)
{
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
