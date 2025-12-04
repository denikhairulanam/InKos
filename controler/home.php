<?php
class HomePage
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Method untuk mendapatkan kos featured
    public function getFeaturedKos()
    {
        $query = "SELECT k.*, d.nama as nama_daerah, d.kota
                  FROM kos k
                  LEFT JOIN daerah d ON k.daerah_id = d.id
                  WHERE k.featured = 1 AND k.status = 'tersedia'
                  ORDER BY k.created_at DESC
                  LIMIT 6";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error getFeaturedKos: " . $e->getMessage());
            return false;
        }
    }

    // Method untuk mencari kos dengan keyword
    public function searchKosByKeyword($keyword = '')
    {
        $query = "SELECT k.*, d.nama as nama_daerah, d.kota
                  FROM kos k
                  LEFT JOIN daerah d ON k.daerah_id = d.id
                  WHERE k.status = 'tersedia'";

        $params = [];

        if (!empty($keyword)) {
            $query .= " AND (k.nama_kos LIKE ? OR k.alamat LIKE ? OR k.deskripsi LIKE ? OR d.nama LIKE ?)";
            $searchTerm = "%$keyword%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $query .= " ORDER BY k.created_at DESC LIMIT 12";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error searchKosByKeyword: " . $e->getMessage());
            return false;
        }
    }

    // Method untuk mencari kos dengan filter
    public function searchKosWithFilters($daerah_id = '', $tipe_kos = '', $harga_max = '')
    {
        $query = "SELECT k.*, d.nama as nama_daerah, d.kota
                  FROM kos k
                  LEFT JOIN daerah d ON k.daerah_id = d.id
                  WHERE k.status = 'tersedia'";

        $params = [];

        if (!empty($daerah_id)) {
            $query .= " AND k.daerah_id = ?";
            $params[] = $daerah_id;
        }

        if (!empty($tipe_kos)) {
            $query .= " AND k.tipe_kos = ?";
            $params[] = $tipe_kos;
        }

        if (!empty($harga_max)) {
            $query .= " AND k.harga_bulanan <= ?";
            $params[] = $harga_max;
        }

        $query .= " ORDER BY k.created_at DESC LIMIT 12";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error searchKosWithFilters: " . $e->getMessage());
            return false;
        }
    }
    // Method untuk mendapatkan daerah populer
    public function getPopularDistricts()
    {
        $query = "SELECT d.*, COUNT(k.id) as jumlah_kos
                  FROM daerah d
                  LEFT JOIN kos k ON d.id = k.daerah_id AND k.status = 'tersedia'
                  GROUP BY d.id
                  ORDER BY jumlah_kos DESC, d.nama ASC
                  LIMIT 8";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error getPopularDistricts: " . $e->getMessage());
            return false;
        }
    }

    // Method untuk mendapatkan semua daerah
    public function getAllDistricts()
    {
        $query = "SELECT * FROM daerah ORDER BY kota, nama";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error getAllDistricts: " . $e->getMessage());
            return false;
        }
    }
    // Method untuk mendapatkan jumlah daerah
    public function getDistrictCount()
    {
        $query = "SELECT COUNT(*) as total FROM daerah";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['total'] : 0;
        } catch (PDOException $e) {
            error_log("Error getDistrictCount: " . $e->getMessage());
            return 0;
        }
    }
}

// Inisialisasi database
$database = new Database();
$db = $database->getConnection();
$homePage = new HomePage($db);

// Ambil parameter pencarian
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_daerah = isset($_GET['daerah']) ? $_GET['daerah'] : '';
$search_tipe = isset($_GET['tipe']) ? $_GET['tipe'] : '';
$search_harga = isset($_GET['harga']) ? $_GET['harga'] : '';

// Inisialisasi variabel
$featuredKos = [];
$searchResults = [];
$is_searching = false;

// Cek apakah ada pencarian
if (!empty($search_keyword)) {
    // Pencarian dengan keyword
    $is_searching = true;
    $stmt = $homePage->searchKosByKeyword($search_keyword);
    if ($stmt) {
        $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} elseif (!empty($search_daerah) || !empty($search_tipe) || !empty($search_harga)) {
    // Pencarian dengan filter
    $is_searching = true;
    $stmt = $homePage->searchKosWithFilters($search_daerah, $search_tipe, $search_harga);
    if ($stmt) {
        $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    // Tampilkan featured kos
    $stmt = $homePage->getFeaturedKos();
    if ($stmt) {
        $featuredKos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Ambil data untuk filter
$popularDistricts = [];
$allDistricts = [];

$stmt_popular = $homePage->getPopularDistricts();
if ($stmt_popular) {
    $popularDistricts = $stmt_popular->fetchAll(PDO::FETCH_ASSOC);
}

$stmt_all = $homePage->getAllDistricts();
if ($stmt_all) {
    $allDistricts = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
}