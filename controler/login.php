<?php
// controller/loginController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

$database = new Database();
$db = $database->getConnection();

$error = "";

// Jika sudah login, redirect
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Jika form di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Email dan password harus diisi";
    } else {
        // Query menggunakan PDO
        $query = "SELECT * FROM users WHERE email = :email AND is_verified = TRUE LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(); // fetch assoc default

        if ($user) {
            if (password_verify($password, $user['password'])) {

                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nama'] = $user['nama'];
                $_SESSION['user_role'] = $user['role'];

                // Redirect berdasarkan role
                if ($user['role'] === 'pemilik') {
                    header("Location: dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $error = "Password salah";
            }
        } else {
            $error = "Email tidak ditemukan atau belum diverifikasi";
        }
    }
}
