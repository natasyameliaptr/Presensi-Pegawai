<?php 
ob_start();
session_start();

if(!isset($_SESSION['login'])){
  header('Location: ../../auth/login.php?pesan=belum_login');
  exit;
}
else if($_SESSION['role'] != 'Admin'){
  header('Location: ../../auth/login.php?pesan=tolak_akses');
  exit;
}

$judul = 'Rekap Harian Excel';
include_once('../../config.php');

require('../../assets/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

$tanggal_dari = $_POST['tanggal_dari'];
$tanggal_sampai = $_POST['tanggal_sampai'];

// 🔥 QUERY SUDAH PAKAI jam_kerja
$result = mysqli_query($connection, "
SELECT 
    presensi.*, 
    pegawai.nama, 
    pegawai.nip,
    pegawai.jabatan_id,
    jam_kerja.batas_telat

    FROM presensi 

    JOIN pegawai 
    ON pegawai.id = presensi.id_pegawai

    LEFT JOIN jabatan 
    ON jabatan.id = pegawai.jabatan_id

    LEFT JOIN jam_kerja 
    ON jam_kerja.jabatan_id = pegawai.jabatan_id

    WHERE tanggal_masuk BETWEEN '$tanggal_dari' AND '$tanggal_sampai'

    ORDER BY tanggal_masuk DESC
");

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// HEADER
$sheet->setCellValue('A1', 'REKAP PRESENSI HARIAN');
$sheet->setCellValue('A2', 'Tanggal Awal');
$sheet->setCellValue('A3', 'Tanggal Akhir');
$sheet->setCellValue('C2', $tanggal_dari);
$sheet->setCellValue('C3', $tanggal_sampai);

$sheet->setCellValue('A5', 'NO');
$sheet->setCellValue('B5', 'NAMA');
$sheet->setCellValue('C5', 'NIP');
$sheet->setCellValue('D5', 'TANGGAL MASUK');
$sheet->setCellValue('E5', 'JAM MASUK');
$sheet->setCellValue('F5', 'FOTO MASUK');
$sheet->setCellValue('G5', 'TANGGAL KELUAR');
$sheet->setCellValue('H5', 'JAM KELUAR');
$sheet->setCellValue('I5', 'FOTO KELUAR');
$sheet->setCellValue('J5', 'TOTAL JAM KERJA');
$sheet->setCellValue('K5', 'TOTAL TERLAMBAT');

$sheet->mergeCells('A1:K1');
$sheet->mergeCells('A2:B2');
$sheet->mergeCells('A3:B3');

$no = 1;
$row = 6;

while($data = mysqli_fetch_array($result)){

  // 🔥 TOTAL JAM KERJA
  $masuk = strtotime($data['tanggal_masuk'].' '.$data['jam_masuk']);
  $keluar = strtotime($data['tanggal_masuk'].' '.$data['jam_keluar']);

  $if($keluar < $masuk){
    $keluar += 86400;
  }

  $selisih = $keluar - $masuk;
  $total_jam = floor($selisih / 3600);
  $selisih_menit = floor(($selisih % 3600) / 60);

  // 🔥 TERLAMBAT
  $total_jam_terlambat = 0;
  $selisih_menit_terlambat = 0;
  $terlambat = 0;

  if(!empty($data['batas_telat'])){

      $jam_masuk = strtotime(trim($data['jam_masuk']));
      $batas_telat = strtotime(trim($data['batas_telat']));

      if($jam_masuk > $batas_telat){

          $terlambat = $jam_masuk - $batas_telat;

          $total_jam_terlambat = floor($terlambat / 3600);
          $selisih_menit_terlambat = floor(($terlambat % 3600) / 60);

      }

  }

  // ISI DATA
  $sheet->setCellValue('A'.$row, $no);
  $sheet->setCellValue('B'.$row, $data['nama']);
  $sheet->setCellValue('C'.$row, $data['nip']);
  $sheet->setCellValue('D'.$row, $data['tanggal_masuk']);
  $sheet->setCellValue('E'.$row, $data['jam_masuk']);
  $sheet->setCellValue('G'.$row, $data['tanggal_keluar']);
  $sheet->setCellValue('H'.$row, $data['jam_keluar']);

  // TOTAL JAM
  if($data['tanggal_keluar'] == '0000-00-00'){
    $sheet->setCellValue('J'.$row, '0 jam 0 menit');
  } else {
    $sheet->setCellValue('J'.$row, $total_jam.' jam '.$selisih_menit.' menit');
  }

  // TERLAMBAT
  if($terlambat <= 0){
    $sheet->setCellValue('K'.$row, 'On Time');
  } else {
    $sheet->setCellValue('K'.$row, $total_jam_terlambat.' jam '.$selisih_menit_terlambat.' menit');
  }

  // FOTO MASUK
  if(!empty($data['foto_masuk']) && file_exists('../../pegawai/presensi/foto/'.$data['foto_masuk'])){
    $drawingMasuk = new Drawing();
    $drawingMasuk->setPath('../../pegawai/presensi/foto/'.$data['foto_masuk']);
    $drawingMasuk->setCoordinates('F'.$row);
    $drawingMasuk->setHeight(80);
    $drawingMasuk->setWorksheet($sheet);
  } else {
    $sheet->setCellValue('F'.$row, 'Tidak ada foto');
  }

  // FOTO KELUAR
  if(!empty($data['foto_keluar']) && file_exists('../../pegawai/presensi/foto/'.$data['foto_keluar'])){
    $drawingKeluar = new Drawing();
    $drawingKeluar->setPath('../../pegawai/presensi/foto/'.$data['foto_keluar']);
    $drawingKeluar->setCoordinates('I'.$row);
    $drawingKeluar->setHeight(80);
    $drawingKeluar->setWorksheet($sheet);
  } else {
    $sheet->setCellValue('I'.$row, 'Tidak ada foto');
  }

  $sheet->getRowDimension($row)->setRowHeight(65);

  $no++;
  $row++;
}

// AUTO SIZE
foreach(range('A','K') as $col){
  $sheet->getColumnDimension($col)->setAutoSize(true);
}

// OUTPUT
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Laporan Presensi Harian.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>