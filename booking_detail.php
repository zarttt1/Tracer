<?php
include 'koneksi.php';
session_start();

// 1. Ambil ID dari URL
if (!isset($_GET['id'])) {
    header("Location: jadwal.php");
    exit();
}
$booking_id = mysqli_real_escape_string($conn, $_GET['id']);

// 2. Tarik Data Detail Booking (Join dengan tabel rooms)
// Pastikan query mengambil kolom admin_comment
$sql_detail = "SELECT b.*, r.nama_ruangan
               FROM bookings b 
               JOIN rooms r ON b.room_id = r.id 
               WHERE b.id = '$booking_id'";
$result = mysqli_query($conn, $sql_detail);
$data = mysqli_fetch_assoc($result);

// Jika data tidak ditemukan
if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='jadwal.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Detail Booking - TRACER</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary: #197b40;
            --bg: #f8fafc;
            --text-muted: #64748b;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Inter", sans-serif; }
        body { background: var(--bg); color: #1e293b; line-height: 1.6; }
        
        nav { background: var(--primary); padding: 1rem 8%; color: white; display: flex; justify-content: space-between; align-items: center; }
        .logo { text-decoration: none; color: white; font-weight: 800; font-size: 22px; }
        
        .header-section { background: var(--primary); color: white; padding: 3rem 8% 6rem; text-align: center; }
        
        .container { padding: 0 8%; max-width: 900px; margin: -4rem auto 3rem; }
        
        .card { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); overflow: hidden; border: 1px solid #e2e8f0; }
        
        .card-header { padding: 25px 30px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        .card-header h3 { font-size: 18px; font-weight: 700; color: var(--primary); }

        .card-body { padding: 40px; }
        
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 30px; margin-bottom: 30px; }
        
        .info-item label { display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px; }
        .info-item p { font-size: 16px; font-weight: 600; color: #334155; }
        
        .divider { height: 1px; background: #f1f5f9; margin: 20px 0; }

        /* Box untuk catatan user */
        .notes-box { background: #f8fafc; padding: 20px; border-radius: 12px; border-left: 4px solid var(--primary); margin-top: 10px; font-size: 14px; color: #475569; }
        
        .badge { padding: 6px 16px; border-radius: 30px; font-size: 12px; font-weight: 800; text-transform: uppercase; }
        .badge-approved { background: #dcfce7; color: #15803d; }
        .badge-pending { background: #fef9c3; color: #a16207; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        
        .btn-back { display: inline-block; padding: 12px 25px; background: #f1f5f9; color: #1e293b; text-decoration: none; border-radius: 10px; font-weight: 700; margin-top: 20px; transition: 0.3s; }
        .btn-back:hover { background: #e2e8f0; }

        @media (max-width: 600px) { .info-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <nav>
        <a href="index.php" class="logo">TRACER</a>
        <div style="font-size: 14px;">
            <?= isset($_SESSION['nama_user']) ? $_SESSION['nama_user'] : 'Mode Tamu' ?>
        </div>
    </nav>

    <div class="header-section">
        <h1>Detail Reservasi</h1>
        <p style="opacity: 0.8;">ID Booking: #<?= $data['id'] ?></p>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3><?= htmlspecialchars($data['subject']) ?></h3> 
                <span class="badge badge-<?= strtolower($data['status']) ?>"><?= $data['status'] ?></span>
            </div>
            
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <label>Business Unit (BU)</label>
                        <p><?= htmlspecialchars($data['bu']) ?></p>
                    </div>
                    <div class="info-item">
                        <label>Nama Pemohon</label>
                        <p><?= htmlspecialchars($data['nama_peminjam']) ?></p>
                    </div>
                    <div class="info-item">
                        <label>Ruangan</label>
                        <p style="color: var(--primary)"><?= htmlspecialchars($data['nama_ruangan']) ?></p>
                    </div>
                    <div class="info-item">
                        <label>Waktu Pelaksanaan</label>
                        <p>📅 <?= date('d M Y', strtotime($data['tanggal'])) ?></p>
                        <p style="font-size: 14px; color: var(--text-muted)">🕒 Sesi: <?= $data['waktu'] ?></p>
                    </div>
                </div>

                <div class="divider"></div>

                <div class="info-item">
                    <label>Catatan Pengguna</label>
                    <div class="notes-box">
                        <?= !empty($data['catatan']) ? nl2br(htmlspecialchars($data['catatan'])) : '<em>Tidak ada catatan tambahan.</em>' ?>
                    </div>
                </div>

                <div style="margin-top: 30px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
                    <a href="jadwal.php" class="btn-back">← Kembali ke Jadwal</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>