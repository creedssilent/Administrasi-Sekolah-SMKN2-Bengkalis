<?php
session_start();
include 'config.php';

// 1. Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak. Anda harus login sebagai admin.");
}

// 2. Pastikan form disubmit dengan metode POST dan ID ada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $userIdToDelete = $_POST['id'];
    $adminUsername = $_SESSION['username'];

    // 3. Ambil ID admin untuk mencegah hapus diri sendiri
    $stmt_admin = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt_admin->bind_param("s", $adminUsername);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();
    $adminData = $result_admin->fetch_assoc();
    $adminId = $adminData['id'];

    // 4. Pencegahan hapus diri sendiri
    if ($userIdToDelete == $adminId) {
        echo "<script>
                alert('Error: Anda tidak dapat menghapus akun Anda sendiri.');
                window.location.href = 'manage_users.php';
              </script>";
        exit();
    }

    // 5. Ambil role dan username pengguna yang akan dihapus untuk penanganan khusus
    $stmt_user = $conn->prepare("SELECT role, username FROM users WHERE id = ?");
    $stmt_user->bind_param("i", $userIdToDelete);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($result_user->num_rows > 0) {
        $userData = $result_user->fetch_assoc();
        $userRole = $userData['role'];
        $userUsername = $userData['username'];

        // Memulai transaksi database untuk memastikan semua query berhasil
        $conn->begin_transaction();

        try {
            // 6. Penanganan khusus berdasarkan role sebelum menghapus dari tabel 'users'
            if ($userRole === 'siswa') {
                // Untuk siswa, kita perlu menghapus record di tabel 'students' terlebih dahulu.
                // Foreign key constraint dengan ON DELETE CASCADE akan otomatis menghapus data di
                // tabel 'attendance', 'grades', dan 'payments'.
                $stmt_delete_student = $conn->prepare("DELETE FROM students WHERE username = ?");
                $stmt_delete_student->bind_param("s", $userUsername);
                $stmt_delete_student->execute();
            } elseif ($userRole === 'guru') {
                // Untuk guru, hapus jadwal mengajarnya terlebih dahulu
                $stmt_delete_schedule = $conn->prepare("DELETE FROM teacher_schedule WHERE teacher_id = ?");
                $stmt_delete_schedule->bind_param("i", $userIdToDelete);
                $stmt_delete_schedule->execute();
            }

            // 7. Hapus pengguna dari tabel 'users'
            $stmt_delete_user = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt_delete_user->bind_param("i", $userIdToDelete);
            $stmt_delete_user->execute();

            // Jika semua query berhasil, commit transaksi
            $conn->commit();

            echo "<script>
                    alert('Pengguna berhasil dihapus.');
                    window.location.href = 'manage_users.php';
                  </script>";
        } catch (mysqli_sql_exception $exception) {
            // Jika terjadi error, batalkan semua perubahan
            $conn->rollback();

            echo "<script>
                    alert('Gagal menghapus pengguna: " . $exception->getMessage() . "');
                    window.location.href = 'manage_users.php';
                  </script>";
        }
    } else {
        echo "<script>
                alert('Pengguna tidak ditemukan.');
                window.location.href = 'manage_users.php';
              </script>";
    }
} else {
    // Jika file diakses langsung
    header('Location: manage_users.php');
    exit();
}
