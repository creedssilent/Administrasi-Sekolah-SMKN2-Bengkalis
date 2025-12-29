<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header('Location: index.php');
    exit();
}
include 'config.php';
$teacher_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Ambil data untuk kartu statistik utama
$stats_query = $conn->prepare("
    SELECT 
        (SELECT COUNT(DISTINCT s.id) FROM students s JOIN teacher_schedule ts ON s.class_id = ts.class_id WHERE ts.teacher_id = ?) as total_students,
        (SELECT COUNT(a.id) FROM attendance a JOIN teacher_schedule ts ON a.subject_id = ts.subject_id WHERE ts.teacher_id = ? AND a.date = CURDATE()) as today_attendance,
        (SELECT AVG(g.grade) FROM grades g JOIN teacher_schedule ts ON g.subject_id = ts.subject_id WHERE ts.teacher_id = ?) as average_grade
");
$stats_query->bind_param("iii", $teacher_id, $teacher_id, $teacher_id);
$stats_query->execute();
$stats = $stats_query->get_result()->fetch_assoc();
$stats_query->close();

// Ambil daftar kelas yang diajar guru
$classes_query = $conn->prepare("SELECT DISTINCT c.id, c.name FROM classes c JOIN teacher_schedule ts ON c.id = ts.class_id WHERE ts.teacher_id = ? ORDER BY c.name ASC");
$classes_query->bind_param("i", $teacher_id);
$classes_query->execute();
$classes_result = $classes_query->get_result();
$classes_taught = [];
while ($row = $classes_result->fetch_assoc()) {
    $class_id = $row['id'];

    // Hitung jumlah siswa
    $count_stmt = $conn->prepare("SELECT COUNT(id) as student_count FROM students WHERE class_id = ?");
    $count_stmt->bind_param("i", $class_id);
    $count_stmt->execute();
    $row['student_count'] = $count_stmt->get_result()->fetch_assoc()['student_count'];
    $count_stmt->close();

    // Cek status absensi hari ini
    $att_stmt = $conn->prepare("SELECT COUNT(id) as open_count FROM attendance_open WHERE class_id = ? AND date = CURDATE() AND is_closed = FALSE");
    $att_stmt->bind_param("i", $class_id);
    $att_stmt->execute();
    $is_open = $att_stmt->get_result()->fetch_assoc()['open_count'] > 0;
    $row['attendance_status'] = $is_open ? 'Dibuka' : 'Tutup';
    $att_stmt->close();

    $classes_taught[] = $row;
}
$classes_query->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* TEMA WARNA BARU: "Biru Langit Pucat" */
            --bg-main: #eaf5ff;
            /* Latar Biru Sangat Pucat */
            --bg-card: #ffffff;
            /* Kartu putih */
            --sidebar-bg: #001f3f;
            /* Sidebar Biru Navy */
            --sidebar-text: #a9d2ff;
            --sidebar-text-hover: #ffffff;
            --text-dark: #1e3a5f;
            --text-muted: #6c757d;
            --accent-color: #3c82f6;
            /* Biru Langit */
            --accent-hover: #2563eb;
            --border-color: #e5e7eb;
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
            border-bottom: 1px solid #1a3a5a;
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
        }

        .sidebar .sidebar-title-container p {
            font-size: 0.8em;
            color: var(--sidebar-text);
            margin-top: 4px;
        }

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
        }

        .sidebar .profile-picture img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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

        .sidebar-menu a:hover,
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
        }

        .container {
            padding: 40px;
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
            animation: slideInUp 0.6s ease-out forwards;
            opacity: 0;
            border: 1px solid var(--border-color);
        }

        .dashboard-header {
            margin-bottom: 30px;
            animation: slideInUp 0.5s ease-out forwards;
            opacity: 0;
        }

        .dashboard-header h1 {
            font-size: 2.2em;
            font-weight: 700;
        }

        .dashboard-header p {
            font-size: 1.1em;
            color: var(--text-muted);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            padding: 20px;
            text-align: left;
        }

        .stat-card .icon {
            font-size: 1.5em;
            color: var(--accent-color);
            background-color: #eaf5ff;
            padding: 12px;
            border-radius: 50%;
            margin-bottom: 15px;
            display: inline-block;
        }

        .stat-card .value {
            font-size: 2em;
            font-weight: 700;
        }

        .stat-card .label {
            font-size: 0.9em;
            color: var(--text-muted);
        }

        .section-title {
            font-size: 1.5em;
            font-weight: 600;
            margin-bottom: 20px;
            animation: slideInUp 0.6s ease-out forwards;
            opacity: 0;
            animation-delay: 0.2s;
        }

        .class-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .class-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .class-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(45, 55, 72, 0.1);
        }

        .class-card a {
            text-decoration: none;
            color: inherit;
        }

        .class-card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .class-card-header .icon {
            font-size: 1.8em;
            color: var(--accent-color);
        }

        .class-card-header h3 {
            font-size: 1.2em;
            margin: 0;
            font-weight: 600;
        }

        .class-card-footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
        }

        .class-card-footer a {
            color: var(--accent-color);
            font-weight: 600;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header"><img src="images/Logo SMK.png" alt="Logo Sekolah" class="logo">
                <div class="sidebar-title-container">
                    <h2>SIMANDAKA</h2>
                    <p>SMK Negeri 2 Bengkalis</p>
                </div>
            </div>
            <div class="sidebar-profile">
                <div class="profile-picture"><img src="images/PP.jpg" alt="Foto Profil"></div><span class="profile-name"><?php echo htmlspecialchars($username); ?></span>
            </div>
            <nav class="sidebar-menu">
                <a href="guru_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="absensi_kelas.php"><i class="fas fa-clipboard-check"></i> Buka Absensi</a>
                <a href="lihat_dan_ubah_absensi.php"><i class="fas fa-edit"></i> Lihat Absensi</a>
                <a href="input_dan_total_nilai.php"><i class="fas fa-calculator"></i> Input Nilai</a>
                <a href="#" onclick="toggleSetting()"><i class="fas fa-cog"></i> Setting</a>
                <ul id="settingMenu">
                    <li><a href="change_password_guru.php">Ubah Password</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
        <main class="container">
            <div class="dashboard-header">
                <h1>Dashboard Guru</h1>
                <p>Selamat datang kembali, <?php echo htmlspecialchars($username); ?>.</p>
            </div>
            <div class="stats-grid">
                <div class="card stat-card">
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <div class="value"><?= $stats['total_students'] ?? 0 ?></div>
                    <div class="label">Total Siswa Diajar</div>
                </div>
                <div class="card stat-card">
                    <div class="icon"><i class="fas fa-user-check"></i></div>
                    <div class="value"><?= $stats['today_attendance'] ?? 0 ?></div>
                    <div class="label">Absensi Tercatat Hari Ini</div>
                </div>
                <div class="card stat-card">
                    <div class="icon"><i class="fas fa-star"></i></div>
                    <div class="value"><?= round($stats['average_grade'] ?? 0, 1) ?></div>
                    <div class="label">Rata-Rata Nilai</div>
                </div>
            </div>
            <h2 class="section-title">Kelas yang Anda Ajar</h2>
            <div class="class-grid">
                <?php if (empty($classes_taught)): ?>
                    <div class="card">
                        <p>Anda belum dijadwalkan untuk mengajar kelas manapun.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($classes_taught as $class): ?>
                        <div class="card class-card">
                            <a href="absensi_kelas.php?class_id=<?= $class['id'] ?>">
                                <div class="class-card-header">
                                    <div class="icon"><i class="fas fa-chalkboard-teacher"></i></div>
                                    <h3><?php echo htmlspecialchars($class['name']); ?></h3>
                                </div>
                                <p class="text-muted">Kelola absensi, nilai, dan data siswa untuk kelas ini.</p>
                                <div class="class-card-footer">
                                    <span>Kelola Kelas &rarr;</span>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
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