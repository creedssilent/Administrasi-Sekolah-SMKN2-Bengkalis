<?php
session_start();
include 'config.php';

// Validasi login siswa
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Ambil data siswa dan kelas
$query = "SELECT s.*, c.name AS class_name 
          FROM students s 
          JOIN classes c ON s.class_id = c.id 
          WHERE s.username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$siswa = $result->fetch_assoc();

if (!$siswa) {
    echo "Data siswa tidak ditemukan.";
    exit();
}

// Ambil semua mata pelajaran
$subjects = [];
$subjects_query = $conn->query("SELECT * FROM subjects ORDER BY id ASC");
while ($row = $subjects_query->fetch_assoc()) {
    $subjects[] = $row;
}

// Ambil nilai siswa dari tabel grades
$grades_stmt = $conn->prepare("SELECT * FROM grades WHERE student_id = ?");
$grades_stmt->bind_param("i", $siswa['id']);
$grades_stmt->execute();
$grades_result = $grades_stmt->get_result();

$grades = [];
while ($g = $grades_result->fetch_assoc()) {
    $grades[$g['subject_id']] = [
        'nilai' => $g['grade'],
        'deskripsi' => ''
    ];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rapor 4</title>
    <style>
        body {
            font-family: 'Gill Sans Ultra Bold Condensed', sans-serif;
            margin: 0;
            padding: 10mm 30mm 30mm 30mm;
            box-sizing: border-box;
            width: 210mm;
            height: 297mm;
            position: relative;
            font-size: 12px;
        }

        .header {
            width: 100%;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .header .info {
            display: flex;
            justify-content: space-between;
        }

        .header .info .column {
            flex-basis: 48%;
        }

        .header .info .column:last-child {
            margin-left: 50px;
        }

        .header .info .row {
            display: flex;
            align-items: baseline;
            margin-bottom: 10px;
        }

        .header .info .row .label {
            flex-basis: 150px;
            text-align: left;
            white-space: nowrap;
            position: relative;
            padding-right: 10px;
        }

        .header .info .row .label::after {
            content: ":";
            position: absolute;
            right: 10px;
        }

        .header .info .row .value {
            flex: 1;
            text-align: left;
            white-space: nowrap;
        }

        .header .info .column:first-child .row .label {
            flex-basis: 110px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            font-size: 12px;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        th:nth-child(1),
        td:nth-child(1) {
            width: 30px;
        }

        th:nth-child(2),
        td:nth-child(2) {
            width: 40%;
        }

        th:nth-child(3),
        td:nth-child(3) {
            width: 15%;
            text-align: center;
        }

        th:nth-child(4),
        td:nth-child(4) {
            width: 45%;
        }

        .footer {
            position: absolute;
            bottom: -5mm;
            left: 10mm;
            right: 10mm;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="info">
            <div class="column">
                <div class="row">
                    <div class="label">Nama Peserta Didik</div>
                    <div class="value"><?= htmlspecialchars($siswa['name']) ?></div>
                </div>
                <div class="row">
                    <div class="label">Nomor Induk/NISN</div>
                    <div class="value"><?= $siswa['id'] ?> / <?= htmlspecialchars($siswa['nisn']) ?></div>
                </div>
                <div class="row">
                    <div class="label">Sekolah</div>
                    <div class="value">SMKN 2 BENGKALIS</div>
                </div>
                <div class="row">
                    <div class="label">Alamat</div>
                    <div class="value">ASSALAM</div>
                </div>
            </div>
            <div class="column">
                <div class="row">
                    <div class="label">Kelas</div>
                    <div class="value"><?= htmlspecialchars($siswa['class_name']) ?></div>
                </div>
                <div class="row">
                    <div class="label">Fase</div>
                    <div class="value">E</div>
                </div>
                <div class="row">
                    <div class="label">Semester</div>
                    <div class="value">Ganjil</div>
                </div>
                <div class="row">
                    <div class="label">Tahun Pelajaran</div>
                    <div class="value">2024/2025</div>
                </div>
            </div>
        </div>
    </div>

    <h2>A. Nilai Akademik</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Mata Pelajaran</th>
                <th>Nilai Akhir</th>
                <th>Capaian Kompetensi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="4"><strong>A. Kelompok Mata Pelajaran Umum</strong></td>
            </tr>
            <?php
            $no = 1;
            foreach ($subjects as $subject) {
                if ($subject['kategori'] === 'A') {
                    $id = $subject['id'];
                    $nilai = isset($grades[$id]['nilai']) ? $grades[$id]['nilai'] : 0;
                    if ($nilai >= 90) {
                        $deskripsi = "Sangat baik, pertahankan prestasi ini.";
                    } elseif ($nilai >= 80) {
                        $deskripsi = "Baik, terus tingkatkan.";
                    } elseif ($nilai >= 70) {
                        $deskripsi = "Cukup, perlu lebih giat belajar.";
                    } elseif ($nilai >= 50) {
                        $deskripsi = "Kurang, perlu pendampingan belajar lebih lanjut.";
                    } else {
                        $deskripsi = "Sangat kurang, harus belajar lebih keras dan meminta bantuan.";
                    }
                    echo "<tr>
                            <td>{$no}</td>
                            <td>" . htmlspecialchars($subject['name']) . "</td>
                            <td>{$nilai}</td>
                            <td>{$deskripsi}</td>
                        </tr>";
                    $no++;
                }
            }
            ?>

            <tr>
                <td colspan="4"><strong>B. Kelompok Mata Pelajaran Kejuruan</strong></td>
            </tr>
            <?php
            foreach ($subjects as $subject) {
                if ($subject['kategori'] === 'B') {
                    $id = $subject['id'];
                    $nilai = isset($grades[$id]['nilai']) ? $grades[$id]['nilai'] : 0;
                    if ($nilai >= 90) {
                        $deskripsi = "Sangat baik, pertahankan prestasi ini.";
                    } elseif ($nilai >= 80) {
                        $deskripsi = "Baik, terus tingkatkan.";
                    } elseif ($nilai >= 70) {
                        $deskripsi = "Cukup, perlu lebih giat belajar.";
                    } elseif ($nilai >= 50) {
                        $deskripsi = "Kurang, perlu pendampingan belajar lebih lanjut.";
                    } else {
                        $deskripsi = "Sangat kurang, harus belajar lebih keras dan meminta bantuan.";
                    }
                    echo "<tr>
                            <td>{$no}</td>
                            <td>" . htmlspecialchars($subject['name']) . "</td>
                            <td>{$nilai}</td>
                            <td>{$deskripsi}</td>
                        </tr>";
                    $no++;
                }
            }
            ?>
        </tbody>
    </table>

    <div class="footer">
        <span><?= htmlspecialchars($siswa['name']) ?> - <?= htmlspecialchars($siswa['class_name']) ?></span>
        <span>3</span>
        <span>Diterbitkan dari e-rapor SMK x.7.0.0</span>
    </div>
</body>

</html>