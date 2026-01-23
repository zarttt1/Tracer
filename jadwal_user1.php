<?php 
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'user') {
    header("Location: login_admin.php");
    exit;
}
include 'koneksi.php'; 
date_default_timezone_set('Asia/Jakarta');

$tanggal_dipilih = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$display_date = date('d F Y', strtotime($tanggal_dipilih));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Jadwal Detail - TRACER</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root { --primary: #197b40; --bg: #f1f5f9; --shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Inter", sans-serif; }
        body { background: var(--bg); color: #1e293b; }
        
        nav { background: var(--primary); padding: 0 5%; height: 65px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; box-shadow: var(--shadow); }
        .logo h1 { color: white; font-size: 20px; font-weight: 800; }
        .nav-links { display: flex; align-items: center; gap: 20px; }
        .nav-links a { color: #e2e8f0; text-decoration: none; font-size: 14px; font-weight: 600; }
        .nav-links a.active { color: white; border-bottom: 2px solid white; padding-bottom: 5px; }
        .btn-logout { background: #ef4444; color: white !important; padding: 8px 15px; border-radius: 6px; text-decoration: none; }

        .hero { background: var(--primary); color: white; padding: 40px 5% 80px; text-align: center; }
        .container { padding: 0 5%; max-width: 1200px; margin: -40px auto 40px; }
        .card { background: white; border-radius: 15px; padding: 25px; box-shadow: var(--shadow); border: 1px solid #e2e8f0; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: #64748b; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }

        .badge-status { background: #dcfce7; color: #166534; padding: 5px 12px; border-radius: 20px; font-weight: 700; font-size: 11px; }
        .btn-view { background: transparent; color: var(--primary); border: 1px solid var(--primary); padding: 6px 12px; border-radius: 6px; cursor: pointer; font-weight: 600; transition: 0.2s; }
        .btn-view:hover { background: var(--primary); color: white; }

        /* Modal styling sesuai image_6ada67.png */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 40px; border-radius: 25px; width: 90%; max-width: 600px; position: relative; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .grid-detail { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .label { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 5px; }
        .val { font-size: 16px; font-weight: 700; color: #1e293b; }
        .note-box { background: #f8fafc; padding: 15px; border-radius: 12px; border-left: 4px solid var(--primary); margin-top: 20px; }
        .admin-reply { background: #fffbeb; padding: 15px; border-radius: 12px; border: 1px solid #fde68a; margin-top: 15px; }
    </style>
</head>
<body>

    <nav>
        <div class="logo"><h1>TRACER</h1></div>
        <div class="nav-links">
            <a href="user1_dashboard.php">Dashboard</a>
            <a href="jadwal_user1.php" class="active">Jadwal</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="hero"><h2>Jadwal Penggunaan Ruangan</h2></div>

    <div class="container">
        <div class="card">
            <input type="date" value="<?= $tanggal_dipilih ?>" onchange="location='?date='+this.value" style="margin-bottom:20px; padding:10px; border-radius:8px; border:1px solid #ddd;">
            
            <table>
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Ruangan</th>
                        <th>Kegiatan</th>
                        <th>Pemesan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Query JOIN sesuai struktur database Anda
                    $sql = "SELECT b.*, r.nama_ruangan, b.nama_peminjam
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.id 
        WHERE b.tanggal = '$tanggal_dipilih' AND b.status = 'approved'
        ORDER BY FIELD(b.waktu, 'Pagi', 'Siang', 'Full Day')";
                    $res = mysqli_query($conn, $sql);
                    while($row = mysqli_fetch_assoc($res)): ?>
                    <tr>
                        <td><b><?= $row['waktu'] ?></b></td>
                        <td><?= $row['nama_ruangan'] ?></td>
                        <td><?= $row['subject'] ?></td>
                        <td><?= $row['nama_peminjam'] ?></td>
                        <td><span class="badge-status">Confirmed</span></td>
                        <td>
                            <button class="btn-view" onclick="openDetail(<?= htmlspecialchars(json_encode($row)) ?>)">Detail</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="modalDetail" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="det-subject" style="color: var(--primary);"></h2>
            <span class="badge-status">APPROVED</span>
        </div>

        <div class="grid-detail">
            <div>
                <div class="label">Business Unit (BU)</div>
                <div class="val" id="det-bu"></div>
            </div>
            <div>
                <div class="label">Nama Pemohon</div>
                <div class="val" id="det-nama_peminjam"></div>
            </div>
            <div>
                <div class="label">Ruangan</div>
                <div class="val" id="det-ruangan" style="color: var(--primary);"></div>
            </div>
            <div>
                <div class="label">Waktu Pelaksanaan</div>
                <div class="val">📅 <span id="det-tgl"></span></div>
                <div style="font-size:14px; color:#64748b;">🕒 Sesi: <span id="det-sesi"></span></div>
            </div>
        </div>

        <div class="label">Catatan Pengguna</div>
        <div class="note-box" id="det-catatan"></div>

        <div id="box-balasan" class="admin-reply" style="margin-top: 20px;">
            <div class="label" style="color: #92400e;">Balasan Admin</div>
            <div style="font-size: 14px;" id="det-balasan-teks"></div>
        </div>

        <button type="button" onclick="closeModal()" style="margin-top:30px; width:100%; padding:15px; border:none; border-radius:12px; background:#f1f5f9; cursor:pointer; font-weight:700; color:#1e293b;">
            ← Kembali ke Jadwal
        </button>
    </div>
</div>

<script>
function openDetail(data) {
    // 1. Isi Data ke Modal
    // Pastikan ID 'det-nama_peminjam' sudah ada di HTML modal kamu
    document.getElementById('det-nama_peminjam').innerText = data.nama_peminjam || '-';
    
    document.getElementById('det-subject').innerText = data.subject || '-';
    document.getElementById('det-ruangan').innerText = data.nama_ruangan || '-';
    document.getElementById('det-tgl').innerText = data.tanggal || '-';
    document.getElementById('det-sesi').innerText = data.waktu || '-';
    document.getElementById('det-catatan').innerText = data.catatan || '-';
    document.getElementById('det-bu').innerText = data.bu || 'GGP';

    // 2. Logika Sembunyikan Balasan Admin jika Kosong
    const boxBalasan = document.getElementById('box-balasan');
    const teksBalasan = document.getElementById('det-balasan-teks');
    
    // Mengecek apakah ada catatan_admin dari database
    if (data.catatan_admin && data.catatan_admin.trim() !== "") {
        teksBalasan.innerText = data.catatan_admin;
        boxBalasan.style.display = 'block';
    } else {
        boxBalasan.style.display = 'none';
    }

    // 3. Tampilkan Modal
    document.getElementById('modalDetail').style.display = 'flex';
}

// Fungsi Tutup Modal agar tombol 'Kembali ke Jadwal' bisa diklik
function closeModal() {
    document.getElementById('modalDetail').style.display = 'none';
}

// Menutup modal jika user klik di luar area putih modal
window.onclick = function(event) {
    const modal = document.getElementById('modalDetail');
    if (event.target == modal) {
        closeModal();
    }
}
</script>
</body>
</html>