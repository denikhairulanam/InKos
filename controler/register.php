<?php
// controler/register.php

require_once 'config.php';

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
        try {
            // Initialize database connection
            $database = new Database();
            $conn = $database->getConnection();

            // Cek apakah email sudah terdaftar
            $checkQuery = "SELECT id FROM users WHERE email = :email";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bindParam(':email', $email);
            $checkStmt->execute();

            if ($checkStmt->rowCount() > 0) {
                $error = 'Email sudah terdaftar!';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user baru
                $query = "INSERT INTO users (nama, email, password, telepon, role, is_verified) 
                         VALUES (:nama, :email, :password, :telepon, :role, TRUE)";

                $stmt = $conn->prepare($query);
                $stmt->bindParam(':nama', $nama);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':telepon', $telepon);
                $stmt->bindParam(':role', $role);

                if ($stmt->execute()) {
                    $success = 'Pendaftaran berhasil! Silakan login.';
                    $_POST = array(); // Reset form
                } else {
                    $error = 'Terjadi kesalahan. Silakan coba lagi.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan database: ' . $e->getMessage();
        }
    }
}
