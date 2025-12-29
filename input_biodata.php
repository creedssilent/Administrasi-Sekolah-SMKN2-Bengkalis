<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header('Location: index.php');
    exit();
}
include 'config.php';
$username = $_SESSION['username'];

$stmt = $conn->prepare("SELECT * FROM students WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // PHP logic untuk update data (tidak diubah)
    $nisn = $_POST['nisn'];
    $name = $_POST['name'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $agama = $_POST['agama'];
    $status_keluarga = $_POST['status_keluarga'];
    $anak_ke = $_POST['anak_ke'];
    $alamat = $_POST['alamat'];
    $nomor_telepon = $_POST['nomor_telepon'];
    $sekolah_asal = $_POST['sekolah_asal'];
    $diterima_di_kelas = $_POST['diterima_di_kelas'];
    $tanggal_diterima = $_POST['tanggal_diterima'];
    $nama_ayah = $_POST['nama_ayah'];
    $pekerjaan_ayah = $_POST['pekerjaan_ayah'];
    $nama_ibu = $_POST['nama_ibu'];
    $pekerjaan_ibu = $_POST['pekerjaan_ibu'];
    $alamat_orang_tua = $_POST['alamat_orang_tua'];
    $nama_wali = $_POST['nama_wali'];
    $pekerjaan_wali = $_POST['pekerjaan_wali'];
    $alamat_wali = $_POST['alamat_wali'];
    $stmt = $conn->prepare("UPDATE students SET nisn=?, name=?, tempat_lahir=?, tanggal_lahir=?, jenis_kelamin=?, agama=?, status_keluarga=?, anak_ke=?, alamat=?, nomor_telepon=?, sekolah_asal=?, diterima_di_kelas=?, tanggal_diterima=?, nama_ayah=?, pekerjaan_ayah=?, nama_ibu=?, pekerjaan_ibu=?, alamat_orang_tua=?, nama_wali=?, pekerjaan_wali=?, alamat_wali=? WHERE username=?");
    $stmt->bind_param("sssssssissssssssssssss", $nisn, $name, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $agama, $status_keluarga, $anak_ke, $alamat, $nomor_telepon, $sekolah_asal, $diterima_di_kelas, $tanggal_diterima, $nama_ayah, $pekerjaan_ayah, $nama_ibu, $pekerjaan_ibu, $alamat_orang_tua, $nama_wali, $pekerjaan_wali, $alamat_wali, $username);
    if ($stmt->execute()) {
        echo "<script>alert('Biodata berhasil diperbarui!'); window.location.href='input_biodata.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui biodata.');</script>";
    }
    $stmt->close();
    exit();
}
$upload_dir = 'uploads/profile_pictures/';
include 'components/upload_profile_modal.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biodata Siswa</title>
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
            text-transform: uppercase;
            letter-spacing: 1px;
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
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            animation: slideInUp 0.6s ease-out forwards;
            opacity: 0;
        }

        .card h3 {
            font-size: 1.2em;
            font-weight: 600;
            margin: 0 0 25px 0;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px 30px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 0.9em;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px 12px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-family: 'Poppins';
            font-size: 1em;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-actions {
            grid-column: 1 / -1;
            text-align: right;
            margin-top: 20px;
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
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="images/Logo SMK.png" alt="Logo Sekolah" class="logo">
                <div class="sidebar-title-container">
                    <h2>SIMANDAKA</h2>
                    <p>SMK Negeri 2 Bengkalis</p>
                </div>
            </div>
            <div class="sidebar-profile">
                <div class="profile-picture" id="profile-picture-container"><img src="<?php echo (!empty($student['profile_picture']) && file_exists($upload_dir . $student['profile_picture'])) ? $upload_dir . $student['profile_picture'] : 'images/default_profile.png'; ?>" alt="Foto Profil"></div>
                <span class="profile-name"><?php echo htmlspecialchars($student['name'] ?? 'Siswa'); ?></span>
            </div>
            <nav class="sidebar-menu">
                <a href="siswa_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="input_biodata.php" class="active"><i class="fas fa-id-card"></i> Biodata</a>
                <a href="absensi.php"><i class="fas fa-user-check"></i> Absensi</a>
                <a href="riwayat_administrasi.php"><i class="fas fa-file-invoice-dollar"></i> Administrasi</a>
                <a href="riwayat_absensi.php"><i class="fas fa-history"></i> Riwayat Absensi</a>
                <a href="lihat_nilai.php"><i class="fas fa-graduation-cap"></i> Lihat Nilai</a>
                <a href="cetak_laporan.php"><i class="fas fa-print"></i> Cetak Lapor</a>
                <a href="#" onclick="toggleSetting()"><i class="fas fa-cog"></i> Setting</a>
                <ul id="settingMenu">
                    <li><a href="change_password_siswa.php">Ubah Password</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
        <main class="container">
            <h2 class="dashboard-title">Profil Biodata Diri</h2>
            <div class="card">
                <form method="POST">
                    <h3>Informasi Pribadi</h3>
                    <div class="form-grid">
                        <div class="form-group"><label>NISN</label><input type="text" name="nisn" value="<?= htmlspecialchars($student['nisn'] ?? '') ?>"></div>
                        <div class="form-group"><label>Nama Lengkap</label><input type="text" name="name" value="<?= htmlspecialchars($student['name'] ?? '') ?>" required></div>
                        <div class="form-group"><label>Tempat Lahir</label><input type="text" name="tempat_lahir" value="<?= htmlspecialchars($student['tempat_lahir'] ?? '') ?>"></div>
                        <div class="form-group"><label>Tanggal Lahir</label><input type="date" name="tanggal_lahir" value="<?= htmlspecialchars($student['tanggal_lahir'] ?? '') ?>"></div>
                        <div class="form-group"><label>Jenis Kelamin</label><select name="jenis_kelamin">
                                <option value="L" <?= ($student['jenis_kelamin'] ?? '') == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="P" <?= ($student['jenis_kelamin'] ?? '') == 'P' ? 'selected' : '' ?>>Perempuan</option>
                            </select></div>
                        <div class="form-group"><label>Agama</label><input type="text" name="agama" value="<?= htmlspecialchars($student['agama'] ?? '') ?>"></div>
                        <div class="form-group"><label>Status dalam Keluarga</label><input type="text" name="status_keluarga" value="<?= htmlspecialchars($student['status_keluarga'] ?? '') ?>"></div>
                        <div class="form-group"><label>Anak Ke</label><input type="number" name="anak_ke" value="<?= htmlspecialchars($student['anak_ke'] ?? '') ?>"></div>
                        <div class="form-group full-width"><label>Alamat Lengkap</label><textarea name="alamat" rows="3"><?= htmlspecialchars($student['alamat'] ?? '') ?></textarea></div>
                        <div class="form-group"><label>Nomor Telepon</label><input type="tel" name="nomor_telepon" value="<?= htmlspecialchars($student['nomor_telepon'] ?? '') ?>"></div>
                    </div>
                    <h3 style="margin-top: 30px;">Informasi Akademik</h3>
                    <div class="form-grid">
                        <div class="form-group"><label>Sekolah Asal</label><input type="text" name="sekolah_asal" value="<?= htmlspecialchars($student['sekolah_asal'] ?? '') ?>"></div>
                        <div class="form-group"><label>Diterima di Kelas</label><input type="text" name="diterima_di_kelas" value="<?= htmlspecialchars($student['diterima_di_kelas'] ?? '') ?>"></div>
                        <div class="form-group full-width"><label>Tanggal Diterima</label><input type="date" name="tanggal_diterima" value="<?= htmlspecialchars($student['tanggal_diterima'] ?? '') ?>"></div>
                    </div>
                    <h3 style="margin-top: 30px;">Informasi Orang Tua / Wali</h3>
                    <div class="form-grid">
                        <div class="form-group"><label>Nama Ayah</label><input type="text" name="nama_ayah" value="<?= htmlspecialchars($student['nama_ayah'] ?? '') ?>"></div>
                        <div class="form-group"><label>Pekerjaan Ayah</label><input type="text" name="pekerjaan_ayah" value="<?= htmlspecialchars($student['pekerjaan_ayah'] ?? '') ?>"></div>
                        <div class="form-group"><label>Nama Ibu</label><input type="text" name="nama_ibu" value="<?= htmlspecialchars($student['nama_ibu'] ?? '') ?>"></div>
                        <div class="form-group"><label>Pekerjaan Ibu</label><input type="text" name="pekerjaan_ibu" value="<?= htmlspecialchars($student['pekerjaan_ibu'] ?? '') ?>"></div>
                        <div class="form-group full-width"><label>Alamat Orang Tua</label><textarea name="alamat_orang_tua" rows="3"><?= htmlspecialchars($student['alamat_orang_tua'] ?? '') ?></textarea></div>
                        <div class="form-group"><label>Nama Wali</label><input type="text" name="nama_wali" value="<?= htmlspecialchars($student['nama_wali'] ?? '') ?>"></div>
                        <div class="form-group"><label>Pekerjaan Wali</label><input type="text" name="pekerjaan_wali" value="<?= htmlspecialchars($student['pekerjaan_wali'] ?? '') ?>"></div>
                        <div class="form-group full-width"><label>Alamat Wali</label><textarea name="alamat_wali" rows="3"><?= htmlspecialchars($student['alamat_wali'] ?? '') ?></textarea></div>
                    </div>
                    <div class="form-actions"><button type="submit" class="btn-submit">Simpan Perubahan</button></div>
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