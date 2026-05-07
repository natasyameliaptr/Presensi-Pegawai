<?php 
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../config.php');

// Kalau sudah login, redirect
if(isset($_SESSION['login'])){
  if($_SESSION['role'] === 'Admin'){
    header('Location: ../admin/home/home.php');
  } else {
    header('Location: ../pegawai/home/home.php');
  }
  exit;
}

if(isset($_POST['login'])){
  $username = mysqli_real_escape_string($connection, $_POST['username']);
  $password = $_POST['password'];

  // Prepared statement (AMAN)
  $stmt = mysqli_prepare($connection, "
    SELECT users.*, pegawai.* 
    FROM users 
    JOIN pegawai ON users.id_pegawai = pegawai.id 
    WHERE username = ?
  ");

  if(!$stmt){
    die("Prepare Error: " . mysqli_error($connection));
  }

  mysqli_stmt_bind_param($stmt, "s", $username);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  if(mysqli_num_rows($result) === 1){
    $row = mysqli_fetch_assoc($result);

    // ✅ FIX DI SINI (bukan iif)
    if(password_verify($password, $row['password'])){
      
      if($row['status'] == 'Aktif'){
        $_SESSION['login'] = true;
        $_SESSION['id'] = $row['id'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['nama'] = $row['nama'];
        $_SESSION['nip'] = $row['nip'];
        $_SESSION['jabatan'] = $row['jabatan'];
        $_SESSION['lokasi_presensi'] = $row['lokasi_presensi'];
        $_SESSION['foto'] = $row['foto'];

        if($row['role'] === 'Admin'){
          header('Location: ../admin/home/home.php');
        } else {
          header('Location: ../pegawai/home/home.php');
        }
        exit;

      } else {
        $_SESSION['gagal'] = 'Akun anda belum aktif!';
      }

    } else {
      $_SESSION['gagal'] = 'Password salah!';
    }

  } else {
    $_SESSION['gagal'] = 'Username tidak ditemukan!';
  }
}
?>

<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Login | ABSENSI ONLINE</title>

    <!-- CSS -->
    <link href="<?= base_url() ?>/assets/css/tabler.min.css" rel="stylesheet"/>
    <link href="<?= base_url() ?>/assets/css/tabler-vendors.min.css" rel="stylesheet"/>
    <link href="<?= base_url() ?>/assets/css/demo.min.css" rel="stylesheet"/>

    <style>
      @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
      body {
        background-color: #e0f2ff;
        font-family: 'Inter', sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        margin: 0;
      }

      .login-card {
        background: #ffffff;
        border-radius: 18px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        color: #333;
        padding: 3rem 4rem;
        width: 100%;
        max-width: 600px;
        animation: fadeIn 0.8s ease;
      }

      /* Animasi untuk logo */
      @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        100% { transform: translateY(0px); }
      }

      @keyframes fadeLogo {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
      }

      .brand-logo {
        display: block;
        margin: 0 auto 0.75rem auto;
        width: 180px;
        height: auto;
        animation: fadeLogo 1s ease-out, float 4s ease-in-out infinite;
      }

      .brand-logo:hover {
        transform: scale(1.05);
        transition: transform 0.3s ease;
      }

      .brand-text {
        text-align: center;
        font-size: 2rem;
        font-weight: 700;
        color: #006a4e;
        letter-spacing: 1px;
        margin-top: 0.5rem;
        margin-bottom: 1.5rem;
      }

      .form-control {
        background: #f8f9fa;
        border: 1px solid #ddd;
        color: #333;
        padding: 0.75rem;
        font-size: 1rem;
        border-radius: 10px;
      }

      .form-control:focus {
        border-color: #00b37a;
        box-shadow: 0 0 0 3px rgba(0,179,122,0.2);
      }

      .btn-primary {
        background: #00b37a;
        border: none;
        transition: all 0.3s ease;
        font-weight: 600;
        padding: 0.75rem;
        border-radius: 10px;
      }

      .btn-primary:hover {
        background: #009c68;
        transform: translateY(-1px);
      }

      .show-pass {
        cursor: pointer;
        color: #00b37a;
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0.7;
      }

      @keyframes fadeIn {
        from {opacity: 0; transform: translateY(30px);}
        to {opacity: 1; transform: translateY(0);}
      }

      .form-label {
        font-weight: 500;
        color: #444;
      }
    </style>
  </head>
  <body>
    <div class="login-card">
      <!-- Logo dengan animasi -->
      <img src="<?= base_url() ?>/assets/img/logo.png" alt="Logo" class="brand-logo">
      <div class="brand-text">PRESENSI PEGAWAI</div>

      <h2 class="text-center mb-4">Silahkan Masuk ke Akun Anda</h2>

      <form action="" method="post" autocomplete="off">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" class="form-control" name="username" placeholder="Masukkan username" required autofocus>
        </div>

        <div class="mb-3 position-relative">
          <label class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
          <span class="show-pass" onclick="togglePassword()">👁</span>
        </div>

        <button type="submit" class="btn btn-primary w-100" name="login">Masuk</button>
      </form>
    </div>

    <!-- Sweet Alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
      function togglePassword(){
        const input = document.getElementById('password');
        input.type = input.type === 'password' ? 'text' : 'password';
      }

      <?php if(isset($_SESSION['gagal'])): ?>
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: '<?= $_SESSION['gagal'] ?>',
          confirmButtonColor: '#00b37a'
        });
        <?php unset($_SESSION['gagal']); ?>
      <?php endif; ?>
    </script>
  </body>
</html>
