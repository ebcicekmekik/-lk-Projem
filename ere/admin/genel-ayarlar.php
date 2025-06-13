<?php
require_once '../includes/functions.php';

// Oturum kontrolü
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin kontrolü
if (!isAdmin()) {
    header('Location: ../giris.php');
    exit;
}

// Ayarları güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Site başlığı
        if (isset($_POST['site_baslik'])) {
            $site_baslik = trim($_POST['site_baslik']);
            $sorgu = $pdo->prepare("UPDATE ayarlar SET deger = :deger WHERE anahtar = 'site_baslik'");
            $sorgu->execute(['deger' => $site_baslik]);
        }
        
        // Site açıklaması
        if (isset($_POST['site_aciklama'])) {
            $site_aciklama = trim($_POST['site_aciklama']);
            $sorgu = $pdo->prepare("UPDATE ayarlar SET deger = :deger WHERE anahtar = 'site_aciklama'");
            $sorgu->execute(['deger' => $site_aciklama]);
        }
        
        // Site anahtar kelimeleri
        if (isset($_POST['site_keywords'])) {
            $site_keywords = trim($_POST['site_keywords']);
            $sorgu = $pdo->prepare("UPDATE ayarlar SET deger = :deger WHERE anahtar = 'site_keywords'");
            $sorgu->execute(['deger' => $site_keywords]);
        }
        
        // İletişim bilgileri
        if (isset($_POST['iletisim_email'])) {
            $iletisim_email = trim($_POST['iletisim_email']);
            $sorgu = $pdo->prepare("UPDATE ayarlar SET deger = :deger WHERE anahtar = 'iletisim_email'");
            $sorgu->execute(['deger' => $iletisim_email]);
        }
        
        if (isset($_POST['iletisim_telefon'])) {
            $iletisim_telefon = trim($_POST['iletisim_telefon']);
            $sorgu = $pdo->prepare("UPDATE ayarlar SET deger = :deger WHERE anahtar = 'iletisim_telefon'");
            $sorgu->execute(['deger' => $iletisim_telefon]);
        }
        
        if (isset($_POST['iletisim_adres'])) {
            $iletisim_adres = trim($_POST['iletisim_adres']);
            $sorgu = $pdo->prepare("UPDATE ayarlar SET deger = :deger WHERE anahtar = 'iletisim_adres'");
            $sorgu->execute(['deger' => $iletisim_adres]);
        }
        
        // Sosyal medya
        if (isset($_POST['facebook'])) {
            $facebook = trim($_POST['facebook']);
            $sorgu = $pdo->prepare("UPDATE ayarlar SET deger = :deger WHERE anahtar = 'facebook'");
            $sorgu->execute(['deger' => $facebook]);
        }
        
        if (isset($_POST['instagram'])) {
            $instagram = trim($_POST['instagram']);
            $sorgu = $pdo->prepare("UPDATE ayarlar SET deger = :deger WHERE anahtar = 'instagram'");
            $sorgu->execute(['deger' => $instagram]);
        }
        
        if (isset($_POST['twitter'])) {
            $twitter = trim($_POST['twitter']);
            $sorgu = $pdo->prepare("UPDATE ayarlar SET deger = :deger WHERE anahtar = 'twitter'");
            $sorgu->execute(['deger' => $twitter]);
        }
        
        // Logo yükleme
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
            $dosya_tipi = $_FILES['logo']['type'];
            $izin_verilen = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (in_array($dosya_tipi, $izin_verilen)) {
                $uzanti = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $yeni_isim = 'logo_' . time() . '.' . $uzanti;
                $hedef_yol = '../uploads/site/' . $yeni_isim;
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $hedef_yol)) {
                    // Eski logoyu sil
                    $sorgu = $pdo->query("SELECT deger FROM ayarlar WHERE anahtar = 'logo'");
                    $eski_logo = $sorgu->fetchColumn();
                    
                    if ($eski_logo && file_exists('../uploads/site/' . $eski_logo)) {
                        unlink('../uploads/site/' . $eski_logo);
                    }
                    
                    // Veritabanını güncelle
                    $sorgu = $pdo->prepare("UPDATE ayarlar SET deger = :deger WHERE anahtar = 'logo'");
                    $sorgu->execute(['deger' => $yeni_isim]);
                }
            }
        }
        
        // Favicon yükleme
        if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === 0) {
            $dosya_tipi = $_FILES['favicon']['type'];
            $izin_verilen = ['image/jpeg', 'image/png', 'image/gif', 'image/x-icon'];
            
            if (in_array($dosya_tipi, $izin_verilen)) {
                $uzanti = pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION);
                $yeni_isim = 'favicon_' . time() . '.' . $uzanti;
                $hedef_yol = '../uploads/site/' . $yeni_isim;
                
                if (move_uploaded_file($_FILES['favicon']['tmp_name'], $hedef_yol)) {
                    // Eski favicon'u sil
                    $sorgu = $pdo->query("SELECT deger FROM ayarlar WHERE anahtar = 'favicon'");
                    $eski_favicon = $sorgu->fetchColumn();
                    
                    if ($eski_favicon && file_exists('../uploads/site/' . $eski_favicon)) {
                        unlink('../uploads/site/' . $eski_favicon);
                    }
                    
                    // Veritabanını güncelle
                    $sorgu = $pdo->prepare("UPDATE ayarlar SET deger = :deger WHERE anahtar = 'favicon'");
                    $sorgu->execute(['deger' => $yeni_isim]);
                }
            }
        }
        
        $basarili = "Ayarlar başarıyla güncellendi.";
    } catch (PDOException $e) {
        $hata = "Bir hata oluştu: " . $e->getMessage();
    }
}

// Mevcut ayarları getir
$ayarlar = [];
$sorgu = $pdo->query("SELECT * FROM ayarlar");
while ($row = $sorgu->fetch()) {
    $ayarlar[$row['anahtar']] = $row['deger'];
}

// Sayfa başlığı
$sayfa_basligi = "Genel Ayarlar";

require_once 'templates/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo $sayfa_basligi; ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Genel Ayarlar</li>
    </ol>
    
    <?php if (isset($hata)): ?>
        <div class="alert alert-danger"><?php echo $hata; ?></div>
    <?php endif; ?>
    
    <?php if (isset($basarili)): ?>
        <div class="alert alert-success"><?php echo $basarili; ?></div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-cog me-1"></i> Site Ayarları
        </div>
        <div class="card-body">
            <form action="" method="post" enctype="multipart/form-data">
                <ul class="nav nav-tabs" id="ayarlarTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="genel-tab" data-bs-toggle="tab" data-bs-target="#genel" type="button" role="tab" aria-controls="genel" aria-selected="true">Genel</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="iletisim-tab" data-bs-toggle="tab" data-bs-target="#iletisim" type="button" role="tab" aria-controls="iletisim" aria-selected="false">İletişim</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sosyal-tab" data-bs-toggle="tab" data-bs-target="#sosyal" type="button" role="tab" aria-controls="sosyal" aria-selected="false">Sosyal Medya</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="logo-tab" data-bs-toggle="tab" data-bs-target="#logo" type="button" role="tab" aria-controls="logo" aria-selected="false">Logo & Favicon</button>
                    </li>
                </ul>
                
                <div class="tab-content p-4 border border-top-0 rounded-bottom" id="ayarlarTabContent">
                    <!-- Genel Ayarlar -->
                    <div class="tab-pane fade show active" id="genel" role="tabpanel" aria-labelledby="genel-tab">
                        <div class="mb-3">
                            <label for="site_baslik" class="form-label">Site Başlığı</label>
                            <input type="text" class="form-control" id="site_baslik" name="site_baslik" value="<?php echo htmlspecialchars($ayarlar['site_baslik'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="site_aciklama" class="form-label">Site Açıklaması</label>
                            <textarea class="form-control" id="site_aciklama" name="site_aciklama" rows="3"><?php echo htmlspecialchars($ayarlar['site_aciklama'] ?? ''); ?></textarea>
                            <div class="form-text">Meta açıklama olarak kullanılır, SEO için önemlidir.</div>
                        </div>
                        <div class="mb-3">
                            <label for="site_keywords" class="form-label">Anahtar Kelimeler</label>
                            <input type="text" class="form-control" id="site_keywords" name="site_keywords" value="<?php echo htmlspecialchars($ayarlar['site_keywords'] ?? ''); ?>">
                            <div class="form-text">Virgülle ayırarak yazın. Örn: spor, ayakkabı, giyim</div>
                        </div>
                    </div>
                    
                    <!-- İletişim Ayarları -->
                    <div class="tab-pane fade" id="iletisim" role="tabpanel" aria-labelledby="iletisim-tab">
                        <div class="mb-3">
                            <label for="iletisim_email" class="form-label">E-posta Adresi</label>
                            <input type="email" class="form-control" id="iletisim_email" name="iletisim_email" value="<?php echo htmlspecialchars($ayarlar['iletisim_email'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="iletisim_telefon" class="form-label">Telefon Numarası</label>
                            <input type="text" class="form-control" id="iletisim_telefon" name="iletisim_telefon" value="<?php echo htmlspecialchars($ayarlar['iletisim_telefon'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="iletisim_adres" class="form-label">Adres</label>
                            <textarea class="form-control" id="iletisim_adres" name="iletisim_adres" rows="3"><?php echo htmlspecialchars($ayarlar['iletisim_adres'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Sosyal Medya Ayarları -->
                    <div class="tab-pane fade" id="sosyal" role="tabpanel" aria-labelledby="sosyal-tab">
                        <div class="mb-3">
                            <label for="facebook" class="form-label">Facebook</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fab fa-facebook"></i></span>
                                <input type="text" class="form-control" id="facebook" name="facebook" value="<?php echo htmlspecialchars($ayarlar['facebook'] ?? ''); ?>">
                            </div>
                            <div class="form-text">Tam URL adresi girin. Örn: https://facebook.com/sayfaadi</div>
                        </div>
                        <div class="mb-3">
                            <label for="instagram" class="form-label">Instagram</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fab fa-instagram"></i></span>
                                <input type="text" class="form-control" id="instagram" name="instagram" value="<?php echo htmlspecialchars($ayarlar['instagram'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="twitter" class="form-label">Twitter</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fab fa-twitter"></i></span>
                                <input type="text" class="form-control" id="twitter" name="twitter" value="<?php echo htmlspecialchars($ayarlar['twitter'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Logo ve Favicon Ayarları -->
                    <div class="tab-pane fade" id="logo" role="tabpanel" aria-labelledby="logo-tab">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="logo" class="form-label">Site Logosu</label>
                                <input class="form-control mb-3" type="file" id="logo" name="logo" accept="image/*">
                                <?php if (!empty($ayarlar['logo']) && file_exists('../uploads/site/' . $ayarlar['logo'])): ?>
                                    <div class="mt-2">
                                        <p>Mevcut Logo:</p>
                                        <img src="../uploads/site/<?php echo $ayarlar['logo']; ?>" alt="Logo" class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="favicon" class="form-label">Favicon (Site İkonu)</label>
                                <input class="form-control mb-3" type="file" id="favicon" name="favicon" accept="image/*">
                                <?php if (!empty($ayarlar['favicon']) && file_exists('../uploads/site/' . $ayarlar['favicon'])): ?>
                                    <div class="mt-2">
                                        <p>Mevcut Favicon:</p>
                                        <img src="../uploads/site/<?php echo $ayarlar['favicon']; ?>" alt="Favicon" class="img-thumbnail" style="max-height: 50px;">
                                    </div>
                                <?php endif; ?>
                                <div class="form-text">32x32 px boyutunda PNG, ICO formatında olmalıdır.</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Ayarları Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab kontrolü için URL hash kontrolü
    let hash = window.location.hash;
    if (hash) {
        $('.nav-tabs a[href="' + hash + '"]').tab('show');
    }
    
    // Tab değişiminde URL hash'i güncelle
    $('.nav-tabs a').on('shown.bs.tab', function(e) {
        let id = $(e.target).attr('data-bs-target');
        if (history.pushState) {
            history.pushState(null, null, id);
        } else {
            location.hash = id;
        }
    });
    
    // 3 saniye sonra alertleri gizle
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 3000);
});
</script>

<?php require_once 'templates/footer.php'; ?> 