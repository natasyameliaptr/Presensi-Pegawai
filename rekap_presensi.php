<?php 
ob_start();
session_start();

if(!isset($_SESSION['login'])){
  header('Location: ../../auth/login.php?pesan=belum_login');
}
else if($_SESSION['role'] != 'Pegawai'){
  header('Location: ../../auth/login.php?pesan=tolak_akses');
}

$judul = 'Rekap Presensi';

include('../layout/header.php'); 

if(empty($_GET['tanggal_dari'])){
  $id = $_SESSION['id'];
  $result = mysqli_query($connection, "SELECT * FROM presensi WHERE id_pegawai = '$id' ORDER BY tanggal_masuk DESC");
}
else{
  $id = $_SESSION['id'];
  $tanggal_dari = $_GET['tanggal_dari'];
  $tanggal_sampai = $_GET['tanggal_sampai'];
  $result = mysqli_query($connection, "SELECT * FROM presensi WHERE id_pegawai = '$id' AND tanggal_masuk BETWEEN '$tanggal_dari' AND '$tanggal_sampai' ORDER BY tanggal_masuk DESC");
}

$jam_kerja = mysqli_query($connection, "SELECT * FROM jam_kerja LIMIT 1");
$jam_kerja_result = mysqli_fetch_array($jam_kerja);

$jam_masuk_kantor = null;

if($jam_kerja_result && !empty($jam_kerja_result['jam_masuk'])){
  $jam_masuk_kantor = strtotime($jam_kerja_result['jam_masuk']);
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
            <input type="date" class="form-control" name="tanggal_dari">
            <input type="date" class="form-control mx-2" name="tanggal_sampai">
            <button type="submit" class="btn btn-primary">Tampilkan</button>
            <button type="refres" class="btn btn-success mx-2">Refres</button>
          </div>
        </form>
      </div>
    </div>

    <table class="table table-bordered">
      <tr class="text-center">
        <th>No.</th>
        <th>Tanggal</th>
        <th>Jam Masuk</th>
        <th>Foto Masuk</th>
        <th>Jam Pulang</th>
        <th>Foto Keluar</th>
        <th>Total Jam</th>
        <th>Total Terlambat</th>
      </tr>

      <?php if(mysqli_num_rows($result) === 0){ ?>
        <tr>
          <td colspan="8" class="text-center">Data rekap presensi masih kosong!</td>
        </tr>
      <?php } ?>

      <?php 
      $no = 1;
      foreach($result as $rekap):
        // Menghitung total jam kerja
        $jam_masuk_data = $rekap['jam_masuk'] ?? '00:00:00';
        $jam_tanggal_masuk = date('Y-m-d H:i:s', strtotime($rekap['tanggal_masuk'].' '.$jam_masuk_data));
        $jam_keluar_data = $rekap['jam_keluar'] ?? '00:00:00';
        $jam_tanggal_keluar = date('Y-m-d H:i:s', strtotime($rekap['tanggal_masuk'].' '.$jam_keluar_data));

        $timestamp_masuk = strtotime($jam_tanggal_masuk);
        $timestamp_keluar = strtotime($jam_tanggal_keluar);

        $selisih = $timestamp_keluar - $timestamp_masuk;
        $total_jam = floor($selisih / 3600);

        $selisih -= $total_jam * 3600;
        $selisih_menit = floor($selisih / 60);

        // Menghitung total jam terlambat 
        $total_jam_terlambat = 0;
        $selisih_menit_terlambat = 0;

        $jam_masuk_data = $rekap['jam_masuk'] ?? null;

        if(!empty($jam_masuk_data) && $jam_masuk_kantor){

            $masuk = strtotime($jam_masuk_data);
            $terlambat = $masuk - $jam_masuk_kantor;

            if($terlambat > 0){

                $total_jam_terlambat = floor($terlambat / 3600);
                $terlambat -= $total_jam_terlambat * 3600;
                $selisih_menit_terlambat = floor($terlambat / 60);

            }
        }
            ?>
        <tr class="text-center">
          <td><?= $no++; ?></td>
          <td><?= date('d F Y', strtotime($rekap['tanggal_masuk'])); ?></td>
          <td><?= $rekap['jam_masuk'] ?? '-'; ?></td>

          <!-- FOTO MASUK -->
          <td>
            <?php 
              $path_masuk = 'foto/'.$rekap['foto_masuk'];
              if(!empty($rekap['foto_masuk']) && file_exists($path_masuk)){ ?>
                <img src="<?= $path_masuk; ?>" alt="Foto Masuk" width="80" height="80" style="object-fit:cover;border-radius:8px;">
              <?php } else { ?>
                <span class="text-muted">Tidak ada</span>
            <?php } ?>
          </td>

          <td><?= $rekap['jam_keluar']; ?></td>

          <!-- FOTO KELUAR -->
          <td>
            <?php 
              $path_keluar = 'foto/'.$rekap['foto_keluar'];
              if(!empty($rekap['foto_keluar']) && file_exists($path_keluar)){ ?>
                <img src="<?= $path_keluar; ?>" alt="Foto Keluar" width="80" height="80" style="object-fit:cover;border-radius:8px;">
              <?php } else { ?>
                <span class="text-muted">Belum keluar</span>
            <?php } ?>
          </td>

          <td>
            <?php if($rekap['tanggal_keluar'] == '0000-00-00'){ ?>
              <span>0 jam 0 menit</span>
            <?php } else { ?>
              <?= $total_jam.' jam '. $selisih_menit.' menit'; ?>
            <?php } ?>
          </td>

          <td>
            <?php if($total_jam_terlambat == 0 && $selisih_menit_terlambat == 0){ ?>
              <span class="badge bg-success">On Time</span>
            <?php } else { ?>
              <?= $total_jam_terlambat.' jam '. $selisih_menit_terlambat.' menit'; ?>
            <?php } ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>

  </div>
</div>


<!-- Modal -->
<div class="modal" id="exampleModal" tabindex="-1">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Export Excel Rekap Presensi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="<?= base_url('pegawai/presensi/rekap_excel.php') ?>" method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label for="">Tanggal Awal</label>
            <input type="date" class="form-control" name="tanggal_dari">
          </div>
          <div class="mb-3">
            <label for="">Tanggal Akhir</label>
            <input type="date" class="form-control" name="tanggal_sampai">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn me-auto" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" data-bs-dismiss="modal">Export</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include('../layout/footer.php'); ?>
