<?php 
require_once 'templates/header.php';

// Sepet içeriğini al
$sepet = sepetiGetir();
$sepetToplami = sepetToplaminiHesapla();

// Kargo ücreti hesaplama
$kargoUcreti = 19.90; // Varsayılan kargo ücreti
if ($sepetToplami >= 500) {
    $kargoUcreti = 0; // 500 TL ve üzeri alışverişlerde ücretsiz kargo
}

// Genel toplam
$genelToplam = $sepetToplami + $kargoUcreti;

// Kupon indirimi
$kuponIndirim = 0;
if (isset($_SESSION['kupon_indirim'])) {
    $kuponIndirim = $_SESSION['kupon_indirim'];
    $genelToplam -= $kuponIndirim;
}
?>

<div class="row">
    <div class="col-12 mb-4">
        <h1 class="border-bottom pb-2">Alışveriş Sepetim</h1>
    </div>
    
    <?php if (empty($sepet)): ?>
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> Sepetinizde ürün bulunmamaktadır.
            </div>
            <div class="text-center my-5">
                <h4 class="mb-4">Alışverişe başlamak ister misiniz?</h4>
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-4">
                        <a href="/index.php" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-home me-2"></i> Ana Sayfaya Dön
                        </a>
                        <a href="/urunler.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-search me-2"></i> Ürünleri İncele
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table cart-table align-middle">
                            <thead>
                                <tr>
                                    <th colspan="2">Ürün</th>
                                    <th>Fiyat</th>
                                    <th>Miktar</th>
                                    <th>Toplam</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sepet as $item): ?>
                                    <tr>
                                        <td width="80">
                                            <?php if (!empty($item['urun']['resim'])): ?>
                                                <img src="/assets/images/products/<?php echo $item['urun']['resim']; ?>" class="cart-item-img" alt="<?php echo $item['urun']['ad']; ?>">
                                            <?php else: ?>
                                                <img src="https://via.placeholder.com/80x80.png?text=<?php echo urlencode($item['urun']['ad']); ?>" class="cart-item-img" alt="<?php echo $item['urun']['ad']; ?>">
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="/urun.php?id=<?php echo $item['urun']['id']; ?>" class="text-decoration-none link-dark">
                                                <?php echo $item['urun']['ad']; ?>
                                            </a>
                                            <?php if (isset($item['urun']['kategori_adi'])): ?>
                                                <div class="small text-muted"><?php echo $item['urun']['kategori_adi']; ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-nowrap"><?php echo number_format($item['urun']['fiyat'], 2, ',', '.'); ?> ₺</td>
                                        <td width="150">
                                            <form action="/sepet-guncelle.php" method="post">
                                                <input type="hidden" name="urun_id" value="<?php echo $item['urun']['id']; ?>">
                                                <div class="input-group">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="this.parentNode.querySelector('input[type=number]').stepDown()">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input type="number" name="miktar" class="form-control form-control-sm text-center quantity-input" data-action="update-cart" value="<?php echo $item['miktar']; ?>" min="1" max="<?php echo $item['urun']['stok_adedi']; ?>" data-max-stock="<?php echo $item['urun']['stok_adedi']; ?>">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="this.parentNode.querySelector('input[type=number]').stepUp()">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                        <td class="text-nowrap fw-bold"><?php echo number_format($item['toplam'], 2, ',', '.'); ?> ₺</td>
                                        <td>
                                            <form action="/sepet-sil.php" method="post">
                                                <input type="hidden" name="urun_id" value="<?php echo $item['urun']['id']; ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Sepet Alt Bilgileri -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <a href="/index.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i> Alışverişe Devam Et
                        </a>
                        <form action="/sepet-temizle.php" method="post">
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-trash-alt me-2"></i> Sepeti Temizle
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Kupon Kodu -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">İndirim Kuponu</h5>
                    <form action="/kupon-uygula.php" method="post">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" name="kupon_kodu" placeholder="Kupon kodu" required>
                            <button class="btn btn-primary" type="submit">Uygula</button>
                        </div>
                    </form>
                    <?php if ($kuponIndirim > 0): ?>
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle me-2"></i> Kupon indirimi uygulandı: <?php echo number_format($kuponIndirim, 2, ',', '.'); ?> ₺
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Sepet Özeti -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Sipariş Özeti</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Ara Toplam
                            <span><?php echo number_format($sepetToplami, 2, ',', '.'); ?> ₺</span>
                        </li>
                        
                        <?php if ($kuponIndirim > 0): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 text-success">
                                Kupon İndirimi
                                <span>- <?php echo number_format($kuponIndirim, 2, ',', '.'); ?> ₺</span>
                            </li>
                        <?php endif; ?>
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Kargo
                            <?php if ($kargoUcreti == 0): ?>
                                <span class="text-success">Ücretsiz</span>
                            <?php else: ?>
                                <span><?php echo number_format($kargoUcreti, 2, ',', '.'); ?> ₺</span>
                            <?php endif; ?>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 fw-bold">
                            Genel Toplam
                            <span class="text-primary"><?php echo number_format($genelToplam, 2, ',', '.'); ?> ₺</span>
                        </li>
                    </ul>
                    
                    <?php if ($kargoUcreti > 0): ?>
                        <div class="alert alert-info mt-3 mb-3">
                            <i class="fas fa-info-circle me-2"></i> <?php echo number_format(500 - $sepetToplami, 2, ',', '.'); ?> ₺ daha alışveriş yaparak ücretsiz kargo fırsatından yararlanabilirsiniz.
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isLoggedIn()): ?>
                        <a href="/odeme.php" class="btn btn-primary w-100">
                            <i class="fas fa-credit-card me-2"></i> Ödeme Adımına Geç
                        </a>
                    <?php else: ?>
                        <div class="text-center mb-3">
                            <p>Ödeme yapmak için giriş yapmalısınız</p>
                        </div>
                        <a href="/giris.php" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt me-2"></i> Giriş Yap
                        </a>
                        <div class="text-center mt-2">
                            <a href="/kayit.php">Hesabınız yok mu? Kayıt ol</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer.php'; ?> 