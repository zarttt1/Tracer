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
        
        .btn-login-admin { background: white !important; color: var(--primary) !important; margin-left: 10px; border-radius: 8px; padding: 8px 16px; font-weight: 600; text-decoration: none; font-size: 14px; }

        /* --- HEADER --- */
        .header-section {
            background: var(--primary);
            color: white;
            padding: 40px 8% 80px 8%;
        }
        .header-section h1 { font-size: 28px; font-weight: 800; }

        /* --- CONTENT CARD --- */
        .container { padding: 0 8%; max-width: 1400px; margin: -50px auto 40px auto; }
        .card {
            background: var(--white);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
        }

        /* --- SEARCH BAR --- */
        .search-container { margin-bottom: 25px; position: relative; max-width: 400px; }
        .search-container input {
            width: 100%;
            padding: 12px 16px;
            padding-left: 40px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            outline: none;
            transition: 0.3s;
            font-size: 14px;
        }
        .search-container input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-light); }
        .search-container::before {
            content: "🔍"; position: absolute; left: 14px; top: 12px; font-size: 14px; opacity: 0.5;
        }

        /* --- TABLE STYLE --- */
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th {
            padding: 16px;
            background: #f8fafc;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            border-bottom: 2px solid var(--border);
            text-align: left;
        }
        td { padding: 16px; border-bottom: 1px solid var(--border); font-size: 14px; vertical-align: middle; }
        
        tr:hover td { background-color: #fcfdfc; }
        
        .room-name { font-weight: 700; color: var(--primary); }
        .subject-text { font-weight: 500; color: var(--text-main); }

        /* --- BADGES --- */
        .badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
            text-transform: uppercase;
        }
        .approved { background: #dcfce7; color: #15803d; }
        .pending { background: #fef9c3; color: #a16207; }
        .rejected { background: #fee2e2; color: #b91c1c; }

        .btn-detail {
            background: #f1f5f9;
            color: var(--text-main);
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: 0.2s;
        }
        .btn-detail:hover { background: var(--primary); color: white; transform: translateY(-1px); }

        @media (max-width: 768px) {
            nav { padding: 0 5%; }
            .header-section { padding: 30px 5% 70px 5%; }
            .container { padding: 0 5%; }
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

    <div class="header-section">
        <h1>Jadwal Reservasi</h1>
        <p style="opacity: 0.8; margin-top: 5px">Daftar penggunaan seluruh ruangan Training Center.</p>
    </div>

    <div class="container">
        <div class="card">
            <div class="search-container">
                <input type="text" placeholder="Cari ruangan, tanggal, atau kegiatan..." id="searchInput" onkeyup="filterTable()" />
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
                        // Query diperbaiki untuk menggunakan kolom 'waktu' sesuai struktur tabel Anda
                        $sql = "SELECT b.*, r.nama_ruangan FROM bookings b 
                                JOIN rooms r ON b.room_id = r.id 
                                ORDER BY b.tanggal DESC, b.waktu ASC";
                        $res = mysqli_query($conn, $sql);

                        if (mysqli_num_rows($res) > 0) {
                            while($row = mysqli_fetch_assoc($res)) {
                                $status_class = strtolower($row['status']);
                        ?>
                        <tr>
                            <td style="white-space: nowrap; font-weight: 500;">
                                <?php echo date('d M Y', strtotime($row['tanggal'])); ?>
                            </td>
                            <td style="white-space: nowrap;">
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($row['waktu']); ?></div>
                            </td>
                            <td class="room-name"><?php echo htmlspecialchars($row['nama_ruangan']); ?></td>
                            <td class="subject-text"><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td>
                                <span class="badge <?php echo $status_class; ?>">
                                    <?php echo strtoupper($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="booking_detail.php?id=<?php echo $row['id']; ?>" class="btn-detail">Detail</a>
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
        function filterTable() {
            let input = document.getElementById("searchInput");
            let filter = input.value.toUpperCase();
            let table = document.getElementById("bookingTable");
            let tr = table.getElementsByTagName("tr");

            for (let i = 0; i < tr.length; i++) {
                let textContent = tr[i].textContent || tr[i].innerText;
                if (textContent.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    </script>
</body>
</html>