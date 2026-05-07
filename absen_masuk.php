<?php
include '../koneksi.php';

// waktu sekarang
$jam_sekarang = date('H:i:s');

// ambil pegawai
$pegawai = mysqli_fetch_assoc(mysqli_query($koneksi, "
SELECT * FROM pegawai WHERE id='$pegawai_id'
"));

// ambil jam kerja
$jamKerja = mysqli_fetch_assoc(mysqli_query($koneksi, "
SELECT * FROM jam_kerja 
WHERE jabatan_id='{$pegawai['jabatan_id']}'
"));

$batas_telat = $jamKerja['batas_telat'];

// ❌ validasi: sudah absen?
$cek = mysqli_query($koneksi, "
SELECT * FROM presensi 
WHERE pegawai_id='$pegawai_id' 
AND tanggal=CURDATE()
");

if(mysqli_num_rows($cek) > 0){
    die("Sudah absen hari ini!");
}

// ✅ cek telat
if (strtotime($jam_sekarang) > strtotime($batas_telat)) {
    $status_masuk = "Terlambat";
} else {
    $status_masuk = "Tepat Waktu";
}

// simpan
mysqli_query($koneksi, "
INSERT INTO presensi (pegawai_id, tanggal, jam_masuk, status_masuk)
VALUES ('$pegawai_id', CURDATE(), '$jam_sekarang', '$status_masuk')
");

echo "Absen masuk berhasil!";
?>