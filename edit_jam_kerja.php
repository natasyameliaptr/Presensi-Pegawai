<?php
include '../layout/header.php';
include '../../koneksi.php';

$id = $_GET['id'];

$data = mysqli_query($connection, "
SELECT * FROM jam_kerja WHERE id='$id'
");

$d = mysqli_fetch_assoc($data);

// ambil data jabatan
$jabatan = mysqli_query($connection, "SELECT * FROM jabatan");

if(isset($_POST['update'])){
    $jabatan_id = $_POST['jabatan_id'];
    $jam_masuk = $_POST['jam_masuk'];
    $batas_telat = $_POST['batas_telat'];
    $jam_pulang = $_POST['jam_pulang'];

    mysqli_query($connection, "
    UPDATE jam_kerja SET
        jabatan_id='$jabatan_id',
        jam_masuk='$jam_masuk',
        batas_telat='$batas_telat',
        jam_pulang='$jam_pulang'
    WHERE id='$id'
    ");

    echo "<script>alert('Data berhasil diupdate');location='jam_kerja.php';</script>";
}
?>

<div class="container">
<h3>Edit Jam Kerja</h3>

<form method="POST">

<label>Jabatan</label>
<select name="jabatan_id" class="form-control">
<?php while($j = mysqli_fetch_assoc($jabatan)){ ?>
<option value="<?= $j['id'] ?>" <?= $j['id']==$d['jabatan_id'] ? 'selected' : '' ?>>
    <?= $j['jabatan'] ?>
</option>
<?php } ?>
</select>

<br>

<label>Jam Masuk</label>
<input type="time" name="jam_masuk" value="<?= $d['jam_masuk'] ?>" class="form-control">

<br>

<label>Batas Telat</label>
<input type="time" name="batas_telat" value="<?= $d['batas_telat'] ?>" class="form-control">

<br>

<label>Jam Pulang</label>
<input type="time" name="jam_pulang" value="<?= $d['jam_pulang'] ?>" class="form-control">

<br>

<button type="submit" name="update" class="btn btn-primary">Update</button>

</form>
</div>