<?php
// File ini HANYA untuk dipanggil oleh generate_pdf.php.
// Tidak untuk dibuka langsung di browser.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    exit("Akses ditolak.");
}
$username = $_SESSION['username'];
$query_siswa = "SELECT s.*, c.name AS class_name FROM students s JOIN classes c ON s.class_id = c.id WHERE s.username = ?";
$stmt_siswa = $conn->prepare($query_siswa);
$stmt_siswa->bind_param("s", $username);
$stmt_siswa->execute();
$result_siswa = $stmt_siswa->get_result();
$siswa = $result_siswa->fetch_assoc();
if (!$siswa) {
    exit("Data siswa tidak ditemukan.");
}

$subjects = [];
$subjects_query = $conn->query("SELECT * FROM subjects ORDER BY kategori ASC, id ASC");
while ($row = $subjects_query->fetch_assoc()) {
    $subjects[] = $row;
}

$grades_stmt = $conn->prepare("SELECT * FROM grades WHERE student_id = ?");
$grades_stmt->bind_param("i", $siswa['id']);
$grades_stmt->execute();
$grades_result = $grades_stmt->get_result();
$grades = [];
while ($g = $grades_result->fetch_assoc()) {
    $grades[$g['subject_id']] = ['nilai' => $g['grade']];
}

$absen = ['sakit' => 0, 'izin' => 0, 'alfa' => 0];
$attendance_stmt = $conn->prepare("SELECT status, COUNT(*) as jumlah FROM attendance WHERE student_id = ? AND status IN ('sakit', 'izin', 'alfa') GROUP BY status");
$attendance_stmt->bind_param("i", $siswa['id']);
$attendance_stmt->execute();
$attendance_result = $attendance_stmt->get_result();
while ($row = $attendance_result->fetch_assoc()) {
    $status = strtolower($row['status']);
    if (isset($absen[$status])) {
        $absen[$status] = $row['jumlah'];
    }
}

function get_image_base64_for_pdf($path)
{
    $full_path = $_SERVER['DOCUMENT_ROOT'] . '/2025/' . $path; // Sesuaikan '/2025/' jika perlu
    if (file_exists($full_path)) {
        $type = pathinfo($full_path, PATHINFO_EXTENSION);
        $data = file_get_contents($full_path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
    return '';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Template Rapor PDF</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
        }

        .page-break {
            page-break-after: always;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .text-center {
            text-align: center;
        }

        .text-bold {
            font-weight: bold;
        }

        .padding-5 {
            padding: 5px;
        }

        .border-solid {
            border: 1px solid black;
        }

        .no-border {
            border: none;
        }
    </style>
</head>

<body>
    <div style="width:100%; height:280mm; border: 5px solid black; padding:15px; box-sizing: border-box;" class="page-break">
        <table style="width: 100%; height: 100%;">
            <tr>
                <td class="text-center" style="height: 150px;"><img src="<?= get_image_base64_for_pdf('images/Logo SMK.png') ?>" style="width: 120px;"></td>
            </tr>
            <tr>
                <td class="text-center" style="height: 120px; vertical-align: top;">
                    <h2 style="font-size: 16pt; margin:0;">RAPOR PESERTA DIDIK<br>SEKOLAH MENENGAH KEJURUAN<br>(SMK)</h2>
                </td>
            </tr>
            <tr>
                <td class="text-center" style="vertical-align: middle;">
                    <p style="font-size: 14pt;">Nama Peserta Didik:</p>
                    <p style="font-size: 14pt; font-weight: bold; border: 1px solid black; padding: 10px; margin: 0 100px;"><?= htmlspecialchars($siswa['name']) ?></p>
                    <p style="font-size: 14pt; margin-top: 20px;">NISN:</p>
                    <p style="font-size: 14pt; font-weight: bold; border: 1px solid black; padding: 10px; margin: 0 100px;"><?= htmlspecialchars($siswa['nisn']) ?></p>
                </td>
            </tr>
            <tr>
                <td class="text-center" style="vertical-align: bottom; height: 180px;">
                    <p>KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET DAN TEKNOLOGI<br>REPUBLIK INDONESIA</p>
                </td>
            </tr>
        </table>
    </div>
    <div style="padding: 10mm 20mm;" class="page-break">
        <h2 style="text-align: center; line-height: 1.5;">RAPOR PESERTA DIDIK<br>SEKOLAH MENENGAH KEJURUAN<br>(SMK)</h2>
        <br><br>
        <table style="font-size: 12pt; line-height: 2;">
            <tr>
                <td style="width: 25%;">Nama Sekolah</td>
                <td style="width: 2%;">:</td>
                <td>SMKN 2 BENGKALIS</td>
            </tr>
            <tr>
                <td>NPSN / NSS</td>
                <td>:</td>
                <td>10495329 / 341090201002</td>
            </tr>
            <tr>
                <td style="vertical-align: top;">Alamat</td>
                <td style="vertical-align: top;">:</td>
                <td>ASSALAM<br>Kode Pos 28751 Telp. 076621952</td>
            </tr>
            <tr>
                <td>Kelurahan</td>
                <td>:</td>
                <td>Kelapa Pati</td>
            </tr>
            <tr>
                <td>Kecamatan</td>
                <td>:</td>
                <td>Bengkalis</td>
            </tr>
            <tr>
                <td>Kabupaten/Kota</td>
                <td>:</td>
                <td>Bengkalis</td>
            </tr>
            <tr>
                <td>Provinsi</td>
                <td>:</td>
                <td>Riau</td>
            </tr>
            <tr>
                <td>Website</td>
                <td>:</td>
                <td>http://smkn2bengkalis.sch.id</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>:</td>
                <td>smkn2bengkalis@yahoo.co.id</td>
            </tr>
        </table>
    </div>
    <div style="padding: 10mm 20mm;" class="page-break">
        <h2 class="text-center text-bold" style="font-size: 14pt;">KETERANGAN TENTANG DIRI PESERTA DIDIK</h2>
        <br>
        <table style="line-height: 1.8;">
            <tr>
                <td style="width: 5%;">1.</td>
                <td style="width: 40%;">Nama Peserta Didik (Lengkap)</td>
                <td style="width: 2%;">:</td>
                <td><?= htmlspecialchars($siswa['name']) ?></td>
            </tr>
            <tr>
                <td>2.</td>
                <td>Nomor Induk/NISN</td>
                <td>:</td>
                <td><?= $siswa['id'] ?> / <?= htmlspecialchars($siswa['nisn']) ?></td>
            </tr>
            <tr>
                <td>3.</td>
                <td>Tempat, Tanggal Lahir</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['tempat_lahir']) ?>, <?= date("d F Y", strtotime($siswa['tanggal_lahir'])) ?></td>
            </tr>
            <tr>
                <td>4.</td>
                <td>Jenis Kelamin</td>
                <td>:</td>
                <td><?= $siswa['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
            </tr>
            <tr>
                <td>5.</td>
                <td>Agama</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['agama']) ?></td>
            </tr>
            <tr>
                <td>6.</td>
                <td>Status dalam Keluarga</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['status_keluarga']) ?></td>
            </tr>
            <tr>
                <td>7.</td>
                <td>Anak Ke</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['anak_ke']) ?></td>
            </tr>
            <tr>
                <td>8.</td>
                <td>Alamat Peserta Didik</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['alamat']) ?></td>
            </tr>
            <tr>
                <td>9.</td>
                <td>Nomor Telepon Rumah</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['nomor_telepon']) ?></td>
            </tr>
            <tr>
                <td>10.</td>
                <td>Sekolah Asal</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['sekolah_asal']) ?></td>
            </tr>
            <tr>
                <td>11.</td>
                <td colspan="3">Diterima di sekolah ini</td>
            </tr>
            <tr>
                <td></td>
                <td style="padding-left:15px;">Di kelas</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['class_name']) ?></td>
            </tr>
            <tr>
                <td></td>
                <td style="padding-left:15px;">Pada tanggal</td>
                <td>:</td>
                <td><?= date("d F Y", strtotime($siswa['tanggal_diterima'])) ?></td>
            </tr>
            <tr>
                <td>12.</td>
                <td colspan="3">Nama Orang Tua</td>
            </tr>
            <tr>
                <td></td>
                <td style="padding-left:15px;">a. Ayah</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['nama_ayah']) ?></td>
            </tr>
            <tr>
                <td></td>
                <td style="padding-left:15px;">b. Ibu</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['nama_ibu']) ?></td>
            </tr>
            <tr>
                <td>13.</td>
                <td>Alamat Orang Tua</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['alamat_orang_tua']) ?></td>
            </tr>
            <tr>
                <td></td>
                <td>Nomor Telepon Rumah</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['nomor_telepon']) ?></td>
            </tr>
            <tr>
                <td>14.</td>
                <td colspan="3">Pekerjaan Orang Tua</td>
            </tr>
            <tr>
                <td></td>
                <td style="padding-left:15px;">a. Ayah</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['pekerjaan_ayah']) ?></td>
            </tr>
            <tr>
                <td></td>
                <td style="padding-left:15px;">b. Ibu</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['pekerjaan_ibu']) ?></td>
            </tr>
            <tr>
                <td>15.</td>
                <td>Nama Wali Peserta Didik</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['nama_wali']) ?></td>
            </tr>
            <tr>
                <td>16.</td>
                <td>Alamat Wali Peserta Didik</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['alamat_wali']) ?></td>
            </tr>
            <tr>
                <td></td>
                <td>Nomor Telepon Rumah</td>
                <td>:</td>
                <td>-</td>
            </tr>
            <tr>
                <td>17.</td>
                <td>Pekerjaan Wali Peserta Didik</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['pekerjaan_wali']) ?></td>
            </tr>
        </table>
        <br><br><br>
        <table>
            <tr>
                <td style="width: 50%;" class="text-center">
                    <div style="width: 3cm; height: 4cm; border: 1px solid black; margin: 0 auto; line-height: 4cm;">
                        <?php $foto_profil = get_image_base64_for_pdf('uploads/profile_pictures/' . $siswa['profile_picture']);
                        if ($foto_profil): ?>
                            <img src="<?= $foto_profil ?>" style="width:100%; height:100%; object-fit:cover;" alt="Foto">
                        <?php else: ?> Pas Foto 3x4 <?php endif; ?>
                    </div>
                </td>
                <td style="width: 50%;" class="text-center">
                    Bengkalis, 01 Juli 2024<br>Kepala Sekolah<br><br><br><br><br>
                    <span class="text-bold"><u>JEFRI, S.Pd.I.</u></span><br>
                    NIP. 197503202008011009
                </td>
            </tr>
        </table>
    </div>
    <div style="padding: 10mm 20mm;" class="page-break">
        <table class="no-border">
            <tr>
                <td style="width: 50%; vertical-align:top;">
                    <table class="no-border">
                        <tr>
                            <td style="width: 130px;">Nama Peserta Didik</td>
                            <td style="width:10px;">:</td>
                            <td><?= htmlspecialchars($siswa['name']) ?></td>
                        </tr>
                        <tr>
                            <td>Nomor Induk/NISN</td>
                            <td>:</td>
                            <td><?= $siswa['id'] ?> / <?= htmlspecialchars($siswa['nisn']) ?></td>
                        </tr>
                        <tr>
                            <td>Sekolah</td>
                            <td>:</td>
                            <td>SMKN 2 BENGKALIS</td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>:</td>
                            <td>ASSALAM</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%; vertical-align:top;">
                    <table class="no-border">
                        <tr>
                            <td style="width: 130px;">Kelas</td>
                            <td style="width:10px;">:</td>
                            <td><?= htmlspecialchars($siswa['class_name']) ?></td>
                        </tr>
                        <tr>
                            <td>Fase</td>
                            <td>:</td>
                            <td>E</td>
                        </tr>
                        <tr>
                            <td>Semester</td>
                            <td>:</td>
                            <td>Ganjil</td>
                        </tr>
                        <tr>
                            <td>Tahun Pelajaran</td>
                            <td>:</td>
                            <td>2024/2025</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <h3 class="text-bold">A. Nilai Akademik</h3>
        <table class="border-solid">
            <thead>
                <tr>
                    <th class="border-solid padding-5" style="width: 5%;">No</th>
                    <th class="border-solid padding-5" style="width: 35%;">Mata Pelajaran</th>
                    <th class="border-solid padding-5" style="width: 10%;">Nilai Akhir</th>
                    <th class="border-solid padding-5">Capaian Kompetensi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" class="text-bold padding-5 border-solid">A. Kelompok Mata Pelajaran Umum</td>
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
                        echo "<tr><td class='text-center border-solid padding-5'>{$no}</td><td class='border-solid padding-5'>" . htmlspecialchars($subject['name']) . "</td><td class='text-center border-solid padding-5'>{$nilai}</td><td class='border-solid padding-5'>{$deskripsi}</td></tr>";
                        $no++;
                    }
                }
                ?>
                <tr>
                    <td colspan="4" class="text-bold padding-5 border-solid">B. Kelompok Mata Pelajaran Kejuruan</td>
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
                        echo "<tr><td class='text-center border-solid padding-5'>{$noB}</td><td class='border-solid padding-5'>" . htmlspecialchars($subject['name']) . "</td><td class='text-center border-solid padding-5'>{$nilai}</td><td class='border-solid padding-5'>{$deskripsi}</td></tr>";
                        $noB++;
                    }
                }
                if ($noB == $no) {
                    echo '<tr><td colspan="4" class="text-center padding-5 border-solid">-</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <div style="padding: 10mm 20mm;">
        <h3 class="text-bold">B. Ekstrakurikuler</h3>
        <table class="border-solid">
            <thead>
                <tr>
                    <th class="border-solid padding-5" style="width: 5%;">No</th>
                    <th class="border-solid padding-5" style="width: 40%;">Kegiatan Ekstrakurikuler</th>
                    <th class="border-solid padding-5">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class='text-center border-solid padding-5'>1</td>
                    <td class='border-solid padding-5'>[Nama Kegiatan]</td>
                    <td class='border-solid padding-5'>[Keterangan]</td>
                </tr>
                <tr>
                    <td class='text-center border-solid padding-5'>2</td>
                    <td class='border-solid padding-5'>[Nama Kegiatan]</td>
                    <td class='border-solid padding-5'>[Keterangan]</td>
                </tr>
            </tbody>
        </table>
        <br>
        <h3 class="text-bold">C. Ketidakhadiran</h3>
        <table class="border-solid" style="width: 50%;">
            <tbody>
                <tr>
                    <td class="padding-5 border-solid" style="width: 60%;">Sakit</td>
                    <td class="padding-5 border-solid">: <?= $absen['sakit'] ?> hari</td>
                </tr>
                <tr>
                    <td class="padding-5 border-solid">Izin</td>
                    <td class="padding-5 border-solid">: <?= $absen['izin'] ?> hari</td>
                </tr>
                <tr>
                    <td class="padding-5 border-solid">Tanpa Keterangan</td>
                    <td class="padding-5 border-solid">: <?= $absen['alfa'] ?> hari</td>
                </tr>
            </tbody>
        </table>
        <br>
        <table class="no-border" style="margin-top: 40px;">
            <tr>
                <td style="width: 50%;" class="text-center">
                    Orang Tua/Wali,<br><br><br><br><br>
                    (.....................................)
                </td>
                <td style="width: 50%;" class="text-center">
                    Bengkalis, 20 Desember 2024<br>
                    Wali Kelas,<br><br><br><br><br>
                    <span class="text-bold"><u>RAMZINUR EFENDI, S. E. I.</u></span><br>
                    NIP. 198402152023211009
                </td>
            </tr>
            <tr>
                <td colspan="2" class="text-center" style="padding-top: 40px;">
                    Mengetahui,<br>
                    Kepala Sekolah,<br><br><br><br><br>
                    <span class="text-bold"><u>JEFRI, S.Pd.I.</u></span><br>
                    NIP. 197503202008011009
                </td>
            </tr>
        </table>
    </div>

</body>

</html>