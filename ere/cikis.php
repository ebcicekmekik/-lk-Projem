<?php
// Oturum başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Oturum değişkenlerini temizle
$_SESSION = array();

// Çerezde oturum ID varsa onu da temizle
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Oturumu sonlandır
session_destroy();

// Ana sayfaya yönlendir
header("Location: index.php");
exit; 