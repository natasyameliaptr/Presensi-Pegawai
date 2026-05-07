<?php

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'absen';

$connection = mysqli_connect("localhost", "root", "", "absen");

if(!$connection){
  die("Koneksi gagal: " . mysqli_connect_error());
}

function base_url($url = null){
  $base_url = 'http://localhost/sdmuh39mdn';
  if($url != null){
    return $base_url . '/' . $url;
  }
  else{
    return $base_url;
  }
}
?>