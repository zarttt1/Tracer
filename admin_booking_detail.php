<?php
include 'koneksi.php';
session_start();

// 1. Proteksi Halaman Admin
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login_admin.php");
    exit();
}

// 2. Ambil ID dari URL
if (!isset($_GET['id'])) {
    header("Location: admin_booking.php");
    exit();
}
$booking_id = mysqli_real_escape_string($conn, $_GET['id']);

// 3. Proses Update Status (Approve/Reject)
if (isset($_POST['update_status'])) {
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    // Menggunakan kolom catatan_admin sesuai dengan file admin_edit_booking sebelumnya
    $admin_comment = mysqli_real_escape_string($conn, $_POST['admin_comment']);

    if (!empty($status)) {
        // Disamakan menggunakan kolom catatan_admin agar sinkron dengan halaman edit
        $sql_update = "UPDATE bookings SET status = '$status', admin_comment = '$admin_comment' WHERE id = '$booking_id'";
        
        if (mysqli_query($conn, $sql_update)) {
            echo "<script>alert('Status booking berhasil diperbarui!'); window.location='admin_booking.php';</script>";
        } else {
            echo "<script>alert('Gagal memperbarui status: " . mysqli_error($conn) . "');</script>";
        }
    }
}

// 4. Tarik Data Detail Booking
$sql_detail = "SELECT b.*, r.nama_ruangan
               FROM bookings b 
               JOIN rooms r ON b.room_id = r.id 
               WHERE b.id = '$booking_id'";
$result = mysqli_query($conn, $sql_detail);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "Data tidak ditemukan.";
    exit();
}

// --- LOGIKA DETEKSI RUANGAN GABUNGAN ---
$catatan_full = $data['catatan'] ?? '';
$nama_ruangan_display = $data['nama_ruangan']; // Default dari database

// Cek pola [GABUNG RUANGAN: ...] yang dikirim dari create_booking.php
if (preg_match('/\[GABUNG RUANGAN: (.*?)\]/', $catatan_full, $matches)) {
    $nama_ruangan_display = $matches[1]; // Ambil nama kustomnya (misal: ruang b dan c)
    $catatan_user_clean = trim(str_replace($matches[0], '', $catatan_full)); // Bersihkan teks tag dari catatan
} else {
    $catatan_user_clean = $catatan_full;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin - Detail Booking | TRACER</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root { --primary: #197b40; --bg: #f5f7fa; --text-main: #2c3e50; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Inter", sans-serif; }
        body { background: var(--bg); color: var(--text-main); }
        .navbar { background: var(--primary); padding: 0 8%; height: 70px; display: flex; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); color: white; }
        .navbar h2 { font-size: 20px; font-weight: 800; }
        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .card { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
        .card-header { padding: 25px 30px; border-bottom: 2px solid #f8fafc; display: flex; justify-content: space-between; align-items: center; }
        .card-header h2 { font-size: 18px; color: var(--primary); }
        .card-body { padding: 30px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 20px; }
        .info-group h3 { color: #94a3b8; font-size: 11px; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 1px; }
        .info-group p { font-size: 15px; font-weight: 600; }
        .notes-box { background: #f8fafc; border-left: 4px solid var(--primary); padding: 15px; border-radius: 8px; margin-top: 10px; font-size: 14px; color: #475569; }
        .approval-panel { background: #f1f5f9; padding: 30px; border-top: 1px solid #e2e8f0; }
        textarea { width: 100%; padding: 15px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 14px; min-height: 100px; margin-bottom: 20px; outline: none; }
        .btn-group { display: flex; gap: 15px; }
        .btn { padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 700; transition: 0.3s; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
        .btn-approve { background: var(--primary); color: white; flex: 2; }
        .btn-reject { background: #fee2e2; color: #991b1b; flex: 1; }
        .btn-back { background: #e2e8f0; color: #475569; }
        .badge { padding: 6px 14px; border-radius: 30px; font-size: 11px; font-weight: 800; text-transform: uppercase; }
        .badge-pending { background: #fef9c3; color: #a16207; }
        .badge-approved { background: #dcfce7; color: #15803d; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>TRACER ADMIN</h2>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Detail Pengajuan Reservasi</h2>
                <span class="badge badge-<?= strtolower($data['status']); ?>"><?= strtoupper($data['status']); ?></span>
            </div>

            <div class="card-body">
                <div class="info-grid">
                    <div class="info-group">
                        <h3>Identitas Pemohon</h3>
                        <p><?= htmlspecialchars($data['nama_peminjam']); ?></p>
                        <small style="color: var(--primary); font-weight: 700;"><?= htmlspecialchars($data['bu']); ?></small>
                    </div>
                    <div class="info-group">
                        <h3>Informasi Ruangan</h3>
                        <p style="color: #197b40; font-size: 16px;">
                            <?= htmlspecialchars($nama_ruangan_display); ?>
                        </p>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-group">
                        <h3>Jadwal & Sesi</h3>
                        <p>📅 <?= date('d F Y', strtotime($data['tanggal'])); ?></p>
                        <p style="color: var(--primary)">🕒 Sesi: <?= $data['waktu']; ?></p>
                    </div>
                    <div class="info-group">
                        <h3>Subjek / Agenda</h3>
                        <p><?= htmlspecialchars($data['subject']); ?></p>
                    </div>
                </div>

                <div style="height: 1px; background: #f1f5f9; margin: 20px 0;"></div>

                <div class="info-group">
                    <h3>Catatan Tambahan User</h3>
                    <div class="notes-box">
                        <?= !empty($catatan_user_clean) ? nl2br(htmlspecialchars($catatan_user_clean)) : '<em>Tidak ada catatan tambahan.</em>' ?>
                    </div>
                </div>
            </div>

            <?php if ($data['status'] == 'pending') : ?>
            <form method="POST" action="">
                <div class="approval-panel">
                    <h3>Keputusan Admin</h3>
                    <textarea name="admin_comment" id="admin_comment" placeholder="Berikan alasan jika ditolak, atau instruksi tambahan jika disetujui..."><?= htmlspecialchars($data['admin_comment'] ?? ''); ?></textarea>

                    <input type="hidden" name="status" id="status_val" value="">
                    
                    <div class="btn-group">
                        <a href="admin_booking.php" class="btn btn-back">Kembali</a>
                        
                        <button type="submit" name="update_status" class="btn btn-reject" onclick="setStatus('rejected')">
                            Tolak Booking
                        </button>
                        
                        <button type="submit" name="update_status" class="btn btn-approve" onclick="setStatus('approved')">
                            Setujui Booking
                        </button>
                    </div>
                </div>
            </form>
            <?php else : ?>
            <div class="approval-panel" style="background: #fff; border-top: 2px solid #f1f5f9;">
                <h3>Balasan Admin:</h3>
                <p style="font-style: italic; color: #64748b; margin-top: 10px; border-left: 3px solid #cbd5e1; padding-left: 10px;">
                    "<?= !empty($data['admin_comment']) ? htmlspecialchars($data['admin_comment']) : 'Tidak ada catatan admin.'; ?>"
                </p>
                <br>
                <a href="admin_booking.php" class="btn btn-back" style="width: 100%;">Kembali ke Daftar</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function setStatus(val) {
            const comment = document.getElementById('admin_comment').value;
            if (val === 'rejected' && comment.trim() === "") {
                alert("Mohon berikan alasan penolakan pada kolom komentar.");
                event.preventDefault();
                return;
            }
            
            if(confirm('Apakah Anda yakin ingin ' + (val === 'approved' ? 'MENYETUJUI' : 'MENOLAK') + ' pengajuan ini?')) {
                document.getElementById('status_val').value = val;
            } else {
                event.preventDefault();
            }
        }
    </script>
</body>
</html>