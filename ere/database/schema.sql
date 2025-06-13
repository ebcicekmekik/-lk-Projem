-- Veritabanını oluştur
CREATE DATABASE IF NOT EXISTS spor_magazasi CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE spor_magazasi;

-- Kullanıcılar tablosu
CREATE TABLE IF NOT EXISTS kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(50) NOT NULL,
    soyad VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    sifre VARCHAR(255) NOT NULL,
    telefon VARCHAR(20),
    adres TEXT,
    rol ENUM('admin', 'kullanici') DEFAULT 'kullanici',
    kayit_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Kategoriler tablosu
CREATE TABLE IF NOT EXISTS kategoriler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(100) NOT NULL,
    aciklama TEXT,
    resim VARCHAR(255)
);

-- Ürünler tablosu
CREATE TABLE IF NOT EXISTS urunler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori_id INT,
    ad VARCHAR(255) NOT NULL,
    aciklama TEXT,
    fiyat DECIMAL(10, 2) NOT NULL,
    stok_adedi INT NOT NULL DEFAULT 0,
    resim VARCHAR(255),
    eklenme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    etiketler VARCHAR(255) NULL,
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (kategori_id) REFERENCES kategoriler(id) ON DELETE SET NULL
);

-- Ürün Resimleri tablosu
CREATE TABLE IF NOT EXISTS urun_resimleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    urun_id INT NOT NULL,
    resim_url VARCHAR(255) NOT NULL,
    FOREIGN KEY (urun_id) REFERENCES urunler(id) ON DELETE CASCADE
);

-- Sepet tablosu
CREATE TABLE IF NOT EXISTS sepet (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id INT NOT NULL,
    urun_id INT NOT NULL,
    miktar INT NOT NULL DEFAULT 1,
    ekleme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (urun_id) REFERENCES urunler(id) ON DELETE CASCADE
);

-- Siparişler tablosu
CREATE TABLE IF NOT EXISTS siparisler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id INT NOT NULL,
    siparis_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    toplam_tutar DECIMAL(10, 2) NOT NULL,
    durum ENUM('beklemede', 'hazırlanıyor', 'kargoya_verildi', 'teslim_edildi', 'iptal_edildi') DEFAULT 'beklemede',
    odeme_yontemi VARCHAR(50) NOT NULL,
    teslimat_adresi TEXT NOT NULL,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE
);

-- Sipariş Detayları tablosu
CREATE TABLE IF NOT EXISTS siparis_detaylari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siparis_id INT NOT NULL,
    urun_id INT NOT NULL,
    miktar INT NOT NULL,
    birim_fiyat DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (siparis_id) REFERENCES siparisler(id) ON DELETE CASCADE,
    FOREIGN KEY (urun_id) REFERENCES urunler(id) ON DELETE CASCADE
);

-- Yorumlar tablosu
CREATE TABLE IF NOT EXISTS yorumlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    urun_id INT NOT NULL,
    kullanici_id INT NOT NULL,
    yorum TEXT NOT NULL,
    puan INT NOT NULL CHECK (puan BETWEEN 1 AND 5),
    tarih TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (urun_id) REFERENCES urunler(id) ON DELETE CASCADE,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE
);

-- Admin kullanıcısı ekle
INSERT INTO kullanicilar (ad, soyad, email, sifre, rol) VALUES ('Admin', 'Admin', 'admin@spormagazasi.com', '$2y$10$1qA.Z5wUuX3XEEgYNls.zu76/3VQBGpJC6D9JZx9Q5F5KN/e0wPqe', 'admin');

