<?php
// 1. Memulai session agar bisa diakses
session_start();

// 2. Menghapus semua data dalam variabel $_SESSION
$_SESSION = array();

// 3. Menghapus cookie session di browser jika ada (opsional tapi disarankan untuk keamanan)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Menghancurkan session secara total di server
session_destroy();

// 5. Mengarahkan kembali ke halaman login atau index
// Anda bisa menyesuaikan ini ke index.php atau login_admin.php
header("Location: index.php");
exit();
?>