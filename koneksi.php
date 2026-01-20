<?php
$host = "localhost";
$user = "root";
$pass = "Admin123";
$db   = "tracer";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>