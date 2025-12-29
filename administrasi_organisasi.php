<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrasi') {
    header('Location: index.php');
    exit();
}
include 'config.php';

if (!isset($_SESSION['username'])) {
    echo "<script>alert('Kesalahan sesi. Silakan login kembali.'); window.location.href='index.php';</script>";
    exit();
}

$username = $_SESSION['username'];

// Fetch data dari tabel organizations
$organisasi_query = $conn->query("SELECT * FROM organizations ORDER BY created_at DESC");
if (!$organisasi_query) {
    die("Error: " . $conn->error);
}

// Tambah data administrasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['description'], $_POST['amount'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $amount = str_replace('.', '', $_POST['amount']); // Hilangkan titik sebelum disimpan ke database

    $query = "INSERT INTO organizations (title, description, amount, created_at) VALUES ('$title', '$description', '$amount', NOW())";
    if ($conn->query($query)) {
        echo "<script>alert('Data administrasi berhasil ditambahkan!'); window.location.href='administrasi_organisasi.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan data administrasi: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrasi Organisasi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* CSS Reset */
        body,
        h1,
        h2,
        h3,
        p,
        ul,
        li,
        button,
        input,
        textarea,
        table,
        th,
        td {
            margin: 0;
            padding: 0;
            border: 0;
            font-size: 100%;
            font: inherit;
            vertical-align: baseline;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #142850;
            color: #c0cde4;
            line-height: 1.6;
            overflow-x: hidden;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: #091f3d;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .sidebar::-webkit-scrollbar {
            display: none;
        }

        /* Logo */
        .logo {
            width: 100px;
            margin-bottom: 10px;
        }

        .sidebar h2 {
            font-size: 1.5em;
            font-weight: 600;
            color: #c0cde4;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            text-align: center;
            width: 100%;
        }

        /* Info Pengguna */
        .user-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
        }

        .profile-picture {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #495670;
            margin-bottom: 10px;
        }

        .user-info span {
            font-size: 1em;
            color: #8892b0;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 12px;
            margin-bottom: 8px;
            background-color: transparent;
            color: #c0cde4;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .sidebar a i {
            margin-right: 15px;
            font-size: 1.2em;
            color: #5dade2;
        }

        .sidebar a:hover {
            background-color: #2980b9;
        }

        /* Submenu */
        .sidebar ul {
            list-style: none;
            padding-left: 0;
            margin-top: 5px;
            display: none;
            /* Awalnya disembunyikan */
        }

        .sidebar ul li a {
            padding-left: 40px;
            background-color: transparent;
            color: #8892b0;
        }

        .sidebar ul li a:hover {
            background-color: #2980b9;
        }

        /* Container Utama */
        .container {
            flex: 1;
            padding: 30px;
            margin-left: 300px;
        }

        /* Judul Dashboard */
        .dashboard-title {
            font-size: 2em;
            font-weight: 700;
            color: #c0cde4;
            letter-spacing: 0.1em;
            margin-bottom: 30px;
            text-transform: uppercase;
            text-align: left;
        }

        /* Card */
        .card {
            background-color: #091f3d;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .card h3 {
            font-size: 1.3em;
            font-weight: 600;
            color: #c0cde4;
            margin-bottom: 15px;
        }

        /* Formulir */
        .form-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-container input,
        .form-container textarea {
            padding: 12px;
            border: none;
            border-radius: 5px;
            background-color: #1e2a47;
            color: #c0cde4;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 1em;
            outline: none;
        }

        .form-container input::placeholder,
        .form-container textarea::placeholder {
            color: #8892b0;
        }

        .form-container textarea {
            resize: vertical;
        }

        /* Tabel */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #495670;
        }

        th {
            background-color: #0a192f;
            color: #c0cde4;
            font-weight: 600;
        }

        /* Responsif */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                align-items: center;
            }

            .sidebar h2 {
                text-align: center;
            }

            .container {
                padding: 20px;
                margin-left: 0;
            }
        }
    </style>
    <script>
        // Fungsi untuk memformat input jumlah dengan titik dan Rp.
        function formatCurrency(input) {
            // Hapus semua karakter selain angka
            let value = input.value.replace(/[^0-9]/g, '');

            // Format angka dengan titik sebagai pemisah ribuan
            if (value.length > 3) {
                value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            // Tambahkan "Rp." di depan angka
            input.value = 'Rp. ' + value;
        }

        // Fungsi untuk menghapus "Rp." dan titik sebelum mengirim data ke server
        function prepareAmountForSubmit() {
            const amountInput = document.querySelector('input[name="amount"]');
            amountInput.value = amountInput.value.replace(/[^0-9]/g, '');
        }

        // Fungsi untuk toggle menu Setting
        function toggleSetting() {
            var settingMenu = document.querySelector('.sidebar ul');
            if (settingMenu.style.display === 'none' || settingMenu.style.display === '') {
                settingMenu.style.display = 'block';
            } else {
                settingMenu.style.display = 'none';
            }
        }
    </script>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <img src="images/Logo SMK.png" alt="Logo Sekolah" class="logo">
        <h2>SISTEM INFORMASI</h2>
        <div class="user-info">
            <div class="profile-picture"></div>
            <span><?php echo htmlspecialchars($username); ?></span>
        </div>
        <a href="administrasi_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="administrasi_siswa.php"><i class="fas fa-user-graduate"></i> Administrasi Siswa</a>
        <a href="administrasi_sekolah.php"><i class="fas fa-school"></i> Administrasi Sekolah</a>
        <a href="administrasi_organisasi.php"><i class="fas fa-users"></i> Organisasi</a>
        <a href="administrasi_kegiatan.php"><i class="fas fa-calendar-alt"></i> Kegiatan Sekolah</a>
        <a href="#" onclick="toggleSetting()"><i class="fas fa-cog"></i> Setting</a>
        <ul>
            <li><a href="change_password_administrasi.php"><i class="fas fa-key"></i> Ubah Password</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Container Utama -->
    <div class="container">
        <h2 class="dashboard-title">ADMINISTRASI ORGANISASI</h2>

        <!-- Form Tambah Administrasi -->
        <div class="card">
            <h3>Tambah Administrasi</h3>
            <form method="POST" class="form-container" onsubmit="prepareAmountForSubmit()">
                <input type="text" name="title" placeholder="Judul (Contoh: Gaji Guru Tetap, Pengeluaran Sekolah, dll.)" required>
                <textarea name="description" placeholder="Deskripsi" rows="3" required></textarea>
                <input type="text" name="amount" placeholder="Jumlah" oninput="formatCurrency(this)" required>
                <button type="submit"><i class="fas fa-plus"></i> Tambah Administrasi</button>
            </form>
        </div>

        <!-- Tabel Daftar Administrasi -->
        <div class="card">
            <h3>Daftar Administrasi</h3>
            <table>
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Deskripsi</th>
                        <th>Jumlah</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $organisasi_query->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td>Rp. <?php echo number_format($row['amount'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>