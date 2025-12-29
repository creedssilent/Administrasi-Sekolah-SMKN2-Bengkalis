<?php
session_start();
include 'config.php';

// Validasi role siswa
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Ambil data siswa
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

// Ambil data ketidakhadiran
$absen = [
    'sakit' => 0,
    'izin' => 0,
    'alfa' => 0
];

$attendance_stmt = $conn->prepare("
    SELECT status, COUNT(*) as jumlah 
    FROM attendance 
    WHERE student_id = ? AND status IN ('sakit', 'izin', 'alfa')
    GROUP BY status
");
$attendance_stmt->bind_param("i", $siswa['id']);
$attendance_stmt->execute();
$attendance_result = $attendance_stmt->get_result();

while ($row = $attendance_result->fetch_assoc()) {
    $status = strtolower($row['status']);
    if (isset($absen[$status])) {
        $absen[$status] = $row['jumlah'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rapor Peserta Didik</title>
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

        .content {
            line-height: 1.5;
        }

        .info-row {
            position: relative;
            padding-left: 150px;
            margin-bottom: 5px;
        }

        .info-row .label {
            position: absolute;
            left: 0;
            width: 140px;
            text-align: left;
            font-weight: bold;
        }

        .info-row .value {
            margin-left: 10px;
        }

        .info-row .label::after {
            content: ":";
            position: absolute;
            right: -20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .signature-section {
            margin-top: 50px;
        }

        .signature-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .signature {
            text-align: left;
            width: 45%;
        }

        .signature p {
            margin: 5px 0;
        }

        .kepala-sekolah {
            text-align: center;
            margin-top: 50px;
            line-height: 1.4;
        }

        .signature.wali-kelas {
            margin-left: 40%;
        }

        .signature p.nama,
        .kepala-sekolah p.nama {
            font-weight: bold;
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
    <div class="content">
        <div class="info-row">
            <span class="label">Nama Peserta Didik</span>
            <span class="value"><?= htmlspecialchars($siswa['name']) ?></span>
        </div>
        <div class="info-row">
            <span class="label">Nomor Induk/NISN</span>
            <span class="value"><?= $siswa['id'] ?> / <?= htmlspecialchars($siswa['nisn']) ?></span>
        </div>
        <div class="info-row">
            <span class="label">Kelas</span>
            <span class="value"><?= htmlspecialchars($siswa['class_name']) ?></span>
        </div>
        <div class="info-row">
            <span class="label">Tahun Pelajaran</span>
            <span class="value">2024/2025</span>
        </div>
        <div class="info-row">
            <span class="label">Semester</span>
            <span class="value">Ganjil</span>
        </div>

        <h3>B. Ekstrakurikuler</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kegiatan Ekstrakurikuler</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>[Nama Kegiatan]</td>
                    <td>[Keterangan]</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>[Nama Kegiatan]</td>
                    <td>[Keterangan]</td>
                </tr>
            </tbody>
        </table>

        <h3>C. Ketidakhadiran</h3>
        <table>
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Jumlah Hari</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Sakit</td>
                    <td><?= $absen['sakit'] ?> hari</td>
                </tr>
                <tr>
                    <td>Izin</td>
                    <td><?= $absen['izin'] ?> hari</td>
                </tr>
                <tr>
                    <td>Tanpa Keterangan</td>
                    <td><?= $absen['alfa'] ?> hari</td>
                </tr>
            </tbody>
        </table>

        <div class="signature-section">
            <div class="signature-container">
                <div class="signature">
                    <p>Orang Tua/Wali</p><br /><br /><br />
                    <p>.....................................................</p><br />
                    <p>(Tanda Tangan)</p>
                </div>
                <div class="signature wali-kelas">
                    <p>Bengkalis, 20 Desember 2024</p>
                    <p>Wali Kelas</p><br /><br /><br />
                    <p class="nama">RAMZINUR EFENDI, S. E. I.</p>
                    <p>NIP. 198402152023211009</p>
                </div>
            </div>

            <div class="kepala-sekolah">
                <p>Kepala Sekolah</p><br /><br /><br />
                <p class="nama">JEFRI, S.Pd.I.</p>
                <p>NIP. 197503202008011009</p>
            </div>
        </div>

        <div class="footer">
            <span><?= htmlspecialchars($siswa['name']) ?> - <?= htmlspecialchars($siswa['class_name']) ?></span>
            <span>4</span>
            <span>Diterbitkan dari e-rapor SMK v.7.0.6</span>
        </div>
    </div>
</body>

</html>