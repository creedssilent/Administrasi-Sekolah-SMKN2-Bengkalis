<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header('Location: index.php');
    exit();
}

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $teacher_id = $_SESSION['user_id'];
    $target_dir = "uploads/profiles/";

    // Buat folder jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Validasi file
    $check = getimagesize($_FILES['profile_picture']['tmp_name']);
    if ($check === false) {
        $_SESSION['error'] = "File bukan gambar valid";
        header("Location: guru_dashboard.php");
        exit();
    }

    // Batasi ukuran file (max 2MB)
    if ($_FILES['profile_picture']['size'] > 2000000) {
        $_SESSION['error'] = "Ukuran file terlalu besar. Maksimal 2MB";
        header("Location: guru_dashboard.php");
        exit();
    }

    // Generate nama file unik
    $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array(strtolower($file_extension), $allowed_extensions)) {
        $_SESSION['error'] = "Hanya format JPG, JPEG, PNG & GIF yang diizinkan";
        header("Location: guru_dashboard.php");
        exit();
    }

    $new_filename = "profile_" . $teacher_id . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;

    // Upload file
    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
        // Update database
        $stmt = $conn->prepare("UPDATE teachers SET profile_picture = ? WHERE id = ?");
        $stmt->bind_param("si", $target_file, $teacher_id);
        $stmt->execute();
        $stmt->close();

        // Update session
        $_SESSION['profile_picture'] = $target_file;
        $_SESSION['success'] = "Foto profil berhasil diubah";
    } else {
        $_SESSION['error'] = "Gagal mengupload file";
    }

    header("Location: guru_dashboard.php");
    exit();
}
