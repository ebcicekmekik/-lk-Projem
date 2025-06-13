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

// Sipariş durumu güncelleme
if (isset($_POST['durum_guncelle']) && isset($_POST['siparis_id']) && is_numeric($_POST['siparis_id'])) {
    $siparis_id = $_POST['siparis_id'];
    $yeni_durum = $_POST['yeni_durum'];
    $gecerli_durumlar = ['beklemede', 'hazırlanıyor', 'kargoya_verildi', 'teslim_edildi', 'iptal_edildi'];
    
    if (in_array($yeni_durum, $gecerli_durumlar)) {
        $sorgu = $pdo->prepare("UPDATE siparisler SET durum = :durum WHERE id = :id");
        $sonuc = $sorgu->execute([
            'durum' => $yeni_durum,
            'id' => $siparis_id
        ]);
        
        if ($sonuc) {
            header("Location: siparisler.php?basarili=1");
            exit;
        } else {
            $hata = "Sipariş durumu güncellenirken bir hata oluştu.";
        }
    }
}

// Filtreleme ayarları
$durum_filtresi = isset($_GET['durum']) ? $_GET['durum'] : '';
$tarih_filtresi = isset($_GET['tarih']) ? $_GET['tarih'] : '';

// Sorgu oluştur
$sql = "
    SELECT s.*, k.ad, k.soyad, k.email, k.telefon 
    FROM siparisler s
    LEFT JOIN kullanicilar k ON s.kullanici_id = k.id
";

$params = [];
$where_conditions = [];

if (!empty($durum_filtresi)) {
    $where_conditions[] = "s.durum = :durum";
    $params['durum'] = $durum_filtresi;
}

if (!empty($tarih_filtresi)) {
    switch ($tarih_filtresi) {
        case 'bugun':
            $where_conditions[] = "DATE(s.siparis_tarihi) = CURDATE()";
            break;
        case 'hafta':
            $where_conditions[] = "s.siparis_tarihi >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            break;
        case 'ay':
            $where_conditions[] = "s.siparis_tarihi >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            break;
    }
}

if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}

$sql .= " ORDER BY s.siparis_tarihi DESC";

// Sorguyu çalıştır
$sorgu = $pdo->prepare($sql);
$sorgu->execute($params);
$siparisler = $sorgu->fetchAll(PDO::FETCH_ASSOC);

// Sayfa başlığı
$sayfa_basligi = "Siparişler";

require_once 'templates/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4"><?php echo $sayfa_basligi; ?></h1>
    </div>
    
    <?php if (isset($hata)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $hata; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['basarili'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Sipariş durumu başarıyla güncellendi.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
        </div>
    <?php endif; ?>
    
    <!-- Filtreler -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <i class="fas fa-filter me-1"></i> Filtreleme Seçenekleri
        </div>
        <div class="card-body">
            <form action="" method="get" class="row g-3">
                <div class="col-md-4">
                    <label for="durum" class="form-label">Sipariş Durumu</label>
                    <select class="form-select" id="durum" name="durum">
                        <option value="">Tümü</option>
                        <option value="beklemede" <?php echo $durum_filtresi === 'beklemede' ? 'selected' : ''; ?>>Beklemede</option>
                        <option value="hazırlanıyor" <?php echo $durum_filtresi === 'hazırlanıyor' ? 'selected' : ''; ?>>Hazırlanıyor</option>
                        <option value="kargoya_verildi" <?php echo $durum_filtresi === 'kargoya_verildi' ? 'selected' : ''; ?>>Kargoya Verildi</option>
                        <option value="teslim_edildi" <?php echo $durum_filtresi === 'teslim_edildi' ? 'selected' : ''; ?>>Teslim Edildi</option>
                        <option value="iptal_edildi" <?php echo $durum_filtresi === 'iptal_edildi' ? 'selected' : ''; ?>>İptal Edildi</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="tarih" class="form-label">Sipariş Tarihi</label>
                    <select class="form-select" id="tarih" name="tarih">
                        <option value="">Tümü</option>
                        <option value="bugun" <?php echo $tarih_filtresi === 'bugun' ? 'selected' : ''; ?>>Bugün</option>
                        <option value="hafta" <?php echo $tarih_filtresi === 'hafta' ? 'selected' : ''; ?>>Son 1 Hafta</option>
                        <option value="ay" <?php echo $tarih_filtresi === 'ay' ? 'selected' : ''; ?>>Son 1 Ay</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-2"></i> Filtrele
                    </button>
                    <a href="siparisler.php" class="btn btn-outline-secondary">
                        <i class="fas fa-sync-alt me-2"></i> Sıfırla
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Siparişler Tablosu -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-shopping-cart me-1"></i> Tüm Siparişler
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="siparislerTable">
                    <thead>
                        <tr>
                            <th>Sipariş ID</th>
                            <th>Müşteri</th>
                            <th>Toplam Tutar</th>
                            <th>Ödeme Yöntemi</th>
                            <th>Durum</th>
                            <th>Sipariş Tarihi</th>
                            <th style="width: 160px">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($siparisler as $siparis): ?>
                            <tr>
                                <td>#<?php echo $siparis['id']; ?></td>
                                <td>
                                    <div><?php echo $siparis['ad'] . ' ' . $siparis['soyad']; ?></div>
                                    <small class="text-muted"><?php echo $siparis['email']; ?></small>
                                    <?php if (!empty($siparis['telefon'])): ?>
                                    <div><small class="text-muted"><?php echo $siparis['telefon']; ?></small></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo number_format($siparis['toplam_tutar'], 2, ',', '.'); ?> ₺</td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $siparis['odeme_yontemi'] ?? 'Belirtilmemiş')); ?></td>
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
                                        default:
                                            echo '<span class="badge bg-secondary">Belirsiz</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo date('d.m.Y H:i', strtotime($siparis['siparis_tarihi'])); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-primary" title="Detay" 
                                           onclick="siparisDetayGoster(<?php echo $siparis['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-success" title="Durum Güncelle" 
                                           data-bs-toggle="modal" data-bs-target="#durumGuncelle" 
                                           data-id="<?php echo $siparis['id']; ?>" 
                                           data-durum="<?php echo $siparis['durum']; ?>">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                        <a href="siparis-yazdir.php?id=<?php echo $siparis['id']; ?>" class="btn btn-secondary" title="Yazdır" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($siparisler)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Henüz sipariş bulunmamaktadır.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Durum Güncelleme Modal -->
<div class="modal fade" id="durumGuncelle" tabindex="-1" aria-labelledby="durumGuncelleLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="durumGuncelleLabel">Sipariş Durumu Güncelle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="siparis_id" id="siparis_id">
                    <div class="mb-3">
                        <label for="yeni_durum" class="form-label">Durum</label>
                        <select class="form-select" id="yeni_durum" name="yeni_durum" required>
                            <option value="beklemede">Beklemede</option>
                            <option value="hazırlanıyor">Hazırlanıyor</option>
                            <option value="kargoya_verildi">Kargoya Verildi</option>
                            <option value="teslim_edildi">Teslim Edildi</option>
                            <option value="iptal_edildi">İptal Edildi</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary" name="durum_guncelle">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sipariş Detay Modal -->
<div class="modal fade" id="siparisDetay" tabindex="-1" aria-labelledby="siparisDetayLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="siparisDetayLabel">Sipariş Detayı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Yükleniyor...</span>
                    </div>
                </div>
                <div id="siparisDetayIcerik"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // DataTable başlat
    new DataTable('#siparislerTable', {
        language: {
            url: 'https://cdn.datatables.net/plug-ins/2.0.0/i18n/tr.json'
        },
        responsive: true,
        order: [[5, 'desc']],
        columnDefs: [
            { 
                targets: 6, // İşlemler kolonu
                orderable: false,
                searchable: false
            }
        ]
    });
    
    // 3 saniye sonra alertleri gizle
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 3000);
    
    // Durum güncelleme modal
    document.getElementById('durumGuncelle').addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const siparis_id = button.getAttribute('data-id');
        const durum = button.getAttribute('data-durum');
        
        document.getElementById('siparis_id').value = siparis_id;
        document.getElementById('yeni_durum').value = durum;
    });
});

// Sipariş detay gösterme fonksiyonu
function siparisDetayGoster(siparisId) {
    const detayModal = new bootstrap.Modal(document.getElementById('siparisDetay'));
    detayModal.show();
    
    // Sipariş detaylarını Ajax ile yükle
    const detayIcerik = document.getElementById('siparisDetayIcerik');
    detayIcerik.innerHTML = '';
    
    // Normalde burada AJAX isteği yapılır ve sipariş detayları getirilir
    // Şimdilik örnek bir veri gösteriyoruz
    setTimeout(function() {
        detayIcerik.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2">Sipariş Bilgileri</h6>
                    <p><strong>Sipariş No:</strong> #${siparisId}</p>
                    <p><strong>Tarih:</strong> ${new Date().toLocaleDateString('tr-TR')}</p>
                    <p><strong>Durum:</strong> <span class="badge bg-primary">Hazırlanıyor</span></p>
                    <p><strong>Ödeme Yöntemi:</strong> Kredi Kartı</p>
                </div>
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2">Müşteri Bilgileri</h6>
                    <p><strong>Ad Soyad:</strong> Ahmet Yılmaz</p>
                    <p><strong>E-posta:</strong> ahmet@example.com</p>
                    <p><strong>Telefon:</strong> +90 555 123 4567</p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="border-bottom pb-2">Teslimat Adresi</h6>
                    <p>Merkez Mah. Cumhuriyet Cad. No:123 D:4, Şişli/İstanbul</p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="border-bottom pb-2">Sipariş Ürünleri</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Ürün</th>
                                    <th>Adet</th>
                                    <th>Birim Fiyat</th>
                                    <th>Toplam</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Nike Air Max 270</td>
                                    <td>1</td>
                                    <td>1.999,90 ₺</td>
                                    <td>1.999,90 ₺</td>
                                </tr>
                                <tr>
                                    <td>Adidas Running Sweatshirt</td>
                                    <td>2</td>
                                    <td>599,90 ₺</td>
                                    <td>1.199,80 ₺</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Ara Toplam:</strong></td>
                                    <td>3.199,70 ₺</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Kargo Ücreti:</strong></td>
                                    <td>29,90 ₺</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Toplam:</strong></td>
                                    <td><strong>3.229,60 ₺</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="border-bottom pb-2">Sipariş Notları</h6>
                    <p class="fst-italic">Lütfen kapıya bırakmayın, zile basın.</p>
                </div>
            </div>
        `;
    }, 1000);
}
</script>

<?php require_once 'templates/footer.php'; ?> 