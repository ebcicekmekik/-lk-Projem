<?php
require_once 'templates/header.php';

// Sayfalama için parametreler
$sayfa = isset($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
$sayfada = 12; // Bir sayfada gösterilecek ürün sayısı
$baslangic = ($sayfa - 1) * $sayfada;

// Sıralama seçenekleri
$siralama = isset($_GET['siralama']) ? $_GET['siralama'] : 'varsayilan';
$siralama_sql = "u.eklenme_tarihi DESC"; // Varsayılan sıralama

switch ($siralama) {
    case 'fiyat_artan':
        $siralama_sql = "u.fiyat ASC";
        break;
    case 'fiyat_azalan':
        $siralama_sql = "u.fiyat DESC";
        break;
    case 'en_yeni':
        $siralama_sql = "u.eklenme_tarihi DESC";
        break;
    case 'en_cok_satan':
        // Varsayılan sıralama kullanılıyor
        break;
    case 'populer':
        $siralama_sql = "ortalama_puan DESC, yorum_sayisi DESC";
        break;
}

// Toplam ürün sayısını al
$sorgu = $pdo->query("SELECT COUNT(*) FROM urunler WHERE aktif = 1");
$toplam_urun = $sorgu->fetchColumn();
$toplam_sayfa = ceil($toplam_urun / $sayfada);

// Sayfa geçerlilik kontrolü
if ($sayfa < 1) $sayfa = 1;
if ($sayfa > $toplam_sayfa && $toplam_sayfa > 0) $sayfa = $toplam_sayfa;

// Ürünleri getir
$sorgu = $pdo->prepare("
    SELECT u.*, k.ad as kategori_adi, COUNT(y.id) as yorum_sayisi, AVG(y.puan) as ortalama_puan
    FROM urunler u
    LEFT JOIN kategoriler k ON u.kategori_id = k.id
    LEFT JOIN yorumlar y ON u.id = y.urun_id
    WHERE u.aktif = 1
    GROUP BY u.id
    ORDER BY $siralama_sql
    LIMIT :baslangic, :sayfada
");
$sorgu->bindParam(':baslangic', $baslangic, PDO::PARAM_INT);
$sorgu->bindParam(':sayfada', $sayfada, PDO::PARAM_INT);
$sorgu->execute();
$urunler = $sorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Ana Sayfa</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tüm Ürünler</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="m-0">Tüm Ürünler</h1>
                
                <div class="d-flex align-items-center">
                    <label for="siralama" class="me-2">Sırala:</label>
                    <select id="siralama" class="form-select form-select-sm" onchange="window.location.href=this.value">
                        <option value="?siralama=varsayilan" <?php echo $siralama === 'varsayilan' ? 'selected' : ''; ?>>Varsayılan</option>
                        <option value="?siralama=fiyat_artan" <?php echo $siralama === 'fiyat_artan' ? 'selected' : ''; ?>>Fiyat (Artan)</option>
                        <option value="?siralama=fiyat_azalan" <?php echo $siralama === 'fiyat_azalan' ? 'selected' : ''; ?>>Fiyat (Azalan)</option>
                        <option value="?siralama=en_yeni" <?php echo $siralama === 'en_yeni' ? 'selected' : ''; ?>>En Yeni</option>
                        <option value="?siralama=populer" <?php echo $siralama === 'populer' ? 'selected' : ''; ?>>En Popüler</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (empty($urunler)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i> Henüz ürün bulunmamaktadır.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($urunler as $urun): ?>
                <div class="col-6 col-md-4 col-lg-3 mb-4">
                    <div class="card h-100">
                        <?php if (strtotime($urun['eklenme_tarihi']) > strtotime('-7 days')): ?>
                            <div class="position-absolute bg-success text-white px-2 py-1 rounded-end" style="top: 10px;">
                                <small>Yeni</small>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($urun['resim'])): ?>
                            <img src="/uploads/urunler/<?php echo $urun['resim']; ?>" class="card-img-top" alt="<?php echo $urun['ad']; ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/300x300.png?text=<?php echo urlencode($urun['ad']); ?>" class="card-img-top" alt="<?php echo $urun['ad']; ?>">
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $urun['ad']; ?></h5>
                            <div class="mb-2">
                                <span class="badge bg-primary">
                                    <i class="fas fa-tag me-1"></i> <?php echo $urun['kategori_adi']; ?>
                                </span>
                            </div>
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
            <?php endforeach; ?>
        </div>
        
        <!-- Sayfalama -->
        <?php if ($toplam_sayfa > 1): ?>
            <nav aria-label="Sayfalama" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($sayfa <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?sayfa=<?php echo $sayfa - 1; ?>&siralama=<?php echo $siralama; ?>" aria-label="Önceki">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php
                    // Sayfa numaralarını göster
                    $baslangic_sayfa = max(1, $sayfa - 2);
                    $bitis_sayfa = min($toplam_sayfa, $sayfa + 2);
                    
                    if ($baslangic_sayfa > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?sayfa=1&siralama=' . $siralama . '">1</a></li>';
                        if ($baslangic_sayfa > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }
                    
                    for ($i = $baslangic_sayfa; $i <= $bitis_sayfa; $i++) {
                        echo '<li class="page-item' . ($i == $sayfa ? ' active' : '') . '"><a class="page-link" href="?sayfa=' . $i . '&siralama=' . $siralama . '">' . $i . '</a></li>';
                    }
                    
                    if ($bitis_sayfa < $toplam_sayfa) {
                        if ($bitis_sayfa < $toplam_sayfa - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="?sayfa=' . $toplam_sayfa . '&siralama=' . $siralama . '">' . $toplam_sayfa . '</a></li>';
                    }
                    ?>
                    
                    <li class="page-item <?php echo ($sayfa >= $toplam_sayfa) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?sayfa=<?php echo $sayfa + 1; ?>&siralama=<?php echo $siralama; ?>" aria-label="Sonraki">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer.php'; ?> 