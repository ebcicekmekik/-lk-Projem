<?php
require_once '../includes/functions.php';

// Oturum başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin kontrolü
if (!isAdmin()) {
    header('Location: ../giris.php');
    exit;
}

// Özet bilgileri al
// Toplam kullanıcı sayısı
$sorgu = $pdo->query("SELECT COUNT(*) FROM kullanicilar");
$kullaniciSayisi = $sorgu->fetchColumn();

// Toplam ürün sayısı
$sorgu = $pdo->query("SELECT COUNT(*) FROM urunler");
$urunSayisi = $sorgu->fetchColumn();

// Toplam sipariş sayısı
$sorgu = $pdo->query("SELECT COUNT(*) FROM siparisler");
$siparisSayisi = $sorgu->fetchColumn();

// Toplam satış tutarı
$sorgu = $pdo->query("SELECT SUM(toplam_tutar) FROM siparisler");
$toplamSatis = $sorgu->fetchColumn();
// Null kontrolü ekle
$toplamSatis = ($toplamSatis === null) ? 0 : $toplamSatis;

// Son 5 sipariş
$sorgu = $pdo->query("
    SELECT s.*, k.ad, k.soyad
    FROM siparisler s
    JOIN kullanicilar k ON s.kullanici_id = k.id
    ORDER BY s.siparis_tarihi DESC
    LIMIT 5
");
$sonSiparisler = $sorgu->fetchAll();

// Son 5 kullanıcı
$sorgu = $pdo->query("
    SELECT *
    FROM kullanicilar
    ORDER BY kayit_tarihi DESC
    LIMIT 5
");
$sonKullanicilar = $sorgu->fetchAll();
?>

<?php require_once 'templates/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Kontrol Paneli</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Kontrol Paneli</li>
    </ol>
    
    <!-- Özet Kartlar -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><?php echo number_format($kullaniciSayisi); ?></h5>
                            <div class="small">Toplam Kullanıcı</div>
                        </div>
                        <div>
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="kullanicilar.php">Detayları Görüntüle</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><?php echo number_format($urunSayisi); ?></h5>
                            <div class="small">Toplam Ürün</div>
                        </div>
                        <div>
                            <i class="fas fa-box-open fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="urunler.php">Detayları Görüntüle</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><?php echo number_format($siparisSayisi); ?></h5>
                            <div class="small">Toplam Sipariş</div>
                        </div>
                        <div>
                            <i class="fas fa-shopping-cart fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="siparisler.php">Detayları Görüntüle</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><?php echo number_format($toplamSatis, 2, ',', '.'); ?> ₺</h5>
                            <div class="small">Toplam Satış</div>
                        </div>
                        <div>
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="siparisler.php">Detayları Görüntüle</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Grafikler -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-area me-1"></i>
                    Aylık Satış Grafiği
                </div>
                <div class="card-body">
                    <canvas id="salesChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Kategori Dağılımı
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tablolar -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-shopping-cart me-1"></i>
                    Son Siparişler
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Müşteri</th>
                                    <th>Tutar</th>
                                    <th>Durum</th>
                                    <th>Tarih</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sonSiparisler as $siparis): ?>
                                    <tr>
                                        <td><?php echo $siparis['id']; ?></td>
                                        <td><?php echo $siparis['ad'] . ' ' . $siparis['soyad']; ?></td>
                                        <td><?php echo number_format($siparis['toplam_tutar'], 2, ',', '.'); ?> ₺</td>
                                        <td>
                                            <?php
                                            switch ($siparis['durum']) {
                                                case 'beklemede':
                                                    echo '<span class="badge bg-warning text-dark">Beklemede</span>';
                                                    break;
                                                case 'hazırlanıyor':
                                                    echo '<span class="badge bg-info">Hazırlanıyor</span>';
                                                    break;
                                                case 'kargoya_verildi':
                                                    echo '<span class="badge bg-primary">Kargoya Verildi</span>';
                                                    break;
                                                case 'teslim_edildi':
                                                    echo '<span class="badge bg-success">Teslim Edildi</span>';
                                                    break;
                                                case 'iptal_edildi':
                                                    echo '<span class="badge bg-danger">İptal Edildi</span>';
                                                    break;
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo date('d.m.Y H:i', strtotime($siparis['siparis_tarihi'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($sonSiparisler)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Henüz sipariş bulunmamaktadır.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="siparisler.php" class="btn btn-primary">Tüm Siparişler</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-users me-1"></i>
                    Son Kayıt Olan Kullanıcılar
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Ad Soyad</th>
                                    <th>E-posta</th>
                                    <th>Rol</th>
                                    <th>Kayıt Tarihi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sonKullanicilar as $kullanici): ?>
                                    <tr>
                                        <td><?php echo $kullanici['id']; ?></td>
                                        <td><?php echo $kullanici['ad'] . ' ' . $kullanici['soyad']; ?></td>
                                        <td><?php echo $kullanici['email']; ?></td>
                                        <td>
                                            <?php if ($kullanici['rol'] === 'admin'): ?>
                                                <span class="badge bg-danger">Admin</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Kullanıcı</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d.m.Y H:i', strtotime($kullanici['kayit_tarihi'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($sonKullanicilar)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Henüz kullanıcı bulunmamaktadır.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="kullanicilar.php" class="btn btn-primary">Tüm Kullanıcılar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Grafik Verileri -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Satış grafiği
    var salesChartCtx = document.getElementById("salesChart");
    var salesChart = new Chart(salesChartCtx, {
        type: 'line',
        data: {
            labels: ["Oca", "Şub", "Mar", "Nis", "May", "Haz", "Tem", "Ağu", "Eyl", "Eki", "Kas", "Ara"],
            datasets: [{
                label: "Satışlar",
                lineTension: 0.3,
                backgroundColor: "rgba(2,117,216,0.2)",
                borderColor: "rgba(2,117,216,1)",
                pointRadius: 5,
                pointBackgroundColor: "rgba(2,117,216,1)",
                pointBorderColor: "rgba(255,255,255,0.8)",
                pointHoverRadius: 5,
                pointHoverBackgroundColor: "rgba(2,117,216,1)",
                pointHitRadius: 50,
                pointBorderWidth: 2,
                data: [1000, 3000, 5000, 4000, 6000, 8000, 7500, 9000, 12000, 11000, 15000, 16000],
            }],
        },
        options: {
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    ticks: {
                        min: 0,
                        max: 20000,
                        maxTicksLimit: 5
                    },
                    grid: {
                        color: "rgba(0, 0, 0, .125)",
                    }
                },
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    
    // Kategori grafiği
    var categoryChartCtx = document.getElementById("categoryChart");
    var categoryChart = new Chart(categoryChartCtx, {
        type: 'bar',
        data: {
            labels: ["Fitness", "Spor Giyim", "Bisiklet", "Futbol", "Basketbol", "Koşu", "Yüzme"],
            datasets: [{
                label: "Ürün Sayısı",
                backgroundColor: "rgba(2,117,216,1)",
                borderColor: "rgba(2,117,216,1)",
                data: [12, 25, 8, 15, 10, 18, 7],
            }],
        },
        options: {
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    ticks: {
                        min: 0,
                        max: 30,
                        maxTicksLimit: 5
                    },
                    grid: {
                        color: "rgba(0, 0, 0, .125)",
                    }
                },
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>

<?php require_once 'templates/footer.php'; ?> 