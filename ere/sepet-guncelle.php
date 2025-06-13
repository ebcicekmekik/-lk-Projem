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
    $miktar = isset($_POST['miktar']) ? intval($_POST['miktar']) : 1;
    
    // Geçerli değerler mi kontrol et
    if ($urunId <= 0 || $miktar <= 0) {
        $_SESSION['error_message'] = "Geçersiz ürün ID'si veya miktar!";
        header('Location: sepet.php');
        exit;
    }
    
    // Sepetteki ürün miktarını güncelle
    $result = sepetMiktarGuncelle($urunId, $miktar);
    
    if ($result) {
        $_SESSION['success_message'] = "Sepetiniz güncellendi!";
    } else {
        $_SESSION['error_message'] = "Sepet güncellenirken bir hata oluştu. Stok yetersiz olabilir.";
    }
    
    // Sepet sayfasına yönlendir
    header('Location: sepet.php');
    exit;
} else {
    // POST değilse sepet sayfasına yönlendir
    header('Location: sepet.php');
    exit;
} 