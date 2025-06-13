<?php
require_once 'templates/header.php';

// Oturum kontrolü
if (!isLoggedIn()) {
    // Kullanıcı giriş yapmamışsa, giriş sayfasına yönlendir
    $_SESSION['error_message'] = 'Ödeme yapmak için giriş yapmalısınız.';
    header('Location: giris.php');
    exit;
}

// Sepet kontrolü
$sepet = sepetiGetir();
if (empty($sepet)) {
    // Sepet boşsa, sepet sayfasına yönlendir
    $_SESSION['error_message'] = 'Sepetinizde ürün bulunmamaktadır.';
    header('Location: sepet.php');
    exit;
}

// Sepet toplamını ve kargo ücretini hesapla
$sepetToplami = sepetToplaminiHesapla();
$kargoUcreti = 19.90; // Varsayılan kargo ücreti
if ($sepetToplami >= 500) {
    $kargoUcreti = 0; // 500 TL ve üzeri alışverişlerde ücretsiz kargo
}

// Kupon indirimi
$kuponIndirim = 0;
if (isset($_SESSION['kupon_indirim'])) {
    $kuponIndirim = $_SESSION['kupon_indirim'];
}

// Genel toplam
$genelToplam = $sepetToplami + $kargoUcreti - $kuponIndirim;

// Form gönderildiğinde siparişi oluştur
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        global $pdo;
        
        // Form verilerini al
        $adres = temizle($_POST['adres']);
        $odeme_yontemi = temizle($_POST['odeme_yontemi']);
        $kullanici_id = $_SESSION['kullanici_id'];
        
        // Stok kontrolü
        $stokYeterli = true;
        $stokHatasi = '';
        
        foreach ($sepet as $item) {
            if ($item['miktar'] > $item['urun']['stok_adedi']) {
                $stokYeterli = false;
                $stokHatasi .= $item['urun']['ad'] . ' ürününden istediğiniz adet stokta bulunmamaktadır. ';
            }
        }
        
        if (!$stokYeterli) {
            throw new Exception($stokHatasi);
        }
        
        // Siparişi veritabanına kaydet
        $pdo->beginTransaction();
        
        // Siparişi oluştur
        $sorgu = $pdo->prepare("
            INSERT INTO siparisler (kullanici_id, toplam_tutar, durum, odeme_yontemi, teslimat_adresi)
            VALUES (:kullanici_id, :toplam_tutar, :durum, :odeme_yontemi, :teslimat_adresi)
        ");
        
        $sorgu->execute([
            'kullanici_id' => $kullanici_id,
            'toplam_tutar' => $genelToplam,
            'durum' => 'beklemede',
            'odeme_yontemi' => $odeme_yontemi,
            'teslimat_adresi' => $adres
        ]);
        
        $siparis_id = $pdo->lastInsertId();
        
        // Sipariş detaylarını oluştur ve stokları güncelle
        foreach ($sepet as $item) {
            // Sipariş detayını ekle
            $sorgu = $pdo->prepare("
                INSERT INTO siparis_detaylari (siparis_id, urun_id, miktar, birim_fiyat)
                VALUES (:siparis_id, :urun_id, :miktar, :birim_fiyat)
            ");
            
            $sorgu->execute([
                'siparis_id' => $siparis_id,
                'urun_id' => $item['urun']['id'],
                'miktar' => $item['miktar'],
                'birim_fiyat' => $item['urun']['fiyat']
            ]);
            
            // Stok miktarını güncelle
            $yeniStok = $item['urun']['stok_adedi'] - $item['miktar'];
            $sorgu = $pdo->prepare("UPDATE urunler SET stok_adedi = :stok WHERE id = :id");
            $sorgu->execute([
                'stok' => $yeniStok,
                'id' => $item['urun']['id']
            ]);
        }
        
        $pdo->commit();
        
        // Sepeti temizle
        sepetiTemizle();
        
        // Varsa kupon indirimini temizle
        if (isset($_SESSION['kupon_indirim'])) {
            unset($_SESSION['kupon_indirim']);
        }
        
        // Başarılı mesajı oluştur
        $_SESSION['success_message'] = 'Siparişiniz başarıyla oluşturuldu. Sipariş numaranız: #' . $siparis_id;
        
        // Sipariş sonuç sayfasına yönlendir
        header('Location: siparis-tamamlandi.php?id=' . $siparis_id);
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Sipariş oluşturulurken bir hata oluştu: ' . $e->getMessage();
    }
}

// Kullanıcı bilgilerini al
$sorgu = $pdo->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$sorgu->execute([$_SESSION['kullanici_id']]);
$kullanici = $sorgu->fetch();
?>

<div class="container">
    <div class="row">
        <!-- Breadcrumb -->
        <div class="col-12 mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Ana Sayfa</a></li>
                    <li class="breadcrumb-item"><a href="/sepet.php">Sepet</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Ödeme</li>
                </ol>
            </nav>
        </div>
        
        <div class="col-12 mb-4">
            <h1 class="border-bottom pb-2">Ödeme Bilgileri</h1>
        </div>
        
        <!-- Ödeme Formu -->
        <div class="col-lg-8">
            <form method="post" id="odeme-formu">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i> Teslimat Adresi</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="adres" class="form-label">Adres</label>
                            <textarea class="form-control" id="adres" name="adres" rows="3" required><?php echo $kullanici['adres'] ?? ''; ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i> Ödeme Yöntemi</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="odeme_yontemi" id="kredi_karti" value="kredi_karti" checked>
                            <label class="form-check-label" for="kredi_karti">
                                <i class="far fa-credit-card me-2"></i> Kredi Kartı
                            </label>
                        </div>
                        
                        <div id="kredi_karti_formu">
                            <div class="mb-3">
                                <label for="kart_sahibi" class="form-label">Kart Sahibinin Adı Soyadı</label>
                                <input type="text" class="form-control" id="kart_sahibi" name="kart_sahibi">
                            </div>
                            <div class="mb-3">
                                <label for="kart_numarasi" class="form-label">Kart Numarası</label>
                                <input type="text" class="form-control" id="kart_numarasi" name="kart_numarasi" placeholder="1234 5678 9012 3456">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="son_kullanma" class="form-label">Son Kullanma Tarihi</label>
                                    <input type="text" class="form-control" id="son_kullanma" name="son_kullanma" placeholder="AA/YY">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="guvenlik_kodu" class="form-label">Güvenlik Kodu (CVV)</label>
                                    <input type="text" class="form-control" id="guvenlik_kodu" name="guvenlik_kodu" placeholder="123">
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="odeme_yontemi" id="havale" value="havale">
                            <label class="form-check-label" for="havale">
                                <i class="fas fa-university me-2"></i> Havale / EFT
                            </label>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="odeme_yontemi" id="kapida_odeme" value="kapida_odeme">
                            <label class="form-check-label" for="kapida_odeme">
                                <i class="fas fa-money-bill-wave me-2"></i> Kapıda Ödeme
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="onay" name="onay" required>
                            <label class="form-check-label" for="onay">
                                <a href="/satis-sozlesmesi" target="_blank">Satış sözleşmesini</a> ve 
                                <a href="/gizlilik-politikasi" target="_blank">gizlilik politikasını</a> okudum ve kabul ediyorum.
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-lock me-2"></i> Siparişi Tamamla
                    </button>
                    <a href="/sepet.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Sepete Geri Dön
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Sipariş Özeti -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i> Sipariş Özeti</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <?php foreach ($sepet as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($item['urun']['resim'])): ?>
                                                <img src="/assets/images/products/<?php echo $item['urun']['resim']; ?>" class="me-2" width="40" height="40" alt="<?php echo $item['urun']['ad']; ?>">
                                            <?php else: ?>
                                                <div class="me-2" style="width:40px;height:40px;background:#eee;display:flex;align-items:center;justify-content:center;">
                                                    <i class="fas fa-box"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div><?php echo $item['urun']['ad']; ?></div>
                                                <small class="text-muted"><?php echo $item['miktar']; ?> adet</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end fw-bold"><?php echo number_format($item['toplam'], 2, ',', '.'); ?> ₺</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <hr>
                    
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            Ara Toplam
                            <span><?php echo number_format($sepetToplami, 2, ',', '.'); ?> ₺</span>
                        </li>
                        
                        <?php if ($kuponIndirim > 0): ?>
                            <li class="list-group-item d-flex justify-content-between px-0 text-success">
                                Kupon İndirimi
                                <span>- <?php echo number_format($kuponIndirim, 2, ',', '.'); ?> ₺</span>
                            </li>
                        <?php endif; ?>
                        
                        <li class="list-group-item d-flex justify-content-between px-0">
                            Kargo
                            <?php if ($kargoUcreti == 0): ?>
                                <span class="text-success">Ücretsiz</span>
                            <?php else: ?>
                                <span><?php echo number_format($kargoUcreti, 2, ',', '.'); ?> ₺</span>
                            <?php endif; ?>
                        </li>
                        
                        <li class="list-group-item d-flex justify-content-between px-0 fw-bold">
                            <span class="text-primary">Genel Toplam</span>
                            <span class="text-primary"><?php echo number_format($genelToplam, 2, ',', '.'); ?> ₺</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="text-primary me-3"><i class="fas fa-shield-alt fa-2x"></i></div>
                        <div>
                            <h5 class="mb-1">Güvenli Ödeme</h5>
                            <p class="text-muted mb-0 small">SSL sertifikası ile güvenli alışveriş</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="text-primary me-3"><i class="fas fa-truck fa-2x"></i></div>
                        <div>
                            <h5 class="mb-1">Hızlı Teslimat</h5>
                            <p class="text-muted mb-0 small">24 saat içinde kargoya verilir</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="text-primary me-3"><i class="fas fa-undo-alt fa-2x"></i></div>
                        <div>
                            <h5 class="mb-1">Kolay İade</h5>
                            <p class="text-muted mb-0 small">14 gün içinde ücretsiz iade hakkı</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Ödeme yöntemi seçimi için JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const krediKartiForm = document.getElementById('kredi_karti_formu');
    const radiolar = document.querySelectorAll('input[name="odeme_yontemi"]');
    
    radiolar.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'kredi_karti') {
                krediKartiForm.style.display = 'block';
            } else {
                krediKartiForm.style.display = 'none';
            }
        });
    });
    
    // Form gönderiminde validasyon
    document.getElementById('odeme-formu').addEventListener('submit', function(e) {
        const odemeYontemi = document.querySelector('input[name="odeme_yontemi"]:checked').value;
        
        if (odemeYontemi === 'kredi_karti') {
            const kartSahibi = document.getElementById('kart_sahibi').value;
            const kartNumarasi = document.getElementById('kart_numarasi').value;
            const sonKullanma = document.getElementById('son_kullanma').value;
            const guvenlikKodu = document.getElementById('guvenlik_kodu').value;
            
            // Kredi kartı alanları boşsa uyarı göster (gerçek bir ödeme işlemi olmadığı için)
            if (!kartSahibi || !kartNumarasi || !sonKullanma || !guvenlikKodu) {
                alert('Lütfen kredi kartı bilgilerini doldurunuz.');
                e.preventDefault();
                return;
            }
        }
    });
});
</script>

<?php require_once 'templates/footer.php'; ?> 