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

// Kategori silme işlemi
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $silinecek_id = $_GET['sil'];
    
        // Alt kategoriler kontrolünü şimdilik atlayalım, çünkü tabloda ust_kategori_id alanı yok
    
    // Kategoriye ait ürünleri kontrol et
    $sorgu = $pdo->prepare("SELECT COUNT(*) FROM urunler WHERE kategori_id = :id");
    $sorgu->execute(['id' => $silinecek_id]);
    $urun_sayisi = $sorgu->fetchColumn();
    
    if ($urun_sayisi > 0) {
        header("Location: kategoriler.php?hata=urun");
        exit;
    }
    
    // Kategori resmi varsa sil
    $sorgu = $pdo->prepare("SELECT resim FROM kategoriler WHERE id = :id");
    $sorgu->execute(['id' => $silinecek_id]);
    $kategori = $sorgu->fetch();
    
    if ($kategori && $kategori['resim'] && file_exists("../uploads/kategoriler/" . $kategori['resim'])) {
        unlink("../uploads/kategoriler/" . $kategori['resim']);
    }
    
    // Kategoriyi sil
    $sorgu = $pdo->prepare("DELETE FROM kategoriler WHERE id = :id");
    $sorgu->execute(['id' => $silinecek_id]);
    
    if ($sorgu->rowCount() > 0) {
        header("Location: kategoriler.php?basarili=1");
        exit;
    }
}

// Tüm kategorileri getir
$sorgu = $pdo->query("
    SELECT k.*,
           (SELECT COUNT(*) FROM urunler WHERE kategori_id = k.id) AS urun_sayisi
    FROM kategoriler k
    ORDER BY k.ad ASC
");
$kategoriler = $sorgu->fetchAll(PDO::FETCH_ASSOC);

// Sayfa başlığı
$sayfa_basligi = "Kategoriler";

require_once 'templates/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4"><?php echo $sayfa_basligi; ?></h1>
        <a href="kategori-ekle.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Yeni Kategori Ekle
        </a>
    </div>
    
    <?php if (isset($_GET['hata']) && $_GET['hata'] === 'alt_kategori'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            Bu kategorinin alt kategorileri olduğu için silinemez. Önce alt kategorileri silmeniz gerekiyor.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['hata']) && $_GET['hata'] === 'urun'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            Bu kategoriye ait ürünler olduğu için silinemez. Önce bu kategorideki ürünleri silmeniz veya başka bir kategoriye taşımanız gerekiyor.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['basarili'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            İşlem başarıyla tamamlandı.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-folder me-1"></i> Tüm Kategoriler
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="kategorilerTable">
                    <thead>
                        <tr>
                            <th style="width: 60px">ID</th>
                            <th style="width: 80px">Görsel</th>
                            <th>Kategori Adı</th>
                            <th>Açıklama</th>
                            <th>Ürün Sayısı</th>
                            <th style="width: 150px">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kategoriler as $kategori): ?>
                            <tr>
                                <td><?php echo $kategori['id']; ?></td>
                                <td>
                                    <?php if ($kategori['resim'] && file_exists("../uploads/kategoriler/" . $kategori['resim'])): ?>
                                        <img src="../uploads/kategoriler/<?php echo $kategori['resim']; ?>" alt="<?php echo $kategori['ad']; ?>" class="img-thumbnail" style="max-width: 50px; max-height: 50px;">
                                    <?php else: ?>
                                        <div class="bg-light text-center p-2 rounded">
                                            <i class="fas fa-folder text-secondary"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $kategori['ad']; ?></td>
                                <td>
                                    <?php if (!empty($kategori['aciklama'])): ?>
                                        <?php echo mb_substr($kategori['aciklama'], 0, 50); ?><?php echo (mb_strlen($kategori['aciklama']) > 50) ? '...' : ''; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($kategori['urun_sayisi'] > 0): ?>
                                        <a href="urunler.php?kategori=<?php echo $kategori['id']; ?>" class="badge bg-info text-decoration-none">
                                            <?php echo $kategori['urun_sayisi']; ?> ürün
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Ürün yok</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="kategori-duzenle.php?id=<?php echo $kategori['id']; ?>" class="btn btn-primary" title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="javascript:void(0);" onclick="silmeOnayi(<?php echo $kategori['id']; ?>, '<?php echo addslashes($kategori['ad']); ?>')" class="btn btn-danger" title="Sil">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($kategoriler)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Henüz kategori bulunmamaktadır.</td>
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
                <h5 class="modal-title" id="silmeOnayiLabel"><i class="fas fa-exclamation-triangle me-2"></i> Kategori Silme Onayı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <p id="silmeMetni">Bu kategoriyi silmek istediğinize emin misiniz? Bu işlem geri alınamaz!</p>
                <p class="text-warning">Not: Alt kategorisi olan veya içinde ürün bulunan kategoriler silinemez.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <a href="#" class="btn btn-danger" id="silmeButonu">Kategoriyi Sil</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($kategoriler)): ?>
    // DataTable başlat
    $('#kategorilerTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/2.0.0/i18n/tr.json'
        },
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [1, 5] }
        ]
    });
    <?php endif; ?>
    
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
    document.getElementById('silmeMetni').innerHTML = `<strong>${isim}</strong> kategorisini silmek istediğinize emin misiniz? Bu işlem geri alınamaz!`;
    document.getElementById('silmeButonu').href = `kategoriler.php?sil=${id}`;
    var silmeModal = new bootstrap.Modal(document.getElementById('silmeOnayi'));
    silmeModal.show();
}
</script>

<?php require_once 'templates/footer.php'; ?> 