<?php
include '../includes/auth.php';
checkAuth();

if (getUserRole() !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "Detail Laporan - INKOS";
include '../includes/header.php';

include '../config.php';
$database = new Database();
$db = $database->getConnection();

// Get laporan data
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: laporan.php');
    exit();
}

try {
    // Get main laporan data
    $query = "SELECT l.*, u.nama as user_nama, u.email as user_email, 
                     u.foto_profil as user_foto, u.telepon as user_telepon,
                     a.nama as admin_nama
              FROM laporan l 
              LEFT JOIN users u ON l.user_id = u.id 
              LEFT JOIN users a ON l.admin_id = a.id 
              WHERE l.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $laporan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$laporan) {
        $_SESSION['error_message'] = "Laporan tidak ditemukan";
        header('Location: laporan.php');
        exit();
    }

    // Mark as read by admin
    $update_query = "UPDATE laporan SET dibaca_admin = 1 WHERE id = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':id', $id);
    $update_stmt->execute();

    // Mark all replies as read
    $update_replies_query = "UPDATE balasan_laporan SET dibaca = 1 WHERE laporan_id = :id AND pengirim_tipe = 'user'";
    $update_replies_stmt = $db->prepare($update_replies_query);
    $update_replies_stmt->bindParam(':id', $id);
    $update_replies_stmt->execute();

    // Get all messages
    $messages_query = "SELECT bl.*, 
                      CASE 
                          WHEN bl.pengirim_tipe = 'user' THEN u.nama 
                          ELSE a.nama 
                      END as pengirim_nama,
                      CASE 
                          WHEN bl.pengirim_tipe = 'user' THEN u.foto_profil 
                          ELSE a.foto_profil 
                      END as pengirim_foto
               FROM balasan_laporan bl
               LEFT JOIN users u ON (bl.pengirim_tipe = 'user' AND bl.pengirim_id = u.id)
               LEFT JOIN users a ON (bl.pengirim_tipe = 'admin' AND bl.pengirim_id = a.id)
               WHERE bl.laporan_id = :id 
               ORDER BY bl.created_at ASC";
    $messages_stmt = $db->prepare($messages_query);
    $messages_stmt->bindParam(':id', $id);
    $messages_stmt->execute();
    $messages = $messages_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header('Location: laporan.php');
    exit();
}

// Handle new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesan'])) {
    $pesan = trim($_POST['pesan']);

    if (!empty($pesan)) {
        try {
            // Insert new message
            $insert_query = "INSERT INTO balasan_laporan (laporan_id, pengirim_id, pengirim_tipe, pesan) 
                            VALUES (:laporan_id, :pengirim_id, 'admin', :pesan)";
            $insert_stmt = $db->prepare($insert_query);
            $insert_stmt->bindParam(':laporan_id', $id);
            $insert_stmt->bindParam(':pengirim_id', $_SESSION['user_id']);
            $insert_stmt->bindParam(':pesan', $pesan);

            if ($insert_stmt->execute()) {
                // Update laporan status and timestamp
                $update_laporan_query = "UPDATE laporan SET status = 'dibalas', admin_id = :admin_id, updated_at = NOW() WHERE id = :id";
                $update_laporan_stmt = $db->prepare($update_laporan_query);
                $update_laporan_stmt->bindParam(':admin_id', $_SESSION['user_id']);
                $update_laporan_stmt->bindParam(':id', $id);
                $update_laporan_stmt->execute();

                $_SESSION['success_message'] = "Pesan berhasil dikirim";
                header('Location: detail_laporan.php?id=' . $id);
                exit();
            }
        } catch (PDOException $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $status = $_POST['status'];

    try {
        $query = "UPDATE laporan SET status = :status, admin_id = :admin_id WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':admin_id', $_SESSION['user_id']);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Status laporan berhasil diperbarui";
            header('Location: detail_laporan.php?id=' . $id);
            exit();
        }
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!-- Main Content -->
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3"><i class="fas fa-comments me-2"></i>Chat Laporan</h2>
        <div class="btn-group">
            <a href="laporan.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Chat Messages -->
        <div class="col-lg-8">
            <div class="card border-0 shadow">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <?php if ($laporan['user_foto']): ?>
                                <img src="../uploads/profiles/<?php echo htmlspecialchars($laporan['user_foto']); ?>"
                                    class="rounded-circle me-3" width="50" height="50" alt="User">
                            <?php else: ?>
                                <div class="bg-secondary rounded-circle me-3 d-flex align-items-center justify-content-center"
                                    style="width: 50px; height: 50px;">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h6 class="mb-0"><?php echo htmlspecialchars($laporan['user_nama']); ?></h6>
                                <small class="text-muted"><?php echo htmlspecialchars($laporan['user_email']); ?></small>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge 
                                        <?php echo $laporan['tipe'] == 'laporan' ? 'bg-primary' : ($laporan['tipe'] == 'pertanyaan' ? 'bg-info' : ($laporan['tipe'] == 'keluhan' ? 'bg-danger' : 'bg-secondary')); ?>">
                                <?php echo ucfirst($laporan['tipe']); ?>
                            </span>
                            <form method="POST" class="d-inline ms-2">
                                <input type="hidden" name="update_status" value="1">
                                <select name="status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                    <option value="baru" <?php echo $laporan['status'] == 'baru' ? 'selected' : ''; ?>>Baru</option>
                                    <option value="dibalas" <?php echo $laporan['status'] == 'dibalas' ? 'selected' : ''; ?>>Dibalas</option>
                                    <option value="selesai" <?php echo $laporan['status'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Chat Messages Area -->
                <div class="card-body" style="max-height: 500px; overflow-y: auto;" id="chatMessages">
                    <!-- Original Report -->
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <?php if ($laporan['user_foto']): ?>
                                <img src="../uploads/profiles/<?php echo htmlspecialchars($laporan['user_foto']); ?>"
                                    class="rounded-circle" width="40" height="40" alt="User">
                            <?php else: ?>
                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center"
                                    style="width: 40px; height: 40px;">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="bg-light rounded p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong><?php echo htmlspecialchars($laporan['user_nama']); ?></strong>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y H:i', strtotime($laporan['created_at'])); ?>
                                    </small>
                                </div>
                                <h6 class="text-primary"><?php echo htmlspecialchars($laporan['judul']); ?></h6>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($laporan['pesan'])); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Messages -->
                    <?php foreach ($messages as $message): ?>
                        <div class="d-flex mb-4 <?php echo $message['pengirim_tipe'] == 'admin' ? 'flex-row-reverse' : ''; ?>">
                            <div class="flex-shrink-0">
                                <?php if ($message['pengirim_foto']): ?>
                                    <img src="../uploads/profiles/<?php echo htmlspecialchars($message['pengirim_foto']); ?>"
                                        class="rounded-circle" width="40" height="40" alt="User">
                                <?php else: ?>
                                    <div class="<?php echo $message['pengirim_tipe'] == 'admin' ? 'bg-primary' : 'bg-secondary'; ?> rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-<?php echo $message['pengirim_tipe'] == 'admin' ? 'user-shield' : 'user'; ?> text-white"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1 <?php echo $message['pengirim_tipe'] == 'admin' ? 'me-3 text-end' : 'ms-3'; ?>">
                                <div class="<?php echo $message['pengirim_tipe'] == 'admin' ? 'bg-primary text-white' : 'bg-light'; ?> rounded p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong><?php echo htmlspecialchars($message['pengirim_nama']); ?></strong>
                                        <small class="<?php echo $message['pengirim_tipe'] == 'admin' ? 'text-white-50' : 'text-muted'; ?>">
                                            <?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?>
                                        </small>
                                    </div>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($message['pesan'])); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($messages)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-comments fa-2x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada balasan</p>
                            <small class="text-muted">Jadilah yang pertama membalas laporan ini</small>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Message Input -->
                <div class="card-footer bg-white">
                    <form method="POST" id="messageForm">
                        <div class="input-group">
                            <textarea class="form-control" name="pesan" placeholder="Ketik balasan..."
                                rows="2" required style="resize: none;"></textarea>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <div class="form-text">
                            Tekan Enter untuk mengirim, Shift+Enter untuk baris baru
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Information -->
        <div class="col-lg-4">
            <!-- User Information -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Informasi User</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <?php if ($laporan['user_foto']): ?>
                            <img src="../uploads/profiles/<?php echo htmlspecialchars($laporan['user_foto']); ?>"
                                class="rounded-circle mb-3" width="80" height="80" alt="User">
                        <?php else: ?>
                            <div class="bg-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                                style="width: 80px; height: 80px;">
                                <i class="fas fa-user text-white fa-2x"></i>
                            </div>
                        <?php endif; ?>
                        <h6><?php echo htmlspecialchars($laporan['user_nama']); ?></h6>
                        <p class="text-muted mb-2"><?php echo htmlspecialchars($laporan['user_email']); ?></p>
                        <?php if ($laporan['user_telepon']): ?>
                            <p class="text-muted mb-0"><?php echo htmlspecialchars($laporan['user_telepon']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Report Information -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Informasi Laporan</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-12">
                            <small class="text-muted">ID Laporan</small>
                            <p class="mb-2"><code>#<?php echo $laporan['id']; ?></code></p>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Tipe</small>
                            <p class="mb-2">
                                <span class="badge 
                                            <?php echo $laporan['tipe'] == 'laporan' ? 'bg-primary' : ($laporan['tipe'] == 'pertanyaan' ? 'bg-info' : ($laporan['tipe'] == 'keluhan' ? 'bg-danger' : 'bg-secondary')); ?>">
                                    <?php echo ucfirst($laporan['tipe']); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Status</small>
                            <p class="mb-2">
                                <span class="badge 
                                            <?php echo $laporan['status'] == 'baru' ? 'bg-warning' : ($laporan['status'] == 'dibalas' ? 'bg-success' : 'bg-secondary'); ?>">
                                    <?php echo ucfirst($laporan['status']); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Dibuat</small>
                            <p class="mb-2"><?php echo date('d/m/Y H:i', strtotime($laporan['created_at'])); ?></p>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Terakhir Update</small>
                            <p class="mb-0"><?php echo date('d/m/Y H:i', strtotime($laporan['updated_at'])); ?></p>
                        </div>
                        <?php if ($laporan['admin_nama']): ?>
                            <div class="col-12">
                                <small class="text-muted">Ditangani oleh</small>
                                <p class="mb-0"><?php echo htmlspecialchars($laporan['admin_nama']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Aksi Cepat</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="mailto:<?php echo htmlspecialchars($laporan['user_email']); ?>"
                            class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-2"></i>Email User
                        </a>
                        <?php if ($laporan['user_telepon']): ?>
                            <a href="https://wa.me/<?php echo htmlspecialchars($laporan['user_telepon']); ?>"
                                target="_blank" class="btn btn-outline-success">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp
                            </a>
                        <?php endif; ?>
                        <button type="button" class="btn btn-outline-info" onclick="scrollToBottom()">
                            <i class="fas fa-arrow-down me-2"></i>Scroll ke Bawah
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<script>
    // Auto scroll to bottom
    function scrollToBottom() {
        const chatMessages = document.getElementById('chatMessages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Auto scroll on page load
    document.addEventListener('DOMContentLoaded', function() {
        scrollToBottom();
    });

    // Handle Enter key for message form
    document.querySelector('textarea[name="pesan"]').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('messageForm').submit();
        }
    });

    // Auto-resize textarea
    document.querySelector('textarea[name="pesan"]').addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
</script>

<?php include '../includes/footer.php'; ?>