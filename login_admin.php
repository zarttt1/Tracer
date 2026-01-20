<?php
session_start();
include 'koneksi.php';

// --- PROSES REGISTER ADMIN BARU ---
if (isset($_POST['register'])) {
    $new_user = mysqli_real_escape_string($conn, $_POST['username']);
    $new_name = mysqli_real_escape_string($conn, $_POST['nama_admin']);
    // Menggunakan password_hash untuk keamanan
    $new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT id FROM admins WHERE username = '$new_user'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Username sudah terdaftar!";
    } else {
        $ins = "INSERT INTO admins (username, password, nama_admin) VALUES ('$new_user', '$new_pass', '$new_name')";
        if (mysqli_query($conn, $ins)) {
            $success = "Admin baru berhasil ditambahkan! Silakan login.";
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
        
        // Verifikasi password yang sudah di-hash
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_name'] = $row['nama_admin'];
            
            header("Location: admin_dashboard.php");
            exit;
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
    <title>Admin Panel - TRACER</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #197b40; --bg: #f8fafc; }
        body { background: var(--bg); font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .container { background: white; padding: 35px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); width: 100%; max-width: 400px; }
        h1 { color: var(--primary); text-align: center; font-weight: 800; margin-bottom: 25px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #475569; }
        input { width: 100%; padding: 10px; border: 1.5px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; }
        .btn { width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-login { background: var(--primary); color: white; }
        .btn-reg { background: #f1f5f9; color: #475569; font-size: 12px; }
        .error { background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 13px; text-align: center; }
        .success { background: #dcfce7; color: #166534; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 13px; text-align: center; }
        .toggle-link { text-align: center; margin-top: 15px; font-size: 13px; cursor: pointer; color: var(--primary); font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <h1 id="form-title">LOGIN ADMIN</h1>
        
        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
        <?php if (isset($success)) echo "<div class='success'>$success</div>"; ?>

        <form id="login-form" action="" method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn btn-login">Sign In</button>
            <div class="toggle-link" onclick="toggleForm()">Belum punya akun? Daftar Admin</div>
        </form>

        <form id="register-form" action="" method="POST" style="display: none;">
            <div class="form-group">
                <label>Nama Lengkap Admin</label>
                <input type="text" name="nama_admin" required placeholder="Contoh: Budi Santoso">
            </div>
            <div class="form-group">
                <label>Username Baru</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="register" class="btn btn-login">Daftarkan Admin</button>
            <div class="toggle-link" onclick="toggleForm()">Sudah punya akun? Login</div>
        </form>
    </div>

    <script>
        function toggleForm() {
            const loginForm = document.getElementById('login-form');
            const regForm = document.getElementById('register-form');
            const title = document.getElementById('form-title');

            if (loginForm.style.display === 'none') {
                loginForm.style.display = 'block';
                regForm.style.display = 'none';
                title.innerText = 'LOGIN ADMIN';
            } else {
                loginForm.style.display = 'none';
                regForm.style.display = 'block';
                title.innerText = 'REGISTER ADMIN';
            }
        }
    </script>
</body>
</html>