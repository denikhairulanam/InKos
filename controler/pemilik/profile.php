<?php

include '../config.php';
include '../includes/auth.php';

// Cek Role Pemilik
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pemilik') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();

// Ambil Data Pemilik
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id AND role = 'pemilik'");
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: ../login.php');
    exit();
}

// UPDATE PROFIL
if (isset($_POST['update_profile'])) {
    $nama = $_POST['nama'];
    $telepon = $_POST['telepon'];
    $alamat = $_POST['alamat'];
    $bio = $_POST['bio'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tanggal_lahir = $_POST['tanggal_lahir'];

    $foto_profil = $user['foto_profil'];
    $target_dir = "../uploads/profiles/";

    if (!empty($_FILES['foto_profil']['name']) && $_FILES['foto_profil']['error'] == 0) {
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

        $file_extension = strtolower(pathinfo($_FILES["foto_profil"]["name"], PATHINFO_EXTENSION));
        $new_filename = "owner_" . $user_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;

        $allowed = ['jpg', 'jpeg', 'png'];
        $max_size = 3 * 1024 * 1024;

        if (in_array($file_extension, $allowed) && $_FILES['foto_profil']['size'] <= $max_size) {
            if (move_uploaded_file($_FILES["foto_profil"]["tmp_name"], $target_file)) {
                if ($user['foto_profil'] && file_exists($target_dir . $user['foto_profil']) && $user['foto_profil'] != 'default.png') {
                    unlink($target_dir . $user['foto_profil']);
                }
                $foto_profil = $new_filename;
            } else $error = "Gagal upload foto.";
        } else $error = "Format atau ukuran file tidak valid.";
    }

    if (empty($error)) {
        try {
            $update = $db->prepare("UPDATE users SET 
                nama=:nama, telepon=:telepon, alamat=:alamat,
                bio=:bio, jenis_kelamin=:jenis_kelamin, tanggal_lahir=:tanggal_lahir,
                foto_profil=:foto_profil, updated_at=NOW()
                WHERE id=:id");

            $update->execute([
                ':nama' => $nama,
                ':telepon' => $telepon,
                ':alamat' => $alamat,
                ':bio' => $bio,
                ':jenis_kelamin' => $jenis_kelamin,
                ':tanggal_lahir' => $tanggal_lahir,
                ':foto_profil' => $foto_profil,
                ':id' => $user_id
            ]);

            $success = "Profil berhasil diperbarui!";
        } catch (PDOException $e) {
            $error = "Kesalahan: " . $e->getMessage();
        }
    }
}

// UPDATE PASSWORD
if (isset($_POST['update_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi'];

    if (!password_verify($password_lama, $user['password'])) {
        $error_password = "Password lama salah!";
    } elseif ($password_baru !== $konfirmasi) {
        $error_password = "Konfirmasi password salah!";
    } elseif (strlen($password_baru) < 6) {
        $error_password = "Minimal 6 karakter.";
    } else {
        $hashed = password_hash($password_baru, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password=:password WHERE id=:id");
        $stmt->execute([':password' => $hashed, ':id' => $user_id]);
        $success_password = "Password berhasil diubah!";
    }
}

// HAPUS AKUN
if (isset($_POST['hapus_akun']) && ($_POST['konfirmasi_hapus'] ?? '') === 'HAPUS') {
    try {
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $user_id]);

        session_destroy();
        header("Location: ../register.php?deleted=1");
        exit();
    } catch (PDOException $e) {
        $error_delete = "Kesalahan: " . $e->getMessage();
    }
}
