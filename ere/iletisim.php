<?php
require_once 'templates/header.php';

// Form gönderildiğinde
$mesaj_gonderildi = false;
$hatalar = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad = trim($_POST['ad'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $konu = trim($_POST['konu'] ?? '');
    $mesaj = trim($_POST['mesaj'] ?? '');
    
    // Doğrulama
    if (empty($ad)) {
        $hatalar[] = 'Adınızı ve soyadınızı girmelisiniz.';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $hatalar[] = 'Geçerli bir e-posta adresi girmelisiniz.';
    }
    
    if (empty($konu)) {
        $hatalar[] = 'Konu girmelisiniz.';
    }
    
    if (empty($mesaj)) {
        $hatalar[] = 'Mesaj girmelisiniz.';
    }
    
    // Hata yoksa mesaj gönderildi varsayalım
    if (empty($hatalar)) {
        // Gerçek projede burada veritabanına kayıt veya e-posta gönderme işlemi yapılır
        $mesaj_gonderildi = true;
    }
}
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Ana Sayfa</a></li>
                    <li class="breadcrumb-item active" aria-current="page">İletişim</li>
                </ol>
            </nav>
            
            <h1 class="border-bottom pb-2 mb-4">İletişim</h1>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <?php if ($mesaj_gonderildi): ?>
                <div class="alert alert-success">
                    <h4 class="alert-heading">Mesajınız Alındı!</h4>
                    <p>Mesajınız için teşekkür ederiz. En kısa sürede size dönüş yapacağız.</p>
                </div>
            <?php else: ?>
                <h2 class="mb-4">Bize Yazın</h2>
                
                <?php if (!empty($hatalar)): ?>
                    <div class="alert alert-danger">
                        <h5 class="alert-heading">Hata!</h5>
                        <ul class="mb-0">
                            <?php foreach ($hatalar as $hata): ?>
                                <li><?php echo $hata; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form action="" method="post">
                    <div class="mb-3">
                        <label for="ad" class="form-label">Adınız Soyadınız <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ad" name="ad" value="<?php echo $_POST['ad'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">E-posta Adresiniz <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $_POST['email'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="konu" class="form-label">Konu <span class="text-danger">*</span></label>
                        <select class="form-select" id="konu" name="konu" required>
                            <option value="" selected disabled>Konu Seçin</option>
                            <option value="Sipariş" <?php echo (isset($_POST['konu']) && $_POST['konu'] === 'Sipariş') ? 'selected' : ''; ?>>Sipariş Hakkında</option>
                            <option value="İade" <?php echo (isset($_POST['konu']) && $_POST['konu'] === 'İade') ? 'selected' : ''; ?>>İade ve Değişim</option>
                            <option value="Ürün" <?php echo (isset($_POST['konu']) && $_POST['konu'] === 'Ürün') ? 'selected' : ''; ?>>Ürün Bilgisi</option>
                            <option value="Şikayet" <?php echo (isset($_POST['konu']) && $_POST['konu'] === 'Şikayet') ? 'selected' : ''; ?>>Şikayet</option>
                            <option value="Diğer" <?php echo (isset($_POST['konu']) && $_POST['konu'] === 'Diğer') ? 'selected' : ''; ?>>Diğer</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="mesaj" class="form-label">Mesajınız <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="mesaj" name="mesaj" rows="6" required><?php echo $_POST['mesaj'] ?? ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i> Mesaj Gönder
                    </button>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="col-lg-6">
            <h2 class="mb-4">İletişim Bilgileri</h2>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Mağaza Adresi</h5>
                    <div class="d-flex align-items-start mb-3">
                        <i class="fas fa-map-marker-alt fa-2x text-primary me-3"></i>
                        <div>
                            <p class="mb-0">Atatürk Caddesi No: 123</p>
                            <p class="mb-0">Beşiktaş / İstanbul</p>
                            <p class="mb-0">34000</p>
                        </div>
                    </div>
                    
                    <h5 class="card-title mb-3">İletişim</h5>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-phone fa-2x text-primary me-3"></i>
                        <div>
                            <p class="mb-0"><strong>Telefon:</strong> +90 212 123 45 67</p>
                            <p class="mb-0"><strong>Fax:</strong> +90 212 123 45 68</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-envelope fa-2x text-primary me-3"></i>
                        <div>
                            <p class="mb-0"><strong>E-posta:</strong> <a href="mailto:info@spormagazasi.com">info@spormagazasi.com</a></p>
                            <p class="mb-0"><strong>Destek:</strong> <a href="mailto:destek@spormagazasi.com">destek@spormagazasi.com</a></p>
                        </div>
                    </div>
                    
                    <h5 class="card-title mb-3">Çalışma Saatleri</h5>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock fa-2x text-primary me-3"></i>
                        <div>
                            <p class="mb-0"><strong>Pazartesi - Cuma:</strong> 09:00 - 18:00</p>
                            <p class="mb-0"><strong>Cumartesi:</strong> 09:00 - 14:00</p>
                            <p class="mb-0"><strong>Pazar:</strong> Kapalı</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <h2 class="mb-3">Harita</h2>
            <!-- Google Harita Yerleştirme -->
            <div class="embed-responsive ratio ratio-4x3">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3008.9862774084075!2d28.99039291541801!3d41.04385292665586!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cab703f4b97d61%3A0x35f4b75077caaba!2zQmXFn2lrdGHFnywgSXN0YW5idWw!5e0!3m2!1sen!2str!4v1658930321919!5m2!1sen!2str" 
                        class="embed-responsive-item w-100 border rounded" 
                        style="height: 300px;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Sosyal Medyada Bizi Takip Edin</h2>
            <div class="d-flex justify-content-center">
                <a href="#" class="btn btn-outline-primary btn-lg mx-2">
                    <i class="fab fa-facebook-f me-2"></i> Facebook
                </a>
                <a href="#" class="btn btn-outline-primary btn-lg mx-2">
                    <i class="fab fa-instagram me-2"></i> Instagram
                </a>
                <a href="#" class="btn btn-outline-primary btn-lg mx-2">
                    <i class="fab fa-twitter me-2"></i> Twitter
                </a>
                <a href="#" class="btn btn-outline-primary btn-lg mx-2">
                    <i class="fab fa-youtube me-2"></i> YouTube
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?> 