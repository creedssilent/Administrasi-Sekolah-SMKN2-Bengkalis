<?php
session_start();
include 'config.php';
include 'encryption.php'; // Pastikan file ini berisi fungsi decryptPassword() dan variabel $encryption_key

// Mengatur header sebagai JSON untuk konsistensi response
header('Content-Type: application/json');

// 1. PERBAIKAN: Menggunakan $_SESSION['username'] dan $_SESSION['role'] sesuai standar
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(["success" => false, "message" => "Akses ditolak. Anda bukan admin."]);
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['admin_password'])) {
    echo json_encode(["success" => false, "message" => "Parameter tidak lengkap."]);
    exit();
}

$userId = $_GET['id'];
$adminPassword = $_GET['admin_password'];
$adminUsername = $_SESSION['username'];

// 2. PERBAIKAN: Menggunakan Prepared Statements untuk keamanan
// Ambil HASH password admin yang sedang login dari database
$stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
$stmt->bind_param("s", $adminUsername);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    $hashed_admin_password = $row['password'];

    // Verifikasi password admin yang diinput dengan hash dari database
    if (password_verify($adminPassword, $hashed_admin_password)) {

        // Jika password admin benar, ambil ENCRYPTED password milik user yang dituju
        $stmt_user = $conn->prepare("SELECT encrypted_password FROM users WHERE id = ?");
        $stmt_user->bind_param("i", $userId);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();

        if ($result_user->num_rows == 1) {
            $user_data = $result_user->fetch_assoc();
            $encrypted_user_password = $user_data['encrypted_password'];

            // 3. KESALAHAN UTAMA DIPERBAIKI: Panggil fungsi dekripsi
            // Pastikan fungsi decryptPassword ada di file encryption.php
            if (function_exists('decryptPassword') && !empty($encrypted_user_password)) {
                $decrypted_password = decryptPassword($encrypted_user_password, $encryption_key);
                echo json_encode(["success" => true, "password" => htmlspecialchars($decrypted_password)]);
            } else {
                echo json_encode(["success" => false, "message" => "Password terenkripsi tidak ditemukan atau fungsi dekripsi bermasalah."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Pengguna tidak ditemukan."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Password Admin salah."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Admin tidak ditemukan."]);
}

// 4. PERBAIKAN: Menghapus kurung kurawal '}' ekstra yang menyebabkan error
