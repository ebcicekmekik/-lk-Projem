<?php
require_once 'templates/header.php';

// Kategori ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$kategori_id = $_GET['id'];

// Kategori bilgilerini getir
$sorgu = $pdo->prepare("SELECT * FROM kategoriler WHERE id = ?");
$sorgu->execute([$kategori_id]);
$kategori = $sorgu->fetch(PDO::FETCH_ASSOC);

if (!$kategori) {
    header('Location: index.php');
    exit;
}

// Kategoriye ait ürünleri getir
$sorgu = $pdo->prepare("
    SELECT u.*, COUNT(y.id) as yorum_sayisi, AVG(y.puan) as ortalama_puan
    FROM urunler u
    LEFT JOIN yorumlar y ON u.id = y.urun_id
    WHERE u.kategori_id = ? AND u.aktif = 1
    GROUP BY u.id
    ORDER BY u.eklenme_tarihi DESC
");
$sorgu->execute([$kategori_id]);
$urunler = $sorgu->fetchAll(PDO::FETCH_ASSOC);

// Sıralama seçenekleri
$siralama = isset($_GET['siralama']) ? $_GET['siralama'] : 'varsayilan';

switch ($siralama) {
    case 'fiyat_artan':
        usort($urunler, function($a, $b) {
            return $a['fiyat'] - $b['fiyat'];
        });
        break;
    case 'fiyat_azalan':
        usort($urunler, function($a, $b) {
            return $b['fiyat'] - $a['fiyat'];
        });
        break;
    case 'en_yeni':
        usort($urunler, function($a, $b) {
            return strtotime($b['eklenme_tarihi']) - strtotime($a['eklenme_tarihi']);
        });
        break;
    case 'en_cok_satan':
        // Bu örnekte varsayılan sıralama kullanılmıştır
        break;
    default:
        // Varsayılan sıralama
        break;
}
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Ana Sayfa</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo $kategori['ad']; ?></li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="m-0"><?php echo $kategori['ad']; ?></h1>
                
                <div class="d-flex align-items-center">
                    <label for="siralama" class="me-2">Sırala:</label>
                    <select id="siralama" class="form-select form-select-sm" onchange="window.location.href=this.value">
                        <option value="?id=<?php echo $kategori_id; ?>&siralama=varsayilan" <?php echo $siralama === 'varsayilan' ? 'selected' : ''; ?>>Varsayılan</option>
                        <option value="?id=<?php echo $kategori_id; ?>&siralama=fiyat_artan" <?php echo $siralama === 'fiyat_artan' ? 'selected' : ''; ?>>Fiyat (Artan)</option>
                        <option value="?id=<?php echo $kategori_id; ?>&siralama=fiyat_azalan" <?php echo $siralama === 'fiyat_azalan' ? 'selected' : ''; ?>>Fiyat (Azalan)</option>
                        <option value="?id=<?php echo $kategori_id; ?>&siralama=en_yeni" <?php echo $siralama === 'en_yeni' ? 'selected' : ''; ?>>En Yeni</option>
                        <option value="?id=<?php echo $kategori_id; ?>&siralama=en_cok_satan" <?php echo $siralama === 'en_cok_satan' ? 'selected' : ''; ?>>En Çok Satan</option>
                    </select>
                </div>
            </div>
            
            <?php if (!empty($kategori['aciklama'])): ?>
                <div class="category-description mb-4">
                    <?php echo $kategori['aciklama']; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (empty($urunler)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i> Bu kategoride henüz ürün bulunmamaktadır.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($urunler as $urun): ?>
                <div class="col-6 col-md-4 col-lg-3 mb-4">
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
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Diğer Kategoriler -->
    <div class="row mt-5">
        <div class="col-12">
            <h2 class="border-bottom pb-2 mb-3">Diğer Kategoriler</h2>
        </div>
        
        <?php
        // Diğer kategorileri getir (mevcut kategori hariç)
        $sorgu = $pdo->prepare("SELECT * FROM kategoriler WHERE id != ? ORDER BY ad ASC LIMIT 4");
        $sorgu->execute([$kategori_id]);
        $diger_kategoriler = $sorgu->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($diger_kategoriler as $diger_kategori) {
            echo '<div class="col-6 col-md-3 mb-3">';
            echo '<a href="/kategori.php?id=' . $diger_kategori['id'] . '" class="text-decoration-none">';
            echo '<div class="category-box">';
            if (!empty($diger_kategori['resim'])) {
                echo '<img src="/uploads/kategoriler/' . $diger_kategori['resim'] . '" alt="' . $diger_kategori['ad'] . '">';
            } else {
                echo '<img src="https://via.placeholder.com/300x150.png?text=' . urlencode($diger_kategori['ad']) . '" alt="' . $diger_kategori['ad'] . '">';
            }
            echo '<div class="category-name">' . $diger_kategori['ad'] . '</div>';
            echo '</div>';
            echo '</a>';
            echo '</div>';
        }
        ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?> 