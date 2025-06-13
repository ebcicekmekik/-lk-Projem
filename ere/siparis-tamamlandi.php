<?php
require_once 'templates/header.php';

// Oturum kontrolü
if (!isLoggedIn()) {
    // Kullanıcı giriş yapmamışsa, giriş sayfasına yönlendir
    $_SESSION['error_message'] = 'Bu sayfaya erişim izniniz bulunmamaktadır.';
    header('Location: giris.php');
    exit;
}

// Sipariş ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$siparis_id = $_GET['id'];
$kullanici_id = $_SESSION['kullanici_id'];

// Siparişi veritabanından al
$sorgu = $pdo->prepare("
    SELECT s.*, k.ad, k.soyad, k.email, k.telefon
    FROM siparisler s
    JOIN kullanicilar k ON s.kullanici_id = k.id
    WHERE s.id = ? AND s.kullanici_id = ?
");
$sorgu->execute([$siparis_id, $kullanici_id]);
$siparis = $sorgu->fetch();

// Sipariş bulunamadı veya başka kullanıcıya aitse yönlendir
if (!$siparis) {
    $_SESSION['error_message'] = 'Sipariş bulunamadı veya erişim izniniz bulunmamaktadır.';
    header('Location: index.php');
    exit;
}

// Sipariş detaylarını al
$sorgu = $pdo->prepare("
    SELECT sd.*, u.ad as urun_adi, u.resim
    FROM siparis_detaylari sd
    JOIN urunler u ON sd.urun_id = u.id
    WHERE sd.siparis_id = ?
");
$sorgu->execute([$siparis_id]);
$siparis_detaylari = $sorgu->fetchAll();

// Sipariş durumunu Türkçe olarak göster
function siparisdurumuTR($durum) {
    switch ($durum) {
        case 'beklemede':
            return 'Beklemede';
        case 'hazırlanıyor':
            return 'Hazırlanıyor';
        case 'kargoya_verildi':
            return 'Kargoya Verildi';
        case 'teslim_edildi':
            return 'Teslim Edildi';
        case 'iptal_edildi':
            return 'İptal Edildi';
        default:
            return 'Belirsiz';
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
        <div class="col-12 mb-4">
            <div class="alert alert-success text-center">
                <i class="fas fa-check-circle fa-2x mb-3"></i>
                <h3>Siparişiniz Başarıyla Oluşturuldu!</h3>
                <p class="mb-0">Sipariş numaranız: <strong>#<?php echo $siparis_id; ?></strong></p>
            </div>
        </div>
        
        <!-- Sipariş Detayları -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Sipariş Bilgileri</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Sipariş Numarası</h6>
                            <p class="mb-3">#<?php echo $siparis_id; ?></p>
                            
                            <h6 class="text-muted">Sipariş Tarihi</h6>
                            <p class="mb-3"><?php echo date('d.m.Y H:i', strtotime($siparis['siparis_tarihi'])); ?></p>
                            
                            <h6 class="text-muted">Ödeme Yöntemi</h6>
                            <p class="mb-md-0"><?php echo odemeYontemiTR($siparis['odeme_yontemi']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Sipariş Durumu</h6>
                            <p class="mb-3">
                                <span class="badge bg-warning text-dark"><?php echo siparisdurumuTR($siparis['durum']); ?></span>
                            </p>
                            
                            <h6 class="text-muted">Müşteri Bilgileri</h6>
                            <p class="mb-1"><?php echo $siparis['ad'] . ' ' . $siparis['soyad']; ?></p>
                            <p class="mb-1"><?php echo $siparis['email']; ?></p>
                            <?php if (!empty($siparis['telefon'])): ?>
                                <p class="mb-3"><?php echo $siparis['telefon']; ?></p>
                            <?php endif; ?>
                            
                            <h6 class="text-muted">Teslimat Adresi</h6>
                            <p class="mb-0"><?php echo nl2br($siparis['teslimat_adresi']); ?></p>
                        </div>
                    </div>
                    
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
                                <?php foreach ($siparis_detaylari as $detay): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($detay['resim'])): ?>
                                                <img src="/assets/images/products/<?php echo $detay['resim']; ?>" class="me-2" width="40" height="40" alt="<?php echo $detay['urun_adi']; ?>">
                                            <?php else: ?>
                                                <div class="me-2" style="width:40px;height:40px;background:#eee;display:flex;align-items:center;justify-content:center;">
                                                    <i class="fas fa-box"></i>
                                                </div>
                                            <?php endif; ?>
                                            <?php echo $detay['urun_adi']; ?>
                                        </div>
                                    </td>
                                    <td><?php echo number_format($detay['birim_fiyat'], 2, ',', '.'); ?> ₺</td>
                                    <td><?php echo $detay['miktar']; ?></td>
                                    <td class="fw-bold"><?php echo number_format($detay['birim_fiyat'] * $detay['miktar'], 2, ',', '.'); ?> ₺</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Toplam:</td>
                                    <td class="fw-bold"><?php echo number_format($siparis['toplam_tutar'], 2, ',', '.'); ?> ₺</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Ne Yapmalıyım -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i> Şimdi Ne Yapmalıyım?</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0">
                            <div class="d-flex">
                                <div class="me-3 text-primary">
                                    <i class="fas fa-envelope fa-2x"></i>
                                </div>
                                <div>
                                    <h6>E-posta Kontrolü</h6>
                                    <p class="text-muted mb-0 small">Sipariş detaylarınızla ilgili bir e-posta alacaksınız. Lütfen kontrol ediniz.</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0">
                            <div class="d-flex">
                                <div class="me-3 text-primary">
                                    <i class="fas fa-clipboard-list fa-2x"></i>
                                </div>
                                <div>
                                    <h6>Siparişlerim Sayfası</h6>
                                    <p class="text-muted mb-0 small">Siparişinizi <a href="/siparislerim.php">Siparişlerim</a> sayfasından takip edebilirsiniz.</p>
                                </div>
                            </div>
                        </li>
                        <?php if ($siparis['odeme_yontemi'] === 'havale'): ?>
                        <li class="list-group-item px-0">
                            <div class="d-flex">
                                <div class="me-3 text-primary">
                                    <i class="fas fa-university fa-2x"></i>
                                </div>
                                <div>
                                    <h6>Havale Bilgileri</h6>
                                    <p class="text-muted mb-0 small">Lütfen ödemenizi aşağıdaki banka hesabımıza yapınız ve dekont numaranızı bizimle paylaşınız.</p>
                                    <div class="alert alert-light mt-2 mb-0 p-2">
                                        <p class="mb-1 small"><strong>Banka:</strong> SportBank</p>
                                        <p class="mb-1 small"><strong>IBAN:</strong> TR00 0000 0000 0000 0000 0000 00</p>
                                        <p class="mb-0 small"><strong>Alıcı:</strong> SportMağazası Ltd. Şti.</p>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i> Ana Sayfaya Dön
                        </a>
                        <a href="/siparislerim.php" class="btn btn-outline-primary">
                            <i class="fas fa-clipboard-list me-2"></i> Siparişlerim
                        </a>
                        <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i> Bu Sayfayı Yazdır
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?> 