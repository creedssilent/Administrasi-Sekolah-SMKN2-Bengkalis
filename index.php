<?php
session_start();
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') header('Location: admin_dashboard.php');
    elseif ($_SESSION['role'] === 'guru') header('Location: guru_dashboard.php');
    elseif ($_SESSION['role'] === 'siswa') header('Location: siswa_dashboard.php');
    elseif ($_SESSION['role'] === 'administrasi') header('Location: administrasi_dashboard.php');
    elseif ($_SESSION['role'] === 'kepala_sekolah') header('Location: kepala_sekolah_dashboard.php'); // ✅ Logika untuk Kepala Sekolah
    exit();
}

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            if ($user['status'] === 'inactive') {
                echo "<script>alert('Akun Anda telah dinonaktifkan.'); window.location.href='index.php';</script>";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['username'] = $user['username'];

                unset($_SESSION['seen_attendance_ids']);

                // Blok 2: Pengalihan setelah user BARU SAJA berhasil login
                if ($user['role'] === 'admin') header('Location: admin_dashboard.php');
                elseif ($user['role'] === 'guru') header('Location: guru_dashboard.php');
                elseif ($user['role'] === 'siswa') header('Location: siswa_dashboard.php');
                elseif ($user['role'] === 'administrasi') header('Location: administrasi_dashboard.php');
                elseif ($user['role'] === 'kepala_sekolah') header('Location: kepala_sekolah_dashboard.php');
                exit();
            }
        } else {
            echo "<script>alert('Password salah.'); window.location.href='index.php';</script>";
        }
    } else {
        echo "<script>alert('Username tidak ditemukan.'); window.location.href='index.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMANDAKA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0056b3;
            --secondary-color: #f4f7fc;
            --info-panel-bg: linear-gradient(135deg, rgba(33, 147, 176, 0.9), rgba(0, 86, 179, 0.95)), url('images/Batik.png');
            --text-dark: #333;
            --text-light: #fff;
            --border-color: #ddd;
            --hover-text-color: #ffd700;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #e9ecef;
            overflow: hidden;
        }

        .login-container {
            display: flex;
            width: 900px;
            height: 550px;
            background-color: var(--text-light);
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        @keyframes slideInFromLeft {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideInFromRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .info-panel {
            flex-basis: 50%;
            background-image: var(--info-panel-bg);
            background-size: cover;
            background-position: center;
            color: var(--text-light);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 40px;
            animation: slideInFromLeft 1s ease-out;
        }

        .logo-container {
            background-color: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(5px);
            border-radius: 50%;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .info-panel .logo {
            width: 100px;
            display: block;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .logo-container:hover .logo {
            transform: scale(1.15) rotate(5deg);
            cursor: pointer;
        }

        .info-panel h1 {
            margin: 0;
            font-size: 2.2em;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .info-panel p {
            margin: 10px 0 0;
            font-size: 1em;
            font-weight: 300;
            opacity: 0.9;
        }

        .letter {
            display: inline-block;
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), color 0.3s;
        }

        .letter:hover {
            transform: scale(1.4) translateY(-8px);
            color: var(--hover-text-color);
            cursor: pointer;
        }

        .login-panel {
            flex-basis: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px;
            background-color: var(--secondary-color);
            animation: slideInFromRight 1s ease-out;
        }

        .login-panel h2 {
            margin: 0 0 10px;
            color: var(--text-dark);
            font-size: 1.8em;
            font-weight: 600;
        }

        .login-panel .subtitle {
            margin-bottom: 30px;
            color: #777;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #aaa;
        }

        .input-wrapper input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .login-btn {
            background-color: var(--primary-color);
            color: var(--text-light);
            padding: 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s, transform 0.1s;
        }

        .login-btn:hover {
            background-color: #004a99;
        }

        .login-btn:active {
            transform: scale(0.98);
        }

        .forgot-password {
            text-align: right;
            margin-top: 15px;
        }

        .forgot-password a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9em;
        }

        @media (max-width: 920px) {
            .login-container {
                flex-direction: column;
                width: 95%;
                max-width: 450px;
                height: auto;
            }

            .info-panel {
                display: none;
            }

            .login-panel {
                border-radius: 15px;
                animation: fadeIn 1s;
            }
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="info-panel">
            <div class="logo-container">
                <img src="images/Logo SMK.png" alt="Logo Sekolah" class="logo">
            </div>
            <h1 id="animated-title">SIMANDAKA</h1>
            <p>
                Sistem Informasi Manajemen Akademik<br>SMK Negeri 2 Bengkalis
            </p>
        </div>
        <div class="login-panel">
            <h2>Selamat Datang</h2>
            <p class="subtitle">Silakan masuk ke akun Anda.</p>

            <form action="index.php" method="POST">
                <div class="input-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" required>
                    </div>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required>
                    </div>
                </div>
                <div class="forgot-password">
                    <a href="#">Lupa Password?</a>
                </div>
                <button type="submit" class="login-btn">Login</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const titleElement = document.getElementById('animated-title');
            if (titleElement) {
                const text = titleElement.innerText;
                titleElement.innerHTML = '';

                text.split('').forEach(letter => {
                    const span = document.createElement('span');
                    span.className = 'letter';
                    span.innerText = letter;
                    titleElement.appendChild(span);
                });
            }
        });
    </script>
</body>

</html>