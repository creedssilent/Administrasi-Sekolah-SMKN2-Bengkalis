<?php
// FILE: administrasi_dashboard.php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrasi') {
    header('Location: index.php');
    exit();
}
include 'config.php';

if (!isset($_SESSION['username'])) {
    echo "<script>alert('Kesalahan sesi. Silakan login kembali.'); window.location.href='index.php';</script>";
    exit();
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrasi</title>
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
        button {
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

        /* Responsif */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                align-items: center;
            }

            .sidebar h2 {
                text-align: center;
            }

            .container {
                padding: 20px;
            }
        }
    </style>
    <script>
        function toggleSetting() {
            var settingMenu = document.querySelector('.sidebar ul');
            settingMenu.style.display = settingMenu.style.display === 'none' ? 'block' : 'none';
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
        <h2 class="dashboard-title">DASHBOARD ADMINISTRASI</h2>

        <div class="card">
            <h3>Selamat Datang di Dashboard Administrasi</h3>
            <p>Kelola informasi sekolah dengan mudah dan efisien.</p>
        </div>
    </div>
</body>

</html>