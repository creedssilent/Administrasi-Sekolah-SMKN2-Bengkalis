<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}
include 'config.php';
$username_session = $_SESSION['username'];

// --- Logika PHP (ditingkatkan keamanannya) ---

// 1. Fetch daftar kelas untuk dropdown
$classes_result = $conn->query("SELECT id, name FROM classes ORDER BY name ASC");
$classes_list = [];
while ($row = $classes_result->fetch_assoc()) {
    $classes_list[] = $row;
}

// 2. Ambil total siswa keseluruhan
$total_students_query = $conn->query("SELECT COUNT(*) AS total FROM students");
$total_students = $total_students_query->fetch_assoc()['total'];

// 3. Fetch daftar siswa berdasarkan filter kelas
$students = [];
$total_students_in_class = 0;
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

if ($class_id > 0) {
    // Jika kelas dipilih, filter siswa
    $stmt = $conn->prepare("SELECT s.id, s.name, c.name AS class_name FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.class_id = ? ORDER BY s.name ASC");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $students_result = $stmt->get_result();
    while ($row = $students_result->fetch_assoc()) {
        $students[] = $row;
    }
    $total_students_in_class = count($students);
    $stmt->close();
} else {
    // Jika tidak ada kelas dipilih, tampilkan semua siswa
    $students_result = $conn->query("SELECT s.id, s.name, c.name AS class_name FROM students s LEFT JOIN classes c ON s.class_id = c.id ORDER BY s.name ASC");
    while ($row = $students_result->fetch_assoc()) {
        $students[] = $row;
    }
    $total_students_in_class = $total_students;
}

// 4. Update kelas siswa secara massal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_class'])) {
    $target_class_id = $_POST['target_class_id'];
    if (isset($_POST['selected_students']) && !empty($_POST['selected_students']) && !empty($target_class_id)) {
        $selected_students = $_POST['selected_students'];

        // Membuat placeholder '?' sebanyak siswa yang dipilih
        $placeholders = implode(',', array_fill(0, count($selected_students), '?'));
        // Menyiapkan tipe data untuk bind_param (i untuk class_id, dan i untuk setiap student_id)
        $types = 'i' . str_repeat('i', count($selected_students));

        $stmt = $conn->prepare("UPDATE students SET class_id = ? WHERE id IN ($placeholders)");
        // Menggabungkan class_id dengan array student_id untuk di-bind
        $params = array_merge([$target_class_id], $selected_students);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            echo "<script>alert('Kelas siswa berhasil diperbarui!');</script>";
        } else {
            echo "<script>alert('Gagal memperbarui kelas siswa: " . $conn->error . "');</script>";
        }
        // Refresh halaman untuk menampilkan data terbaru
        echo "<script>window.location.href='manage_students.php" . ($class_id > 0 ? "?class_id=" . $class_id : "") . "';</script>";
    } else {
        echo "<script>alert('Tidak ada siswa atau kelas tujuan yang dipilih.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Siswa - SIMANDAKA</title>
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

        .stats-info {
            background-color: var(--panel-bg);
            padding: 15px 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stats-info span {
            font-weight: 500;
            color: var(--text-secondary);
        }

        .stats-info strong {
            color: var(--text-primary);
        }

        .actions-bar {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .actions-bar select {
            flex-grow: 1;
        }

        .actions-bar button {
            width: auto;
            padding: 12px 20px;
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
                <a href="manage_students.php" class="active"><i class="fas fa-user-graduate"></i> Kelola Siswa</a>
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
                <div class="page-title">
                    <h1>Kelola Siswa</h1>
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
                <div class="stats-info">
                    <span>Total Siswa Keseluruhan: <strong><?= $total_students ?></strong></span>
                    <span>Menampilkan: <strong><?= count($students) ?></strong> siswa</span>
                </div>

                <div class="content-panel">
                    <h2>Filter dan Atur Kelas Siswa</h2>
                    <form method="GET" action="manage_students.php">
                        <label for="class_id">Tampilkan Siswa dari Kelas:</label>
                        <select name="class_id" id="class_id" onchange="this.form.submit()">
                            <option value="0">Tampilkan Semua Kelas</option>
                            <?php foreach ($classes_list as $class) : ?>
                                <option value="<?= $class['id'] ?>" <?= ($class_id == $class['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($class['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <div class="content-panel">
                    <form method="POST" action="manage_students.php<?= ($class_id > 0) ? '?class_id=' . $class_id : '' ?>">
                        <h3>Daftar Siswa</h3>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width: 5%;"><input type="checkbox" id="select_all"></th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas Saat Ini</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($students)) : ?>
                                        <?php foreach ($students as $student) : ?>
                                            <tr>
                                                <td><input type="checkbox" name="selected_students[]" value="<?= $student['id'] ?>" class="student-checkbox"></td>
                                                <td><?= htmlspecialchars($student['name']) ?></td>
                                                <td><?= htmlspecialchars($student['class_name'] ?? 'Belum Ditentukan') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="3" style="text-align:center;">Tidak ada siswa di kelas ini.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="actions-bar">
                            <select name="target_class_id" required>
                                <option value="" disabled selected>Pindahkan ke Kelas...</option>
                                <?php foreach ($classes_list as $class) : ?>
                                    <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="assign_class">Tetapkan Kelas</button>
                        </div>
                    </form>
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

        // Fungsi "Pilih Semua" checkbox
        document.getElementById('select_all').addEventListener('change', function(e) {
            document.querySelectorAll('.student-checkbox').forEach(function(checkbox) {
                checkbox.checked = e.target.checked;
            });
        });
    </script>
</body>

</html>