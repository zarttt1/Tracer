<?php
include 'koneksi.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login_admin.php");
    exit();
}

// 1. Ambil data ruangan
if (!isset($_GET['id'])) { header("Location: admin_room.php"); exit(); }
$id = mysqli_real_escape_string($conn, $_GET['id']);

$query = "SELECT * FROM rooms WHERE id = '$id'";
$res = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($res);

// 2. Proses Update
if (isset($_POST['update_room'])) {
    // Menangkap nilai 1 (true) atau 0 (false)
    $status = $_POST['status_aktif']; 
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan_ruangan']);

    // Update kolom status_aktif (Boolean)
    $sql = "UPDATE rooms SET status_aktif = '$status', keterangan_ruangan = '$keterangan' WHERE id = '$id'";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Status ruangan berhasil diperbarui!'); window.location='admin_room.php';</script>";
    } else {
        echo "<script>alert('Gagal update data: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Ruangan - TRACER</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #27ae60; --bg: #f5f7fa; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: white; width: 100%; max-width: 500px; padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        h2 { margin-bottom: 10px; color: #2c3e50; text-align: center; font-weight: 800; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px; }
        select, textarea { width: 100%; padding: 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-family: inherit; outline: none; transition: 0.3s; font-size: 14px; }
        select:focus, textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.1); }
        .btn-save { width: 100%; background: var(--primary); color: white; padding: 14px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 16px; margin-top: 10px; transition: 0.3s; }
        .btn-save:hover { background: #219150; transform: translateY(-1px); }
        .btn-cancel { display: block; text-align: center; margin-top: 15px; color: #64748b; text-decoration: none; font-size: 14px; font-weight: 600; }
    </style>
</head>
<body>

    <div class="card">
        <h2>Update Ruangan</h2>
        <p style="text-align: center; color: #64748b; margin-bottom: 30px; font-size: 14px;">
            Mengedit: <strong><?= htmlspecialchars($data['nama_ruangan']) ?></strong>
        </p>

        <form method="POST">
            <div class="form-group">
                <label>Status Operasional</label>
                <select name="status_aktif">
                    <option value="1" <?= ($data['status_aktif'] == 1) ? 'selected' : '' ?>>Tersedia (Dapat Digunakan)</option>
                    <option value="0" <?= ($data['status_aktif'] == 0) ? 'selected' : '' ?>>Tidak Tersedia (Terpakai/Maintenance)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Keterangan Tambahan</label>
                <textarea name="keterangan_ruangan" placeholder="Berikan info seperti: AC Rusak, Sedang Renovasi, atau Ruangan Terpakai Kegiatan Internal..." rows="4"><?= htmlspecialchars($data['keterangan_ruangan'] ?? '') ?></textarea>
            </div>

            <button type="submit" name="update_room" class="btn-save">Simpan Perubahan</button>
            <a href="admin_room.php" class="btn-cancel">Batal & Kembali</a>
        </form>
    </div>

</body>
</html>