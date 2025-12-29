<?php
session_start();
include 'config.php';

// Cek apakah login dan role siswa
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

$query = "SELECT s.*, c.name AS class_name 
          FROM students s
          JOIN classes c ON s.class_id = c.id
          WHERE s.username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    echo "Data siswa tidak ditemukan.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        .container {
            width: 100%;
        }

        .title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 60px;
            font-size: 20px;
        }

        .content {
            width: 100%;
            line-height: 1.8;
        }

        .content table {
            width: 100%;
            border-collapse: collapse;
        }

        .content td {
            vertical-align: top;
            padding-bottom: 5px;
        }

        .content td:first-child {
            width: 40%;
            padding-right: 10px;
        }

        .content td:nth-child(2) {
            width: 60%;
            padding-left: 20px;
            position: relative;
        }

        .content td:nth-child(2)::before {
            content: ":";
            position: absolute;
            left: 0;
        }

        .photo-box {
            width: 3cm;
            height: 4cm;
            border: 1px solid black;
            text-align: center;
            line-height: 4cm;
            position: absolute;
            top: 230mm;
            right: 130mm;
            overflow: hidden;
            /* Ditambahkan untuk memastikan gambar pas di dalam kotak */
        }

        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* Memastikan gambar menutupi area tanpa distorsi */
        }

        .signature-box {
            position: absolute;
            top: 230mm;
            left: 100mm;
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
    <div class="container">
        <div class="title">KETERANGAN TENTANG DIRI PESERTA DIDIK</div>
        <div class="content">
            <table>
                <tr>
                    <td>1. Nama Peserta Didik (Lengkap)</td>
                    <td><?= htmlspecialchars($data['name']) ?></td>
                </tr>
                <tr>
                    <td>2. Nomor Induk/NISN</td>
                    <td><?= $data['id'] ?> / <?= htmlspecialchars($data['nisn']) ?></td>
                </tr>
                <tr>
                    <td>3. Tempat, Tanggal Lahir</td>
                    <td><?= htmlspecialchars($data['tempat_lahir']) ?>, <?= date("d F Y", strtotime($data['tanggal_lahir'])) ?></td>
                </tr>
                <tr>
                    <td>4. Jenis Kelamin</td>
                    <td><?= $data['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                </tr>
                <tr>
                    <td>5. Agama</td>
                    <td><?= htmlspecialchars($data['agama']) ?></td>
                </tr>
                <tr>
                    <td>6. Status dalam Keluarga</td>
                    <td><?= htmlspecialchars($data['status_keluarga']) ?></td>
                </tr>
                <tr>
                    <td>7. Anak Ke</td>
                    <td><?= htmlspecialchars($data['anak_ke']) ?></td>
                </tr>
                <tr>
                    <td>8. Alamat Peserta Didik</td>
                    <td><?= htmlspecialchars($data['alamat']) ?></td>
                </tr>
                <tr>
                    <td>9. Nomor Telepon Rumah</td>
                    <td><?= htmlspecialchars($data['nomor_telepon']) ?></td>
                </tr>
                <tr>
                    <td>10. Sekolah Asal</td>
                    <td><?= htmlspecialchars($data['sekolah_asal']) ?></td>
                </tr>
                <tr>
                    <td>11. Diterima di sekolah ini</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Di kelas</td>
                    <td><?= htmlspecialchars($data['class_name']) ?></td>
                </tr>
                <tr>
                    <td>Pada tanggal</td>
                    <td><?= date("d F Y", strtotime($data['tanggal_diterima'])) ?></td>
                </tr>
                <tr>
                    <td>Nama Orang Tua</td>
                    <td></td>
                </tr>
                <tr>
                    <td>a. Ayah</td>
                    <td><?= htmlspecialchars($data['nama_ayah']) ?></td>
                </tr>
                <tr>
                    <td>b. Ibu</td>
                    <td><?= htmlspecialchars($data['nama_ibu']) ?></td>
                </tr>
                <tr>
                    <td>12. Alamat Orang Tua</td>
                    <td><?= htmlspecialchars($data['alamat_orang_tua']) ?></td>
                </tr>
                <tr>
                    <td>Nomor Telepon Rumah</td>
                    <td><?= htmlspecialchars($data['nomor_telepon']) ?></td>
                </tr>
                <tr>
                    <td>13. Pekerjaan Orang Tua</td>
                    <td></td>
                </tr>
                <tr>
                    <td>a. Ayah</td>
                    <td><?= htmlspecialchars($data['pekerjaan_ayah']) ?></td>
                </tr>
                <tr>
                    <td>b. Ibu</td>
                    <td><?= htmlspecialchars($data['pekerjaan_ibu']) ?></td>
                </tr>
                <tr>
                    <td>14. Nama Wali Peserta Didik</td>
                    <td><?= htmlspecialchars($data['nama_wali']) ?></td>
                </tr>
                <tr>
                    <td>15. Alamat Wali Peserta Didik</td>
                    <td><?= htmlspecialchars($data['alamat_wali']) ?></td>
                </tr>
                <tr>
                    <td>Nomor Telepon Rumah</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>16. Pekerjaan Wali Peserta Didik</td>
                    <td><?= htmlspecialchars($data['pekerjaan_wali']) ?></td>
                </tr>
            </table>

            <div class="photo-box">
                <?php if (!empty($data['profile_picture'])) : ?>
                    <img src="uploads/profile_pictures/<?= htmlspecialchars($data['profile_picture']) ?>" alt="Foto Profil">
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
                <span><?= htmlspecialchars($data['name']) ?> - <?= htmlspecialchars($data['class_name']) ?></span>
                <span>3</span>
                <span>Dicetak dari v.7.0.6</span>
            </div>
        </div>
    </div>
</body>

</html>