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
    $kategori_id = $_POST['kategori_id'];
    $fiyat = str_replace(',', '.', $_POST['fiyat']);
    $stok = $_POST['stok'];
    $aciklama = $_POST['aciklama'] ?? '';
    $etiketler = $_POST['etiketler'] ?? '';
    $aktif = isset($_POST['aktif']) ? 1 : 0;
    
    // Doğrulama
    $hatalar = [];
    
    if (empty($ad)) {
        $hatalar[] = "Ürün adı boş olamaz";
    }
    
    if (!is_numeric($fiyat) || $fiyat <= 0) {
        $hatalar[] = "Geçerli bir fiyat giriniz";
    }
    
    if (!is_numeric($stok) || $stok < 0) {
        $hatalar[] = "Geçerli bir stok miktarı giriniz";
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
                    $resim_adi = 'urun-' . time() . '.' . $uzanti;
                    $hedef_yol = '../uploads/urunler/' . $resim_adi;
                    
                    // Uploads klasörü yoksa oluştur
                    if (!file_exists('../uploads/urunler/')) {
                        mkdir('../uploads/urunler/', 0777, true);
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
                    INSERT INTO urunler (
                        ad, kategori_id, fiyat, 
                        stok_adedi, aciklama, resim, 
                        etiketler, aktif, eklenme_tarihi
                    ) VALUES (
                        :ad, :kategori_id, :fiyat, 
                        :stok, :aciklama, :resim, 
                        :etiketler, :aktif, NOW()
                    )
                ");
                
                $sorgu->execute([
                    'ad' => $ad,
                    'kategori_id' => $kategori_id,
                    'fiyat' => $fiyat,
                    'stok' => $stok,
                    'aciklama' => $aciklama,
                    'resim' => $resim_adi,
                    'etiketler' => $etiketler,
                    'aktif' => $aktif
                ]);
                
                $son_eklenen_id = $pdo->lastInsertId();
                
                // Ek resimleri yükle
                if (isset($_FILES['ek_resimler']) && is_array($_FILES['ek_resimler']['name'])) {
                    $resim_sayisi = count($_FILES['ek_resimler']['name']);
                    
                    for ($i = 0; $i < $resim_sayisi; $i++) {
                        if ($_FILES['ek_resimler']['error'][$i] === 0) {
                            $dosya_tipi = $_FILES['ek_resimler']['type'][$i];
                            
                            if (in_array($dosya_tipi, $izin_verilen_tipler)) {
                                $uzanti = pathinfo($_FILES['ek_resimler']['name'][$i], PATHINFO_EXTENSION);
                                $resim_adi = 'urun-' . time() . '-' . ($i+1) . '.' . $uzanti;
                                $hedef_yol = '../uploads/urunler/' . $resim_adi;
                                
                                if (move_uploaded_file($_FILES['ek_resimler']['tmp_name'][$i], $hedef_yol)) {
                                    // Resmi veritabanına kaydet
                                    $sorgu = $pdo->prepare("
                                        INSERT INTO urun_resimleri (urun_id, resim_url) 
                                        VALUES (:urun_id, :resim_yolu)
                                    ");
                                    
                                    $sorgu->execute([
                                        'urun_id' => $son_eklenen_id,
                                        'resim_yolu' => $resim_adi
                                    ]);
                                }
                            }
                        }
                    }
                }
                
                // Başarılı mesajı ile yönlendir
                header("Location: urunler.php?basarili=1");
                exit;
            }
        } catch (PDOException $e) {
            $hatalar[] = "Veritabanı hatası: " . $e->getMessage();
        }
    }
}

// Kategorileri getir
$sorgu = $pdo->query("SELECT * FROM kategoriler ORDER BY ad ASC");
$kategoriler = $sorgu->fetchAll(PDO::FETCH_ASSOC);

// Sayfa başlığı
$sayfa_basligi = "Yeni Ürün Ekle";

require_once 'templates/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4"><?php echo $sayfa_basligi; ?></h1>
        <a href="urunler.php" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i> Ürünlere Dön
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
    
    <div class="card mb-4">
        <div class="card-body">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Temel Bilgiler -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2">Temel Bilgiler</h5>
                            
                            <div class="mb-3">
                                <label for="ad" class="form-label">Ürün Adı <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ad" name="ad" value="<?php echo $_POST['ad'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="kategori_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select" id="kategori_id" name="kategori_id" required>
                                    <option value="">Kategori Seçin</option>
                                    <?php foreach ($kategoriler as $kategori): ?>
                                        <option value="<?php echo $kategori['id']; ?>" <?php echo (isset($_POST['kategori_id']) && $_POST['kategori_id'] == $kategori['id']) ? 'selected' : ''; ?>>
                                            <?php echo $kategori['ad']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="fiyat" class="form-label">Fiyat (₺) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="fiyat" name="fiyat" value="<?php echo $_POST['fiyat'] ?? ''; ?>" placeholder="0.00" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="stok" class="form-label">Stok <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="stok" name="stok" value="<?php echo $_POST['stok'] ?? '1'; ?>" min="0" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Açıklamalar -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2">Açıklamalar</h5>
                            
                            <div class="mb-3">
                                <label for="aciklama" class="form-label">Detaylı Açıklama</label>
                                <textarea class="form-control" id="aciklama" name="aciklama" rows="8"><?php echo $_POST['aciklama'] ?? ''; ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Etiketler -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2">Etiketler ve SEO</h5>
                            
                            <div class="mb-3">
                                <label for="etiketler" class="form-label">Etiketler</label>
                                <input type="text" class="form-control" id="etiketler" name="etiketler" value="<?php echo $_POST['etiketler'] ?? ''; ?>" placeholder="spor, ayakkabı, koşu">
                                <div class="form-text">Etiketleri virgül ile ayırın</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- Resim Yükleme -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2">Ürün Resimleri</h5>
                            
                            <div class="mb-3">
                                <label for="resim" class="form-label">Ana Ürün Resmi</label>
                                <input class="form-control" type="file" id="resim" name="resim" accept="image/*">
                                <div class="form-text">Önerilen boyut: 800x800 piksel</div>
                                
                                <div class="mt-2">
                                    <img id="resimOnizleme" src="#" alt="Resim önizleme" class="img-thumbnail d-none" style="max-height: 200px;">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="ek_resimler" class="form-label">Ek Resimler</label>
                                <input class="form-control" type="file" id="ek_resimler" name="ek_resimler[]" accept="image/*" multiple>
                                <div class="form-text">Birden fazla resim seçebilirsiniz</div>
                                
                                <div class="mt-2" id="ekResimlerOnizleme">
                                    <!-- Ek resimler burada gösterilecek -->
                                </div>
                            </div>
                        </div>
                        
                        <!-- Durum -->
                        <div class="card mb-4">
                            <div class="card-header">Durum</div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="aktif" name="aktif" <?php echo (!isset($_POST['aktif']) || $_POST['aktif']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="aktif">Aktif</label>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i> Ürünü Kaydet
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // SummerNote editörünü başlat
    if (document.getElementById('aciklama')) {
        $('#aciklama').summernote({
            placeholder: 'Ürün açıklamasını buraya yazın...',
            tabsize: 2,
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            lang: 'tr-TR'
        });
    }
    
    // Ana resim önizleme
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
    
    // Ek resimler önizleme
    document.getElementById('ek_resimler').addEventListener('change', function(e) {
        const ekResimlerOnizleme = document.getElementById('ekResimlerOnizleme');
        ekResimlerOnizleme.innerHTML = '';
        
        if (e.target.files.length > 0) {
            const row = document.createElement('div');
            row.className = 'row mt-2';
            
            for (let i = 0; i < e.target.files.length; i++) {
                const dosya = e.target.files[i];
                const dosyaURL = URL.createObjectURL(dosya);
                
                const col = document.createElement('div');
                col.className = 'col-4 mb-2';
                
                const img = document.createElement('img');
                img.src = dosyaURL;
                img.className = 'img-thumbnail';
                img.style.height = '100px';
                img.style.objectFit = 'cover';
                
                col.appendChild(img);
                row.appendChild(col);
            }
            
            ekResimlerOnizleme.appendChild(row);
        }
    });
});
</script>