<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala_sekolah') {
    header('Location: index.php');
    exit();
}
include 'config.php';

// Ambil data untuk filter dropdown
$classes = $conn->query("SELECT id, name FROM classes ORDER BY name");
$subjects = $conn->query("SELECT id, name FROM subjects ORDER BY name");

// Logika Filter
$selected_class = $_GET['class_id'] ?? '';
$selected_subject = $_GET['subject_id'] ?? '';

$where_clauses = [];
$params = [];
$types = '';

if (!empty($selected_class)) {
    $where_clauses[] = "c.id = ?";
    $params[] = $selected_class;
    $types .= 'i';
}
if (!empty($selected_subject)) {
    $where_clauses[] = "s.id = ?";
    $params[] = $selected_subject;
    $types .= 'i';
}

$where_sql = count($where_clauses) > 0 ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Kueri utama untuk mengambil data nilai
$sql = "SELECT c.name as class_name, s.name as subject_name, FORMAT(AVG(g.grade), 2) as average_grade, COUNT(g.id) as entry_count
        FROM grades g
        JOIN students st ON g.student_id = st.id
        JOIN classes c ON st.class_id = c.id
        JOIN subjects s ON g.subject_id = s.id
        $where_sql
        GROUP BY c.id, s.id
        ORDER BY c.name, s.name";

$stmt = $conn->prepare($sql);
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$grade_report = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Data untuk Grafik
$chart_labels = [];
$chart_data = [];
foreach ($grade_report as $data) {
    $chart_labels[] = $data['class_name'] . ' - ' . $data['subject_name'];
    $chart_data[] = $data['average_grade'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Nilai Akademik</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
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

        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }

        .filter-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group select,
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
                <a href="laporan_nilai.php" class="active"><i class="fas fa-graduation-cap"></i> Laporan Nilai</a>
            </nav>
        </aside>
        <div class="main-wrapper">
            <div class="page-header">
                <h1>Laporan Nilai Akademik</h1>
            </div>

            <div class="card">
                <h2>Filter Laporan</h2>
                <form method="GET" class="filter-form">
                    <div class="form-group">
                        <label>Kelas</label>
                        <select name="class_id">
                            <option value="">Semua Kelas</option>
                            <?php while ($class = $classes->fetch_assoc()): ?>
                                <option value="<?= $class['id'] ?>" <?= $selected_class == $class['id'] ? 'selected' : '' ?>><?= htmlspecialchars($class['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Mata Pelajaran</label>
                        <select name="subject_id">
                            <option value="">Semua Mapel</option>
                            <?php while ($subject = $subjects->fetch_assoc()): ?>
                                <option value="<?= $subject['id'] ?>" <?= $selected_subject == $subject['id'] ? 'selected' : '' ?>><?= htmlspecialchars($subject['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group"><button type="submit">Tampilkan</button></div>
                </form>
            </div>

            <div class="card">
                <h2>Grafik Rata-Rata Nilai</h2>
                <div class="chart-container">
                    <canvas id="gradesChart"></canvas>
                </div>
            </div>

            <div class="card">
                <h2>Detail Rata-Rata Nilai</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Kelas</th>
                            <th>Mata Pelajaran</th>
                            <th>Rata-Rata Nilai</th>
                            <th>Jumlah Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($grade_report)): foreach ($grade_report as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['class_name']) ?></td>
                                    <td><?= htmlspecialchars($row['subject_name']) ?></td>
                                    <td><?= htmlspecialchars($row['average_grade']) ?></td>
                                    <td><?= htmlspecialchars($row['entry_count']) ?></td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center;">Tidak ada data nilai yang sesuai dengan filter.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        const ctx = document.getElementById('gradesChart').getContext('2d');
        const gradesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [{
                    label: 'Rata-Rata Nilai',
                    data: <?= json_encode($chart_data) ?>,
                    backgroundColor: 'rgba(79, 70, 229, 0.8)',
                    borderColor: 'rgba(79, 70, 229, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>

</html>