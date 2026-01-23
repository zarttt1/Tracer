<?php
session_start();

// 1. Proteksi Halaman: Cek apakah sudah login DAN pastikan role-nya adalah 'admin'
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    // Jika bukan admin (atau belum login), lempar ke halaman login utama
    header("Location: login_admin.php");
    exit;
}

// 2. Sertakan koneksi setelah proteksi berhasil
include 'koneksi.php';
$count_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'"))['total'];
$count_approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE status = 'approved'"))['total'];
$count_rooms = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM rooms"))['total'];

// Tanggal Hari Ini
$today = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard - TRACER</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary: #27ae60;
            --primary-dark: #1e8449;
            --bg: #f5f7fa;
            --text-main: #2c3e50;
            --warning: #f39c12;
            --blue: #3498db;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Inter", sans-serif; }
        body { background: var(--bg); color: var(--text-main); }

        /* --- NAVBAR --- */
        .navbar {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            padding: 0 8%;
            height: 70px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            color: white;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .nav-left { display: flex; align-items: center; gap: 40px; }
        .navbar h2 { font-size: 20px; font-weight: 800; letter-spacing: 1px; }
        .nav-menu { display: flex; list-style: none; gap: 10px; }
        .nav-menu a { color: rgba(255, 255, 255, 0.8); text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 8px; transition: 0.3s; }
        .nav-menu a:hover, .nav-menu a.active { color: white; background: rgba(255, 255, 255, 0.2); }
        
        .navbar-user { display: flex; align-items: center; gap: 15px; }
        .logout-btn { background: #e74c3c; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px; text-decoration: none; transition: 0.3s; }
        .logout-btn:hover { background: #c0392b; }

        /* --- LAYOUT --- */
        .container { padding: 30px 8%; max-width: 1400px; margin: 0 auto; }
        
        /* --- QUICK ACTIONS --- */
        .quick-actions {
            margin-bottom: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .card-action {
            background: white;
            padding: 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 20px;
            text-decoration: none;
            color: inherit;
            border: 2px solid transparent;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
            transition: 0.3s;
        }
        .card-action:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        }
        .card-action .icon {
            width: 50px;
            height: 50px;
            background: rgba(39, 174, 96, 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            border-radius: 10px;
        }
        .card-action .info h4 { font-size: 16px; margin-bottom: 4px; }
        .card-action .info p { font-size: 13px; color: #64748b; }

        /* --- STATS --- */
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); border: 1px solid #edf2f7; text-align: left; }
        .stat-card h3 { color: #94a3b8; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .stat-number { font-size: 32px; font-weight: 800; }
        .pending .stat-number { color: var(--warning); }
        .approved .stat-number { color: var(--primary); }
        .rooms .stat-number { color: var(--blue); }

        /* --- TABLES --- */
        .card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 30px; border: 1px solid #edf2f7; }
        .card-header { background: #f8fafc; padding: 20px 25px; border-bottom: 1px solid #edf2f7; font-weight: 700; display: flex; justify-content: space-between; align-items: center; }
        .card-body { padding: 15px 25px 25px; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px; background: #fff; border-bottom: 2px solid #f1f5f9; font-size: 11px; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.5px; }
        td { padding: 15px 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        
        .badge { padding: 6px 12px; border-radius: 30px; font-size: 11px; font-weight: 700; }
        .badge-approved { background: #dcfce7; color: #15803d; }
        .badge-pending { background: #fef9c3; color: #a16207; }

        .btn-info { padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 700; transition: 0.2s; border: 1px solid #e2e8f0; color: var(--text-main); }
        .btn-info:hover { background: #f1f5f9; }
        .btn-primary { background: var(--primary); color: white; border: none; }
        .btn-primary:hover { background: var(--primary-dark); }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="nav-left">
            <h2>TRACER</h2>
            <ul class="nav-menu">
                <li><a href="admin_dashboard.php" class="active">Dashboard</a></li>
                <li><a href="admin_booking.php">Approval</a></li>
                <li><a href="admin_room.php">Fasilitas</a></li>
            </ul>
        </div>
        <div class="navbar-user">
            <span style="font-size: 13px;">👋 Halo, <strong><?php echo $_SESSION['admin_name']; ?></strong></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="quick-actions">
            <a href="admin_create_booking.php" class="card-action">
                <div class="icon">⚡</div>
                <div class="info">
                    <h4>Booking Langsung</h4>
                    <p>Input reservasi instan tanpa proses approval</p>
                </div>
            </a>
            <a href="admin_room.php" class="card-action">
                <div class="icon">🏫</div>
                <div class="info">
                    <h4>Kelola Ruangan</h4>
                    <p>Tambah atau edit status fasilitas ruangan</p>
                </div>
            </a>
        </div>

        <div class="stats">
            <div class="stat-card pending">
                <h3>Menunggu Approval</h3>
                <div class="stat-number"><?php echo $count_pending; ?></div>
            </div>
            <div class="stat-card approved">
                <h3>Total Disetujui</h3>
                <div class="stat-number"><?php echo $count_approved; ?></div>
            </div>
            <div class="stat-card rooms">
                <h3>Total Ruangan</h3>
                <div class="stat-number"><?php echo $count_rooms; ?></div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px;">
            <div class="card">
                <div class="card-header">
                    <span>📅 Agenda Berjalan Hari Ini</span>
                    <span style="font-size: 12px; font-weight: 400; color: #64748b;"><?php echo date('d M Y'); ?></span>
                </div>
                <div class="card-body">
                    <table>
                        <thead>
                            <tr>
                                <th>Sesi</th>
                                <th>Ruangan</th>
                                <th>Pemesan</th>
                                <th>Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_today = "SELECT b.*, r.nama_ruangan FROM bookings b 
                                          JOIN rooms r ON b.room_id = r.id 
                                          WHERE b.tanggal = '$today' AND b.status = 'approved'
                                          ORDER BY b.waktu ASC"; 
                            $res_today = mysqli_query($conn, $sql_today);
                            
                            if(mysqli_num_rows($res_today) > 0) {
                                while($row = mysqli_fetch_assoc($res_today)) { ?>
                                <tr>
                                    <td><strong><?php echo $row['waktu']; ?></strong></td>
                                    <td style="color: var(--primary); font-weight: 600;"><?php echo $row['nama_ruangan']; ?></td>
                                    <td><?php echo $row['nama_peminjam']; ?></td>
                                    <td><a href="admin_booking_detail.php?id=<?php echo $row['id']; ?>" class="btn-info">Detail</a></td>
                                </tr>
                            <?php } 
                            } else {
                                echo "<tr><td colspan='4' style='text-align:center; padding: 40px; color:#94a3b8;'>Tidak ada agenda hari ini.</td></tr>";
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">🚀 Antrean Terbaru</div>
                <div class="card-body">
                    <table>
                        <thead>
                            <tr>
                                <th>Pemesan</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_pending = "SELECT b.* FROM bookings b 
                                            WHERE b.status = 'pending' 
                                            ORDER BY b.id DESC LIMIT 5";
                            $res_pending = mysqli_query($conn, $sql_pending);

                            if(mysqli_num_rows($res_pending) > 0) {
                                while($p = mysqli_fetch_assoc($res_pending)) { ?>
                                <tr>
                                    <td>
                                        <b><?php echo $p['nama_peminjam']; ?></b><br>
                                        <small style="color: #64748b"><?php echo $p['waktu']; ?></small>
                                    </td>
                                    <td><?php echo date('d/m', strtotime($p['tanggal'])); ?></td>
                                    <td>
                                        <a href="admin_booking_detail.php?id=<?php echo $p['id']; ?>" class="btn-info btn-primary">Cek</a>
                                    </td>
                                </tr>
                            <?php }
                            } else {
                                echo "<tr><td colspan='3' style='text-align:center; padding: 40px; color:#94a3b8;'>Bersih! Tidak ada antrean.</td></tr>";
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>