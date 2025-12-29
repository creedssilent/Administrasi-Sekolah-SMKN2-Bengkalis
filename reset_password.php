<?php
session_start();
include 'config.php';
include 'encryption.php';

// 1. Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak. Anda harus login sebagai admin.");
}

// 2. Pastikan form disubmit dengan metode POST dan tombol reset ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {

    // 3. Validasi Input: Pastikan ID dan password baru tidak kosong
    if (isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['new_password']) && !empty($_POST['new_password'])) {

        $userId = $_POST['id'];
        $newPassword = $_POST['new_password'];

        // 4. Proses Password: Buat hash dan enkripsi untuk password baru
        // Hash untuk verifikasi login (disimpan di kolom 'password')
        $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);

        // Enkripsi untuk fitur "Lihat Password" (disimpan di kolom 'encrypted_password')
        $encrypted_password = encryptPassword($newPassword, $encryption_key);

        // 5. Update Database: Gunakan prepared statement untuk keamanan
        $stmt = $conn->prepare("UPDATE users SET password = ?, encrypted_password = ? WHERE id = ?");
        // 'ssi' berarti: string, string, integer
        $stmt->bind_param("ssi", $hashed_password, $encrypted_password, $userId);

        if ($stmt->execute()) {
            // Jika berhasil, beri notifikasi dan kembalikan ke halaman manage_users
            echo "<script>
                    alert('Password untuk user ID: {$userId} berhasil direset.');
                    window.location.href = 'manage_users.php';
                  </script>";
        } else {
            // Jika gagal, beri notifikasi error
            echo "<script>
                    alert('Gagal mereset password: " . $stmt->error . "');
                    window.location.href = 'manage_users.php';
                  </script>";
        }
        $stmt->close();
    } else {
        // Jika input tidak lengkap
        echo "<script>
                alert('Input tidak lengkap. Silakan isi password baru.');
                window.location.href = 'manage_users.php';
              </script>";
    }
} else {
    // Jika file diakses langsung tanpa submit form
    header('Location: manage_users.php');
    exit();
}

$conn->close();
