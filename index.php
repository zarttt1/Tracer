<?php 
include 'koneksi.php'; 
date_default_timezone_set('Asia/Jakarta');

$selected_day   = isset($_GET['d']) ? $_GET['d'] : date('d');
$selected_month = isset($_GET['m']) ? $_GET['m'] : date('m');
$selected_year  = isset($_GET['y']) ? $_GET['y'] : date('Y');

$selected_full_date = "$selected_year-$selected_month-" . str_pad($selected_day, 2, "0", STR_PAD_LEFT);
$display_date = date('d F Y', strtotime($selected_full_date));

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
            --primary-light: #e8f5ed;
            --primary-dark: #125a2f;
            --accent: #f59e0b;
            --bg: #f1f5f9;
            --white: #ffffff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Inter", sans-serif; }
        body { background: var(--bg); color: var(--text-dark); line-height: 1.6; }
        
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
        }
        .logo { text-decoration: none; color: white; }
        .logo h1 { font-size: 22px; font-weight: 800; letter-spacing: -0.5px; }
        
        .nav-links { display: flex; align-items: center; gap: 10px; }
        .nav-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            padding: 8px 16px;
            font-weight: 600;
            font-size: 14px;
        }
        .btn-login-admin { background: white !important; color: var(--primary) !important; border-radius: 8px; }

        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 60px 8% 100px;
            text-align: center;
        }

        .container { padding: 0 8%; max-width: 1300px; margin: -50px auto 50px; }
        .grid { display: grid; grid-template-columns: 380px 1fr; gap: 30px; }

        .card {
            background: var(--white);
            border-radius: 24px;
            padding: 30px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255,255,255,0.8);
        }

        .card-title { font-size: 18px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        
        .filter-group { display: flex; gap: 12px; margin-bottom: 20px; }
        .select-custom {
            flex: 1; padding: 12px; border-radius: 12px; border: 2px solid #f1f5f9;
            background: #f8fafc; font-weight: 600; font-size: 14px; cursor: pointer;
        }

        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; }
        .cal-day {
            aspect-ratio: 1/1; display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 600; cursor: pointer; border-radius: 12px;
            color: var(--text-muted); background: #f8fafc; transition: 0.2s;
        }
        .cal-day:hover { background: var(--primary-light); color: var(--primary); }
        .active-day { background: var(--primary) !important; color: white !important; }

        .room-list { display: flex; flex-direction: column; gap: 15px; }
        .room-card {
            display: flex; justify-content: space-between; align-items: center;
            padding: 18px 24px; border-radius: 18px; border: 1px solid #e2e8f0;
            transition: 0.3s;
        }
        
        .room-info h4 { font-size: 16px; font-weight: 700; }
        
        .status-tag {
            padding: 6px 16px; border-radius: 100px; font-size: 12px;
            font-weight: 700; cursor: help;
        }
        .available { background: #ecfdf5; color: #059669; border: 1px solid #10b98133; }
        .booked { background: #fef2f2; color: #dc2626; border: 1px solid #ef444433; }
        /* Style Tambahan untuk Maintenance */
        .maintenance { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

        .btn-booking {
            background: var(--primary); color: white; padding: 15px; border-radius: 14px;
            text-decoration: none; font-weight: 700; display: block; margin-top: 25px;
            text-align: center; box-shadow: 0 4px 14px rgba(25, 123, 64, 0.4);
        }
    </style>
</head>
<body>

    <nav>
        <a href="index.php" class="logo"><h1>TRACER</h1></a>
        <div class="nav-links">
            <a href="jadwal.php">Jadwal</a>
            <a href="my_bookings.php">My Booking</a>
            <a href="login_admin.php" class="btn-login-admin">Admin</a>
        </div>
    </nav>

    <div class="hero">
        <h1>Training Center Reservation Room</h1>
        <p>Sistem manajemen peminjaman ruangan yang cepat, transparan, dan terintegrasi.</p>
    </div>

    <div class="container">
        <div class="grid">
            <div class="card">
                <div class="card-title">📅 Pilih Waktu</div>
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
                <div class="card-title">🏢 Status Ruangan <span>• <?= $display_date; ?></span></div>
                <div class="room-list">
                    <?php
                    // Ambil SEMUA ruangan
                    $sql = "SELECT * FROM rooms"; 
                    $result = mysqli_query($conn, $sql);
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        $room_id = $row['id'];
                        $status_aktif = $row['status_aktif']; 
                        
                        // Cek booking jika ruangan aktif
                        $q_check = "SELECT waktu, subject FROM bookings 
                                    WHERE room_id = '$room_id' AND tanggal = '$selected_full_date' AND status = 'approved' LIMIT 1";
                        $res_check = mysqli_query($conn, $q_check);
                        $data_book = mysqli_fetch_assoc($res_check);
                        
                        // Logika Penentuan Label
                        if ($status_aktif == 0) {
                            $status_label = "Maintenance";
                            $status_class = "maintenance";
                            $tooltip = "Status: Tidak Tersedia. Ket: " . ($row['keterangan_ruangan'] ?: 'Sedang dalam perbaikan.');
                        } else {
                            if ($data_book) {
                                $status_label = "Terisi";
                                $status_class = "booked";
                                $tooltip = "Sesi: " . $data_book['waktu'] . " | Acara: " . $data_book['subject'];
                            } else {
                                $status_label = "Tersedia";
                                $status_class = "available";
                                $tooltip = "Ruangan siap digunakan.";
                            }
                        }
                    ?>
                        <div class="room-card">
                            <div class="room-info">
                                <h4><?= htmlspecialchars($row['nama_ruangan']); ?></h4>
                            </div>
                            <span class="status-tag <?= $status_class; ?>" title="<?= htmlspecialchars($tooltip); ?>">
                                <?= $status_label; ?>
                            </span>
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
        const daysInMonth = new Date(<?= $selected_year; ?>, <?= $selected_month; ?>, 0).getDate();

        for (let i = 1; i <= daysInMonth; i++) {
            const div = document.createElement("div");
            div.className = "cal-day" + (i == <?= (int)$selected_day; ?> ? " active-day" : "");
            div.innerText = i;
            div.onclick = () => selectDay(i);
            cal.appendChild(div);
        }
    </script>
</body>
</html>