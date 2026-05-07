<style>
  #map{
    height: 300px;
  }
</style>


<?php 
ob_start();
session_start();

if(!isset($_SESSION['login'])){
  header('Location: ../../auth/login.php?pesan=belum_login');
}
else if($_SESSION['role'] != 'Pegawai'){
  header('Location: ../../auth/login.php?pesan=tolak_akses');
}

$judul = 'Presensi Masuk';

include('../layout/header.php'); 

if(isset($_POST['masuk'])){
  $latitude_pegawai = $_POST['latitude_pegawai'];
  $longitude_pegawai = $_POST['longitude_pegawai'];
  $latitude_kantor = $_POST['latitude_kantor'];
  $longitude_kantor = $_POST['longitude_kantor'];
  $radius = $_POST['radius'];
  $zona_waktu = $_POST['zona_waktu'];
  $tgl_masuk = $_POST['tgl_masuk'];
  $jam_masuk = $_POST['jam_masuk'];
}

if(empty($latitude_pegawai) || empty($longitude_pegawai)){
  $_SESSION['gagal'] = 'Lokasi anda belum aktif!';
  header('Location: ../home/home.php');
  exit;
}

if(empty($latitude_kantor) || empty($longitude_kantor)){
  $_SESSION['gagal'] = 'Koordinat kantor belum disetting!';
  header('Location: ../home/home.php');
  exit;
}

$perbedaan_koordinat = $longitude_pegawai - $longitude_kantor;
$jarak = sin(deg2rad($latitude_pegawai)) * sin(deg2rad($latitude_kantor)) + cos(deg2rad($latitude_pegawai)) * cos(deg2rad($latitude_kantor)) * cos(deg2rad($perbedaan_koordinat));
$jarak = acos($jarak);
$jarak = rad2deg($jarak);
$mil = $jarak * 60 * 1.1515;
$jarak_km = $mil * 1.609344;
$jarak_meter = $jarak_km * 1000;

// echo $jarak_meter;
// echo "<br>";
// echo $radius;
// die;

if($jarak_meter > $radius){
  $_SESSION['gagal'] = 'Anda berada diluar area kantor';
  header('Location: ../home/home.php');
  exit;
}
else if($jarak_meter <= $radius){ ?>
  <div class="page-body">
    <div class="container-xl">
      <div class="row">

        <div class="col-md-6">
          <div class="card">
            <div class="card-body">
              <div id="map"></div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card text-center">
            <div class="card-body" style="margin: auto">
              <input type="hidden" id="id" value="<?= $_SESSION['id'] ?>">
              <input type="hidden" id="tanggal_masuk" value="<?= $tgl_masuk ?>">
              <input type="hidden" id="jam_masuk" value="<?= $jam_masuk ?>">
              <div id="my_camera"></div>
              <div id="my_result"></div>
              <div><?= date('d F Y', strtotime($tgl_masuk)).' - '.$jam_masuk; ?></div>
              <button class="btn btn-primary mt-2" id="ambil-foto">Masuk</button>
            </div>
          </div>
        </div>
        
      </div>
    </div>
  </div>

<?php } ?>



<script language="JavaScript">
  Webcam.set({
    width: 320,
    height: 240,
    dest_width: 320,
    dest_height: 240,
    image_format: 'jpeg',
    jpeg_quality: 90,
    force_flash: false
  });
  Webcam.attach( '#my_camera' );
  
  document.getElementById('ambil-foto').addEventListener('click', function(){
    let id = document.getElementById('id').value;
    let tanggal_masuk = document.getElementById('tanggal_masuk').value;
    let jam_masuk = document.getElementById('jam_masuk').value;
    Webcam.snap( function(data_uri) {
      var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
        document.getElementById('my_result').innerHTML = '<img src="'+data_uri+'"/>';
        if (xhttp.readyState == 4 && xhttp.status == 200) {
          window.location.href = '../home/home.php';
        }
      };
      xhttp.open("POST", "presensi_masuk_aksi.php", true);
      xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhttp.send(
        'photo=' + encodeURIComponent(data_uri) +
        '&id=' + id +
        '&tanggal_masuk=' + tanggal_masuk +
        '&jam_masuk=' + jam_masuk
      );
    });
  });

  // Map leaflet js
  let latitude_ktr = <?= $latitude_kantor ?>;
  let longitude_ktr = <?= $longitude_kantor ?>;

  let latitude_peg = <?= $latitude_pegawai ?>;
  let longitude_peg = <?= $longitude_pegawai ?>;
  let map = L.map('map').setView([latitude_ktr, longitude_ktr], 13);
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
  }).addTo(map);

  let marker = L.marker([latitude_ktr, longitude_ktr]).addTo(map);


  let circle = L.circle([latitude_peg, longitude_peg], {
      color: 'red',
      fillColor: '#f03',
      fillOpacity: 0.5,
      radius: 500
  }).addTo(map).bindPopup('Lokasi anda saat ini').openPopup();
</script>



<?php include('../layout/footer.php'); ?>

