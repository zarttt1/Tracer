<?php
include 'koneksi.php';
session_start();

// 1. Proteksi Halaman Admin
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login_admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin - Kelola Ruangan | TRACER</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root { --primary: #27ae60; --bg: #f5f7fa; --text-main: #2c3e50; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Inter", sans-serif; }
        body { background: var(--bg); color: var(--text-main); }

        /* --- NAVBAR --- */
        .navbar { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); padding: 0 8%; height: 70px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); color: white; position: sticky; top: 0; z-index: 100; }
        .nav-left { display: flex; align-items: center; gap: 40px; }
        .navbar h2 { font-size: 20px; font-weight: 800; letter-spacing: 1px; }
        .nav-menu { display: flex; list-style: none; gap: 20px; }
        .nav-menu a { color: rgba(255, 255, 255, 0.8); text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 12px; border-radius: 6px; transition: 0.3s; }
        .nav-menu a:hover, .nav-menu a.active { color: white; background: rgba(255, 255, 255, 0.2); }
        .logout-btn { background: #e74c3c; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; text-decoration: none; font-size: 13px; }

        /* --- CONTENT --- */
        .container { padding: 40px 8%; max-width: 1400px; margin: 0 auto; }
        .card { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); overflow: hidden; }
        .card-header { padding: 25px; border-bottom: 1px solid #edf2f7; display: flex; justify-content: space-between; align-items: center; }
        .card-header h3 { font-size: 18px; font-weight: 700; }

        .btn-add { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 13px; transition: 0.3s; text-decoration: none; }
        .btn-add:hover { background: #1e8449; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 15px 25px; text-align: left; font-size: 11px; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.5px; }
        td { padding: 20px 25px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }

        .badge { padding: 6px 14px; border-radius: 30px; font-size: 11px; font-weight: 800; }
        .badge-active { background: #dcfce7; color: #15803d; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }

        .btn-edit { background: #f1f5f9; color: #475569; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 700; border: 1px solid #e2e8f0; transition: 0.2s; }
        .btn-edit:hover { background: #e2e8f0; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="nav-left">
            <h2>TRACER</h2>
            <ul class="nav-menu">
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="admin_booking.php">Approval</a></li>
                <li><a href="admin_room.php" class="active">Fasilitas</a></li>
            </ul>
        </div>
        <div class="navbar-user">
            <span style="margin-right: 15px; font-size: 14px; font-weight: 600;">Halo, <?= $_SESSION['admin_name']; ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3>🏢 Kelola Ruangan Training</h3>
                <a href="tambah_room.php" class="btn-add">+ Tambah Ruang</a>
            </div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Ruangan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $rooms = mysqli_query($conn, "SELECT * FROM rooms ORDER BY nama_ruangan ASC");
                        if(mysqli_num_rows($rooms) > 0) {
                            while($r = mysqli_fetch_assoc($rooms)) {
                                // Logika penentuan label status
                                // Asumsi kolom di DB adalah 'status_aktif' (1 untuk aktif, 0 untuk non-aktif)
                                $status_label = ($r['status_aktif'] == 1) ? 'Aktif' : 'Non-Aktif';
                                $status_class = ($r['status_aktif'] == 1) ? 'badge-active' : 'badge-inactive';
                        ?>
                        <tr>
                            <td><b><?= htmlspecialchars($r['nama_ruangan']) ?></b></td>
                            <td><span class="badge <?= $status_class ?>"><?= $status_label ?></span></td>
                            <td>
                                <a href="admin_room_edit.php?id=<?= $r['id'] ?>" class="btn-edit">Edit</a>
                            </td>
                        </tr>
                        <?php 
                            } 
                        } else {
                            echo "<tr><td colspan='5' style='text-align:center; padding: 40px; color: #94a3b8;'>Belum ada data ruangan.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>