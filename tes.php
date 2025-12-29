<?php
// FILE: absensi_kelas.php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header('Location: index.php');
    exit();
}
include 'config.php';

// Fetch daftar kelas
$classes = $conn->query("SELECT * FROM classes");

// Fetch daftar mata pelajaran berdasarkan kelas yang dipilih
$subjects = [];
if (isset($_POST['class_id'])) {
    $class_id = $_POST['class_id'];
    $subjects = $conn->query("SELECT s.id, s.name 
                              FROM subjects s
                              JOIN class_subjects cs ON s.id = cs.subject_id
                              WHERE cs.class_id = '$class_id'");
}

// Jika guru membuka absensi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['open_attendance'])) {
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];
    $date = date('Y-m-d'); // Tanggal hari ini

    // Cek apakah absensi sudah dibuka
    $check_query = $conn->query("SELECT * FROM attendance_open 
                                 WHERE class_id = '$class_id' AND subject_id = '$subject_id' AND date = '$date'");

    if ($check_query->num_rows == 0) {
        // Simpan ke tabel attendance_open
        $insert_query = "INSERT INTO attendance_open (class_id, subject_id, date) VALUES ('$class_id', '$subject_id', '$date')";
        if ($conn->query($insert_query)) {
            echo "<script>alert('Absensi berhasil dibuka!'); window.location.href='absensi_kelas.php';</script>";
        } else {
            echo "<script>alert('Gagal membuka absensi: " . $conn->error . "');</script>";
        }
    } else {
        echo "<script>alert('Absensi untuk kelas dan mata pelajaran ini sudah dibuka hari ini.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buka Absensi Kelas</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            height: 100vh;
            background: linear-gradient(120deg, #16a085, #27ae60);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .dashboard-container {
            background: rgba(0, 0, 0, 0.8);
            padding: 20px 40px;
            border-radius: 10px;
            box-shadow: 0px 15px 25px rgba(0, 0, 0, 0.5);
            width: 400px;
            text-align: center;
        }

        .dashboard-container h2 {
            margin-bottom: 20px;
        }

        .menu-btn {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            background: #16a085;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            font-size: 16px;
            transition: 0.3s;
        }

        .menu-btn:hover {
            background: #1abc9c;
        }

        .back-btn,
        .logout-btn {
            width: 150px;
            padding: 10px;
            background: #27ae60;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            font-size: 14px;
            transition: 0.3s;
            position: absolute;
        }

        .back-btn {
            bottom: 20px;
            left: 20px;
        }

        .logout-btn {
            bottom: 20px;
            right: 20px;
        }

        .back-btn:hover,
        .logout-btn:hover {
            background: #2ecc71;
        }
    </style>
</head>

<body>
    <button class="back-btn" onclick="window.location.href='guru_dashboard.php'">Kembali</button>
    <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>

    <div class="dashboard-container">
        <h2>Buka Absensi Kelas</h2>
        <form method="POST">
            <label for="class_id">Pilih Kelas:</label>
            <select name="class_id" id="class_id" required onchange="this.form.submit()">
                <option value="">-- Pilih Kelas --</option>
                <?php while ($class = $classes->fetch_assoc()) : ?>
                    <option value="<?= $class['id'] ?>" <?= isset($_POST['class_id']) && $_POST['class_id'] == $class['id'] ? 'selected' : '' ?>>
                        <?= $class['name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <?php if (isset($_POST['class_id'])) : ?>
            <form method="POST">
                <input type="hidden" name="class_id" value="<?= $_POST['class_id'] ?>">

                <label for="subject_id">Pilih Mata Pelajaran:</label>
                <select name="subject_id" id="subject_id" required>
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    <?php while ($subject = $subjects->fetch_assoc()) : ?>
                        <option value="<?= $subject['id'] ?>"><?= $subject['name'] ?></option>
                    <?php endwhile; ?>
                </select>

                <button class="menu-btn" type="submit" name="open_attendance">Buka Absensi</button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>