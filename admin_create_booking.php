<?php
include 'koneksi.php';
session_start();
date_default_timezone_set('Asia/Jakarta');

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_admin.php");
    exit;
}

// --- LOGIKA PROSES SIMPAN DATA (VERSI ADMIN) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_peminjam']);
    $bu = mysqli_real_escape_string($conn, $_POST['bu']);
    $room_id = mysqli_real_escape_string($conn, $_POST['room_id']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $sesi_pilihan = mysqli_real_escape_string($conn, $_POST['sesi']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $layout = mysqli_real_escape_string($conn, $_POST['roomset']);
    $meals = mysqli_real_escape_string($conn, $_POST['meals']);
    $catatan_user = mysqli_real_escape_string($conn, $_POST['notes']);
    
    // Gunakan nama admin dari session, jika tidak ada pakai 'Admin'
    $admin_name = isset($_SESSION['nama_admin']) ? $_SESSION['nama_admin'] : 'Admin';
    $admin_note = "Booking langsung oleh Admin: " . $admin_name;
    $final_note = $admin_note . ($catatan_user ? " | Catatan: " . $catatan_user : "");

    // --- FITUR CEK BENTROK (Smart Logic) ---
    // 1. Ambil info ruangan yang ingin dipesan sekarang
    $room_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id, parent_components FROM rooms WHERE id = '$room_id'"));
    $current_components = !empty($room_info['parent_components']) ? explode(',', $room_info['parent_components']) : [$room_id];

    // 2. Ambil semua booking yang sudah ada di tanggal & sesi tersebut (Kecuali yang di-Reject)
    // Gunakan OR untuk mengecek sesi Full Day yang bentrok dengan Pagi/Siang
    $sql_existing = "SELECT b.room_id, r.parent_components 
                     FROM bookings b 
                     JOIN rooms r ON b.room_id = r.id 
                     WHERE b.tanggal = '$tanggal' 
                     AND b.status != 'rejected'
                     AND (b.waktu = '$sesi_pilihan' OR b.waktu = 'Full Day' OR '$sesi_pilihan' = 'Full Day')";

    $res_existing = mysqli_query($conn, $sql_existing);
    $is_bentrok = false;

    while ($row = mysqli_fetch_assoc($res_existing)) {
        $existing_components = !empty($row['parent_components']) ? explode(',', $row['parent_components']) : [$row['room_id']];
        $intersect = array_intersect($current_components, $existing_components);
        
        if (!empty($intersect)) {
            $is_bentrok = true;
            break;
        }
    }

    if ($is_bentrok) {
        echo "<script>alert('MAAF! Ruangan ini (atau bagian dari ruangan gabungan ini) sudah dipesan untuk sesi tersebut. Silakan pilih ruangan atau waktu lain.'); window.history.back();</script>";
        exit();
    }

    // --- PROSES INSERT KE DATABASE ---
    // Status langsung 'approved' karena ini dibuat oleh Admin
    $query_insert = "INSERT INTO bookings (room_id, nama_peminjam, bu, tanggal, waktu, subject, roomset, meals, catatan, status) 
                     VALUES ('$room_id', '$nama', '$bu', '$tanggal', '$sesi_pilihan', '$subject', '$layout', '$meals', '$final_note', 'approved')";

    if (mysqli_query($conn, $query_insert)) {
        echo "<script>alert('Booking berhasil dibuat!'); window.location='admin_booking.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan data: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin - Create Instant Booking | TRACER</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root { --primary: #27ae60; --primary-dark: #1e8449; --bg: #f5f7fa; --text-main: #2c3e50; --text-muted: #94a3b8; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Inter", sans-serif; }
        body { background: var(--bg); color: var(--text-main); line-height: 1.6; }

        /* --- NAVBAR --- */
        .navbar { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); padding: 0 8%; height: 70px; display: flex; justify-content: space-between; align-items: center; color: white; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .nav-left { display: flex; align-items: center; gap: 40px; }
        .navbar h2 { font-size: 20px; font-weight: 800; letter-spacing: 1px; }
        .nav-menu { display: flex; list-style: none; gap: 10px; }
        .nav-menu a { color: rgba(255, 255, 255, 0.8); text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 8px; transition: 0.3s; }
        .nav-menu a:hover { color: white; background: rgba(255, 255, 255, 0.2); }

        /* --- CONTENT --- */
        .header-section { background: var(--primary); color: white; padding: 40px 8% 80px; text-align: center; }
        .container { padding: 0 8%; max-width: 900px; margin: -50px auto 40px; }
        .card { background: white; border-radius: 16px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05); border: 1px solid #edf2f7; padding: 35px; }
        .card-header { margin-bottom: 25px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; }
        .card-header h3 { color: var(--primary); font-size: 20px; font-weight: 800; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; }

        input, select { width: 100%; padding: 12px 16px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 14px; background: #fcfcfd; transition: 0.3s; }
        input:focus, select:focus { outline: none; border-color: var(--primary); background: #fff; box-shadow: 0 0 0 4px rgba(39, 174, 96, 0.1); }

        .btn-group { display: flex; gap: 15px; margin-top: 20px; }
        .btn-submit { background: var(--primary); color: white; border: none; flex: 2; padding: 16px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; font-size: 16px; }
        .btn-submit:hover { background: var(--primary-dark); transform: translateY(-2px); }
        .btn-back { background: #f1f5f9; color: #475569; text-decoration: none; flex: 1; padding: 16px; border-radius: 12px; font-weight: 700; text-align: center; font-size: 14px; display: flex; align-items: center; justify-content: center; }

        @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } .full-width { grid-column: span 1; } }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="nav-left">
            <h2>TRACER</h2>
            <ul class="nav-menu">
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="admin_booking.php">Approval</a></li>
                <li><a href="admin_room.php">Fasilitas</a></li>
            </ul>
        </div>
        <span style="font-size: 13px;">Admin: <strong><?= isset($_SESSION['nama_admin']) ? $_SESSION['nama_admin'] : 'Administrator' ?></strong></span>
    </div>

    <div class="header-section">
        <h1>Reservasi Instan</h1>
        <p>Gunakan formulir ini untuk membooking ruangan langsung tanpa proses persetujuan.</p>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3>⚡ Buat Reservasi (Status: Approved)</h3>
            </div>
            <form action="" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Lengkap Pemesan</label>
                        <input type="text" name="nama_peminjam" placeholder="Nama Pemesan" required />
                    </div>

                    <div class="form-group">
                        <label>Business Unit (BU)</label>
                        <select name="bu" required>
                            <option value="">-- Pilih BU --</option>
                            <option value="GGP">GGP</option>
                            <option value="GGL">GGL</option>
                            <option value="SREEYA">SREEYA</option>
                            <option value="SSN">SSN</option>
                            <option value="REJUVE">REJUVE</option>
                            <option value="UJA">UJA</option>
                            <option value="NSA">NSA</option>
                            <option value="SKT">SKT</option>
                            <option value="ITN">ITN</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Pilih Ruangan</label>
                        <select name="room_id" required>
                            <option value="">-- Pilih Ruangan --</option>
                            <?php
                            $rooms = mysqli_query($conn, "SELECT * FROM rooms WHERE status_aktif = 1 ORDER BY parent_components DESC, nama_ruangan ASC");
                            while ($r = mysqli_fetch_assoc($rooms)) {
                                $label = $r['nama_ruangan'];
                                if(!empty($r['parent_components'])) $label .= " (Gabungan)";
                                echo "<option value='".$r['id']."'>".$label."</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tanggal Kegiatan</label>
                        <input type="date" name="tanggal" id="inputDate" required />
                    </div>

                    <div class="form-group">
                        <label>Waktu (Sesi)</label>
                        <select name="sesi" required>
                            <option value="">-- Pilih Sesi --</option>
                            <option value="Pagi">Pagi</option>
                            <option value="Siang">Siang</option>
                            <option value="Full Day">Full Day</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Layout Ruangan</label>
                        <select name="roomset" required>
                            <option value="">-- Pilih Layout --</option>
                            <option value="U-Shape">U-Shape</option>
                            <option value="Classroom">Classroom</option>
                            <option value="Theater">Theater</option>
                            <option value="Round Table">Round Table</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label>Purpose / Judul Kegiatan</label>
                        <input type="text" name="subject" placeholder="Judul Kegiatan" required />
                    </div>

                    <div class="form-group">
                        <label>Opsi Konsumsi</label>
                        <select name="meals" required>
                            <option value="">-- Pilih Konsumsi --</option>
                            <option value="1x Coffee Break">1x Coffee Break</option>
                            <option value="1x Coffee Break + 1 Lunch">1x Coffee Break + 1 Lunch</option>
                            <option value="2x Coffee Break + 1 Lunch">2x Coffee Break + 1 Lunch</option>
                            <option value="Tanpa Konsumsi">Tanpa Konsumsi</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Catatan Tambahan (Opsional)</label>
                        <input type="text" name="notes" placeholder="Contoh: Butuh Mic Wireless" />
                    </div>
                </div>

                <div class="btn-group">
                    <a href="admin_dashboard.php" class="btn-back">Batal</a>
                    <button type="submit" class="btn-submit">Konfirmasi & Buat Booking</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const today = new Date().toISOString().split("T")[0];
        document.getElementById("inputDate").setAttribute('min', today);
    </script>
</body>
</html>