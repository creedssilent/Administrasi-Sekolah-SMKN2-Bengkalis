<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header('Location: index.php');
    exit();
}
include 'config.php';
$username = $_SESSION['username'];
$error = '';
$success = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Semua kolom harus diisi.";
    } elseif ($new_password != $confirm_password) {
        $error = "Password baru dan konfirmasi password tidak cocok.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($old_password, $row['password'])) {
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
                $update_stmt->bind_param("ss", $new_hashed_password, $username);
                if ($update_stmt->execute()) {
                    $success = "Password berhasil diubah.";
                } else {
                    $error = "Terjadi kesalahan saat mengubah password.";
                }
                $update_stmt->close();
            } else {
                $error = "Password lama tidak sesuai.";
            }
        }
        $stmt->close();
    }
}
$student_query = $conn->prepare("SELECT * FROM students WHERE username = ?");
$student_query->bind_param("s", $username);
$student_query->execute();
$student = $student_query->get_result()->fetch_assoc();
$upload_dir = 'uploads/profile_pictures/';
include 'components/upload_profile_modal.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password</title>
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
        }

        .sidebar-menu ul li a {
            font-size: 0.9em;
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
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            animation: slideInUp 0.6s ease-out forwards;
            opacity: 0;
            max-width: 500px;
            margin: 0 auto;
            /* PERBAIKAN: Form di tengah */
        }

        .card h3 {
            font-size: 1.2em;
            font-weight: 600;
            margin: 0 0 25px 0;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 1em;
            box-sizing: border-box;
        }

        .btn-submit {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        .btn-submit:hover {
            background-color: var(--accent-hover);
        }

        /* PERBAIKAN: Animasi Tombol */
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .success {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
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
                <a href="riwayat_absensi.php"><i class="fas fa-history"></i> Riwayat Absensi</a>
                <a href="lihat_nilai.php"><i class="fas fa-graduation-cap"></i> Lihat Nilai</a>
                <a href="cetak_laporan.php"><i class="fas fa-print"></i> Cetak Lapor</a>
                <a href="#" onclick="toggleSetting()" class="active"><i class="fas fa-cog"></i> Setting</a>
                <ul id="settingMenu" style="display: block;">
                    <li><a href="change_password_siswa.php">Ubah Password</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
        <main class="container">
            <h2 class="dashboard-title">Ubah Password</h2>
            <div class="card">
                <form method="post">
                    <?php if ($error): ?><p class="message error"><?= $error ?></p><?php endif; ?>
                    <?php if ($success): ?><p class="message success"><?= $success ?></p><?php endif; ?>
                    <div class="form-group"><label for="old_password">Password Lama</label><input type="password" name="old_password" required></div>
                    <div class="form-group"><label for="new_password">Password Baru</label><input type="password" name="new_password" required></div>
                    <div class="form-group"><label for="confirm_password">Konfirmasi Password Baru</label><input type="password" name="confirm_password" required></div>
                    <button type="submit" class="btn-submit">Simpan Password Baru</button>
                </form>
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