<?php
// Veritabanı bağlantı bilgileri
$host = 'localhost';
$dbname = 'spor_magazasi';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

try {
    // PDO bağlantısı oluşturma
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
} catch (PDOException $e) {
    // Hata durumunda hata mesajını göster
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
} 