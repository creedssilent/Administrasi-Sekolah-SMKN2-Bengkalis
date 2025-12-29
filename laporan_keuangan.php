<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala_sekolah') {
    header('Location: index.php');
    exit();
}
include 'config.php';

// Filter Tanggal
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// 1. Kueri Ringkasan
$income_stmt = $conn->prepare("SELECT SUM(amount) as total FROM payments WHERE date BETWEEN ? AND ?");
$income_stmt->bind_param("ss", $start_date, $end_date);
$income_stmt->execute();
$income = $income_stmt->get_result()->fetch_assoc()['total'] ?? 0;
$income_stmt->close();

$expense_stmt = $conn->prepare("SELECT SUM(amount) as total FROM organizations WHERE created_at BETWEEN ? AND ?");
$expense_stmt->bind_param("ss", $start_date, $end_date);
$expense_stmt->execute();
$expense = $expense_stmt->get_result()->fetch_assoc()['total'] ?? 0;
$expense_stmt->close();
$balance = $income - $expense;

// 2. Kueri Detail Pemasukan
$income_details = [];
$income_detail_stmt = $conn->prepare("SELECT p.date, s.name as student_name, a.title, p.amount FROM payments p JOIN students s ON p.student_id = s.id JOIN administrations a ON p.administrasi_id = a.id WHERE p.date BETWEEN ? AND ? ORDER BY p.date DESC");
$income_detail_stmt->bind_param("ss", $start_date, $end_date);
$income_detail_stmt->execute();
$result_income = $income_detail_stmt->get_result();
while ($row = $result_income->fetch_assoc()) {
    $income_details[] = $row;
}
$income_detail_stmt->close();

// 3. Kueri Detail Pengeluaran
$expense_details = [];
$expense_detail_stmt = $conn->prepare("SELECT created_at, title, description, amount FROM organizations WHERE created_at BETWEEN ? AND ? ORDER BY created_at DESC");
$expense_detail_stmt->bind_param("ss", $start_date, $end_date);
$expense_detail_stmt->execute();
$result_expense = $expense_detail_stmt->get_result();
while ($row = $result_expense->fetch_assoc()) {
    $expense_details[] = $row;
}
$expense_detail_stmt->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan Sekolah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Salin semua CSS dari file dashboard di sini untuk konsistensi */
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
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-item {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }

        .stat-item .value {
            font-size: 1.8em;
            font-weight: 700;
        }

        .stat-item .label {
            font-size: 0.9em;
            color: var(--text-secondary);
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

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
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
            font-size: 0.9em;
        }
    </style>
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
                <a href="laporan_keuangan.php" class="active"><i class="fas fa-wallet"></i> Laporan Keuangan</a>
            </nav>
        </aside>
        <div class="main-wrapper">
            <div class="page-header">
                <h1>Laporan Keuangan Sekolah</h1>
            </div>

            <div class="card">
                <h2>Filter Laporan</h2>
                <form method="GET" class="filter-form">
                    <div class="form-group"><label>Dari Tanggal</label><input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>"></div>
                    <div class="form-group"><label>Sampai Tanggal</label><input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>"></div>
                    <div class="form-group"><button type="submit">Tampilkan</button></div>
                </form>
            </div>

            <div class="stats-grid">
                <div class="stat-item" style="border-left: 5px solid #28a745;">
                    <div class="value" style="color:#28a745;"><?= 'Rp ' . number_format($income, 0, ',', '.') ?></div>
                    <div class="label">Total Pemasukan</div>
                </div>
                <div class="stat-item" style="border-left: 5px solid #dc3545;">
                    <div class="value" style="color:#dc3545;"><?= 'Rp ' . number_format($expense, 0, ',', '.') ?></div>
                    <div class="label">Total Pengeluaran</div>
                </div>
                <div class="stat-item" style="border-left: 5px solid #007bff;">
                    <div class="value" style="color:#007bff;"><?= 'Rp ' . number_format($balance, 0, ',', '.') ?></div>
                    <div class="label">Saldo Akhir</div>
                </div>
            </div>

            <div class="detail-grid">
                <div class="card">
                    <h2>Detail Pemasukan</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Siswa</th>
                                <th>Keterangan</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($income_details)): foreach ($income_details as $row): ?>
                                    <tr>
                                        <td><?= date("d M Y", strtotime($row['date'])) ?></td>
                                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                                        <td><?= htmlspecialchars($row['title']) ?></td>
                                        <td><?= 'Rp ' . number_format($row['amount'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="4" style="text-align:center;">Tidak ada pemasukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card">
                    <h2>Detail Pengeluaran</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($expense_details)): foreach ($expense_details as $row): ?>
                                    <tr>
                                        <td><?= date("d M Y", strtotime($row['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($row['title']) ?></td>
                                        <td><?= 'Rp ' . number_format($row['amount'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="3" style="text-align:center;">Tidak ada pengeluaran.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>