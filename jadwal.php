<?php 
include 'koneksi.php'; 
date_default_timezone_set('Asia/Jakarta');
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Jadwal Reservasi - TRACER</title>
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
        .btn-login-admin:hover { background: white; color: var(--primary) !important; }
        /* --- HEADER --- */
        .header-section { background: var(--primary); color: white; padding: 40px 8% 80px 8%; }
        .header-section h1 { font-size: 28px; font-weight: 800; }

        /* --- CONTENT CARD --- */
        .container { padding: 0 8%; max-width: 1400px; margin: -50px auto 40px auto; }
        .card {
            background: var(--white); border-radius: 16px; padding: 30px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); border: 1px solid var(--border);
        }

        /* --- SEARCH & FILTER PILLS --- */
        .controls-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .search-container { position: relative; width: 100%; max-width: 400px; }
        .search-container input {
            width: 100%; padding: 12px 16px 12px 40px; border: 1.5px solid var(--border);
            border-radius: 10px; outline: none; transition: 0.3s; font-size: 14px;
        }
        .search-container input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-light); }
        .search-container::before { content: "🔍"; position: absolute; left: 14px; top: 12px; font-size: 14px; opacity: 0.5; }

        .filter-pills { display: flex; gap: 8px; }
        .pill {
            padding: 8px 16px; border-radius: 20px; border: 1px solid var(--border);
            background: white; cursor: pointer; font-size: 13px; font-weight: 600; color: var(--text-muted); transition: 0.3s;
        }
        .pill.active { background: var(--primary); color: white; border-color: var(--primary); }

        /* --- TABLE STYLE --- */
        .table-container { overflow-x: auto; margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th {
            padding: 16px; background: #f8fafc; font-size: 12px; font-weight: 700;
            text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border); text-align: left;
        }
        td { padding: 16px; border-bottom: 1px solid var(--border); font-size: 14px; vertical-align: middle; }
        tr:hover td { background-color: #fcfdfc; }

        /* --- UI ELEMENTS --- */
        .room-name { font-weight: 700; color: var(--primary); }
        .subject-text { font-weight: 500; color: var(--text-main); }
        .time-tag { display: flex; align-items: center; gap: 6px; font-weight: 600; }
        
        .badge { padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 700; display: inline-block; text-transform: uppercase; }
        .approved { background: #dcfce7; color: #15803d; }
        .pending { background: #fef9c3; color: #a16207; }
        .rejected { background: #fee2e2; color: #b91c1c; }

        .btn-detail {
            background: #f1f5f9; color: var(--text-main); padding: 8px 16px;
            border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 600; transition: 0.2s;
        }
        .btn-detail:hover { background: var(--primary); color: white; transform: translateY(-1px); }

        @media (max-width: 768px) {
            nav { padding: 0 5%; }
            .header-section { padding: 30px 5% 70px 5%; }
            .container { padding: 0 5%; }
            .controls-row { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <nav>
        <a href="index.php" class="logo"><h1>TRACER</h1></a>
        <div class="nav-links">
            <a href="index.php">Beranda</a>
            <a href="jadwal.php"class="active">Jadwal</a>
            <a href="my_bookings.php">My Booking</a>
            <a href="login_admin.php" class="btn-login-admin">Admin</a>
        </div>
    </nav>

    <div class="header-section">
        <h1>Jadwal Reservasi</h1>
        <p style="opacity: 0.8; margin-top: 5px">Daftar penggunaan seluruh ruangan Training Center.</p>
    </div>

    <div class="container">
        <div class="card">
            <div class="controls-row">
                <div class="search-container">
                    <input type="text" placeholder="Cari ruangan, tanggal, atau kegiatan..." id="searchInput" onkeyup="filterTable()" />
                </div>
                <div class="filter-pills">
                    <button class="pill active" onclick="filterStatus('all', this)">Semua</button>
                    <button class="pill" onclick="filterStatus('approved', this)">Disetujui</button>
                    <button class="pill" onclick="filterStatus('pending', this)">Menunggu</button>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Sesi Waktu</th>
                            <th>Ruangan</th>
                            <th>Kegiatan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="bookingTable">
                        <?php
                        $sql = "SELECT b.*, r.nama_ruangan FROM bookings b 
                                JOIN rooms r ON b.room_id = r.id 
                                ORDER BY b.tanggal DESC, b.waktu ASC";
                        $res = mysqli_query($conn, $sql);

                        if (mysqli_num_rows($res) > 0) {
                            while($row = mysqli_fetch_assoc($res)) {
                                $status_val = strtolower($row['status']);
                                $waktu = $row['waktu'];
                                $icon = ($waktu == 'Pagi') ? '☀️' : (($waktu == 'Siang') ? '🌥️' : '📅');
                        ?>
                        <tr data-status="<?= $status_val ?>">
                            <td style="white-space: nowrap; font-weight: 600;">
                                <?= date('d M Y', strtotime($row['tanggal'])); ?>
                            </td>
                            <td>
                                <div class="time-tag"><?= $icon ?> <?= htmlspecialchars($waktu); ?></div>
                            </td>
                            <td class="room-name"><?= htmlspecialchars($row['nama_ruangan']); ?></td>
                            <td class="subject-text"><?= htmlspecialchars($row['subject']); ?></td>
                            <td>
                                <span class="badge <?= $status_val; ?>">
                                    <?= strtoupper($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="booking_detail.php?id=<?= $row['id']; ?>" class="btn-detail">Detail</a>
                            </td>
                        </tr>
                        <?php 
                            } 
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding: 40px; color:#94a3b8;'>Belum ada data reservasi yang ditemukan.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Fungsi Filter berdasarkan Teks
        function filterTable() {
            let input = document.getElementById("searchInput").value.toUpperCase();
            let tr = document.getElementById("bookingTable").getElementsByTagName("tr");

            for (let i = 0; i < tr.length; i++) {
                if(tr[i].cells.length < 2) continue; // Abaikan baris "Data tidak ditemukan"
                let text = tr[i].textContent || tr[i].innerText;
                tr[i].style.display = text.toUpperCase().indexOf(input) > -1 ? "" : "none";
            }
        }

        // Fungsi Filter berdasarkan Status (Pills)
        function filterStatus(status, element) {
            // Ubah tampilan tombol aktif
            document.querySelectorAll('.pill').forEach(btn => btn.classList.remove('active'));
            element.classList.add('active');

            let tr = document.getElementById("bookingTable").getElementsByTagName("tr");
            for (let i = 0; i < tr.length; i++) {
                if(tr[i].cells.length < 2) continue;
                let rowStatus = tr[i].getAttribute("data-status");
                if (status === 'all' || rowStatus === status) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    </script>
</body>
</html>