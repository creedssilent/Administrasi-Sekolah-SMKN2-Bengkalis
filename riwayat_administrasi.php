<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header('Location: index.php');
    exit();
}
include 'config.php';
$username = $_SESSION['username'];
$student_query = $conn->prepare("SELECT * FROM students WHERE username = ?");
$student_query->bind_param("s", $username);
$student_query->execute();
$student = $student_query->get_result()->fetch_assoc();
$student_query->close();
$upload_dir = 'uploads/profile_pictures/';
include 'components/upload_profile_modal.php';
$administration_query = $conn->prepare("SELECT p.*, a.title AS jenis_pembayaran FROM payments p JOIN administrations a ON p.administrasi_id = a.id WHERE student_id = ? ORDER BY date DESC");
$administration_query->bind_param("i", $student['id']);
$administration_query->execute();
$administration_result = $administration_query->get_result();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Administrasi</title>
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
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            animation: slideInUp 0.6s ease-out forwards;
            opacity: 0;
            border: 1px solid #e9ecef;
        }

        .card h3 {
            font-size: 1.2em;
            font-weight: 600;
            margin: 0 0 20px 0;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        th {
            font-weight: 600;
            font-size: 0.9em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        td a {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 600;
        }

        .popup {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow-y: auto;
            background-color: rgba(45, 55, 72, 0.85);
            justify-content: center;
            align-items: flex-start;
            padding: 5vh 20px;
        }

        .popup-content {
            animation: zoomIn 0.3s ease-out;
        }

        @keyframes zoomIn {
            from {
                transform: scale(0.9);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .close-button {
            position: fixed;
            top: 20px;
            right: 30px;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.8);
            transition: color 0.3s;
            z-index: 1001;
        }

        .close-button:hover {
            color: #fff;
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
                <a href="riwayat_administrasi.php" class="active"><i class="fas fa-file-invoice-dollar"></i> Administrasi</a>
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
            <h2 class="dashboard-title">Riwayat Administrasi</h2>
            <div class="card">
                <h3>Daftar Transaksi Pembayaran</h3>
                <table>
                    <thead>
                        <tr>
                            <th>No. Kwitansi</th>
                            <th>Tanggal</th>
                            <th>Jenis Pembayaran</th>
                            <th style="text-align: right;">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($administration_result->num_rows > 0): ?>
                            <?php while ($row = $administration_result->fetch_assoc()): ?>
                                <tr>
                                    <td><a href="#" onclick="showReceiptDetails(event, <?= $row['id'] ?>)"><?= htmlspecialchars("KWT-" . sprintf("%04d", $row['id'])) ?></a></td>
                                    <td><?= date("d M Y", strtotime($row['date'])) ?></td>
                                    <td><?= htmlspecialchars($row['jenis_pembayaran']) ?></td>
                                    <td style="text-align: right;">Rp <?= number_format($row['amount'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">Tidak ada riwayat pembayaran.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
        <div class="popup" id="receiptPopup">
            <span class="close-button" onclick="closeReceiptDetails()">&times;</span>
            <div class="popup-content" id="popupContent"></div>
        </div>
        <script>
            function showReceiptDetails(e, id) {
                e.preventDefault();
                const popup = document.getElementById('receiptPopup');
                const popupContent = document.getElementById('popupContent');
                popup.style.display = 'flex';
                fetch('detail_kwitansi.php?payment_id=' + id)
                    .then(res => res.text())
                    .then(data => {
                        popupContent.innerHTML = data;

                        // --- LOGIKA BARU UNTUK AUTO-FIT KWITANSI ---
                        // Dijalankan setelah konten dimuat
                        requestAnimationFrame(() => {
                            const receiptContainer = popupContent.querySelector('.receipt-container');
                            if (receiptContainer) {
                                const windowHeight = window.innerHeight;
                                const receiptHeight = receiptContainer.offsetHeight;
                                const verticalMargin = 100; // Total jarak atas & bawah (50px + 50px)

                                // Hanya kecilkan jika lebih tinggi dari layar
                                if (receiptHeight > (windowHeight - verticalMargin)) {
                                    const scaleFactor = (windowHeight - verticalMargin) / receiptHeight;
                                    receiptContainer.style.transform = `scale(${scaleFactor})`;
                                    receiptContainer.style.transformOrigin = 'top center'; // Mulai scaling dari atas
                                }
                            }
                        });
                        // --- AKHIR LOGIKA BARU ---
                    })
                    .catch(error => console.error('Error:', error));
            }

            function closeReceiptDetails() {
                const popup = document.getElementById('receiptPopup');
                const popupContent = document.getElementById('popupContent');
                const receiptContainer = popupContent.querySelector('.receipt-container');

                // Reset transform sebelum disembunyikan
                if (receiptContainer) {
                    receiptContainer.style.transform = 'scale(1)';
                }

                popup.style.display = 'none';
                popupContent.innerHTML = ''; // Kosongkan konten agar tidak menumpuk
            }
            window.onclick = e => {
                if (e.target == document.getElementById('receiptPopup')) closeReceiptDetails();
            };

            function toggleSetting() {
                var settingMenu = document.querySelector('.sidebar-menu ul');
                if (settingMenu) {
                    settingMenu.style.display = settingMenu.style.display === 'none' || settingMenu.style.display === '' ? 'block' : 'none';
                }
            }
        </script>
</body>

</html>