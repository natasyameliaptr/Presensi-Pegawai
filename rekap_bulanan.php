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

$judul = 'Rekap Presensi Bulanan';
include('../layout/header.php'); 
include_once('../../config.php');

// === FIX FILTER ===
$bulan = '';
$tahun = '';

if(isset($_GET['filter_bulan']) && isset($_GET['filter_tahun']) 
   && $_GET['filter_bulan'] != '' && $_GET['filter_tahun'] != ''){

  $bulan = $_GET['filter_bulan'];
  $tahun = $_GET['filter_tahun'];
  $tahun_bulan = $tahun . '-' . $bulan;

} else {
  // default bulan sekarang
  $tahun_bulan = date('Y-m');
  $bulan = date('m');
  $tahun = date('Y');
}

// === QUERY ===
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

WHERE DATE_FORMAT(presensi.tanggal_masuk, '%Y-%m') = '$tahun_bulan'

ORDER BY presensi.tanggal_masuk DESC
");

// CEK ERROR QUERY
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
            <select name="filter_bulan" class="form-control">
              <option value="">-- Pilih Bulan --</option>
              <?php
              $bulanList = [
                "01" => "Januari", "02" => "Februari", "03" => "Maret", "04" => "April",
                "05" => "Mei", "06" => "Juni", "07" => "Juli", "08" => "Agustus",
                "09" => "September", "10" => "Oktober", "11" => "November", "12" => "Desember"
              ];
              foreach($bulanList as $key => $value){
                $selected = (isset($_GET['filter_bulan']) && $_GET['filter_bulan'] == $key) ? 'selected' : '';
                echo "<option value='$key' $selected>$value</option>";
              }
              ?>
            </select>

            <?php $tahunSekarang = date('Y'); ?>
            <select name="filter_tahun" class="form-control mx-2">
              <option value="">-- Pilih Tahun --</option>
              <?php for ($i = 5; $i >= 0; $i--): 
                $tahun = $tahunSekarang - $i;
                $selected = (isset($_GET['filter_tahun']) && $_GET['filter_tahun'] == $tahun) ? 'selected' : '';
              ?>
                <option value="<?= $tahun ?>" <?= $selected ?>><?= $tahun ?></option>
              <?php endfor; ?>
            </select>

            <button type="submit" class="btn btn-primary">Tampilkan</button>
            <a href="rekap_bulanan.php" class="btn btn-success mx-2">Refresh</a>
          </div>
        </form>
      </div>
    </div>

    <span>
      Rekap Presensi Bulan 
      <?= date('F Y', strtotime($tahun_bulan.'-01')); ?>
    </span>

    <div class="table-responsive mt-2">
      <table class="table table-bordered text-center align-middle">
        <thead class="table-primary">
          <tr>
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
        </thead>
        <tbody>
        <?php if(mysqli_num_rows($result) === 0){ ?>
          <tr>
            <td colspan="9">Data rekap presensi masih kosong!</td>
          </tr>
        <?php } ?>

        <?php 
        $no = 1;
        foreach($result as $rekap):
          // Hitung total jam kerja
          $masuk = strtotime($rekap['tanggal_masuk'].' '.$rekap['jam_masuk']);
$keluar = strtotime($rekap['tanggal_masuk'].' '.$rekap['jam_keluar']);

$selisih = $keluar - $masuk;

$total_jam = floor($selisih / 3600);
$selisih_menit = floor(($selisih % 3600) / 60);

          // Hitung keterlambatan
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
          <tr>
            <td><?= $no++; ?></td>
            <td><?= htmlspecialchars($rekap['nama']); ?></td>
            <td><?= date('d F Y', strtotime($rekap['tanggal_masuk'])); ?></td>
            <td><?= $rekap['jam_masuk']; ?></td>
            <td>
              <?php if(!empty($rekap['foto_masuk']) && file_exists("../../pegawai/presensi/foto/".$rekap['foto_masuk'])): ?>
                <img src="../../pegawai/presensi/foto/<?= $rekap['foto_masuk']; ?>" alt="foto masuk" width="70" class="img-thumbnail">
              <?php else: ?>
                <span class="text-muted">Tidak ada</span>
              <?php endif; ?>
            </td>
            <td><?= $rekap['jam_keluar']; ?></td>
            <td>
              <?php if(!empty($rekap['foto_keluar']) && file_exists("../../pegawai/presensi/foto/".$rekap['foto_keluar'])): ?>
                <img src="../../pegawai/presensi/foto/<?= $rekap['foto_keluar']; ?>" alt="foto keluar" width="70" class="img-thumbnail">
              <?php else: ?>
                <span class="text-muted">Tidak ada</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if($rekap['tanggal_keluar'] == '0000-00-00'){ ?>
                0 jam 0 menit
              <?php } else { ?>
                <?= $total_jam.' jam '. $selisih_menit.' menit'; ?>
              <?php } ?>
            </td>
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
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Export Excel -->
<div class="modal" id="exampleModal" tabindex="-1">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Export Excel Rekap Presensi Bulanan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= base_url('admin/presensi/rekap_bulanan_excel.php') ?>" method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label>Bulan</label>
            <select name="filter_bulan" class="form-control" required>
              <option value="">-- Pilih Bulan --</option>
              <?php foreach($bulanList as $key => $value): ?>
                <option value="<?= $key ?>"><?= $value ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label>Tahun</label>
            <select name="filter_tahun" class="form-control" required>
              <option value="">-- Pilih Tahun --</option>
              <?php for ($i = 5; $i >= 0; $i--): 
                $tahun = $tahunSekarang - $i; ?>
                <option value="<?= $tahun ?>"><?= $tahun ?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn me-auto" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Export</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include('../layout/footer.php'); ?>
