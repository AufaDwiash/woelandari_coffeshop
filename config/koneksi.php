<?php

$host     = "localhost";
$username = "root";     
$password = "";         
$database = "db_cafe";   

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}


date_default_timezone_set('Asia/Jakarta');
?>