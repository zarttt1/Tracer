<?php 
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'user') {
    header("Location: login_admin.php");
    exit;
}

include 'koneksi.php'; 
date_default_timezone_set('Asia/Jakarta');

$selected_day   = isset($_GET['d']) ? (int)$_GET['d'] : (int)date('d');
$selected_month = isset($_GET['m']) ? $_GET['m'] : date('m');
$selected_year  = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');

$max_days_in_month = cal_days_in_month(CAL_GREGORIAN, (int)$selected_month, $selected_year);
if ($selected_day > $max_days_in_month) { $selected_day = $max_days_in_month; }

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
    <title>User Dashboard - TRACER</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary: #197b40; --primary-dark: #125a2f;
            --bg: #f1f5f9; --white: #ffffff; --shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Inter", sans-serif; }
        body { background: var(--bg); color: #1e293b; }
        
        nav {
            background: var(--primary); padding: 0 5%; height: 65px;
            display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 1000; box-shadow: var(--shadow);
        }
        .logo h1 { color: white; font-size: 20px; font-weight: 800; text-decoration: none; }
        .nav-links { display: flex; align-items: center; gap: 20px; }
        .nav-links a { color: #e2e8f0; text-decoration: none; font-size: 14px; font-weight: 600; transition: 0.3s; }
        .nav-links a.active { color: white; border-bottom: 2px solid white; padding-bottom: 5px; }
        .nav-links a:hover { color: white; }
        .nav-links span { color: white; font-size: 13px; background: rgba(0,0,0,0.1); padding: 5px 12px; border-radius: 20px; }
        .btn-logout { background: #ef4444; color: white !important; padding: 8px 15px; border-radius: 6px; }

        .hero { background: var(--primary); color: white; padding: 40px 5% 80px; text-align: center; }
        .container { padding: 0 5%; max-width: 1200px; margin: -40px auto 40px; }
        
        .grid { display: grid; grid-template-columns: 350px 1fr; gap: 25px; }
        @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }

        .card { background: white; border-radius: 15px; padding: 25px; box-shadow: var(--shadow); border: 1px solid #e2e8f0; }
        .card-title { font-weight: 700; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }

        .filter-group { display: flex; gap: 10px; margin-bottom: 15px; }
        .select-custom { flex: 1; padding: 10px; border-radius: 8px; border: 1px solid #ddd; background: #fff; }
        
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; }
        .cal-day { 
            aspect-ratio: 1; display: flex; align-items: center; justify-content: center; 
            font-size: 12px; border-radius: 8px; cursor: pointer; background: #f8fafc; transition: 0.2s;
        }
        .cal-day:hover { background: #dcfce7; color: var(--primary); }
        .active-day { background: var(--primary) !important; color: white !important; font-weight: bold; }

        .room-list { display: flex; flex-direction: column; gap: 10px; }
        .room-card { 
            display: flex; justify-content: space-between; align-items: center; 
            padding: 15px; border: 1px solid #f1f5f9; border-radius: 10px;
        }
        
        .status-tag { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .available { background: #dcfce7; color: #166534; }
        .booked { background: #fee2e2; color: #991b1b; }
        .maintenance { background: #f1f5f9; color: #475569; }

        .btn-booking {
            background: var(--primary); color: white; text-align: center; display: block;
            padding: 15px; border-radius: 10px; text-decoration: none; font-weight: 700; 
            margin-top: 20px; transition: 0.3s;
        }
    </style>
</head>
<body>

    <nav>
        <div class="logo"><h1>TRACER</h1></div>
        <div class="nav-links">
            <a href="user1_dashboard.php" class="active">Dashboard</a>
            <a href="jadwal_user1.php">Jadwal</a>
            <span>👤 <?= htmlspecialchars($_SESSION['admin_name']); ?></span>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="hero">
        <h2>Dashboard Ketersediaan Ruangan</h2>
        <p>Lihat status ruangan secara real-time pada tanggal terpilih.</p>
    </div>

    <div class="container">
        <div class="grid">
            <div class="card">
                <div class="card-title">📅 Filter Kalender</div>
                <div class="filter-group">
                    <select class="select-custom" id="m" onchange="go()">
                        <?php foreach($months as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $k==$selected_month?'selected':'' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="select-custom" id="y" onchange="go()">
                        <?php for($i=2025; $i<=2026; $i++): ?>
                            <option value="<?= $i ?>" <?= $i==$selected_year?'selected':'' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="calendar-grid" id="cal"></div>
                <a href="create_booking.php?date=<?= $selected_full_date ?>" class="btn-booking">Buat Reservasi</a>
            </div>

            <div class="card">
                <div class="card-title">
                    <span>🏢 Status Ruangan</span>
                    <small style="color: var(--primary)"><?= $display_date ?></small>
                </div>
                <div class="room-list">
                    <?php
                    $rooms = mysqli_query($conn, "SELECT * FROM rooms ORDER BY nama_ruangan ASC");
                    while($r = mysqli_fetch_assoc($rooms)):
                        $rid = $r['id'];
                        $book = mysqli_query($conn, "SELECT id FROM bookings WHERE room_id='$rid' AND tanggal='$selected_full_date' AND status='approved'");
                        $is_booked = mysqli_num_rows($book) > 0;
                        
                        $status = "Tersedia"; $class = "available";
                        if($r['status_aktif'] == 0) { $status = "Maintenance"; $class = "maintenance"; }
                        elseif($is_booked) { $status = "Terisi"; $class = "booked"; }
                    ?>
                    <div class="room-card">
                        <div>
                            <strong><?= htmlspecialchars($r['nama_ruangan']); ?></strong>
                        </div>
                        <span class="status-tag <?= $class ?>"><?= $status ?></span>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function go() {
            const m = document.getElementById('m').value;
            const y = document.getElementById('y').value;
            window.location.href = `user1_dashboard.php?d=1&m=${m}&y=${y}`;
        }
        function setD(d) {
            const m = document.getElementById('m').value;
            const y = document.getElementById('y').value;
            window.location.href = `user1_dashboard.php?d=${d}&m=${m}&y=${y}`;
        }
        const cal = document.getElementById('cal');
        const daysInMonth = new Date(<?= $selected_year ?>, <?= (int)$selected_month ?>, 0).getDate();
        for(let i=1; i<=daysInMonth; i++) {
            const div = document.createElement('div');
            div.className = `cal-day ${i == <?= $selected_day ?> ? 'active-day' : ''}`;
            div.innerText = i;
            div.onclick = () => setD(i);
            cal.appendChild(div);
        }
    </script>
</body>
</html>