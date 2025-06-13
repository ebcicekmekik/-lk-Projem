        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5><i class="fas fa-running me-2"></i>SportMağazası</h5>
                    <p class="text-muted">En kaliteli spor malzemelerini en uygun fiyatlarla sunuyoruz.</p>
                    <div class="d-flex mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <h5>Kategoriler</h5>
                    <ul class="list-unstyled">
                        <?php
                        $kategoriler = tumKategorileriGetir();
                        $count = 0;
                        foreach ($kategoriler as $kategori) {
                            echo '<li><a href="/kategori.php?id=' . $kategori['id'] . '" class="text-muted">' . $kategori['ad'] . '</a></li>';
                            $count++;
                            if ($count >= 5) break; // Sadece 5 kategoriyi göster
                        }
                        ?>
                    </ul>
                </div>
                <div class="col-md-2 mb-3">
                    <h5>Bilgiler</h5>
                    <ul class="list-unstyled">
                        <li><a href="/hakkimizda.php" class="text-muted">Hakkımızda</a></li>
                        <li><a href="/iletisim.php" class="text-muted">İletişim</a></li>
                        <li><a href="/sss.php" class="text-muted">S.S.S</a></li>
                        <li><a href="/gizlilik-politikasi.php" class="text-muted">Gizlilik Politikası</a></li>
                        <li><a href="/iade-politikasi.php" class="text-muted">İade Koşulları</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Bültenimize Abone Olun</h5>
                    <p class="text-muted">En yeni ürünler ve kampanyalardan haberdar olun.</p>
                    <form action="/bulten-kayit.php" method="post" class="d-flex">
                        <input type="email" class="form-control me-2" placeholder="E-posta adresiniz" required>
                        <button type="submit" class="btn btn-primary">Abone Ol</button>
                    </form>
                    <div class="mt-3">
                        <p class="mb-0"><i class="fas fa-phone me-2"></i> +90 212 123 45 67</p>
                        <p class="mb-0"><i class="fas fa-envelope me-2"></i> info@spormagazasi.com</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-between align-items-center">
                <p class="m-0 text-muted">&copy; <?php echo date('Y'); ?> SportMağazası - Tüm Hakları Saklıdır.</p>
                <div>
                    <img src="/assets/images/payment-methods.png" alt="Ödeme Yöntemleri" class="img-fluid" style="max-height: 30px;">
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="/assets/js/scripts.js"></script>
</body>
</html>
<?php 
// Çıktı tamponlamayı sonlandır ve çıktıyı gönder
ob_end_flush(); 
?> 