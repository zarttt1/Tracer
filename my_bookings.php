<?php
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');
$current_page = basename($_SERVER['PHP_SELF']);

// --- LOGIKA PEMBATALAN BOOKING ---
if (isset($_GET['cancel_id'])) {
    $id_to_cancel = mysqli_real_escape_string($conn, $_GET['cancel_id']);
    // Hanya izinkan pembatalan jika status masih pending
    $query_cancel = "DELETE FROM bookings WHERE id = '$id_to_cancel' AND status = 'pending'";
    if (mysqli_query($conn, $query_cancel)) {
        echo "<script>alert('Booking berhasil dibatalkan.'); window.location='my_bookings.php';</script>";
    } else {
        echo "<script>alert('Gagal membatalkan booking.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Booking Saya - TRACER</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary: #197b40;
            --primary-dark: #125a2f;
            --bg: #f8fafc;
            --white: #ffffff;
            --danger: #dc2626;
            --border: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Inter", sans-serif; }
        body { background: var(--bg); color: var(--text-main); line-height: 1.6; }

        /* --- NAVBAR --- */
        nav {
            background: var(--primary);
            padding: 0 8%;
            height: 65px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .logo { text-decoration: none; color: white; }
        .logo h1 { font-size: 22px; font-weight: 800; letter-spacing: -0.5px; }
        
        .nav-links { display: flex; align-items: center; gap: 10px; }
        .nav-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: 0.3s;
        }
        .nav-links a:hover { color: white; background: rgba(255, 255, 255, 0.1); }
        .nav-links a.active { color: white; background: rgba(255, 255, 255, 0.2); }
        
        .btn-login-admin { background: white !important; color: var(--primary) !important; margin-left: 10px; border-radius: 8px; padding: 8px 16px; font-weight: 600; }

        /* --- HEADER --- */
        .header-section {
            background: var(--primary);
            color: white;
            padding: 40px 8% 80px 8%;
        }
        .header-section h1 { font-size: 28px; font-weight: 800; }

        /* --- CONTENT --- */
        .container { padding: 0 8%; max-width: 1400px; margin: -50px auto 40px auto; }
        .card { 
            background: var(--white); 
            border-radius: 16px; 
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); 
            border: 1px solid var(--border); 
            overflow: hidden;
        }
        .card-header { 
            padding: 24px 30px; 
            border-bottom: 1px solid var(--border);
            display: flex; 
            justify-content: space-between; 
            align-items: center;
        }
        
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th { 
            text-align: left; padding: 16px 24px; background: #f8fafc; 
            color: var(--text-muted); font-size: 12px; text-transform: uppercase; 
            font-weight: 700; border-bottom: 2px solid var(--border);
        }
        td { padding: 20px 24px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:hover td { background-color: #fcfdfc; }

        .room-name { color: var(--primary); font-weight: 700; font-size: 15px; display: block; }
        .booking-time { font-size: 13px; color: var(--text-muted); margin-top: 4px; display: block; }

        /* --- BADGES --- */
        .badge { 
            padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 800; 
            display: inline-block; text-transform: uppercase;
        }
        .status-approved { background: #dcfce7; color: #15803d; }
        .status-pending { background: #fef9c3; color: #a16207; }
        .status-rejected { background: #fee2e2; color: #b91c1c; }

        /* --- BUTTONS --- */
        .btn-cancel { 
            color: var(--danger); background: #fef2f2; border: 1px solid #fee2e2;
            padding: 8px 16px; border-radius: 8px; font-weight: 700; 
            cursor: pointer; text-decoration: none; font-size: 12px; transition: 0.2s;
        }
        .btn-cancel:hover { background: var(--danger); color: white; }
        
        .btn-new { 
            background: var(--primary); color: white; padding: 12px 20px; 
            border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 14px; 
        }
    </style>
</head>
<body>
    <nav>
        <a href="index.php" class="logo"><h1>TRACER</h1></a>
        <div class="nav-links">
            <a href="index.php">Beranda</a>
            <a href="jadwal.php">Jadwal</a>
            <a href="my_bookings.php"class="active">My Booking</a>
            <a href="login_admin.php" class="btn-login-admin">Admin</a>
        </div>
    </nav>

    <div class="header-section">
        <h1>Riwayat Reservasi</h1>
        <p style="opacity: 0.8; margin-top: 8px">Pantau status pengajuan reservasi Anda secara berkala.</p>
    </div>

    <div class="container">
        <main class="card">
            <div class="card-header">
                <h3 style="font-size: 18px; font-weight: 700;">Daftar Booking Saya</h3>
                <a href="create_booking.php" class="btn-new">+ Booking Baru</a>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Ruangan & Waktu</th>
                            <th>BU & Pemohon</th>
                            <th>Status</th>
                            <th>Catatan Admin</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Menjalankan query untuk mengambil data
                        $query = "SELECT b.*, r.nama_ruangan FROM bookings b 
                                  JOIN rooms r ON b.room_id = r.id 
                                  ORDER BY b.id DESC";
                        $result = mysqli_query($conn, $query);

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $status_class = "status-" . strtolower($row['status']);
                        ?>
                        <tr>
                            <td>
                                <span class="room-name"><?php echo htmlspecialchars($row['nama_ruangan']); ?></span>
                                <span class="booking-time">
                                    📅 <?php echo date('d M Y', strtotime($row['tanggal'])); ?> | 
                                    🕒 Sesi: <?php echo htmlspecialchars($row['waktu']); ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-weight:700; font-size: 14px; color: var(--primary); text-transform: uppercase;">
                                    <?php echo htmlspecialchars($row['bu']); ?>
                                </div>
                                <div style="font-size: 13px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($row['nama_peminjam']); ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?php echo $status_class; ?>">
                                    <?php echo strtoupper($row['status']); ?>
                                </span>
                            </td>
                            <td style="font-style: italic; font-size: 13px; color: var(--text-muted);">
                                <?php echo !empty($row['admin_comment']) ? htmlspecialchars($row['admin_comment']) : "-"; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($row['status'] == 'pending') : ?>
                                    <a href="my_bookings.php?cancel_id=<?php echo $row['id']; ?>" 
                                       class="btn-cancel" 
                                       onclick="return confirm('Apakah Anda yakin ingin membatalkan pengajuan ini?')">
                                       Batalkan
                                    </a>
                                <?php else : ?>
                                    <span style="color: #cbd5e1; font-size: 12px; font-weight: 600;">TERPROSES</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align:center; padding: 60px; color: #94a3b8;'>Belum ada data reservasi.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>