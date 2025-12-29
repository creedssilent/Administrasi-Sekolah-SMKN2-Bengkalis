<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala_sekolah') {
    header('Location: index.php');
    exit();
}
include 'config.php';
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kepala Sekolah</title>
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

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: var(--sidebar-link-active-bg);
            color: #fff;
        }

        .sidebar-menu a i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }

        .sidebar-menu ul {
            list-style: none;
            padding-left: 15px;
            margin: 0;
            display: none;
        }

        .main-wrapper {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 30px;
        }

        .page-header h1 {
            font-size: 1.8em;
            font-weight: 600;
            margin: 0 0 5px 0;
        }

        .page-header p {
            margin: 0 0 20px 0;
            color: var(--text-secondary);
        }

        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .report-card {
            background-color: var(--panel-bg);
            border-radius: 12px;
            padding: 25px;
            text-decoration: none;
            color: var(--text-primary);
            display: block;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 100, 100, 0.1);
        }

        .report-card .icon {
            font-size: 2.5em;
            color: var(--sidebar-link-active-bg);
            margin-bottom: 15px;
        }

        .report-card h3 {
            margin: 0 0 10px 0;
            font-size: 1.2em;
        }

        .report-card p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 0.9em;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="images/Logo SMK.png" class="logo" alt="Logo">
                <div class="sidebar-title">
                    <h2>SIMANDAKA</h2>
                    <p>SMK Negeri 2 Bengkalis</p>
                </div>
            </div>
            <nav class="sidebar-menu">
                <a href="#" class="active"><i class="fas fa-home"></i> Dasbor</a>
                <a href="#" onclick="toggleSetting(event)"><i class="fas fa-cog"></i> Setting</a>
                <ul id="settingMenu">
                    <li><a href="change_password_ks.php"><i class="fas fa-key"></i> Ubah Password</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <div class="main-wrapper">
            <div class="page-header">
                <h1>Dasbor Kepala Sekolah</h1>
                <p>Selamat datang, <?php echo htmlspecialchars($username); ?>. Silakan pilih laporan yang ingin Anda lihat.</p>
            </div>
            <div class="report-grid">
                <a href="laporan_kehadiran.php" class="report-card">
                    <div class="icon"><i class="fas fa-calendar-check"></i></div>
                    <h3>Laporan Kehadiran</h3>
                    <p>Pantau rekapitulasi kehadiran siswa secara harian, mingguan, atau bulanan.</p>
                </a>
                <a href="laporan_keuangan.php" class="report-card">
                    <div class="icon"><i class="fas fa-wallet"></i></div>
                    <h3>Laporan Keuangan</h3>
                    <p>Lihat ringkasan pemasukan, pengeluaran, dan saldo akhir keuangan sekolah.</p>
                </a>
                <a href="laporan_nilai.php" class="report-card">
                    <div class="icon"><i class="fas fa-graduation-cap"></i></div>
                    <h3>Laporan Nilai Akademik</h3>
                    <p>Analisis performa nilai siswa per kelas dan per mata pelajaran.</p>
                </a>
                <a href="laporan_guru.php" class="report-card">
                    <div class="icon"><i class="fas fa-chalkboard-teacher"></i></div>
                    <h3>Laporan Data Guru</h3>
                    <p>Lihat daftar guru beserta mata pelajaran yang diampu dan jadwal mengajarnya.</p>
                </a>
            </div>
        </div>
    </div>
    <script>
        function toggleSetting(event) {
            event.preventDefault();
            var menu = document.getElementById('settingMenu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</body>

</html>