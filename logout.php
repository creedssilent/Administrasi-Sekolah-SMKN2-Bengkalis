<?php
session_start(); // Wajib untuk memulai session sebelum bisa diubah

// 1. Hapus semua variabel di dalam array session
$_SESSION = array();

// 2. Hapus cookie session dari browser jika digunakan
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 3. Hancurkan session secara total di server
session_destroy();

// 4. Alihkan ke halaman login
header("Location: index.php");
exit();
