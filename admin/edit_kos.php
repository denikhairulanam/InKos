<?php
include '../includes/auth.php';
checkAuth();

if (getUserRole() !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "Edit Kos - INKOS";
include '../includes/header.php';

include '../config.php';
$database = new Database();
$db = $database->getConnection();

// Get kos data
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: kos.php');
    exit();
}

try {
    $query = "SELECT * FROM kos WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $kos = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$kos) {
        $_SESSION['error_message'] = "Kos tidak ditemukan";
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
$users_query = "SELECT id, nama FROM users WHERE role = 'pemilik' ORDER BY nama";
$users_stmt = $db->query($users_query);
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

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
    'Listrik Included'
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
    $user_id = $_POST['user_id'];
    $status = $_POST['status'];
    $featured = isset($_POST['featured']) ? 1 : 0;

    // Handle facilities
    $fasilitas = isset($_POST['fasilitas']) ? $_POST['fasilitas'] : [];
    $fasilitas_json = !empty($fasilitas) ? json_encode($fasilitas) : null;

    // Handle file uploads (you can implement this later)
    // $foto_utama = handleFileUpload('foto_utama');
    // $foto_lainnya = handleMultipleFileUpload('foto_lainnya');

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
                  user_id = :user_id, 
                  status = :status, 
                  featured = :featured 
                  WHERE id = :id";

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
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':featured', $featured);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Kos berhasil diperbarui";
            header('Location: kos.php');
            exit();
        } else {
            $error_message = "Gagal memperbarui kos";
        }
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>
<?php include '../includes/admin_header.php'; ?>
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

    <div class="card border-0 shadow">
        <div class="card-header bg-white">
            <h5 class="mb-0">Edit Data Kos</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="edit_kos.php?id=<?php echo $id; ?>" id="kosForm">
                <div class="row g-3">
                    <!-- Basic Information -->
                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Informasi Dasar</h6>
                    </div>

                    <div class="col-md-6">
                        <label for="nama_kos" class="form-label">Nama Kos <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_kos" name="nama_kos" required
                            value="<?php echo htmlspecialchars($kos['nama_kos']); ?>"
                            placeholder="Contoh: Kos Mawar Indah">
                    </div>

                    <div class="col-md-6">
                        <label for="user_id" class="form-label">Pemilik Kos <span class="text-danger">*</span></label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Pilih Pemilik</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>"
                                    <?php echo $user['id'] == $kos['user_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['nama']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4"
                            placeholder="Deskripsi lengkap tentang kos..."><?php echo htmlspecialchars($kos['deskripsi']); ?></textarea>
                    </div>

                    <!-- Location Information -->
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mt-4">Informasi Lokasi</h6>
                    </div>

                    <div class="col-md-6">
                        <label for="daerah_id" class="form-label">Daerah</label>
                        <select class="form-select" id="daerah_id" name="daerah_id">
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
                                placeholder="Contoh: 1500000" min="0">
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="alamat" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required
                            placeholder="Alamat lengkap kos..."><?php echo htmlspecialchars($kos['alamat']); ?></textarea>
                    </div>

                    <!-- Room Information -->
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mt-4">Informasi Kamar</h6>
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
                    </div>

                    <div class="col-md-4">
                        <label for="kamar_mandi" class="form-label">Kamar Mandi <span class="text-danger">*</span></label>
                        <select class="form-select" id="kamar_mandi" name="kamar_mandi" required>
                            <option value="">Pilih Tipe</option>
                            <option value="dalam" <?php echo $kos['kamar_mandi'] == 'dalam' ? 'selected' : ''; ?>>Dalam</option>
                            <option value="luar" <?php echo $kos['kamar_mandi'] == 'luar' ? 'selected' : ''; ?>>Luar</option>
                        </select>
                    </div>

                    <!-- Facilities -->
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mt-4">Fasilitas</h6>
                        <div class="row g-2" id="facilities_container">
                            <?php foreach ($common_facilities as $facility): ?>
                                <div class="col-md-4">
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
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mt-4">Informasi Tambahan</h6>
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="tersedia" <?php echo $kos['status'] == 'tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                            <option value="tidak_tersedia" <?php echo $kos['status'] == 'tidak_tersedia' ? 'selected' : ''; ?>>Tidak Tersedia</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1"
                                <?php echo $kos['featured'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="featured">
                                Jadikan Featured
                            </label>
                            <div class="form-text">Kos featured akan ditampilkan di halaman utama</div>
                        </div>
                    </div>

                    <!-- System Information -->
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Informasi Sistem</h6>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <small class="text-muted">ID Kos</small>
                                        <p class="mb-2"><code><?php echo $kos['id']; ?></code></p>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Views</small>
                                        <p class="mb-2"><?php echo number_format($kos['views']); ?></p>
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
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="kos.php" class="btn btn-secondary me-md-2">Batal</a>
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
</div>
</div>

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
            newFacility.className = 'col-md-4';
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
        }
    }

    // Allow Enter key to add custom facility
    document.getElementById('custom_facility').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addCustomFacility();
        }
    });
</script>

<?php include '../includes/footer.php'; ?>