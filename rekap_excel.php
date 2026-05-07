<?php 
ob_start();
session_start();
if(!isset($_SESSION['login'])){
  header('Location: ../../auth/login.php?pesan=belum_login');
  exit;
}
else if($_SESSION['role'] != 'Pegawai'){
  header('Location: ../../auth/login.php?pesan=tolak_akses');
  exit;
}

include_once('../../config.php');
require('../../assets/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

$id = $_SESSION['id'];
$tanggal_dari = $_POST['tanggal_dari'];
$tanggal_sampai = $_POST['tanggal_sampai'];

$result = mysqli_query($connection, "SELECT * FROM presensi WHERE id_pegawai = '$id' AND tanggal_masuk BETWEEN '$tanggal_dari' AND '$tanggal_sampai' ORDER BY tanggal_masuk DESC");

$lokasi_presensi = $_SESSION['lokasi_presensi'];
$lokasi = mysqli_query($connection, "SELECT * FROM lokasi_presensi WHERE nama_lokasi = '$lokasi_presensi'");
while($lokasi_result = mysqli_fetch_array($lokasi)){
  $jam_masuk_kantor = date('H:i:s', strtotime($lokasi_result['jam_masuk']));
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header
$sheet->setCellValue('A1', 'REKAP PRESENSI');
$sheet->mergeCells('A1:I1');
$sheet->setCellValue('A2', 'Tanggal Awal');
$sheet->setCellValue('A3', 'Tanggal Akhir');
$sheet->setCellValue('C2', $tanggal_dari);
$sheet->setCellValue('C3', $tanggal_sampai);

// Kolom Judul
$sheet->setCellValue('A5', 'NO');
$sheet->setCellValue('B5', 'TANGGAL MASUK');
$sheet->setCellValue('C5', 'JAM MASUK');
$sheet->setCellValue('D5', 'TANGGAL KELUAR');
$sheet->setCellValue('E5', 'JAM KELUAR');
$sheet->setCellValue('F5', 'TOTAL JAM KERJA');
$sheet->setCellValue('G5', 'TOTAL JAM TERLAMBAT');
$sheet->setCellValue('H5', 'FOTO MASUK');
$sheet->setCellValue('I5', 'FOTO KELUAR');

// Mengatur lebar kolom agar gambar muat
foreach (range('A', 'I') as $columnID) {
    $sheet->getColumnDimension($columnID)->setWidth(20);
}

$no = 1;
$row = 6;

while($data = mysqli_fetch_array($result)){
  // Menghitung total jam kerja
  $jam_tanggal_masuk = date('Y-m-d H:i:s', strtotime($data['tanggal_masuk'].' '.$data['jam_masuk']));
  $jam_tanggal_keluar = date('Y-m-d H:i:s', strtotime($data['tanggal_masuk'].' '.$data['jam_keluar']));

  $timestamp_masuk = strtotime($jam_tanggal_masuk);
  $timestamp_keluar = strtotime($jam_tanggal_keluar);
  $selisih = $timestamp_keluar - $timestamp_masuk;
  $total_jam = floor($selisih / 3600);
  $selisih -= $total_jam * 3600;
  $selisih_menit = floor($selisih / 60);

  // Menghitung keterlambatan
  $jam_masuk = date('H:i:s', strtotime($data['jam_masuk']));
  $timestamp_jam_masuk_pegawai = strtotime($jam_masuk);
  $timestamp_jam_masuk_kantor = strtotime($jam_masuk_kantor);
  $terlambat = $timestamp_jam_masuk_pegawai - $timestamp_jam_masuk_kantor;
  $total_jam_terlambat = floor($terlambat / 3600);
  $terlambat -= $total_jam_terlambat * 3600;
  $selisih_menit_terlambat = floor($terlambat / 60);

  // Isi data text
  $sheet->setCellValue('A'.$row, $no);
  $sheet->setCellValue('B'.$row, $data['tanggal_masuk']);
  $sheet->setCellValue('C'.$row, $data['jam_masuk']);
  $sheet->setCellValue('D'.$row, $data['tanggal_keluar']);
  $sheet->setCellValue('E'.$row, $data['jam_keluar']);

  if($data['tanggal_keluar'] == '0000-00-00'){
    $sheet->setCellValue('F'.$row, '0 jam 0 menit');
  } else {
    $sheet->setCellValue('F'.$row, $total_jam.' jam '.$selisih_menit.' menit');
  }

  if($total_jam_terlambat < 0){
    $sheet->setCellValue('G'.$row, 'On Time');
  } else {
    $sheet->setCellValue('G'.$row, $total_jam_terlambat.' jam '.$selisih_menit_terlambat.' menit');
  }

  // FOTO MASUK
  $foto_masuk = '../../pegawai/presensi/foto/'.$data['foto_masuk'];
  if (!empty($data['foto_masuk']) && file_exists($foto_masuk)) {
      $drawingMasuk = new Drawing();
      $drawingMasuk->setPath($foto_masuk);
      $drawingMasuk->setHeight(80); // tinggi gambar
      $drawingMasuk->setCoordinates('H'.$row);
      $drawingMasuk->setWorksheet($sheet);
  } else {
      $sheet->setCellValue('H'.$row, 'Tidak ada');
  }

  // FOTO KELUAR
  $foto_keluar = '../../pegawai/presensi/foto/'.$data['foto_keluar'];
  if (!empty($data['foto_keluar']) && file_exists($foto_keluar)) {
      $drawingKeluar = new Drawing();
      $drawingKeluar->setPath($foto_keluar);
      $drawingKeluar->setHeight(80);
      $drawingKeluar->setCoordinates('I'.$row);
      $drawingKeluar->setWorksheet($sheet);
  } else {
      $sheet->setCellValue('I'.$row, 'Belum keluar');
  }

  $no++;
  $row++;
}

// Header Export
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Rekap_Presensi.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
