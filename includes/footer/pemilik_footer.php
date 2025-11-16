<?php
// includes/pemilik_footer.php
?>
</div> <!-- Close col-12 -->
</div> <!-- Close row -->
</div> <!-- Close container -->

<!-- Footer -->
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

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto close mobile menu when clicking on links
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
        const navbarCollapse = document.getElementById('pemilikNavbar');

        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    }
                }
            });
        });

        // Add active state based on current page
        const currentPage = window.location.pathname.split('/').pop();
        navLinks.forEach(link => {
            const linkHref = link.getAttribute('href');
            if (linkHref === currentPage) {
                link.classList.add('active', 'text-primary', 'fw-bold');
            }
        });

        // Auto-dismiss alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });

        // Handle image errors for profile pictures
        const profileImages = document.querySelectorAll('.user-avatar');
        profileImages.forEach(function(img) {
            img.addEventListener('error', function() {
                this.style.display = 'none';
                const fallback = this.nextElementSibling;
                if (fallback && fallback.classList.contains('profile-image-fallback')) {
                    fallback.style.display = 'flex';
                }
            });
        });
    });

    // Initialize Bootstrap components
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
    var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl)
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            const navbarCollapse = document.getElementById('pemilikNavbar');
            if (navbarCollapse.classList.contains('show')) {
                const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                if (bsCollapse) {
                    bsCollapse.hide();
                }
            }
        }
    }); 
</script>
</body>

</html>