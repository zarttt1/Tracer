<?php
session_start();
include 'koneksi.php';

// 1. Proteksi Halaman: Cek apakah sudah login DAN pastikan role-nya adalah 'admin'
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_admin.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = mysqli_query($conn, "SELECT * FROM bookings WHERE id = '$id'");
    $data = mysqli_fetch_assoc($query);
}

// Ambil daftar semua ruangan untuk dropdown pindah ruangan
$query_rooms = mysqli_query($conn, "SELECT * FROM rooms ORDER BY nama_ruangan ASC");

if (isset($_POST['update'])) {
    $room_id = $_POST['room_id'];
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $nama    = mysqli_real_escape_string($conn, $_POST['nama_peminjam']);
    $tanggal = $_POST['tanggal'];
    $waktu   = $_POST['waktu'];
    $status  = $_POST['status'];
    $admin_comment = mysqli_real_escape_string($conn, $_POST['admin_comment']);

    $sql = "UPDATE bookings SET 
            room_id = '$room_id', subject = '$subject', nama_peminjam = '$nama', 
            tanggal = '$tanggal', waktu = '$waktu', status = '$status', 
            admin_comment = '$admin_comment', updated_at = NOW() 
            WHERE id = '$id'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Data Berhasil Diperbarui!'); window.location='admin_booking.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Reservasi - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; padding: 40px; }
        .form-card { background: white; max-width: 600px; margin: auto; padding: 30px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: 700; margin-bottom: 5px; font-size: 13px; color: #64748b; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: 'Inter'; }
        .btn-save { background: #197b40; color: white; border: none; padding: 12px; width: 100%; border-radius: 8px; font-weight: 700; cursor: pointer; margin-top: 20px; }
        .btn-back { display: block; text-align: center; margin-top: 15px; color: #64748b; text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>
<div class="form-card">
    <h2 style="margin-bottom: 20px; color: #1e293b;">Edit Reservasi</h2>
    <form method="POST">
        <div class="form-group">
            <label>Pindah Ruangan</label>
            <select name="room_id">
                <?php while($r = mysqli_fetch_assoc($query_rooms)): ?>
                    <option value="<?= $r['id'] ?>" <?= $r['id'] == $data['room_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($r['nama_ruangan']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Nama Pemohon</label>
            <input type="text" name="nama_peminjam" value="<?= htmlspecialchars($data['nama_peminjam']) ?>" required>
        </div>
        <div class="form-group">
            <label>Kegiatan / Subject</label>
            <input type="text" name="subject" value="<?= htmlspecialchars($data['subject']) ?>" required>
        </div>
        <div class="form-group">
            <label>Tanggal</label>
            <input type="date" name="tanggal" value="<?= $data['tanggal'] ?>" required>
        </div>
        <div class="form-group">
            <label>Waktu Sesi</label>
            <select name="waktu">
                <option value="Pagi" <?= $data['waktu'] == 'Pagi' ? 'selected' : '' ?>>Pagi</option>
                <option value="Siang" <?= $data['waktu'] == 'Siang' ? 'selected' : '' ?>>Siang</option>
                <option value="Full Day" <?= $data['waktu'] == 'Full Day' ? 'selected' : '' ?>>Full Day</option>
            </select>
        </div>
        <div class="form-group">
            <label>Status Persetujuan</label>
            <select name="status" style="border: 2px solid #197b40; font-weight: bold;">
                <option value="pending" <?= $data['status'] == 'pending' ? 'selected' : '' ?>>PENDING</option>
                <option value="approved" <?= $data['status'] == 'approved' ? 'selected' : '' ?>>APPROVED</option>
                <option value="rejected" <?= $data['status'] == 'rejected' ? 'selected' : '' ?>>REJECTED</option>
            </select>
        </div>
        <div class="form-group">
            <label>Balasan Admin (Catatan)</label>
            <textarea name="admin_comment" rows="3"><?= htmlspecialchars($data['admin_comment']?? '') ?></textarea>
        </div>
        <button type="submit" name="update" class="btn-save">Simpan Perubahan</button>
        <a href="admin_booking.php" class="btn-back">← Batal</a>
    </form>
</div>
</body>
</html>