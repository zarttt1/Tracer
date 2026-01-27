<?php
include 'koneksi.php';
session_start();

// 1. Proteksi Halaman Admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_admin.php");
    exit;
}

// 2. Ambil ID dari URL
if (!isset($_GET['id'])) {
    header("Location: admin_booking.php");
    exit();
}
$booking_id = mysqli_real_escape_string($conn, $_GET['id']);

// 3. Proses Update Status (Approve/Reject)
if (isset($_POST['update_status'])) {
    $booking_id = mysqli_real_escape_string($conn, $_POST['id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $comment = mysqli_real_escape_string($conn, $_POST['admin_comment']);

    $sql = "UPDATE bookings SET status = '$status', admin_comment = '$comment' WHERE id = '$booking_id'";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Status berhasil diperbarui!'); window.location='admin_booking.php';</script>";
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
$nama_ruangan_display = $data['nama_ruangan']; 

if (preg_match('/\[GABUNG RUANGAN: (.*?)\]/', $catatan_full, $matches)) {
    $nama_ruangan_display = $matches[1]; 
    $catatan_user_clean = trim(str_replace($matches[0], '', $catatan_full)); 
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
        
        .navbar { background: var(--primary); padding: 0 8%; height: 70px; display: flex; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); color: white; position: sticky; top: 0; z-index: 100; }
        .navbar h2 { font-size: 20px; font-weight: 800; }

        /* --- LAYOUT DUA KOLOM --- */
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 25px; }

        .card { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; height: fit-content; }
        .card-header { padding: 25px 30px; border-bottom: 2px solid #f8fafc; display: flex; justify-content: space-between; align-items: center; }
        .card-header h2 { font-size: 16px; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; }
        .card-body { padding: 30px; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .info-group h3 { color: #94a3b8; font-size: 10px; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 1px; }
        .info-group p { font-size: 15px; font-weight: 600; line-height: 1.4; }

        .notes-box { background: #f8fafc; border-left: 4px solid var(--primary); padding: 15px; border-radius: 8px; margin-top: 10px; font-size: 14px; color: #475569; line-height: 1.6; }

        /* PANEL AKSI (Kanan) */
        .approval-panel { padding: 30px; }
        .panel-title { font-size: 16px; font-weight: 800; margin-bottom: 20px; color: var(--text-main); border-bottom: 1px solid #eee; padding-bottom: 10px; }
        
        textarea { width: 100%; padding: 15px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 14px; min-height: 120px; margin-bottom: 20px; outline: none; transition: 0.3s; }
        textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(25, 123, 64, 0.1); }

        .btn-group { display: flex; flex-direction: column; gap: 12px; }
        .btn { padding: 14px; border: none; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: 700; transition: 0.3s; text-decoration: none; display: flex; align-items: center; justify-content: center; width: 100%; }
        
        .btn-approve { background: var(--primary); color: white; }
        .btn-approve:hover { background: #146333; transform: translateY(-2px); }
        
        .btn-reject { background: #fff; color: #991b1b; border: 2px solid #fee2e2; }
        .btn-reject:hover { background: #fee2e2; }
        
        .btn-back { background: #e2e8f0; color: #475569; margin-top: 10px; }

        .badge { padding: 6px 14px; border-radius: 30px; font-size: 11px; font-weight: 800; text-transform: uppercase; }
        .badge-pending { background: #fef9c3; color: #a16207; }
        .badge-approved { background: #dcfce7; color: #15803d; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }

        @media (max-width: 900px) {
            .container { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>TRACER ADMIN</h2>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Informasi Reservasi</h2>
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
                        <p style="color: #197b40; font-size: 16px;"><?= htmlspecialchars($nama_ruangan_display); ?></p>
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
        </div>

        <div class="card">
            <div class="approval-panel">
                <h3 class="panel-title">Keputusan Admin</h3>
                
                <?php if ($data['status'] == 'pending') : ?>
                <form method="POST" action="">
                    <input type="hidden" name="id" value="<?= $data['id']; ?>">
                    <textarea name="admin_comment" id="admin_comment" placeholder="Berikan alasan jika ditolak, atau instruksi tambahan jika disetujui..."><?= htmlspecialchars($data['admin_comment'] ?? ''); ?></textarea>
                    
                    <input type="hidden" name="status" id="status_val" value="">
                    
                    <div class="btn-group">
                        <button type="submit" name="update_status" class="btn btn-approve" onclick="setStatus('approved')">
                            Setujui Booking
                        </button>
                        
                        <button type="submit" name="update_status" class="btn btn-reject" onclick="setStatus('rejected')">
                            Tolak Booking
                        </button>
                        
                        <a href="admin_booking.php" class="btn btn-back">Kembali</a>
                    </div>
                </form>
                <?php else : ?>
                <div class="info-group">
                    <h3>Komentar / Balasan:</h3>
                    <p style="font-style: italic; color: #64748b; margin-top: 10px; border-left: 3px solid #cbd5e1; padding-left: 10px;">
                        "<?= !empty($data['admin_comment']) ? htmlspecialchars($data['admin_comment']) : 'Tidak ada catatan admin.'; ?>"
                    </p>
                    <a href="admin_booking.php" class="btn btn-back" style="margin-top: 25px;">Kembali ke Daftar</a>
                </div>
                <?php endif; ?>
            </div>
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