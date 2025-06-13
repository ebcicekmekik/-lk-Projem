<?php
// Çıktı tamponlamayı başlat
ob_start();

// Oturum başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Gerekli dosyaları dahil et
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/cart.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportMağazası - Spor Malzemeleri</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-running me-2"></i>SportMağazası
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Ana Sayfa</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Kategoriler
                        </a>
                        <ul class="dropdown-menu">
                            <?php
                            $kategoriler = tumKategorileriGetir();
                            foreach ($kategoriler as $kategori) {
                                echo '<li><a class="dropdown-item" href="/kategori.php?id=' . $kategori['id'] . '">' . $kategori['ad'] . '</a></li>';
                            }
                            ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/kategoriler.php"><i class="fas fa-list me-2"></i>Tüm Kategoriler</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/urunler.php">Tüm Ürünler</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/hakkimizda.php">Hakkımızda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/iletisim.php">İletişim</a>
                    </li>
                </ul>
                
                <!-- Arama formu -->
                <form class="d-flex me-2" action="/arama.php" method="GET">
                    <div class="input-group">
                        <input class="form-control" type="search" name="q" placeholder="Ürün ara..." aria-label="Ara">
                        <button class="btn btn-light" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <!-- Sepet ve kullanıcı -->
                <div class="d-flex">
                    <a href="/sepet.php" class="btn btn-outline-light me-2 position-relative">
                        <i class="fas fa-shopping-cart"></i>
                        <?php
                        $sepetUrunSayisi = isset($_SESSION['sepet']) ? count($_SESSION['sepet']) : 0;
                        if ($sepetUrunSayisi > 0) {
                            echo '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">' . $sepetUrunSayisi . '</span>';
                        }
                        ?>
                    </a>
                    
                    <?php if (isLoggedIn()): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i> <?php echo $_SESSION['kullanici_ad']; ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/hesabim.php">Hesabım</a></li>
                                <li><a class="dropdown-item" href="/siparislerim.php">Siparişlerim</a></li>
                                <li><a class="dropdown-item" href="/favorilerim.php">Favorilerim</a></li>
                                <?php if (isAdmin()): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/admin/">Admin Paneli</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/cikis.php">Çıkış Yap</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div>
                            <a href="/giris.php" class="btn btn-outline-light me-2">Giriş Yap</a>
                            <a href="/kayit.php" class="btn btn-light">Kayıt Ol</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="py-4">
        <div class="container"><?php
            // Mesajlar varsa göster ve temizle
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
                echo $_SESSION['success_message'];
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>';
                echo '</div>';
                unset($_SESSION['success_message']);
            }
            
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                echo $_SESSION['error_message'];
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>';
                echo '</div>';
                unset($_SESSION['error_message']);
            }
        ?> 