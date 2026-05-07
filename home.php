<?php 
session_start();

if(!isset($_SESSION['login'])){
  header('Location: ../../auth/login.php?pesan=belum_login');
  exit;
}
else if($_SESSION['role'] != 'Admin'){
  header('Location: ../../auth/login.php?pesan=tolak_akses');
  exit;
}

include('../../config.php');
$judul = 'Home';
include('../layout/header.php');

// Hitung total pegawai aktif
$pegawai = mysqli_query($connection, "
  SELECT pegawai.*, users.status 
  FROM pegawai 
  JOIN users ON pegawai.id = users.id_pegawai 
  WHERE users.status = 'Aktif'
");
$total_pegawai_aktif = mysqli_num_rows($pegawai);

$tanggal_hari_ini = date('Y-m-d');

// Hitung jumlah hadir
$hadir = mysqli_query($connection, "
  SELECT COUNT(DISTINCT id_pegawai) AS jumlah_hadir 
  FROM presensi 
  WHERE tanggal_masuk = '$tanggal_hari_ini'
");
$data_hadir = mysqli_fetch_assoc($hadir);
$jumlah_hadir = $data_hadir['jumlah_hadir'] ?? 0;

// Hitung jumlah sakit, izin, cuti
$sakit_izin_cuti = mysqli_query($connection, "
  SELECT COUNT(DISTINCT id_pegawai) AS jumlah_sakit_izin_cuti 
  FROM ketidakhadiran 
  WHERE tanggal = '$tanggal_hari_ini'
");
$data_sakit_izin_cuti = mysqli_fetch_assoc($sakit_izin_cuti);
$jumlah_sakit_izin_cuti = $data_sakit_izin_cuti['jumlah_sakit_izin_cuti'] ?? 0;

// Hitung jumlah alpa
$jumlah_alpa = $total_pegawai_aktif - ($jumlah_hadir + $jumlah_sakit_izin_cuti);
if ($jumlah_alpa < 0) $jumlah_alpa = 0;
?>

<!-- Page body -->
<div class="page-body">
  <div class="container-xl">

    <!-- JAM DIGITAL -->
    <div class="row mb-4">
      <div class="col text-center">
        <div id="clock" style="
          font-size: 48px;
          font-weight: bold;
          color: #007bff;
          text-shadow: 0 0 15px rgba(0,123,255,0.6);
          letter-spacing: 2px;
          transition: all 0.3s ease-in-out;
        "></div>
        <div id="date" style="font-size: 20px; color: #6c757d; margin-top: 5px;"></div>
      </div>
    </div>

    <div class="row row-deck row-cards">
      <div class="col-12">
        <div class="row row-cards">

          <!-- Total Pegawai Aktif -->
          <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <span class="bg-primary text-white avatar">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-user"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                    </span>
                  </div>
                  <div class="col">
                    <div class="font-weight-medium">Total Pegawai Aktif</div>
                    <div class="text-secondary"><?= $total_pegawai_aktif; ?> pegawai</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Jumlah Hadir -->
          <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <span class="bg-green text-white avatar">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-user-check"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4" /><path d="M15 19l2 2l4 -4" /></svg>
                    </span>
                  </div>
                  <div class="col">
                    <div class="font-weight-medium">Jumlah Hadir</div>
                    <div class="text-secondary"><?= $jumlah_hadir; ?> pegawai</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Jumlah Alpa -->
          <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <span class="bg-twitter text-white avatar">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-user-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h3.5" /><path d="M22 22l-5 -5" /><path d="M17 22l5 -5" /></svg>
                    </span>
                  </div>
                  <div class="col">
                    <div class="font-weight-medium">Jumlah Alpa</div>
                    <div class="text-secondary"><?= $jumlah_alpa; ?> pegawai</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Jumlah Sakit, Izin & Cuti -->
          <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <span class="bg-facebook text-white avatar">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-user-question"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h3.5" /><path d="M19 22v.01" /><path d="M19 19a2.003 2.003 0 0 0 .914 -3.782a1.98 1.98 0 0 0 -2.414 .483" /></svg>
                    </span>
                  </div>
                  <div class="col">
                    <div class="font-weight-medium">Jumlah Sakit, Izin & Cuti</div>
                    <div class="text-secondary"><?= $jumlah_sakit_izin_cuti; ?> pegawai</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>

<!-- SCRIPT JAM DIGITAL -->
<script>
function updateClock() {
  const now = new Date();
  const hours = String(now.getHours()).padStart(2, '0');
  const minutes = String(now.getMinutes()).padStart(2, '0');
  const seconds = String(now.getSeconds()).padStart(2, '0');
  const clock = document.getElementById('clock');
  const date = document.getElementById('date');

  clock.textContent = `${hours}:${minutes}:${seconds}`;

  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  date.textContent = now.toLocaleDateString('id-ID', options);
}

setInterval(updateClock, 1000);
updateClock();
</script>

<?php include('../layout/footer.php'); ?>
