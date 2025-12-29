<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}
include 'config.php';
$username_session = $_SESSION['username'];
$selected_teacher_id = null;
$teacher_schedule_list = [];

// Fetch data untuk dropdowns
$teachers_result = $conn->query("SELECT id, username FROM users WHERE role = 'guru' ORDER BY username ASC");
$classes_result = $conn->query("SELECT id, name FROM classes ORDER BY name ASC");
$subjects_result = $conn->query("SELECT id, name FROM subjects ORDER BY name ASC");

$teachers_list = $teachers_result->fetch_all(MYSQLI_ASSOC);
$classes_list = $classes_result->fetch_all(MYSQLI_ASSOC);
$subjects_list = $subjects_result->fetch_all(MYSQLI_ASSOC);

// Inisialisasi jadwal sementara dari session
$temp_schedule = isset($_SESSION['temp_schedule']) ? $_SESSION['temp_schedule'] : [];

// --- Logika Form Handling ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // PERBAIKAN: Kondisi untuk melihat jadwal guru dipindahkan ke atas dan diubah
    // Sekarang memeriksa data dropdown, bukan tombol.
    if (isset($_POST['teacher_id_view'])) {
        if (!empty($_POST['teacher_id_view'])) {
            $selected_teacher_id = $_POST['teacher_id_view'];
            $stmt = $conn->prepare("SELECT ts.id, c.name as class_name, s.name as subject_name, ts.start_time, ts.end_time FROM teacher_schedule ts JOIN classes c ON ts.class_id = c.id JOIN subjects s ON ts.subject_id = s.id WHERE ts.teacher_id = ? ORDER BY ts.start_time");
            $stmt->bind_param("i", $selected_teacher_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $teacher_schedule_list = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
    }
    // Menambah jadwal ke daftar sementara
    elseif (isset($_POST['add_to_schedule'])) {
        if (!empty($_POST['teacher_id']) && !empty($_POST['class_id']) && !empty($_POST['subject_id']) && !empty($_POST['start_time']) && !empty($_POST['end_time'])) {
            $temp_schedule[] = [
                'teacher_id' => $_POST['teacher_id'],
                'class_id' => $_POST['class_id'],
                'subject_id' => $_POST['subject_id'],
                'start_time' => $_POST['start_time'],
                'end_time' => $_POST['end_time']
            ];
            $_SESSION['temp_schedule'] = $temp_schedule;
        } else {
            echo "<script>alert('Harap isi semua bidang!');</script>";
        }
    }
    // Menghapus jadwal dari daftar sementara
    elseif (isset($_POST['remove_schedule'])) {
        $schedule_index = $_POST['schedule_index'];
        unset($temp_schedule[$schedule_index]);
        $_SESSION['temp_schedule'] = array_values($temp_schedule); // Re-index array
    }
    // Menyimpan semua jadwal sementara ke database
    elseif (isset($_POST['save_all_schedule'])) {
        if (!empty($temp_schedule)) {
            $stmt = $conn->prepare("INSERT INTO teacher_schedule (teacher_id, subject_id, class_id, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
            foreach ($temp_schedule as $schedule) {
                $stmt->bind_param("iisss", $schedule['teacher_id'], $schedule['subject_id'], $schedule['class_id'], $schedule['start_time'], $schedule['end_time']);
                $stmt->execute();
            }
            $stmt->close();
            unset($_SESSION['temp_schedule']);
            echo "<script>alert('Semua jadwal berhasil disimpan!'); window.location.href='manage_teachers.php';</script>";
            exit();
        }
    }
    // Menghapus jadwal yang dipilih dari database
    elseif (isset($_POST['delete_schedules'])) {
        if (!empty($_POST['selected_schedules'])) {
            $stmt = $conn->prepare("DELETE FROM teacher_schedule WHERE id = ?");
            foreach ($_POST['selected_schedules'] as $schedule_id) {
                $stmt->bind_param("i", $schedule_id);
                $stmt->execute();
            }
            $stmt->close();
            echo "<script>alert('Jadwal yang dipilih berhasil dihapus!'); window.location.href='manage_teachers.php';</script>";
            exit();
        } else {
            echo "<script>alert('Tidak ada jadwal yang dipilih untuk dihapus.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jadwal Guru - SIMANDAKA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
        }

        body.light-theme {
            --main-bg: #f1f5f9;
            --panel-bg: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--main-bg);
            color: var(--text-primary);
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

        .sidebar-menu a:hover {
            background-color: var(--sidebar-link-hover-bg);
            color: #fff;
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

        .sidebar-menu ul {
            list-style: none;
            padding-left: 15px;
            margin: 0;
            display: none;
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

        .top-nav .page-title h1 {
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
            cursor: pointer;
        }

        .theme-switcher label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            cursor: pointer;
        }

        .theme-switcher:hover {
            background-color: var(--sidebar-link-hover-bg);
        }

        .main-content {
            padding: 30px;
        }

        .content-panel {
            background-color: var(--panel-bg);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .content-panel h2,
        .content-panel h3 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.25em;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }

        .content-panel h3 {
            border-bottom: none;
        }

        input,
        select,
        button {
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            background-color: var(--main-bg);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            font-size: 0.9em;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #4f46e5;
        }

        button {
            background-color: #4f46e5;
            color: white;
            cursor: pointer;
            border: none;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        button:hover {
            background-color: #4338ca;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        label {
            font-weight: 500;
            color: var(--text-secondary);
            font-size: 0.9em;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            color: var(--text-primary);
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        th {
            background-color: #2a374a;
            font-weight: 600;
        }

        tr:last-of-type td {
            border-bottom: none;
        }

        .btn-danger {
            background-color: #dc3545;
            font-size: 0.85em;
            padding: 8px 12px;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-success {
            background-color: #28a745;
            margin-top: 15px;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .checkbox-cell {
            width: 5%;
            text-align: center;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #4f46e5;
        }
    </style>
</head>

<body class="dark-theme">
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="images/Logo SMK.png" class="logo" alt="Logo Sekolah">
                <div class="sidebar-title">
                    <h2>SIMANDAKA</h2>
                    <p>SMK Negeri 2 Bengkalis</p>
                </div>
            </div>
            <nav class="sidebar-menu">
                <h3>Navigation</h3>
                <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <h3>Management</h3>
                <a href="manage_users.php"><i class="fas fa-users-cog"></i> Kelola Pengguna</a>
                <a href="manage_classes.php"><i class="fas fa-school"></i> Kelola Kelas</a>
                <a href="manage_subjects.php"><i class="fas fa-book"></i> Kelola Mapel</a>
                <a href="manage_students.php"><i class="fas fa-user-graduate"></i> Kelola Siswa</a>
                <a href="manage_teachers.php" class="active"><i class="fas fa-chalkboard-teacher"></i> Kelola Guru</a>
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
                <div class="page-title">
                    <h1>Kelola Jadwal Guru</h1>
                </div>
                <div class="profile">
                    <div class="profile-info">
                        <div class="user-name"><?php echo htmlspecialchars($username_session); ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                    <div class="profile-dropdown-container">
                        <div class="profile-dropdown-toggle" onclick="toggleProfileDropdown()"><img src="images/PP.jpg" alt="Admin"></div>
                        <div class="profile-dropdown" id="profileDropdown">
                            <div class="theme-switcher">
                                <label for="theme-toggle"><span><i class="fas fa-palette"></i> Ganti Tema</span><input type="checkbox" id="theme-toggle" style="display:none;"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <main class="main-content">

                <div class="content-panel">
                    <h2>Tambah Jadwal Mengajar</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label for="teacher_id">Pilih Guru:</label>
                            <select name="teacher_id" required>
                                <option value="" disabled selected>-- Pilih Guru --</option>
                                <?php foreach ($teachers_list as $teacher) : ?>
                                    <option value="<?= $teacher['id'] ?>"><?= htmlspecialchars($teacher['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-grid">
                            <div class="form-group"><label>Pilih Kelas:</label><select name="class_id" required>
                                    <option value="" disabled selected>-- Pilih Kelas --</option><?php foreach ($classes_list as $class) : ?><option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option><?php endforeach; ?>
                                </select></div>
                            <div class="form-group"><label>Pilih Mata Pelajaran:</label><select name="subject_id" required>
                                    <option value="" disabled selected>-- Pilih Mapel --</option><?php foreach ($subjects_list as $subject) : ?><option value="<?= $subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?></option><?php endforeach; ?>
                                </select></div>
                            <div class="form-group"><label>Jam Mulai:</label><input type="time" name="start_time" required></div>
                            <div class="form-group"><label>Jam Selesai:</label><input type="time" name="end_time" required></div>
                        </div>
                        <br>
                        <button type="submit" name="add_to_schedule">Tambah ke Daftar</button>
                    </form>
                </div>

                <?php if (!empty($temp_schedule)) : ?>
                    <div class="content-panel">
                        <h3>Jadwal Sementara (Belum Disimpan)</h3>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Guru</th>
                                        <th>Kelas</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Jam</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($temp_schedule as $key => $schedule) : ?>
                                        <tr>
                                            <td><?php foreach ($teachers_list as $t) {
                                                    if ($t['id'] == $schedule['teacher_id']) echo htmlspecialchars($t['username']);
                                                } ?></td>
                                            <td><?php foreach ($classes_list as $c) {
                                                    if ($c['id'] == $schedule['class_id']) echo htmlspecialchars($c['name']);
                                                } ?></td>
                                            <td><?php foreach ($subjects_list as $s) {
                                                    if ($s['id'] == $schedule['subject_id']) echo htmlspecialchars($s['name']);
                                                } ?></td>
                                            <td><?= htmlspecialchars($schedule['start_time']) ?> - <?= htmlspecialchars($schedule['end_time']) ?></td>
                                            <td>
                                                <form method="POST"><input type="hidden" name="schedule_index" value="<?= $key ?>"><button type="submit" name="remove_schedule" class="btn-danger">Hapus</button></form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <form method="POST">
                            <button type="submit" name="save_all_schedule" class="btn-success">Simpan Semua Jadwal Sementara</button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="content-panel">
                    <h2>Lihat & Hapus Jadwal Tersimpan</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label for="teacher_id_view">Pilih Guru untuk Melihat Jadwal:</label>
                            <select name="teacher_id_view" required onchange="this.form.submit()">
                                <option value="">-- Pilih Guru --</option>
                                <?php foreach ($teachers_list as $teacher) : ?>
                                    <option value="<?= $teacher['id'] ?>" <?= ($selected_teacher_id == $teacher['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($teacher['username']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>

                    <?php if ($selected_teacher_id) : ?>
                        <hr style="border-color: var(--border-color); margin: 20px 0;">
                        <h3>Jadwal untuk: <strong><?php foreach ($teachers_list as $t) {
                                                        if ($t['id'] == $selected_teacher_id) echo htmlspecialchars($t['username']);
                                                    } ?></strong></h3>

                        <?php if (!empty($teacher_schedule_list)): ?>
                            <form method="POST">
                                <div class="table-wrapper">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th class="checkbox-cell"><input type="checkbox" id="select_all_schedules"></th>
                                                <th>Kelas</th>
                                                <th>Mata Pelajaran</th>
                                                <th>Jam</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($teacher_schedule_list as $schedule) : ?>
                                                <tr>
                                                    <td class="checkbox-cell"><input type="checkbox" class="schedule-checkbox" name="selected_schedules[]" value="<?= $schedule['id'] ?>"></td>
                                                    <td><?= htmlspecialchars($schedule['class_name']) ?></td>
                                                    <td><?= htmlspecialchars($schedule['subject_name']) ?></td>
                                                    <td><?= htmlspecialchars(date('H:i', strtotime($schedule['start_time']))) ?> - <?= htmlspecialchars(date('H:i', strtotime($schedule['end_time']))) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <br>
                                <button type="submit" name="delete_schedules" class="btn-danger">Hapus Jadwal yang Dipilih</button>
                            </form>
                        <?php else: ?>
                            <p style="text-align:center; margin-top:20px; color: var(--text-secondary);">Tidak ada jadwal yang tersimpan untuk guru ini.</p>
                        <?php endif; ?>
                    <?php endif; ?>
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
            var menu = document.getElementById('settingMenu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }
        window.onclick = function(event) {
            if (!event.target.closest('.profile-dropdown-container')) {
                document.getElementById('profileDropdown').style.display = 'none';
            }
        }
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;
        const currentTheme = localStorage.getItem('theme') || 'dark-theme';
        body.classList.add(currentTheme);
        if (currentTheme === 'light-theme') {
            themeToggle.checked = true;
        }
        themeToggle.addEventListener('change', function() {
            body.classList.toggle('light-theme', this.checked);
            body.classList.toggle('dark-theme', !this.checked);
            localStorage.setItem('theme', this.checked ? 'light-theme' : 'dark-theme');
        });

        const selectAllSchedules = document.getElementById('select_all_schedules');
        if (selectAllSchedules) {
            selectAllSchedules.addEventListener('change', function(e) {
                document.querySelectorAll('.schedule-checkbox').forEach(function(checkbox) {
                    checkbox.checked = e.target.checked;
                });
            });
        }
    </script>
</body>

</html>