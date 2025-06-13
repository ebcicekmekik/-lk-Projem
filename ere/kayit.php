<?php
require_once 'includes/functions.php';

// Oturum başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kullanıcı zaten giriş yapmışsa ana sayfaya yönlendir
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad = isset($_POST['ad']) ? temizle($_POST['ad']) : '';
    $soyad = isset($_POST['soyad']) ? temizle($_POST['soyad']) : '';
    $email = isset($_POST['email']) ? temizle($_POST['email']) : '';
    $sifre = isset($_POST['sifre']) ? $_POST['sifre'] : '';
    $sifreTekrar = isset($_POST['sifre_tekrar']) ? $_POST['sifre_tekrar'] : '';
    $telefon = isset($_POST['telefon']) ? temizle($_POST['telefon']) : '';
    $adres = isset($_POST['adres']) ? temizle($_POST['adres']) : '';
    
    // Hata kontrolü
    $errors = array();
    
    if (empty($ad)) {
        $errors[] = "Ad gereklidir.";
    }
    
    if (empty($soyad)) {
        $errors[] = "Soyad gereklidir.";
    }
    
    if (empty($email)) {
        $errors[] = "E-posta adresi gereklidir.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Geçerli bir e-posta adresi giriniz.";
    }
    
    if (empty($sifre)) {
        $errors[] = "Şifre gereklidir.";
    } elseif (strlen($sifre) < 6) {
        $errors[] = "Şifre en az 6 karakter olmalıdır.";
    }
    
    if ($sifre !== $sifreTekrar) {
        $errors[] = "Şifreler eşleşmiyor.";
    }
    
    // E-posta adresi daha önce kullanılmış mı kontrol et
    $sorgu = $pdo->prepare("SELECT COUNT(*) FROM kullanicilar WHERE email = ?");
    $sorgu->execute([$email]);
    
    if ($sorgu->fetchColumn() > 0) {
        $errors[] = "Bu e-posta adresi zaten kullanılıyor.";
    }
    
    // Hata yoksa kayıt işlemini yap
    if (empty($errors)) {
        $sifreHash = password_hash($sifre, PASSWORD_DEFAULT);
        
        $sorgu = $pdo->prepare("INSERT INTO kullanicilar (ad, soyad, email, sifre, telefon, adres, rol) VALUES (?, ?, ?, ?, ?, ?, 'kullanici')");
        $result = $sorgu->execute([$ad, $soyad, $email, $sifreHash, $telefon, $adres]);
        
        if ($result) {
            $kullaniciId = $pdo->lastInsertId();
            
            // Oturum bilgilerini ayarla
            $_SESSION['kullanici_id'] = $kullaniciId;
            $_SESSION['kullanici_ad'] = $ad;
            $_SESSION['kullanici_soyad'] = $soyad;
            $_SESSION['kullanici_email'] = $email;
            $_SESSION['kullanici_rol'] = 'kullanici';
            
            $_SESSION['success_message'] = "Kayıt başarılı. Hoş geldiniz, " . $ad . "!";
            
            // Sepetteki ürünleri veritabanına aktar
            if (!empty($_SESSION['sepet'])) {
                foreach ($_SESSION['sepet'] as $urunId => $item) {
                    $sorgu = $pdo->prepare("INSERT INTO sepet (kullanici_id, urun_id, miktar) VALUES (?, ?, ?)");
                    $sorgu->execute([$kullaniciId, $urunId, $item['miktar']]);
                }
            }
            
            // Yönlendirilecek sayfayı kontrol et
            $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php';
            unset($_SESSION['redirect_after_login']);
            
            header('Location: ' . $redirect);
            exit;
        } else {
            $errors[] = "Kayıt oluşturulurken bir hata oluştu. Lütfen tekrar deneyin.";
        }
    }
}
?>

<?php require_once 'templates/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center py-3">
                <h2 class="h4 mb-0">Kayıt Ol</h2>
            </div>
            <div class="card-body p-4">
                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="kayit.php">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ad" class="form-label">Ad</label>
                            <input type="text" class="form-control" id="ad" name="ad" value="<?php echo isset($ad) ? $ad : ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="soyad" class="form-label">Soyad</label>
                            <input type="text" class="form-control" id="soyad" name="soyad" value="<?php echo isset($soyad) ? $soyad : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">E-posta Adresi</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                        <div class="form-text">E-posta adresiniz giriş yaparken kullanıcı adınız olacaktır.</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="sifre" class="form-label">Şifre</label>
                            <input type="password" class="form-control" id="sifre" name="sifre" required>
                            <div class="form-text">Şifreniz en az 6 karakter olmalıdır.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sifre_tekrar" class="form-label">Şifre Tekrar</label>
                            <input type="password" class="form-control" id="sifre_tekrar" name="sifre_tekrar" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="telefon" class="form-label">Telefon Numarası</label>
                        <input type="tel" class="form-control" id="telefon" name="telefon" value="<?php echo isset($telefon) ? $telefon : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="adres" class="form-label">Adres</label>
                        <textarea class="form-control" id="adres" name="adres" rows="3"><?php echo isset($adres) ? $adres : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="sozlesme" name="sozlesme" required>
                        <label class="form-check-label" for="sozlesme">
                            <a href="/kullanim-sartlari.php" target="_blank">Kullanım Şartları</a>'nı ve 
                            <a href="/gizlilik-politikasi.php" target="_blank">Gizlilik Politikası</a>'nı okudum, kabul ediyorum.
                        </label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Kayıt Ol</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center py-3 bg-light">
                <p class="mb-0">Zaten hesabınız var mı? <a href="giris.php">Giriş Yap</a></p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?> 