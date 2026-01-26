<?php
// 1. Pastikan session_start adalah baris paling pertama
session_start();
include 'koneksi.php';

// 2. Proteksi Halaman Admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login_admin.php");
    exit();
}

// 3. Logika Filter Status
$filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// 4. Query Ambil Data
$query_base = "SELECT b.*, r.nama_ruangan 
               FROM bookings b 
               JOIN rooms r ON b.room_id = r.id";

if ($filter !== 'all') {
    $filter_safe = mysqli_real_escape_string($conn, $filter);
    $query_base .= " WHERE b.status = '$filter_safe'";
}

$query_base .= " ORDER BY b.id DESC"; 
$result = mysqli_query($conn, $query_base);
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin - Approval Booking | TRACER</title>
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
        .filter-tabs { display: flex; gap: 10px; margin-bottom: 25px; background: white; padding: 10px; border-radius: 12px; width: fit-content; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); }
        .filter-btn { padding: 10px 20px; text-decoration: none; border-radius: 8px; font-size: 13px; font-weight: 700; color: #7f8c8d; transition: 0.3s; }
        .filter-btn.active { background: var(--primary); color: white; }

        .card { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); overflow: hidden; }
        .card-header { padding: 25px; border-bottom: 1px solid #edf2f7; }
        .card-header h3 { font-size: 18px; font-weight: 700; }

        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 15px 25px; text-align: left; font-size: 11px; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.5px; }
        td { padding: 20px 25px; border-bottom: 1px solid #f1f5f9; font-size: 14px; vertical-align: middle; }

        /* --- BADGES --- */
        .badge { padding: 6px 14px; border-radius: 30px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
        .badge-pending { background: #fef9c3; color: #a16207; }
        .badge-approved { background: #dcfce7; color: #15803d; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }

        .btn-action { display: inline-block; padding: 8px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 700; transition: 0.2s; border: 1px solid #e2e8f0; margin-right: 5px; }
        .btn-detail { background: #f1f5f9; color: #475569; }
        .btn-edit { background: #fff7ed; color: #c2410c; border-color: #fdba74; }
        
        .btn-detail:hover { background: var(--primary); color: white; border-color: var(--primary); }
        .btn-edit:hover { background: #ea580c; color: white; border-color: #ea580c; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="nav-left">
            <h2>TRACER</h2>
            <ul class="nav-menu">
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="admin_booking.php" class="active">Approval</a></li>
                <li><a href="admin_room.php">Fasilitas</a></li>
            </ul>
        </div>
        <div class="navbar-user">
            <span style="margin-right: 15px; font-size: 14px; font-weight: 600;">
                Halo, <?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin'; ?>
            </span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="filter-tabs">
            <a href="admin_booking.php?status=all" class="filter-btn <?php echo $filter == 'all' ? 'active' : ''; ?>">Semua</a>
            <a href="admin_booking.php?status=pending" class="filter-btn <?php echo $filter == 'pending' ? 'active' : ''; ?>">Pending</a>
            <a href="admin_booking.php?status=approved" class="filter-btn <?php echo $filter == 'approved' ? 'active' : ''; ?>">Approved</a>
            <a href="admin_booking.php?status=rejected" class="filter-btn <?php echo $filter == 'rejected' ? 'active' : ''; ?>">Rejected</a>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Daftar Pengajuan Reservasi</h3>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Pemohon / BU</th>
                            <th>Ruangan</th>
                            <th>Tanggal & Sesi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #2c3e50;"><?php echo htmlspecialchars($row['nama_peminjam']); ?></div>
                                        <div style="font-size: 12px; color: #7f8c8d;"><?php echo htmlspecialchars($row['bu']); ?></div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--primary);"><?php echo htmlspecialchars($row['nama_ruangan']); ?></div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600;"><?php echo date('d M Y', strtotime($row['tanggal'])); ?></div>
                                        <div style="font-size: 12px; color: #7f8c8d;">Sesi: <?php echo htmlspecialchars($row['waktu']); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="admin_booking_detail.php?id=<?php echo $row['id']; ?>" class="btn-action btn-detail">Kelola</a>
                                        <a href="admin_edit_booking.php?id=<?php echo $row['id']; ?>" class="btn-action btn-edit">Edit</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 50px; color: #94a3b8;">Tidak ada data booking yang ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>