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

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad = trim($_POST['ad']);    
    $aciklama = trim($_POST['aciklama'] ?? '');    
    // Şimdilik üst kategori ve sıralama kullanmıyoruz
    $siralama = 0;
    $aktif = isset($_POST['aktif']) ? 1 : 0;
    
    // Doğrulama
    $hatalar = [];
    
    if (empty($ad)) {
        $hatalar[] = "Kategori adı boş olamaz";
    }
    
    // Hata yoksa devam et
    if (empty($hatalar)) {
        try {
            // Slug artık kullanılmıyor
            // Resim yükleme
            $resim_adi = null;
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
                    } else {
                        $hatalar[] = "Resim yüklenirken hata oluştu";
                    }
                } else {
                    $hatalar[] = "Sadece JPG, PNG ve WEBP formatında resimler yüklenebilir";
                }
            }
            
            // Hata yoksa veritabanına kaydet
            if (empty($hatalar)) {
                $sorgu = $pdo->prepare("
                    INSERT INTO kategoriler (
                        ad, aciklama, resim
                    ) VALUES (
                        :ad, :aciklama, :resim
                    )
                ");
                
                $sorgu->execute([
                    'ad' => $ad,
                    'aciklama' => $aciklama,
                    'resim' => $resim_adi
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

// Üst kategoriler şimdilik kullanılmıyor

// Sayfa başlığı
$sayfa_basligi = "Yeni Kategori Ekle";

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
                            <input type="text" class="form-control" id="ad" name="ad" value="<?php echo $_POST['ad'] ?? ''; ?>" required>
                        </div>
                        
                                                <!-- Üst kategori özelliği şu an kullanılmıyor -->                        <!--                        <div class="mb-3">                            <label for="ust_kategori_id" class="form-label">Üst Kategori</label>                            <select class="form-select" id="ust_kategori_id" name="ust_kategori_id">                                <option value="">Ana Kategori Olarak Ekle</option>                            </select>                            <div class="form-text">Alt kategori olarak eklemek istiyorsanız üst kategori seçin</div>                        </div>                        -->
                        
                        <div class="mb-3">
                            <label for="aciklama" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="aciklama" name="aciklama" rows="3"><?php echo $_POST['aciklama'] ?? ''; ?></textarea>
                            <div class="form-text">Kategori hakkında kısa bir açıklama (isteğe bağlı)</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <!-- Sıralama alanı kaldırıldı -->
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="resim" class="form-label">Kategori Resmi</label>
                                <input class="form-control" type="file" id="resim" name="resim" accept="image/*">
                                <div class="form-text">Önerilen boyut: 600x400 piksel</div>
                                <div class="mt-2">
                                    <img id="resimOnizleme" src="#" alt="Resim önizleme" class="img-thumbnail d-none" style="max-height: 150px;">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="aktif" name="aktif" <?php echo (!isset($_POST['aktif']) || $_POST['aktif']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="aktif">Aktif</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Kategori Kaydet
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
                            <li>Alt kategoriler için üst kategori seçmeyi unutmayın</li>
                            <li>Görsel kullanımı kategori sayfalarını daha çekici hale getirir</li>
                        </ul>
                    </div>
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
});
</script>

<?php require_once 'templates/footer.php'; ?> 