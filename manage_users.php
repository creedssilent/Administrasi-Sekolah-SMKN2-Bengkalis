<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}
include 'config.php';
if (!function_exists('encryptPassword')) {
    include 'encryption.php';
}

$username_session = $_SESSION['username'];

// Proses Form Handling dari file lama Anda
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        if (isset($_POST['username'], $_POST['password'], $_POST['role'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $role = $_POST['role'];
            $name = $_POST['name'] ?? '';
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $encrypted_password = encryptPassword($password, $encryption_key);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role, status, encrypted_password) VALUES (?, ?, ?, 'active', ?)");
            $stmt->bind_param("ssss", $username, $hashed_password, $role, $encrypted_password);
            if ($stmt->execute()) {
                if ($role === 'siswa' && !empty($name)) {
                    $stmt_student = $conn->prepare("INSERT INTO students (name, username, class_id) VALUES (?, ?, 1)");
                    $stmt_student->bind_param("ss", $name, $username);
                    $stmt_student->execute();
                }
                echo "<script>alert('Akun berhasil ditambahkan'); window.location.href='manage_users.php';</script>";
            } else {
                echo "<script>alert('Gagal menambahkan akun. Mungkin username sudah ada.'); window.location.href='manage_users.php';</script>";
            }
        } else {
            echo "<script>alert('Input tidak lengkap');</script>";
        }
    }
    if (isset($_POST['update_status'])) {
        if (isset($_POST['id'], $_POST['status'])) {
            $id = $_POST['id'];
            $status = $_POST['status'];
            $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $id);
            if ($stmt->execute()) {
                echo "<script>alert('Status akun berhasil diperbarui'); window.location.href='manage_users.php';</script>";
            } else {
                echo "<script>alert('Gagal memperbarui status.'); window.location.href='manage_users.php';</script>";
            }
        } else {
            echo "<script>alert('Input tidak lengkap untuk memperbarui status');</script>";
        }
    }
    if (isset($_POST['import_excel'])) {
        if (file_exists('vendor/autoload.php')) {
            require 'vendor/autoload.php';
            $file = $_FILES['excel_file']['tmp_name'];
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($file);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            $conn->begin_transaction();
            try {
                for ($row = 2; $row <= $highestRow; $row++) {
                    $username = $worksheet->getCell([1, $row])->getValue();
                    $password = (string) $worksheet->getCell([2, $row])->getValue();
                    $role = strtolower($worksheet->getCell([3, $row])->getValue());
                    $name = $worksheet->getCell([4, $row])->getValue();
                    if (empty($username) || empty($password) || empty($role)) continue;
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $encrypted_password = encryptPassword($password, $encryption_key);
                    $stmt = $conn->prepare("INSERT INTO users (username, password, role, status, encrypted_password) VALUES (?, ?, ?, 'active', ?)");
                    $stmt->bind_param("ssss", $username, $hashed_password, $role, $encrypted_password);
                    $stmt->execute();
                    if ($role === 'siswa' && !empty($name)) {
                        $stmt_student = $conn->prepare("INSERT INTO students (name, username, class_id) VALUES (?, ?, 1)");
                        $stmt_student->bind_param("ss", $name, $username);
                        $stmt_student->execute();
                    }
                }
                $conn->commit();
                echo "<script>alert('Import berhasil!'); window.location.href='manage_users.php';</script>";
            } catch (Exception $e) {
                $conn->rollback();
                echo "<script>alert('Import gagal: " . $e->getMessage() . "'); window.location.href='manage_users.php';</script>";
            }
        } else {
            echo "<script>alert('Gagal import: Library PhpSpreadsheet tidak ditemukan.'); window.location.href='manage_users.php';</script>";
        }
    }
}
function maskPassword($password)
{
    return '********';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - SIMANDAKA</title>
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

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: center;
        }

        .form-grid .full-width {
            grid-column: 1 / -1;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 5px;
            font-size: 0.9em;
            color: var(--text-secondary);
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

        input[type="file"] {
            padding: 8px;
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

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-secondary);
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

        td .actions-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        td .actions-container form {
            display: flex;
            gap: 5px;
            align-items: center;
            margin: 0;
        }

        td button,
        td select {
            font-size: 0.85em;
            padding: 8px;
            margin: 0;
        }

        .btn-info {
            background-color: #17a2b8;
        }

        .btn-info:hover {
            background-color: #138496;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-lihat {
            font-size: 0.85em !important;
            padding: 8px !important;
            margin-left: 5px;
        }

        /* --- AWAL DARI KODE CSS TOMBOL SCROLL --- */
        #scrollToTopBtn,
        #scrollToBottomBtn {
            position: fixed;
            bottom: 20px;
            width: 50px;
            height: 50px;
            font-size: 20px;
            border-radius: 50%;
            z-index: 1000;
            transition: background-color 0.3s, opacity 0.3s;
        }

        #scrollToBottomBtn {
            right: 20px;
        }

        #scrollToTopBtn {
            right: 85px;
            display: none;
            /* Tombol atas disembunyikan secara default */
        }

        /* --- AKHIR DARI KODE CSS TOMBOL SCROLL --- */
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
                <a href="manage_users.php" class="active"><i class="fas fa-users-cog"></i> Kelola Pengguna</a>
                <a href="manage_classes.php"><i class="fas fa-school"></i> Kelola Kelas</a>
                <a href="manage_subjects.php"><i class="fas fa-book"></i> Kelola Mapel</a>
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
                    <h1>Kelola Pengguna</h1>
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
                    <h2>Tambah Akun Manual</h2>
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group"><label for="add-username">Username</label><input type="text" id="add-username" name="username" required></div>
                            <div class="form-group"><label for="add-password">Password</label>
                                <div class="password-container"><input type="password" name="password" required id="add-password"><span class="password-toggle" onclick="togglePasswordVisibility('add-password')"><i class="fas fa-eye"></i></span></div>
                            </div>
                            <div class="form-group"><label for="add-role">Role</label><select id="add-role" name="role" required>
                                    <option value="">Pilih Role</option>
                                    <option value="guru">Guru</option>
                                    <option value="siswa">Siswa</option>
                                    <option value="administrasi">Administrasi</option>
                                    <option value="kepala_sekolah">Kepala Sekolah</option>
                                </select></div>
                            <div class="form-group"><label for="add-name">Nama Lengkap (Siswa)</label><input type="text" id="add-name" name="name"></div>
                            <div class="form-group full-width"><button type="submit" name="add_user">Tambah Akun</button></div>
                        </div>
                    </form>
                </div>
                <?php if (file_exists('vendor/autoload.php')): ?>
                    <div class="content-panel">
                        <h2>Import Akun dari Excel</h2>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-grid">
                                <div class="form-group" style="grid-column: 1 / 4;"><label for="excel_file">File (.xls, .xlsx)</label><input type="file" id="excel_file" name="excel_file" accept=".xls,.xlsx" required></div>
                                <div class="form-group" style="grid-column: 4 / 5;"><label>&nbsp;</label><button type="submit" name="import_excel">Import</button></div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                <div class="content-panel">
                    <h3>Daftar Akun</h3>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Password</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th style="width: 40%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT id, username, password, role, status FROM users ORDER BY id ASC";
                                $result = $conn->query($sql);
                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>" . htmlspecialchars($row['username']) . "</td>
                                <td id='password-cell-{$row['id']}'>" . maskPassword($row['password']) . " <button class='btn-lihat' onclick='confirmAdminPassword({$row['id']})' title='Lihat Password Asli'>Lihat</button></td>
                                <td>" . htmlspecialchars($row['role']) . "</td>
                                <td>" . htmlspecialchars($row['status']) . "</td>
                                <td><div class='actions-container'>
                                    <form action='reset_password.php' method='POST'>
                                        <input type='hidden' name='id' value='{$row['id']}'>
                                        <div class='password-container' style='width:100%'>
                                            <input type='password' name='new_password' placeholder='Password Baru' required id='reset-{$row['id']}'>
                                            <span class='password-toggle' onclick=\"togglePasswordVisibility('reset-{$row['id']}')\"><i class='fas fa-eye'></i></span>
                                        </div>
                                        <button type='submit' name='reset_password' class='btn-info'>Reset</button>
                                    </form>
                                    <form action='manage_users.php' method='POST'>
                                        <input type='hidden' name='id' value='{$row['id']}'>
                                        <select name='status' style='width:100%'><option value='active' " . ($row['status'] === 'active' ? 'selected' : '') . ">Aktif</option><option value='inactive' " . ($row['status'] === 'inactive' ? 'selected' : '') . ">Nonaktif</option></select>
                                        <button type='submit' name='update_status'>Update</button>
                                    </form>
                                    <form action='delete_user.php' method='POST' onsubmit=\"return confirm('PERINGATAN! Yakin hapus pengguna ini? SEMUA DATA TERKAIT akan hilang permanen.');\">
                                        <input type='hidden' name='id' value='{$row['id']}'>
                                        <button type='submit' class='btn-danger' style='width:100%'>Hapus Pengguna</button>
                                    </form>
                                </div></td>
                            </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' style='text-align: center;'>Tidak ada data pengguna.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <button id="scrollToBottomBtn" title="Ke Bawah"><i class="fas fa-arrow-down"></i></button>
    <button id="scrollToTopBtn" title="Ke Atas"><i class="fas fa-arrow-up"></i></button>
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

        function togglePasswordVisibility(inputId) {
            var passwordInput = document.getElementById(inputId);
            var eyeIcon = passwordInput.nextElementSibling.querySelector('i');
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        function confirmAdminPassword(userId) {
            var adminPassword = prompt("Masukkan password Admin Anda untuk melihat password asli:");
            if (adminPassword != null && adminPassword.trim() != "") {
                fetch('verify_admin_password.php?id=' + userId + '&admin_password=' + encodeURIComponent(adminPassword))
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            var passwordCell = document.getElementById("password-cell-" + userId);
                            passwordCell.childNodes[0].nodeValue = data.password + " ";
                        } else {
                            alert("Gagal: " + data.message);
                        }
                    }).catch(error => {
                        console.error('Error:', error);
                        alert("Terjadi kesalahan saat menghubungi server.");
                    });
            }
        }

        // --- AWAL DARI KODE JAVASCRIPT TOMBOL SCROLL ---
        var scrollToTopBtn = document.getElementById("scrollToTopBtn");
        var scrollToBottomBtn = document.getElementById("scrollToBottomBtn");

        // Tampilkan tombol "Ke Atas" saat scroll ke bawah
        window.onscroll = function() {
            if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
                scrollToTopBtn.style.display = "block";
            } else {
                scrollToTopBtn.style.display = "none";
            }
        };

        // Fungsi klik untuk scroll ke atas
        scrollToTopBtn.onclick = function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        };

        // Fungsi klik untuk scroll ke bawah
        scrollToBottomBtn.onclick = function() {
            window.scrollTo({
                top: document.body.scrollHeight,
                behavior: 'smooth'
            });
        };
        // --- AKHIR DARI KODE JAVASCRIPT TOMBOL SCROLL ---
    </script>
</body>

</html>