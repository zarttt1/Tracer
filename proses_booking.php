<?php
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_id = $_POST['room_id'];
    $nama = $_POST['nama_peminjam'];
    $subject = $_POST['subject'];
    $tanggal = $_POST['tanggal'];
    $mulai = $_POST['waktu_mulai'];
    $selesai = $_POST['waktu_selesai'];
    // ... ambil data lainnya ...

    $query = "INSERT INTO bookings (room_id, nama_peminjam, subject, tanggal, waktu_mulai, waktu_selesai, status) 
              VALUES ('$room_id', '$nama', '$subject', '$tanggal', '$mulai', '$selesai', 'pending')";

    if (mysqli_query($conn, $query)) {
        header("Location: jadwal.php?status=success");
    }
}
?>