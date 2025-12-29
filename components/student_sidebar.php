<?php
// FILE: components/student_sidebar.php
// Pastikan file ini di-include SETELAH 'config.php' dan SETELAH data $student diambil
// dari database di halaman utama (misal siswa_dashboard.php, absensi.php, dll).

// Variabel yang dibutuhkan dari halaman utama:
// $student (array asosiatif berisi data siswa yang sedang login)
// $upload_dir (direktori unggahan gambar profil)
?>

<div class="sidebar">
    <img src="images/Logo SMK.png" alt="Logo Sekolah" class="logo">

    <h2>SISTEM INFORMASI</h2>

    <div class="user-info">
        <div class="profile-picture" id="profile-picture-container">
            <?php
            $profile_pic_path = 'images/default_profile.png'; // Gambar default jika tidak ada
            // Cek jika ada gambar profil di database siswa
            if (!empty($student['profile_picture']) && file_exists($upload_dir . $student['profile_picture'])) {
                $profile_pic_path = $upload_dir . $student['profile_picture'];
            } else if (isset($_SESSION['profile_picture']) && file_exists($upload_dir . $_SESSION['profile_picture'])) {
                // Fallback ke session jika database belum update atau baru diunggah di halaman lain
                // Ini penting agar gambar langsung berubah setelah upload tanpa perlu login ulang
                $profile_pic_path = $upload_dir . $_SESSION['profile_picture'];
            }
            ?>
            <img src="<?php echo htmlspecialchars($profile_pic_path); ?>" alt="Foto Profil">
        </div>
        <span><?php echo htmlspecialchars($student['name']); ?></span>
    </div>

    <a href="siswa_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="input_biodata.php"><i class="fas fa-id-card"></i> Input Biodata</a>
    <a href="absensi.php"><i class="fas fa-check"></i> Absensi</a>
    <a href="riwayat_administrasi.php"><i class="fas fa-folder"></i> Riwayat Administrasi</a>
    <a href="riwayat_absensi.php"><i class="fas fa-history"></i> Riwayat Absensi</a>
    <a href="lihat_nilai.php"><i class="fas fa-graduation-cap"></i> Lihat Nilai</a>
    <a href="cetak_laporan.php"><i class="fas fa-print"></i> Cetak Lapor</a>
    <a href="#" onclick="toggleSetting()"><i class="fas fa-cog"></i> Setting</a>
    <ul>
        <li><a href="change_password_siswa.php"><i class="fas fa-key"></i> Change Password</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</div>

<style>
    /* CSS untuk Sidebar */
    /* Pastikan ini ada di file CSS utama Anda atau tambahkan di setiap halaman */
    body,
    h1,
    h2,
    h3,
    p,
    ul,
    li,
    button {
        margin: 0;
        padding: 0;
        border: 0;
        font-size: 100%;
        font: inherit;
        vertical-align: baseline;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #050d1a;
        color: #ccd6f6;
        line-height: 1.6;
        overflow-x: hidden;
        display: flex;
        min-height: 100vh;
    }

    .sidebar {
        width: 250px;
        background-color: #0a192f;
        padding: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: fixed;
        /* Sidebar tetap di posisi saat di-scroll */
        height: 100vh;
        overflow-y: auto;
        /* Aktifkan scroll jika menu terlalu banyak */
        scrollbar-width: none;
        /* Sembunyikan scrollbar Firefox */
        -ms-overflow-style: none;
        /* Sembunyikan scrollbar IE/Edge */
    }

    .sidebar::-webkit-scrollbar {
        display: none;
        /* Sembunyikan scrollbar Chrome/Safari */
    }

    .logo {
        width: 80px;
        margin-bottom: 20px;
    }

    .sidebar h2 {
        font-size: 1.5em;
        font-weight: 600;
        color: #ccd6f6;
        margin-bottom: 30px;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        text-align: center;
        width: 100%;
    }

    .user-info {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 30px;
    }

    .profile-picture {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-color: #495670;
        margin-bottom: 10px;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        /* Menambahkan pointer cursor untuk menunjukkan bisa diklik */
        position: relative;
        /* Penting untuk pseudo-elemen ::after */
    }

    .profile-picture img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        /* Penting untuk menjaga rasio aspek dan mengisi wadah */
    }

    /* Efek Hover Kamera pada Foto Profil */
    .profile-picture:hover::after {
        content: "\f030";
        /* FontAwesome camera icon */
        font-family: "Font Awesome 6 Free";
        /* Pastikan font ini dimuat */
        font-weight: 900;
        /* Untuk solid icon */
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        /* Overlay gelap */
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 2em;
        /* Ukuran ikon */
        border-radius: 50%;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .profile-picture:hover::after {
        opacity: 1;
        /* Tampilkan overlay saat hover */
    }

    .user-info span {
        font-size: 1em;
        color: #8892b0;
    }

    .sidebar a {
        display: flex;
        align-items: center;
        width: 100%;
        padding: 12px;
        margin-bottom: 8px;
        background-color: transparent;
        color: #ccd6f6;
        text-decoration: none;
        border-radius: 8px;
        transition: background-color 0.3s ease;
    }

    .sidebar a i {
        margin-right: 15px;
        font-size: 1.2em;
        color: #64ffda;
    }

    .sidebar a:hover {
        background-color: #443ccf;
    }

    /* Submenu Setting */
    .sidebar ul {
        list-style: none;
        padding-left: 0;
        margin-top: 5px;
        display: none;
        /* Sembunyikan secara default */
    }

    .sidebar ul li a {
        padding-left: 40px;
        /* Indentasi submenu */
        background-color: transparent;
        color: #8892b0;
    }

    .sidebar ul li a:hover {
        background-color: #443ccf;
    }

    /* Responsif untuk Sidebar */
    @media (max-width: 768px) {
        .sidebar {
            width: 100%;
            position: static;
            /* Hilangkan fixed position di mobile */
            height: auto;
            align-items: center;
        }

        .sidebar h2 {
            text-align: center;
        }

        /* Kontainer utama perlu menyesuaikan margin di mobile */
        .container {
            margin-left: 0;
        }
    }
</style>

<script>
    // JavaScript untuk toggle menu "Setting"
    function toggleSetting() {
        var settingMenu = document.querySelector('.sidebar ul');
        if (settingMenu) {
            settingMenu.style.display = settingMenu.style.display === 'none' || settingMenu.style.display === '' ? 'block' : 'none';
        }
    }
</script>