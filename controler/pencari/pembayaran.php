<?php
session_start();
include '../config.php';

$db = new Database();
$conn = $db->getConnection();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pencari') {
    header('Location: ../login.php');
    exit;
}

$pencari_id = $_SESSION['user_id'];
$pemesanan_id = $_GET['pemesanan_id'] ?? null;

if (!$pemesanan_id) {
    header('Location: pemesanan.php');
    exit;
}

// Ambil data pemesanan + informasi pembayaran
$query = "SELECT p.*, k.nama_kos, k.alamat, k.id as kos_id, k.rekening_pemilik, k.nama_rekening, k.bank,
                 d.nama as nama_daerah, u.nama as nama_pemilik, u.telepon as telepon_pemilik,
                 pb.id as pembayaran_id, pb.jumlah_bayar, pb.status_pembayaran,
                 pb.bukti_bayar, pb.metode_pembayaran, pb.tanggal_bayar
          FROM pemesanan p 
          JOIN kos k ON p.kos_id = k.id 
          JOIN daerah d ON k.daerah_id = d.id
          JOIN users u ON p.pemilik_id = u.id
          LEFT JOIN pembayaran pb ON p.id = pb.pemesanan_id
          WHERE p.id = ? AND p.pencari_id = ? AND p.status = 'dikonfirmasi'";

$stmt = $conn->prepare($query);
$stmt->execute([$pemesanan_id, $pencari_id]);
$data = $stmt->fetch();

if (!$data) {
    die("Pemesanan tidak ditemukan atau tidak valid untuk pembayaran.");
}


// Proses Upload File
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti_pembayaran'])) {

    if ($_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {

        $upload_dir = '../uploads/bukti_bayar/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $max_size = 5 * 1024 * 1024;

        if (!in_array($file_extension, $allowed)) {
            $error = "Format tidak didukung";
        } elseif ($_FILES['bukti_pembayaran']['size'] > $max_size) {
            $error = "Ukuran file maksimal 5MB";
        } else {
            $file_name = 'bukti_' . $pemesanan_id . '_' . time() . "." . $file_extension;
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $file_path)) {

                try {
                    $conn->beginTransaction();

                    $status = "menunggu";

                    if ($data['pembayaran_id']) {
                        // Update pembayaran
                        $sql_update = "UPDATE pembayaran SET bukti_bayar=?, status_pembayaran=?, 
                                       tanggal_bayar=NOW(), metode_pembayaran='transfer'
                                       WHERE id=?";
                        $stmt = $conn->prepare($sql_update);
                        $stmt->execute([$file_name, $status, $data['pembayaran_id']]);
                    } else {
                        // Insert pembayaran baru
                        $sql_insert = "INSERT INTO pembayaran 
                                       (pemesanan_id, jumlah_bayar, metode_pembayaran, 
                                        status_pembayaran, bukti_bayar, tanggal_bayar)
                                       VALUES (?, ?, 'transfer', ?, ?, NOW())";
                        $stmt = $conn->prepare($sql_insert);
                        $stmt->execute([$pemesanan_id, $data['total_harga'], $status, $file_name]);
                    }

                    // Update status pembayaran pemesanan
                    $sql_status = "UPDATE pemesanan SET status_pembayaran=? WHERE id=?";
                    $stmt = $conn->prepare($sql_status);
                    $stmt->execute([$status, $pemesanan_id]);

                    $conn->commit();
                    $_SESSION['success'] = "Bukti pembayaran berhasil dikirim!";
                    header("Location: pembayaran.php?pemesanan_id=" . $pemesanan_id);
                    exit;
                } catch (Exception $e) {
                    $conn->rollBack();
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                    $error = "Kesalahan sistem: " . $e->getMessage();
                }
            } else {
                $error = "Gagal upload file";
            }
        }
    } else {
        $error = "Harap pilih file!";
    }
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
