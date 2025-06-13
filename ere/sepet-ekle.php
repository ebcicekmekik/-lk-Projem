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
        header('Location: index.php');
        exit;
    }
    
    // Sepete ekle
    $result = sepeteEkle($urunId, $miktar);
    
    if ($result) {
        $_SESSION['success_message'] = "Ürün sepetinize eklendi!";
    } else {
        $_SESSION['error_message'] = "Ürün sepete eklenirken bir hata oluştu. Stok yetersiz olabilir.";
    }
    
    // Geri dön
    if (isset($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        header('Location: sepet.php');
    }
    exit;
} else {
    // POST değilse ana sayfaya yönlendir
    header('Location: index.php');
    exit;
} 