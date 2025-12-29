<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala_sekolah') {
    header('Location: index.php');
    exit();
}
include 'config.php';

// Kueri untuk menghitung jumlah guru
$total_guru_result = $conn->query("SELECT COUNT(id) as total FROM users WHERE role = 'guru'");
$total_guru = $total_guru_result->fetch_assoc()['total'];

// Kueri untuk mengambil data jadwal guru
$sql = "SELECT t.name as teacher_name, u.username, s.name as subject_name, c.name as class_name, 
               DATE_FORMAT(ts.start_time, '%H:%i') as start_time, 
               DATE_FORMAT(ts.end_time, '%H:%i') as end_time
        FROM teacher_schedule ts
        JOIN users u ON ts.teacher_id = u.id
        LEFT JOIN teachers t ON u.username = t.name -- Diasumsikan relasi melalui username, perbaiki jika ada relasi ID langsung
        JOIN subjects s ON ts.subject_id = s.id
        JOIN classes c ON ts.class_id = c.id
        WHERE u.role = 'guru'
        ORDER BY t.name, ts.start_time";

$result = $conn->query($sql);
$teacher_report = [];
while ($row = $result->fetch_assoc()) {
    $teacher_report[$row['teacher_name']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Data Guru</title>
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

        .sidebar-menu a.active {
            background-color: var(--sidebar-link-active-bg);
            color: #fff;
        }

        .sidebar-menu a i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }

        .main-wrapper {
            margin-left: 250px;
            width: calc(100% - 250px);
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
        }

        .card h2 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.25em;
        }

        .stat-card {
            background-color: #eef2ff;
            border-left: 5px solid #4f46e5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .stat-card .value {
            font-size: 2em;
            font-weight: 700;
            color: #4338ca;
        }

        .stat-card .label {
            font-size: 1em;
            color: var(--text-secondary);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
            text-align: left;
        }

        .teacher-name-header {
            background-color: #f8f9fa;
            font-weight: 600;
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
                <a href="kepala_sekolah_dashboard.php"><i class="fas fa-home"></i> Dasbor</a>
                <a href="laporan_guru.php" class="active"><i class="fas fa-chalkboard-teacher"></i> Laporan Guru</a>
            </nav>
        </aside>
        <div class="main-wrapper">
            <div class="page-header">
                <h1>Laporan Data Guru & Jadwal Mengajar</h1>
            </div>

            <div class="stat-card">
                <div class="value"><?= $total_guru ?></div>
                <div class="label">Total Guru Terdaftar</div>
            </div>

            <div class="card">
                <h2>Detail Jadwal Mengajar per Guru</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($teacher_report)): ?>
                            <?php foreach ($teacher_report as $teacher_name => $schedules): ?>
                                <tr class="teacher-name-header">
                                    <td colspan="3">
                                        <i class="fas fa-user-circle"></i>
                                        <strong><?= htmlspecialchars($teacher_name) ?></strong>
                                        (<?= htmlspecialchars($schedules[0]['username']) ?>)
                                    </td>
                                </tr>
                                <?php foreach ($schedules as $schedule): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($schedule['subject_name']) ?></td>
                                        <td><?= htmlspecialchars($schedule['class_name']) ?></td>
                                        <td><?= $schedule['start_time'] . ' - ' . $schedule['end_time'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align:center;">Tidak ada data jadwal guru yang ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>