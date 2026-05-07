<?php 
ob_start();
session_start();

if(!isset($_SESSION['login'])){
  header('Location: ../../auth/login.php?pesan=belum_login');
}
else if($_SESSION['role'] != 'Admin'){
  header('Location: ../../auth/login.php?pesan=tolak_akses');
}

$judul = 'Edit Pegawai';

include('../layout/header.php');
require_once('../../config.php');

$id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
$result = mysqli_query($connection, "SELECT users.id_pegawai, users.username, users.password, users.status, users.role, pegawai.* FROM users JOIN pegawai ON pegawai.id = users.id_pegawai WHERE pegawai.id = '$id'");

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

// Ambil data jabatan
$ambil_jabatan = mysqli_query($connection, "SELECT * FROM jabatan ORDER BY jabatan ASC");
// $jabatan = mysqli_fetch_assoc($ambil_jabatan);
// echo "<pre>";
// print_r($jabatan);
// echo "</pre>";

// Ambil data lokasi presensi
$ambil_lok_presensi = mysqli_query($connection, "SELECT * FROM lokasi_presensi ORDER BY nama_lokasi ASC");


if(isset($_POST['edit'])){
  $id = $_POST['id'];
  $nama = htmlspecialchars($_POST['nama']);
  $jenis_kelamin = htmlspecialchars($_POST['jenis_kelamin']);
  $alamat = htmlspecialchars($_POST['alamat']);
  $no_handphone = htmlspecialchars($_POST['no_handphone']);
  $jabatan = htmlspecialchars($_POST['jabatan']);
  $username = htmlspecialchars($_POST['username']);
  // $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $role = htmlspecialchars($_POST['role']);
  $status = htmlspecialchars($_POST['status']);
  $lokasi_presensi = htmlspecialchars($_POST['lokasi_presensi']);

  if(empty($_POST['password'])){
    $password = $_POST['password_lama'];
  }
  else{
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  }

  if($_FILES['foto_baru']['error'] === 4){
    $nama_file = $_POST['foto_lama'];
  }
  else{
    if(isset($_FILES['foto_baru'])){
      $file = $_FILES['foto_baru'];
      $nama_file = $file['name'];
      $file_tmp = $file['tmp_name'];
      $ukuran_file = $file['size'];
      $file_dir = '../../assets/img/foto_pegawai/'.$nama_file;
  
      $ambil_ext = pathinfo($nama_file, PATHINFO_EXTENSION);
      $ext_diizinkan = ['jpg', 'png', 'jpeg'];
      $max_size = 10 * 1024 * 1024;
  
      move_uploaded_file($file_tmp, $file_dir);
    }
  }

  if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(empty($nama)){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Nama wajib diisi!";
    }
    if(empty($jenis_kelamin)){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Jenis kelamin lokasi wajib diisi!";
    }
    if(empty($alamat)){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Alamat wajib diisi!";
    }
    if(empty($no_handphone)){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> No handphone wajib diisi!";
    }
    if(empty($jabatan)){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Jabatan wajib diisi!";
    }
    if(empty($status)){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Status wajib diisi!";
    }
    if(empty($username)){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Username wajib diisi!";
    }
    if(empty($role)){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Role wajib diisi!";
    }
    if(empty($lokasi_presensi)){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Lokasi Presensi wajib diisi!";
    }

    if($_POST['password'] != $_POST['ulangi_password']){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Password tidak sama!";
    }

    if($_FILES['foto_baru']['error'] !== 4){
      if(!in_array(strtolower($ambil_ext), $ext_diizinkan)){
        $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Hanya file jpg, jpeg, dan png yang diizinkan!";
      }
  
      if($ukuran_file > $max_size){
        $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Ukuran file melebihi 10 MB!";
      }
    }

    if(!empty($pesan_kesalahan)){
      $_SESSION['validasi'] = implode('<br>',$pesan_kesalahan);
    }
    else{ 
      $pegawai = mysqli_query($connection, "UPDATE pegawai SET
        nama = '$nama',
        jenis_kelamin = '$jenis_kelamin',
        alamat = '$alamat',
        no_handphone = '$no_handphone',
        jabatan = '$jabatan',
        lokasi_presensi = '$lokasi_presensi',
        foto = '$nama_file'
      WHERE id = '$id'");

      $user = mysqli_query($connection, "UPDATE users SET
        username = '$username',
        password = '$password',
        status = '$status',
        role = '$role'
      WHERE id = '$id'");

      $_SESSION['berhasil'] = 'Data berhasil diupdate';
      header('Location: pegawai.php');
      exit;
    }
  }

}

?>

<!-- Page body -->
<div class="page-body">
  <div class="container-xl">
    
    <form action="<?= base_url('admin/data_pegawai/edit.php') ?>" method="POST" enctype="multipart/form-data">

      <div class="row">
        <div class="col-md-6">
          <div class="card">
            <div class="card-body">
              <div class="mb-3">
                <input type="hidden" class="form-control" name="id" value="<?= $id ?>">
                <label for="">Nama</label>
                <input type="text" class="form-control" name="nama" value="<?= $nama ?>">
              </div>
              <div class="mb-3">
                <label for="">Jenis Kelamin</label>
                <select name="jenis_kelamin" id="" class="form-control">
                  <option value="">-- Pilih Jenis Kelamin --</option>
                  <option <?= ($jenis_kelamin == 'Laki-laki') ? 'selected' : '' ?> value="Laki-laki">Laki-laki</option>
                  <option <?= ($jenis_kelamin == 'Perempuan') ? 'selected' : '' ?> value="Perempuan">Perempuan</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="">Alamat</label>
                <textarea name="alamat" id="" class="form-control"><?= $alamat ?></textarea>
              </div>

              <div class="mb-3">
                <label for="">No Handphone</label>
                <input type="text" class="form-control" name="no_handphone" value="<?= $no_handphone ?>">
              </div>

              <div class="mb-3">
                <label for="">Jabatan</label>
                <select name="jabatan" id="" class="form-control">
                  <option value="">-- Pilih Jabatan --</option>
                  <?php foreach($ambil_jabatan as $data): ?>
                    <option value="<?= $data['jabatan'] ?>" <?= ($jabatan == $data['jabatan']) ? 'selected' : ''; ?>>
                      <?= $data['jabatan']; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
      
              <div class="mb-3">
                <label for="">Status</label>
                <select name="status" id="" class="form-control">
                  <option value="">-- Pilih Status --</option>
                  <option <?= ($status == 'Aktif') ? 'selected' : '' ?> value="Aktif">Aktif</option>
                  <option <?= ($status == 'Tidak Aktif') ? 'selected' : '' ?> value="Tidak Aktif">Tidak Aktif</option>
                </select>
              </div>

            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card">
            <div class="card-body">
              <div class="mb-3">
                <label for="">Username</label>
                <input type="text" class="form-control" name="username" value="<?= $username ?>">
              </div>
              <div class="mb-3">
                <label for="">Password</label>
                <input type="hidden" name="password_lama" id="" value="<?= $password ?>">
                <input type="password" class="form-control" name="password">
              </div>
              <div class="mb-3">
                <label for="">Ulangi Password</label>
                <input type="password" class="form-control" name="ulangi_password">
              </div>

              <div class="mb-3">
                <label for="">Role</label>
                <select name="role" id="" class="form-control">
                  <option value="">-- Pilih Role --</option>
                  <option <?= ($role == 'Admin') ? 'selected' : '' ?> value="Admin">Admin</option>
                  <option <?= ($role == 'Pegawai') ? 'selected' : '' ?> value="Pegawai">Pegawai</option>
                </select>
              </div>

              <div class="mb-3">
                <label for="">Lokasi Presensi</label>
                <select name="lokasi_presensi" id="" class="form-control">
                  <option value="">-- Pilih Lokasi Presensi --</option>
                  <?php foreach($ambil_lok_presensi as $data): ?>
                    <option value="<?= $data['nama_lokasi']; ?>" <?= ($lokasi_presensi == $data['nama_lokasi']) ? 'selected' : ''; ?>>
                      <?= $data['nama_lokasi']; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label for="">Foto</label>
                <input type="hidden" value="<?= $foto ?>" name="foto_lama">
                <input type="file" class="form-control" name="foto_baru">
              </div>

              <button type="submit" class="btn btn-primary" name="edit">Update</button>
            </div>
          </div>
        </div>
      </div>

    </form>

  </div>
</div>


<?php include('../layout/footer.php'); ?>
