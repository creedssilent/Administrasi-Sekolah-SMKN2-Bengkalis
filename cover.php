<?php
session_start();
include 'config.php';

// Cek apakah user sudah login dan role-nya siswa
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header('Location: login.php');
    exit();
}

// Ambil username dari sesi
$username = $_SESSION['username'];

// Ambil data siswa berdasarkan username
$query = "SELECT s.name, s.nisn, c.name AS class_name 
          FROM students s
          JOIN classes c ON s.class_id = c.id
          WHERE s.username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

// Jika data tidak ditemukan, arahkan keluar
if (!$data) {
    echo "Data siswa tidak ditemukan.";
    exit();
}

$nama = $data['name'];
$nisn = $data['nisn'];
$kelas = $data['class_name'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rapor Peserta Didik</title>
    <style>
        body {
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            width: 210mm;
            height: 297mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .container {
            width: 190mm;
            height: 277mm;
            border: 5px solid #6a5acd;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-sizing: border-box;
            padding-top: 50px;
        }

        .logo {
            width: 120px;
            margin-bottom: 100px;
        }

        h1 {
            font-size: 16px;
            text-transform: uppercase;
            line-height: 0.5;
            margin: 0 0 15px 0;
            font-weight: normal;
        }

        .subtitle {
            font-size: 16px;
            margin: 0;
            text-transform: uppercase;
            text-align: center;
            line-height: 2;
            font-weight: normal;
            margin-bottom: 100px;
        }

        .label {
            margin: 30px 0 5px;
            font-weight: normal;
        }

        .box {
            border: 1px solid #000;
            padding: 10px 20px;
            display: inline-block;
            min-width: 300px;
            text-align: center;
            font-size: 16px;
            font-weight: normal;
            margin-bottom: 50px;
        }

        p {
            margin-top: 180px;
            text-align: center;
            line-height: 1.5;
            font-weight: normal;
        }

        .footer {
            position: absolute;
            bottom: 0px;
            left: 30px;
            right: 30px;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
            font-weight: normal;
        }
    </style>
</head>

<body>
    <div class="container">
        <img src="images/Logo SMK.png" alt="Logo SMK" class="logo">

        <h1>Rapor Peserta Didik</h1>
        <div class="subtitle">
            Sekolah Menengah Kejuruan<br>(SMK)
        </div>

        <div class="label">Nama Peserta Didik:</div>
        <div class="box"><?= htmlspecialchars($nama) ?></div>

        <div class="label">NISN:</div>
        <div class="box"><?= htmlspecialchars($nisn) ?></div>

        <p>KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET DAN TEKNOLOGI<br>REPUBLIK INDONESIA</p>
    </div>

    <div class="footer">
        <span><?= htmlspecialchars($nama) ?> - <?= htmlspecialchars($kelas) ?></span>
        <span>Dicetak dari v.7.0.6</span>
    </div>
</body>

</html>