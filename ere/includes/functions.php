<?php
// Hata raporlamayı aktif edelim
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Veritabanı bağlantısını içe aktaralım
require_once __DIR__ . '/../database/db.php';

/**
 * Oturum kontrolü
 * @return bool Kullanıcı girişi varsa true, yoksa false döner
 */
function isLoggedIn() {
    return isset($_SESSION['kullanici_id']);
}

/**
 * Admin kontrolü
 * @return bool Kullanıcı admin ise true, değilse false döner
 */
function isAdmin() {
    return isset($_SESSION['kullanici_rol']) && $_SESSION['kullanici_rol'] === 'admin';
}

/**
 * Güvenli input temizleme
 * @param string $data Temizlenecek veri
 * @return string Temizlenmiş veri
 */
function temizle($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Veritabanından tüm kategorileri al
 * @return array Kategoriler dizisi
 */
function tumKategorileriGetir() {
    global $pdo;
    $sorgu = $pdo->query("SELECT * FROM kategoriler ORDER BY ad ASC");
    return $sorgu->fetchAll();
}

/**
 * Bir kategoriye ait ürünleri getir
 * @param int $kategoriId Kategori ID
 * @return array Ürünler dizisi
 */
function kategoriUrunleriniGetir($kategoriId) {
    global $pdo;
    $sorgu = $pdo->prepare("SELECT * FROM urunler WHERE kategori_id = ? ORDER BY eklenme_tarihi DESC");
    $sorgu->execute([$kategoriId]);
    return $sorgu->fetchAll();
}

/**
 * Ana sayfada gösterilecek popüler ürünleri getir
 * @param int $limit Gösterilecek ürün sayısı
 * @return array Ürünler dizisi
 */
function populerUrunleriGetir($limit = 8) {
    global $pdo;
    $sorgu = $pdo->prepare("
        SELECT u.*, AVG(y.puan) as ortalama_puan, COUNT(y.id) as yorum_sayisi
        FROM urunler u
        LEFT JOIN yorumlar y ON u.id = y.urun_id
        GROUP BY u.id
        ORDER BY ortalama_puan DESC, yorum_sayisi DESC
        LIMIT ?
    ");
    $sorgu->execute([$limit]);
    return $sorgu->fetchAll();
}

/**
 * Yeni eklenen ürünleri getir
 * @param int $limit Gösterilecek ürün sayısı
 * @return array Ürünler dizisi
 */
function yeniUrunleriGetir($limit = 8) {
    global $pdo;
    $sorgu = $pdo->prepare("SELECT * FROM urunler ORDER BY eklenme_tarihi DESC LIMIT ?");
    $sorgu->execute([$limit]);
    return $sorgu->fetchAll();
}

/**
 * Ürün detaylarını ID'ye göre getir
 * @param int $urunId Ürün ID
 * @return array|false Ürün bilgileri veya false
 */
function urunDetayGetir($urunId) {
    global $pdo;
    $sorgu = $pdo->prepare("
        SELECT u.*, k.ad as kategori_adi, AVG(y.puan) as ortalama_puan, COUNT(y.id) as yorum_sayisi
        FROM urunler u
        LEFT JOIN kategoriler k ON u.kategori_id = k.id
        LEFT JOIN yorumlar y ON u.id = y.urun_id
        WHERE u.id = ?
        GROUP BY u.id
    ");
    $sorgu->execute([$urunId]);
    return $sorgu->fetch();
}

/**
 * Ürünün yorumlarını getir
 * @param int $urunId Ürün ID
 * @return array Yorumlar dizisi
 */
function urunYorumlariGetir($urunId) {
    global $pdo;
    $sorgu = $pdo->prepare("
        SELECT y.*, CONCAT(k.ad, ' ', k.soyad) as kullanici_adi
        FROM yorumlar y
        JOIN kullanicilar k ON y.kullanici_id = k.id
        WHERE y.urun_id = ?
        ORDER BY y.tarih DESC
    ");
    $sorgu->execute([$urunId]);
    return $sorgu->fetchAll();
}

/**
 * Arama sonuçlarını getir
 * @param string $arananKelime Aranacak kelime
 * @return array Ürünler dizisi
 */
function aramaYap($arananKelime) {
    global $pdo;
    $arananKelime = "%$arananKelime%";
    $sorgu = $pdo->prepare("
        SELECT u.*, k.ad as kategori_adi
        FROM urunler u
        LEFT JOIN kategoriler k ON u.kategori_id = k.id
        WHERE u.ad LIKE ? OR u.aciklama LIKE ? OR k.ad LIKE ?
        ORDER BY u.eklenme_tarihi DESC
    ");
    $sorgu->execute([$arananKelime, $arananKelime, $arananKelime]);
    return $sorgu->fetchAll();
} 