<?php
// Mulai session untuk menyimpan data login dan notifikasi
session_start();
// Include file konfigurasi database
include '../config.php';

// Ambil koneksi PDO dari class Database
$db = new Database();
$conn = $db->getConnection();

// Cek apakah user sudah login dan memiliki role sebagai pemilik
// Jika tidak, redirect ke halaman login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pemilik') {
    header('Location: ../../login.php');
    exit;
}

// Ambil ID pemilik dari session
$pemilik_id = $_SESSION['user_id'];
// Ambil filter status dari URL, default 'all' jika tidak ada
$status_filter = $_GET['status'] ?? 'all';

// Query utama untuk mengambil data pemesanan
// Mengambil data dari tabel pemesanan, kos, daerah, users, dan pembayaran (LEFT JOIN)
// Pastikan mengambil alasan_penolakan dari tabel pembayaran
$query = "SELECT p.*, k.nama_kos, k.alamat, k.foto_utama, k.deskripsi, k.fasilitas, k.id as kos_id, 
                 d.nama as nama_daerah,
                 u.nama as nama_pencari, u.telepon, u.email, 
                 pb.id as pembayaran_id, pb.status_pembayaran, pb.bukti_bayar,
                 pb.tanggal_bayar, pb.metode_pembayaran, pb.alasan_penolakan,
                 k.status as status_kos
          FROM pemesanan p 
          JOIN kos k ON p.kos_id = k.id 
          JOIN daerah d ON k.daerah_id = d.id
          JOIN users u ON p.pencari_id = u.id 
          LEFT JOIN pembayaran pb ON p.id = pb.pemesanan_id
          WHERE p.pemilik_id = ?";

$params = [$pemilik_id];

// Tambahkan filter berdasarkan status jika bukan 'all'
if ($status_filter !== 'all') {
    $query .= " AND p.status = ?";
    $params[] = $status_filter;
}

// Urutkan berdasarkan tanggal pemesanan terbaru
$query .= " ORDER BY p.tanggal_pemesanan DESC";

// Eksekusi query dengan parameter
$stmt = $conn->prepare($query);
$stmt->execute($params);
$pemesanan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk statistik pemesanan
// Menghitung total, status menunggu, dikonfirmasi, ditolak, selesai, dan status pembayaran
$query_stats = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN p.status = 'menunggu' THEN 1 ELSE 0 END) as menunggu,
    SUM(CASE WHEN p.status = 'dikonfirmasi' THEN 1 ELSE 0 END) as dikonfirmasi,
    SUM(CASE WHEN p.status = 'ditolak' THEN 1 ELSE 0 END) as ditolak,
    SUM(CASE WHEN p.status = 'selesai' THEN 1 ELSE 0 END) as selesai,
    SUM(CASE WHEN pb.status_pembayaran = 'menunggu' AND p.status = 'dikonfirmasi' THEN 1 ELSE 0 END) as menunggu_bayar,
    SUM(CASE WHEN pb.status_pembayaran = 'lunas' AND p.status = 'dikonfirmasi' THEN 1 ELSE 0 END) as lunas
    FROM pemesanan p 
    LEFT JOIN pembayaran pb ON p.id = pb.pemesanan_id
    WHERE p.pemilik_id = ?";
$stmt_stats = $conn->prepare($query_stats);
$stmt_stats->execute([$pemilik_id]);
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

// Proses aksi form jika request method adalah POST (form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pemesanan_id = $_POST['pemesanan_id'];
    $action = $_POST['action'];

    try {
        // Ambil kos_id dari pemesanan untuk update status kos
        $stmt_kos = $conn->prepare("SELECT kos_id FROM pemesanan WHERE id = ?");
        $stmt_kos->execute([$pemesanan_id]);
        $kos_data = $stmt_kos->fetch(PDO::FETCH_ASSOC);

        if ($kos_data) {
            // Mulai transaction untuk menjaga konsistensi data
            $conn->beginTransaction();

            // Aksi konfirmasi pemesanan
            if ($action === 'konfirmasi') {
                // Update status pemesanan menjadi 'dikonfirmasi'
                $conn->prepare("UPDATE pemesanan SET status='dikonfirmasi' 
                                WHERE id=? AND pemilik_id=?")
                    ->execute([$pemesanan_id, $pemilik_id]);

                // Update status kos menjadi 'dipesan'
                $conn->prepare("UPDATE kos SET status='dipesan' WHERE id=?")
                    ->execute([$kos_data['kos_id']]);

                // Cek apakah sudah ada data pembayaran
                $stmt_check = $conn->prepare("SELECT id FROM pembayaran WHERE pemesanan_id=?");
                $stmt_check->execute([$pemesanan_id]);

                // Jika belum ada, buat data pembayaran baru
                if (!$stmt_check->fetch()) {
                    $stmt_pemesanan = $conn->prepare("SELECT total_harga FROM pemesanan WHERE id=?");
                    $stmt_pemesanan->execute([$pemesanan_id]);
                    $harga = $stmt_pemesanan->fetchColumn();

                    $conn->prepare("INSERT INTO pembayaran 
                                    (pemesanan_id, jumlah_bayar, metode_pembayaran, status_pembayaran)
                                    VALUES (?, ?, 'transfer', 'menunggu')")
                        ->execute([$pemesanan_id, $harga]);
                }

                $_SESSION['success'] = "Berhasil konfirmasi pemesanan!";
            
            // Aksi tolak pemesanan
            } elseif ($action === 'tolak') {
                $alasan = $_POST['alasan_penolakan'] ?? '';

                // Update status pemesanan menjadi 'ditolak' dan simpan catatan
                $conn->prepare("UPDATE pemesanan 
                                SET status='ditolak', catatan_pembatalan=? 
                                WHERE id=? AND pemilik_id=?")
                    ->execute([$alasan, $pemesanan_id, $pemilik_id]);

                // Update status kos kembali menjadi 'tersedia'
                $conn->prepare("UPDATE kos SET status='tersedia' WHERE id=?")
                    ->execute([$kos_data['kos_id']]);

                $_SESSION['success'] = "Pemesanan ditolak!";
            
            // Aksi selesaikan penyewaan
            } elseif ($action === 'selesai') {
                // Update status pemesanan menjadi 'selesai'
                $conn->prepare("UPDATE pemesanan SET status='selesai', status_penyewaan='selesai'
                                WHERE id=? AND pemilik_id=?")
                    ->execute([$pemesanan_id, $pemilik_id]);

                // Update status kos kembali menjadi 'tersedia'
                $conn->prepare("UPDATE kos SET status='tersedia' WHERE id=?")
                    ->execute([$kos_data['kos_id']]);

                $_SESSION['success'] = "Kos kini tersedia kembali.";
            
            // Aksi verifikasi pembayaran
            } elseif ($action === 'verifikasi_pembayaran') {
                $pembayaran_id = $_POST['pembayaran_id'];
                $verifikasi_action = $_POST['verifikasi_action'];

                // Terima pembayaran
                if ($verifikasi_action === 'terima') {
                    $status_bayar = 'lunas';
                    $alasan = null;

                    // Update status pembayaran menjadi 'lunas'
                    $conn->prepare("UPDATE pembayaran SET status_pembayaran=?, alasan_penolakan=? WHERE id=?")
                        ->execute([$status_bayar, $alasan, $pembayaran_id]);

                    // Update status pembayaran di pemesanan
                    $conn->prepare("UPDATE pemesanan SET status_pembayaran=? WHERE id=?")
                        ->execute([$status_bayar, $pemesanan_id]);

                    $_SESSION['success'] = "Pembayaran diterima!";
                
                // Tolak pembayaran
                } elseif ($verifikasi_action === 'tolak') {
                    $status_bayar = 'gagal';
                    $alasan = $_POST['alasan_penolakan'] ?? '';

                    // Update pembayaran dengan status 'gagal' dan alasan penolakan
                    $conn->prepare("UPDATE pembayaran SET status_pembayaran=?, alasan_penolakan=? WHERE id=?")
                        ->execute([$status_bayar, $alasan, $pembayaran_id]);

                    // Update status pemesanan menjadi 'ditolak'
                    $conn->prepare("UPDATE pemesanan SET status_pembayaran=?, status='ditolak', catatan_pembatalan=? WHERE id=?")
                        ->execute([$status_bayar, $alasan, $pemesanan_id]);

                    // Update status kos kembali menjadi 'tersedia'
                    $conn->prepare("UPDATE kos SET status='tersedia' WHERE id=?")
                        ->execute([$kos_data['kos_id']]);

                    $_SESSION['success'] = "Pembayaran ditolak dan kos tersedia kembali!";
                }
            }

            // Commit semua perubahan ke database
            $conn->commit();
        }

        // Redirect ke halaman pemesanan setelah proses selesai
        header("Location: pemesanan.php");
        exit;
    } catch (Exception $e) {
        // Rollback jika terjadi error
        $conn->rollback();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

// Tampilkan notifikasi success jika ada
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']); // Hapus dari session setelah ditampilkan
}

// Tampilkan notifikasi error jika ada
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']); // Hapus dari session setelah ditampilkan
}
