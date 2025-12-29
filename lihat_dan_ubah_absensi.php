<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header('Location: index.php');
    exit();
}
include 'config.php';
$teacher_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance_id'])) {
    $attendance_id = $_POST['attendance_id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE attendance SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $attendance_id);
    if ($stmt->execute()) {
        echo "<script>alert('Status berhasil diperbarui!'); window.location.href='lihat_dan_ubah_absensi.php?class_id=$class_id&subject_id=$subject_id';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui status.');</script>";
    }
    $stmt->close();
    exit();
}
$classes_query = $conn->prepare("SELECT DISTINCT c.id, c.name FROM classes c JOIN teacher_schedule ts ON c.id = ts.class_id WHERE ts.teacher_id = ? ORDER BY c.name ASC");
$classes_query->bind_param("i", $teacher_id);
$classes_query->execute();
$classes = $classes_query->get_result();
$subjects = null;
if ($class_id) {
    $subjects_query = $conn->prepare("SELECT DISTINCT s.id, s.name FROM subjects s JOIN teacher_schedule ts ON s.id = ts.subject_id WHERE ts.class_id = ? AND ts.teacher_id = ?");
    $subjects_query->bind_param("ii", $class_id, $teacher_id);
    $subjects_query->execute();
    $subjects = $subjects_query->get_result();
}
$students = [];
$dates = [];
$attendance_data = [];
if ($class_id && $subject_id) {
    $students_query = $conn->prepare("SELECT id, name FROM students WHERE class_id = ? ORDER BY name ASC");
    $students_query->bind_param("i", $class_id);
    $students_query->execute();
    $students_result = $students_query->get_result();
    while ($row = $students_result->fetch_assoc()) {
        $students[] = $row;
    }
    $dates_query = $conn->prepare("SELECT DISTINCT date FROM attendance WHERE subject_id = ? AND student_id IN (SELECT id FROM students WHERE class_id = ?) ORDER BY date ASC");
    $dates_query->bind_param("ii", $subject_id, $class_id);
    $dates_query->execute();
    $dates_result = $dates_query->get_result();
    while ($row = $dates_result->fetch_assoc()) {
        $dates[] = $row['date'];
    }
    $attendance_query = $conn->prepare("SELECT id, student_id, date, status FROM attendance WHERE subject_id = ? AND student_id IN (SELECT id FROM students WHERE class_id = ?)");
    $attendance_query->bind_param("ii", $subject_id, $class_id);
    $attendance_query->execute();
    $attendance_result = $attendance_query->get_result();
    while ($row = $attendance_result->fetch_assoc()) {
        $attendance_data[$row['student_id']][$row['date']] = ['id' => $row['id'], 'status' => $row['status']];
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat & Ubah Absensi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-main: #f0f5fa;
            --bg-card: #ffffff;
            --sidebar-bg: #001f3f;
            --sidebar-text: #a9d2ff;
            --sidebar-text-hover: #ffffff;
            --text-dark: #1e3a5f;
            --text-muted: #6c757d;
            --accent-color: #3c82f6;
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
            font-weight: 500;
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
        }

        .sidebar .profile-picture img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: var(--accent-color);
            color: #fff;
            font-weight: 600;
        }

        .sidebar-menu a i {
            margin-right: 15px;
            font-size: 1.2em;
            width: 20px;
            text-align: center;
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
            border: 1px solid var(--border-color);
            margin-bottom: 25px;
        }

        .filter-container {
            display: flex;
            gap: 20px;
        }

        .form-group {
            flex: 1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        select {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #cbd5e0;
        }

        .table-container {
            overflow-x: auto;
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
            color: var(--text-muted);
        }

        th:first-child,
        td:first-child {
            text-align: left;
            position: sticky;
            left: 0;
            background-color: var(--bg-card);
            z-index: 1;
        }

        th:first-child {
            z-index: 2;
        }

        td form {
            display: flex;
            gap: 5px;
            align-items: center;
            justify-content: center;
        }

        td select {
            width: 90px;
            padding: 5px;
            font-size: 0.9em;
        }

        td button {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 5px;
            cursor: pointer;
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
                <a href="guru_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="absensi_kelas.php"><i class="fas fa-clipboard-check"></i> Buka Absensi</a>
                <a href="lihat_dan_ubah_absensi.php" class="active"><i class="fas fa-edit"></i> Lihat Absensi</a>
                <a href="input_dan_total_nilai.php"><i class="fas fa-calculator"></i> Input Nilai</a>
                <a href="#" onclick="toggleSetting()"><i class="fas fa-cog"></i> Setting</a>
                <ul id="settingMenu">
                    <li><a href="change_password_guru.php">Ubah Password</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
        <main class="container">
            <h2 class="dashboard-title">Lihat & Ubah Absensi</h2>
            <div class="card">
                <div class="filter-container">
                    <div class="form-group"><label>Pilih Kelas:</label><select onchange="window.location.href='?class_id='+this.value">
                            <option value="">-- Pilih --</option><?php mysqli_data_seek($classes, 0);
                                                                    while ($class = $classes->fetch_assoc()): ?><option value="<?= $class['id'] ?>" <?= ($class_id == $class['id']) ? 'selected' : '' ?>><?= $class['name'] ?></option><?php endwhile; ?>
                        </select></div>
                    <?php if ($class_id): ?>
                        <div class="form-group"><label>Pilih Mata Pelajaran:</label><select onchange="window.location.href='?class_id=<?= $class_id ?>&subject_id='+this.value">
                                <option value="">-- Pilih --</option><?php if ($subjects) {
                                                                            mysqli_data_seek($subjects, 0);
                                                                            while ($subject = $subjects->fetch_assoc()): ?><option value="<?= $subject['id'] ?>" <?= ($subject_id == $subject['id']) ? 'selected' : '' ?>><?= $subject['name'] ?></option><?php endwhile;
                                                                                                                                                                                                                                                                                                    } ?>
                            </select></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($class_id && $subject_id): ?>
                <div class="card" style="animation-delay: 0.1s;">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama Siswa</th><?php foreach ($dates as $date): ?><th><?= date('d M Y', strtotime($date)) ?></th><?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($student['name']) ?></td>
                                        <?php foreach ($dates as $date): ?>
                                            <td>
                                                <?php if (isset($attendance_data[$student['id']][$date])): $att = $attendance_data[$student['id']][$date]; ?>
                                                    <form method="POST"><input type="hidden" name="attendance_id" value="<?= $att['id'] ?>"><select name="status">
                                                            <option value="hadir" <?= $att['status'] == 'hadir' ? 'selected' : '' ?>>Hadir</option>
                                                            <option value="sakit" <?= $att['status'] == 'sakit' ? 'selected' : '' ?>>Sakit</option>
                                                            <option value="izin" <?= $att['status'] == 'izin' ? 'selected' : '' ?>>Izin</option>
                                                            <option value="alfa" <?= $att['status'] == 'alfa' ? 'selected' : '' ?>>Alfa</option>
                                                        </select><button type="submit">Ubah</button></form>
                                                <?php else: ?> - <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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