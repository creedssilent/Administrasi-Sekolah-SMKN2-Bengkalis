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

// Fetch student payments with joined administrations data
$siswa_pembayaran = $conn->query("SELECT s.id, s.name, p.amount, p.date, p.id AS payment_id, p.keterangan, a.title AS jenis_pembayaran
                                    FROM students s
                                    JOIN payments p ON s.id = p.student_id
                                    JOIN administrations a ON p.administrasi_id = a.id
                                    ORDER BY p.date DESC");

// Fetch available payment types from administrations table
$jenis_pembayaran_query = $conn->query("SELECT id, title FROM administrations");

// Add student payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'], $_POST['amount'], $_POST['keterangan'], $_POST['administrasi_id'])) {
    $student_id = $_POST['student_id'];
    $amount = $_POST['amount'];
    $keterangan = $_POST['keterangan'];
    $administrasi_id = $_POST['administrasi_id']; // ID for selected payment type

    $query = "INSERT INTO payments (student_id, amount, date, keterangan, administrasi_id) VALUES ('$student_id', '$amount', NOW(), '$keterangan', '$administrasi_id')";
    if ($conn->query($query)) {
        echo "<script>alert('Pembayaran siswa berhasil ditambahkan!'); window.location.href='administrasi_siswa.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan pembayaran siswa: " . $conn->error . "');</script>";
    }
}

// Edit student payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_payment_id'], $_POST['edit_amount'], $_POST['edit_keterangan'], $_POST['edit_administrasi_id'])) {
    $edit_payment_id = $_POST['edit_payment_id'];
    $edit_amount = $_POST['edit_amount'];
    $edit_keterangan = $_POST['edit_keterangan'];
    $edit_administrasi_id = $_POST['edit_administrasi_id'];

    $query = "UPDATE payments SET amount = '$edit_amount', keterangan = '$edit_keterangan', administrasi_id = '$edit_administrasi_id' WHERE id = '$edit_payment_id'";
    if ($conn->query($query)) {
        echo "<script>alert('Pembayaran siswa berhasil diperbarui!'); window.location.href='administrasi_siswa.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui pembayaran siswa: " . $conn->error . "');</script>";
    }
}

// Delete student payment
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_payment_id'])) {
    $delete_payment_id = $_GET['delete_payment_id'];

    $query = "DELETE FROM payments WHERE id = '$delete_payment_id'";
    if ($conn->query($query)) {
        echo "<script>alert('Pembayaran siswa berhasil dihapus!'); window.location.href='administrasi_siswa.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus pembayaran siswa: " . $conn->error . "');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrasi Siswa</title>
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
        table,
        th,
        td,
        input,
        select,
        textarea {
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
            /* Warna Hover yang Berbeda */
        }

        /* Submenu */
        .sidebar ul {
            list-style: none;
            padding-left: 0;
            margin-top: 5px;
            display: none;
        }

        .sidebar ul li a {
            padding-left: 40px;
            background-color: transparent;
            color: #8892b0;
        }

        .sidebar ul li a:hover {
            background-color: #2980b9;
            /* Warna Hover yang Berbeda */
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

        /* Tabel */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #495670;
        }

        th {
            background-color: #0a193f;
            /* Darkened Table Header */
            color: #c0cde4;
            font-weight: 600;
        }

        tr:hover {
            background-color: #1e2a47;
        }

        /* Formulir */
        .form-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-container input,
        .form-container textarea,
        .form-container select {
            padding: 12px;
            border: none;
            border-radius: 5px;
            background-color: #1e2a47;
            color: #c0cde4;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 1em;
            outline: none;
            resize: vertical;
            /* Prevent textarea from being too large */
        }

        .form-container input::placeholder,
        .form-container textarea::placeholder,
        .form-container select::placeholder {
            color: #8892b0;
        }

        .form-container textarea {
            height: 100px;
            /* Makes textarea more spacious */
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
        function toggleSetting() {
            var settingMenu = document.querySelector('.sidebar ul');
            settingMenu.style.display = settingMenu.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Logo -->
        <img src="images/Logo SMK.png" alt="Logo Sekolah" class="logo">

        <h2>SISTEM INFORMASI</h2>

        <!-- Info Pengguna -->
        <div class="user-info">
            <div class="profile-picture">
                <!-- TODO: Tambahkan gambar profil pengguna -->
            </div>
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
        <h2 class="dashboard-title">ADMINISTRASI SISWA</h2>

        <div class="card">
            <h3>Tambah Pembayaran Siswa</h3>
            <form method="POST" class="form-container">
                <label for="student_id">ID Siswa:</label>
                <input type="number" name="student_id" placeholder="ID Siswa" required>

                <label for="administrasi_id">Jenis Pembayaran:</label>
                <select name="administrasi_id" required>
                    <option value="">Pilih Jenis Pembayaran</option>
                    <?php while ($jenis = $jenis_pembayaran_query->fetch_assoc()) : ?>
                        <option value="<?= htmlspecialchars($jenis['id']) ?>">
                            <?= htmlspecialchars($jenis['title']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label for="amount">Jumlah Pembayaran:</label>
                <input type="number" name="amount" placeholder="Jumlah Pembayaran" required>

                <label for="keterangan">Keterangan:</label>
                <textarea name="keterangan" placeholder="Keterangan (Lunas, Cicilan 1, dll.)" required></textarea>

                <button type="submit"><i class="fas fa-plus"></i> Tambah Pembayaran</button>
            </form>
        </div>

        <div class="card">
            <h3>Riwayat Pembayaran Siswa</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID Siswa</th>
                        <th>Nama Siswa</th>
                        <th>Jenis Pembayaran</th>
                        <th>Jumlah</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($siswa_pembayaran->num_rows > 0) : ?>
                        <?php
                        // Reset pointer ke awal result set
                        mysqli_data_seek($siswa_pembayaran, 0);
                        while ($row = $siswa_pembayaran->fetch_assoc()) : ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['jenis_pembayaran']) ?></td>
                                <td>Rp<?= number_format(htmlspecialchars($row['amount']), 2, ',', '.') ?></td>
                                <td><?= htmlspecialchars($row['date']) ?></td>
                                <td><?= htmlspecialchars($row['keterangan']) ?></td>
                                <td>
                                    <a href="?edit_payment_id=<?= htmlspecialchars($row['payment_id']) ?>"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="?delete_payment_id=<?= htmlspecialchars($row['payment_id']) ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus pembayaran ini?')"><i class="fas fa-trash"></i> Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7">Tidak ada data pembayaran siswa.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Form Edit Pembayaran -->
        <?php
        if (isset($_GET['edit_payment_id'])) {
            $edit_payment_id = $_GET['edit_payment_id'];
            $edit_query = $conn->query("SELECT * FROM payments WHERE id = '$edit_payment_id'");
            if ($edit_query->num_rows == 1) {
                $edit_data = $edit_query->fetch_assoc();
        ?>
                <div class="card">
                    <h3>Edit Pembayaran Siswa</h3>
                    <form method="POST" class="form-container">
                        <input type="hidden" name="edit_payment_id" value="<?= htmlspecialchars($edit_data['id']) ?>">

                        <label for="edit_administrasi_id">Jenis Pembayaran:</label>
                        <select name="edit_administrasi_id" required>
                            <option value="">Pilih Jenis Pembayaran</option>
                            <?php
                            // Reset the pointer to the beginning of the result set
                            if ($jenis_pembayaran_query->num_rows > 0) {
                                mysqli_data_seek($jenis_pembayaran_query, 0);
                            }
                            while ($jenis = $jenis_pembayaran_query->fetch_assoc()) : ?>
                                <option value="<?= htmlspecialchars($jenis['id']) ?>" <?= ($edit_data['administrasi_id'] == $jenis['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($jenis['title']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>

                        <label for="edit_amount">Jumlah Pembayaran:</label>
                        <input type="number" name="edit_amount" placeholder="Jumlah Pembayaran" value="<?= htmlspecialchars($edit_data['amount']) ?>" required>

                        <label for="edit_keterangan">Keterangan:</label>
                        <textarea name="edit_keterangan" placeholder="Keterangan (Lunas, Cicilan 1, dll.)" required><?= htmlspecialchars($edit_data['keterangan']) ?></textarea>

                        <button type="submit"><i class="fas fa-save"></i> Simpan Perubahan</button>
                    </form>
                </div>
        <?php
            } else {
                echo "<p>Data pembayaran tidak ditemukan.</p>";
            }
        }
        ?>
    </div>
</body>

</html>