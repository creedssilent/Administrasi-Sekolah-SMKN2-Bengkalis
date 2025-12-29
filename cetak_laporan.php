<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header('Location: index.php');
    exit();
}

if (!isset($_SESSION['username'])) {
    echo "<script>alert('Kesalahan sesi. Silakan login kembali.'); window.location.href='index.php';</script>";
    exit();
}

include 'config.php';

$username = $_SESSION['username'];

// Ambil data siswa berdasarkan username
$student_query = $conn->prepare("SELECT * FROM students WHERE username = ?");
$student_query->bind_param("s", $username);
$student_query->execute();
$student_result = $student_query->get_result();

if (!$student_result || $student_result->num_rows === 0) {
    die("<script>alert('Data siswa tidak ditemukan. Silakan hubungi admin.'); window.location.href='siswa_dashboard.php';</script>");
}
$student = $student_result->fetch_assoc();
$student_query->close();

// Variabel yang diperlukan untuk modal upload foto
$user_id = $student['id'];
$current_profile_picture = $student['profile_picture'];
$user_table = 'students';
$upload_dir = 'uploads/profile_pictures/';
include 'components/upload_profile_modal.php';

// Ambil data nilai siswa
$nilai_query = $conn->prepare("SELECT subjects.name AS mata_pelajaran, grades.grade AS nilai FROM grades JOIN subjects ON grades.subject_id = subjects.id WHERE grades.student_id = ?");
$nilai_query->bind_param("i", $student['id']);
$nilai_query->execute();
$nilai_result = $nilai_query->get_result();
$nilai_query->close();

// Ambil data absensi siswa
$attendance_query = $conn->prepare("SELECT date, status, s.name AS subject_name FROM attendance a JOIN subjects s ON a.subject_id = s.id WHERE student_id = ? ORDER BY date DESC");
$attendance_query->bind_param("i", $student['id']);
$attendance_query->execute();
$attendance_result = $attendance_query->get_result();
$attendance_query->close();

// Ambil data riwayat pembayaran
$payment_query = $conn->prepare("SELECT p.date, p.amount, a.title AS jenis_pembayaran FROM payments p JOIN administrations a ON p.administrasi_id = a.id WHERE p.student_id = ? ORDER BY p.date DESC");
$payment_query->bind_param("i", $student['id']);
$payment_query->execute();
$payment_result = $payment_query->get_result();
$payment_query->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Semester</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
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
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-dark);
            margin: 0;
            line-height: 1.6;
        }

        .dashboard-container {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        /* Sidebar Styles */
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
            width: 100%;
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

        .sidebar-menu a:hover {
            background-color: var(--accent-color);
            color: #fff;
        }

        .sidebar-menu a.active {
            background-color: var(--accent-color);
            color: #fff;
            font-weight: 600;
        }

        .sidebar-menu ul {
            list-style: none;
            padding-left: 15px;
            width: 100%;
            margin-top: 5px;
            display: none;
        }

        /* Main Content Styles */
        .container {
            padding: 40px;
            overflow-y: auto;
        }

        .dashboard-header {
            margin-bottom: 30px;
        }

        .dashboard-header h1 {
            font-size: 2.2em;
            font-weight: 700;
            color: var(--text-dark);
        }

        .dashboard-header p {
            font-size: 1.1em;
            color: var(--text-muted);
        }

        .card {
            background-color: var(--bg-card);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
            border: 1px solid var(--border-color);
        }

        .card h3 {
            font-size: 1.4em;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        /* Student Info Styles */
        .student-info p {
            font-size: 1em;
            margin-bottom: 8px;
            color: var(--text-muted);
        }

        .student-info p strong {
            color: var(--text-dark);
            min-width: 150px;
            display: inline-block;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: #f8fafc;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 0.85em;
        }

        tbody tr:hover {
            background-color: #f9f9f9;
        }

        /* Download Button */
        .download-btn {
            background-color: var(--accent-color);
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.3s;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 1em;
            margin-top: 20px;
        }

        .download-btn:hover {
            background-color: var(--accent-hover);
            transform: translateY(-2px);
        }

        .download-btn i {
            font-size: 1.1em;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                height: auto;
                position: static;
            }

            .container {
                padding: 20px;
            }
        }

        @media (max-width: 576px) {
            .dashboard-header h1 {
                font-size: 1.8em;
            }

            .card {
                padding: 20px;
            }

            th,
            td {
                padding: 10px;
            }
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
                <div class="profile-picture" id="profile-picture-container">
                    <img src="<?php echo (!empty($student['profile_picture']) && file_exists($upload_dir . $student['profile_picture'])) ? $upload_dir . $student['profile_picture'] : 'images/default_profile.png'; ?>" alt="Foto Profil">
                </div>
                <span class="profile-name"><?php echo htmlspecialchars($student['name'] ?? 'Siswa'); ?></span>
            </div>
            <nav class="sidebar-menu">
                <a href="siswa_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="input_biodata.php"><i class="fas fa-id-card"></i> Biodata</a>
                <a href="absensi.php"><i class="fas fa-user-check"></i> Absensi</a>
                <a href="riwayat_administrasi.php"><i class="fas fa-file-invoice-dollar"></i> Administrasi</a>
                <a href="riwayat_absensi.php"><i class="fas fa-history"></i> Riwayat Absensi</a>
                <a href="lihat_nilai.php"><i class="fas fa-graduation-cap"></i> Lihat Nilai</a>
                <a href="cetak_laporan.php" class="active"><i class="fas fa-print"></i> Cetak Lapor</a>
                <a href="#" onclick="toggleSetting()"><i class="fas fa-cog"></i> Setting</a>
                <ul id="settingMenu">
                    <li><a href="change_password_siswa.php"><i class="fas fa-key"></i> Ubah Password</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>

        <main class="container" id="reportContent">
            <div class="dashboard-header">
                <h1>Laporan Semester</h1>
                <p>Berikut adalah rekapitulasi data akademik dan administratif Anda.</p>
            </div>

            <div class="card">
                <h3>Informasi Siswa</h3>
                <div class="student-info">
                    <p><strong>Nama</strong>: <?php echo htmlspecialchars($student['name']); ?></p>
                    <p><strong>NISN</strong>: <?php echo htmlspecialchars($student['nisn'] ?? '-'); ?></p>
                    <p><strong>Kelas</strong>: <?php
                                                $class_name = "Belum ada kelas";
                                                if (!empty($student['class_id'])) {
                                                    $class_query = $conn->prepare("SELECT name FROM classes WHERE id = ?");
                                                    $class_query->bind_param("i", $student['class_id']);
                                                    $class_query->execute();
                                                    $class_result = $class_query->get_result();
                                                    if ($class_row = $class_result->fetch_assoc()) {
                                                        $class_name = $class_row['name'];
                                                    }
                                                    $class_query->close();
                                                }
                                                echo htmlspecialchars($class_name);
                                                ?></p>
                    <p><strong>Tahun Akademik</strong>: <?php echo date('Y') . '/' . (date('Y') + 1); ?></p>
                </div>
            </div>

            <div class="card">
                <h3>Daftar Nilai</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Mata Pelajaran</th>
                            <th>Nilai</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($nilai_result->num_rows > 0): ?>
                            <?php while ($row = $nilai_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['mata_pelajaran']) ?></td>
                                    <td><?= htmlspecialchars($row['nilai']) ?></td>
                                    <td><?= ($row['nilai'] >= 75) ? 'Lulus' : 'Remedial' ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center;">Belum ada nilai yang tersedia.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h3>Riwayat Absensi</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Mata Pelajaran</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($attendance_result->num_rows > 0) : ?>
                            <?php while ($row = $attendance_result->fetch_assoc()) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['subject_name']) ?></td>
                                    <td><?= htmlspecialchars(date('d M Y', strtotime($row['date']))) ?></td>
                                    <td><?= htmlspecialchars($row['status']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="3" style="text-align: center;">Tidak ada data absensi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h3>Riwayat Administrasi</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis Pembayaran</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($payment_result->num_rows > 0) : ?>
                            <?php while ($row = $payment_result->fetch_assoc()) : ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('d M Y', strtotime($row['date']))) ?></td>
                                    <td><?= htmlspecialchars($row['jenis_pembayaran']) ?></td>
                                    <td>Rp<?= number_format($row['amount'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="3" style="text-align: center;">Belum ada riwayat pembayaran.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <button onclick="downloadPDF()" class="download-btn">
                <i class="fas fa-download"></i> Unduh Rapor PDF
            </button>
        </main>
    </div>

    <script>
        // Toggle untuk menu setting di sidebar
        function toggleSetting() {
            var settingMenu = document.getElementById('settingMenu');
            if (settingMenu) {
                settingMenu.style.display = settingMenu.style.display === 'none' || settingMenu.style.display === '' ? 'block' : 'none';
            }
        }

        // Fungsi untuk mengunduh konten sebagai PDF
        function downloadPDF() {
            const {
                jsPDF
            } = window.jspdf;
            const content = document.getElementById('reportContent');
            const sidebar = document.querySelector('.sidebar');
            const downloadButton = document.querySelector('.download-btn');

            // Sembunyikan elemen yang tidak perlu dicetak
            if (sidebar) sidebar.style.display = 'none';
            if (downloadButton) downloadButton.style.display = 'none';

            // Atur ulang grid agar konten mengisi seluruh halaman
            document.querySelector('.dashboard-container').style.gridTemplateColumns = '1fr';

            html2canvas(content, {
                scale: 2, // Skala lebih tinggi untuk kualitas gambar yang lebih baik
                useCORS: true,
                backgroundColor: '#ffffff' // Pastikan background putih untuk PDF
            }).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF('p', 'mm', 'a4');
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                const canvasWidth = canvas.width;
                const canvasHeight = canvas.height;
                const ratio = canvasWidth / canvasHeight;
                const imgWidth = pdfWidth;
                const imgHeight = imgWidth / ratio;

                let heightLeft = imgHeight;
                let position = 0;

                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pdfHeight;

                while (heightLeft > 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pdfHeight;
                }

                pdf.save('Laporan_Semester_<?= str_replace(" ", "_", htmlspecialchars($student['name'])) ?>.pdf');

                // Kembalikan tampilan elemen seperti semula
                if (sidebar) sidebar.style.display = 'flex';
                if (downloadButton) downloadButton.style.display = 'inline-flex';
                document.querySelector('.dashboard-container').style.gridTemplateColumns = '260px 1fr';

            }).catch(error => {
                console.error("Gagal membuat PDF:", error);
                alert("Terjadi kesalahan saat membuat PDF. Silakan coba lagi.");
                // Pastikan untuk mengembalikan tampilan jika terjadi error
                if (sidebar) sidebar.style.display = 'flex';
                if (downloadButton) downloadButton.style.display = 'inline-flex';
                document.querySelector('.dashboard-container').style.gridTemplateColumns = '260px 1fr';
            });
        }
    </script>
</body>

</html>
<?php
$conn->close();
?>