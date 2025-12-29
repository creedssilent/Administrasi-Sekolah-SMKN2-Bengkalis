<?php
require_once 'vendor/autoload.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    die("Akses ditolak.");
}

use Dompdf\Dompdf;
use Dompdf\Options;

// Menangkap seluruh output HTML dari 'template_rapor_pdf.php'
ob_start();
include 'template_rapor_pdf.php'; // <<< DIUBAH: Menggunakan template PDF baru
$html_content = ob_get_clean();

// Konfigurasi Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Times New Roman');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html_content);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Membuat nama file yang aman
$nama_siswa_safe = preg_replace("/[^a-zA-Z0-9\s]/", "", $siswa['name']);
$nama_file = "Rapor Semester - " . $nama_siswa_safe . ".pdf";

// Mengirimkan PDF ke browser
$dompdf->stream($nama_file, ["Attachment" => false]);
exit();
