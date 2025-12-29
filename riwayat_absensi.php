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
$student = $student_query->get_result()->fetch_assoc();
$student_query->close();
$user_id = $student['id'];
$subjects = [];
$dates = [];
$attendance_data = [];
$subjects_query = $conn->prepare("SELECT DISTINCT s.id, s.name FROM subjects s JOIN attendance a ON s.id = a.subject_id WHERE a.student_id = ? ORDER BY s.name ASC");
$subjects_query->bind_param("i", $user_id);
$subjects_query->execute();
$subjects_result = $subjects_query->get_result();
while ($row = $subjects_result->fetch_assoc()) {
    $subjects[$row['id']] = $row['name'];
}
$subjects_query->close();
if (!empty($subjects)) {
    $dates_query = $conn->prepare("SELECT DISTINCT date FROM attendance WHERE student_id = ? ORDER BY date ASC");
    $dates_query->bind_param("i", $user_id);
    $dates_query->execute();
    $dates_result = $dates_query->get_result();
    while ($row = $dates_result->fetch_assoc()) {
        $dates[] = $row['date'];
    }
    $dates_query->close();
    $attendance_query = $conn->prepare("SELECT subject_id, date, status FROM attendance WHERE student_id = ?");
    $attendance_query->bind_param("i", $user_id);
    $attendance_query->execute();
    $attendance_result = $attendance_query->get_result();
    while ($row = $attendance_result->fetch_assoc()) {
        $attendance_data[$row['subject_id']][$row['date']] = $row['status'];
    }
    $attendance_query->close();
}
$upload_dir = 'uploads/profile_pictures/';
include 'components/upload_profile_modal.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Absensi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-main: #f0f2f5;
            --bg-card: #ffffff;
            --sidebar-bg: #2d3748;
            --sidebar-text: #a0aec0;
            --sidebar-text-hover: #ffffff;
            --text-dark: #2d3748;
            --text-muted: #718096;
            --accent-color: #4299e1;
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

        .dashboard-title {
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 20px;
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
            padding: 5px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            animation: slideInUp 0.6s ease-out forwards;
            opacity: 0;
        }

        .table-container {
            overflow-x: auto;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            white-space: nowrap;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: center;
            border: 1px solid #e9ecef;
        }

        th {
            font-weight: 600;
            font-size: 0.9em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        td:first-child,
        th:first-child {
            text-align: left;
            position: sticky;
            left: 0;
            background-color: var(--bg-card);
            z-index: 1;
            font-weight: 500;
        }

        th:first-child {
            z-index: 2;
        }

        .status-hadir {
            color: #28a745;
            font-weight: 500;
        }

        .status-sakit {
            color: #ffc107;
        }

        .status-izin {
            color: #17a2b8;
        }

        .status-alfa {
            color: #dc3545;
            font-weight: 700;
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
                <div class="profile-picture" id="profile-picture-container"><img src="<?php echo (!empty($student['profile_picture']) && file_exists($upload_dir . $student['profile_picture'])) ? $upload_dir . $student['profile_picture'] : 'images/default_profile.png'; ?>" alt="Foto Profil"></div><span class="profile-name"><?php echo htmlspecialchars($student['name'] ?? 'Siswa'); ?></span>
            </div>
            <nav class="sidebar-menu">
                <a href="siswa_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="input_biodata.php"><i class="fas fa-id-card"></i> Biodata</a>
                <a href="absensi.php"><i class="fas fa-user-check"></i> Absensi</a>
                <a href="riwayat_administrasi.php"><i class="fas fa-file-invoice-dollar"></i> Administrasi</a>
                <a href="riwayat_absensi.php" class="active"><i class="fas fa-history"></i> Riwayat Absensi</a>
                <a href="lihat_nilai.php"><i class="fas fa-graduation-cap"></i> Lihat Nilai</a>
                <a href="cetak_laporan.php"><i class="fas fa-print"></i> Cetak Lapor</a>
                <a href="#" onclick="toggleSetting()"><i class="fas fa-cog"></i> Setting</a>
                <ul id="settingMenu">
                    <li><a href="change_password_siswa.php">Ubah Password</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
        <main class="container">
            <h2 class="dashboard-title">Riwayat Absensi</h2>
            <div class="card">
                <div class="table-container">
                    <?php if (!empty($subjects)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Mata Pelajaran</th>
                                    <?php foreach ($dates as $date): ?><th><?= date('d M Y', strtotime($date)) ?></th><?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subjects as $subject_id => $subject_name): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($subject_name) ?></td>
                                        <?php foreach ($dates as $date): ?>
                                            <?php $status = $attendance_data[$subject_id][$date] ?? '-';
                                            $status_class = 'status-' . strtolower($status); ?>
                                            <td class="<?= htmlspecialchars($status_class) ?>"><?= ucfirst(htmlspecialchars($status)) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="text-align: center; padding: 20px;">Tidak ada data riwayat absensi untuk ditampilkan.</p>
                    <?php endif; ?>
                </div>
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