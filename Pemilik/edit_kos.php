<?php
ob_start(); // Start output buffering
include '../includes/auth.php';
include '../includes/pemilik_header.php';
checkAuth();

if (getUserRole() !== 'pemilik') {
    header('Location: ../login.php');
    exit();
}

$page_title = "Edit Kos - INKOS";

include '../config.php';
$database = new Database();
$db = $database->getConnection();

// Get kos data - hanya kos milik pemilik yang login
$id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$id) {
    header('Location: kos.php');
    exit();
}

try {
    $query = "SELECT * FROM kos WHERE id = :id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $kos = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$kos) {
        $_SESSION['error_message'] = "Kos tidak ditemukan atau Anda tidak memiliki akses";
        header('Location: kos.php');
        exit();
    }

    // Parse JSON fields
    $fasilitas = $kos['fasilitas'] ? json_decode($kos['fasilitas'], true) : [];
    $foto_lainnya = $kos['foto_lainnya'] ? json_decode($kos['foto_lainnya'], true) : [];
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header('Location: kos.php');
    exit();
}

// Get data for dropdowns
$daerah_query = "SELECT id, nama, kota FROM daerah ORDER BY nama";
$daerah_stmt = $db->query($daerah_query);
$daerah_list = $daerah_stmt->fetchAll(PDO::FETCH_ASSOC);

// Common facilities
$common_facilities = [
    'WiFi',
    'AC',
    'Kipas Angin',
    'Lemari',
    'Kasur',
    'Meja',
    'Kursi',
    'Kamar Mandi Dalam',
    'Kamar Mandi Luar',
    'Dapur',
    'Laundry',
    'Parkir Motor',
    'Parkir Mobil',
    'Security',
    'CCTV',
    'Listrik Included',
    'Air Panas',
    'Ruang Tamu',
    'Balkon',
    'Tempat Jemur'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kos = $_POST['nama_kos'];
    $deskripsi = $_POST['deskripsi'];
    $alamat = $_POST['alamat'];
    $daerah_id = $_POST['daerah_id'] ?: null;
    $harga_bulanan = $_POST['harga_bulanan'];
    $tipe_kos = $_POST['tipe_kos'];
    $ukuran_kamar = $_POST['ukuran_kamar'];
    $kamar_mandi = $_POST['kamar_mandi'];
    $status = $_POST['status'];
    $featured = isset($_POST['featured']) ? 1 : 0;

    // Handle facilities
    $fasilitas = isset($_POST['fasilitas']) ? $_POST['fasilitas'] : [];
    $fasilitas_json = !empty($fasilitas) ? json_encode($fasilitas) : null;

    // Handle file uploads
    $upload_errors = [];

    // Handle foto utama upload
    $foto_utama = $kos['foto_utama'];
    if (isset($_FILES['foto_utama']) && $_FILES['foto_utama']['error'] === 0) {
        $foto_utama = handleFileUpload('foto_utama', $upload_errors);
        if ($foto_utama === null) {
            $foto_utama = $kos['foto_utama']; // Keep old photo if upload fails
        }
    }

    // Handle foto lainnya upload
    $foto_lainnya_array = $foto_lainnya;
    if (isset($_FILES['foto_lainnya']) && !empty($_FILES['foto_lainnya']['name'][0])) {
        $new_fotos = handleMultipleFileUpload('foto_lainnya', $upload_errors);
        if (!empty($new_fotos)) {
            $foto_lainnya_array = array_merge($foto_lainnya_array, $new_fotos);
        }
    }
    $foto_lainnya_json = !empty($foto_lainnya_array) ? json_encode($foto_lainnya_array) : null;

    try {
        $query = "UPDATE kos SET 
                  nama_kos = :nama_kos, 
                  deskripsi = :deskripsi, 
                  alamat = :alamat, 
                  daerah_id = :daerah_id, 
                  harga_bulanan = :harga_bulanan, 
                  tipe_kos = :tipe_kos, 
                  ukuran_kamar = :ukuran_kamar, 
                  kamar_mandi = :kamar_mandi, 
                  fasilitas = :fasilitas, 
                  foto_utama = :foto_utama,
                  foto_lainnya = :foto_lainnya,
                  status = :status, 
                  featured = :featured,
                  updated_at = NOW()
                  WHERE id = :id AND user_id = :user_id";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':nama_kos', $nama_kos);
        $stmt->bindParam(':deskripsi', $deskripsi);
        $stmt->bindParam(':alamat', $alamat);
        $stmt->bindParam(':daerah_id', $daerah_id);
        $stmt->bindParam(':harga_bulanan', $harga_bulanan);
        $stmt->bindParam(':tipe_kos', $tipe_kos);
        $stmt->bindParam(':ukuran_kamar', $ukuran_kamar);
        $stmt->bindParam(':kamar_mandi', $kamar_mandi);
        $stmt->bindParam(':fasilitas', $fasilitas_json);
        $stmt->bindParam(':foto_utama', $foto_utama);
        $stmt->bindParam(':foto_lainnya', $foto_lainnya_json);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':featured', $featured);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $user_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Kos berhasil diperbarui";
            header('Location: detail_kos.php?id=' . $id);
            ob_end_flush(); // Clean output buffer before redirect
            exit();
        } else {
            $error_message = "Gagal memperbarui kos";
        }
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// File upload functions
function handleFileUpload($field_name, &$errors)
{
    if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] !== 0) {
        return null;
    }

    $target_dir = "../uploads/";
    $file_name = basename($_FILES[$field_name]["name"]);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is actual image
    $check = getimagesize($_FILES[$field_name]["tmp_name"]);
    if ($check === false) {
        $errors[] = "File bukan gambar.";
        return null;
    }

    // Check file size (5MB max)
    if ($_FILES[$field_name]["size"] > 5000000) {
        $errors[] = "Ukuran file terlalu besar (maksimal 5MB).";
        return null;
    }

    // Allow certain file formats
    if (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
        $errors[] = "Hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
        return null;
    }

    // Generate unique filename
    $new_filename = uniqid() . '_' . $file_name;
    $target_file = $target_dir . $new_filename;

    if (move_uploaded_file($_FILES[$field_name]["tmp_name"], $target_file)) {
        return $new_filename;
    } else {
        $errors[] = "Terjadi kesalahan saat upload file.";
        return null;
    }
}

function handleMultipleFileUpload($field_name, &$errors)
{
    $uploaded_files = [];

    if (!isset($_FILES[$field_name]) || empty($_FILES[$field_name]['name'][0])) {
        return $uploaded_files;
    }

    $file_count = count($_FILES[$field_name]['name']);

    for ($i = 0; $i < $file_count; $i++) {
        if ($_FILES[$field_name]['error'][$i] === 0) {
            $target_dir = "../uploads/";
            $file_name = basename($_FILES[$field_name]["name"][$i]);
            $target_file = $target_dir . $file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if image file is actual image
            $check = getimagesize($_FILES[$field_name]["tmp_name"][$i]);
            if ($check === false) {
                $errors[] = "File " . $file_name . " bukan gambar.";
                continue;
            }

            // Check file size (5MB max)
            if ($_FILES[$field_name]["size"][$i] > 5000000) {
                $errors[] = "Ukuran file " . $file_name . " terlalu besar (maksimal 5MB).";
                continue;
            }

            // Allow certain file formats
            if (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
                $errors[] = "Hanya file JPG, JPEG, PNG & GIF yang diizinkan untuk " . $file_name;
                continue;
            }

            // Generate unique filename
            $new_filename = uniqid() . '_' . $file_name;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES[$field_name]["tmp_name"][$i], $target_file)) {
                $uploaded_files[] = $new_filename;
            } else {
                $errors[] = "Terjadi kesalahan saat upload file " . $file_name;
            }
        }
    }

    return $uploaded_files;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            color: #0d6efd;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #0d6efd;
        }

        .photo-preview {
            max-height: 150px;
            object-fit: cover;
        }

        .current-photos {
            border: 2px dashed #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
        }

        .facility-checkbox {
            margin-bottom: 0.5rem;
        }

        .system-info-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #dee2e6;
        }
    </style>
</head>

<body class="bg-light">
    <?php include '../includes/pemilik_header.php'; ?>

    <!-- Main Content -->
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3"><i class="fas fa-edit me-2"></i>Edit Kos</h2>
            <div class="btn-group">
                <a href="detail_kos.php?id=<?php echo $id; ?>" class="btn btn-outline-info">
                    <i class="fas fa-eye me-2"></i>Detail
                </a>
                <a href="kos.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <?php if (!empty($upload_errors)): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h6 class="alert-heading">Peringatan Upload:</h6>
                <ul class="mb-0">
                    <?php foreach ($upload_errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow">
            <div class="card-header bg-white">
                <h5 class="mb-0">Edit Data Kos - <?php echo htmlspecialchars($kos['nama_kos']); ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="edit_kos.php?id=<?php echo $id; ?>" id="kosForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <!-- Basic Information -->
                        <div class="col-12 form-section">
                            <h6 class="section-title">Informasi Dasar</h6>
                        </div>

                        <div class="col-md-12">
                            <label for="nama_kos" class="form-label">Nama Kos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_kos" name="nama_kos" required
                                value="<?php echo htmlspecialchars($kos['nama_kos']); ?>"
                                placeholder="Contoh: Kos Mawar Indah">
                        </div>

                        <div class="col-12">
                            <label for="deskripsi" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" required
                                placeholder="Deskripsi lengkap tentang kos, fasilitas, dan keunggulan..."><?php echo htmlspecialchars($kos['deskripsi']); ?></textarea>
                            <div class="form-text">Jelaskan secara detail tentang kos Anda untuk menarik calon penyewa</div>
                        </div>

                        <!-- Location Information -->
                        <div class="col-12 form-section">
                            <h6 class="section-title">Informasi Lokasi & Harga</h6>
                        </div>

                        <div class="col-md-6">
                            <label for="daerah_id" class="form-label">Daerah <span class="text-danger">*</span></label>
                            <select class="form-select" id="daerah_id" name="daerah_id" required>
                                <option value="">Pilih Daerah</option>
                                <?php foreach ($daerah_list as $daerah): ?>
                                    <option value="<?php echo $daerah['id']; ?>"
                                        <?php echo $daerah['id'] == $kos['daerah_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($daerah['nama']); ?> - <?php echo htmlspecialchars($daerah['kota']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="harga_bulanan" class="form-label">Harga Bulanan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="harga_bulanan" name="harga_bulanan" required
                                    value="<?php echo $kos['harga_bulanan']; ?>"
                                    placeholder="Contoh: 1500000" min="100000" step="50000">
                            </div>
                            <div class="form-text">Harga sewa per bulan dalam Rupiah</div>
                        </div>

                        <div class="col-12">
                            <label for="alamat" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" required
                                placeholder="Alamat lengkap kos (nama jalan, nomor, RT/RW, dll)..."><?php echo htmlspecialchars($kos['alamat']); ?></textarea>
                        </div>

                        <!-- Room Information -->
                        <div class="col-12 form-section">
                            <h6 class="section-title">Spesifikasi Kamar</h6>
                        </div>

                        <div class="col-md-4">
                            <label for="tipe_kos" class="form-label">Tipe Kos <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipe_kos" name="tipe_kos" required>
                                <option value="">Pilih Tipe</option>
                                <option value="putra" <?php echo $kos['tipe_kos'] == 'putra' ? 'selected' : ''; ?>>Putra</option>
                                <option value="putri" <?php echo $kos['tipe_kos'] == 'putri' ? 'selected' : ''; ?>>Putri</option>
                                <option value="campur" <?php echo $kos['tipe_kos'] == 'campur' ? 'selected' : ''; ?>>Campur</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="ukuran_kamar" class="form-label">Ukuran Kamar <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ukuran_kamar" name="ukuran_kamar" required
                                value="<?php echo htmlspecialchars($kos['ukuran_kamar']); ?>"
                                placeholder="Contoh: 3x4 meter">
                            <div class="form-text">Ukuran kamar dalam meter (contoh: 3x4, 4x4, dll)</div>
                        </div>

                        <div class="col-md-4">
                            <label for="kamar_mandi" class="form-label">Kamar Mandi <span class="text-danger">*</span></label>
                            <select class="form-select" id="kamar_mandi" name="kamar_mandi" required>
                                <option value="">Pilih Tipe</option>
                                <option value="dalam" <?php echo $kos['kamar_mandi'] == 'dalam' ? 'selected' : ''; ?>>Dalam</option>
                                <option value="luar" <?php echo $kos['kamar_mandi'] == 'luar' ? 'selected' : ''; ?>>Luar</option>
                            </select>
                        </div>

                        <!-- Photo Upload -->
                        <div class="col-12 form-section">
                            <h6 class="section-title">Foto Kos</h6>
                        </div>

                        <div class="col-md-6">
                            <label for="foto_utama" class="form-label">Foto Utama</label>
                            <input type="file" class="form-control" id="foto_utama" name="foto_utama" accept="image/*">
                            <div class="form-text">
                                <?php if ($kos['foto_utama']): ?>
                                    Foto saat ini: <a href="../uploads/<?php echo $kos['foto_utama']; ?>" target="_blank">Lihat Foto</a>
                                <?php else: ?>
                                    Belum ada foto utama
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="foto_lainnya" class="form-label">Foto Lainnya</label>
                            <input type="file" class="form-control" id="foto_lainnya" name="foto_lainnya[]" multiple accept="image/*">
                            <div class="form-text">
                                Pilih beberapa foto (maksimal 5MB per foto)
                                <?php if (!empty($foto_lainnya)): ?>
                                    <br>Total foto saat ini: <?php echo count($foto_lainnya); ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Current Photos Preview -->
                        <?php if (!empty($foto_lainnya)): ?>
                            <div class="col-12">
                                <label class="form-label">Foto Saat Ini</label>
                                <div class="current-photos">
                                    <div class="row g-2">
                                        <?php foreach ($foto_lainnya as $index => $foto): ?>
                                            <div class="col-md-3">
                                                <div class="card">
                                                    <img src="../uploads/<?php echo $foto; ?>" class="card-img-top photo-preview" alt="Foto <?php echo $index + 1; ?>">
                                                    <div class="card-body p-2 text-center">
                                                        <small class="text-muted">Foto <?php echo $index + 1; ?></small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Facilities -->
                        <div class="col-12 form-section">
                            <h6 class="section-title">Fasilitas</h6>
                            <p class="text-muted mb-3">Pilih fasilitas yang tersedia di kos Anda:</p>
                            <div class="row g-2" id="facilities_container">
                                <?php foreach ($common_facilities as $facility): ?>
                                    <div class="col-md-4 facility-checkbox">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="fasilitas[]"
                                                value="<?php echo htmlspecialchars($facility); ?>"
                                                id="facility_<?php echo preg_replace('/[^a-zA-Z0-9]/', '_', $facility); ?>"
                                                <?php echo in_array($facility, $fasilitas) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="facility_<?php echo preg_replace('/[^a-zA-Z0-9]/', '_', $facility); ?>">
                                                <?php echo htmlspecialchars($facility); ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Custom Facility Input -->
                            <div class="row mt-3">
                                <div class="col-md-8">
                                    <input type="text" class="form-control" id="custom_facility"
                                        placeholder="Tambah fasilitas custom...">
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-outline-primary w-100" onclick="addCustomFacility()">
                                        <i class="fas fa-plus me-2"></i>Tambah
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="col-12 form-section">
                            <h6 class="section-title">Status & Pengaturan</h6>
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label">Status Kos <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="tersedia" <?php echo $kos['status'] == 'tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                                <option value="tidak_tersedia" <?php echo $kos['status'] == 'tidak_tersedia' ? 'selected' : ''; ?>>Tidak Tersedia</option>
                            </select>
                            <div class="form-text">Status "Tidak Tersedia" akan menyembunyikan kos dari pencarian</div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1"
                                    <?php echo $kos['featured'] ? 'checked' : ''; ?>>
                                <label class="form-check-label fw-bold" for="featured">
                                    <i class="fas fa-star text-warning me-1"></i>Jadikan Featured
                                </label>
                                <div class="form-text">Kos featured akan ditampilkan di halaman utama dan mendapatkan prioritas lebih tinggi</div>
                            </div>
                        </div>

                        <!-- System Information -->
                        <div class="col-12">
                            <div class="card system-info-card">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">Informasi Sistem</h6>
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <small class="text-muted">ID Kos</small>
                                            <p class="mb-2"><code><?php echo $kos['id']; ?></code></p>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Views</small>
                                            <p class="mb-2"><?php echo number_format($kos['views']); ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Status</small>
                                            <p class="mb-2">
                                                <span class="badge <?php echo $kos['status'] == 'tersedia' ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo $kos['status'] == 'tersedia' ? 'Tersedia' : 'Tidak Tersedia'; ?>
                                                </span>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Dibuat</small>
                                            <p class="mb-2"><?php echo date('d/m/Y H:i', strtotime($kos['created_at'])); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Terakhir Update</small>
                                            <p class="mb-0"><?php echo date('d/m/Y H:i', strtotime($kos['updated_at'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end border-top pt-4">
                                <a href="detail_kos.php?id=<?php echo $id; ?>" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-times me-2"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Perbarui Kos
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Format harga input
        document.getElementById('harga_bulanan').addEventListener('input', function(e) {
            // Remove non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Add custom facility
        function addCustomFacility() {
            const customFacility = document.getElementById('custom_facility').value.trim();
            if (customFacility) {
                const facilitiesContainer = document.getElementById('facilities_container');
                const newId = 'facility_custom_' + Date.now();

                const newFacility = document.createElement('div');
                newFacility.className = 'col-md-4 facility-checkbox';
                newFacility.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="fasilitas[]" 
                               value="${customFacility}" id="${newId}" checked>
                        <label class="form-check-label" for="${newId}">
                            ${customFacility}
                        </label>
                    </div>
                `;

                facilitiesContainer.appendChild(newFacility);
                document.getElementById('custom_facility').value = '';

                // Show success message
                showToast('Fasilitas berhasil ditambahkan', 'success');
            }
        }

        // Allow Enter key to add custom facility
        document.getElementById('custom_facility').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addCustomFacility();
            }
        });

        // Show toast notification
        function showToast(message, type = 'info') {
            // Simple alert for now, can be replaced with proper toast
            alert(message);
        }

        // Form validation
        document.getElementById('kosForm').addEventListener('submit', function(e) {
            const harga = document.getElementById('harga_bulanan').value;
            if (harga < 100000) {
                e.preventDefault();
                alert('Harga bulanan minimal Rp 100,000');
                document.getElementById('harga_bulanan').focus();
            }
        });

        // Character counter for description
        const descTextarea = document.getElementById('deskripsi');
        const charCount = document.createElement('div');
        charCount.className = 'form-text text-end';
        descTextarea.parentNode.appendChild(charCount);

        descTextarea.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = `${count} karakter`;
            if (count > 1000) {
                charCount.className = 'form-text text-end text-danger';
            } else if (count > 500) {
                charCount.className = 'form-text text-end text-warning';
            } else {
                charCount.className = 'form-text text-end text-success';
            }
        });

        // Trigger initial count
        descTextarea.dispatchEvent(new Event('input'));

        // Photo preview for new uploads
        document.getElementById('foto_utama').addEventListener('change', function(e) {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // You can add preview functionality here if needed
                    console.log('Foto utama selected:', file.name);
                }
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('foto_lainnya').addEventListener('change', function(e) {
            const files = this.files;
            console.log(`${files.length} foto lainnya selected`);
        });
    </script>
</body>

</html>
<?php ob_end_flush(); // End output buffering and flush 
?>