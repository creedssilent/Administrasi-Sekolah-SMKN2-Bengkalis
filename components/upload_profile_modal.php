<?php
// FILE: components/upload_profile_modal.php
// Pastikan file ini di-include SETELAH 'config.php' dan SETELAH data $student diambil
// dari database di halaman utama (misal siswa_dashboard.php, absensi.php, dll).

// Variabel yang dibutuhkan dari halaman utama:
// $conn (dari config.php)
// $user_id (ID siswa yang sedang login, dari $student['id'])
// $current_profile_picture (nama file gambar profil saat ini, dari $student['profile_picture'])
// $user_table (nama tabel user: 'students')
// $upload_dir (direktori unggahan, misal 'uploads/profile_pictures/')

// Pastikan direktori unggahan ada dan memiliki izin tulis
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true); // Pastikan izin 0777 di lingkungan development, atur lebih ketat di produksi
}

// Tangani unggah gambar profil jika ada POST request dari form modal ini
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture_upload'])) {
    $file_name = $_FILES['profile_picture_upload']['name'];
    $file_tmp = $_FILES['profile_picture_upload']['tmp_name'];
    $file_size = $_FILES['profile_picture_upload']['size'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_extensions = array("jpeg", "jpg", "png", "gif");
    $max_file_size = 2 * 1024 * 1024; // Maksimal 2MB

    if (in_array($file_ext, $allowed_extensions) === false) {
        echo "<script>alert('Ekstensi file tidak diizinkan. Hanya JPG, JPEG, PNG, GIF yang diperbolehkan.');</script>";
    } elseif ($file_size > $max_file_size) {
        echo "<script>alert('Ukuran file terlalu besar. Maksimal 2MB.');</script>";
    } else {
        // Buat nama file unik untuk mencegah konflik
        $new_file_name = uniqid('profile_') . '.' . $file_ext;
        $target_file = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp, $target_file)) {
            // Hapus gambar lama jika ada dan file-nya ada di server
            if (!empty($current_profile_picture) && file_exists($upload_dir . $current_profile_picture)) {
                unlink($upload_dir . $current_profile_picture);
            }

            // Update path gambar di database
            $update_query = $conn->prepare("UPDATE `$user_table` SET profile_picture = ? WHERE id = ?");
            $update_query->bind_param("si", $new_file_name, $user_id);

            if ($update_query->execute()) {
                // Perbarui variabel sesi agar gambar di sidebar langsung berubah tanpa harus login ulang
                $_SESSION['profile_picture'] = $new_file_name;
                echo "<script>alert('Foto profil berhasil diunggah!');</script>";
                // Refresh halaman untuk memastikan gambar diperbarui di tampilan
                echo "<script>window.location.href = window.location.href;</script>";
            } else {
                echo "<script>alert('Gagal memperbarui database: " . $conn->error . "');</script>";
            }
            $update_query->close();
        } else {
            echo "<script>alert('Gagal mengunggah file.');</script>";
        }
    }
}
?>

<div id="uploadPhotoModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h3>Unggah Foto Profil</h3>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="file" name="profile_picture_upload" id="profile_picture_input" accept="image/*" required>
            <button type="submit" class="menu-btn">
                <i class="fas fa-upload"></i> Unggah Foto
            </button>
        </form>
        <p style="font-size: 0.9em; color: #8892b0; margin-top: 15px;">
            Ukuran maksimum file: 2MB. Format yang didukung: JPG, JPEG, PNG, GIF.
        </p>
    </div>
</div>

<style>
    /* CSS untuk Modal Unggah Foto */
    .modal {
        display: none;
        /* KOREKSI PENTING: Sembunyikan secara default */
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.8);
        /* display: flex; <--- HAPUS ATAU KOMEN BARIS INI */
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background-color: #1e2a47;
        margin: auto;
        padding: 30px;
        border-radius: 10px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        position: relative;
        text-align: center;
    }

    .close-button {
        color: #aaa;
        position: absolute;
        top: 10px;
        right: 20px;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close-button:hover,
    .close-button:focus {
        color: #ccd6f6;
        text-decoration: none;
        cursor: pointer;
    }

    .modal-content h3 {
        color: #ccd6f6;
        margin-bottom: 20px;
    }

    /* Gaya untuk input file di dalam modal */
    .modal-content form input[type="file"] {
        display: block;
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 5px;
        background-color: #0a192f;
        color: #ccd6f6;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 1em;
        outline: none;
        margin-bottom: 20px;
        cursor: pointer;
    }

    .modal-content form input[type="file"]::-webkit-file-upload-button {
        visibility: hidden;
    }

    .modal-content form input[type="file"]::before {
        content: 'Pilih Gambar';
        display: inline-block;
        background: #64ffda;
        border-radius: 5px;
        padding: 8px 12px;
        outline: none;
        white-space: nowrap;
        -webkit-user-select: none;
        /* Untuk Chrome, Safari, Edge lama */
        -moz-user-select: none;
        /* Untuk Firefox */
        -ms-user-select: none;
        /* Untuk Internet Explorer */
        user-select: none;
        /* Untuk semua browser modern */
        cursor: pointer;
        color: #0a192f;
        font-weight: 600;
        font-size: 0.9em;
    }

    .modal-content form input[type="file"]:hover::before {
        background: #52e7c4;
    }

    .modal-content form input[type="file"]:active::before {
        background: #41d2ad;
    }

    /* Ini adalah CSS untuk tombol Unggah Foto di dalam modal */
    .modal-content .menu-btn {
        /* Menargetkan tombol di dalam modal */
        background-color: #64ffda;
        color: #0a192f;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        transition: background-color 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: auto;
        /* Agar tidak terlalu lebar */
        margin: 0 auto;
        /* Pusatkan tombol */
    }

    .modal-content .menu-btn:hover {
        background-color: #52e7c4;
    }
</style>

<script>
    // JavaScript untuk mengelola Modal
    document.addEventListener('DOMContentLoaded', function() {
        var profilePicContainer = document.getElementById('profile-picture-container');
        var uploadModal = document.getElementById('uploadPhotoModal');
        var closeButton = document.querySelector('.modal .close-button');

        // Pastikan elemen profilePicContainer ada sebelum menambahkan event listener
        if (profilePicContainer) {
            profilePicContainer.addEventListener('click', function() {
                uploadModal.style.display = 'flex'; // Tampilkan modal saat diklik
            });
        }

        // Event listener untuk tombol tutup
        if (closeButton) {
            closeButton.addEventListener('click', function() {
                uploadModal.style.display = 'none'; // Sembunyikan modal
            });
        }

        // Event listener untuk menutup modal saat mengklik di luar konten modal
        window.addEventListener('click', function(event) {
            if (event.target == uploadModal) {
                uploadModal.style.display = 'none';
            }
        });
    });
</script>