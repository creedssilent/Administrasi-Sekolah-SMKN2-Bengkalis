<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

include 'config.php';
$username_session = $_SESSION['username'];
$error = '';
$success = '';

// Logika untuk Menambah Mata Pelajaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    if (!empty($_POST['subject_name']) && !empty($_POST['kategori'])) {
        $subject_name = $_POST['subject_name'];
        $kategori = $_POST['kategori'];

        $stmt = $conn->prepare("INSERT INTO subjects (name, kategori) VALUES (?, ?)");
        $stmt->bind_param("ss", $subject_name, $kategori);

        if ($stmt->execute()) {
            $success = "Mata pelajaran berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan mata pelajaran: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Nama mata pelajaran dan kategori tidak boleh kosong!";
    }
}

// Logika untuk Menghapus Mata Pelajaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_subject'])) {
    if (!empty($_POST['subject_id'])) {
        $subject_id = $_POST['subject_id'];

        $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
        $stmt->bind_param("i", $subject_id);

        try {
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $success = "Mata pelajaran berhasil dihapus.";
                } else {
                    $error = "Mata pelajaran tidak ditemukan atau sudah dihapus.";
                }
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1451) {
                $error = "Gagal Hapus: Mapel ini masih terdaftar di data lain (Jadwal Guru, Nilai Siswa, dll).";
            } else {
                $error = "Terjadi error pada database: " . $e->getMessage();
            }
        }
        $stmt->close();
    }
}

// Mengambil data mapel (kolom created_at tidak diambil)
$subjects = [];
$sql = "SELECT id, name, kategori FROM subjects ORDER BY id ASC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Mata Pelajaran - SIMANDAKA</title>
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

        .content-panel h2 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.25em;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }

        .content-panel h3 {
            margin-top: 0;
            margin-bottom: 20px;
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

        .add-form {
            display: grid;
            grid-template-columns: 2fr 1fr auto;
            gap: 10px;
            align-items: center;
        }

        .add-form button {
            width: auto;
            padding-left: 25px;
            padding-right: 25px;
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

        .alert-message {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-error {
            color: #f87171;
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.5);
        }

        .alert-success {
            color: #34d399;
            background-color: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.5);
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
                <a href="manage_subjects.php" class="active"><i class="fas fa-book"></i> Kelola Mapel</a>
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
                <div class="page-title">
                    <h1>Kelola Mata Pelajaran</h1>
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
                <?php if (!empty($error)) : ?>
                    <div class="alert-message alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if (!empty($success)) : ?>
                    <div class="alert-message alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <div class="content-panel">
                    <h2>Tambah Mata Pelajaran Baru</h2>
                    <form method="POST" action="manage_subjects.php" class="add-form">
                        <input type="text" name="subject_name" placeholder="Contoh: Matematika" required>
                        <select name="kategori" required>
                            <option value="" disabled selected>Pilih Kategori</option>
                            <option value="A">Kelompok Umum (A)</option>
                            <option value="B">Kelompok Kejuruan (B)</option>
                        </select>
                        <button type="submit" name="add_subject">Tambah</button>
                    </form>
                </div>

                <div class="content-panel">
                    <h3>Daftar Mata Pelajaran</h3>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Mapel</th>
                                    <th>Kategori</th>
                                    <th style="width: 10%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($subjects)) : ?>
                                    <?php foreach ($subjects as $subject) : ?>
                                        <tr>
                                            <td><?= $subject['id'] ?></td>
                                            <td><?= htmlspecialchars($subject['name']) ?></td>
                                            <td>
                                                <?php
                                                if ($subject['kategori'] == 'A') echo 'Umum';
                                                elseif ($subject['kategori'] == 'B') echo 'Kejuruan';
                                                else echo '-';
                                                ?>
                                            </td>
                                            <td>
                                                <form method="POST" action="manage_subjects.php" onsubmit="return confirm('Anda yakin ingin menghapus mata pelajaran ini?');">
                                                    <input type="hidden" name="subject_id" value="<?= $subject['id'] ?>">
                                                    <button type="submit" name="delete_subject" class="btn-danger">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center;">Belum ada data mata pelajaran.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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
    </script>
</body>

</html>