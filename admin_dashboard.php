<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}
include 'config.php';
$username = $_SESSION['username'];
// Ambil data statistik
$total_pengguna = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$pengguna_aktif = $conn->query("SELECT COUNT(*) AS total FROM users WHERE status = 'active'")->fetch_assoc()['total'];
$total_kelas = $conn->query("SELECT COUNT(*) AS total FROM classes")->fetch_assoc()['total'];
$total_siswa = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];
$total_guru = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'guru'")->fetch_assoc()['total'];
$total_administrasi = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'administrasi'")->fetch_assoc()['total'];
$total_admin = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'")->fetch_assoc()['total'];
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sidebar-bg: #111827;
            --sidebar-link-color: #9ca3af;
            --sidebar-link-hover-bg: #374151;
            --sidebar-link-active-bg: #4f46e5;
        }

        body.dark-theme {
            --main-bg: #0f172a;
            --panel-bg: #1e293b;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --border-color: #334155;
            --accent-purple: #8b5cf6;
            --accent-blue: #38bdf8;
        }

        body.light-theme {
            --main-bg: #f1f5f9;
            --panel-bg: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --accent-purple: #7c3aed;
            --accent-blue: #0ea5e9;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--main-bg);
            color: var(--text-primary);
            margin: 0;
            transition: background-color 0.3s, color 0.3s;
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
            letter-spacing: 1px;
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
            -ms-overflow-style: none;
        }

        .sidebar-menu::-webkit-scrollbar {
            display: none;
        }

        .sidebar-menu h3 {
            color: #6b7280;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
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

        .sidebar-menu ul li a {
            font-size: 0.9em;
            padding-left: 25px;
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

        .top-nav .welcome-message h1 {
            font-size: 1.5em;
            margin: 0;
            font-weight: 600;
        }

        .top-nav .profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .top-nav .profile-info {
            text-align: right;
        }

        .top-nav .profile-info .user-name {
            font-weight: 600;
            font-size: 0.9em;
        }

        .top-nav .profile-info .user-role {
            font-size: 0.8em;
            color: var(--text-secondary);
        }

        .top-nav .profile-dropdown-toggle {
            cursor: pointer;
        }

        .top-nav .profile-dropdown-toggle img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .profile-dropdown-container {
            position: relative;
        }

        .top-nav .profile-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 55px;
            background-color: var(--panel-bg);
            border-radius: 8px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 100;
            width: 200px;
            overflow: hidden;
        }

        .profile-dropdown .theme-switcher {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: var(--text-secondary);
            font-size: 0.9em;
        }

        .theme-switcher label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            cursor: pointer;
        }

        .main-content {
            padding: 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: var(--panel-bg);
            padding: 25px;
            border-radius: 12px;
        }

        .stat-card .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--text-secondary);
            font-size: 0.9em;
        }

        .stat-card .value {
            font-size: 2.2em;
            font-weight: 700;
            margin-top: 5px;
        }

        .chart-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .chart-container {
            background-color: var(--panel-bg);
            padding: 25px;
            border-radius: 12px;
        }

        .chart-wrapper {
            position: relative;
            height: 320px;
        }

        /* Perbaikan untuk bug rendering */
        .chart-container h3 {
            margin-top: 0;
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="dark-theme">
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
                <h3>Navigation</h3>
                <a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <h3>Management</h3>
                <a href="manage_users.php"><i class="fas fa-users-cog"></i> Kelola Pengguna</a>
                <a href="manage_classes.php"><i class="fas fa-school"></i> Kelola Kelas</a>
                <a href="manage_subjects.php"><i class="fas fa-book"></i> Kelola Mapel</a>
                <a href="manage_students.php"><i class="fas fa-user-graduate"></i> Kelola Siswa</a>
                <a href="manage_teachers.php"><i class="fas fa-chalkboard-teacher"></i> Kelola Guru</a>
                <a href="manage_administrations.php"><i class="fas fa-file-invoice"></i> Kelola Administrasi</a>
                <h3>Other</h3>
                <a href="#" onclick="toggleSetting(event)"><i class="fas fa-cog"></i> Setting</a>
                <ul id="settingMenu">
                    <li><a href="change_password_admin.php"><i class="fas fa-key"></i> Ubah Password</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <div class="main-wrapper">
            <header class="top-nav">
                <div class="welcome-message">
                    <h1>Welcome, Admin!</h1>
                </div>
                <div class="profile">
                    <div class="profile-info">
                        <div class="user-name"><?php echo htmlspecialchars($username); ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                    <div class="profile-dropdown-container">
                        <div class="profile-dropdown-toggle" onclick="toggleProfileDropdown()"><img src="images/PP.jpg" alt="Admin"></div>
                        <div class="profile-dropdown" id="profileDropdown">
                            <div class="theme-switcher"><label for="theme-toggle"><span><i class="fas fa-palette"></i> Ganti Tema</span><input type="checkbox" id="theme-toggle" style="display:none;"></label></div>
                        </div>
                    </div>
                </div>
            </header>
            <main class="main-content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="header"><span>Total Pengguna</span><i style="color:var(--accent-purple);" class="fas fa-users"></i></div>
                        <div class="value"><?= $total_pengguna ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="header"><span>Pengguna Aktif</span><i style="color:var(--accent-blue);" class="fas fa-user-check"></i></div>
                        <div class="value"><?= $pengguna_aktif ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="header"><span>Total Kelas</span><i style="color:#34d399;" class="fas fa-chalkboard"></i></div>
                        <div class="value"><?= $total_kelas ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="header"><span>Total Siswa</span><i style="color:#f87171;" class="fas fa-graduation-cap"></i></div>
                        <div class="value"><?= $total_siswa ?></div>
                    </div>
                </div>
                <div class="chart-grid">
                    <div class="chart-container">
                        <h3>Aktivitas Sistem (7 Hari Terakhir)</h3>
                        <div class="chart-wrapper"><canvas id="mainChart"></canvas></div>
                    </div>
                    <div class="chart-container">
                        <h3>Komposisi Pengguna</h3>
                        <div class="chart-wrapper"><canvas id="userCompositionChart"></canvas></div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        function toggleProfileDropdown() {
            document.getElementById('profileDropdown').style.display = document.getElementById('profileDropdown').style.display === 'block' ? 'none' : 'block';
        }

        function toggleSetting(event) {
            event.preventDefault();
            document.getElementById('settingMenu').style.display = document.getElementById('settingMenu').style.display === 'block' ? 'none' : 'block';
        }
        window.onclick = function(event) {
            if (!event.target.closest('.profile-dropdown-container')) {
                document.getElementById('profileDropdown').style.display = 'none';
            }
        }

        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;
        const currentTheme = localStorage.getItem('theme') || 'dark-theme';
        if (currentTheme) {
            body.classList.add(currentTheme);
            if (currentTheme === 'light-theme') themeToggle.checked = true;
        }
        themeToggle.addEventListener('change', function() {
            body.classList.toggle('light-theme');
            let theme = body.classList.contains('light-theme') ? 'light-theme' : 'dark-theme';
            localStorage.setItem('theme', theme);
            updateChartColors(theme);
        });

        function updateChartColors(theme) {
            const isLight = theme === 'light-theme';
            const gridColor = isLight ? 'rgba(0, 0, 0, 0.1)' : 'rgba(255, 255, 255, 0.1)';
            const textColor = isLight ? '#64748b' : '#94a3b8';
            const accentPurple = getComputedStyle(document.body).getPropertyValue('--accent-purple').trim();

            mainChart.data.datasets[0].borderColor = accentPurple;
            mainChart.data.datasets[0].backgroundColor = accentPurple + '20';
            mainChart.options.scales.x.ticks.color = textColor;
            mainChart.options.scales.y.ticks.color = textColor;
            mainChart.options.scales.x.grid.color = gridColor;
            mainChart.options.scales.y.grid.color = gridColor;
            mainChart.options.plugins.legend.labels.color = textColor;
            mainChart.update();
            userCompositionChart.options.plugins.legend.labels.color = textColor;
            userCompositionChart.update();
        }

        Chart.defaults.font.family = 'Inter';
        const mainCtx = document.getElementById('mainChart').getContext('2d');
        const mainChart = new Chart(mainCtx, {
            type: 'line',
            data: {
                labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                datasets: [{
                    label: 'Aktivitas',
                    data: [65, 59, 80, 81, 56, 55, 90],
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: {
                            drawBorder: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {}
                    }
                }
            }
        });
        const userCtx = document.getElementById('userCompositionChart').getContext('2d');
        const userCompositionChart = new Chart(userCtx, {
            type: 'doughnut',
            data: {
                labels: ['Siswa', 'Guru', 'Administrasi', 'Admin'],
                datasets: [{
                    data: [<?= $total_siswa ?>, <?= $total_guru ?>, <?= $total_administrasi ?>, <?= $total_admin ?>],
                    backgroundColor: ['#38bdf8', '#34d399', '#f59e0b', '#f87171'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {}
                    }
                }
            }
        });

        updateChartColors(localStorage.getItem('theme') || 'dark-theme');
    </script>
</body>

</html>