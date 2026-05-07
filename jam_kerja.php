<?php 
$judul = "Jam Kerja";
include '../layout/header.php'; 
include '../../koneksi.php';

$data = mysqli_query($connection, "
SELECT jam_kerja.*, jabatan.jabatan 
FROM jam_kerja 
JOIN jabatan ON jam_kerja.jabatan_id = jabatan.id
");

if(isset($_POST['simpan'])){

    $jabatan_id = $_POST['jabatan_id'];
    $jam_masuk = $_POST['jam_masuk'];
    $batas_telat = $_POST['batas_telat'];
    $jam_pulang = $_POST['jam_pulang'];

if(isset($_GET['hapus'])){
    $id = intval($_GET['hapus']); // 🔥 DI SINI

    mysqli_query($connection, "
    DELETE FROM jam_kerja WHERE id='$id'
    ");

    echo "<script>alert('Data berhasil dihapus');location='jam_kerja.php';</script>";
}

    // WAJIB ADA INI
    $cek = mysqli_query($connection, "
    SELECT * FROM jam_kerja WHERE jabatan_id='$jabatan_id'
    ");

    if(mysqli_num_rows($cek) > 0){
        echo "<script>alert('Jabatan sudah punya jam kerja!');</script>";
    } else {

        mysqli_query($connection, "
        INSERT INTO jam_kerja (jabatan_id, jam_masuk, batas_telat, jam_pulang)
        VALUES ('$jabatan_id','$jam_masuk','$batas_telat','$jam_pulang')
        ") or die(mysqli_error($connection));

        echo "<script>alert('Data berhasil disimpan');location='jam_kerja.php';</script>";
    }
}
?>

<div class="page-body">
<div class="container-xl">

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Data Jam Kerja</h3>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered">
      <tr>
        <th>No</th>
        <th>Jabatan</th>
        <th>Jam Masuk</th>
        <th>Batas Telat</th>
        <th>Jam Pulang</th>
        <th>Aksi</th>
      </tr>

      <?php 
      $no = 1;
      while($d = mysqli_fetch_array($data)){
      ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= $d['jabatan'] ?></td>
        <td><?= $d['jam_masuk'] ?></td>
        <td><?= $d['batas_telat'] ?></td>
        <td><?= $d['jam_pulang'] ?></td>
        <td>
          <a href="edit_jam_kerja.php?id=<?= $d['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
          
          <a href="jam_kerja.php?hapus=<?= $d['id'] ?>" 
            class="btn btn-danger btn-sm"
            onclick="return confirm('Yakin mau hapus data ini?')">
            Hapus
          </a>
        </td>
      </tr>
      <?php } ?>
    </table>
  </div>
</div>

<div class="card mt-4">
  <div class="card-header">
    <h3 class="card-title">Tambah Jam Kerja</h3>
  </div>
  <div class="card-body">

<form method="POST">

<label>Jabatan</label>
<select name="jabatan_id" class="form-control">
<?php
$jabatan = mysqli_query($koneksi, "SELECT * FROM jabatan");
while($j = mysqli_fetch_assoc($jabatan)){
?>
<option value="<?= $j['id'] ?>">
    <?= $j['jabatan'] ?>
</option>
<?php } ?>
</select>

<br>

<label>Jam Masuk</label>
<input type="time" name="jam_masuk" class="form-control">

<br>

<label>Batas Telat</label>
<input type="time" name="batas_telat" class="form-control">

<br>

<label>Jam Pulang</label>
<input type="time" name="jam_pulang" class="form-control">

<br>

<button type="submit" name="simpan" class="btn btn-primary">Simpan</button>

</form>

</div>
</div>

</div>
</div>