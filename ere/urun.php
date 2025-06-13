<?php
require_once 'templates/header.php';

// Ürün ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$urun_id = $_GET['id'];

// Ürün bilgilerini getir
$sorgu = $pdo->prepare("
    SELECT u.*, k.ad as kategori_adi, AVG(y.puan) as ortalama_puan, COUNT(y.id) as yorum_sayisi
    FROM urunler u
    LEFT JOIN kategoriler k ON u.kategori_id = k.id
    LEFT JOIN yorumlar y ON u.id = y.urun_id
    WHERE u.id = ?
    GROUP BY u.id
");
$sorgu->execute([$urun_id]);
$urun = $sorgu->fetch(PDO::FETCH_ASSOC);

if (!$urun) {
    header('Location: index.php');
    exit;
}

// Yorum ekleme işlemi
$yorum_hatasi = '';
$yorum_basarili = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['yorum_ekle']) && isLoggedIn()) {
    $yorum = trim($_POST['yorum'] ?? '');
    $puan = intval($_POST['puan'] ?? 5);
    
    if (empty($yorum)) {
        $yorum_hatasi = 'Yorum metni boş olamaz';
    } else if ($puan < 1 || $puan > 5) {
        $yorum_hatasi = 'Geçerli bir puan veriniz (1-5)';
    } else {
        // Yorumu kaydet
        $sorgu = $pdo->prepare("
            INSERT INTO yorumlar (urun_id, kullanici_id, yorum, puan, tarih)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $sorgu->execute([
            $urun_id,
            $_SESSION['kullanici_id'],
            $yorum,
            $puan
        ]);
        
        $yorum_basarili = true;
        
        // Sayfayı yenile (post verilerini temizle)
        header("Location: urun.php?id=$urun_id&yorum=basarili");
        exit;
    }
}

// Ürün yorumlarını getir
$sorgu = $pdo->prepare("
    SELECT y.*, CONCAT(k.ad, ' ', k.soyad) as kullanici_adi
    FROM yorumlar y
    JOIN kullanicilar k ON y.kullanici_id = k.id
    WHERE y.urun_id = ?
    ORDER BY y.tarih DESC
");
$sorgu->execute([$urun_id]);
$yorumlar = $sorgu->fetchAll(PDO::FETCH_ASSOC);

// Ürün ek resimlerini getir
$sorgu = $pdo->prepare("SELECT * FROM urun_resimleri WHERE urun_id = ? ORDER BY id ASC");
$sorgu->execute([$urun_id]);
$ek_resimler = $sorgu->fetchAll(PDO::FETCH_ASSOC);

// Benzer ürünleri getir
$sorgu = $pdo->prepare("
    SELECT * FROM urunler 
    WHERE kategori_id = ? AND id != ? AND aktif = 1
    ORDER BY RAND() LIMIT 4
");
$sorgu->execute([$urun['kategori_id'], $urun_id]);
$benzer_urunler = $sorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Ana Sayfa</a></li>
                    <li class="breadcrumb-item"><a href="/kategori.php?id=<?php echo $urun['kategori_id']; ?>"><?php echo $urun['kategori_adi']; ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo $urun['ad']; ?></li>
                </ol>
            </nav>
            
            <?php if (isset($_GET['yorum']) && $_GET['yorum'] === 'basarili'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Yorumunuz başarıyla eklendi. Teşekkür ederiz!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row mb-5">
        <!-- Ürün Resimleri -->
        <div class="col-md-5 mb-4 mb-md-0">
            <div class="product-images">
                <div class="main-image mb-3">
                    <?php if (!empty($urun['resim'])): ?>
                        <img src="/uploads/urunler/<?php echo $urun['resim']; ?>" class="img-fluid rounded product-image" alt="<?php echo $urun['ad']; ?>">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/600x600.png?text=<?php echo urlencode($urun['ad']); ?>" class="img-fluid rounded product-image" alt="<?php echo $urun['ad']; ?>">
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($ek_resimler)): ?>
                    <div class="row thumbnail-images">
                        <?php foreach ($ek_resimler as $resim): ?>
                            <div class="col-3 mb-2">
                                <img src="/uploads/urunler/<?php echo $resim['resim_url']; ?>" class="img-thumbnail" alt="<?php echo $urun['ad']; ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Ürün Bilgileri -->
        <div class="col-md-7">
            <h1 class="mb-3"><?php echo $urun['ad']; ?></h1>
            
            <div class="d-flex align-items-center mb-3">
                <div class="me-3">
                    <span class="star-rating">
                        <?php
                        $rating = round($urun['ortalama_puan'] ?? 0);
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $rating) {
                                echo '<i class="fas fa-star"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                    </span>
                    <span class="text-muted ms-2">(<?php echo $urun['yorum_sayisi']; ?> yorum)</span>
                </div>
                
                <span class="badge bg-primary">
                    <i class="fas fa-tag me-1"></i> <?php echo $urun['kategori_adi']; ?>
                </span>
            </div>
            
            <div class="product-price mb-4">
                <h2 class="text-primary"><?php echo number_format($urun['fiyat'], 2, ',', '.'); ?> ₺</h2>
            </div>
            
            <div class="product-stock mb-4">
                <?php if ($urun['stok_adedi'] > 0): ?>
                    <span class="badge bg-success"><i class="fas fa-check me-1"></i> Stokta</span>
                    <span class="text-muted ms-2"><?php echo $urun['stok_adedi']; ?> adet</span>
                <?php else: ?>
                    <span class="badge bg-danger"><i class="fas fa-times me-1"></i> Stokta Yok</span>
                <?php endif; ?>
            </div>
            
            <div class="product-actions mb-4">
                <form action="/sepet-ekle.php" method="post" class="d-flex">
                    <input type="hidden" name="urun_id" value="<?php echo $urun['id']; ?>">
                    
                    <div class="input-group me-2" style="width: 120px;">
                        <button type="button" class="btn btn-outline-secondary" onclick="decreaseQuantity()">-</button>
                        <input type="number" id="miktar" name="miktar" class="form-control text-center" value="1" min="1" max="<?php echo $urun['stok_adedi']; ?>">
                        <button type="button" class="btn btn-outline-secondary" onclick="increaseQuantity()">+</button>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" <?php echo $urun['stok_adedi'] <= 0 ? 'disabled' : ''; ?>>
                        <i class="fas fa-shopping-cart me-2"></i> Sepete Ekle
                    </button>
                </form>
            </div>
            
            <div class="product-description mb-4">
                <h5>Ürün Açıklaması</h5>
                <div class="border rounded p-3">
                    <?php echo !empty($urun['aciklama']) ? $urun['aciklama'] : 'Bu ürün için açıklama bulunmamaktadır.'; ?>
                </div>
            </div>
            
            <div class="product-features">
                <div class="row">
                    <div class="col-6">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-truck fa-2x text-primary me-3"></i>
                            <div>
                                <h6 class="mb-0">Hızlı Teslimat</h6>
                                <small class="text-muted">2-4 iş günü içinde kargoda</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-undo fa-2x text-primary me-3"></i>
                            <div>
                                <h6 class="mb-0">Kolay İade</h6>
                                <small class="text-muted">30 gün koşulsuz iade</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shield-alt fa-2x text-primary me-3"></i>
                            <div>
                                <h6 class="mb-0">Güvenli Ödeme</h6>
                                <small class="text-muted">256-bit SSL koruması</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-headset fa-2x text-primary me-3"></i>
                            <div>
                                <h6 class="mb-0">7/24 Destek</h6>
                                <small class="text-muted">Her zaman yanınızdayız</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Yorumlar -->
    <div class="row mb-5">
        <div class="col-12">
            <h3 class="border-bottom pb-2 mb-4">Müşteri Yorumları (<?php echo count($yorumlar); ?>)</h3>
            
            <?php if (empty($yorumlar)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Bu ürün için henüz yorum yapılmamış. İlk yorumu siz yapın!
                </div>
            <?php else: ?>
                <div class="product-reviews mb-4">
                    <?php foreach ($yorumlar as $yorum): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <div>
                                        <h5 class="card-title mb-0"><?php echo $yorum['kullanici_adi']; ?></h5>
                                        <div class="text-muted small">
                                            <?php echo date('d.m.Y H:i', strtotime($yorum['tarih'])); ?>
                                        </div>
                                    </div>
                                    <div class="star-rating">
                                        <?php
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $yorum['puan']) {
                                                echo '<i class="fas fa-star"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                                <p class="card-text"><?php echo $yorum['yorum']; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Yorum Formu -->
            <?php if (isLoggedIn()): ?>
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Ürünü Değerlendir</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($yorum_hatasi)): ?>
                            <div class="alert alert-danger">
                                <?php echo $yorum_hatasi; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="" method="post">
                            <div class="mb-3">
                                <label for="puan" class="form-label">Puanınız</label>
                                <select class="form-select" id="puan" name="puan">
                                    <option value="5">5 - Mükemmel</option>
                                    <option value="4">4 - Çok İyi</option>
                                    <option value="3">3 - İyi</option>
                                    <option value="2">2 - Kötü</option>
                                    <option value="1">1 - Çok Kötü</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="yorum" class="form-label">Yorumunuz</label>
                                <textarea class="form-control" id="yorum" name="yorum" rows="3" required><?php echo isset($_POST['yorum']) ? $_POST['yorum'] : ''; ?></textarea>
                            </div>
                            <button type="submit" name="yorum_ekle" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i> Yorumu Gönder
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i> Yorum yapabilmek için <a href="/giris.php">giriş yapmalısınız</a>.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Benzer Ürünler -->
    <?php if (!empty($benzer_urunler)): ?>
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="border-bottom pb-2 mb-4">Benzer Ürünler</h3>
                
                <div class="row">
                    <?php foreach ($benzer_urunler as $benzer_urun): ?>
                        <div class="col-6 col-md-3 mb-4">
                            <div class="card h-100">
                                <?php if (!empty($benzer_urun['resim'])): ?>
                                    <img src="/uploads/urunler/<?php echo $benzer_urun['resim']; ?>" class="card-img-top" alt="<?php echo $benzer_urun['ad']; ?>">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/300x300.png?text=<?php echo urlencode($benzer_urun['ad']); ?>" class="card-img-top" alt="<?php echo $benzer_urun['ad']; ?>">
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $benzer_urun['ad']; ?></h5>
                                    <p class="card-text text-primary fw-bold"><?php echo number_format($benzer_urun['fiyat'], 2, ',', '.'); ?> ₺</p>
                                </div>
                                <div class="card-footer bg-white d-flex justify-content-between">
                                    <a href="/urun.php?id=<?php echo $benzer_urun['id']; ?>" class="btn btn-sm btn-outline-primary">Detaylar</a>
                                    <form action="/sepet-ekle.php" method="post">
                                        <input type="hidden" name="urun_id" value="<?php echo $benzer_urun['id']; ?>">
                                        <input type="hidden" name="miktar" value="1">
                                        <button type="submit" class="btn btn-sm btn-primary" <?php echo $benzer_urun['stok_adedi'] <= 0 ? 'disabled' : ''; ?>>
                                            <i class="fas fa-shopping-cart me-1"></i> Sepete Ekle
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Miktar arttır/azalt
function increaseQuantity() {
    var input = document.getElementById('miktar');
    var max = parseInt(input.getAttribute('max'));
    var value = parseInt(input.value);
    if (value < max) {
        input.value = value + 1;
    }
}

function decreaseQuantity() {
    var input = document.getElementById('miktar');
    var value = parseInt(input.value);
    if (value > 1) {
        input.value = value - 1;
    }
}

// Ürün küçük resimlerine tıklandığında büyük resmi değiştir
document.addEventListener('DOMContentLoaded', function() {
    var thumbnails = document.querySelectorAll('.thumbnail-images img');
    var mainImage = document.querySelector('.main-image img');
    
    thumbnails.forEach(function(thumbnail) {
        thumbnail.addEventListener('click', function() {
            mainImage.src = this.src;
        });
    });
});
</script>

<?php require_once 'templates/footer.php'; ?> 