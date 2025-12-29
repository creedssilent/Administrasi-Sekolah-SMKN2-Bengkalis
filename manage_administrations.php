<?php
session_start();
// 1. Pengecekan Hak Akses Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}
include 'config.php';
$username_session = $_SESSION['username'];
$error = '';
$success = '';

// --- Logika PHP dari administrasi_siswa.php (ditingkatkan keamanannya) ---

// Fungsi format Rupiah
function formatRupiah($number)
{
    return 'Rp ' . number_format($number, 0, ',', '.');
}

// 2. Fetch data untuk dropdown
// Ambil daftar siswa untuk dropdown tambah
$students_list_result = $conn->query("SELECT id, name FROM students ORDER BY name ASC");
// Ambil jenis-jenis pembayaran untuk dropdown
$payment_types_result = $conn->query("SELECT id, title FROM administrations ORDER BY title ASC");


// 3. Tambah Pembayaran Siswa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_payment'])) {
    if (!empty($_POST['student_id']) && isset($_POST['amount']) && !empty($_POST['keterangan']) && !empty($_POST['administrasi_id'])) {
        $student_id = $_POST['student_id'];
        $amount = $_POST['amount'];
        $keterangan = $_POST['keterangan'];
        $administrasi_id = $_POST['administrasi_id'];

        $stmt = $conn->prepare("INSERT INTO payments (student_id, amount, date, keterangan, administrasi_id) VALUES (?, ?, NOW(), ?, ?)");
        $stmt->bind_param("idsi", $student_id, $amount, $keterangan, $administrasi_id);
        if ($stmt->execute()) {
            $success = "Pembayaran siswa berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan pembayaran: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Semua field wajib diisi.";
    }
}

// 4. Edit Pembayaran Siswa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_payment'])) {
    if (!empty($_POST['edit_payment_id']) && isset($_POST['edit_amount']) && !empty($_POST['edit_keterangan']) && !empty($_POST['edit_administrasi_id'])) {
        $edit_payment_id = $_POST['edit_payment_id'];
        $edit_amount = $_POST['edit_amount'];
        $edit_keterangan = $_POST['edit_keterangan'];
        $edit_administrasi_id = $_POST['edit_administrasi_id'];

        $stmt = $conn->prepare("UPDATE payments SET amount = ?, keterangan = ?, administrasi_id = ? WHERE id = ?");
        $stmt->bind_param("dsii", $edit_amount, $edit_keterangan, $edit_administrasi_id, $edit_payment_id);
        if ($stmt->execute()) {
            echo "<script>alert('Pembayaran berhasil diperbarui!'); window.location.href='manage_administrations.php';</script>";
            exit();
        } else {
            $error = "Gagal memperbarui pembayaran: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Semua field untuk edit wajib diisi.";
    }
}

// 5. Hapus Pembayaran Siswa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_payment'])) {
    $delete_payment_id = $_POST['delete_payment_id'];
    $stmt = $conn->prepare("DELETE FROM payments WHERE id = ?");
    $stmt->bind_param("i", $delete_payment_id);
    if ($stmt->execute()) {
        $success = "Pembayaran berhasil dihapus.";
    } else {
        $error = "Gagal menghapus pembayaran: " . $stmt->error;
    }
    $stmt->close();
}


// 6. Fetch riwayat pembayaran untuk ditampilkan di tabel
$payment_history_result = $conn->query("SELECT p.id as payment_id, s.name as student_name, s.id as student_id, a.title as payment_type, p.amount, p.date, p.keterangan
                                       FROM payments p
                                       JOIN students s ON p.student_id = s.id
                                       JOIN administrations a ON p.administrasi_id = a.id
                                       ORDER BY p.date DESC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Administrasi Siswa - SIMANDAKA</title>
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
        button,
        textarea {
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
        select:focus,
        textarea:focus {
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

        .btn-edit {
            background-color: #ffc107;
            color: #1e293b;
            font-size: 0.85em;
            padding: 8px 12px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-edit:hover {
            background-color: #e0a800;
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

        .full-width {
            grid-column: 1 / -1;
        }

        td .actions {
            display: flex;
            gap: 5px;
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
                <a href="manage_teachers.php"><i class="fas fa-chalkboard-teacher"></i> Kelola Guru</a>
                <a href="manage_administrations.php" class="active"><i class="fas fa-file-invoice"></i> Kelola Administrasi</a>
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
                    <h1>Kelola Administrasi Siswa</h1>
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

                <?php if (isset($_GET['edit_payment_id'])) :
                    $edit_id = $_GET['edit_payment_id'];
                    $stmt = $conn->prepare("SELECT * FROM payments WHERE id = ?");
                    $stmt->bind_param("i", $edit_id);
                    $stmt->execute();
                    $edit_result = $stmt->get_result();
                    if ($edit_result->num_rows > 0) :
                        $edit_data = $edit_result->fetch_assoc();
                ?>
                        <div class="content-panel">
                            <h2>Edit Pembayaran Siswa</h2>
                            <form method="POST" action="manage_administrations.php">
                                <input type="hidden" name="edit_payment_id" value="<?= htmlspecialchars($edit_data['id']) ?>">
                                <div class="form-grid">
                                    <div class="form-group"><label>Siswa ID</label><input type="text" value="<?= htmlspecialchars($edit_data['student_id']) ?>" disabled></div>
                                    <div class="form-group"><label for="edit_administrasi_id">Jenis Pembayaran</label><select name="edit_administrasi_id" required><?php $payment_types_result->data_seek(0);
                                                                                                                                                                    while ($jenis = $payment_types_result->fetch_assoc()) : ?><option value="<?= $jenis['id'] ?>" <?= ($edit_data['administrasi_id'] == $jenis['id']) ? 'selected' : '' ?>><?= htmlspecialchars($jenis['title']) ?></option><?php endwhile; ?></select></div>
                                    <div class="form-group"><label for="edit_amount">Jumlah (Rp)</label><input type="number" name="edit_amount" value="<?= htmlspecialchars($edit_data['amount']) ?>" required></div>
                                    <div class="form-group"><label for="edit_keterangan">Keterangan</label><input type="text" name="edit_keterangan" value="<?= htmlspecialchars($edit_data['keterangan']) ?>" required></div>
                                </div><br>
                                <button type="submit" name="edit_payment">Simpan Perubahan</button>
                            </form>
                        </div>
                <?php endif;
                    $stmt->close();
                endif; ?>

                <div class="content-panel">
                    <h2>Tambah Pembayaran Siswa</h2>
                    <form method="POST" action="manage_administrations.php">
                        <div class="form-grid">
                            <div class="form-group"><label for="student_id">Pilih Siswa</label><select name="student_id" required>
                                    <option value="" disabled selected>-- Pilih Siswa --</option><?php while ($siswa = $students_list_result->fetch_assoc()) : ?><option value="<?= $siswa['id'] ?>"><?= htmlspecialchars($siswa['name']) ?></option><?php endwhile; ?>
                                </select></div>
                            <div class="form-group"><label for="administrasi_id">Jenis Pembayaran</label><select name="administrasi_id" required>
                                    <option value="" disabled selected>-- Pilih Jenis --</option><?php $payment_types_result->data_seek(0);
                                                                                                    while ($jenis = $payment_types_result->fetch_assoc()) : ?><option value="<?= $jenis['id'] ?>"><?= htmlspecialchars($jenis['title']) ?></option><?php endwhile; ?>
                                </select></div>
                            <div class="form-group full-width"><label for="amount">Jumlah (Rp)</label><input type="number" name="amount" placeholder="Contoh: 50000" required></div>
                            <div class="form-group full-width"><label for="keterangan">Keterangan</label><input type="text" name="keterangan" placeholder="Contoh: Lunas, Cicilan ke-1" required></div>
                        </div><br>
                        <button type="submit" name="add_payment">Tambah Pembayaran</button>
                    </form>
                </div>

                <div class="content-panel">
                    <h3>Riwayat Pembayaran Siswa</h3>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID Bayar</th>
                                    <th>Nama Siswa</th>
                                    <th>Jenis Pembayaran</th>
                                    <th>Jumlah</th>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($payment_history_result->num_rows > 0) : ?>
                                    <?php while ($row = $payment_history_result->fetch_assoc()) : ?>
                                        <tr>
                                            <td><?= $row['payment_id'] ?></td>
                                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                                            <td><?= htmlspecialchars($row['payment_type']) ?></td>
                                            <td><?= formatRupiah($row['amount']) ?></td>
                                            <td><?= date('d M Y, H:i', strtotime($row['date'])) ?></td>
                                            <td><?= htmlspecialchars($row['keterangan']) ?></td>
                                            <td>
                                                <div class="actions">
                                                    <a href="?edit_payment_id=<?= $row['payment_id'] ?>" class="btn-edit">Edit</a>
                                                    <form method="POST" action="manage_administrations.php" onsubmit="return confirm('Yakin ingin menghapus data pembayaran ini?');" style="display:inline;">
                                                        <input type="hidden" name="delete_payment_id" value="<?= $row['payment_id'] ?>">
                                                        <button type="submit" name="delete_payment" class="btn-danger">Hapus</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center;">Belum ada riwayat pembayaran.</td>
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