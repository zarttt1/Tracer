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
    $layout = mysqli_real_escape_string($conn, $_POST['layout']); 
    $meals = mysqli_real_escape_string($conn, $_POST['meals']);
    $catatan_user = mysqli_real_escape_string($conn, $_POST['notes']); 

    // 1. Penanganan khusus jika pilih "Gabung Ruangan"
    if ($room_id === 'custom') {
        $detail_gabung = mysqli_real_escape_string($conn, $_POST['custom_room_name']);
        // Untuk sistem database, kita harus arahkan ke satu ID master (misal ID 99) 
        // atau simpan di catatan. Mari kita asumsikan untuk sementara gabung ruangan 
        // menggunakan ID tertentu atau validasi manual.
        // SARAN: Buat satu record di tabel 'rooms' bernama 'Gabungan / Custom' dengan ID khusus.
        $room_id = 9; // Contoh ID untuk 'Gabungan'
        $catatan_user = "[Permintaan Gabung: $detail_gabung] " . $catatan_user;
    }

    // 2. Logika Cek Bentrok
    $room_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id, parent_components FROM rooms WHERE id = '$room_id'"));
    $current_components = !empty($room_info['parent_components']) ? explode(',', $room_info['parent_components']) : [$room_id];

    $sql_existing = "SELECT b.room_id, r.parent_components 
                     FROM bookings b 
                     JOIN rooms r ON b.room_id = r.id 
                     WHERE b.tanggal = '$tanggal' 
                     AND b.waktu = '$sesi_pilihan' 
                     AND b.status != 'rejected'";

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
        echo "<script>alert('MAAF! Ruangan ini sudah dipesan pada waktu tersebut.'); window.history.back();</script>";
        exit();
    }

    // 3. PROSES INSERT (Tambahkan bagian ini)
    $query_insert = "INSERT INTO bookings (room_id, nama_peminjam, bu, tanggal, waktu, subject, layout, meals, catatan, status) 
                     VALUES ('$room_id', '$nama', '$bu', '$tanggal', '$sesi_pilihan', '$subject', '$layout', '$meals', '$catatan_user', 'pending')";

    if (mysqli_query($conn, $query_insert)) {
        echo "<script>alert('Reservasi Berhasil Dikirim! Silakan tunggu konfirmasi admin.'); window.location='my_bookings.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
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
        .nav-links a { 
    color: rgba(255, 255, 255, 0.7); 
    text-decoration: none; 
    padding: 8px 15px; /* Padding yang lebih masuk akal */
    border-radius: 8px; 
    font-weight: 600; 
    font-size: 14px; 
}
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
                        <select name="layout" required>
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