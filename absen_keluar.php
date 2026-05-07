<?php
include '../koneksi.php';

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

$jam_pulang = $jamKerja['jam_pulang'];

// ❌ validasi: belum absen masuk
$cek = mysqli_query($koneksi, "
SELECT * FROM presensi 
WHERE pegawai_id='$pegawai_id' 
AND tanggal=CURDATE()
");

if(mysqli_num_rows($cek) == 0){
    die("Belum absen masuk!");
}

// ✅ cek pulang cepat
if (strtotime($jam_sekarang) < strtotime($jam_pulang)) {
    $status_keluar = "Pulang Cepat";
} else {
    $status_keluar = "Sesuai Waktu";
}

// update
mysqli_query($koneksi, "
UPDATE presensi 
SET jam_keluar='$jam_sekarang',
    status_keluar='$status_keluar'
WHERE pegawai_id='$pegawai_id'
AND tanggal=CURDATE()
");

echo "Absen keluar berhasil!";
?>