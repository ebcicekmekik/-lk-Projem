<?php
// Oturum başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin kontrolü
if (!isset($_SESSION['kullanici_rol']) || $_SESSION['kullanici_rol'] !== 'admin') {
    header('Location: ../giris.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Admin Paneli - SportMağazası</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/2.0.0/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <!-- SummerNote Editor -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <!-- Custom Admin CSS -->
    <link href="../admin/assets/css/admin.css" rel="stylesheet">
    <style>
    /* Dropdown menü stili düzeltmesi */
    .nav-item .dropdown-menu {
        left: 0 !important;
        right: auto !important;
        transform: none !important;
        top: 100% !important;
        margin-top: 0.5rem !important;
        width: 300px; /* Dropdown genişliği */
        z-index: 1050;
    }

    /* Profil dropdown'u için özel stil */
    #userDropdown + .dropdown-menu {
        margin-left: -200px; /* Sağa kaydırılmış dropdown'u sola çekme */
    }

    /* Bildirim dropdown'u için özel stil */
    #navbarDropdown + .dropdown-menu {
        margin-left: -180px; /* Sağa kaydırılmış dropdown'u sola çekme */
    }

    /* Ana menü öğeleri için dropdown pozisyonlaması */
    #layoutSidenav_nav .collapse {
        position: static;
    }

    /* Kategori ve diğer dropdown'ların düzgün pozisyonlanması */
    .sb-sidenav-menu .collapse {
        background-color: rgba(0, 0, 0, 0.1);
    }

    /* Dropdown okları için stil */
    .sb-sidenav-menu .nav a[data-bs-toggle="collapse"] .sb-sidenav-collapse-arrow {
        transform: rotate(0);
        transition: transform 0.15s ease;
    }

    .sb-sidenav-menu .nav a[data-bs-toggle="collapse"][aria-expanded="true"] .sb-sidenav-collapse-arrow {
        transform: rotate(90deg);
    }
    </style>
</head>
<body class="sb-nav-fixed">
    <!-- Üst Navbar -->
    <nav class="navbar navbar-expand navbar-dark fixed-top bg-dark">
        <div class="container-fluid px-4">
            <!-- Logo ve Marka -->
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <div class="d-flex align-items-center">
                    <div class="brand-icon me-2 bg-white rounded p-1 shadow-sm">
                        <i class="fas fa-running text-primary"></i>
                    </div>
                    <div class="brand-text d-none d-sm-block">
                        <span class="fw-bold">Sport</span>Mağazası
                    </div>
                </div>
            </a>
            
            <!-- Menü Açma/Kapama Butonu -->
            <button class="btn btn-link btn-sm me-auto me-lg-0 text-white" id="sidebarToggle" href="#!">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Arama Formu -->
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="Ara..." aria-label="Ara..." aria-describedby="btnNavbarSearch" />
                    <button class="btn btn-primary" id="btnNavbarSearch" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            
            <!-- Sağdaki Menü Öğeleri -->
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item">
                    <a class="nav-link position-relative" href="../index.php" target="_blank" title="Siteyi Görüntüle" data-bs-toggle="tooltip" data-bs-placement="bottom">
                        <i class="fas fa-globe fa-fw"></i>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle position-relative" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell fa-fw"></i>
                        <span class="position-absolute top-0 start-75 translate-middle badge rounded-pill bg-danger">
                            3
                            <span class="visually-hidden">okunmamış bildirim</span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3" aria-labelledby="navbarDropdown">
                        <li class="notification-header bg-light p-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0">Bildirimler</h6>
                            <span class="badge rounded-pill bg-danger">3 Yeni</span>
                        </li>
                        <li><hr class="dropdown-divider m-0"></li>
                        <li class="notification-item">
                            <a class="dropdown-item d-flex align-items-center py-2" href="#">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small text-muted notification-time">2 dakika önce</div>
                                    <div class="notification-info text-dark">Yeni bir sipariş oluşturuldu</div>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider m-0"></li>
                        <li class="notification-item">
                            <a class="dropdown-item d-flex align-items-center py-2" href="#">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small text-muted notification-time">1 saat önce</div>
                                    <div class="notification-info text-dark">Yeni bir kullanıcı kayıt oldu</div>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider m-0"></li>
                        <li class="notification-item">
                            <a class="dropdown-item d-flex align-items-center py-2" href="#">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small text-muted notification-time">3 saat önce</div>
                                    <div class="notification-info text-dark">Stok uyarısı: 5 ürün tükeniyor</div>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider m-0"></li>
                        <li class="notification-footer p-2 bg-light text-center">
                            <a class="small fw-bold text-decoration-none" href="bildirimler.php">Tüm Bildirimleri Göster</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" id="userDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar bg-white text-primary rounded-circle me-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                            <?php echo mb_substr($_SESSION['kullanici_ad'], 0, 1); ?>
                        </div>
                        <span class="d-none d-lg-inline text-truncate" style="max-width: 120px;"><?php echo $_SESSION['kullanici_ad']; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3" aria-labelledby="userDropdown">
                        <li class="dropdown-header bg-primary text-white p-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-white text-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <?php echo mb_substr($_SESSION['kullanici_ad'], 0, 1); ?>
                                </div>
                                <div>
                                    <h6 class="mb-0"><?php echo $_SESSION['kullanici_ad'] . ' ' . $_SESSION['kullanici_soyad']; ?></h6>
                                    <small><?php echo $_SESSION['kullanici_email']; ?></small>
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center py-2" href="#!">
                                <i class="fas fa-user-cog me-2 text-muted"></i> Profil Ayarları
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center py-2" href="#!">
                                <i class="fas fa-list-alt me-2 text-muted"></i> Etkinlik Günlüğü
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center py-2" href="../cikis.php">
                                <i class="fas fa-sign-out-alt me-2 text-danger"></i> Çıkış Yap
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
    
    <!-- Ana Wrapper -->
    <div class="d-flex wrapper">
        <!-- Sidebar -->
        <div class="sidebar bg-dark" id="sidebar-wrapper">
            <div class="sidebar-sticky pt-5">
                <div class="px-3 mt-3 mb-4">
                    <div class="user-info d-flex align-items-center">
                        <div class="avatar bg-white text-primary rounded-circle me-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                            <?php echo mb_substr($_SESSION['kullanici_ad'], 0, 1); ?>
                        </div>
                        <div class="user-details text-white">
                            <h6 class="mb-0"><?php echo $_SESSION['kullanici_ad']; ?></h6>
                            <span class="text-muted small">Admin</span>
                        </div>
                    </div>
                </div>
                
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="index.php">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link text-white" href="kullanicilar.php">
                            <i class="fas fa-users me-2"></i> Kullanıcılar
                        </a>
                    </li>
                    
                                        <li class="nav-item">                        <a class="nav-link text-white collapsed" data-bs-toggle="collapse" href="#collapseUrunler" aria-expanded="false">                            <i class="fas fa-box-open me-2"></i>                            Ürün Yönetimi                            <i class="fas fa-angle-down ms-auto"></i>                        </a>                        <div class="collapse" id="collapseUrunler">                            <ul class="nav flex-column ms-3">                                <li class="nav-item">                                    <a class="nav-link text-white" href="urunler.php">                                        <i class="fas fa-list me-2"></i> Tüm Ürünler                                    </a>                                </li>                                <li class="nav-item">                                    <a class="nav-link text-white" href="urun-ekle.php">                                        <i class="fas fa-plus me-2"></i> Yeni Ürün Ekle                                    </a>                                </li>                                <li class="nav-item">                                    <a class="nav-link text-white" href="kategoriler.php">                                        <i class="fas fa-folder me-2"></i> Kategoriler                                    </a>                                </li>                                <li class="nav-item">                                    <a class="nav-link text-white" href="kategori-ekle.php">                                        <i class="fas fa-folder-plus me-2"></i> Kategori Ekle                                    </a>                                </li>                            </ul>                        </div>                    </li>                                        <li class="nav-item">                        <a class="nav-link text-white" href="siparisler.php">                            <i class="fas fa-shopping-cart me-2"></i> Siparişler                        </a>                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link text-white collapsed" data-bs-toggle="collapse" href="#collapseLayouts" aria-expanded="false">
                            <i class="fas fa-cog me-2"></i>
                            Ayarlar
                            <i class="fas fa-angle-down ms-auto"></i>
                        </a>
                        <div class="collapse" id="collapseLayouts">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a class="nav-link text-white" href="genel-ayarlar.php">
                                        <i class="fas fa-wrench me-2"></i> Genel Ayarlar
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white" href="email-ayarlari.php">
                                        <i class="fas fa-envelope me-2"></i> E-posta Ayarları
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white" href="odeme-ayarlari.php">
                                        <i class="fas fa-money-bill me-2"></i> Ödeme Ayarları
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
                
                <div class="mt-4 px-3">
                    <hr class="border-secondary">
                    <div class="d-grid gap-2">
                        <a href="../cikis.php" class="btn btn-danger btn-sm">
                            <i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sayfa İçeriği -->
        <div class="content-wrapper" id="page-content-wrapper">
            <div class="container-fluid">
                <main class="mt-5 py-3">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/2.0.0/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.0/js/dataTables.bootstrap5.min.js"></script>
    <!-- SummerNote Editor JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <!-- Custom Admin JS -->
    <script src="../admin/assets/js/admin.js"></script>
    
    <!-- Dropdown Davranış Düzeltmesi -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tüm dropdown menülerin açılma yönünü düzeltme
        const dropdowns = document.querySelectorAll('.dropdown-menu');
        dropdowns.forEach(dropdown => {
            dropdown.classList.remove('dropdown-menu-end');
        });
        
        // Yan menü dropdown oklarını düzeltme
        const collapsibleLinks = document.querySelectorAll('.sb-sidenav-menu .nav a[data-bs-toggle="collapse"]');
        collapsibleLinks.forEach(link => {
            link.addEventListener('click', function() {
                // Açılıp kapanma durumuna göre ok ikonunun dönme animasyonu
                setTimeout(() => {
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';
                    const arrow = this.querySelector('.sb-sidenav-collapse-arrow i');
                    if (isExpanded) {
                        arrow.style.transform = 'rotate(90deg)';
                    } else {
                        arrow.style.transform = 'rotate(0deg)';
                    }
                }, 10);
            });
        });
    });
    </script>
</body>
</html> 