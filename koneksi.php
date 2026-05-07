<?php
$koneksi = mysqli_connect("localhost", "root", "", "absen");

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>