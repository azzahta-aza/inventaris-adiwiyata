<?php

include_once __DIR__ . '/koneksi.php';


// == 1. AUTHENTICATION ==

function login($post)
{
    global $db;
    $username = trim($post['username'] ?? '');
    $password = trim($post['password'] ?? '');

    if ($username === '' || $password === '') {
        return 0;
    }

    // Cari user berdasarkan username menggunakan prepared statement
    $stmt = mysqli_prepare($db, "SELECT id_user, nama, username, password, role FROM users WHERE username = ?");
    if (!$stmt) {
        error_log('[UKKinventory] Login: gagal menyiapkan query: ' . mysqli_error($db));
        return 0;
    }
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    if (!$result || mysqli_num_rows($result) !== 1) {
        return 0; // Username tidak ditemukan
    }

    $user = mysqli_fetch_assoc($result);

    // Verifikasi password: prioritas bcrypt, lalu md5 (lama), lalu plain text (lama)
    $password_cocok = false;
    $perlu_upgrade  = false;

    if (password_verify($password, $user['password'])) {
        // Password sudah bcrypt — paling aman
        $password_cocok = true;
    } elseif ($user['password'] === md5($password)) {
        // Password lama format MD5 — perlu diupgrade
        $password_cocok = true;
        $perlu_upgrade  = true;
    } elseif ($user['password'] === $password) {
        // Password lama plain text — perlu diupgrade segera
        $password_cocok = true;
        $perlu_upgrade  = true;
    }

    if (!$password_cocok) {
        return -1; // Password salah
    }

    // Upgrade otomatis password lama ke bcrypt saat login berhasil
    if ($perlu_upgrade) {
        $hash_baru = password_hash($password, PASSWORD_DEFAULT);
        if ($hash_baru !== false) {
            $upd = mysqli_prepare($db, "UPDATE users SET password = ? WHERE id_user = ?");
            if ($upd) {
                mysqli_stmt_bind_param($upd, 'si', $hash_baru, $user['id_user']);
                mysqli_stmt_execute($upd);
                mysqli_stmt_close($upd);
            }
        }
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // Mencegah Session Fixation: buat ID session baru saat pengguna berhasil login
    if (!headers_sent()) {
        session_regenerate_id(true);
    }

    $_SESSION['login']    = true;

    $_SESSION['id_user']  = $user['id_user'];
    $_SESSION['nama']     = $user['nama'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];

    return 1; // Sukses
}


function is_logged_in()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['login']) && $_SESSION['login'] === true;
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
        return "$prefix/dashboard/index.php";
    } elseif ($role === 'petugas') {
        return "$prefix/dashboard/index.php";
    } elseif ($role === 'kepala') {
        return "$prefix/dashboard/index.php";
    }
    return "$prefix/dashboard/index.php";
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


// == PAGINATION ==

function paginate_rows($rows, $page = 1, $per_page = 10)
{
    $page = max(1, (int)$page);
    $per_page = max(1, (int)$per_page);

    $total = count($rows);

    $total_pages = max(
        1,
        (int)ceil($total / $per_page)
    );

    if ($page > $total_pages) {
        $page = $total_pages;
    }

    $offset = ($page - 1) * $per_page;

    return [
        'data' => array_slice($rows, $offset, $per_page),
        'page' => $page,
        'per_page' => $per_page,
        'total' => $total,
        'total_pages' => $total_pages,
        'from' => $total > 0 ? $offset + 1 : 0,
        'to' => min($offset + $per_page, $total)
    ];
}

function pagination_url($params = [])
{
    $params = array_filter($params, static function ($value) {
        return $value !== null && $value !== '';
    });

    return '?' . http_build_query($params);
}


// == 2. KATEGORI ==

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
    if (!has_role(['admin', 'petugas'])) {
        return 0;
    }
    global $db;
    $nama_kategori = trim($post['nama'] ?? '');
    if ($nama_kategori === '') {
        return 0;
    }

    // Periksa apakah nama kategori sudah digunakan.
    $cek = mysqli_prepare(
        $db,
        "SELECT id_kategori FROM kategori WHERE nama_kategori = ? LIMIT 1"
    );
    if ($cek === false) {
        error_log('[UKKinventory] Gagal menyiapkan pemeriksaan kategori: ' . mysqli_error($db));
        return -1;
    }
    mysqli_stmt_bind_param($cek, 's', $nama_kategori);
    if (!mysqli_stmt_execute($cek)) {
        error_log('[UKKinventory] Gagal memeriksa nama kategori: ' . mysqli_stmt_error($cek));
        mysqli_stmt_close($cek);
        return -1;
    }
    mysqli_stmt_store_result($cek);
    $kategori_ada = mysqli_stmt_num_rows($cek) > 0;
    mysqli_stmt_close($cek);

    // Jika nama sudah ada, batalkan penambahan.
    if ($kategori_ada) {
        return -2;
    }

    // Simpan kategori jika namanya belum digunakan.
    $stmt = mysqli_prepare(
        $db,
        "INSERT INTO kategori (nama_kategori) VALUES (?)"
    );
    if ($stmt === false) {
        error_log('[UKKinventory] Gagal menyiapkan query tambah kategori: ' . mysqli_error($db));
        return -1;
    }
    mysqli_stmt_bind_param($stmt, 's', $nama_kategori);
    $success = mysqli_stmt_execute($stmt);
    $affected = $success ? mysqli_stmt_affected_rows($stmt) : 0;
    if (!$success) {
        error_log('[UKKinventory] Gagal menambahkan kategori: ' . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);
    return $success && $affected > 0 ? 1 : -1;
}

function delete_kategori($id_kategori)
{
    if (!has_role(['admin', 'petugas'])) {
        return 0;
    }

    global $db;

    $id_kategori = filter_var(
        $id_kategori,
        FILTER_VALIDATE_INT
    );

    if (
        $id_kategori === false ||
        $id_kategori === null ||
        $id_kategori <= 0
    ) {
        return 0;
    }

    // Periksa apakah kategori masih digunakan oleh barang.
    $cek = mysqli_prepare(
        $db,
        "SELECT id_barang
         FROM barang
         WHERE id_kategori = ?
         LIMIT 1"
    );

    if (!$cek) {
        error_log(
            '[UKKinventory] Gagal menyiapkan pemeriksaan kategori: ' .
            mysqli_error($db)
        );
        return 0;
    }

    mysqli_stmt_bind_param($cek, 'i', $id_kategori);

    if (!mysqli_stmt_execute($cek)) {
        error_log(
            '[UKKinventory] Gagal memeriksa penggunaan kategori: ' .
            mysqli_stmt_error($cek)
        );
        mysqli_stmt_close($cek);
        return 0;
    }

    mysqli_stmt_store_result($cek);
    $masih_dipakai = mysqli_stmt_num_rows($cek) > 0;
    mysqli_stmt_close($cek);

    // Jangan hapus kategori yang masih digunakan barang.
    if ($masih_dipakai) {
        return -2;
    }

    // Hapus kategori jika tidak digunakan oleh barang.
    $stmt = mysqli_prepare(
        $db,
        "DELETE FROM kategori WHERE id_kategori = ?"
    );

    if (!$stmt) {
        error_log(
            '[UKKinventory] Gagal menyiapkan penghapusan kategori: ' .
            mysqli_error($db)
        );
        return 0;
    }

    mysqli_stmt_bind_param($stmt, 'i', $id_kategori);

    $berhasil = mysqli_stmt_execute($stmt);
    $affected = $berhasil
        ? mysqli_stmt_affected_rows($stmt)
        : 0;

    if (!$berhasil) {
        error_log(
            '[UKKinventory] Gagal menghapus kategori: ' .
            mysqli_stmt_error($stmt)
        );
    }

    mysqli_stmt_close($stmt);

    return ($berhasil && $affected > 0) ? 1 : 0;
}

 // == 3. LOKASI ==

function select_lokasi($query)
{
    global $db;

    $result = mysqli_query($db, $query);
    $rows = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        mysqli_free_result($result);
    }

    return $rows;
}

function create_lokasi($post)
{
    global $db;

    if (!has_role(['admin', 'petugas'])) {
        return 0;
    }

    $nama_lokasi = trim($post['nama'] ?? '');

    if ($nama_lokasi === '' || mb_strlen($nama_lokasi, 'UTF-8') > 30) {
        return 0;
    }

    // Periksa apakah nama lokasi sudah terdaftar.
    $stmt = mysqli_prepare(
        $db,
        "SELECT id_lokasi
         FROM lokasi
         WHERE LOWER(TRIM(nama_lokasi)) = LOWER(TRIM(?))
         LIMIT 1"
    );

    if (!$stmt) {
        error_log('Gagal menyiapkan pemeriksaan lokasi: ' . mysqli_error($db));
        return 0;
    }

    mysqli_stmt_bind_param($stmt, 's', $nama_lokasi);

    if (!mysqli_stmt_execute($stmt)) {
        error_log('Gagal memeriksa lokasi: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return 0;
    }

    mysqli_stmt_store_result($stmt);
    $sudah_ada = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    if ($sudah_ada) {
        return -2;
    }

    // Simpan lokasi jika namanya belum terdaftar.
    $stmt = mysqli_prepare(
        $db,
        "INSERT INTO lokasi (nama_lokasi) VALUES (?)"
    );

    if (!$stmt) {
        error_log('Gagal menyiapkan penyimpanan lokasi: ' . mysqli_error($db));
        return 0;
    }

    mysqli_stmt_bind_param($stmt, 's', $nama_lokasi);
    $berhasil = mysqli_stmt_execute($stmt);

    if (!$berhasil) {
        error_log('Gagal menambahkan lokasi: ' . mysqli_stmt_error($stmt));
    }

    $jumlah_data = $berhasil ? mysqli_stmt_affected_rows($stmt) : 0;
    mysqli_stmt_close($stmt);

    return ($berhasil && $jumlah_data > 0) ? 1 : 0;
}

function update_lokasi($post)
{
    if (!has_role(['admin', 'petugas'])) {
        return 0;
    }

    global $db;

    $id_lokasi = filter_var(
        $post['id_lokasi'] ?? null,
        FILTER_VALIDATE_INT
    );

    $nama_lokasi = trim($post['nama'] ?? '');

    if (
        $id_lokasi === false ||
        $id_lokasi === null ||
        $id_lokasi <= 0 ||
        $nama_lokasi === '' ||
        mb_strlen($nama_lokasi) > 30
    ) {
        return 0;
    }

    // Cek apakah nama dipakai lokasi lain.
    $cek = mysqli_prepare(
        $db,
        "SELECT id_lokasi
         FROM lokasi
         WHERE LOWER(TRIM(nama_lokasi)) = LOWER(TRIM(?))
           AND id_lokasi != ?
         LIMIT 1"
    );

    if (!$cek) {
        error_log(
            '[UKKinventory] Gagal menyiapkan pemeriksaan edit lokasi: ' .
            mysqli_error($db)
        );
        return 0;
    }

    mysqli_stmt_bind_param(
        $cek,
        'si',
        $nama_lokasi,
        $id_lokasi
    );

    if (!mysqli_stmt_execute($cek)) {
        mysqli_stmt_close($cek);
        return 0;
    }

    mysqli_stmt_store_result($cek);
    $duplikat = mysqli_stmt_num_rows($cek) > 0;
    mysqli_stmt_close($cek);

    if ($duplikat) {
        return -2;
    }

    $stmt = mysqli_prepare(
        $db,
        "UPDATE lokasi
         SET nama_lokasi = ?
         WHERE id_lokasi = ?"
    );

    if (!$stmt) {
        return 0;
    }

    mysqli_stmt_bind_param(
        $stmt,
        'si',
        $nama_lokasi,
        $id_lokasi
    );

    $berhasil = mysqli_stmt_execute($stmt);

    if (!$berhasil) {
        error_log(
            '[UKKinventory] Gagal mengedit lokasi: ' .
            mysqli_stmt_error($stmt)
        );
    }

    $affected = $berhasil
        ? mysqli_stmt_affected_rows($stmt)
        : 0;

    mysqli_stmt_close($stmt);

    // Nilai 1 berarti data berubah.
    // Nilai 0 juga bisa berarti nama tetap sama.
    if ($berhasil) {
        return 1;
    }

    return 0;
}

function delete_lokasi($id_lokasi)
{
    if (!has_role(['admin', 'petugas'])) {
        return 0;
    }

    global $db;

    $id_lokasi = filter_var(
        $id_lokasi,
        FILTER_VALIDATE_INT
    );

    if (
        $id_lokasi === false ||
        $id_lokasi === null ||
        $id_lokasi <= 0
    ) {
        return 0;
    }

    // Jangan hapus lokasi yang masih dipakai barang.
    $cek = mysqli_prepare(
        $db,
        "SELECT id_barang
         FROM barang
         WHERE id_lokasi = ?
         LIMIT 1"
    );

    if (!$cek) {
        return 0;
    }

    mysqli_stmt_bind_param($cek, 'i', $id_lokasi);

    if (!mysqli_stmt_execute($cek)) {
        mysqli_stmt_close($cek);
        return 0;
    }

    mysqli_stmt_store_result($cek);
    $masih_dipakai = mysqli_stmt_num_rows($cek) > 0;
    mysqli_stmt_close($cek);

    if ($masih_dipakai) {
        return -2;
    }

    $stmt = mysqli_prepare(
        $db,
        "DELETE FROM lokasi WHERE id_lokasi = ?"
    );

    if (!$stmt) {
        return 0;
    }

    mysqli_stmt_bind_param($stmt, 'i', $id_lokasi);

    $berhasil = mysqli_stmt_execute($stmt);
    $affected = $berhasil
        ? mysqli_stmt_affected_rows($stmt)
        : 0;

    if (!$berhasil) {
        error_log(
            '[UKKinventory] Gagal menghapus lokasi: ' .
            mysqli_stmt_error($stmt)
        );
    }

    mysqli_stmt_close($stmt);

    return $berhasil && $affected > 0 ? 1 : 0;
}

// == 4. BARANG ==

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
    if (!has_role(['admin', 'petugas'])) {
        return 0;
    }

    global $db;

    $id_kategori = (int) ($post['id_kategori'] ?? 0);
    $id_lokasi   = (int) ($post['id_lokasi'] ?? 0);
    $kode_barang = trim($post['kode_barang'] ?? '');
    $nama_barang = trim($post['nama_barang'] ?? '');
    $stok        = filter_var($post['stok'] ?? null, FILTER_VALIDATE_INT);
    $satuan      = trim($post['satuan'] ?? '');
    $kondisi     = $post['kondisi'] ?? '';
    $keterangan  = trim($post['keterangan'] ?? '');

    if (
        $id_kategori <= 0 ||
        $id_lokasi <= 0 ||
        $kode_barang === '' ||
        $nama_barang === '' ||
        $satuan === '' ||
        !in_array($kondisi, ['layak', 'tidak_layak', 'perbaikan'], true) ||
        $stok === false ||
        $stok === null ||
        $stok < 0
    ) {
        return 0;
    }

    // Cek nama barang duplikat.
    $cek = mysqli_prepare(
        $db,
        "SELECT id_barang
         FROM barang
         WHERE LOWER(TRIM(nama_barang)) = LOWER(TRIM(?))
         LIMIT 1"
    );

    if (!$cek) {
        return 0;
    }

    mysqli_stmt_bind_param($cek, "s", $nama_barang);

    if (!mysqli_stmt_execute($cek)) {
        mysqli_stmt_close($cek);
        return 0;
    }

    mysqli_stmt_store_result($cek);
    $duplikat = mysqli_stmt_num_rows($cek) > 0;
    mysqli_stmt_close($cek);

    if ($duplikat) {
        return -2;
    }

    // Simpan data barang.
    $query = mysqli_prepare(
        $db,
        "INSERT INTO barang
        (id_kategori, id_lokasi, kode_barang, nama_barang,
         stok, satuan, kondisi, keterangan)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$query) {
        return 0;
    }

    mysqli_stmt_bind_param(
        $query,
        "iississs",
        $id_kategori,
        $id_lokasi,
        $kode_barang,
        $nama_barang,
        $stok,
        $satuan,
        $kondisi,
        $keterangan
    );

    $berhasil = mysqli_stmt_execute($query);
    mysqli_stmt_close($query);

    return $berhasil ? 1 : 0;
}

function update_barang($post)
{
    if (!has_role(['admin', 'petugas'])) {
        return 0;
    }

    global $db;

    $id_barang = filter_var(
        $post['id_barang'] ?? null,
        FILTER_VALIDATE_INT
    );

    $id_kategori = filter_var(
        $post['id_kategori'] ?? null,
        FILTER_VALIDATE_INT
    );

    $id_lokasi = filter_var(
        $post['id_lokasi'] ?? null,
        FILTER_VALIDATE_INT
    );

    $stok = filter_var(
        $post['stok'] ?? null,
        FILTER_VALIDATE_INT
    );

    $kode_barang = trim($post['kode_barang'] ?? '');
    $nama_barang = trim($post['nama_barang'] ?? '');
    $satuan = trim($post['satuan'] ?? '');
    $kondisi = $post['kondisi'] ?? '';
    $keterangan = trim($post['keterangan'] ?? '');

    if (
        $id_barang === false || $id_barang === null || $id_barang <= 0 ||
        $id_kategori === false || $id_kategori === null || $id_kategori <= 0 ||
        $id_lokasi === false || $id_lokasi === null || $id_lokasi <= 0 ||
        $stok === false || $stok === null || $stok < 0 ||
        $kode_barang === '' ||
        $nama_barang === '' ||
        $satuan === '' ||
        !in_array(
            $kondisi,
            ['layak', 'tidak_layak', 'perbaikan'],
            true
        )
    ) {
        return 0;
    }

    // Cek nama barang duplikat, kecuali barang yang sedang diedit.
    $cek = mysqli_prepare(
        $db,
        "SELECT id_barang
         FROM barang
         WHERE LOWER(TRIM(nama_barang)) = LOWER(TRIM(?))
           AND id_barang != ?
         LIMIT 1"
    );

    if (!$cek) {
        return 0;
    }

    mysqli_stmt_bind_param(
        $cek,
        'si',
        $nama_barang,
        $id_barang
    );

    if (!mysqli_stmt_execute($cek)) {
        mysqli_stmt_close($cek);
        return 0;
    }

    mysqli_stmt_store_result($cek);
    $duplikat = mysqli_stmt_num_rows($cek) > 0;
    mysqli_stmt_close($cek);

    if ($duplikat) {
        return -2;
    }

    // Perbarui seluruh data barang, termasuk stok.
    $stmt = mysqli_prepare(
        $db,
        "UPDATE barang
         SET id_kategori = ?,
             id_lokasi = ?,
             kode_barang = ?,
             nama_barang = ?,
             stok = ?,
             satuan = ?,
             kondisi = ?,
             keterangan = ?
         WHERE id_barang = ?"
    );

    if (!$stmt) {
        return 0;
    }

    mysqli_stmt_bind_param(
        $stmt,
        'iississsi',
        $id_kategori,
        $id_lokasi,
        $kode_barang,
        $nama_barang,
        $stok,
        $satuan,
        $kondisi,
        $keterangan,
        $id_barang
    );

    $berhasil = mysqli_stmt_execute($stmt);

    if (!$berhasil) {
        error_log(
            '[UKKinventory] Gagal memperbarui barang: ' .
            mysqli_stmt_error($stmt)
        );
    }

    mysqli_stmt_close($stmt);

    return $berhasil ? 1 : 0;
}

function delete_barang($id_barang)
{
    if (!has_role(['admin', 'petugas'])) {
        return 0;
    }

    global $db;

    $id_barang = filter_var($id_barang, FILTER_VALIDATE_INT);

    if ($id_barang === false || $id_barang === null || $id_barang <= 0) {
        return 0;
    }

    // Pastikan barang memang tersedia.
    $stmt = mysqli_prepare(
        $db,
        "SELECT id_barang FROM barang WHERE id_barang = ?"
    );

    if ($stmt === false) {
        error_log('[UKKinventory] Gagal memeriksa barang: ' . mysqli_error($db));
        return -1;
    }

    mysqli_stmt_bind_param($stmt, 'i', $id_barang);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    $barang_ada = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    if (!$barang_ada) {
        return 0;
    }

    // Periksa apakah barang memiliki riwayat peminjaman.
    $stmt = mysqli_prepare(
        $db,
        "SELECT id_barang FROM peminjaman WHERE id_barang = ? LIMIT 1"
    );

    if ($stmt === false) {
        error_log('[UKKinventory] Gagal memeriksa riwayat peminjaman: ' . mysqli_error($db));
        return -1;
    }

    mysqli_stmt_bind_param($stmt, 'i', $id_barang);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    $ada_peminjaman = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    // Periksa apakah barang memiliki riwayat transaksi.
    $stmt = mysqli_prepare(
        $db,
        "SELECT id_barang FROM transaksi WHERE id_barang = ? LIMIT 1"
    );

    if ($stmt === false) {
        error_log('[UKKinventory] Gagal memeriksa riwayat transaksi: ' . mysqli_error($db));
        return -1;
    }
    mysqli_stmt_bind_param($stmt, 'i', $id_barang);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    $ada_transaksi = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    // Jangan hapus barang jika riwayatnya masih ada.
    if ($ada_peminjaman || $ada_transaksi) {
        return -2;
    }
    $stmt = mysqli_prepare(
        $db,
        "DELETE FROM barang WHERE id_barang = ?"
    );

    if ($stmt === false) {
        error_log('[UKKinventory] Gagal menyiapkan penghapusan barang: ' . mysqli_error($db));
        return -1;
    }
    mysqli_stmt_bind_param($stmt, 'i', $id_barang);
    $berhasil = mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);

    if (!$berhasil) {
        error_log('[UKKinventory] Gagal menghapus barang: ' . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);
    return $berhasil && $affected > 0 ? 1 : -1;
}

// == 5. PEMINJAMAN ==

function select_peminjaman($query)
{
    global $db;

    $result = mysqli_query($db, $query);

    if (!$result) {
        error_log('Gagal mengambil data peminjaman: ' . mysqli_error($db));
        return [];
    }

    $data = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    return $data;
}

function create_peminjaman($post)
{
    global $db;

    if (!has_role(['admin', 'petugas'])) {
        return 0;
    }

    $id_barang = filter_var(
        $post['id_barang'] ?? null,
        FILTER_VALIDATE_INT
    );
    $jumlah = filter_var(
        $post['jumlah'] ?? null,
        FILTER_VALIDATE_INT
    );
    $nama_peminjam = trim($post['nama_peminjam'] ?? '');
    $tanggal_pinjam = $post['tanggal_pinjam'] ?? '';
    $tanggal_kembali_post = trim($post['tanggal_kembali'] ?? '');

    if (
        !$id_barang || $id_barang < 1 ||
        !$jumlah || $jumlah < 1 ||
        $nama_peminjam === '' ||
        strlen($nama_peminjam) > 50 ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_pinjam)
    ) {
        return 0;
    }

    [$tahun, $bulan, $hari] = array_map(
        'intval',
        explode('-', $tanggal_pinjam)
    );

    if (!checkdate($bulan, $hari, $tahun) || $tanggal_pinjam < date('Y-m-d')) {
        return 0;
    }

    $tanggal_kembali = null;
    if ($tanggal_kembali_post !== '') {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_kembali_post)) {
            return 0;
        }
        [$tk_tahun, $tk_bulan, $tk_hari] = array_map(
            'intval',
            explode('-', $tanggal_kembali_post)
        );
        if (!checkdate($tk_bulan, $tk_hari, $tk_tahun)) {
            return 0;
        }
        if ($tanggal_kembali_post < $tanggal_pinjam) {
            return 0;
        }
        $tanggal_kembali = $tanggal_kembali_post;
    }

    $id_user = (int) ($_SESSION['id_user'] ?? 0);

    if ($id_user < 1) {
        return 0;
    }

    mysqli_begin_transaction($db);

    try {
        // Kunci data barang agar stok tidak dipinjam melebihi jumlahnya.
        $stmt = mysqli_prepare(
            $db,
            "SELECT stok, kondisi
             FROM barang
             WHERE id_barang = ?
             FOR UPDATE"
        );
        mysqli_stmt_bind_param($stmt, 'i', $id_barang);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $barang = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (
            !$barang ||
            $barang['kondisi'] !== 'layak' ||
            (int) $barang['stok'] < $jumlah
        ) {
            mysqli_rollback($db);
            return -1;
        }

        // Pastikan pengguna yang dipilih benar-benar ada.
        $stmt = mysqli_prepare(
            $db,
            "SELECT id_user FROM users WHERE id_user = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $id_user);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user_exists = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$user_exists) {
            mysqli_rollback($db);
            return 0;
        }

        $status = 'dipinjam';

        $stmt = mysqli_prepare(
            $db,
            "INSERT INTO peminjaman
                (id_barang, nama_peminjam, tanggal_pinjam,
                jumlah, status, id_user,
                tanggal_kembali, kondisi_kembali)
            VALUES (?, ?, ?, ?, ?, ?, ?, NULL)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'issisis',
            $id_barang,
            $nama_peminjam,
            $tanggal_pinjam,
            $jumlah,
            $status,
            $id_user,
            $tanggal_kembali
        );

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            throw new Exception('Gagal menyimpan peminjaman.');
        }

        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare(
            $db,
            "UPDATE barang
             SET stok = stok - ?
             WHERE id_barang = ? AND stok >= ? AND kondisi = 'layak'"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'iii',
            $jumlah,
            $id_barang,
            $jumlah
        );

        if (
            !mysqli_stmt_execute($stmt) ||
            mysqli_stmt_affected_rows($stmt) !== 1
        ) {
            mysqli_stmt_close($stmt);
            throw new Exception('Stok barang gagal diperbarui.');
        }

        mysqli_stmt_close($stmt);
        mysqli_commit($db);

        return 1;
    } catch (Throwable $e) {
        mysqli_rollback($db);
        error_log('Gagal membuat peminjaman: ' . $e->getMessage());
        return 0;
    }
}

function kembalikan_peminjaman($post)
{
    global $db;

    if (!has_role(['admin', 'petugas'])) {
        return 0;
    }

    $id_pinjam = filter_var(
        $post['id_pinjam'] ?? null,
        FILTER_VALIDATE_INT
    );
    $tanggal_kembali = $post['tanggal_kembali'] ?? '';
    $kondisi_kembali = trim($post['kondisi_kembali'] ?? '');

    $kondisi_valid = [
        'Baik / Layak',
        'Rusak Ringan',
        'Rusak Berat',
        'Perlu Perbaikan'
    ];

    if (
        !$id_pinjam || $id_pinjam < 1 ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_kembali) ||
        !in_array($kondisi_kembali, $kondisi_valid, true)
    ) {
        return 0;
    }

    [$tahun, $bulan, $hari] = array_map(
        'intval',
        explode('-', $tanggal_kembali)
    );

    if (!checkdate($bulan, $hari, $tahun)) {
        return 0;
    }

    mysqli_begin_transaction($db);

    try {
        $stmt = mysqli_prepare(
            $db,
            "SELECT id_barang, jumlah, tanggal_pinjam
             FROM peminjaman
             WHERE id_pinjam = ? AND status = 'dipinjam'
             FOR UPDATE"
        );
        mysqli_stmt_bind_param($stmt, 'i', $id_pinjam);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $pinjaman = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$pinjaman) {
            mysqli_rollback($db);
            return 0;
        }

        // Tanggal kembali tidak boleh sebelum tanggal pinjam.
        if ($tanggal_kembali < $pinjaman['tanggal_pinjam']) {
            mysqli_rollback($db);
            return 0;
        }

        $id_barang = (int) $pinjaman['id_barang'];
        $jumlah = (int) $pinjaman['jumlah'];

        $stmt = mysqli_prepare(
            $db,
            "UPDATE barang SET stok = stok + ? WHERE id_barang = ?"
        );
        mysqli_stmt_bind_param($stmt, 'ii', $jumlah, $id_barang);

        if (
            !mysqli_stmt_execute($stmt) ||
            mysqli_stmt_affected_rows($stmt) !== 1
        ) {
            mysqli_stmt_close($stmt);
            throw new Exception('Stok barang gagal diperbarui.');
        }

        mysqli_stmt_close($stmt);

        $status = 'dikembalikan';

        $stmt = mysqli_prepare(
            $db,
            "UPDATE peminjaman
             SET status = ?, tanggal_kembali = ?, kondisi_kembali = ?
             WHERE id_pinjam = ? AND status = 'dipinjam'"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'sssi',
            $status,
            $tanggal_kembali,
            $kondisi_kembali,
            $id_pinjam
        );

        if (
            !mysqli_stmt_execute($stmt) ||
            mysqli_stmt_affected_rows($stmt) !== 1
        ) {
            mysqli_stmt_close($stmt);
            throw new Exception('Status pengembalian gagal diperbarui.');
        }

        mysqli_stmt_close($stmt);
        mysqli_commit($db);

        return 1;
    } catch (Throwable $e) {
        mysqli_rollback($db);
        error_log('Gagal memproses pengembalian: ' . $e->getMessage());
        return 0;
    }
}

function update_peminjaman($post)
{
    global $db;

    if (!has_role(['admin', 'petugas'])) {
        return 0;
    }

    $id_pinjam = filter_var(
        $post['id_pinjam'] ?? null,
        FILTER_VALIDATE_INT
    );
    $nama_peminjam = trim($post['nama_peminjam'] ?? '');
    $tanggal_pinjam = $post['tanggal_pinjam'] ?? '';

    if (
        !$id_pinjam || $id_pinjam < 1 ||
        $nama_peminjam === '' ||
        strlen($nama_peminjam) > 50 ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_pinjam)
    ) {
        return 0;
    }

    [$tahun, $bulan, $hari] = array_map(
        'intval',
        explode('-', $tanggal_pinjam)
    );

    if (!checkdate($bulan, $hari, $tahun)) {
        return 0;
    }

    $stmt = mysqli_prepare(
        $db,
        "UPDATE peminjaman
         SET nama_peminjam = ?, tanggal_pinjam = ?
         WHERE id_pinjam = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        'ssi',
        $nama_peminjam,
        $tanggal_pinjam,
        $id_pinjam
    );

    $success = mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    return ($success && $affected > 0) ? 1 : 0;
}

function delete_peminjaman($id_pinjam)
{
    global $db;

    if (!has_role(['admin', 'petugas'])) {
        return 0;
    }

    $id_pinjam = filter_var($id_pinjam, FILTER_VALIDATE_INT);

    if ($id_pinjam === false || $id_pinjam === null || $id_pinjam < 1) {
        return 0;
    }

    mysqli_begin_transaction($db);

    try {
        $stmt = mysqli_prepare(
            $db,
            "SELECT status
             FROM peminjaman
             WHERE id_pinjam = ?
             FOR UPDATE"
        );

        if (!$stmt) {
            throw new Exception('Gagal menyiapkan pemeriksaan peminjaman.');
        }

        mysqli_stmt_bind_param($stmt, 'i', $id_pinjam);

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            throw new Exception('Gagal memeriksa status peminjaman.');
        }

        $result = mysqli_stmt_get_result($stmt);
        $pinjaman = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$pinjaman) {
            mysqli_rollback($db);
            return 0;
        }

        // Peminjaman yang belum dikembalikan tidak boleh dihapus.
        if ($pinjaman['status'] === 'dipinjam') {
            mysqli_rollback($db);
            return -2;
        }

        // Hapus hanya jika barang sudah dikembalikan.
        $stmt = mysqli_prepare(
            $db,
            "DELETE FROM peminjaman
             WHERE id_pinjam = ?"
        );

        if (!$stmt) {
            throw new Exception('Gagal menyiapkan penghapusan peminjaman.');
        }

        mysqli_stmt_bind_param($stmt, 'i', $id_pinjam);

        if (
            !mysqli_stmt_execute($stmt) ||
            mysqli_stmt_affected_rows($stmt) !== 1
        ) {
            mysqli_stmt_close($stmt);
            throw new Exception('Data peminjaman gagal dihapus.');
        }

        mysqli_stmt_close($stmt);

        mysqli_commit($db);
        return 1;

    } catch (Throwable $e) {
        mysqli_rollback($db);
        error_log('Gagal menghapus peminjaman: ' . $e->getMessage());
        return 0;
    }
}

// == 6. TRANSAKSI ==

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
    $id_barang = (int)($post['id_barang'] ?? 0);
    $tanggal = trim($post['tanggal'] ?? '');
    $jenis_transaksi = strtolower(trim($post['jenis_transaksi'] ?? ''));
    $jumlah = (int)($post['jumlah'] ?? 0);
    $keterangan = trim($post['keterangan'] ?? '');
    $id_user = (int)($_SESSION['id_user'] ?? 0);
    $date = DateTime::createFromFormat('!Y-m-d', $tanggal);

    if (
        $id_barang <= 0 ||
        $id_user <= 0 ||
        $jumlah <= 0 ||
        strlen($keterangan) > 100 ||
        !in_array($jenis_transaksi, ['masuk', 'keluar'], true) ||
        !$date ||
        $date->format('Y-m-d') !== $tanggal
    ) {
        return -2;
    }

    if (!mysqli_begin_transaction($db)) {
        error_log('[UKKinventory] create_transaksi: gagal memulai transaksi database: ' . mysqli_error($db));
        return 0;
    }

    $cek_stok = mysqli_prepare($db, 'SELECT stok FROM barang WHERE id_barang = ? FOR UPDATE');
    if (!$cek_stok) {
        error_log('[UKKinventory] create_transaksi: gagal menyiapkan query stok: ' . mysqli_error($db));
        mysqli_rollback($db);
        return 0;
    }
    mysqli_stmt_bind_param($cek_stok, 'i', $id_barang);
    if (!mysqli_stmt_execute($cek_stok)) {
        error_log('[UKKinventory] create_transaksi: gagal membaca stok: ' . mysqli_stmt_error($cek_stok));
        mysqli_stmt_close($cek_stok);
        mysqli_rollback($db);
        return 0;
    }
    mysqli_stmt_bind_result($cek_stok, $stok);
    $barang_ditemukan = mysqli_stmt_fetch($cek_stok);
    mysqli_stmt_close($cek_stok);

    if (!$barang_ditemukan) {
        mysqli_rollback($db);
        return -2;
    }
    if ($jenis_transaksi === 'keluar' && $stok < $jumlah) {
        mysqli_rollback($db);
        return -1;
    }

    $insert = mysqli_prepare(
        $db,
        'INSERT INTO transaksi (id_barang, id_user, tanggal, jenis_transaksi, jumlah, keterangan) VALUES (?, ?, ?, ?, ?, ?)'
    );
    if (!$insert) {
        error_log('[UKKinventory] create_transaksi: gagal menyiapkan query transaksi: ' . mysqli_error($db));
        mysqli_rollback($db);
        return 0;
    }
    mysqli_stmt_bind_param($insert, 'iissis', $id_barang, $id_user, $tanggal, $jenis_transaksi, $jumlah, $keterangan);
    if (!mysqli_stmt_execute($insert)) {
        error_log('[UKKinventory] create_transaksi: gagal menyimpan transaksi: ' . mysqli_stmt_error($insert));
        mysqli_stmt_close($insert);
        mysqli_rollback($db);
        return 0;
    }
    mysqli_stmt_close($insert);

    $perubahan_stok = $jenis_transaksi === 'masuk' ? $jumlah : -$jumlah;
    $update_stok = mysqli_prepare($db, 'UPDATE barang SET stok = stok + ? WHERE id_barang = ?');
    if (!$update_stok) {
        error_log('[UKKinventory] create_transaksi: gagal menyiapkan perubahan stok: ' . mysqli_error($db));
        mysqli_rollback($db);
        return 0;
    }
    mysqli_stmt_bind_param($update_stok, 'ii', $perubahan_stok, $id_barang);
    if (!mysqli_stmt_execute($update_stok) || mysqli_stmt_affected_rows($update_stok) !== 1) {
        error_log('[UKKinventory] create_transaksi: gagal memperbarui stok: ' . mysqli_stmt_error($update_stok));
        mysqli_stmt_close($update_stok);
        mysqli_rollback($db);
        return 0;
    }
    mysqli_stmt_close($update_stok);

    if (!mysqli_commit($db)) {
        error_log('[UKKinventory] create_transaksi: gagal menyelesaikan transaksi database: ' . mysqli_error($db));
        mysqli_rollback($db);
        return 0;
    }

    return 1;
}

function delete_transaksi($id_transaksi)
{
    if (!has_role(['admin', 'petugas'])) {
        return 0;
    }
    $id_transaksi = (int)$id_transaksi;
    if ($id_transaksi <= 0) {
        return 0;
    }
    global $db;
    if (!mysqli_begin_transaction($db)) {
        error_log('[UKKinventory] Gagal memulai penghapusan transaksi: ' . mysqli_error($db));
        return 0;
    }
    $cek = mysqli_prepare(
        $db,
        "SELECT id_barang, jumlah, jenis_transaksi
         FROM transaksi
         WHERE id_transaksi = ?
         FOR UPDATE"
    );
    if ($cek === false) {
        error_log('[UKKinventory] Gagal menyiapkan query transaksi: ' . mysqli_error($db));
        mysqli_rollback($db);
        return 0;
    }
    mysqli_stmt_bind_param($cek, 'i', $id_transaksi);
    if (!mysqli_stmt_execute($cek)) {
        error_log('[UKKinventory] Gagal membaca transaksi: ' . mysqli_stmt_error($cek));
        mysqli_stmt_close($cek);
        mysqli_rollback($db);
        return 0;
    }
    mysqli_stmt_bind_result($cek, $id_barang, $jumlah, $jenis_transaksi);
    $ditemukan = mysqli_stmt_fetch($cek);
    mysqli_stmt_close($cek);
    if (!$ditemukan) {
        mysqli_rollback($db);
        return 0;
    }
    $id_barang = (int)$id_barang;
    $jumlah = (int)$jumlah;
    $jenis_transaksi = strtolower($jenis_transaksi);
    if (
        $id_barang <= 0 ||
        $jumlah <= 0 ||
        !in_array($jenis_transaksi, ['masuk', 'keluar'], true)
    ) {
        mysqli_rollback($db);
        return 0;
    }
    $perubahan_stok = $jenis_transaksi === 'masuk' ? -$jumlah : $jumlah;
    $update = mysqli_prepare(
        $db,
        "UPDATE barang
         SET stok = stok + ?
         WHERE id_barang = ?
           AND stok + ? >= 0"
    );
    if ($update === false) {
        error_log('[UKKinventory] Gagal menyiapkan perubahan stok: ' . mysqli_error($db));
        mysqli_rollback($db);
        return 0;
    }
    mysqli_stmt_bind_param(
        $update,
        'iii',
        $perubahan_stok,
        $id_barang,
        $perubahan_stok
    );
    if (!mysqli_stmt_execute($update) || mysqli_stmt_affected_rows($update) !== 1) {
        error_log('[UKKinventory] Gagal memperbarui stok saat menghapus transaksi: ' . mysqli_stmt_error($update));
        mysqli_stmt_close($update);
        mysqli_rollback($db);
        return 0;
    }
    mysqli_stmt_close($update);
    $hapus = mysqli_prepare(
        $db,
        "DELETE FROM transaksi WHERE id_transaksi = ?"
    );
    if ($hapus === false) {
        error_log('[UKKinventory] Gagal menyiapkan penghapusan transaksi: ' . mysqli_error($db));
        mysqli_rollback($db);
        return 0;
    }
    mysqli_stmt_bind_param($hapus, 'i', $id_transaksi);
    if (!mysqli_stmt_execute($hapus) || mysqli_stmt_affected_rows($hapus) !== 1) {
        error_log('[UKKinventory] Gagal menghapus transaksi: ' . mysqli_stmt_error($hapus));
        mysqli_stmt_close($hapus);
        mysqli_rollback($db);
        return 0;
    }
    mysqli_stmt_close($hapus);
    if (!mysqli_commit($db)) {
        error_log('[UKKinventory] Gagal menyelesaikan penghapusan transaksi: ' . mysqli_error($db));
        mysqli_rollback($db);
        return 0;
    }
    return 1;
}

// == 7. MANAJEMEN USERS ==

function select_users($query)
{
    if (!has_role('admin')) {
        return [];
    }
    global $db;
    $result = mysqli_query($db, $query);
    if ($result === false) {
        error_log(
            '[UKKinventory] Gagal mengambil data pengguna: ' .
            mysqli_error($db)
        );
        return [];
    }
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_free_result($result);
    return $rows;
}

function create_user($post)
{
    if (!has_role('admin')) {
        return 0;
    }
    $nama = trim($post['nama'] ?? '');
    $username = strtolower(trim($post['username'] ?? ''));
    $password_input = $post['password'] ?? '';
    $role = trim($post['role'] ?? '');
    if (
        $nama === '' ||
        $username === '' ||
        !is_string($password_input) ||
        trim($password_input) === '' ||
        !in_array($role, ['admin', 'petugas', 'kepala'], true)
    ) {
        return 0;
    }
    global $db;
    $cek = mysqli_prepare(
        $db,
        "SELECT id_user FROM users WHERE username = ?"
    );
    if ($cek === false) {
        error_log('[UKKinventory] Gagal menyiapkan pemeriksaan username: ' . mysqli_error($db));
        return 0;
    }
    mysqli_stmt_bind_param($cek, 's', $username);
    if (!mysqli_stmt_execute($cek)) {
        error_log('[UKKinventory] Gagal memeriksa username: ' . mysqli_stmt_error($cek));
        mysqli_stmt_close($cek);
        return 0;
    }
    mysqli_stmt_store_result($cek);
    $username_ada = mysqli_stmt_num_rows($cek) > 0;
    mysqli_stmt_close($cek);
    if ($username_ada) {
        return -1;
    }
    $password = password_hash(trim($password_input), PASSWORD_DEFAULT);
    if ($password === false) {
        error_log('[UKKinventory] Gagal membuat hash password pengguna.');
        return 0;
    }
    $stmt = mysqli_prepare(
        $db,
        "INSERT INTO users (nama, username, password, role)
         VALUES (?, ?, ?, ?)"
    );
    if ($stmt === false) {
        error_log('[UKKinventory] Gagal menyiapkan query tambah pengguna: ' . mysqli_error($db));
        return 0;
    }
    mysqli_stmt_bind_param(
        $stmt,
        'ssss',
        $nama,
        $username,
        $password,
        $role
    );
    $success = mysqli_stmt_execute($stmt);
    $affected = $success ? mysqli_stmt_affected_rows($stmt) : 0;
    if (!$success) {
        error_log('[UKKinventory] Gagal menambahkan pengguna: ' . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);
    return $affected;
}

function update_user($post)
{
    if (!has_role('admin')) {
        return 0;
    }
    $id_user = (int)($post['id_user'] ?? 0);
    $nama = trim($post['nama'] ?? '');
    $username = strtolower(trim($post['username'] ?? ''));
    $role = trim($post['role'] ?? '');
    $password_input = $post['password'] ?? '';
    if (
        $id_user <= 0 ||
        $nama === '' ||
        $username === '' ||
        !in_array($role, ['admin', 'petugas', 'kepala'], true) ||
        !is_string($password_input)
    ) {
        return 0;
    }
    global $db;
    // Lindungi agar Admin terakhir tidak kehilangan role Admin.
    if ($role !== 'admin') {
        $admin_check = mysqli_prepare(
            $db,
            "SELECT COUNT(*) AS total
             FROM users
             WHERE LOWER(role) = 'admin'
               AND id_user = ?"
        );

        if ($admin_check === false) {
            error_log(
                '[UKKinventory] Gagal memeriksa role Admin: ' .
                mysqli_error($db)
            );
            return 0;
        }

        mysqli_stmt_bind_param($admin_check, 'i', $id_user);

        if (!mysqli_stmt_execute($admin_check)) {
            error_log(
                '[UKKinventory] Gagal menjalankan pemeriksaan role Admin: ' .
                mysqli_stmt_error($admin_check)
            );
            mysqli_stmt_close($admin_check);
            return 0;
        }
        $admin_result = mysqli_stmt_get_result($admin_check);
        $admin_data = $admin_result
            ? mysqli_fetch_assoc($admin_result)
            : null;

        mysqli_stmt_close($admin_check);

        if ($admin_data && (int)$admin_data['total'] > 0) {
            $total_result = mysqli_query(
                $db,
                "SELECT COUNT(*) AS total
                 FROM users
                 WHERE LOWER(role) = 'admin'"
            );
            if (!$total_result) {
                error_log(
                    '[UKKinventory] Gagal menghitung akun Admin: ' .
                    mysqli_error($db)
                );
                return 0;
            }
            $total_data = mysqli_fetch_assoc($total_result);
            if ((int)$total_data['total'] <= 1) {
                return -2;
            }
        }
    }
    $cek = mysqli_prepare(
        $db,
        "SELECT id_user FROM users WHERE username = ? AND id_user != ?"
    );
    if ($cek === false) {
        error_log('[UKKinventory] Gagal menyiapkan pemeriksaan username edit: ' . mysqli_error($db));
        return 0;
    }
    mysqli_stmt_bind_param($cek, 'si', $username, $id_user);
    if (!mysqli_stmt_execute($cek)) {
        error_log('[UKKinventory] Gagal memeriksa username edit: ' . mysqli_stmt_error($cek));
        mysqli_stmt_close($cek);
        return 0;
    }
    mysqli_stmt_store_result($cek);
    $username_ada = mysqli_stmt_num_rows($cek) > 0;
    mysqli_stmt_close($cek);
    if ($username_ada) {
        return -1;
    }
    if (trim($password_input) !== '') {
        $password = password_hash(trim($password_input), PASSWORD_DEFAULT);
        if ($password === false) {
            error_log('[UKKinventory] Gagal membuat hash password saat edit pengguna.');
            return 0;
        }
        $stmt = mysqli_prepare(
            $db,
            "UPDATE users
             SET nama = ?, username = ?, password = ?, role = ?
             WHERE id_user = ?"
        );
        if ($stmt === false) {
            error_log('[UKKinventory] Gagal menyiapkan query edit pengguna: ' . mysqli_error($db));
            return 0;
        }
        mysqli_stmt_bind_param(
            $stmt,
            'ssssi',
            $nama,
            $username,
            $password,
            $role,
            $id_user
        );
    } else {
        $stmt = mysqli_prepare(
            $db,
            "UPDATE users
             SET nama = ?, username = ?, role = ?
             WHERE id_user = ?"
        );
        if ($stmt === false) {
            error_log('[UKKinventory] Gagal menyiapkan query edit pengguna: ' . mysqli_error($db));
            return 0;
        }
        mysqli_stmt_bind_param(
            $stmt,
            'sssi',
            $nama,
            $username,
            $role,
            $id_user
        );
    }
    $success = mysqli_stmt_execute($stmt);
    $affected = $success ? mysqli_stmt_affected_rows($stmt) : 0;
    if (!$success) {
        error_log('[UKKinventory] Gagal mengedit pengguna: ' . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);
    return $affected;
}

function delete_user($id_user)
{
    if (!has_role('admin')) {
        return 0;
    }
    $id_user = (int)$id_user;
    if ($id_user <= 0) {
        return 0;
    }
    if (
        isset($_SESSION['id_user']) &&
        (int)$_SESSION['id_user'] === $id_user
    ) {
        return -2;
    }
   
    global $db;
    // Cegah penghapusan akun Admin terakhir.
    $admin_check = mysqli_prepare(
        $db,
        "SELECT role FROM users WHERE id_user = ?"
    );

    if ($admin_check === false) {
        error_log(
            '[UKKinventory] Gagal memeriksa role sebelum menghapus pengguna: ' .
            mysqli_error($db)
        );
        return 0;
    }

    mysqli_stmt_bind_param($admin_check, 'i', $id_user);

    if (!mysqli_stmt_execute($admin_check)) {
        error_log(
            '[UKKinventory] Gagal menjalankan pemeriksaan role pengguna: ' .
            mysqli_stmt_error($admin_check)
        );
        mysqli_stmt_close($admin_check);
        return 0;
    }

    $admin_result = mysqli_stmt_get_result($admin_check);
    $user_data = $admin_result
        ? mysqli_fetch_assoc($admin_result)
        : null;

    mysqli_stmt_close($admin_check);

    if (!$user_data) {
        return 0;
    }

    if (strtolower($user_data['role']) === 'admin') {
        $total_result = mysqli_query(
            $db,
            "SELECT COUNT(*) AS total
             FROM users
             WHERE LOWER(role) = 'admin'"
        );

        if (!$total_result) {
            error_log(
                '[UKKinventory] Gagal menghitung akun Admin: ' .
                mysqli_error($db)
            );
            return 0;
        }

        $total_data = mysqli_fetch_assoc($total_result);

        if ((int)$total_data['total'] <= 1) {
            return -3;
        }
    }
    $stmt = mysqli_prepare(
        $db,
        "DELETE FROM users WHERE id_user = ?"
    );
    if ($stmt === false) {
        error_log(
            '[UKKinventory] Gagal menyiapkan penghapusan pengguna: ' .
            mysqli_error($db)
        );
        return 0;
    }
    mysqli_stmt_bind_param($stmt, 'i', $id_user);
    $success = mysqli_stmt_execute($stmt);
    $affected = $success ? mysqli_stmt_affected_rows($stmt) : 0;
    if (!$success) {
        error_log(
            '[UKKinventory] Gagal menghapus pengguna: ' .
            mysqli_stmt_error($stmt)
        );
    }
    mysqli_stmt_close($stmt);
    return $affected;
}


// == 8. LAPORAN ==

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


// == 9. DASHBOARD AGGREGATION ==

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