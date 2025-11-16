<footer class="footer mt-5 py-4">
    <div class="container container-main">
        <div class="row">
            <div class="col-md-6">
                <p class="text-muted mb-0">&copy; <?php echo date('Y'); ?> INKOS - Sistem Informasi Kos. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="text-muted mb-0">Welcome, <?php echo htmlspecialchars($_SESSION['nama'] ?? 'Pemilik'); ?>!</p>
            </div>
        </div>
    </div>
</footer>