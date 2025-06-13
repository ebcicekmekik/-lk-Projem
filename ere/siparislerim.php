<?php
require_once 'templates/header.php';

// Oturum kontrolü
if (!isLoggedIn()) {
    // Kullanıcı giriş yapmamışsa, giriş sayfasına yönlendir
    $_SESSION['error_message'] = 'Siparişlerinizi görmek için giriş yapmalısınız.';
    header('Location: giris.php');
    exit;
}

$kullanici_id = $_SESSION['kullanici_id'];

// Siparişleri veritabanından çek
$sorgu = $pdo->prepare("
    SELECT * FROM siparisler 
    WHERE kullanici_id = ? 
    ORDER BY siparis_tarihi DESC
");
$sorgu->execute([$kullanici_id]);
$siparisler = $sorgu->fetchAll();

// Sipariş detayı için id kontrolü
$siparis_detayi = null;
$siparis_urunleri = [];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $detay_id = $_GET['id'];
    
    // Seçilen siparişin kullanıcıya ait olup olmadığını kontrol et
    $sorgu = $pdo->prepare("
        SELECT s.*, k.ad, k.soyad, k.email, k.telefon
        FROM siparisler s
        JOIN kullanicilar k ON s.kullanici_id = k.id
        WHERE s.id = ? AND s.kullanici_id = ?
    ");
    $sorgu->execute([$detay_id, $kullanici_id]);
    $siparis_detayi = $sorgu->fetch();
    
    if ($siparis_detayi) {
        // Sipariş ürünlerini al
        $sorgu = $pdo->prepare("
            SELECT sd.*, u.ad as urun_adi, u.resim
            FROM siparis_detaylari sd
            JOIN urunler u ON sd.urun_id = u.id
            WHERE sd.siparis_id = ?
        ");
        $sorgu->execute([$detay_id]);
        $siparis_urunleri = $sorgu->fetchAll();
    }
}

// Sipariş durumunu Türkçe olarak göster
function siparisdurumuTR($durum) {
    switch ($durum) {
        case 'beklemede':
            return '<span class="badge bg-warning text-dark">Beklemede</span>';
        case 'hazırlanıyor':
            return '<span class="badge bg-info text-dark">Hazırlanıyor</span>';
        case 'kargoya_verildi':
            return '<span class="badge bg-primary">Kargoya Verildi</span>';
        case 'teslim_edildi':
            return '<span class="badge bg-success">Teslim Edildi</span>';
        case 'iptal_edildi':
            return '<span class="badge bg-danger">İptal Edildi</span>';
        default:
            return '<span class="badge bg-secondary">Belirsiz</span>';
    }
}

// Ödeme yöntemini Türkçe olarak göster
function odemeYontemiTR($yontem) {
    switch ($yontem) {
        case 'kredi_karti':
            return 'Kredi Kartı';
        case 'havale':
            return 'Havale / EFT';
        case 'kapida_odeme':
            return 'Kapıda Ödeme';
        default:
            return 'Belirtilmemiş';
    }
}

?>

<div class="container">
    <div class="row">
        <!-- Breadcrumb -->
        <div class="col-12 mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Ana Sayfa</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Siparişlerim</li>
                </ol>
            </nav>
        </div>
        
        <div class="col-12 mb-4">
            <h1 class="border-bottom pb-2">Siparişlerim</h1>
        </div>
        
        <?php if (empty($siparisler)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Henüz bir siparişiniz bulunmamaktadır.
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
            <!-- Siparişler ve Detay Bölmesi -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i> Sipariş Listem</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach ($siparisler as $siparis): ?>
                            <a href="?id=<?php echo $siparis['id']; ?>" class="list-group-item list-group-item-action <?php echo (isset($_GET['id']) && $_GET['id'] == $siparis['id']) ? 'active' : ''; ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">#<?php echo $siparis['id']; ?> Numaralı Sipariş</h6>
                                    <small><?php echo date('d.m.Y', strtotime($siparis['siparis_tarihi'])); ?></small>
                                </div>
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <p class="mb-1"><?php echo number_format($siparis['toplam_tutar'], 2, ',', '.'); ?> ₺</p>
                                        <small><?php echo odemeYontemiTR($siparis['odeme_yontemi']); ?></small>
                                    </div>
                                    <div>
                                        <?php echo siparisdurumuTR($siparis['durum']); ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <?php if ($siparis_detayi): ?>
                    <!-- Sipariş Detayı -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Sipariş #<?php echo $siparis_detayi['id']; ?> Detayı</h5>
                            <span><?php echo siparisdurumuTR($siparis_detayi['durum']); ?></span>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted">Sipariş Numarası</h6>
                                    <p class="mb-3">#<?php echo $siparis_detayi['id']; ?></p>
                                    
                                    <h6 class="text-muted">Sipariş Tarihi</h6>
                                    <p class="mb-3"><?php echo date('d.m.Y H:i', strtotime($siparis_detayi['siparis_tarihi'])); ?></p>
                                    
                                    <h6 class="text-muted">Ödeme Yöntemi</h6>
                                    <p class="mb-0"><?php echo odemeYontemiTR($siparis_detayi['odeme_yontemi']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Teslimat Adresi</h6>
                                    <p class="mb-3"><?php echo nl2br($siparis_detayi['teslimat_adresi']); ?></p>
                                    
                                    <h6 class="text-muted">Toplam Tutar</h6>
                                    <p class="mb-0 fw-bold"><?php echo number_format($siparis_detayi['toplam_tutar'], 2, ',', '.'); ?> ₺</p>
                                </div>
                            </div>
                            
                            <?php if ($siparis_detayi['durum'] === 'beklemede' && $siparis_detayi['odeme_yontemi'] === 'havale'): ?>
                            <div class="alert alert-warning mb-4">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i> Ödeme Bekleniyor</h6>
                                <p class="mb-2">Siparişiniz için havale/EFT ödemesi bekleniyor. Lütfen ödemenizi aşağıdaki hesaba yapınız:</p>
                                <div class="bg-light p-2 rounded">
                                    <p class="mb-1 small"><strong>Banka:</strong> SportBank</p>
                                    <p class="mb-1 small"><strong>IBAN:</strong> TR00 0000 0000 0000 0000 0000 00</p>
                                    <p class="mb-0 small"><strong>Alıcı:</strong> SportMağazası Ltd. Şti.</p>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <h6 class="mb-3">Sipariş Edilen Ürünler</h6>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Ürün</th>
                                            <th>Birim Fiyat</th>
                                            <th>Miktar</th>
                                            <th>Toplam</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($siparis_urunleri as $urun): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($urun['resim'])): ?>
                                                        <img src="/assets/images/products/<?php echo $urun['resim']; ?>" class="me-2" width="40" height="40" alt="<?php echo $urun['urun_adi']; ?>">
                                                    <?php else: ?>
                                                        <div class="me-2" style="width:40px;height:40px;background:#eee;display:flex;align-items:center;justify-content:center;">
                                                            <i class="fas fa-box"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php echo $urun['urun_adi']; ?>
                                                </div>
                                            </td>
                                            <td><?php echo number_format($urun['birim_fiyat'], 2, ',', '.'); ?> ₺</td>
                                            <td><?php echo $urun['miktar']; ?></td>
                                            <td class="fw-bold"><?php echo number_format($urun['birim_fiyat'] * $urun['miktar'], 2, ',', '.'); ?> ₺</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">Toplam:</td>
                                            <td class="fw-bold"><?php echo number_format($siparis_detayi['toplam_tutar'], 2, ',', '.'); ?> ₺</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <div class="mt-4">
                                <?php if ($siparis_detayi['durum'] === 'teslim_edildi'): ?>
                                <a href="/yorum-ekle.php" class="btn btn-outline-primary btn-sm">
                                    <i class="far fa-comment me-2"></i> Ürün Değerlendirmesi Yap
                                </a>
                                <?php endif; ?>
                                
                                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                                    <i class="fas fa-print me-2"></i> Siparişi Yazdır
                                </button>
                                
                                <?php if ($siparis_detayi['durum'] === 'beklemede'): ?>
                                <a href="/siparis-iptal.php?id=<?php echo $siparis_detayi['id']; ?>" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Siparişinizi iptal etmek istediğinize emin misiniz?')">
                                    <i class="fas fa-times me-2"></i> Siparişi İptal Et
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kargo Takip Bilgisi (Eğer kargoya verildiyse) -->
                    <?php if ($siparis_detayi['durum'] === 'kargoya_verildi'): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-truck me-2"></i> Kargo Takibi</h5>
                        </div>
                        <div class="card-body">
                            <p>Siparişiniz kargoya verilmiştir. Kargo takip numarası ile siparişinizin durumunu takip edebilirsiniz.</p>
                            
                            <div class="alert alert-primary">
                                <strong>Kargo Firması:</strong> Hızlı Kargo<br>
                                <strong>Takip Numarası:</strong> 123456789012
                            </div>
                            
                            <a href="https://www.example.com/kargo-takip" target="_blank" class="btn btn-primary">
                                <i class="fas fa-external-link-alt me-2"></i> Kargo Durumunu Takip Et
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info h-100 d-flex flex-column align-items-center justify-content-center text-center">
                        <i class="fas fa-info-circle fa-3x mb-3"></i>
                        <h4>Sipariş Detaylarını Görüntüle</h4>
                        <p class="mb-0">Detaylarını görmek istediğiniz siparişe sol taraftan tıklayınız.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?> 