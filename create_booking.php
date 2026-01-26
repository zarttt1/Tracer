<?php 
include 'koneksi.php'; 
date_default_timezone_set('Asia/Jakarta');
$current_page = basename($_SERVER['PHP_SELF']);

// --- LOGIKA PROSES SIMPAN DATA ---
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

    // --- LOGIKA GABUNG RUANGAN ---
    if ($room_id === 'custom') {
        $detail_gabungan = mysqli_real_escape_string($conn, $_POST['custom_room_name']);
        // Simpan info gabungan ke dalam catatan agar admin tahu
        $catatan = "[GABUNG RUANGAN: $detail_gabungan] " . $catatan_user;
        // Kita gunakan room_id 0 atau ID salah satu ruangan sebagai placeholder
        $final_room_id = 1; // Sesuaikan dengan salah satu ID di tabel rooms (misal Ruang A)
    } else {
        $catatan = $catatan_user;
        $final_room_id = $room_id;
    }

    // --- FITUR CEK BENTROK ---
    $check_bentrok = mysqli_query($conn, "SELECT * FROM bookings 
                                          WHERE room_id = '$final_room_id' 
                                          AND tanggal = '$tanggal' 
                                          AND waktu = '$sesi_pilihan' 
                                          AND status = 'approved'");

    if (mysqli_num_rows($check_bentrok) > 0) {
        echo "<script>alert('Maaf, ruangan sudah terisi pada tanggal dan sesi tersebut.'); window.history.back();</script>";
    } else {
        $sql = "INSERT INTO bookings (room_id, nama_peminjam, bu, subject, tanggal, layout, meals, catatan, status, waktu, updated_at) 
                VALUES ('$final_room_id', '$nama', '$bu', '$subject', '$tanggal', '$layout', '$meals', '$catatan', 'pending', '$sesi_pilihan', NOW())";

        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Booking berhasil dikirim!'); window.location='my_bookings.php';</script>";
        } else {
            echo "<script>alert('Gagal Simpan: " . mysqli_error($conn) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Form Reservasi - TRACER</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root { --primary: #197b40; --primary-dark: #125a2f; --bg: #f8fafc; --white: #ffffff; --border: #e2e8f0; --text-main: #1e293b; --text-muted: #64748b; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Inter", sans-serif; }
        body { background: var(--bg); color: var(--text-main); line-height: 1.6; }
        nav { background: var(--primary); padding: 0 8%; height: 65px; display: flex; justify-content: space-between; align-items: center; color: white; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); }
        .logo { text-decoration: none; color: white; } .logo h1 { font-size: 22px; font-weight: 800; }
        .nav-links { display: flex; align-items: center; gap: 10px; }
        .nav-links a { color: rgba(255, 255, 255, 0.7); text-decoration: none; padding: 8px 166px; border-radius: 8px; font-weight: 600; font-size: 14px; }
        .header-section { background: var(--primary); color: white; padding: 40px 8% 80px 8%; }
        .container { padding: 0 8%; max-width: 900px; margin: -50px auto 40px auto; }
        .card { background: var(--white); border-radius: 16px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); border: 1px solid var(--border); padding: 35px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 700; font-size: 12px; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; }
        input, select { width: 100%; padding: 12px 16px; border: 1.5px solid var(--border); border-radius: 10px; font-size: 14px; background: #fcfcfd; outline: none; transition: 0.2s; }
        input:focus, select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(25, 123, 64, 0.1); }
        .btn-submit { width: 100%; padding: 16px; border: none; border-radius: 12px; background: var(--primary); color: white; font-size: 16px; font-weight: 700; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-submit:hover { background: var(--primary-dark); transform: translateY(-2px); }
        .custom-box { background: #fffbeb; border: 1px solid #fde68a; padding: 15px; border-radius: 10px; margin-top: 10px; display: none; }
        @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } .full-width { grid-column: span 1; } }
    </style>
</head>
<body>
    <nav>
        <a href="index.php" class="logo"><h1>TRACER</h1></a>
        <div class="nav-links">
            <a href="jadwal.php">Jadwal</a>
            <a href="my_bookings.php">My Booking</a>
        </div>
    </nav>

    <div class="header-section">
        <h1>Form Reservasi</h1>
        <p>Lengkapi detail untuk mengajukan penggunaan ruangan.</p>
    </div>

    <div class="container">
        <div class="card">
            <form action="" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Lengkap Pemesan</label>
                        <input type="text" name="nama_peminjam" placeholder="Nama Anda" required />
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
                            <option value="BE">BE</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label>Pilih Ruangan</label>
                        <select name="room_id" id="roomSelect" onchange="toggleCustomRoom()" required>
                            <option value="">-- Pilih Ruangan --</option>
                            <?php
                            $rooms = mysqli_query($conn, "SELECT * FROM rooms WHERE status_aktif = 1");
                            while ($r = mysqli_fetch_assoc($rooms)) {
                                echo "<option value='".$r['id']."'>".$r['nama_ruangan']."</option>";
                            }
                            ?>
                            <option value="custom" style="font-weight: bold; color: var(--primary);">+ Gabung Ruangan (A, B, C)</option>
                        </select>

                        <div id="customRoomBox" class="custom-box">
                            <label style="color: #92400e;">Detail Ruangan yang Digabung</label>
                            <input type="text" name="custom_room_name" id="customInput" placeholder="Contoh: Ruang A & Ruang B" />
                        </div>
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

                    <div class="form-group full-width">
                        <label>Purpose / Judul Kegiatan</label>
                        <input type="text" name="subject" placeholder="Contoh: Rapat Koordinasi Tahunan" required />
                    </div>

                    <div class="form-group full-width">
                        <label>Catatan Tambahan (Opsional)</label>
                        <input type="text" name="notes" placeholder="Contoh: Butuh Mic Wireless" />
                    </div>
                </div>

                <button type="submit" class="btn-submit">Kirim Pengajuan Reservasi</button>
            </form>
        </div>
    </div>

    <script>
        // Mencegah user memilih tanggal kemarin
        const today = new Date().toISOString().split("T")[0];
        document.getElementById("inputDate").setAttribute('min', today);

        // Fungsi Tampilkan/Sembunyikan Input Custom
        function toggleCustomRoom() {
            const select = document.getElementById('roomSelect');
            const box = document.getElementById('customRoomBox');
            const input = document.getElementById('customInput');

            if (select.value === 'custom') {
                box.style.display = 'block';
                input.setAttribute('required', 'required');
                input.focus();
            } else {
                box.style.display = 'none';
                input.removeAttribute('required');
                input.value = '';
            }
        }
    </script>
</body>
</html>