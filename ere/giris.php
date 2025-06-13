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
    $email = isset($_POST['email']) ? temizle($_POST['email']) : '';
    $sifre = isset($_POST['sifre']) ? $_POST['sifre'] : '';
    
    // Hata kontrolü
    $errors = array();
    
    if (empty($email)) {
        $errors[] = "E-posta adresi gereklidir.";
    }
    
    if (empty($sifre)) {
        $errors[] = "Şifre gereklidir.";
    }
    
    // Hata yoksa giriş işlemini yap
    if (empty($errors)) {
        // Kullanıcıyı kontrol et
        $sorgu = $pdo->prepare("SELECT * FROM kullanicilar WHERE email = ?");
        $sorgu->execute([$email]);
        $kullanici = $sorgu->fetch();
        
        if ($kullanici && password_verify($sifre, $kullanici['sifre'])) {
            // Giriş başarılı
            $_SESSION['kullanici_id'] = $kullanici['id'];
            $_SESSION['kullanici_ad'] = $kullanici['ad'];
            $_SESSION['kullanici_soyad'] = $kullanici['soyad'];
            $_SESSION['kullanici_email'] = $kullanici['email'];
            $_SESSION['kullanici_rol'] = $kullanici['rol'];
            
            $_SESSION['success_message'] = "Giriş başarılı. Hoş geldiniz, " . $kullanici['ad'] . "!";
            
            // Sepetteki ürünleri veritabanına aktar
            if (!empty($_SESSION['sepet'])) {
                foreach ($_SESSION['sepet'] as $urunId => $item) {
                    $sorgu = $pdo->prepare("SELECT id FROM sepet WHERE kullanici_id = ? AND urun_id = ?");
                    $sorgu->execute([$kullanici['id'], $urunId]);
                    $sepetItem = $sorgu->fetch();
                    
                    if ($sepetItem) {
                        $sorgu = $pdo->prepare("UPDATE sepet SET miktar = miktar + ? WHERE id = ?");
                        $sorgu->execute([$item['miktar'], $sepetItem['id']]);
                    } else {
                        $sorgu = $pdo->prepare("INSERT INTO sepet (kullanici_id, urun_id, miktar) VALUES (?, ?, ?)");
                        $sorgu->execute([$kullanici['id'], $urunId, $item['miktar']]);
                    }
                }
            }
            
            // Yönlendirilecek sayfayı kontrol et
            $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php';
            unset($_SESSION['redirect_after_login']);
            
            header('Location: ' . $redirect);
            exit;
        } else {
            $errors[] = "E-posta adresi veya şifre hatalı.";
        }
    }
}
?>

<?php require_once 'templates/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center py-3">
                <h2 class="h4 mb-0">Giriş Yap</h2>
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
                
                <form method="post" action="giris.php">
                    <div class="mb-3">
                        <label for="email" class="form-label">E-posta Adresi</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="sifre" class="form-label">Şifre</label>
                        <input type="password" class="form-control" id="sifre" name="sifre" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="benihatirla" name="benihatirla">
                        <label class="form-check-label" for="benihatirla">Beni hatırla</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Giriş Yap</button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <a href="sifremi-unuttum.php">Şifremi Unuttum</a>
                </div>
            </div>
            <div class="card-footer text-center py-3 bg-light">
                <p class="mb-0">Hesabınız yok mu? <a href="kayit.php">Kayıt Ol</a></p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?> 