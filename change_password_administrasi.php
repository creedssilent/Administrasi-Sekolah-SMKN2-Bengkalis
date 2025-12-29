<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrasi') {
    header('Location: index.php');
    exit();
}

if (!isset($_SESSION['username'])) {
    echo "<script>alert('Kesalahan sesi. Silakan login kembali.'); window.location.href='index.php';</script>";
    exit();
}

include 'config.php';

$username = $_SESSION['username'];
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi input
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Semua kolom harus diisi.";
    } elseif ($new_password != $confirm_password) {
        $error = "Password baru dan konfirmasi password tidak cocok.";
    } else {
        // Ambil password lama dari database
        $sql = "SELECT password FROM users WHERE username = '$username'";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $hashed_password = $row['password'];

            // Verifikasi password lama
            if (password_verify($old_password, $hashed_password)) {
                // Hash password baru
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update password di database
                $update_sql = "UPDATE users SET password = '$new_hashed_password' WHERE username = '$username'";
                if ($conn->query($update_sql) === TRUE) {
                    $success = "Password berhasil diubah.";
                } else {
                    $error = "Terjadi kesalahan saat mengubah password: " . $conn->error;
                }
            } else {
                $error = "Password lama tidak sesuai.";
            }
        } else {
            $error = "Pengguna tidak ditemukan.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password - Administrasi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* CSS Reset */
        body,
        h1,
        h2,
        h3,
        p,
        ul,
        li,
        button,
        input {
            margin: 0;
            padding: 0;
            border: 0;
            font-size: 100%;
            font: inherit;
            vertical-align: baseline;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #142850;
            /* Warna Latar Belakang yang Berbeda */
            color: #c0cde4;
            line-height: 1.6;
            overflow-x: hidden;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: #091f3d;
            /* Warna Sidebar yang Berbeda */
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .sidebar::-webkit-scrollbar {
            display: none;
        }

        /* Logo */
        .logo {
            width: 100px;
            margin-bottom: 10px;
        }

        .sidebar h2 {
            font-size: 1.5em;
            font-weight: 600;
            color: #c0cde4;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            text-align: center;
            width: 100%;
        }

        /* Info Pengguna */
        .user-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
        }

        .profile-picture {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #495670;
            margin-bottom: 10px;
        }

        .user-info span {
            font-size: 1em;
            color: #8892b0;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 12px;
            margin-bottom: 8px;
            background-color: transparent;
            color: #c0cde4;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .sidebar a i {
            margin-right: 15px;
            font-size: 1.2em;
            color: #5dade2;
        }

        .sidebar a:hover {
            background-color: #2980b9;
            /* Warna Hover yang Berbeda */
        }

        /* Submenu */
        .sidebar ul {
            list-style: none;
            padding-left: 0;
            margin-top: 5px;
            display: none;
        }

        .sidebar ul li a {
            padding-left: 40px;
            background-color: transparent;
            color: #8892b0;
        }

        .sidebar ul li a:hover {
            background-color: #2980b9;
            /* Warna Hover yang Berbeda */
        }

        /* Container Utama */
        .container {
            flex: 1;
            padding: 30px;
            margin-left: 300px;
        }

        /* Judul Dashboard */
        .dashboard-title {
            font-size: 2em;
            font-weight: 700;
            color: #c0cde4;
            letter-spacing: 0.1em;
            margin-bottom: 30px;
            text-transform: uppercase;
            text-align: left;
        }

        /* Card */
        .card {
            background-color: #091f3d;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .card h3 {
            font-size: 1.3em;
            font-weight: 600;
            color: #c0cde4;
            margin-bottom: 15px;
        }

        /* Form Styling */
        .form-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-container input {
            padding: 12px;
            border: none;
            border-radius: 5px;
            background-color: #1e2a47;
            color: #c0cde4;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 1em;
            outline: none;
        }

        .form-container input::placeholder {
            color: #8892b0;
        }

        button {
            width: calc(100% - 22px);
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            background-color: #3498db;
            color: white;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background: linear-gradient(to right, rgb(49 46 129), rgb(88 28 135), rgb(88 28 135));
            color: white;
            border-radius: 0.5rem;
            box-shadow:
                inset 0 1px 0 0 rgba(255, 255, 255, 0.2),
                0 2px 0 0 #312e81,
                0 4px 0 0 #1e1b4b,
                0 6px 0 0 #0f172a,
                0 8px 0 0 #020617,
                0 8px 16px 0 rgba(49, 46, 129, 0.5);
            overflow: hidden;
        }

        .small-button {
            width: auto;
            padding: 5px 10px;
            font-size: 0.9em;
            margin-top: 10px;
        }

        .error {
            color: #ff4d4d;
            margin-top: 10px;
        }

        .success {
            color: #52c41a;
            margin-top: 10px;
        }
    </style>
    <script>
        function toggleSetting() {
            var settingMenu = document.querySelector('.sidebar ul');
            settingMenu.style.display = settingMenu.style.display === 'none' ? 'block' : 'none';
        }

        function togglePassword() {
            var oldPassword = document.getElementById('old_password');
            var newPassword = document.getElementById('new_password');
            var confirmPassword = document.getElementById('confirm_password');
            if (oldPassword.type === "password") {
                oldPassword.type = "text";
                newPassword.type = "text";
                confirmPassword.type = "text";
            } else {
                oldPassword.type = "password";
                newPassword.type = "password";
                confirmPassword.type = "password";
            }
        }
    </script>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Logo -->
        <img src="images/Logo SMK.png" alt="Logo Sekolah" class="logo">

        <h2>SISTEM INFORMASI</h2>

        <!-- Info Pengguna -->
        <div class="user-info">
            <div class="profile-picture">
                <!-- TODO: Tambahkan gambar profil pengguna -->
            </div>
            <span><?php echo htmlspecialchars($username); ?></span>
        </div>
        <a href="administrasi_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="administrasi_siswa.php"><i class="fas fa-user-graduate"></i> Administrasi Siswa</a>
        <a href="administrasi_sekolah.php"><i class="fas fa-school"></i> Administrasi Sekolah</a>
        <a href="administrasi_organisasi.php"><i class="fas fa-users"></i> Organisasi</a>
        <a href="administrasi_kegiatan.php"><i class="fas fa-calendar-alt"></i> Kegiatan Sekolah</a>
        <a href="#" onclick="toggleSetting()"><i class="fas fa-cog"></i> Setting</a>
        <ul>
            <li><a href="change_password_administrasi.php"><i class="fas fa-key"></i> Ubah Password</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Container Utama -->
    <div class="container">
        <h2 class="dashboard-title">UBAH PASSWORD</h2>

        <div class="card">
            <h3>Form Ubah Password</h3>
            <form method="POST" class="form-container">
                <?php if ($error) : ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success) : ?>
                    <div class="success"><?php echo $success; ?></div>
                <?php endif; ?>

                <input type="password" name="old_password" placeholder="Password Lama" required>
                <input type="password" name="new_password" placeholder="Password Baru" required>
                <input type="password" name="confirm_password" placeholder="Konfirmasi Password Baru" required>
                <button type="button" onclick="togglePassword()" class="small-button">Lihat Password</button>
                <button type="submit"><i class="fas fa-key"></i> Ubah Password</button>
            </form>
        </div>
    </div>
</body>

</html>