<?php
require_once 'includes/functions.php';
require_once 'includes/cart.php';

// Oturum başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sepeti temizle
    sepetiTemizle();
    
    $_SESSION['success_message'] = "Sepetiniz temizlendi!";
    
    // Sepet sayfasına yönlendir
    header('Location: sepet.php');
    exit;
} else {
    // POST değilse sepet sayfasına yönlendir
    header('Location: sepet.php');
    exit;
} 