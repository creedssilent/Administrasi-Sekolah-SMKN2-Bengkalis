<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala_sekolah') {
    header('Location: index.php');
    exit();
}
include 'config.php';

// --- AWAL BAGIAN BARU: Ambil rentang data yang tersedia ---
$date_range_result = $conn->query("SELECT MIN(date) as min_date, MAX(date) as max_date FROM attendance");
$date_range = $date_range_result->fetch_assoc();
$min_date = $date_range['min_date'] ?? date('Y-m-d');
$max_date = $date_range['max_date'] ?? date('Y-m-d');
// --- AKHIR BAGIAN BARU ---

// Filter Tanggal
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// 1. Kueri untuk Ringkasan & Grafik
$summary_data = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alfa' => 0];
$stmt_summary = $conn->prepare("SELECT status, COUNT(id) as jumlah FROM attendance WHERE date BETWEEN ? AND ? GROUP BY status");
$stmt_summary->bind_param("ss", $start_date, $end_date);
$stmt_summary->execute();
$result_summary = $stmt_summary->get_result();
while ($row = $result_summary->fetch_assoc()) {
    if (isset($summary_data[$row['status']])) {
        $summary_data[$row['status']] = $row['jumlah'];
    }
}
$stmt_summary->close();
$total_kehadiran = array_sum($summary_data);

// 2. Kueri untuk Tabel Detail
$attendance_report = [];
$att_stmt = $conn->prepare("SELECT a.date, s.name as student_name, c.name as class_name, sub.name as subject_name, a.status 
                            FROM attendance a
                            JOIN students s ON a.student_id = s.id
                            JOIN classes c ON s.class_id = c.id
                            JOIN subjects sub ON a.subject_id = sub.id
                            WHERE a.date BETWEEN ? AND ? 
                            ORDER BY a.date DESC, s.name ASC");
$att_stmt->bind_param("ss", $start_date, $end_date);
$att_stmt->execute();
$result_detail = $att_stmt->get_result();
while ($row = $result_detail->fetch_assoc()) {
    $attendance_report[] = $row;
}
$att_stmt->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Kehadiran Siswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Salin semua CSS dari file sebelumnya */
        :root {
            --sidebar-bg: #111827;
            --sidebar-link-color: #9ca3af;
            --sidebar-link-hover-bg: #374151;
            --sidebar-link-active-bg: #4f46e5;
            --main-bg: #f1f5f9;
            --panel-bg: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--main-bg);
            color: var(--text-primary);
            margin: 0;
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
        }

        .sidebar .sidebar-title p {
            font-size: 0.8em;
            color: var(--sidebar-link-color);
            margin-top: 4px;
        }

        .sidebar-menu {
            flex-grow: 1;
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

        .sidebar-menu a.active {
            background-color: var(--sidebar-link-active-bg);
            color: #fff;
        }

        .sidebar-menu a i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }

        .main-wrapper {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 30px;
        }

        .page-header h1 {
            font-size: 1.8em;
            font-weight: 600;
            margin: 0 0 20px 0;
        }

        .card {
            background-color: var(--panel-bg);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .card h2 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.25em;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-item {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-item .value {
            font-size: 2em;
            font-weight: 700;
        }

        .stat-item .label {
            font-size: 0.9em;
            color: var(--text-secondary);
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            max-width: 500px;
            margin: auto;
            margin-bottom: 30px;
        }

        .filter-container {
            margin-bottom: 20px;
        }

        .filter-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            margin-bottom: 10px;
        }

        .form-group {
            flex: 1;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group button {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
        }

        .form-group button {
            background-color: var(--sidebar-link-active-bg);
            color: #fff;
            cursor: pointer;
        }

        .quick-filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .quick-filters .btn-filter {
            background-color: #eef2ff;
            color: var(--sidebar-link-active-bg);
            border: 1px solid var(--sidebar-link-active-bg);
            padding: 5px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.8em;
        }

        .quick-filters .btn-filter:hover {
            background-color: #e0e7ff;
        }

        .data-range-info {
            font-size: 0.8em;
            color: var(--text-secondary);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
            text-align: left;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="images/Logo SMK.png" class="logo" alt="Logo">
                <div class="sidebar-title">
                    <h2>SIMANDAKA</h2>
                    <p>SMK Negeri 2 Bengkalis</p>
                </div>
            </div>
            <nav class="sidebar-menu">
                <a href="kepala_sekolah_dashboard.php"><i class="fas fa-home"></i> Dasbor</a>
                <a href="laporan_kehadiran.php" class="active"><i class="fas fa-calendar-check"></i> Laporan Kehadiran</a>
                <a href="laporan_keuangan.php"><i class="fas fa-wallet"></i> Laporan Keuangan</a>
                <a href="laporan_nilai.php"><i class="fas fa-graduation-cap"></i> Laporan Nilai</a>
                <a href="laporan_guru.php"><i class="fas fa-chalkboard-teacher"></i> Laporan Data Guru</a>
            </nav>
        </aside>
        <div class="main-wrapper">
            <div class="page-header">
                <h1>Laporan Kehadiran Siswa</h1>
            </div>

            <div class="card">
                <h2>Filter Laporan</h2>
                <div class="filter-container">
                    <div class="quick-filters">
                        <button class="btn-filter" data-range="today">Hari Ini</button>
                        <button class="btn-filter" data-range="this_week">Minggu Ini</button>
                        <button class="btn-filter" data-range="this_month">Bulan Ini</button>
                        <button class="btn-filter" data-range="last_month">Bulan Lalu</button>
                        <button class="btn-filter" data-range="all">Semua Waktu</button>
                    </div>
                    <p class="data-range-info">
                        <i class="fas fa-info-circle"></i> Rentang data tersedia:
                        <strong><?= date("d M Y", strtotime($min_date)) ?></strong> s/d <strong><?= date("d M Y", strtotime($max_date)) ?></strong>
                    </p>
                </div>
                <form method="GET" class="filter-form" id="filterForm">
                    <div class="form-group"><label>Dari Tanggal</label><input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($start_date) ?>"></div>
                    <div class="form-group"><label>Sampai Tanggal</label><input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($end_date) ?>"></div>
                    <div class="form-group"><button type="submit">Tampilkan</button></div>
                </form>
            </div>

            <div class="stats-grid">
                <div class="stat-item" style="border-left: 5px solid #28a745;">
                    <div class="value"><?= $summary_data['hadir'] ?></div>
                    <div class="label">Hadir</div>
                </div>
                <div class="stat-item" style="border-left: 5px solid #17a2b8;">
                    <div class="value"><?= $summary_data['izin'] ?></div>
                    <div class="label">Izin</div>
                </div>
                <div class="stat-item" style="border-left: 5px solid #ffc107;">
                    <div class="value"><?= $summary_data['sakit'] ?></div>
                    <div class="label">Sakit</div>
                </div>
                <div class="stat-item" style="border-left: 5px solid #dc3545;">
                    <div class="value"><?= $summary_data['alfa'] ?></div>
                    <div class="label">Alfa</div>
                </div>
            </div>
            <div class="card">
                <h2>Grafik Persentase Kehadiran</h2>
                <div class="chart-container"><canvas id="attendanceChart"></canvas></div>
            </div>
            <div class="card">
                <h2>Detail Kehadiran</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Mata Pelajaran</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($attendance_report)): foreach ($attendance_report as $row): ?>
                                <tr>
                                    <td><?= date("d M Y", strtotime($row['date'])) ?></td>
                                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                                    <td><?= htmlspecialchars($row['class_name']) ?></td>
                                    <td><?= htmlspecialchars($row['subject_name']) ?></td>
                                    <td><?= htmlspecialchars($row['status']) ?></td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center;">Tidak ada data kehadiran.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        // Fungsi untuk memformat tanggal ke YYYY-MM-DD
        function formatDate(date) {
            let d = new Date(date),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;

            return [year, month, day].join('-');
        }

        document.querySelector('.quick-filters').addEventListener('click', function(e) {
            if (e.target && e.target.matches('button.btn-filter')) {
                const range = e.target.dataset.range;
                const startDateInput = document.getElementById('start_date');
                const endDateInput = document.getElementById('end_date');
                const today = new Date();
                let startDate, endDate;

                switch (range) {
                    case 'today':
                        startDate = today;
                        endDate = today;
                        break;
                    case 'this_week':
                        startDate = new Date(today.setDate(today.getDate() - today.getDay() + (today.getDay() === 0 ? -6 : 1))); // Senin
                        endDate = new Date(); // Hari ini
                        break;
                    case 'this_month':
                        startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                        endDate = new Date();
                        break;
                    case 'last_month':
                        startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                        endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                        break;
                    case 'all':
                        startDate = new Date('<?= $min_date ?>');
                        endDate = new Date('<?= $max_date ?>');
                        break;
                }

                startDateInput.value = formatDate(startDate);
                endDateInput.value = formatDate(endDate);

                // Otomatis submit form
                document.getElementById('filterForm').submit();
            }
        });

        // Inisialisasi Chart.js (tidak berubah)
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const attendanceChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Hadir', 'Izin', 'Sakit', 'Alfa'],
                datasets: [{
                    label: 'Jumlah',
                    data: [<?= $summary_data['hadir'] ?>, <?= $summary_data['izin'] ?>, <?= $summary_data['sakit'] ?>, <?= $summary_data['alfa'] ?>],
                    backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545'],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.raw;
                                let total = context.chart.getDatasetMeta(0).total;
                                let percentage = total > 0 ? (value / total * 100).toFixed(1) + '%' : '0%';
                                return `${label}: ${value} (${percentage})`;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>