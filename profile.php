<?php 
ob_start();
session_start();

if(!isset($_SESSION['login'])){
  header('Location: ../../auth/login.php?pesan=belum_login');
}
else if($_SESSION['role'] != 'Admin'){
  header('Location: ../../auth/login.php?pesan=tolak_akses');
}

$judul = 'Profile';

include('../layout/header.php');
require_once('../../config.php');

$id = $_SESSION['id'];
$result = mysqli_query($connection, "SELECT pegawai.*, users.id_pegawai, users.username, users.status, users.role FROM users JOIN pegawai ON pegawai.id = users.id_pegawai WHERE pegawai.id = '$id'");
// $pegawai = mysqli_fetch_array($result);
// echo "<pre>";
// print_r($pegawai);
// echo "</pre>";

while($pegawai = mysqli_fetch_array($result)){
  $foto = $pegawai['foto'];
  $nama = $pegawai['nama'];
  $jenis_kelamin = $pegawai['jenis_kelamin'];
  $alamat = $pegawai['alamat'];
  $no_handphone = $pegawai['no_handphone'];
  $jabatan = $pegawai['jabatan'];
  $username = $pegawai['username'];
  $role = $pegawai['role'];
  $lokasi_presensi = $pegawai['lokasi_presensi'];
  $status = $pegawai['status'];
}
?>

<!-- Page body -->
<div class="page-body">
  <div class="container-xl">

    <div class="row justify-content-md-center">
      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <center>
              <img style="border-radius: 100%; width: 50%" src="<?= base_url('assets/img/foto_pegawai/'.$foto) ?>" alt="">
            </center>
            <table class="table mt-3">
              <tr>
                <td>Nama</td>
                <td>: <?= $nama; ?></td>
              </tr>
              <tr>
                <td>Jenis Kelamin</td>
                <td>: <?= $jenis_kelamin; ?></td>
              </tr>
              <tr>
                <td>Alamat</td>
                <td>: <?= $alamat; ?></td>
              </tr>
              <tr>
                <td>No handphone</td>
                <td>: <?= $no_handphone; ?></td>
              </tr>
              <tr>
                <td>Jabatan</td>
                <td>: <?= $jabatan; ?></td>
              </tr>
              <tr>
                <td>Username</td>
                <td>: <?= $username; ?></td>
              </tr>
              <tr>
                <td>Role</td>
                <td>: <?= $role; ?></td>
              </tr>
              <tr>
                <td>Lokasi Presensi</td>
                <td>: <?= $lokasi_presensi; ?></td>
              </tr>
              <tr>
                <td>Status</td>
                <td>: <?= $status; ?></td>
              </tr>

            </table>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<?php include('../layout/footer.php'); ?>