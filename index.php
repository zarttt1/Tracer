<?php 
include 'koneksi.php'; 
date_default_timezone_set('Asia/Jakarta');

// Mengambil tanggal dari URL atau default hari ini
$selected_day   = isset($_GET['d']) ? (int)$_GET['d'] : (int)date('d');
$selected_month = isset($_GET['m']) ? $_GET['m'] : date('m');
$selected_year  = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');

$selected_full_date = "$selected_year-$selected_month-" . str_pad($selected_day, 2, "0", STR_PAD_LEFT);
$display_date = date('d F Y', strtotime($selected_full_date));

// Mengambil data untuk statistik (Stats Cards)
$total_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings"))['total'];
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'"))['total'];
$approved_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE status = 'approved'"))['total'];

$months = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TRACER - Training Center Room Reservation</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary: #197b40;
            --primary-dark: #125a2f;
            --primary-light: rgba(25, 123, 64, 0.1);
            --bg: #f8fafc;
            --white: #ffffff;
            --border: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Inter", sans-serif; }
        body { background: var(--bg); color: var(--text-main); line-height: 1.6; }

        /* --- NAVBAR --- */
        nav {
            background: var(--primary); padding: 0 8%; height: 65px;
            display: flex; justify-content: space-between; align-items: center; color: white;
            position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .logo { text-decoration: none; color: white; }
        .logo h1 { font-size: 22px; font-weight: 800; letter-spacing: -0.5px; }
        .nav-links { display: flex; align-items: center; gap: 10px; }
        .nav-links a {
            color: rgba(255, 255, 255, 0.7); text-decoration: none; padding: 8px 16px;
            border-radius: 8px; font-weight: 600; font-size: 14px; transition: all 0.3s ease;
        }
        .nav-links a:hover { color: white; background: rgba(255, 255, 255, 0.1); }
        .nav-links a.active { color: white; background: rgba(255, 255, 255, 0.2); }
        .btn-login-admin { background: white !important; color: var(--primary) !important; margin-left: 10px; border-radius: 8px; padding: 8px 16px; font-weight: 600; text-decoration: none; font-size: 14px; }

        /* --- HEADER --- */
        .header-section { background: var(--primary); color: white; padding: 40px 8% 100px 8%; text-align: left; }
        .header-section h1 { font-size: 28px; font-weight: 800; }
        .header-section p { opacity: 0.8; margin-top: 5px; }

        /* --- STATS GRID --- */
        .stats-grid { 
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; 
            padding: 0 8%; max-width: 1400px; margin: -60px auto 0;
        }
        .stat-card { 
            background: white; padding: 20px; border-radius: 16px; 
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); text-align: center; border-bottom: 4px solid #e2e8f0;
        }
        .stat-card .value { display: block; font-size: 28px; font-weight: 800; color: var(--primary); }
        .stat-card .label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; }
        .stat-card.pending { border-color: #f59e0b; }
        .stat-card.approved { border-color: #10b981; }

        /* --- CONTAINER --- */
        .container { padding: 40px 8%; max-width: 1400px; margin: 0 auto; }
        .main-grid { display: grid; grid-template-columns: 350px 1fr; gap: 30px; }
        .card { background: var(--white); border-radius: 20px; padding: 25px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); border: 1px solid var(--border); }
        
        /* --- CALENDAR --- */
        .filter-group { display: flex; gap: 10px; margin-bottom: 20px; }
        .select-custom { flex: 1; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 600; outline: none; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; }
        .cal-day { 
            aspect-ratio: 1/1; display: flex; align-items: center; justify-content: center; 
            font-size: 13px; font-weight: 600; cursor: pointer; border-radius: 8px; transition: 0.2s;
            background: #f8fafc;
        }
        .cal-day:hover { background: var(--primary-light); color: var(--primary); }
        .active-day { background: var(--primary) !important; color: white !important; box-shadow: 0 4px 10px rgba(25, 123, 64, 0.3); }

        /* --- ROOM CARDS --- */
        .room-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .room-card { 
            background: white; border: 1px solid var(--border); padding: 20px; 
            border-radius: 16px; transition: 0.3s;
        }
        .room-card:hover { transform: translateY(-5px); box-shadow: 0 12px 20px rgba(0,0,0,0.05); }
        .room-card h4 { margin-top: 10px; font-size: 18px; color: var(--text-main); }
        
        .status-tag { 
            display: inline-block; padding: 6px 12px; border-radius: 8px; 
            font-size: 11px; font-weight: 800; text-transform: uppercase; 
        }
        .available { background: #dcfce7; color: #166534; }
        .booked { background: #fee2e2; color: #991b1b; }
        .maintenance { background: #f1f5f9; color: #475569; }

        .btn-booking { 
            background: var(--primary); color: white; padding: 14px; border-radius: 12px; 
            text-decoration: none; font-weight: 700; display: block; margin-top: 20px; 
            text-align: center; transition: 0.3s; 
        }
        .btn-booking:hover { background: var(--primary-dark); }

        @media (max-width: 992px) {
            .main-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr; margin-top: -100px; }
        }
    </style>
</head>
<body>

    <nav>
        <a href="index.php" class="logo"><h1>TRACER</h1></a>
        <div class="nav-links">
            <a href="index.php" class="active">Beranda</a>
            <a href="jadwal.php">Jadwal</a>
            <a href="my_bookings.php">My Booking</a>
            <a href="login_admin.php" class="btn-login-admin">Admin</a>
        </div>
    </nav>

    <div class="header-section">
        <h1>Reservasi Ruangan Training Center</h1>
        <p>Sistem manajemen peminjaman ruangan yang cepat dan terintegrasi.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="value"><?= $total_count; ?></span>
            <span class="label">Total Reservasi</span>
        </div>
        <div class="stat-card pending">
            <span class="value"><?= $pending_count; ?></span>
            <span class="label">Menunggu Review</span>
        </div>
        <div class="stat-card approved">
            <span class="value"><?= $approved_count; ?></span>
            <span class="label">Disetujui</span>
        </div>
    </div>

    <div class="container">
        <div class="main-grid">
            <div class="card">
                <h3 style="margin-bottom:20px;">📅 Pilih Waktu</h3>
                <div class="filter-group">
                    <select class="select-custom" id="monthSelect" onchange="updateCalendar()">
                        <?php foreach ($months as $m_val => $m_name): ?>
                            <option value="<?= $m_val; ?>" <?= ($selected_month == $m_val) ? 'selected' : ''; ?>><?= $m_name; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="select-custom" id="yearSelect" onchange="updateCalendar()">
                        <?php for($y = date('Y'); $y <= date('Y')+1; $y++): ?>
                            <option value="<?= $y; ?>" <?= ($selected_year == $y) ? 'selected' : ''; ?>><?= $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="calendar-grid" id="cal"></div>
                <a href="create_booking.php?date=<?= $selected_full_date; ?>" class="btn-booking">Buat Reservasi Baru</a>
            </div>

            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; border-bottom: 2px solid #f8fafc; padding-bottom: 15px;">
                    <h3>🏢 Status Ruangan</h3>
                    <span style="font-weight:800; color:var(--primary); background: var(--primary-light); padding: 5px 15px; border-radius: 10px;">
                        <?= $display_date; ?>
                    </span>
                </div>
                
                <div class="room-list">
                    <?php
                    $sql = "SELECT * FROM rooms ORDER BY nama_ruangan ASC"; 
                    $result = mysqli_query($conn, $sql);
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        $room_id = $row['id'];
                        $status_aktif = $row['status_aktif']; 
                        $nama_ruangan = $row['nama_ruangan'];
                        $data_book = null; // Inisialisasi awal agar tidak error

                        if ($status_aktif == 0) {
                            $status_label = "Maintenance"; 
                            $status_class = "maintenance";
                            $tooltip = "Sedang dalam perbaikan.";
                        } else {
                            $q_check = "SELECT b.subject, b.nama_peminjam FROM bookings b 
                                        WHERE b.room_id = '$room_id' 
                                        AND b.tanggal = '$selected_full_date' 
                                        AND b.status = 'approved' LIMIT 1";
                            
                            $res_check = mysqli_query($conn, $q_check);
                            $data_book = mysqli_fetch_assoc($res_check);

                            if ($data_book) {
                                $status_label = "Terisi"; 
                                $status_class = "booked";
                                $tooltip = "Acara: " . $data_book['subject'];
                            } else {
                                $status_label = "Tersedia"; 
                                $status_class = "available";
                                $tooltip = "Siap digunakan.";
                            }
                        }
                    ?>
                        <div class="room-card">
                            <span class="status-tag <?= $status_class; ?>" title="<?= htmlspecialchars($tooltip); ?>">
                                <?= $status_label; ?>
                            </span>
                            <h4><?= htmlspecialchars($nama_ruangan); ?></h4>
                            
                            <div class="room-detail" style="margin-top: 10px; font-size: 12px;">
                                <?php if ($status_label == "Terisi" && $data_book): ?>
                                    <p style="color: var(--text-main); font-weight: 600;">📌 <?= htmlspecialchars($data_book['subject']) ?></p>
                                    <p style="color: var(--text-muted);">👤 Oleh: <?= htmlspecialchars($data_book['nama_peminjam']) ?></p>
                                <?php elseif ($status_label == "Maintenance"): ?>
                                    <p style="color: #ef4444; font-weight: 600;">🛠️ Sedang Perbaikan</p>
                                <?php else: ?>
                                    <p style="color: #10b981;">✅ Tersedia untuk dipesan</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateCalendar() {
            const m = document.getElementById("monthSelect").value;
            const y = document.getElementById("yearSelect").value;
            window.location.href = `index.php?d=1&m=${m}&y=${y}`;
        }
        function selectDay(d) {
            const m = document.getElementById("monthSelect").value;
            const y = document.getElementById("yearSelect").value;
            window.location.href = `index.php?d=${d}&m=${m}&y=${y}`;
        }

        const cal = document.getElementById("cal");
        const daysInMonth = new Date(<?= $selected_year; ?>, <?= (int)$selected_month; ?>, 0).getDate();
        
        for (let i = 1; i <= daysInMonth; i++) {
            const div = document.createElement("div");
            div.className = "cal-day" + (i == <?= $selected_day; ?> ? " active-day" : "");
            div.innerText = i;
            div.onclick = () => selectDay(i);
            cal.appendChild(div);
        }
    </script>
</body>
</html>