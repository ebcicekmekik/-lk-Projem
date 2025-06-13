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

// Ürün silme işlemi
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $silinecek_id = $_GET['sil'];
    
    // Önce ürün resmi varsa sil
    $sorgu = $pdo->prepare("SELECT resim FROM urunler WHERE id = :id");
    $sorgu->execute(['id' => $silinecek_id]);
    $urun = $sorgu->fetch();
    
    if ($urun && $urun['resim'] && file_exists("../uploads/urunler/" . $urun['resim'])) {
        unlink("../uploads/urunler/" . $urun['resim']);
    }
    
    // Ürünü sil
    $sorgu = $pdo->prepare("DELETE FROM urunler WHERE id = :id");
    $sorgu->execute(['id' => $silinecek_id]);
    
    if ($sorgu->rowCount() > 0) {
        header("Location: urunler.php?basarili=1");
        exit;
    }
}

// Tüm ürünleri getir
$sorgu = $pdo->query("
    SELECT u.*, k.ad AS kategori_adi 
    FROM urunler u
    LEFT JOIN kategoriler k ON u.kategori_id = k.id
    ORDER BY u.eklenme_tarihi DESC
");
$urunler = $sorgu->fetchAll(PDO::FETCH_ASSOC);

// Sayfa başlığı
$sayfa_basligi = "Ürünler";

require_once 'templates/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4"><?php echo $sayfa_basligi; ?></h1>
        <a href="urun-ekle.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Yeni Ürün Ekle
        </a>
    </div>
    
    <?php if (isset($_GET['basarili'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            İşlem başarıyla tamamlandı.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-box-open me-1"></i> Tüm Ürünler
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="urunlerTable">
                    <thead>
                        <tr>
                            <th style="width: 60px">ID</th>
                            <th style="width: 100px">Resim</th>
                            <th>Ürün Adı</th>
                            <th>Kategori</th>
                            <th>Fiyat</th>
                            <th>Stok</th>
                            <th>Durum</th>
                            <th style="width: 150px">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($urunler as $urun): ?>
                            <tr>
                                <td><?php echo $urun['id']; ?></td>
                                <td>
                                    <?php if ($urun['resim'] && file_exists("../uploads/urunler/" . $urun['resim'])): ?>
                                        <img src="../uploads/urunler/<?php echo $urun['resim']; ?>" alt="<?php echo $urun['ad']; ?>" class="img-thumbnail" style="max-width: 50px; max-height: 50px;">
                                    <?php else: ?>
                                        <div class="bg-light text-center p-2 rounded">
                                            <i class="fas fa-image text-secondary"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $urun['ad']; ?></td>
                                <td><?php echo $urun['kategori_adi'] ?? 'Kategorisiz'; ?></td>
                                <td><?php echo number_format($urun['fiyat'], 2, ',', '.'); ?> ₺</td>
                                <td><?php echo $urun['stok_adedi']; ?> adet</td>
                                <td>
                                    <?php if (isset($urun['aktif']) && $urun['aktif'] == 1): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Pasif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="urun-duzenle.php?id=<?php echo $urun['id']; ?>" class="btn btn-primary" title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="javascript:void(0);" onclick="silmeOnayi(<?php echo $urun['id']; ?>, '<?php echo addslashes($urun['ad']); ?>')" class="btn btn-danger" title="Sil">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($urunler)): ?>
                            <tr>
                                <td colspan="8" class="text-center">Henüz ürün bulunmamaktadır.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Silme Onayı Modal -->
<div class="modal fade" id="silmeOnayi" tabindex="-1" aria-labelledby="silmeOnayiLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="silmeOnayiLabel"><i class="fas fa-exclamation-triangle me-2"></i> Ürün Silme Onayı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <p id="silmeMetni">Bu ürünü silmek istediğinize emin misiniz? Bu işlem geri alınamaz!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <a href="#" class="btn btn-danger" id="silmeButonu">Ürünü Sil</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // DataTable başlat
    $('#urunlerTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/2.0.0/i18n/tr.json'
        },
        order: [[0, 'desc']]
    });
    
    // 3 saniye sonra alertleri gizle
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 3000);
});

// Silme onayı fonksiyonu
function silmeOnayi(id, isim) {
    document.getElementById('silmeMetni').innerHTML = `<strong>${isim}</strong> adlı ürünü silmek istediğinize emin misiniz? Bu işlem geri alınamaz!`;
    document.getElementById('silmeButonu').href = `urunler.php?sil=${id}`;
    var silmeModal = new bootstrap.Modal(document.getElementById('silmeOnayi'));
    silmeModal.show();
}
</script>

<?php require_once 'templates/footer.php'; ?> 