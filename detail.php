<?php 
session_start();

if(!isset($_SESSION['login'])){
  header('Location: ../../auth/login.php?pesan=belum_login');
}
else if($_SESSION['role'] != 'Admin'){
  header('Location: ../../auth/login.php?pesan=tolak_akses');
}

$judul = 'Detail Pegawai';

include('../layout/header.php');
require_once('../../config.php');

$id = $_GET['id'];
$result = mysqli_query($connection, "SELECT users.id_pegawai, users.username, users.password, users.status, users.role, pegawai.* FROM users JOIN pegawai ON pegawai.id = users.id_pegawai WHERE pegawai.id = $id");

while($pegawai = mysqli_fetch_array($result)){
  $nama = $pegawai['nama'];
  $jenis_kelamin = $pegawai['jenis_kelamin'];
  $alamat = $pegawai['alamat'];
  $no_handphone = $pegawai['no_handphone'];
  $jabatan = $pegawai['jabatan'];
  $username = $pegawai['username'];
  $password = $pegawai['password'];
  $status = $pegawai['status'];
  $lokasi_presensi = $pegawai['lokasi_presensi'];
  $role = $pegawai['role'];
  $foto = $pegawai['foto'];
}
?>

<!-- Page body -->
<div class="page-body">
  <div class="container-xl">

  <div class="row">
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <table class="table">
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
              <td>No Jabatan</td>
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

    <div class="col-md-6">
      <img style="width:350px; border-radius:15px" src="<?= base_url('assets/img/foto_pegawai/'.$foto) ?>" alt="">
      
    </div>
  </div>
    

  </div>
</div>

<?php include('../layout/footer.php'); ?>