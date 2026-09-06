<?php

include_once '../../app/app.php';

// Jika sudah login, langsung arahkan ke halaman utama sesuai role
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header('Location: ' . get_landing_url('..'));
    exit;
}

$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = login($_POST);
    if ($res === 1) {
        header('Location: ' . get_landing_url('..'));
        exit;
    } elseif ($res === -1) {
        $pesan_error = 'Maaf, Login Gagal, Password anda tidak sesuai!';
    } else {
        $pesan_error = 'Maaf, Login Gagal, Username anda tidak terdaftar!';
    }
}

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="generator" content="Hugo 0.101.0">
    <title>Login Adiwiyata</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/4.6/examples/floating-labels/">

    <!-- Bootstrap core CSS -->
    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }
    </style>

    <!-- Custom styles for this template -->
    <link href="assets/dist/css/floating-labels.css" rel="stylesheet">
  </head>
  <body>
    
<form class="form-signin" method="POST" action="">
  <div class="text-center mb-4">
    <img class="mb-4" src="../../img/logo-adiwiyata.png" alt="" width="72" height="72">
    <h1 class="h3 mb-3 font-weight-normal">Login Adiwiyata</h1>
    <p>Masukkan Username dan Password anda dengan benar!</p>
  </div>

  <?php if (!empty($pesan_error)) : ?>
    <div class="alert alert-danger text-center mb-3" role="alert">
      <?= $pesan_error; ?>
    </div>
  <?php endif; ?>

  <div class="form-label-group">
    <input type="text" class="form-control" name="username" placeholder="Masukkan Username Anda!" required autofocus>
    <label>Masukkan Username Anda!</label>
  </div>

  <div class="form-label-group">
    <input type="password" name="password" class="form-control" placeholder="Masukkan Password Anda!" required>
    <label>Masukkan Password Anda!</label>
  </div>

  <div class="checkbox mb-3">
    <label>
      <input type="checkbox" value="remember-me"> Remember me
    </label>
  </div>
  <button class="btn btn-lg btn-primary btn-block" type="submit" name="login">Sign in</button>
  <p class="mt-5 mb-3 text-muted text-center">&copy; Login Adiwiyata 2026-<?= date("Y") ?></p>
</form>

</body>
</html>
