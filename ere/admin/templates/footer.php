                </main>
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">
                                &copy; <?php echo date('Y'); ?> SportMağazası - Tüm Hakları Saklıdır.
                            </div>
                            <div>
                                <a href="#" class="text-decoration-none me-2 text-secondary">Gizlilik Politikası</a>
                                &middot;
                                <a href="#" class="text-decoration-none ms-2 text-secondary">Kullanım Şartları</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <!-- Bootstrap ve diğer JS dosyaları -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/2.0.0/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.0/js/dataTables.bootstrap5.min.js"></script>
    <!-- SummerNote Editor JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.body.classList.toggle('sb-sidenav-toggled');
                    document.querySelector('.sidebar').classList.toggle('collapsed');
                    document.querySelector('.content-wrapper').classList.toggle('expanded');
                });
            }
            
            // Aktif sayfayı belirle
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href === currentPage) {
                    link.classList.add('active');
                }
            });
            
            // Dropdown için tıklama işleyicisi
            const dropdowns = document.querySelectorAll('[data-bs-toggle="collapse"]');
            dropdowns.forEach(dropdown => {
                dropdown.addEventListener('click', function() {
                    const icon = this.querySelector('i.fas.fa-angle-down');
                    if (icon) {
                        icon.classList.toggle('rotate-icon');
                    }
                });
            });
        });
    </script>
</body>
</html> 