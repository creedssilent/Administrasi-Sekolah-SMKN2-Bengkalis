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
$student_id = $student['id'];
$class_id = $student['class_id'];
$student_query->close();

if (isset($class_id)) {
    $open_sessions_query = $conn->prepare("SELECT id FROM attendance_open WHERE class_id = ? AND is_closed = FALSE");
    $open_sessions_query->bind_param("i", $class_id);
    $open_sessions_query->execute();
    $result = $open_sessions_query->get_result();
    $open_ids = [];
    while ($row = $result->fetch_assoc()) {
        $open_ids[] = $row['id'];
    }
    $open_sessions_query->close();
    if (!isset($_SESSION['seen_attendance_ids'])) {
        $_SESSION['seen_attendance_ids'] = [];
    }
    $_SESSION['seen_attendance_ids'] = array_unique(array_merge($_SESSION['seen_attendance_ids'], $open_ids));
}

$upload_dir = 'uploads/profile_pictures/';
include 'components/upload_profile_modal.php';

$all_subjects_result = null;
if ($class_id) {
    $all_subjects_query = $conn->prepare("SELECT ts.subject_id, s.name AS subject_name, ts.start_time, ts.end_time FROM teacher_schedule ts JOIN subjects s ON ts.subject_id = s.id WHERE ts.class_id = ?");
    $all_subjects_query->bind_param("i", $class_id);
    $all_subjects_query->execute();
    $all_subjects_result = $all_subjects_query->get_result();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_absen'])) {
    $subject_id = $_POST['subject_id'];
    $status = $_POST['status'];
    $attendance_open_id = $_POST['attendance_open_id'];
    $date = date("Y-m-d");

    $check_query = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND attendance_open_id = ?");
    $check_query->bind_param("ii", $student_id, $attendance_open_id);
    $check_query->execute();
    if ($check_query->get_result()->num_rows > 0) {
        echo "<script>alert('Anda sudah absen untuk sesi ini!');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO attendance (student_id, subject_id, date, status, attendance_open_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissi", $student_id, $subject_id, $date, $status, $attendance_open_id);
        if ($stmt->execute()) {
            echo "<script>alert('Absensi berhasil disimpan.'); window.location.href='absensi.php';</script>";
        } else {
            echo "<script>alert('Gagal menyimpan absensi.');</script>";
        }
        $stmt->close();
    }
    $check_query->close();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Siswa</title>
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
            color: var(--text-dark);
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
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            animation: slideInUp 0.6s ease-out forwards;
            opacity: 0;
            border: 1px solid #e9ecef;
        }

        .card h3 {
            font-size: 1.2em;
            font-weight: 600;
            margin: 0 0 20px 0;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .subject-list {
            list-style: none;
            padding: 0;
        }

        .subject-list li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .subject-list form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .subject-list select {
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #ced4da;
            font-family: 'Poppins';
            background-color: #fff;
            color: var(--text-dark);
        }

        .subject-list button {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .subject-list button:hover {
            background-color: var(--accent-hover);
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
                <a href="absensi.php" class="active"><i class="fas fa-user-check"></i> Absensi</a>
                <a href="riwayat_administrasi.php"><i class="fas fa-file-invoice-dollar"></i> Administrasi</a>
                <a href="riwayat_absensi.php"><i class="fas fa-history"></i> Riwayat Absensi</a>
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
            <h2 class="dashboard-title">Absensi Hari Ini</h2>
            <div class="card">
                <h3>Daftar Mata Pelajaran</h3>
                <ul class="subject-list">
                    <?php if ($all_subjects_result && $all_subjects_result->num_rows > 0): ?>
                        <?php while ($subject = $all_subjects_result->fetch_assoc()):
                            $today = date("Y-m-d");
                            $open_session_query = $conn->prepare("SELECT id FROM attendance_open WHERE class_id = ? AND subject_id = ? AND date = ? AND is_closed = FALSE");
                            $open_session_query->bind_param("iis", $class_id, $subject['subject_id'], $today);
                            $open_session_query->execute();
                            $active_session = $open_session_query->get_result()->fetch_assoc();
                        ?>
                            <li>
                                <div>
                                    <strong style="font-size:1.1em;"><?= htmlspecialchars($subject['subject_name']) ?></strong>
                                    <div style="font-size:0.9em; color: var(--text-muted);"><?= date("H:i", strtotime($subject['start_time'])) ?> - <?= date("H:i", strtotime($subject['end_time'])) ?></div>
                                </div>
                                <?php if ($active_session):
                                    $attendance_open_id = $active_session['id'];
                                    $check_attendance = $conn->prepare("SELECT status FROM attendance WHERE student_id = ? AND attendance_open_id = ?");
                                    $check_attendance->bind_param("ii", $student_id, $attendance_open_id);
                                    $check_attendance->execute();
                                    $already_absent = $check_attendance->get_result()->fetch_assoc();
                                ?>
                                    <?php if ($already_absent): ?>
                                        <span style="font-weight:600; color: #28a745;"><i class="fas fa-check-circle"></i> Sudah Absen: <?= ucfirst(htmlspecialchars($already_absent['status'])) ?></span>
                                    <?php else: ?>
                                        <form method="POST">
                                            <input type="hidden" name="subject_id" value="<?= $subject['subject_id'] ?>">
                                            <input type="hidden" name="attendance_open_id" value="<?= $attendance_open_id ?>">
                                            <select name="status" required>
                                                <option value="hadir">Hadir</option>
                                                <option value="sakit">Sakit</option>
                                                <option value="izin">Izin</option>
                                            </select>
                                            <button type="submit" name="submit_absen">Kirim</button>
                                        </form>
                                    <?php endif;
                                    $check_attendance->close(); ?>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);"><i class="fas fa-times-circle"></i> Absensi Ditutup</span>
                                <?php endif;
                                $open_session_query->close(); ?>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li>Tidak ada mata pelajaran yang dijadwalkan hari ini.</li>
                    <?php endif; ?>
                </ul>
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