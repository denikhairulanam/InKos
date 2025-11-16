<?php
// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $telepon = trim($_POST['telepon']);
    $role = $_POST['role'];

    // Validasi input
    if (empty($nama) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Semua field wajib diisi!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        // Cek apakah email sudah terdaftar
        $checkQuery = "SELECT id FROM users WHERE email = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Email sudah terdaftar!';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user baru
            $query = "INSERT INTO users (nama, email, password, telepon, role, is_verified)
    VALUES (?, ?, ?, ?, ?, TRUE)";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssss", $nama, $email, $hashed_password, $telepon, $role);

            if ($stmt->execute()) {
                $success = 'Pendaftaran berhasil! Silakan login.';
                $_POST = array(); // Reset form
            } else {
                $error = 'Terjadi kesalahan. Silakan coba lagi.';
            }
            $stmt->close();
        }
        $checkStmt->close();
    }
}
