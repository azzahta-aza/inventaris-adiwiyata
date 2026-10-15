<?php

include_once '../../app/app.php';

// Jika sudah login, arahkan ke halaman sesuai role
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header('Location: ' . get_landing_url('..'));
    exit;
}

$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF token
    $csrf_form  = $_POST['csrf_token'] ?? '';
    $csrf_sesi  = $_SESSION['csrf_token'] ?? '';

    if (
        !is_string($csrf_form) ||
        $csrf_sesi === '' ||
        !hash_equals($csrf_sesi, $csrf_form)
    ) {
        $pesan_error = 'Permintaan tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } else {
        $res = login($_POST);

        if ($res === 1) {
            header('Location: ' . get_landing_url('..'));
            exit;
        } elseif ($res === -1) {
            $pesan_error = 'Password yang Anda masukkan tidak sesuai!';
        } else {
            $pesan_error = 'Username tidak terdaftar!';
        }
    }
}
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login | SI Inventory Barang Adiwiyata</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            min-height: 100vh;
            margin: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            background:
                url('../../img/background.png')
                center / cover no-repeat;

            font-family: Arial, sans-serif;
        }

        .login-box {
            width: 100%;
            max-width: 420px;

            padding: 35px;

            background: #ffffff;
            border-radius: 18px;

            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .login-logo {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
        }

        .login-title {
            color: #1b5e20;
            font-weight: bold;
        }

        .login-subtitle {
            color: #777;
            font-size: 14px;
        }

        .form-control {
            height: 48px;
            border-radius: 10px;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-control {
            padding-right: 48px;
        }

        .toggle-password-btn {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #6c757d;
            cursor: pointer;
            padding: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            z-index: 5;
            transition: color 0.2s ease-in-out;
        }

        .toggle-password-btn:hover,
        .toggle-password-btn:focus {
            color: #1b5e20;
            outline: none;
        }

        .btn-login {
            width: 100%;
            height: 48px;

            border: none;
            border-radius: 10px;

            background: #2e7d32;
            color: #ffffff;

            font-weight: bold;
        }

        .btn-login:hover {
            background: #1b5e20;
        }

        .remember-me {
            font-size: 14px;
            color: #666;
        }

        .footer {
            margin-top: 25px;

            text-align: center;

            font-size: 12px;
            color: #999;
        }

        /* Tampilan HP */
        @media (max-width: 576px) {
            .login-box {
                width: calc(100% - 30px);
                padding: 30px 25px;
            }
        }
    </style>
</head>

<body>

    <div class="login-box">

        <!-- Logo dan judul -->
        <div class="text-center">

            <img
                src="../../img/logo-adiwiyata.png"
                class="login-logo"
                alt="Logo Adiwiyata">

            <h1 class="h4 login-title">
                SI Inventory Barang Adiwiyata
            </h1>

            <p class="login-subtitle">
                Silakan masuk untuk mengakses sistem
            </p>

        </div>

        <!-- Pesan error -->
        <?php if (!empty($pesan_error)) : ?>

            <div class="alert alert-danger text-center">
                <?= $pesan_error; ?>
            </div>

        <?php endif; ?>

        <!-- Form login -->
        <form method="POST" action="">

            <!-- CSRF Token -->
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

            <!-- Username -->
            <div class="form-group">

                <label for="username">
                    Username
                </label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    class="form-control"
                    placeholder="Masukkan username"
                    required
                    autofocus>

            </div>

            <!-- Password -->
            <div class="form-group mb-3">

                <label for="password">
                    Password
                </label>

                <div class="password-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required>

                    <button
                        type="button"
                        id="btnTogglePassword"
                        class="toggle-password-btn"
                        aria-label="Tampilkan atau sembunyikan password"
                        title="Tampilkan password">
                        <i id="iconTogglePassword" class="bi bi-eye"></i>
                    </button>
                </div>

            </div>

            <!-- Remember me -->
            <div class="form-check mb-3">

                <input
                    type="checkbox"
                    class="form-check-input"
                    id="remember"
                    name="remember"
                    value="1">

                <label
                    class="form-check-label remember-me"
                    for="remember">

                    Remember me

                </label>

            </div>

            <!-- Tombol login -->
            <button
                type="submit"
                name="login"
                class="btn-login">

                Masuk

            </button>

        </form>

        <!-- Footer -->
        <div class="footer">
            &copy; 2026-<?= date("Y") ?>
            SI Inventory Barang Adiwiyata
        </div>

    </div>

    <!-- Script Toggle Password Visibility -->
    <script>
        (function() {
            var btnToggle = document.getElementById('btnTogglePassword');
            var inputPass = document.getElementById('password');
            var iconToggle = document.getElementById('iconTogglePassword');

            if (btnToggle && inputPass && iconToggle) {
                btnToggle.addEventListener('click', function() {
                    var isPassword = inputPass.getAttribute('type') === 'password';
                    if (isPassword) {
                        inputPass.setAttribute('type', 'text');
                        iconToggle.className = 'bi bi-eye-slash';
                        btnToggle.setAttribute('title', 'Sembunyikan password');
                        btnToggle.setAttribute('aria-label', 'Sembunyikan password');
                    } else {
                        inputPass.setAttribute('type', 'password');
                        iconToggle.className = 'bi bi-eye';
                        btnToggle.setAttribute('title', 'Tampilkan password');
                        btnToggle.setAttribute('aria-label', 'Tampilkan password');
                    }
                });
            }
        })();
    </script>

</body>

</html>

