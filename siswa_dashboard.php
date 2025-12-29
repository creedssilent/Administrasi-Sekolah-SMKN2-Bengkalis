<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header('Location: index.php');
    exit();
}
include 'config.php';
$username = $_SESSION['username'];
$student_query = $conn->prepare("SELECT * FROM students WHERE username = ?");
$student_query->bind_param("s", $username);
$student_query->execute();
$student_result = $student_query->get_result();
if (!$student_result || $student_result->num_rows === 0) {
    session_destroy();
    echo "<script>alert('Data siswa tidak ditemukan.'); window.location.href = 'index.php';</script>";
    exit();
}
$student = $student_result->fetch_assoc();
$student_query->close();
$biodata_complete = !empty($student['name']) && !empty($student['class_id']);
$user_id = $student['id'];
$current_profile_picture = $student['profile_picture'];
$user_table = 'students';
$upload_dir = 'uploads/profile_pictures/';
$new_notification_count = 0;
if ($biodata_complete) {
    $open_sessions_query = $conn->prepare("SELECT id FROM attendance_open WHERE class_id = ? AND is_closed = FALSE");
    $open_sessions_query->bind_param("i", $student['class_id']);
    $open_sessions_query->execute();
    $result = $open_sessions_query->get_result();
    $all_open_ids = [];
    while ($row = $result->fetch_assoc()) {
        $all_open_ids[] = $row['id'];
    }
    $open_sessions_query->close();
    if (!isset($_SESSION['seen_attendance_ids'])) {
        $_SESSION['seen_attendance_ids'] = [];
    }
    $new_notification_ids = array_diff($all_open_ids, $_SESSION['seen_attendance_ids']);
    $new_notification_count = count($new_notification_ids);
}
include 'components/upload_profile_modal.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* TEMA WARNA BARU: "Soft & Calm" (Putih Abu-abu & Biru) */
            --bg-main: #f0f2f5;
            /* Latar abu-abu lembut (tidak silau) */
            --bg-card: #ffffff;
            /* Kartu putih bersih */
            --sidebar-bg: #2d3748;
            /* Sidebar abu-abu gelap */
            --sidebar-text: #a0aec0;
            /* Teks di sidebar */
            --sidebar-text-hover: #ffffff;
            --text-dark: #2d3748;
            /* Teks gelap untuk latar terang */
            --text-muted: #718096;
            /* Teks abu-abu */
            --accent-color: #4299e1;
            /* Biru yang lebih lembut */
            --accent-hover: #2b6cb0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-dark);
            margin: 0;
        }

        .dashboard-container {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background-color: var(--sidebar-bg);
            padding: 25px;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: sticky;
            top: 0;
        }

        .sidebar-header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #4a5568;
        }

        .sidebar .logo {
            width: 80px;
            margin-bottom: 15px;
        }

        .sidebar .sidebar-title-container h2 {
            font-size: 1.3em;
            font-weight: 600;
            color: var(--sidebar-text-hover);
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sidebar .sidebar-title-container p {
            font-size: 0.8em;
            color: var(--sidebar-text);
            margin-top: 4px;
        }

        /* Warna diperterang */
        .sidebar-profile {
            text-align: center;
            padding: 25px 0;
        }

        .sidebar .profile-picture {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 3px solid var(--accent-color);
            margin: 0 auto 15px auto;
            overflow: hidden;
            cursor: pointer;
            position: relative;
        }

        .sidebar .profile-picture img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sidebar .profile-picture:hover::after {
            content: "\f030";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5em;
            border-radius: 50%;
        }

        .sidebar .profile-name {
            font-weight: 500;
            color: var(--sidebar-text-hover);
        }

        .sidebar-menu {
            flex-grow: 1;
            width: 100%;
            overflow-y: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .sidebar-menu::-webkit-scrollbar {
            display: none;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 8px;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .sidebar-menu a i {
            margin-right: 15px;
            font-size: 1.2em;
            width: 20px;
            text-align: center;
        }

        .sidebar-menu a:hover {
            background-color: var(--accent-color);
            color: #fff;
        }

        .sidebar-menu a.active {
            background-color: var(--accent-color);
            color: #fff;
            font-weight: 600;
        }

        .sidebar-menu ul {
            list-style: none;
            padding-left: 15px;
            width: 100%;
            margin-top: 5px;
            display: none;
            /* PERBAIKAN: Pastikan tersembunyi */
        }

        .container {
            padding: 40px;
            overflow-y: auto;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            background-color: var(--bg-card);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: slideInUp 0.6s ease-out forwards;
            opacity: 0;
            border: 1px solid #e2e8f0;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(45, 55, 72, 0.1);
        }

        .dashboard-header {
            margin-bottom: 30px;
            animation: slideInUp 0.5s ease-out forwards;
        }

        .dashboard-header h1 {
            font-size: 2.2em;
            font-weight: 700;
            color: var(--text-dark);
        }

        .dashboard-header p {
            font-size: 1.1em;
            color: var(--text-muted);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }

        .focus-card {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, var(--accent-color), var(--accent-hover));
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 30px;
        }

        .focus-card h2 {
            font-size: 1.5em;
            margin: 0;
        }

        .focus-card p {
            margin: 5px 0 0;
            opacity: 0.9;
        }

        .focus-card a.btn {
            background-color: rgba(255, 255, 255, 0.25);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.3s;
            cursor: pointer;
        }

        .focus-card a.btn:hover {
            background-color: rgba(255, 255, 255, 0.4);
            transform: scale(1.05);
        }

        /* PERBAIKAN: Animasi Tombol */
        .stat-card .label {
            font-size: 0.9em;
            color: var(--text-muted);
        }

        .stat-card .value {
            font-size: 2.2em;
            font-weight: 700;
            color: var(--accent-color);
        }

        .timeline-card {
            grid-column: 1 / -1;
        }

        .timeline-card h3 {
            color: var(--text-dark);
        }

        .timeline {
            list-style: none;
            padding-left: 20px;
            border-left: 2px solid #e2e8f0;
        }

        .timeline li {
            margin-bottom: 20px;
            position: relative;
        }

        .timeline li::before {
            content: '';
            width: 12px;
            height: 12px;
            background: var(--accent-color);
            border-radius: 50%;
            position: absolute;
            left: -27px;
            top: 5px;
            border: 3px solid var(--bg-main);
        }

        <?php for ($i = 1; $i <= 5; $i++): ?>.card:nth-child(<?php echo $i; ?>) {
            animation-delay: <?php echo $i * 0.1; ?>s;
        }

        <?php endfor; ?>
    </style>
</head>

<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="images/Logo SMK.png" alt="Logo Sekolah" class="logo">
                <div class="sidebar-title-container">
                    <h2>SIMANDAKA</h2>
                    <p>SMK Negeri 2 Bengkalis</p>
                </div>
            </div>
            <div class="sidebar-profile">
                <div class="profile-picture" id="profile-picture-container">
                    <img src="<?php echo (!empty($student['profile_picture']) && file_exists($upload_dir . $student['profile_picture'])) ? $upload_dir . $student['profile_picture'] : 'images/default_profile.png'; ?>" alt="Foto Profil">
                </div>
                <span class="profile-name"><?php echo htmlspecialchars($student['name'] ?? 'Siswa'); ?></span>
            </div>
            <nav class="sidebar-menu">
                <a href="siswa_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="input_biodata.php"><i class="fas fa-id-card"></i> Biodata</a>
                <a href="absensi.php"><i class="fas fa-user-check"></i> Absensi</a>
                <a href="riwayat_administrasi.php"><i class="fas fa-file-invoice-dollar"></i> Administrasi</a>
                <a href="riwayat_absensi.php"><i class="fas fa-history"></i> Riwayat Absensi</a>
                <a href="lihat_nilai.php"><i class="fas fa-graduation-cap"></i> Lihat Nilai</a>
                <a href="cetak_laporan.php"><i class="fas fa-print"></i> Cetak Lapor</a>
                <a href="#" onclick="toggleSetting()"><i class="fas fa-cog"></i> Setting</a>
                <ul id="settingMenu">
                    <li><a href="change_password_siswa.php"><i class="fas fa-key"></i> Ubah Password</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
        <main class="container">
            <?php if (!$biodata_complete): ?>
                <div class="card" style="text-align:center; background: #fff3cd; color: #664d03;">
                    <h2>Lengkapi Biodata Anda</h2>
                    <p>Fitur dashboard akan aktif setelah biodata Anda lengkap.</p>
                    <a href="input_biodata.php" style="background-color: var(--accent-color); color:white; padding: 10px 20px; border-radius: 8px; text-decoration:none; display:inline-block; margin-top:15px;">Lengkapi Sekarang</a>
                </div>
            <?php else: ?>
                <div class="dashboard-header">
                    <h1>Selamat Datang, <?php echo htmlspecialchars(explode(' ', $student['name'])[0]); ?>!</h1>
                    <p>Siap untuk belajar hari ini?</p>
                </div>
                <div class="dashboard-grid">
                    <div class="card focus-card">
                        <div>
                            <h2><?php echo ($new_notification_count > 0) ? $new_notification_count . " Absensi Dibuka" : "Tidak Ada Absensi Terbuka"; ?></h2>
                            <p>Jangan lupa untuk mengisi kehadiranmu tepat waktu.</p>
                        </div>
                        <a href="absensi.php" class="btn">Isi Sekarang <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="card stat-card">
                        <div class="label">Total Kehadiran</div>
                        <div class="value">98%</div>
                    </div>
                    <div class="card stat-card">
                        <div class="label">Rata-Rata Nilai</div>
                        <div class="value">85.7</div>
                    </div>
                    <div class="card stat-card">
                        <div class="label">Pelajaran Hari Ini</div>
                        <div class="value">5</div>
                    </div>
                    <div class="card timeline-card">
                        <h3>Aktivitas Terbaru</h3>
                        <ul class="timeline">
                            <li>
                                <div class="event">Nilai Matematika telah di-input.</div>
                                <div class="time">Kemarin</div>
                            </li>
                            <li>
                                <div class="event">Anda ditandai 'Hadir' pada pelajaran Fisika.</div>
                                <div class="time">2 Hari yang Lalu</div>
                            </li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <script>
        function toggleSetting() {
            var settingMenu = document.querySelector('.sidebar-menu ul');
            if (settingMenu) {
                settingMenu.style.display = settingMenu.style.display === 'none' || settingMenu.style.display === '' ? 'block' : 'none';
            }
        }
    </script>
</body>

</html>