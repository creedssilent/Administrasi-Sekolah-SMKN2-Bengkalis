<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    exit('Akses ditolak.');
}
include 'config.php';

if (!isset($_GET['payment_id'])) {
    die("ID pembayaran tidak valid.");
}

$payment_id = (int)$_GET['payment_id'];
$username_session = $_SESSION['username'];

// 1. Ambil ID siswa yang benar dari tabel 'students' berdasarkan username di session
$student_profile_query = $conn->prepare("SELECT id FROM students WHERE username = ?");
$student_profile_query->bind_param("s", $username_session);
$student_profile_query->execute();
$student_profile_result = $student_profile_query->get_result();
if ($student_profile_result->num_rows === 0) {
    die("Gagal memverifikasi data siswa.");
}
$student_profile = $student_profile_result->fetch_assoc();
$student_id_profile = $student_profile['id'];
$student_profile_query->close();

// 2. Gunakan ID siswa yang benar untuk mengambil data kwitansi
$stmt = $conn->prepare("SELECT p.*, a.title AS jenis_pembayaran, s.name AS student_name, s.nisn, c.name as class_name
                        FROM payments p 
                        JOIN administrations a ON p.administrasi_id = a.id
                        JOIN students s ON p.student_id = s.id
                        JOIN classes c ON s.class_id = c.id
                        WHERE p.id = ? AND p.student_id = ?");
$stmt->bind_param("ii", $payment_id, $student_id_profile);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Kwitansi tidak ditemukan atau bukan milik Anda.");
}
$payment = $result->fetch_assoc();
$stmt->close();
?>
<style>
    .receipt-container {
        font-family: 'Poppins', sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        background-color: #fff;
        color: #333;
        padding: 35px;
        border-radius: 8px;
        max-width: 450px;
        margin: auto;
    }

    .receipt-header {
        text-align: center;
        border-bottom: 2px dashed #ccc;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }

    .receipt-header .school-logo {
        width: 60px;
        margin-bottom: 10px;
    }

    .receipt-header h3 {
        font-size: 1.5em;
        font-weight: 600;
        margin: 0;
        color: #0056b3;
    }

    .receipt-header p {
        font-size: 0.9em;
        color: #555;
        margin: 0;
    }

    .receipt-title {
        text-align: center;
        font-weight: 600;
        font-size: 1.2em;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 25px;
    }

    .receipt-details dl {
        margin: 0;
    }

    .receipt-details .detail-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
        font-size: 0.95em;
    }

    .receipt-details dt {
        color: #666;
    }

    .receipt-details dd {
        font-weight: 500;
        text-align: right;
    }

    /* --- PERUBAHAN CSS DI SINI --- */
    .receipt-total {
        margin-top: 25px;
        padding-top: 20px;
        border-top: 2px solid #333;
        text-align: center;
        /* Membuat semua teks di dalamnya menjadi tengah */
    }

    .receipt-total .detail-item {
        display: block;
        /* Mengubah dari flex menjadi block agar bisa di-center */
        font-size: 1em;
        font-weight: 500;
        color: #666;
        border-bottom: none;
        /* Hilangkan garis bawah di total */
    }

    .receipt-total dt {
        margin-bottom: 5px;
        /* Jarak antara label "Total Bayar" dan nominalnya */
    }

    .receipt-total dd {
        font-size: 1.9em;
        /* Membuat nominal lebih besar dan jadi fokus utama */
        font-weight: 700;
        color: #0056b3;
        text-align: center;
        /* Pastikan nominal juga di tengah */
    }

    /* --- AKHIR PERUBAHAN CSS --- */

    .receipt-footer {
        text-align: center;
        margin-top: 30px;
        font-size: 0.85em;
        color: #888;
    }
</style>
<div class="receipt-container">
    <div class="receipt-header">
        <img src="images/Logo SMK.png" alt="Logo Sekolah" class="school-logo">
        <h3>SMK NEGERI 2 BENGKALIS</h3>
        <p>Jl. Pramuka, Bengkalis, Riau</p>
    </div>
    <div class="receipt-title">Kwitansi Pembayaran</div>
    <div class="receipt-details">
        <dl>
            <div class="detail-item">
                <dt>No. Kwitansi</dt>
                <dd>KWT-<?= sprintf("%04d", htmlspecialchars($payment['id'])) ?></dd>
            </div>
            <div class="detail-item">
                <dt>Tanggal</dt>
                <dd><?= date("d F Y", strtotime($payment['date'])) ?></dd>
            </div>
            <div class="detail-item">
                <dt>Diterima dari</dt>
                <dd><?= htmlspecialchars($payment['student_name']) ?></dd>
            </div>
            <div class="detail-item">
                <dt>Kelas</dt>
                <dd><?= htmlspecialchars($payment['class_name']) ?></dd>
            </div>
            <div class="detail-item">
                <dt>Untuk Pembayaran</dt>
                <dd><?= htmlspecialchars($payment['jenis_pembayaran']) ?></dd>
            </div>
        </dl>
    </div>
    <div class="receipt-total">
        <dl>
            <div class="detail-item">
                <dt>Total Bayar</dt>
                <dd>Rp <?= number_format(htmlspecialchars($payment['amount']), 0, ',', '.') ?></dd>
            </div>
        </dl>
    </div>
    <div class="receipt-footer">
        --- Terima Kasih ---<br>Harap simpan kwitansi ini sebagai bukti pembayaran yang sah.
    </div>
</div>