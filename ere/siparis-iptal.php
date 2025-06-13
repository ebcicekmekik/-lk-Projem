<?php
require_once 'templates/header.php';

// Oturum kontrolü
if (!isLoggedIn()) {
    // Kullanıcı giriş yapmamışsa, giriş sayfasına yönlendir
    $_SESSION['error_message'] = 'Bu işlemi yapmak için giriş yapmalısınız.';
    header('Location: giris.php');
    exit;
}

// Sipariş ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = 'Geçersiz sipariş numarası.';
    header('Location: siparislerim.php');
    exit;
}

$siparis_id = $_GET['id'];
$kullanici_id = $_SESSION['kullanici_id'];

try {
    global $pdo;
    
    // Siparişin kullanıcıya ait olup olmadığını ve durumunu kontrol et
    $sorgu = $pdo->prepare("
        SELECT * FROM siparisler 
        WHERE id = ? AND kullanici_id = ?
    ");
    $sorgu->execute([$siparis_id, $kullanici_id]);
    $siparis = $sorgu->fetch();
    
    // Sipariş bulunamadı veya başka kullanıcıya aitse hata ver
    if (!$siparis) {
        throw new Exception('Sipariş bulunamadı veya bu işlem için yetkiniz yok.');
    }
    
    // Sipariş durumu beklemede değilse iptal edilemez
    if ($siparis['durum'] !== 'beklemede') {
        throw new Exception('Sadece beklemede olan siparişler iptal edilebilir.');
    }
    
    // İşlemleri transaction ile yap
    $pdo->beginTransaction();
    
    // Siparişi iptal et
    $sorgu = $pdo->prepare("UPDATE siparisler SET durum = 'iptal_edildi' WHERE id = ?");
    $sorgu->execute([$siparis_id]);
    
    // Sipariş ürünlerini al
    $sorgu = $pdo->prepare("SELECT * FROM siparis_detaylari WHERE siparis_id = ?");
    $sorgu->execute([$siparis_id]);
    $urunler = $sorgu->fetchAll();
    
    // Stokları geri al
    foreach ($urunler as $urun) {
        $sorgu = $pdo->prepare("
            UPDATE urunler 
            SET stok_adedi = stok_adedi + ? 
            WHERE id = ?
        ");
        $sorgu->execute([$urun['miktar'], $urun['urun_id']]);
    }
    
    // İşlemi kaydet
    $pdo->commit();
    
    // Başarılı mesajını göster ve siparişlerim sayfasına yönlendir
    $_SESSION['success_message'] = 'Siparişiniz başarıyla iptal edildi. İlgili ürünler stoklara geri eklendi.';
    header("Location: siparislerim.php?id={$siparis_id}");
    exit;
    
} catch (Exception $e) {
    // Hata durumunda transaction'ı geri al
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Hata mesajını göster ve siparişlerim sayfasına yönlendir
    $_SESSION['error_message'] = 'Sipariş iptal edilirken bir hata oluştu: ' . $e->getMessage();
    header('Location: siparislerim.php');
    exit;
}
?> 