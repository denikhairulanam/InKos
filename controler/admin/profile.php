<?php
include '../config.php';
include '../includes/auth.php';
// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();

// Ambil data user yang login
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika user tidak ditemukan, redirect ke login
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// UPDATE DATA PROFIL
if (isset($_POST['update_profile'])) {
    $nama = $_POST['nama'];
    $telepon = $_POST['telepon'];
    $alamat = $_POST['alamat'];
    $bio = $_POST['bio'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tanggal_lahir = $_POST['tanggal_lahir'];

    // Upload foto profil jika ada
    $foto_profil = $user['foto_profil'];
    $target_dir = "../uploads/profiles/";

    if (!empty($_FILES['foto_profil']['name']) && $_FILES['foto_profil']['error'] == 0) {
        // Buat folder uploads jika belum ada
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES["foto_profil"]["name"], PATHINFO_EXTENSION);
        $new_filename = "profile_" . $user_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;

        // Validasi file
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (in_array(strtolower($file_extension), $allowed_types)) {
            if ($_FILES['foto_profil']['size'] <= $max_size) {
                if (move_uploaded_file($_FILES["foto_profil"]["tmp_name"], $target_file)) {
                    // Hapus foto lama jika bukan default
                    if ($user['foto_profil'] && $user['foto_profil'] != 'default.png' && file_exists($target_dir . $user['foto_profil'])) {
                        unlink($target_dir . $user['foto_profil']);
                    }
                    $foto_profil = $new_filename;
                } else {
                    $error = "Gagal mengupload foto profil.";
                }
            } else {
                $error = "Ukuran file terlalu besar. Maksimal 2MB.";
            }
        } else {
            $error = "Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.";
        }
    }

    // Validasi data
    if (empty($error)) {
        try {
            $update = $db->prepare("UPDATE users SET 
                nama = :nama,
                telepon = :telepon,
                alamat = :alamat,
                bio = :bio,
                jenis_kelamin = :jenis_kelamin,
                tanggal_lahir = :tanggal_lahir,
                foto_profil = :foto_profil,
                updated_at = NOW()
                WHERE id = :id");

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

            // Ambil data user yang sudah diupdate
            $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $success = "Profil berhasil diperbarui!";
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// UBAH PASSWORD
if (isset($_POST['update_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi'];

    // Validasi
    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi)) {
        $error_password = "Semua field password harus diisi!";
    } elseif ($password_baru !== $konfirmasi) {
        $error_password = "Konfirmasi password tidak cocok!";
    } elseif (strlen($password_baru) < 6) {
        $error_password = "Password baru minimal 6 karakter!";
    } elseif (!password_verify($password_lama, $user['password'])) {
        $error_password = "Password lama salah!";
    } else {
        try {
            $hashed = password_hash($password_baru, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id");
            $stmt->execute([':password' => $hashed, ':id' => $user_id]);

            $success_password = "Password berhasil diubah!";
        } catch (PDOException $e) {
            $error_password = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// HAPUS AKUN
if (isset($_POST['hapus_akun'])) {
    $konfirmasi = $_POST['konfirmasi_hapus'] ?? '';

    if ($konfirmasi === 'HAPUS') {
        try {
            // Hapus foto profil jika ada
            $target_dir = "../uploads/profiles/";
            if ($user['foto_profil'] && $user['foto_profil'] != 'default.png') {
                if (file_exists($target_dir . $user['foto_profil'])) {
                    unlink($target_dir . $user['foto_profil']);
                }
            }

            $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $user_id]);

            session_destroy();
            header("Location: register.php?deleted=1");
            exit();
        } catch (PDOException $e) {
            $error_delete = "Terjadi kesalahan: " . $e->getMessage();
        }
    } else {
        $error_delete = "Silakan ketik 'HAPUS' untuk mengonfirmasi penghapusan akun.";
    }
}
?>