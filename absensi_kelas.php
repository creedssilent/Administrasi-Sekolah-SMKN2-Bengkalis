<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header('Location: index.php');
    exit();
}
include 'config.php';
$teacher_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'];
if (!$teacher_id) {
    echo "<script>alert('Kesalahan sesi. Silakan login kembali.'); window.location.href='index.php';</script>";
    exit();
}
$update_auto_query = "UPDATE attendance_open SET is_closed = TRUE WHERE CONCAT(date, ' ', end_time) < NOW() AND is_closed = FALSE";
$conn->query($update_auto_query);

function getSubjectsForTeacher($conn, $class_id, $teacher_id)
{
    $sql = "SELECT DISTINCT s.id, s.name FROM subjects s JOIN teacher_schedule ts ON s.id = ts.subject_id WHERE ts.class_id = ? AND ts.teacher_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $class_id, $teacher_id);
    $stmt->execute();
    return $stmt->get_result();
}
function getOpenedAttendance($conn, $class_id)
{
    $sql = "SELECT a.id, s.name AS subject_name, a.start_time, a.end_time FROM attendance_open a JOIN subjects s ON a.subject_id = s.id WHERE a.class_id = ? AND a.is_closed = FALSE";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    return $stmt->get_result();
}
function openAttendance($conn, $class_id, $subject_id, $start_time, $end_time, $date)
{
    $sql_check = "SELECT id FROM attendance_open WHERE class_id = ? AND subject_id = ? AND date = ? AND is_closed = FALSE";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("iis", $class_id, $subject_id, $date);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        return "Absensi untuk mata pelajaran ini sudah dibuka hari ini.";
    }
    $insert_sql = "INSERT INTO attendance_open (class_id, subject_id, date, start_time, end_time) VALUES (?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iisss", $class_id, $subject_id, $date, $start_time, $end_time);
    return $insert_stmt->execute() ? true : "Gagal membuka absensi: " . $conn->error;
}
function closeAttendance($conn, $attendance_id)
{
    $sql = "UPDATE attendance_open SET is_closed = TRUE WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $attendance_id);
    return $stmt->execute() ? true : "Gagal menutup absensi: " . $conn->error;
}

$classes_query = $conn->prepare("SELECT DISTINCT c.id, c.name FROM classes c JOIN teacher_schedule ts ON c.id = ts.class_id WHERE ts.teacher_id = ? ORDER BY c.name ASC");
$classes_query->bind_param("i", $teacher_id);
$classes_query->execute();
$classes = $classes_query->get_result();
$selected_class_id = $_GET['class_id'] ?? null;
$subjects = [];
$opened_attendance = [];
if ($selected_class_id) {
    $subjects = getSubjectsForTeacher($conn, $selected_class_id, $teacher_id);
    $opened_attendance = getOpenedAttendance($conn, $selected_class_id);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['open_attendance'])) {
        $class_id_post = $_POST['class_id'];
        $result = openAttendance($conn, $class_id_post, $_POST['subject_id'], $_POST['start_time'], $_POST['end_time'], date('Y-m-d'));
        $message = ($result === true) ? "Absensi berhasil dibuka!" : $result;
        echo "<script>alert('$message'); window.location.href='absensi_kelas.php?class_id=$class_id_post';</script>";
        exit();
    }
    if (isset($_POST['close_attendance'])) {
        $class_id_post = $_POST['class_id'];
        $result = closeAttendance($conn, $_POST['attendance_id']);
        $message = ($result === true) ? "Absensi telah ditutup!" : $result;
        echo "<script>alert('$message'); window.location.href='absensi_kelas.php?class_id=$class_id_post';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buka Absensi Kelas</title>
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
            --danger-color: #e53e3e;
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

        .dashboard-title {
            font-size: 2em;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 20px;
            animation: slideInUp 0.5s ease-out forwards;
            opacity: 0;
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

        .card h3 {
            font-size: 1.2em;
            font-weight: 600;
            margin: 0 0 20px 0;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        input[type="time"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #cbd5e0;
            background-color: #f8fafc;
            font-family: 'Poppins';
            font-size: 1em;
        }

        button {
            background: var(--accent-color);
            color: white;
            cursor: pointer;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            width: 100%;
            font-size: 1em;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        button:hover {
            background: var(--accent-hover);
        }

        .attendance-list ul {
            list-style: none;
            padding: 0;
        }

        .attendance-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f8fafc;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .close-btn {
            background-color: var(--danger-color);
            padding: 6px 12px;
            font-size: 0.9em;
            width: auto;
        }

        .close-btn:hover {
            background-color: #c53030;
        }

        <?php for ($i = 1; $i <= 3; $i++): ?>.card:nth-child(<?php echo $i; ?>) {
            animation-delay: <?php echo $i * 0.1; ?>s;
        }

        <?php endfor; ?>
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
                <a href="absensi_kelas.php" class="active"><i class="fas fa-clipboard-check"></i> Buka Absensi</a>
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
            <h2 class="dashboard-title">Buka Sesi Absensi</h2>
            <div class="card">
                <h3>Pilih Kelas</h3>
                <form method="GET" action=""><select name="class_id" required onchange="this.form.submit()">
                        <option value="">-- Pilih Kelas yang Anda Ajar --</option><?php mysqli_data_seek($classes, 0);
                                                                                    while ($class = $classes->fetch_assoc()) : ?><option value="<?= $class['id'] ?>" <?= ($selected_class_id == $class['id']) ? 'selected' : '' ?>><?= $class['name'] ?></option><?php endwhile; ?>
                    </select></form>
            </div>
            <?php if ($selected_class_id) : ?>
                <div class="card attendance-list">
                    <h3>Daftar Sesi Terbuka</h3>
                    <ul>
                        <?php if ($opened_attendance && $opened_attendance->num_rows > 0) : ?>
                            <?php while ($attendance = $opened_attendance->fetch_assoc()) : ?>
                                <li><span><?= htmlspecialchars($attendance['subject_name']) ?> (<?= date('H:i', strtotime($attendance['start_time'])) ?> - <?= date('H:i', strtotime($attendance['end_time'])) ?>)</span>
                                    <form method="POST" style="width: auto;"><input type="hidden" name="attendance_id" value="<?= $attendance['id'] ?>"><input type="hidden" name="class_id" value="<?= $selected_class_id ?>"><button type="submit" name="close_attendance" class="close-btn">Tutup</button></form>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?><li>Tidak ada absensi yang dibuka untuk kelas ini.</li><?php endif; ?>
                    </ul>
                </div>
                <div class="card">
                    <h3>Buka Sesi Baru</h3>
                    <form method="POST">
                        <input type="hidden" name="class_id" value="<?= $selected_class_id ?>">
                        <label>Mata Pelajaran:</label>
                        <select name="subject_id" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            <?php if ($subjects && $subjects->num_rows > 0) : mysqli_data_seek($subjects, 0);
                                while ($subject = $subjects->fetch_assoc()) : ?><option value="<?= $subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?></option><?php endwhile;
                                                                                                                                                                                                                                                else: ?><option value="" disabled>Tidak ada mapel yang anda ajar.</option><?php endif; ?>
                        </select>
                        <label>Jam Mulai:</label><input type="time" name="start_time" required>
                        <label>Jam Selesai:</label><input type="time" name="end_time" required>
                        <button type="submit" name="open_attendance">Buka Absensi</button>
                    </form>
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