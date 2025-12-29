<?php
session_start();

// 1. Keamanan: Pastikan hanya kepala sekolah yang sudah login yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala_sekolah') {
    header('Location: index.php');
    exit();
}

include 'config.php';
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$pesan = '';

// 2. Logika Form saat disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi dasar
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $pesan = "Semua field wajib diisi!";
    } elseif ($new_password !== $confirm_password) {
        $pesan = "Password baru dan konfirmasi password tidak cocok!";
    } else {
        // Ambil password saat ini dari database
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        // 3. Verifikasi password lama
        if (password_verify($old_password, $user['password'])) {
            // Jika password lama benar, hash password baru
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

            // 4. Update password baru ke database
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_new_password, $user_id);
            if ($update_stmt->execute()) {
                echo "<script>alert('Password berhasil diubah. Silakan login kembali.'); window.location.href='logout.php';</script>";
                exit();
            } else {
                $pesan = "Gagal memperbarui password. Silakan coba lagi.";
            }
            $update_stmt->close();
        } else {
            $pesan = "Password lama yang Anda masukkan salah!";
        }
    }
    // Tampilkan pesan error dalam bentuk alert
    if ($pesan) {
        echo "<script>alert('" . addslashes($pesan) . "');</script>";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password - Kepala Sekolah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-bg: #111827;
            --sidebar-link-color: #9ca3af;
            --sidebar-link-hover-bg: #374151;
            --sidebar-link-active-bg: #4f46e5;
            --main-bg: #f1f5f9;
            --panel-bg: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--main-bg);
            color: var(--text-primary);
            margin: 0;
        }

        .dashboard-container {
            display: flex;
        }

        .sidebar {
            width: 250px;
            background-color: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #374151;
        }

        .sidebar .logo {
            width: 70px;
            margin-bottom: 15px;
        }

        .sidebar .sidebar-title h2 {
            font-size: 1.2em;
            font-weight: 600;
            color: #fff;
            margin: 0;
        }

        .sidebar .sidebar-title p {
            font-size: 0.8em;
            color: var(--sidebar-link-color);
            margin-top: 4px;
        }

        .sidebar-menu {
            flex-grow: 1;
            overflow-y: auto;
            scrollbar-width: none;
        }

        .sidebar-menu::-webkit-scrollbar {
            display: none;
        }

        .sidebar-menu h3 {
            color: #6b7280;
            font-size: 0.75rem;
            text-transform: uppercase;
            padding: 0 10px;
            margin-top: 20px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 10px;
            color: var(--sidebar-link-color);
            text-decoration: none;
            border-radius: 6px;
            margin: 5px 0;
            font-size: 0.9em;
        }

        .sidebar-menu a:hover {
            background-color: var(--sidebar-link-hover-bg);
            color: #fff;
        }

        .sidebar-menu a.active {
            background-color: var(--sidebar-link-active-bg);
            color: #fff;
        }

        .sidebar-menu ul {
            list-style: none;
            padding-left: 15px;
            margin: 0;
            display: block;
        }

        /* Ubah jadi block agar menu setting terbuka */
        .sidebar-menu ul a.active {
            background-color: transparent;
            color: #fff;
            font-weight: 600;
        }

        .sidebar-menu a i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }

        .main-wrapper {
            margin-left: 250px;
            width: calc(100% - 250px);
        }

        .top-nav {
            height: 70px;
            background-color: var(--panel-bg);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .main-content {
            padding: 30px;
        }

        .page-header h1 {
            font-size: 1.8em;
            font-weight: 600;
            margin: 0 0 20px 0;
        }

        .card {
            background-color: var(--panel-bg);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            max-width: 600px;
            margin: auto;
        }

        .card h2 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.25em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1em;
        }

        .btn {
            display: block;
            width: 100%;
            background-color: var(--sidebar-link-active-bg);
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            font-size: 1em;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="images/Logo SMK.png" class="logo">
                <div class="sidebar-title">
                    <h2>SIMANDAKA</h2>
                    <p>SMK Negeri 2 Bengkalis</p>
                </div>
            </div>
            <nav class="sidebar-menu">
                <h3>Laporan</h3>
                <a href="kepala_sekolah_dashboard.php"><i class="fas fa-file-alt"></i> Pusat Laporan</a>
                <h3>Other</h3>
                <a href="#" class="active"><i class="fas fa-cog"></i> Setting</a>
                <ul id="settingMenu">
                    <li><a href="change_password_ks.php" class="active"><i class="fas fa-key"></i> Ubah Password</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <div class="main-wrapper">
            <header class="top-nav">
            </header>
            <main class="main-content">
                <div class="page-header">
                    <h1>Ubah Password</h1>
                </div>

                <div class="card">
                    <h2>Formulir Ganti Password</h2>
                    <form method="POST" action="change_password_ks.php">
                        <div class="form-group">
                            <label for="old_password">Password Lama</label>
                            <input type="password" id="old_password" name="old_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">Password Baru</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password Baru</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn">Ubah Password</button>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>

</html>