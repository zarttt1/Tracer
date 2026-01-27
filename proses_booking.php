<?php
session_start();
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Ambil data dari form dan bersihkan (Sanitize)
    $room_id       = mysqli_real_escape_string($conn, $_POST['room_id']);
    $nama_peminjam = mysqli_real_escape_string($conn, $_POST['nama_peminjam']);
    $subject       = mysqli_real_escape_string($conn, $_POST['subject']);
    $tanggal       = mysqli_real_escape_string($conn, $_POST['tanggal']);
    
    // Sesuai struktur tabel database: kolom 'waktu' dan 'bu'
    $waktu         = mysqli_real_escape_string($conn, $_POST['waktu']); 
    $bu            = mysqli_real_escape_string($conn, $_POST['bu'] ?? '-');
    
    // Data tambahan sesuai tabel bookings
    $layout        = mysqli_real_escape_string($conn, $_POST['layout'] ?? '');
    $meals         = mysqli_real_escape_string($conn, $_POST['meals'] ?? '');
    $catatan       = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');

    // 2. Query Insert sesuai kolom di tabel 'bookings'
    // Kolom: room_id, nama_peminjam, subject, tanggal, layout, meals, catatan, status, waktu, bu
    $query = "INSERT INTO bookings (
                room_id, 
                nama_peminjam, 
                subject, 
                tanggal, 
                layout, 
                meals, 
                catatan, 
                status, 
                waktu, 
                bu, 
                created_at, 
                updated_at
              ) VALUES (
                '$room_id', 
                '$nama_peminjam', 
                '$subject', 
                '$tanggal', 
                '$layout', 
                '$meals', 
                '$catatan', 
                'pending', 
                '$waktu', 
                '$bu', 
                NOW(), 
                NOW()
              )";

    if (mysqli_query($conn, $query)) {
        // Alihkan kembali ke dashboard dengan status sukses
        header("Location: user1_dashboard.php?status=success");
        exit;
    } else {
        echo "Gagal menyimpan data: " . mysqli_error($conn);
    }
}
?>