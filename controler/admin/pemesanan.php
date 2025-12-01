<?php

include '../includes/auth.php';
require_once '../config.php';

// Check authentication dan role
checkAuth();
if (getUserRole() !== 'admin') {
    header('Location: ../login.php');
    exit();
}

class PemesananController
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    // Get all bookings with filters
    public function getPemesanan($page = 1, $limit = 10, $search = '', $status = '', $status_pembayaran = '')
    {
        $offset = ($page - 1) * $limit;

        $query = "SELECT 
                    p.*,
                    u_pencari.nama as nama_pencari,
                    u_pencari.email as email_pencari,
                    u_pencari.telepon as telepon_pencari,
                    u_pemilik.nama as nama_pemilik,
                    u_pemilik.email as email_pemilik,
                    k.nama_kos,
                    k.alamat as alamat_kos,
                    k.harga_bulanan,
                    k.tipe_kos,
                    k.foto_utama,
                    pb.jumlah_bayar,
                    pb.status_pembayaran as status_pembayaran_detail,
                    pb.bukti_bayar
                 FROM pemesanan p
                 JOIN users u_pencari ON p.pencari_id = u_pencari.id
                 JOIN users u_pemilik ON p.pemilik_id = u_pemilik.id
                 JOIN kos k ON p.kos_id = k.id
                 LEFT JOIN pembayaran pb ON p.id = pb.pemesanan_id
                 WHERE 1=1";

        $params = [];

        if (!empty($search)) {
            $query .= " AND (u_pencari.nama LIKE ? OR u_pencari.email LIKE ? OR k.nama_kos LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($status)) {
            $query .= " AND p.status = ?";
            $params[] = $status;
        }

        if (!empty($status_pembayaran)) {
            $query .= " AND p.status_pembayaran = ?";
            $params[] = $status_pembayaran;
        }

        $query .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $pemesanan = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count
            $countQuery = "SELECT COUNT(*) as total 
                          FROM pemesanan p
                          JOIN users u_pencari ON p.pencari_id = u_pencari.id
                          JOIN users u_pemilik ON p.pemilik_id = u_pemilik.id
                          JOIN kos k ON p.kos_id = k.id
                          WHERE 1=1";

            $countParams = [];

            if (!empty($search)) {
                $countQuery .= " AND (u_pencari.nama LIKE ? OR u_pencari.email LIKE ? OR k.nama_kos LIKE ?)";
                $searchTerm = "%$search%";
                $countParams[] = $searchTerm;
                $countParams[] = $searchTerm;
                $countParams[] = $searchTerm;
            }

            if (!empty($status)) {
                $countQuery .= " AND p.status = ?";
                $countParams[] = $status;
            }

            if (!empty($status_pembayaran)) {
                $countQuery .= " AND p.status_pembayaran = ?";
                $countParams[] = $status_pembayaran;
            }

            $countStmt = $this->db->prepare($countQuery);
            $countStmt->execute($countParams);
            $total_count = $countStmt->fetch()['total'];

            return [
                'pemesanan' => $pemesanan,
                'total_count' => $total_count,
                'total_pages' => ceil($total_count / $limit),
                'current_page' => $page
            ];
        } catch (PDOException $e) {
            error_log("Error getPemesanan: " . $e->getMessage());
            return [
                'pemesanan' => [],
                'total_count' => 0,
                'total_pages' => 0,
                'current_page' => $page
            ];
        }
    }

    // Get booking detail by ID
    public function getDetailPemesanan($id)
    {
        $query = "SELECT 
                    p.*,
                    u_pencari.id as pencari_id,
                    u_pencari.nama as nama_pencari,
                    u_pencari.email as email_pencari,
                    u_pencari.telepon as telepon_pencari,
                    u_pencari.alamat as alamat_pencari,
                    u_pencari.universitas as universitas_pencari,
                    u_pencari.jenis_kelamin as jenis_kelamin_pencari,
                    u_pemilik.id as pemilik_id,
                    u_pemilik.nama as nama_pemilik,
                    u_pemilik.email as email_pemilik,
                    u_pemilik.telepon as telepon_pemilik,
                    k.id as kos_id,
                    k.nama_kos,
                    k.alamat as alamat_kos,
                    k.deskripsi,
                    k.harga_bulanan,
                    k.tipe_kos,
                    k.ukuran_kamar,
                    k.kamar_mandi,
                    k.fasilitas,
                    k.foto_utama,
                    k.foto_lainnya,
                    pb.id as pembayaran_id,
                    pb.jumlah_bayar,
                    pb.metode_pembayaran,
                    pb.status_pembayaran as status_pembayaran_detail,
                    pb.tanggal_bayar,
                    pb.bukti_bayar
                 FROM pemesanan p
                 JOIN users u_pencari ON p.pencari_id = u_pencari.id
                 JOIN users u_pemilik ON p.pemilik_id = u_pemilik.id
                 JOIN kos k ON p.kos_id = k.id
                 LEFT JOIN pembayaran pb ON p.id = pb.pemesanan_id
                 WHERE p.id = ?";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getDetailPemesanan: " . $e->getMessage());
            return null;
        }
    }

    // Update booking status
    public function updateStatusPemesanan($id, $status, $admin_id, $catatan_pembatalan = null)
    {
        $query = "UPDATE pemesanan 
                 SET status = ?, 
                     catatan_pembatalan = ?,
                     updated_at = NOW()
                 WHERE id = ?";

        try {
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$status, $catatan_pembatalan, $id]);
        } catch (PDOException $e) {
            error_log("Error updateStatusPemesanan: " . $e->getMessage());
            return false;
        }
    }

    // Update payment status
    public function updateStatusPembayaran($id, $status, $admin_id)
    {
        $query = "UPDATE pemesanan 
                 SET status_pembayaran = ?,
                     updated_at = NOW()
                 WHERE id = ?";

        try {
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$status, $id]);
        } catch (PDOException $e) {
            error_log("Error updateStatusPembayaran: " . $e->getMessage());
            return false;
        }
    }

    // Update rental status
    public function updateStatusPenyewaan($id, $status, $admin_id)
    {
        $query = "UPDATE pemesanan 
                 SET status_penyewaan = ?,
                     updated_at = NOW()
                 WHERE id = ?";

        try {
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$status, $id]);
        } catch (PDOException $e) {
            error_log("Error updateStatusPenyewaan: " . $e->getMessage());
            return false;
        }
    }

    // Delete booking
    public function deletePemesanan($id)
    {
        $query = "DELETE FROM pemesanan WHERE id = ?";

        try {
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deletePemesanan: " . $e->getMessage());
            return false;
        }
    }

    // Get statistics
    public function getStats()
    {
        $query = "SELECT 
                    status,
                    COUNT(*) as total
                 FROM pemesanan 
                 GROUP BY status";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getStats: " . $e->getMessage());
            return [];
        }
    }
}

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize controller
$pemesananController = new PemesananController($db);

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $pemesanan_id = $_POST['pemesanan_id'] ?? '';
        $new_status = $_POST['status'] ?? '';
        $catatan_pembatalan = $_POST['catatan_pembatalan'] ?? null;
        $admin_id = $_SESSION['user_id'] ?? '';

        if (!empty($pemesanan_id) && !empty($new_status)) {
            if ($pemesananController->updateStatusPemesanan($pemesanan_id, $new_status, $admin_id, $catatan_pembatalan)) {
                $_SESSION['success_message'] = "Status pemesanan berhasil diperbarui!";
            } else {
                $_SESSION['error_message'] = "Gagal memperbarui status pemesanan!";
            }
        } else {
            $_SESSION['error_message'] = "Data tidak lengkap!";
        }

        $redirect_url = "pemesanan.php";
        if (isset($_POST['pemesanan_id'])) {
            $redirect_url .= "?id=" . $_POST['pemesanan_id'];
        }
        header("Location: " . $redirect_url);
        exit;
    }

    if (isset($_POST['update_payment_status'])) {
        $pemesanan_id = $_POST['pemesanan_id'] ?? '';
        $new_status = $_POST['status_pembayaran'] ?? '';
        $admin_id = $_SESSION['user_id'] ?? '';

        if (!empty($pemesanan_id) && !empty($new_status)) {
            if ($pemesananController->updateStatusPembayaran($pemesanan_id, $new_status, $admin_id)) {
                $_SESSION['success_message'] = "Status pembayaran berhasil diperbarui!";
            } else {
                $_SESSION['error_message'] = "Gagal memperbarui status pembayaran!";
            }
        } else {
            $_SESSION['error_message'] = "Data tidak lengkap!";
        }

        $redirect_url = "pemesanan.php";
        if (isset($_POST['pemesanan_id'])) {
            $redirect_url .= "?id=" . $_POST['pemesanan_id'];
        }
        header("Location: " . $redirect_url);
        exit;
    }

    if (isset($_POST['update_rental_status'])) {
        $pemesanan_id = $_POST['pemesanan_id'] ?? '';
        $new_status = $_POST['status_penyewaan'] ?? '';
        $admin_id = $_SESSION['user_id'] ?? '';

        if (!empty($pemesanan_id) && !empty($new_status)) {
            if ($pemesananController->updateStatusPenyewaan($pemesanan_id, $new_status, $admin_id)) {
                $_SESSION['success_message'] = "Status penyewaan berhasil diperbarui!";
            } else {
                $_SESSION['error_message'] = "Gagal memperbarui status penyewaan!";
            }
        } else {
            $_SESSION['error_message'] = "Data tidak lengkap!";
        }

        $redirect_url = "pemesanan.php";
        if (isset($_POST['pemesanan_id'])) {
            $redirect_url .= "?id=" . $_POST['pemesanan_id'];
        }
        header("Location: " . $redirect_url);
        exit;
    }

    if (isset($_POST['delete_id'])) {
        $pemesanan_id = $_POST['delete_id'] ?? '';

        if (!empty($pemesanan_id)) {
            if ($pemesananController->deletePemesanan($pemesanan_id)) {
                $_SESSION['success_message'] = "Data pemesanan berhasil dihapus!";
            } else {
                $_SESSION['error_message'] = "Gagal menghapus data pemesanan!";
            }
        } else {
            $_SESSION['error_message'] = "ID pemesanan tidak valid!";
        }

        header("Location: pemesanan.php");
        exit;
    }
}

// Get parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$status_pembayaran_filter = isset($_GET['status_pembayaran']) ? trim($_GET['status_pembayaran']) : '';
$limit = 10;

// Get booking data
$data = $pemesananController->getPemesanan($page, $limit, $search, $status_filter, $status_pembayaran_filter);
$pemesanan = $data['pemesanan'];
$total_count = $data['total_count'];
$total_pages = $data['total_pages'];
$current_page = $data['current_page'];
$offset = ($page - 1) * $limit;

// Get stats
$stats = $pemesananController->getStats();

// Get detail if ID provided
$detail_pemesanan = null;
if (isset($_GET['id'])) {
    $detail_pemesanan = $pemesananController->getDetailPemesanan($_GET['id']);
}
