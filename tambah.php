<?php 
session_start();
ob_start();

if(!isset($_SESSION['login'])){
  header('Location: ../../auth/login.php?pesan=belum_login');
}
else if($_SESSION['role'] != 'Admin'){
  header('Location: ../../auth/login.php?pesan=tolak_akses');
}

$judul = 'Tambah Pegawai';

include('../layout/header.php');
require_once('../../config.php');

// Ambil data jabatan
$ambil_jabatan = mysqli_query($connection, "SELECT * FROM jabatan ORDER BY jabatan ASC");
// $jabatan = mysqli_fetch_assoc($ambil_jabatan);
// echo "<pre>";
// print_r($jabatan);
// echo "</pre>";

// Ambil data lokasi presensi
$ambil_lok_presensi = mysqli_query($connection, "SELECT * FROM lokasi_presensi ORDER BY nama_lokasi ASC");


if(isset($_POST['submit'])){

  // Ambil NIP
  $ambil_nip = mysqli_query($connection, "SELECT nip FROM pegawai ORDER BY nip DESC LIMIT 1");
  if(mysqli_num_rows($ambil_nip) > 0){
    $row = mysqli_fetch_assoc($ambil_nip);
    $nip_db = $row['nip'];
    $nip_db = explode('-', $nip_db);
    $no_baru = (int)$nip_db[1] + 1;
    $nip_baru = 'PEG-' . str_pad($no_baru, 4, 0, STR_PAD_LEFT);
  }
  else{
    $nip_baru = "PEG-0001";
  }

  $nip = $nip_baru;
  $nama = htmlspecialchars($_POST['nama']);
  $jenis_kelamin = htmlspecialchars($_POST['jenis_kelamin']);
  $alamat = htmlspecialchars($_POST['alamat']);
  $no_handphone = htmlspecialchars($_POST['no_handphone']);
  $jabatan = htmlspecialchars($_POST['jabatan']);
  $username = htmlspecialchars($_POST['username']);

  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $role = htmlspecialchars($_POST['role']);
  $status = htmlspecialchars($_POST['status']);
  $lokasi_presensi = htmlspecialchars($_POST['lokasi_presensi']);

  // echo $password;

  if(isset($_FILES['foto'])){
    $file = $_FILES['foto'];
    $nama_file = $file['name'];
    $file_tmp = $file['tmp_name'];
    $ukuran_file = $file['size'];
    $file_dir = '../../assets/img/foto_pegawai/'.$nama_file;

    $ambil_ext = pathinfo($nama_file, PATHINFO_EXTENSION);
    $ext_diizinkan = ['jpg', 'png', 'jpeg'];
    $max_size = 10 * 1024 * 1024;

    move_uploaded_file($file_tmp, $file_dir);
  }

  if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(empty($nip)){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Nama lokasi wajib diisi!";
    }
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
    if(empty($password)){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Password wajib diisi!";
    }
    if(empty($role)){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Role wajib diisi!";
    }
    if(empty($lokasi_presensi)){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Lokasi Presensi wajib diisi!";
    }
    if($_POST['password'] == ''){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Password wajib diisi!";
    }
    if($_POST['ulangi_password'] == ''){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Ulangi password wajib diisi!";
    }

    if($_POST['password'] != $_POST['ulangi_password']){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Password tidak sama!";
    }

    if(!in_array(strtolower($ambil_ext), $ext_diizinkan)){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Hanya file jpg, jpeg, dan png yang diizinkan!";
    }

    if($ukuran_file > $max_size){
      $pesan_kesalahan[] = "<i class='fa-solid fa-check'></i> Ukuran file melebihi 10 MB!";
    }



    if(!empty($pesan_kesalahan)){
      $_SESSION['validasi'] = implode('<br>',$pesan_kesalahan);
    }
    else{ 
      $pegawai = mysqli_query($connection, "INSERT INTO pegawai(nip, nama, jenis_kelamin, alamat, no_handphone, jabatan, lokasi_presensi, foto) VALUES('$nip', '$nama', '$jenis_kelamin', '$alamat', '$no_handphone', '$jabatan', '$lokasi_presensi', '$nama_file')");

      $id_pegawai = mysqli_insert_id($connection);
      $user = mysqli_query($connection, "INSERT INTO users(id_pegawai, username, password, status, role) VALUES('$id_pegawai', '$username', '$password', '$status', '$role')");

      $_SESSION['berhasil'] = 'Data berhasil disimpan';
      header('Location: pegawai.php');
      exit;
    }
  }

}

?>

<!-- Page body -->
<div class="page-body">
  <div class="container-xl">
    
    <form action="<?= base_url('admin/data_pegawai/tambah.php') ?>" method="POST" enctype="multipart/form-data">

      <div class="row">
        <div class="col-md-6">
          <div class="card">
            <div class="card-body">
              <div class="mb-3">
                <label for="">Nama</label>
                <input type="text" class="form-control" name="nama" value="<?= isset($_POST['nama']) ? $_POST['nama'] : '' ?>">
              </div>
              <div class="mb-3">
                <label for="">Jenis Kelamin</label>
                <select name="jenis_kelamin" id="" class="form-control">
                  <option value="">-- Pilih Jenis Kelamin --</option>
                  <option <?= (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] == 'Laki-laki') ? 'selected' : '' ?> value="Laki-laki">Laki-laki</option>
                  <option <?= (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] == 'Perempuan') ? 'selected' : '' ?> value="Perempuan">Perempuan</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="">Alamat</label>
                <textarea name="alamat" id="" class="form-control"><?= isset($_POST['alamat']) ? $_POST['alamat'] : '' ?></textarea>
              </div>

              <div class="mb-3">
                <label for="">No Handphone</label>
                <input type="text" class="form-control" name="no_handphone" value="<?= isset($_POST['no_handphone']) ? $_POST['no_handphone'] : '' ?>">
              </div>

              <div class="mb-3">
                <label for="">Jabatan</label>
                <select name="jabatan" id="" class="form-control">
                  <option value="">-- Pilih Jabatan --</option>
                  <?php foreach($ambil_jabatan as $jabatan): ?>
                    <option value="<?= $jabatan['jabatan'] ?>" <?= (isset($_POST['jabatan']) && $_POST['jabatan'] == $jabatan['jabatan']) ? 'selected' : ''; ?>><?= $jabatan['jabatan']; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
      
              <div class="mb-3">
                <label for="">Status</label>
                <select name="status" id="" class="form-control">
                  <option value="">-- Pilih Status --</option>
                  <option <?= (isset($_POST['status']) && $_POST['status'] == 'Aktif') ? 'selected' : '' ?> value="Aktif">Aktif</option>
                  <option <?= (isset($_POST['status']) && $_POST['status'] == 'Tidak Aktif') ? 'selected' : '' ?> value="Tidak Aktif">Tidak Aktif</option>
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
                <input type="text" class="form-control" name="username" value="<?= isset($_POST['username']) ? $_POST['username'] : '' ?>">
              </div>
              <div class="mb-3">
                <label for="">Password</label>
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
                  <option <?= (isset($_POST['role']) && $_POST['role'] == 'Admin') ? 'selected' : '' ?> value="Admin">Admin</option>
                  <option <?= (isset($_POST['role']) && $_POST['role'] == 'Pegawai') ? 'selected' : '' ?> value="Pegawai">Pegawai</option>
                </select>
              </div>

              <div class="mb-3">
                <label for="">Lokasi Presensi</label>
                <select name="lokasi_presensi" id="" class="form-control">
                  <option value="">-- Pilih Lokasi Presensi --</option>
                  <?php foreach($ambil_lok_presensi as $lokasi_presensi): ?>
                    <option value="<?= $lokasi_presensi['nama_lokasi']; ?>" <?= (isset($_POST['lokasi_presensi']) && $_POST['lokasi_presensi'] == $lokasi_presensi['nama_lokasi']) ? 'selected' : ''; ?>><?= $lokasi_presensi['nama_lokasi']; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label for="">Foto</label>
                <input type="file" class="form-control" name="foto">
              </div>

              <button type="submit" class="btn btn-primary" name="submit">Simpan</button>
            </div>
          </div>
        </div>
      </div>

    </form>
  </div>
</div>


<?php include('../layout/footer.php'); ?>
