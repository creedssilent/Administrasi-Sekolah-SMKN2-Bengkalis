<?php
// PERBAIKAN: Hanya memulai sesi jika belum ada yang aktif.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config.php';

// =====================================================================
// LANGKAH 1: PENGAMBILAN SEMUA DATA DI AWAL
// =====================================================================

// Validasi login dan peran siswa
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    // Jika file ini diakses langsung, arahkan ke login.
    // Jika di-include oleh generate_pdf.php, validasi sudah terjadi di sana.
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Ambil semua data siswa yang dibutuhkan untuk semua halaman
$query_siswa = "SELECT s.*, c.name AS class_name 
                FROM students s 
                JOIN classes c ON s.class_id = c.id 
                WHERE s.username = ?";
$stmt_siswa = $conn->prepare($query_siswa);
$stmt_siswa->bind_param("s", $username);
$stmt_siswa->execute();
$result_siswa = $stmt_siswa->get_result();
$siswa = $result_siswa->fetch_assoc();

if (!$siswa) {
    echo "Data siswa tidak ditemukan.";
    exit();
}

// Ambil semua data mata pelajaran (untuk halaman 3)
$subjects = [];
$subjects_query = $conn->query("SELECT * FROM subjects ORDER BY kategori ASC, id ASC");
while ($row = $subjects_query->fetch_assoc()) {
    $subjects[] = $row;
}

// Ambil semua nilai siswa (untuk halaman 3)
$grades_stmt = $conn->prepare("SELECT * FROM grades WHERE student_id = ?");
$grades_stmt->bind_param("i", $siswa['id']);
$grades_stmt->execute();
$grades_result = $grades_stmt->get_result();
$grades = [];
while ($g = $grades_result->fetch_assoc()) {
    $grades[$g['subject_id']] = ['nilai' => $g['grade']];
}

// Ambil data ketidakhadiran (untuk halaman 4)
$absen = ['sakit' => 0, 'izin' => 0, 'alfa' => 0];
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
    <title>Rapor Lengkap Peserta Didik - <?= htmlspecialchars($siswa['name']) ?></title>
    <style>
        /* CSS Umum untuk Page Break */
        @media print {
            .page-break {
                page-break-after: always;
            }
        }

        .page-container {
            width: 210mm;
            height: 297mm;
            box-sizing: border-box;
            position: relative;
        }

        /* ===================================================== */
        /* == STYLES UNTUK COVER (DARI cover.php) == */
        /* ===================================================== */
        .cover-body {
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .cover-body .container {
            width: 190mm;
            height: 277mm;
            border: 5px solid #6a5acd;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-sizing: border-box;
            padding-top: 50px;
        }

        .cover-body .logo {
            width: 120px;
            margin-bottom: 100px;
        }

        .cover-body h1 {
            font-size: 16px;
            text-transform: uppercase;
            line-height: 0.5;
            margin: 0 0 15px 0;
            font-weight: normal;
        }

        .cover-body .subtitle {
            font-size: 16px;
            margin: 0;
            text-transform: uppercase;
            text-align: center;
            line-height: 2;
            font-weight: normal;
            margin-bottom: 100px;
        }

        .cover-body .label {
            margin: 30px 0 5px;
            font-weight: normal;
        }

        .cover-body .box {
            border: 1px solid #000;
            padding: 10px 20px;
            display: inline-block;
            min-width: 300px;
            text-align: center;
            font-size: 16px;
            font-weight: normal;
            margin-bottom: 50px;
        }

        .cover-body p {
            margin-top: 180px;
            text-align: center;
            line-height: 1.5;
            font-weight: normal;
        }

        .cover-body .footer {
            position: absolute;
            bottom: 0px;
            left: 30px;
            right: 30px;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
            font-weight: normal;
        }

        /* ===================================================== */
        /* == STYLES UNTUK HALAMAN 1 (DARI halaman1.php) == */
        /* ===================================================== */
        .halaman1-body {
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
            padding: 0mm 30mm 30mm 30mm;
        }

        .halaman1-body .content h1 {
            text-align: center;
            font-size: 16px;
            line-height: 1.5;
            font-weight: normal;
            margin-bottom: 40px;
        }

        .halaman1-body .info {
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-size: 16px;
            line-height: 4;
        }

        .halaman1-body .info div {
            display: flex;
        }

        .halaman1-body .info .label {
            min-width: 50mm;
        }

        .halaman1-body .footer {
            position: absolute;
            bottom: -5mm;
            left: 10mm;
            right: 10mm;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
        }

        /* ===================================================== */
        /* == STYLES UNTUK HALAMAN 2 (DARI halaman2.php) == */
        /* ===================================================== */
        .halaman2-body {
            font-family: 'Gill Sans Ultra Bold Condensed', sans-serif;
            padding: 10mm 30mm 30mm 30mm;
            font-size: 12px;
        }

        .halaman2-body .title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 60px;
            font-size: 20px;
        }

        .halaman2-body .content {
            width: 100%;
            line-height: 1.8;
        }

        .halaman2-body .content table {
            width: 100%;
            border-collapse: collapse;
        }

        .halaman2-body .content td {
            vertical-align: top;
            padding-bottom: 5px;
        }

        .halaman2-body .content td:first-child {
            width: 40%;
            padding-right: 10px;
        }

        .halaman2-body .content td:nth-child(2) {
            width: 60%;
            padding-left: 20px;
            position: relative;
        }

        .halaman2-body .content td:nth-child(2)::before {
            content: ":";
            position: absolute;
            left: 0;
        }

        .halaman2-body .photo-box {
            width: 3cm;
            height: 4cm;
            border: 1px solid black;
            text-align: center;
            line-height: 4cm;
            position: absolute;
            top: 230mm;
            right: 130mm;
            overflow: hidden;
        }

        .halaman2-body .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .halaman2-body .signature-box {
            position: absolute;
            top: 230mm;
            left: 100mm;
        }

        .halaman2-body .footer {
            position: absolute;
            bottom: -5mm;
            left: 10mm;
            right: 10mm;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
        }

        /* ===================================================== */
        /* == STYLES UNTUK HALAMAN 3 (DARI halaman3.php) == */
        /* ===================================================== */
        .halaman3-body {
            font-family: 'Gill Sans Ultra Bold Condensed', sans-serif;
            padding: 10mm 30mm 30mm 30mm;
            font-size: 12px;
        }

        .halaman3-body .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .halaman3-body .header .info {
            display: flex;
            justify-content: space-between;
        }

        .halaman3-body .header .info .column {
            flex-basis: 48%;
        }

        .halaman3-body .header .info .column:last-child {
            margin-left: 50px;
        }

        .halaman3-body .header .info .row {
            display: flex;
            align-items: baseline;
            margin-bottom: 10px;
        }

        .halaman3-body .header .info .row .label {
            flex-basis: 150px;
            text-align: left;
            white-space: nowrap;
            position: relative;
            padding-right: 10px;
        }

        .halaman3-body .header .info .row .label::after {
            content: ":";
            position: absolute;
            right: 10px;
        }

        .halaman3-body .header .info .row .value {
            flex: 1;
            text-align: left;
            white-space: nowrap;
        }

        .halaman3-body .header .info .column:first-child .row .label {
            flex-basis: 110px;
        }

        .halaman3-body table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed;
        }

        .halaman3-body th,
        .halaman3-body td {
            border: 1px solid black;
            padding: 8px;
            font-size: 12px;
        }

        .halaman3-body th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .halaman3-body th:nth-child(1),
        .halaman3-body td:nth-child(1) {
            width: 30px;
        }

        .halaman3-body th:nth-child(2),
        .halaman3-body td:nth-child(2) {
            width: 40%;
        }

        .halaman3-body th:nth-child(3),
        .halaman3-body td:nth-child(3) {
            width: 15%;
            text-align: center;
        }

        .halaman3-body th:nth-child(4),
        .halaman3-body td:nth-child(4) {
            width: 45%;
        }

        .halaman3-body .footer {
            position: absolute;
            bottom: -5mm;
            left: 10mm;
            right: 10mm;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
        }

        /* ===================================================== */
        /* == STYLES UNTUK HALAMAN 4 (DARI halaman4.php) == */
        /* ===================================================== */
        .halaman4-body {
            font-family: 'Gill Sans Ultra Bold Condensed', sans-serif;
            padding: 10mm 30mm 30mm 30mm;
            font-size: 12px;
        }

        .halaman4-body .content {
            line-height: 1.5;
        }

        .halaman4-body .info-row {
            position: relative;
            padding-left: 150px;
            margin-bottom: 5px;
        }

        .halaman4-body .info-row .label {
            position: absolute;
            left: 0;
            width: 140px;
            text-align: left;
            font-weight: bold;
        }

        .halaman4-body .info-row .value {
            margin-left: 10px;
        }

        .halaman4-body .info-row .label::after {
            content: ":";
            position: absolute;
            right: -20px;
        }

        .halaman4-body table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .halaman4-body th,
        .halaman4-body td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        .halaman4-body th {
            background-color: #f2f2f2;
        }

        .halaman4-body .signature-section {
            margin-top: 50px;
        }

        .halaman4-body .signature-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .halaman4-body .signature {
            text-align: left;
            width: 45%;
        }

        .halaman4-body .signature p {
            margin: 5px 0;
        }

        .halaman4-body .kepala-sekolah {
            text-align: center;
            margin-top: 50px;
            line-height: 1.4;
        }

        .halaman4-body .signature.wali-kelas {
            margin-left: 40%;
        }

        .halaman4-body .signature p.nama,
        .halaman4-body .kepala-sekolah p.nama {
            font-weight: bold;
        }

        .halaman4-body .footer {
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

    <div class="page-container page-break cover-body">
        <div class="container">
            <img src="images/Logo SMK.png" alt="Logo SMK" class="logo">
            <h1>Rapor Peserta Didik</h1>
            <div class="subtitle">
                Sekolah Menengah Kejuruan<br>(SMK)
            </div>
            <div class="label">Nama Peserta Didik:</div>
            <div class="box"><?= htmlspecialchars($siswa['name']) ?></div>
            <div class="label">NISN:</div>
            <div class="box"><?= htmlspecialchars($siswa['nisn']) ?></div>
            <p>KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET DAN TEKNOLOGI<br>REPUBLIK INDONESIA</p>
        </div>
        <div class="footer">
            <span><?= htmlspecialchars($siswa['name']) ?> - <?= htmlspecialchars($siswa['class_name']) ?></span>
            <span>1</span>
            <span>Dicetak dari v.7.0.6</span>
        </div>
    </div>

    <div class="page-container page-break halaman1-body">
        <div class="content">
            <h1>RAPOR PESERTA DIDIK<br>SEKOLAH MENENGAH KEJURUAN<br>(SMK)</h1>
            <div class="info">
                <div><span class="label">Nama Sekolah</span> : SMKN 2 BENGKALIS</div>
                <div><span class="label">NPSN / NSS</span> : 10495329 / 341090201002</div>
                <div><span class="label">Alamat</span> : ASSALAM<br>&nbsp;Kode Pos 28751 Telp. 076621952</div>
                <div><span class="label">Kelurahan</span> : Kelapa Pati</div>
                <div><span class="label">Kecamatan</span> : Bengkalis</div>
                <div><span class="label">Kabupaten/Kota</span> : Bengkalis</div>
                <div><span class="label">Provinsi</span> : Riau</div>
                <div><span class="label">Website</span> : http://smkn2bengkalis.sch.id</div>
                <div><span class="label">Email</span> : smkn2bengkalis@yahoo.co.id</div>
            </div>
        </div>
        <div class="footer">
            <span><?= htmlspecialchars($siswa['name']) ?> - <?= htmlspecialchars($siswa['class_name']) ?></span>
            <span>2</span>
            <span>Dicetak dari v.7.0.6</span>
        </div>
    </div>

    <div class="page-container page-break halaman2-body">
        <div class="container">
            <div class="title">KETERANGAN TENTANG DIRI PESERTA DIDIK</div>
            <div class="content">
                <table>
                    <tr>
                        <td>1. Nama Peserta Didik (Lengkap)</td>
                        <td><?= htmlspecialchars($siswa['name']) ?></td>
                    </tr>
                    <tr>
                        <td>2. Nomor Induk/NISN</td>
                        <td><?= $siswa['id'] ?> / <?= htmlspecialchars($siswa['nisn']) ?></td>
                    </tr>
                    <tr>
                        <td>3. Tempat, Tanggal Lahir</td>
                        <td><?= htmlspecialchars($siswa['tempat_lahir']) ?>, <?= date("d F Y", strtotime($siswa['tanggal_lahir'])) ?></td>
                    </tr>
                    <tr>
                        <td>4. Jenis Kelamin</td>
                        <td><?= $siswa['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                    </tr>
                    <tr>
                        <td>5. Agama</td>
                        <td><?= htmlspecialchars($siswa['agama']) ?></td>
                    </tr>
                    <tr>
                        <td>6. Status dalam Keluarga</td>
                        <td><?= htmlspecialchars($siswa['status_keluarga']) ?></td>
                    </tr>
                    <tr>
                        <td>7. Anak Ke</td>
                        <td><?= htmlspecialchars($siswa['anak_ke']) ?></td>
                    </tr>
                    <tr>
                        <td>8. Alamat Peserta Didik</td>
                        <td><?= htmlspecialchars($siswa['alamat']) ?></td>
                    </tr>
                    <tr>
                        <td>9. Nomor Telepon Rumah</td>
                        <td><?= htmlspecialchars($siswa['nomor_telepon']) ?></td>
                    </tr>
                    <tr>
                        <td>10. Sekolah Asal</td>
                        <td><?= htmlspecialchars($siswa['sekolah_asal']) ?></td>
                    </tr>
                    <tr>
                        <td>11. Diterima di sekolah ini</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Di kelas</td>
                        <td><?= htmlspecialchars($siswa['class_name']) ?></td>
                    </tr>
                    <tr>
                        <td>Pada tanggal</td>
                        <td><?= date("d F Y", strtotime($siswa['tanggal_diterima'])) ?></td>
                    </tr>
                    <tr>
                        <td>Nama Orang Tua</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>a. Ayah</td>
                        <td><?= htmlspecialchars($siswa['nama_ayah']) ?></td>
                    </tr>
                    <tr>
                        <td>b. Ibu</td>
                        <td><?= htmlspecialchars($siswa['nama_ibu']) ?></td>
                    </tr>
                    <tr>
                        <td>12. Alamat Orang Tua</td>
                        <td><?= htmlspecialchars($siswa['alamat_orang_tua']) ?></td>
                    </tr>
                    <tr>
                        <td>Nomor Telepon Rumah</td>
                        <td><?= htmlspecialchars($siswa['nomor_telepon']) ?></td>
                    </tr>
                    <tr>
                        <td>13. Pekerjaan Orang Tua</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>a. Ayah</td>
                        <td><?= htmlspecialchars($siswa['pekerjaan_ayah']) ?></td>
                    </tr>
                    <tr>
                        <td>b. Ibu</td>
                        <td><?= htmlspecialchars($siswa['pekerjaan_ibu']) ?></td>
                    </tr>
                    <tr>
                        <td>14. Nama Wali Peserta Didik</td>
                        <td><?= htmlspecialchars($siswa['nama_wali']) ?></td>
                    </tr>
                    <tr>
                        <td>15. Alamat Wali Peserta Didik</td>
                        <td><?= htmlspecialchars($siswa['alamat_wali']) ?></td>
                    </tr>
                    <tr>
                        <td>Nomor Telepon Rumah</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td>16. Pekerjaan Wali Peserta Didik</td>
                        <td><?= htmlspecialchars($siswa['pekerjaan_wali']) ?></td>
                    </tr>
                </table>

                <div class="photo-box">
                    <?php if (!empty($siswa['profile_picture'])) : ?>
                        <img src="uploads/profile_pictures/<?= htmlspecialchars($siswa['profile_picture']) ?>" alt="Foto Profil">
                    <?php else : ?>
                        3x4
                    <?php endif; ?>
                </div>
                <div class="signature-box">
                    Bengkalis, 01 Juli 2024<br>
                    Kepala Sekolah<br><br><br>
                    JEFRI, S.Pd.I.<br>
                    NIP. 197503202008011009
                </div>

                <div class="footer">
                    <span><?= htmlspecialchars($siswa['name']) ?> - <?= htmlspecialchars($siswa['class_name']) ?></span>
                    <span>3</span>
                    <span>Dicetak dari v.7.0.6</span>
                </div>
            </div>
        </div>
    </div>

    <div class="page-container page-break halaman3-body">
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
                        echo "<tr><td>{$no}</td><td>" . htmlspecialchars($subject['name']) . "</td><td>{$nilai}</td><td>{$deskripsi}</td></tr>";
                        $no++;
                    }
                }
                ?>
                <tr>
                    <td colspan="4"><strong>B. Kelompok Mata Pelajaran Kejuruan</strong></td>
                </tr>
                <?php
                $noB = $no;
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
                        echo "<tr><td>{$noB}</td><td>" . htmlspecialchars($subject['name']) . "</td><td>{$nilai}</td><td>{$deskripsi}</td></tr>";
                        $noB++;
                    }
                }
                ?>
            </tbody>
        </table>
        <div class="footer">
            <span><?= htmlspecialchars($siswa['name']) ?> - <?= htmlspecialchars($siswa['class_name']) ?></span>
            <span>4</span>
            <span>Diterbitkan dari e-rapor SMK x.7.0.0</span>
        </div>
    </div>

    <div class="page-container halaman4-body">
        <div class="content">
            <div class="info-row"><span class="label">Nama Peserta Didik</span><span class="value"><?= htmlspecialchars($siswa['name']) ?></span></div>
            <div class="info-row"><span class="label">Nomor Induk/NISN</span><span class="value"><?= $siswa['id'] ?> / <?= htmlspecialchars($siswa['nisn']) ?></span></div>
            <div class="info-row"><span class="label">Kelas</span><span class="value"><?= htmlspecialchars($siswa['class_name']) ?></span></div>
            <div class="info-row"><span class="label">Tahun Pelajaran</span><span class="value">2024/2025</span></div>
            <div class="info-row"><span class="label">Semester</span><span class="value">Ganjil</span></div>

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
                <span>5</span>
                <span>Diterbitkan dari e-rapor SMK v.7.0.6</span>
            </div>
        </div>
    </div>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>