<?php
// controller/loginController.php

// Memastikan session sudah berjalan, jika belum maka jalankan session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Memanggil konfigurasi database
require_once __DIR__ . '/../config.php';

// Membuat koneksi database melalui class Database()
$database = new Database();
$db = $database->getConnection();

// Variabel untuk menampung pesan error
$error = "";

// Jika user sudah login, langsung redirect ke index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Mengecek jika form dikirimkan dengan method POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Mengambil input email & password
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validasi input kosong
    if (empty($email) || empty($password)) {
        $error = "Email dan password harus diisi";
    } else {

        // Query untuk mencari user yang email-nya cocok & sudah terverifikasi
        $query = "
            SELECT * FROM users 
            WHERE email = :email 
            AND is_verified = TRUE 
            LIMIT 1
        ";

        // Menyiapkan query dengan PDO (mencegah SQL Injection)
        $stmt = $db->prepare($query);

        // Mengikat parameter email
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        // Menjalankan query
        $stmt->execute();

        // Mengambil data user jika ada
        $user = $stmt->fetch(); // fetch assoc default

        // Jika user ditemukan
        if ($user) {

            // Memverifikasi apakah password yang diinput cocok dengan hash di database
            if (password_verify($password, $user['password'])) {

                // Set session agar user dianggap login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nama'] = $user['nama'];
                $_SESSION['user_role'] = $user['role'];

                // Redirect berdasarkan role pengguna
                if ($user['role'] === 'pemilik') {
                    header("Location: dashboard.php"); // Halaman pemilik kos
                } else {
                    header("Location: index.php"); // Halaman umum/user biasa
                }
                exit;
            } else {
                // Password salah
                $error = "Password salah";
            }
        } else {
            // Email tidak ditemukan atau belum terverifikasi
            $error = "Email tidak ditemukan atau belum diverifikasi";
        }
    }
}
