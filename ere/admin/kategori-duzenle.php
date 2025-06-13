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

// ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: kategoriler.php');
    exit;
}

$kategori_id = (int) $_GET['id'];

// Kategoriler tablosunda aktif sütunu yoksa oluştur
try {
    $sorgu = $pdo->query("SHOW COLUMNS FROM kategoriler LIKE 'aktif'");
    if ($sorgu->rowCount() == 0) {
        $pdo->exec("ALTER TABLE kategoriler ADD aktif TINYINT(1) NOT NULL DEFAULT 1");
    }
} catch (PDOException $e) {
    // Hata olursa devam et, kritik bir hata değil
}

// Kategori bilgilerini çek
$sorgu = $pdo->prepare("SELECT * FROM kategoriler WHERE id = :id");
$sorgu->execute(['id' => $kategori_id]);
$kategori = $sorgu->fetch(PDO::FETCH_ASSOC);

if (!$kategori) {
    header('Location: kategoriler.php');
    exit;
}

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad = trim($_POST['ad']);    
    $aciklama = trim($_POST['aciklama'] ?? '');    
    $aktif = isset($_POST['aktif']) ? 1 : 0;
    
    // Doğrulama
    $hatalar = [];
    
    if (empty($ad)) {
        $hatalar[] = "Kategori adı boş olamaz";
    }
    
    // Hata yoksa devam et
    if (empty($hatalar)) {
        try {
            // Mevcut resim
            $resim_adi = $kategori['resim'];
            
            // Resim yükleme
            if (isset($_FILES['resim']) && $_FILES['resim']['error'] === 0) {
                // İzin verilen dosya tipleri
                $izin_verilen_tipler = ['image/jpeg', 'image/png', 'image/webp'];
                
                if (in_array($_FILES['resim']['type'], $izin_verilen_tipler)) {
                    // Yeni dosya adı oluştur
                    $uzanti = pathinfo($_FILES['resim']['name'], PATHINFO_EXTENSION);
                    $resim_adi = 'kategori-' . time() . '.' . $uzanti;
                    $hedef_yol = '../uploads/kategoriler/' . $resim_adi;
                    
                    // Uploads klasörü yoksa oluştur
                    if (!file_exists('../uploads/kategoriler/')) {
                        mkdir('../uploads/kategoriler/', 0777, true);
                    }
                    
                    // Dosyayı yükle
                    if (move_uploaded_file($_FILES['resim']['tmp_name'], $hedef_yol)) {
                        // Başarılı
                        
                        // Eski resmi sil
                        if ($kategori['resim'] && file_exists('../uploads/kategoriler/' . $kategori['resim'])) {
                            unlink('../uploads/kategoriler/' . $kategori['resim']);
                        }
                    } else {
                        $hatalar[] = "Resim yüklenirken hata oluştu";
                    }
                } else {
                    $hatalar[] = "Sadece JPG, PNG ve WEBP formatında resimler yüklenebilir";
                }
            }
            
            // Resmi kaldır
            if (isset($_POST['resim_kaldir']) && $_POST['resim_kaldir'] == 1) {
                // Eski resmi sil
                if ($kategori['resim'] && file_exists('../uploads/kategoriler/' . $kategori['resim'])) {
                    unlink('../uploads/kategoriler/' . $kategori['resim']);
                }
                $resim_adi = null;
            }
            
            // Hata yoksa veritabanını güncelle
            if (empty($hatalar)) {
                $sorgu = $pdo->prepare("
                    UPDATE kategoriler SET
                        ad = :ad,
                        aciklama = :aciklama,
                        resim = :resim,
                        aktif = :aktif
                    WHERE id = :id
                ");
                
                $sorgu->execute([
                    'ad' => $ad,
                    'aciklama' => $aciklama,
                    'resim' => $resim_adi,
                    'aktif' => $aktif,
                    'id' => $kategori_id
                ]);
                
                // Başarılı mesajı ile yönlendir
                header("Location: kategoriler.php?basarili=1");
                exit;
            }
        } catch (PDOException $e) {
            $hatalar[] = "Veritabanı hatası: " . $e->getMessage();
        }
    }
}

// Aktif değerini ayarla (yoksa varsayılan olarak 1 kabul et)
if (!isset($kategori['aktif'])) {
    $kategori['aktif'] = 1;
}

// Sayfa başlığı
$sayfa_basligi = "Kategori Düzenle: " . $kategori['ad'];

require_once 'templates/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4"><?php echo $sayfa_basligi; ?></h1>
        <a href="kategoriler.php" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i> Kategorilere Dön
        </a>
    </div>
    
    <?php if (!empty($hatalar)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($hatalar as $hata): ?>
                    <li><?php echo $hata; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-folder me-1"></i> Kategori Bilgileri
                </div>
                <div class="card-body">
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="ad" class="form-label">Kategori Adı <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ad" name="ad" value="<?php echo isset($_POST['ad']) ? htmlspecialchars($_POST['ad']) : htmlspecialchars($kategori['ad']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="aciklama" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="aciklama" name="aciklama" rows="3"><?php echo isset($_POST['aciklama']) ? htmlspecialchars($_POST['aciklama']) : htmlspecialchars($kategori['aciklama']); ?></textarea>
                            <div class="form-text">Kategori hakkında kısa bir açıklama (isteğe bağlı)</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <!-- Sıralama alanı kullanılmıyor -->
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="resim" class="form-label">Kategori Resmi</label>
                                
                                <?php if ($kategori['resim'] && file_exists('../uploads/kategoriler/' . $kategori['resim'])): ?>
                                    <div class="mb-2">
                                        <img src="../uploads/kategoriler/<?php echo $kategori['resim']; ?>" alt="<?php echo $kategori['ad']; ?>" class="img-thumbnail" style="max-height: 150px;">
                                        
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" id="resim_kaldir" name="resim_kaldir" value="1">
                                            <label class="form-check-label" for="resim_kaldir">Mevcut resmi kaldır</label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <input class="form-control" type="file" id="resim" name="resim" accept="image/*">
                                <div class="form-text">Önerilen boyut: 600x400 piksel</div>
                                
                                <div class="mt-2">
                                    <img id="resimOnizleme" src="#" alt="Resim önizleme" class="img-thumbnail d-none" style="max-height: 150px;">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="aktif" name="aktif" <?php echo (isset($_POST['aktif']) || (isset($kategori['aktif']) && $kategori['aktif'] == 1)) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="aktif">Aktif</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Değişiklikleri Kaydet
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i> Kategori Bilgileri
                </div>
                <div class="card-body">
                    <p>Kategoriler, ürünlerinizi düzenlemek ve müşterilerinizin istediği ürünleri daha kolay bulmasını sağlamak için önemlidir.</p>
                    
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-lightbulb me-2"></i> <strong>İpucu:</strong> 
                        <ul class="mb-0 ps-3">
                            <li>Anlaşılır kategori isimleri kullanın</li>
                            <li>Görsel kullanımı kategori sayfalarını daha çekici hale getirir</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-chart-pie me-1"></i> Kategori İstatistikleri
                </div>
                <div class="card-body">
                    <?php
                    // Kategori ürün sayısını getir
                    $sorgu = $pdo->prepare("SELECT COUNT(*) FROM urunler WHERE kategori_id = :id");
                    $sorgu->execute(['id' => $kategori_id]);
                    $urun_sayisi = $sorgu->fetchColumn();
                    ?>
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Ürün Sayısı:</span>
                        <span class="badge bg-primary"><?php echo $urun_sayisi; ?></span>
                    </div>
                    
                    <?php if (isset($kategori['created_at']) && !empty($kategori['created_at'])): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Oluşturulma Tarihi:</span>
                        <span class="badge bg-secondary"><?php echo date('d.m.Y', strtotime($kategori['created_at'])); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($urun_sayisi > 0): ?>
                        <div class="mt-3">
                            <a href="urunler.php?kategori=<?php echo $kategori_id; ?>" class="btn btn-sm btn-outline-primary w-100">
                                <i class="fas fa-box me-1"></i> Bu Kategorideki Ürünleri Gör
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Resim önizleme
    document.getElementById('resim').addEventListener('change', function(e) {
        const resimOnizleme = document.getElementById('resimOnizleme');
        
        if (e.target.files.length > 0) {
            const dosya = e.target.files[0];
            const dosyaURL = URL.createObjectURL(dosya);
            
            resimOnizleme.src = dosyaURL;
            resimOnizleme.classList.remove('d-none');
        } else {
            resimOnizleme.classList.add('d-none');
        }
    });
    
    // Resim kaldır checkbox
    if (document.getElementById('resim_kaldir')) {
        document.getElementById('resim_kaldir').addEventListener('change', function(e) {
            const resimInput = document.getElementById('resim');
            
            if (e.target.checked) {
                resimInput.disabled = true;
            } else {
                resimInput.disabled = false;
            }
        });
    }
});
</script>

<?php require_once 'templates/footer.php'; ?> 