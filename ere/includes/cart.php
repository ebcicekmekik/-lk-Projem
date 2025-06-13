<?php
// Oturum başlatılmamışsa başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sepet başlatılmamışsa başlat
if (!isset($_SESSION['sepet'])) {
    $_SESSION['sepet'] = [];
}

/**
 * Sepete ürün ekle
 * @param int $urunId Ürün ID
 * @param int $miktar Miktar
 * @return bool Başarılıysa true, değilse false
 */
function sepeteEkle($urunId, $miktar = 1) {
    global $pdo;
    
    // Ürün var mı kontrol et
    $sorgu = $pdo->prepare("SELECT id, fiyat, stok_adedi FROM urunler WHERE id = ?");
    $sorgu->execute([$urunId]);
    $urun = $sorgu->fetch();
    
    if (!$urun) {
        return false; // Ürün bulunamadı
    }
    
    // Stok kontrolü
    if ($urun['stok_adedi'] < $miktar) {
        return false; // Stok yetersiz
    }
    
    // Kullanıcı giriş yapmışsa veritabanına ekle
    if (isset($_SESSION['kullanici_id'])) {
        $kullaniciId = $_SESSION['kullanici_id'];
        
        // Sepette bu ürün var mı kontrol et
        $sorgu = $pdo->prepare("SELECT id, miktar FROM sepet WHERE kullanici_id = ? AND urun_id = ?");
        $sorgu->execute([$kullaniciId, $urunId]);
        $sepetItem = $sorgu->fetch();
        
        if ($sepetItem) {
            // Varsa miktarı güncelle
            $yeniMiktar = $sepetItem['miktar'] + $miktar;
            if ($yeniMiktar > $urun['stok_adedi']) {
                return false; // Stok yetersiz
            }
            
            $sorgu = $pdo->prepare("UPDATE sepet SET miktar = ? WHERE id = ?");
            $sorgu->execute([$yeniMiktar, $sepetItem['id']]);
        } else {
            // Yoksa yeni ekle
            $sorgu = $pdo->prepare("INSERT INTO sepet (kullanici_id, urun_id, miktar) VALUES (?, ?, ?)");
            $sorgu->execute([$kullaniciId, $urunId, $miktar]);
        }
    }
    
    // Session'a ekle
    if (isset($_SESSION['sepet'][$urunId])) {
        $_SESSION['sepet'][$urunId]['miktar'] += $miktar;
    } else {
        $_SESSION['sepet'][$urunId] = [
            'urun_id' => $urunId,
            'miktar' => $miktar,
            'fiyat' => $urun['fiyat']
        ];
    }
    
    return true;
}

/**
 * Sepetten ürün sil
 * @param int $urunId Ürün ID
 * @return bool Başarılıysa true
 */
function sepettenSil($urunId) {
    // Sepette böyle bir ürün yoksa false döndür
    if (!isset($_SESSION['sepet'][$urunId])) {
        return false;
    }
    
    // Kullanıcı giriş yapmışsa veritabanından da sil
    if (isset($_SESSION['kullanici_id'])) {
        global $pdo;
        $kullaniciId = $_SESSION['kullanici_id'];
        
        $sorgu = $pdo->prepare("DELETE FROM sepet WHERE kullanici_id = ? AND urun_id = ?");
        $sorgu->execute([$kullaniciId, $urunId]);
    }
    
    // Session'dan sil
    unset($_SESSION['sepet'][$urunId]);
    
    return true;
}

/**
 * Sepetteki ürün miktarını güncelle
 * @param int $urunId Ürün ID
 * @param int $miktar Yeni miktar
 * @return bool Başarılıysa true, değilse false
 */
function sepetMiktarGuncelle($urunId, $miktar) {
    global $pdo;
    
    // Sepette böyle bir ürün yoksa false döndür
    if (!isset($_SESSION['sepet'][$urunId])) {
        return false;
    }
    
    // Stok kontrolü
    $sorgu = $pdo->prepare("SELECT stok_adedi FROM urunler WHERE id = ?");
    $sorgu->execute([$urunId]);
    $urun = $sorgu->fetch();
    
    if (!$urun || $urun['stok_adedi'] < $miktar) {
        return false; // Ürün bulunamadı veya stok yetersiz
    }
    
    // Kullanıcı giriş yapmışsa veritabanını güncelle
    if (isset($_SESSION['kullanici_id'])) {
        $kullaniciId = $_SESSION['kullanici_id'];
        
        $sorgu = $pdo->prepare("UPDATE sepet SET miktar = ? WHERE kullanici_id = ? AND urun_id = ?");
        $sorgu->execute([$miktar, $kullaniciId, $urunId]);
    }
    
    // Session'ı güncelle
    $_SESSION['sepet'][$urunId]['miktar'] = $miktar;
    
    return true;
}

/**
 * Sepeti getir
 * @return array Sepet içeriği
 */
function sepetiGetir() {
    global $pdo;
    $sepet = [];
    
    // Session'daki sepet bilgisini al
    if (!empty($_SESSION['sepet'])) {
        foreach ($_SESSION['sepet'] as $urunId => $item) {
            $sorgu = $pdo->prepare("
                SELECT u.*, k.ad as kategori_adi 
                FROM urunler u
                LEFT JOIN kategoriler k ON u.kategori_id = k.id
                WHERE u.id = ?
            ");
            $sorgu->execute([$urunId]);
            $urun = $sorgu->fetch();
            
            if ($urun) {
                $sepet[] = [
                    'urun' => $urun,
                    'miktar' => $item['miktar'],
                    'toplam' => $item['miktar'] * $urun['fiyat']
                ];
            }
        }
    }
    
    return $sepet;
}

/**
 * Sepet toplamını hesapla
 * @return float Sepet toplamı
 */
function sepetToplaminiHesapla() {
    $toplam = 0;
    $sepet = sepetiGetir();
    
    foreach ($sepet as $item) {
        $toplam += $item['toplam'];
    }
    
    return $toplam;
}

/**
 * Sepeti temizle
 */
function sepetiTemizle() {
    // Kullanıcı giriş yapmışsa veritabanından da temizle
    if (isset($_SESSION['kullanici_id'])) {
        global $pdo;
        $kullaniciId = $_SESSION['kullanici_id'];
        
        $sorgu = $pdo->prepare("DELETE FROM sepet WHERE kullanici_id = ?");
        $sorgu->execute([$kullaniciId]);
    }
    
    // Session'ı temizle
    $_SESSION['sepet'] = [];
} 