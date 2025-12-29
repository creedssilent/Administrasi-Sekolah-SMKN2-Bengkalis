<?php
$conn = new mysqli('localhost', 'root', '', 'school_management');
if ($conn->connect_error) {
    die('Koneksi gagal: ' . $conn->connect_error);
}

if (isset($_POST['submit'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    $conn->query("UPDATE users SET username = '$username', password = '$password', role = '$role' WHERE id = $id");
    header('Location: manage_users.php');
    exit();
}

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM users WHERE id = $id");
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
</head>

<body>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $user['id'] ?>">
        <label>Username</label>
        <input type="text" name="username" value="<?= $user['username'] ?>">
        <label>Password</label>
        <input type="password" name="password">
        <label>Role</label>
        <select name="role">
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
        </select>
        <button type="submit" name="submit">Simpan</button>
    </form>
</body>

</html>