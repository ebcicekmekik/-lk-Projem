<?php
require_once 'includes/functions.php';

// Oturum başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kullanıcı giriş yapmış mı kontrol et
if (!isLoggedIn()) {
    $_SESSION['error_message'] = "Yorum yapabilmek için giriş yapmalısınız!";
    $_SESSION['redirect_after_login'] = 'index.php';
    header('Location: giris.php');
    exit;
}

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $urunId = isset($_POST['urun_id']) ? intval($_POST['urun_id']) : 0;
    $puan = isset($_POST['puan']) ? intval($_POST['puan']) : 5;
    $yorum = isset($_POST['yorum']) ? temizle($_POST['yorum']) : '';
    
    // Geçerli değerler mi kontrol et
    if ($urunId <= 0) {
        $_SESSION['error_message'] = "Geçersiz ürün ID'si!";
        header('Location: index.php');
        exit;
    }
    
    if ($puan < 1 || $puan > 5) {
        $_SESSION['error_message'] = "Geçersiz puan! Puan 1 ile 5 arasında olmalıdır.";
        header('Location: urun.php?id=' . $urunId);
        exit;
    }
    
    if (empty($yorum)) {
        $_SESSION['error_message'] = "Yorum metni boş olamaz!";
        header('Location: urun.php?id=' . $urunId);
        exit;
    }
    
    // Kullanıcı daha önce bu ürüne yorum yapmış mı kontrol et
    $sorgu = $pdo->prepare("SELECT COUNT(*) FROM yorumlar WHERE kullanici_id = ? AND urun_id = ?");
    $sorgu->execute([$_SESSION['kullanici_id'], $urunId]);
    
    if ($sorgu->fetchColumn() > 0) {
        // Mevcut yorumu güncelle
        $sorgu = $pdo->prepare("UPDATE yorumlar SET yorum = ?, puan = ?, tarih = CURRENT_TIMESTAMP WHERE kullanici_id = ? AND urun_id = ?");
        $result = $sorgu->execute([$yorum, $puan, $_SESSION['kullanici_id'], $urunId]);
        
        if ($result) {
            $_SESSION['success_message'] = "Yorumunuz güncellendi!";
        } else {
            $_SESSION['error_message'] = "Yorumunuz güncellenirken bir hata oluştu. Lütfen tekrar deneyin.";
        }
    } else {
        // Yeni yorum ekle
        $sorgu = $pdo->prepare("INSERT INTO yorumlar (urun_id, kullanici_id, yorum, puan) VALUES (?, ?, ?, ?)");
        $result = $sorgu->execute([$urunId, $_SESSION['kullanici_id'], $yorum, $puan]);
        
        if ($result) {
            $_SESSION['success_message'] = "Yorumunuz kaydedildi, teşekkür ederiz!";
        } else {
            $_SESSION['error_message'] = "Yorumunuz kaydedilirken bir hata oluştu. Lütfen tekrar deneyin.";
        }
    }
    
    // Ürün sayfasına yönlendir
    header('Location: urun.php?id=' . $urunId);
    exit;
} else {
    // POST değilse ana sayfaya yönlendir
    header('Location: index.php');
    exit;
} 