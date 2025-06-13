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

// Parametreleri kontrol et
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['urun_id']) || !is_numeric($_GET['urun_id'])) {
    header('Location: urunler.php');
    exit;
}

$resim_id = $_GET['id'];
$urun_id = $_GET['urun_id'];

try {
    // Resim bilgilerini al
    $sorgu = $pdo->prepare("SELECT * FROM urun_resimleri WHERE id = :id AND urun_id = :urun_id");
    $sorgu->execute([
        'id' => $resim_id,
        'urun_id' => $urun_id
    ]);
    
    $resim = $sorgu->fetch(PDO::FETCH_ASSOC);
    
    if (!$resim) {
        header('Location: urun-duzenle.php?id=' . $urun_id . '&hata=resim-bulunamadi');
        exit;
    }
    
    // Resim dosyasını sil
    if (file_exists("../uploads/urunler/" . $resim['resim_url'])) {
        unlink("../uploads/urunler/" . $resim['resim_url']);
    }
    
    // Veritabanından resim kaydını sil
    $sorgu = $pdo->prepare("DELETE FROM urun_resimleri WHERE id = :id");
    $sorgu->execute(['id' => $resim_id]);
    
    // Başarılı mesajı ile yönlendir
    header('Location: urun-duzenle.php?id=' . $urun_id . '&basarili=resim-silindi');
    
} catch (PDOException $e) {
    // Hata durumunda
    header('Location: urun-duzenle.php?id=' . $urun_id . '&hata=veritabani');
}
?> 