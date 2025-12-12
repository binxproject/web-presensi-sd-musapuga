<?php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'presensi_sd_musapuga';

$conn = new mysqli($host, $username, $password, $dbname);

if( $conn->connect_error ) {
    die('Koneksi database gagal: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

date_default_timezone_set('Asia/Jakarta');
?>