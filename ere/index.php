<?php require_once 'templates/header.php'; ?>

<!-- Ana Slider -->
<div id="main-slider" class="carousel slide mb-4" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#main-slider" data-bs-slide-to="0" class="active"></button>
        <button type="button" data-bs-target="#main-slider" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#main-slider" data-bs-slide-to="2"></button>
    </div>
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="https://images.unsplash.com/photo-1540497077202-7c8a3999166f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1200&q=80" class="d-block w-100" alt="Fitness Ekipmanları">
            <div class="carousel-caption d-none d-md-block">
                <h2>Fitness Ekipmanları</h2>
                <p>En kaliteli fitness ekipmanları ile antrenmanlarını daha verimli hale getir!</p>
                <a href="/kategori.php?id=1" class="btn btn-light">Alışverişe Başla</a>
            </div>
        </div>
        <div class="carousel-item">
            <img src="https://images.unsplash.com/photo-1552674605-db6ffd4facb5?ixlib=rb-1.2.1&auto=format&fit=crop&w=1200&q=80" class="d-block w-100" alt="Koşu Ürünleri">
            <div class="carousel-caption d-none d-md-block">
                <h2>Koşu Ürünleri</h2>
                <p>Profesyonel koşu ekipmanları ile performansını artır!</p>
                <a href="/kategori.php?id=6" class="btn btn-light">Alışverişe Başla</a>
            </div>
        </div>
        <div class="carousel-item">
            <img src="https://www.bursahakimiyet.com.tr/static/2023/07/14/super-lig-takimlarinin-yeni-sezon-formalari-1689331611-923_small.jpg" class="d-block w-100" alt="Spor Giyim">
            <div class="carousel-caption d-none d-md-block">
                <h2>Formalar</h2>
                <p>Yeni-Eski formalarımız ile tarzını yansıt!</p>
                <a href="/kategori.php?id=2" class="btn btn-light">Alışverişe Başla</a>
            </div>
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#main-slider" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Önceki</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#main-slider" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Sonraki</span>
    </button>
</div>

<!-- Kategoriler -->
<div class="row mb-4">
    <div class="col-12">
        <h2 class="border-bottom pb-2">Kategoriler</h2>
    </div>
    <?php
    $kategoriler = tumKategorileriGetir();
    foreach ($kategoriler as $kategori) {
        echo '<div class="col-6 col-md-4 col-lg-3">';
        echo '<a href="/kategori.php?id=' . $kategori['id'] . '" class="text-decoration-none">';
        echo '<div class="category-box">';
        if (!empty($kategori['resim'])) {
            echo '<img src="/uploads/kategoriler/' . $kategori['resim'] . '" alt="' . $kategori['ad'] . '">';
        } else {
            // Varsayılan kategori resmi
            echo '<img src="https://via.placeholder.com/300x150.png?text=' . urlencode($kategori['ad']) . '" alt="' . $kategori['ad'] . '">';
        }
        echo '<div class="category-name">' . $kategori['ad'] . '</div>';
        echo '</div>';
        echo '</a>';
        echo '</div>';
    }
    ?>
</div>

<!-- Kampanya Banner -->
<div class="promo-banner mb-4">
    <div class="container">
        <h2><i class="fas fa-bolt me-2"></i> İLK SİPARİŞİNİZE ÖZEL %10 İNDİRİM!</h2>
        <p class="mb-3">Tüm spor ürünlerinde geçerli, kaçırılmayacak fırsat!</p>
        <a href="/urunler.php" class="btn btn-light">HEMEN İNCELE</a>
    </div>
</div>

<!-- Popüler Ürünler -->
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Popüler Ürünler</h2>
        <a href="/urunler.php?sort=popular" class="btn btn-outline-primary btn-sm">Tümünü Gör</a>
    </div>
    
    <?php
    $populerUrunler = populerUrunleriGetir();
    foreach ($populerUrunler as $urun) {
        ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card h-100">
                <?php if (!empty($urun['resim'])): ?>
                    <img src="/uploads/urunler/<?php echo $urun['resim']; ?>" class="card-img-top" alt="<?php echo $urun['ad']; ?>">
                <?php else: ?>
                    <img src="https://via.placeholder.com/300x300.png?text=<?php echo urlencode($urun['ad']); ?>" class="card-img-top" alt="<?php echo $urun['ad']; ?>">
                <?php endif; ?>
                
                <div class="card-body">
                    <h5 class="card-title"><?php echo $urun['ad']; ?></h5>
                    <div class="mb-2">
                        <span class="star-rating">
                            <?php
                            $rating = isset($urun['ortalama_puan']) ? round($urun['ortalama_puan']) : 0;
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating) {
                                    echo '<i class="fas fa-star"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </span>
                        <small class="text-muted ms-1">
                            (<?php echo isset($urun['yorum_sayisi']) ? $urun['yorum_sayisi'] : 0; ?> yorum)
                        </small>
                    </div>
                    <p class="card-text text-primary fw-bold"><?php echo number_format($urun['fiyat'], 2, ',', '.'); ?> ₺</p>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between">
                    <a href="/urun.php?id=<?php echo $urun['id']; ?>" class="btn btn-sm btn-outline-primary">Detaylar</a>
                    <form action="/sepet-ekle.php" method="post">
                        <input type="hidden" name="urun_id" value="<?php echo $urun['id']; ?>">
                        <input type="hidden" name="miktar" value="1">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-shopping-cart me-1"></i> Sepete Ekle
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div>

<!-- Yeni Ürünler -->
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Yeni Ürünler</h2>
        <a href="/urunler.php?sort=newest" class="btn btn-outline-primary btn-sm">Tümünü Gör</a>
    </div>
    
    <?php
    $yeniUrunler = yeniUrunleriGetir();
    foreach ($yeniUrunler as $urun) {
        ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card h-100">
                <div class="position-absolute bg-success text-white px-2 py-1 rounded-end" style="top: 10px;">
                    <small>Yeni</small>
                </div>
                <?php if (!empty($urun['resim'])): ?>
                    <img src="/uploads/urunler/<?php echo $urun['resim']; ?>" class="card-img-top" alt="<?php echo $urun['ad']; ?>">
                <?php else: ?>
                    <img src="https://via.placeholder.com/300x300.png?text=<?php echo urlencode($urun['ad']); ?>" class="card-img-top" alt="<?php echo $urun['ad']; ?>">
                <?php endif; ?>
                
                <div class="card-body">
                    <h5 class="card-title"><?php echo $urun['ad']; ?></h5>
                    <p class="card-text text-primary fw-bold"><?php echo number_format($urun['fiyat'], 2, ',', '.'); ?> ₺</p>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between">
                    <a href="/urun.php?id=<?php echo $urun['id']; ?>" class="btn btn-sm btn-outline-primary">Detaylar</a>
                    <form action="/sepet-ekle.php" method="post">
                        <input type="hidden" name="urun_id" value="<?php echo $urun['id']; ?>">
                        <input type="hidden" name="miktar" value="1">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-shopping-cart me-1"></i> Sepete Ekle
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div>

<!-- Özellikler -->
<div class="row mb-5">
    <div class="col-md-3 mb-3 mb-md-0">
        <div class="d-flex align-items-center justify-content-center flex-column text-center p-3 border rounded h-100">
            <i class="fas fa-truck fa-3x text-primary mb-3"></i>
            <h5>Ücretsiz Kargo</h5>
            <p class="mb-0 text-muted">500₺ ve üzeri alışverişlerde ücretsiz kargo imkanı</p>
        </div>
    </div>
    <div class="col-md-3 mb-3 mb-md-0">
        <div class="d-flex align-items-center justify-content-center flex-column text-center p-3 border rounded h-100">
            <i class="fas fa-undo fa-3x text-primary mb-3"></i>
            <h5>30 Gün İade</h5>
            <p class="mb-0 text-muted">30 gün içinde koşulsuz iade garantisi</p>
        </div>
    </div>
    <div class="col-md-3 mb-3 mb-md-0">
        <div class="d-flex align-items-center justify-content-center flex-column text-center p-3 border rounded h-100">
            <i class="fas fa-lock fa-3x text-primary mb-3"></i>
            <h5>Güvenli Ödeme</h5>
            <p class="mb-0 text-muted">256-bit SSL ile güvenli ödeme işlemleri</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="d-flex align-items-center justify-content-center flex-column text-center p-3 border rounded h-100">
            <i class="fas fa-headset fa-3x text-primary mb-3"></i>
            <h5>7/24 Destek</h5>
            <p class="mb-0 text-muted">Her zaman yanınızdayız, 7/24 müşteri desteği</p>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?> 