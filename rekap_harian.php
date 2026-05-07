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

$judul = 'Rekap Presensi Harian';
include('../layout/header.php'); 
include_once('../../config.php');

$tanggal = '';
$result = null;

$tanggal_dari = $_GET['tanggal_dari'] ?? '';
$tanggal_sampai = $_GET['tanggal_sampai'] ?? '';

// ================= QUERY =================
if($tanggal_dari == '' || $tanggal_sampai == ''){

  $tanggal_hari_ini = date('Y-m-d');
  $tanggal = date('d F Y', strtotime($tanggal_hari_ini));

  $result = mysqli_query($connection, "
    SELECT 
    presensi.*, 
    pegawai.nama,
    pegawai.jabatan_id,
    jam_kerja.batas_telat,

    TIMESTAMPDIFF(
        MINUTE,
        CONCAT(presensi.tanggal_masuk,' ',presensi.jam_masuk),
        CONCAT(presensi.tanggal_masuk,' ',presensi.jam_keluar)
    ) AS total_menit

    FROM presensi

    JOIN pegawai 
    ON pegawai.id = presensi.id_pegawai

    LEFT JOIN jabatan 
    ON jabatan.id = pegawai.jabatan_id

    LEFT JOIN jam_kerja 
    ON jam_kerja.jabatan_id = pegawai.jabatan_id

    WHERE tanggal_masuk BETWEEN '$tanggal_dari' AND '$tanggal_sampai'
  ");

} else {

  $tanggal = date('d F Y', strtotime($tanggal_dari)).' sampai '.date('d F Y', strtotime($tanggal_sampai));

  $result = mysqli_query($connection, "
    SELECT presensi.*, pegawai.nama, jam_kerja.batas_telat,
    TIMESTAMPDIFF(
        MINUTE,
        CONCAT(presensi.tanggal_masuk,' ',presensi.jam_masuk),
        CONCAT(presensi.tanggal_masuk,' ',presensi.jam_keluar)
    ) AS total_menit
    FROM presensi
    JOIN pegawai ON pegawai.id = presensi.id_pegawai
    LEFT JOIN jabatan ON jabatan.id = pegawai.jabatan_id
    LEFT JOIN jam_kerja ON jam_kerja.jabatan_id = jabatan.id
    WHERE tanggal_masuk BETWEEN '$tanggal_dari' AND '$tanggal_sampai'
  ");
}

if(!$result){
    die("Query Error: " . mysqli_error($connection));
}

?>

<div class="page-body">
  <div class="container-xl">

    <div class="row">
      <div class="col-md-2">
        <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#exampleModal">
          Export Excel
        </button>
      </div>

      <div class="col-md-8">
        <form action="" method="GET">
          <div class="input-group">
            <input type="date" class="form-control" name="tanggal_dari" required>
            <input type="date" class="form-control mx-2" name="tanggal_sampai" required>
            <button type="submit" class="btn btn-primary">Tampilkan</button>
            <a href="rekap_harian.php" class="btn btn-success mx-2">Refresh</a>
          </div>
        </form>
      </div>
    </div>

    <span>Rekap presensi tanggal <?= $tanggal; ?></span>

    <table class="table table-bordered mt-3">
      <tr class="text-center align-middle">
        <th>No.</th>
        <th>Nama</th>
        <th>Tanggal</th>
        <th>Jam Masuk</th>
        <th>Foto Masuk</th>
        <th>Jam Pulang</th>
        <th>Foto Keluar</th>
        <th>Total Jam</th>
        <th>Total Terlambat</th>
      </tr>

      <?php if(mysqli_num_rows($result) == 0){ ?>
          <tr>
            <td colspan="9" class="text-center">
              Data rekap presensi masih kosong!
            </td>
          </tr>
      <?php } ?>

      <?php 
      $no = 1;
      foreach($result as $rekap):

        // ================= TOTAL JAM =================
        $masuk = strtotime($rekap['tanggal_masuk'].' '.$rekap['jam_masuk']);
        $keluar = strtotime($rekap['tanggal_masuk'].' '.$rekap['jam_keluar']);

        $selisih = $keluar - $masuk;

        $total_jam = floor($selisih / 3600);
        $selisih_menit = floor(($selisih % 3600) / 60);

        // ================= TERLAMBAT =================
        $total_jam_terlambat = 0;
        $selisih_menit_terlambat = 0;

        if(!empty($rekap['batas_telat'])){

            $jam_masuk = strtotime(trim($rekap['jam_masuk']));
            $batas_telat = strtotime(trim($rekap['batas_telat']));

            if($jam_masuk > $batas_telat){

                $terlambat = $jam_masuk - $batas_telat;

                $total_jam_terlambat = floor($terlambat / 3600);
                $selisih_menit_terlambat = floor(($terlambat % 3600) / 60);

            }

        }
      ?>

      <tr class="text-center align-middle">
        <td><?= $no++; ?></td>
        <td><?= htmlspecialchars($rekap['nama']); ?></td>
        <td><?= date('d F Y', strtotime($rekap['tanggal_masuk'])); ?></td>
        <td><?= $rekap['jam_masuk']; ?></td>

        <!-- FOTO MASUK -->
        <td>
          <?php 
          $path_masuk = '../../pegawai/presensi/foto/'.$rekap['foto_masuk'];
          if(!empty($rekap['foto_masuk']) && file_exists($path_masuk)){ ?>
            <img src="<?= $path_masuk; ?>" width="80" height="80" style="object-fit:cover;border-radius:8px;">
          <?php } else { ?>
            <span class="text-muted">Tidak ada</span>
          <?php } ?>
        </td>

        <td><?= $rekap['jam_keluar']; ?></td>

        <!-- FOTO KELUAR -->
        <td>
          <?php 
          $path_keluar = '../../pegawai/presensi/foto/'.$rekap['foto_keluar'];
          if(!empty($rekap['foto_keluar']) && file_exists($path_keluar)){ ?>
            <img src="<?= $path_keluar; ?>" width="80" height="80" style="object-fit:cover;border-radius:8px;">
          <?php } else { ?>
            <span class="text-muted">Belum keluar</span>
          <?php } ?>
        </td>

        <!-- TOTAL JAM -->
        <td>
          <?php if($rekap['tanggal_keluar'] == '0000-00-00'){ ?>
            0 jam 0 menit
          <?php } else { ?>
            <?= $total_jam.' jam '.$selisih_menit.' menit'; ?>
          <?php } ?>
        </td>

        <!-- TERLAMBAT -->
        <td>
        <?php if($total_jam_terlambat > 0 || $selisih_menit_terlambat > 0){ ?>

            <span class="badge bg-danger">
                <?= $total_jam_terlambat ?> jam <?= $selisih_menit_terlambat ?> menit
            </span>

        <?php } else { ?>

            <span class="badge bg-success">
                On Time
            </span>

        <?php } ?>
        </td>

      </tr>
      <?php endforeach; ?>
    </table>

  </div>
</div>

<?php include('../layout/footer.php'); ?>