<?php


// Include auth.php yang sudah ada fungsi checkAuth()
include '../includes/auth.php';

// Check authentication dan role di awal
checkAuth();
if (getUserRole() !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "Tambah User - INKOS";

include '../config.php';
$database = new Database();
$db = $database->getConnection();

// Handle form submission
$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $telepon = $_POST['telepon'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $universitas = $_POST['universitas'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $role = $_POST['role'] ?? 'pencari';
    $is_verified = isset($_POST['is_verified']) ? 1 : 0;

    // Validation
    if (empty($nama)) {
        $errors[] = "Nama harus diisi";
    }

    if (empty($email)) {
        $errors[] = "Email harus diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    } else {
        // Check if email already exists
        $check_email = $db->prepare("SELECT id FROM users WHERE email = ?");
        $check_email->execute([$email]);
        if ($check_email->fetch()) {
            $errors[] = "Email sudah terdaftar";
        }
    }

    if (empty($password)) {
        $errors[] = "Password harus diisi";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter";
    }

    if (empty($errors)) {
        try {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $query = "INSERT INTO users (nama, email, password, telepon, alamat, universitas, bio, jenis_kelamin, tanggal_lahir, role, is_verified) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $db->prepare($query);
            $stmt->execute([
                $nama,
                $email,
                $hashed_password,
                $telepon,
                $alamat,
                $universitas,
                $bio,
                $jenis_kelamin,
                $tanggal_lahir,
                $role,
                $is_verified
            ]);

            $_SESSION['success_message'] = "User berhasil ditambahkan";
            header('Location: users.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}

// INCLUDE HEADER SETELAH SEMUA PROCESSING
include '../includes/header/admin_header.php';
?>