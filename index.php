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
      
      /* --- NAVIGATION --- */
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
            transition: all 0.3s ease;
        }
        .nav-links a:hover { color: white; background: rgba(255, 255, 255, 0.1); }
        .nav-links a.active { color: white; background: rgba(255, 255, 255, 0.2); }
        
        .btn-login-admin { background: white !important; color: var(--primary) !important; margin-left: 10px; }

        /* --- HEADER --- */
        .header-section {
            background: var(--primary);
            color: white;
            padding: 40px 8% 80px 8%;
        }
        .header-section h1 { font-size: 28px; font-weight: 800; }
      /* --- HERO SECTION --- */
      .hero {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 60px 8% 100px;
        text-align: center;
      }
      .hero h1 { font-size: 32px; font-weight: 800; margin-bottom: 10px; }
      .hero p { opacity: 0.9; font-size: 16px; max-width: 600px; margin: 0 auto; }

      /* --- MAIN CONTENT --- */
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
      
      /* --- CALENDAR UI --- */
      .filter-group { display: flex; gap: 12px; margin-bottom: 20px; }
      .select-custom {
        flex: 1;
        padding: 12px;
        border-radius: 12px;
        border: 2px solid #f1f5f9;
        background: #f8fafc;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: 0.3s;
      }
      .select-custom:focus { border-color: var(--primary); outline: none; }

      .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; }
      .cal-day {
        aspect-ratio: 1/1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border-radius: 12px;
        transition: 0.2s;
        color: var(--text-muted);
        background: #f8fafc;
      }
      .cal-day:hover { background: var(--primary-light); color: var(--primary); transform: translateY(-2px); }
      .active-day { background: var(--primary) !important; color: white !important; transform: scale(1.1); box-shadow: 0 4px 12px rgba(25, 123, 64, 0.3); }

      /* --- ROOM LIST UI --- */
      .room-list { display: flex; flex-direction: column; gap: 15px; }
      .room-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 24px;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        transition: 0.3s;
      }
      .room-card:hover { border-color: var(--primary); background: #fcfdfc; transform: translateX(5px); }
      
      .room-info h4 { font-size: 16px; font-weight: 700; color: var(--text-dark); }
      
      .status-tag {
        padding: 6px 16px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.5px;
      }
      .available { background: #ecfdf5; color: #059669; border: 1px solid #10b98133; }
      .booked { background: #fef2f2; color: #dc2626; border: 1px solid #ef444433; }

      .btn-booking {
        background: var(--primary);
        color: white;
        padding: 15px;
        border-radius: 14px;
        text-decoration: none;
        font-weight: 700;
        display: block;
        margin-top: 25px;
        text-align: center;
        transition: 0.3s;
        box-shadow: 0 4px 14px rgba(25, 123, 64, 0.4);
      }
      .btn-booking:hover { background: var(--primary-dark); transform: translateY(-2px); }

      /* Responsive */
      @media (max-width: 992px) {
        .grid { grid-template-columns: 1fr; }
        .container { margin-top: -30px; }
      }
    </style>
  </head>
  <body>

    <nav>
        <a href="index.php" class="logo"><h1>TRACER</h1></a>
        <div class="nav-links">
            <a href="jadwal.php" class="<?= ($current_page == 'jadwal.php') ? 'active' : '' ?>">Jadwal</a>
            <a href="my_bookings.php" class="<?= ($current_page == 'my_bookings.php') ? 'active' : '' ?>">My Booking</a>
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
                <option value="<?php echo $m_val; ?>" <?php echo ($selected_month == $m_val) ? 'selected' : ''; ?>>
                  <?php echo $m_name; ?>
                </option>
              <?php endforeach; ?>
            </select>

            <select class="select-custom" id="yearSelect" onchange="updateCalendar()">
              <?php 
              $current_y = date('Y');
              for($y = $current_y; $y <= $current_y + 1; $y++): ?>
                <option value="<?php echo $y; ?>" <?php echo ($selected_year == $y) ? 'selected' : ''; ?>>
                  <?php echo $y; ?>
                </option>
              <?php endfor; ?>
            </select>
          </div>

          <div class="calendar-grid" id="cal"></div>
          
          <a href="create_booking.php?date=<?php echo $selected_full_date; ?>" class="btn-booking">
            Buat Reservasi Baru
          </a>
        </div>

        <div class="card">
          <div class="card-title">🏢 Status Ruangan <span>• <?php echo $display_date; ?></span></div>
          
          <div class="room-list">
            <?php
            $sql = "SELECT * FROM rooms WHERE status_aktif = 1";
            $result = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                $room_id = $row['id'];
                $check = mysqli_query($conn, "SELECT id FROM bookings WHERE room_id = '$room_id' AND tanggal = '$selected_full_date' AND status = 'approved'");
                $is_booked = mysqli_num_rows($check) > 0;
            ?>
                <div class="room-card">
                  <div class="room-info">
                    <h4><?php echo $row['nama_ruangan']; ?></h4>
                  </div>
                  <span class="status-tag <?php echo $is_booked ? 'booked' : 'available'; ?>">
                    <?php echo $is_booked ? 'Terisi' : 'Tersedia'; ?>
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
        window.location.href = `index.php?d=1&m=${m}&y=${y}&date=${y}-${m}-01`;
      }

      function selectDay(d) {
        const m = document.getElementById("monthSelect").value;
        const y = document.getElementById("yearSelect").value;
        const dayFormatted = d.toString().padStart(2, '0');
        window.location.href = `index.php?d=${d}&m=${m}&y=${y}&date=${y}-${m}-${dayFormatted}`;
      }

      const cal = document.getElementById("cal");
      const selectedDay = <?php echo $selected_day; ?>;
      const daysInMonth = new Date(<?php echo $selected_year; ?>, <?php echo $selected_month; ?>, 0).getDate();

      for (let i = 1; i <= daysInMonth; i++) {
        const div = document.createElement("div");
        div.className = "cal-day" + (i === selectedDay ? " active-day" : "");
        div.innerText = i;
        div.onclick = () => selectDay(i);
        cal.appendChild(div);
      }
    </script>
  </body>
</html>