<?php
require_once 'includes/functions.php';
require_once 'includes/cart.php';

// Oturum başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $urunId = isset($_POST['urun_id']) ? intval($_POST['urun_id']) : 0;
    
    // Geçerli değer mi kontrol et
    if ($urunId <= 0) {
        $_SESSION['error_message'] = "Geçersiz ürün ID'si!";
        header('Location: sepet.php');
        exit;
    }
    
    // Sepetten sil
    $result = sepettenSil($urunId);
    
    if ($result) {
        $_SESSION['success_message'] = "Ürün sepetinizden kaldırıldı!";
    } else {
        $_SESSION['error_message'] = "Ürün sepetten kaldırılırken bir hata oluştu.";
    }
    
    // Sepet sayfasına yönlendir
    header('Location: sepet.php');
    exit;
} else {
    // POST değilse sepet sayfasına yönlendir
    header('Location: sepet.php');
    exit;
} 