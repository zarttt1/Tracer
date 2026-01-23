<?php
session_start();
include 'koneksi.php';

$error = "";
$success = "";

// --- PROSES REGISTER ---
if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Pengamanan: Jika ada kata "admin" di username (case insensitive), role jadi admin
    $role = (stripos($username, 'admin') !== false) ? 'admin' : 'user';

    $check = mysqli_query($conn, "SELECT id FROM admins WHERE username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Username sudah terdaftar!";
    } else {
        $ins = "INSERT INTO admins (username, password, nama_admin, role) VALUES ('$username', '$password', '$nama', '$role')";
        if (mysqli_query($conn, $ins)) {
            $success = "Berhasil daftar sebagai <b>$role</b>! Silakan login.";
        } else {
            $error = "Gagal mendaftar: " . mysqli_error($conn);
        }
    }
}

// --- PROSES LOGIN ---
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM admins WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $row['password'])) {
            // Bersihkan output buffer untuk mencegah error "Headers already sent"
            ob_start(); 

            $_SESSION['logged_in'] = true;
            $_SESSION['user_id']   = $row['id'];
            $_SESSION['admin_name'] = $row['nama_admin'];
            $_SESSION['role']      = strtolower($row['role']); // Paksa jadi huruf kecil

            session_write_close(); // Pastikan sesi tersimpan

            // Debugging sederhana: Jika redirect gagal, teks ini akan muncul
            if ($_SESSION['role'] === 'admin') {
                header("Location: admin_dashboard.php");
                echo "<script>window.location.href='admin_dashboard.php';</script>"; // Backup redirect via JS
                exit;
            } else {
                header("Location: user1_dashboard.php");
                echo "<script>window.location.href='user1_dashboard.php';</script>"; // Backup redirect via JS
                exit;
            }
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>TRACER - Login System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #197b40; --bg: #f1f5f9; }
        body { background: var(--bg); font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { color: var(--primary); text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; color: #475569; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn { width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-main { background: var(--primary); color: white; }
        .msg { padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 15px; text-align: center; }
        .error { background: #fee2e2; color: #dc2626; }
        .success { background: #dcfce7; color: #166534; }
        .toggle { text-align: center; margin-top: 15px; font-size: 13px; cursor: pointer; color: var(--primary); text-decoration: underline; }
    </style>
</head>
<body>

<div class="container">
    <h2 id="title">LOGIN</h2>
    
    <?php if ($error) echo "<div class='msg error'>$error</div>"; ?>
    <?php if ($success) echo "<div class='msg success'>$success</div>"; ?>

    <form id="loginForm" method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" name="login" class="btn btn-main">Masuk</button>
        <div class="toggle" onclick="toggleForm('reg')">Belum punya akun? Daftar</div>
    </form>

    <form id="regForm" method="POST" style="display: none;">
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama_lengkap" required>
        </div>
        <div class="form-group">
            <label>Username (Gunakan kata 'admin' untuk akses Admin)</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" name="register" class="btn btn-main">Daftar Akun</button>
        <div class="toggle" onclick="toggleForm('login')">Sudah punya akun? Login</div>
    </form>
</div>

<script>
    function toggleForm(type) {
        const login = document.getElementById('loginForm');
        const reg = document.getElementById('regForm');
        const title = document.getElementById('title');
        if(type === 'reg') {
            login.style.display = 'none';
            reg.style.display = 'block';
            title.innerText = 'REGISTER';
        } else {
            login.style.display = 'block';
            reg.style.display = 'none';
            title.innerText = 'LOGIN';
        }
    }
</script>

</body>
</html>