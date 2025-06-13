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

// Kullanıcı silme işlemi
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $silinecek_id = $_GET['sil'];
    
    // Admin kendini silemez kontrolü
    if ($silinecek_id == $_SESSION['kullanici_id']) {
        $hata = "Kendinizi silemezsiniz!";
    } else {
        $sorgu = $pdo->prepare("DELETE FROM kullanicilar WHERE id = :id AND rol != 'admin'");
        $sorgu->execute(['id' => $silinecek_id]);
        
        if ($sorgu->rowCount() > 0) {
            $basarili = "Kullanıcı başarıyla silindi.";
            header("Location: kullanicilar.php?basarili=1");
            exit;
        } else {
            $hata = "Kullanıcı silinemedi. Admin kullanıcılar silinemez.";
        }
    }
}

// Tüm kullanıcıları getir
$sorgu = $pdo->query("SELECT * FROM kullanicilar ORDER BY kayit_tarihi DESC");
$kullanicilar = $sorgu->fetchAll(PDO::FETCH_ASSOC);

// Sayfa başlığı
$sayfa_basligi = "Kullanıcılar";

require_once 'templates/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4"><?php echo $sayfa_basligi; ?></h1>
        <a href="kullanici-ekle.php" class="btn btn-primary">
            <i class="fas fa-user-plus me-2"></i> Yeni Kullanıcı
        </a>
    </div>
    
    <?php if (isset($hata)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $hata; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['basarili'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Kullanıcı başarıyla silindi.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-users me-1"></i> Tüm Kullanıcılar
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="kullanicilarTable">
                    <thead>
                        <tr>
                            <th style="width: 60px">ID</th>
                            <th>Ad Soyad</th>
                            <th>E-posta</th>
                            <th>Rol</th>
                            <th>Kayıt Tarihi</th>
                            <th style="width: 150px">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kullanicilar as $kullanici): ?>
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
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="kullanici-duzenle.php?id=<?php echo $kullanici['id']; ?>" class="btn btn-primary" title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($kullanici['id'] != $_SESSION['kullanici_id'] && $kullanici['rol'] != 'admin'): ?>
                                            <a href="javascript:void(0);" onclick="silmeOnayi(<?php echo $kullanici['id']; ?>, '<?php echo addslashes($kullanici['ad'] . ' ' . $kullanici['soyad']); ?>')" class="btn btn-danger" title="Sil">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-danger" disabled title="Bu kullanıcı silinemez">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($kullanicilar)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Henüz kayıtlı kullanıcı bulunmamaktadır.</td>
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
                <h5 class="modal-title" id="silmeOnayiLabel"><i class="fas fa-exclamation-triangle me-2"></i> Kullanıcı Silme Onayı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <p id="silmeMetni">Bu kullanıcıyı silmek istediğinize emin misiniz? Bu işlem geri alınamaz!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <a href="#" class="btn btn-danger" id="silmeButonu">Kullanıcıyı Sil</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // DataTable başlat
    new DataTable('#kullanicilarTable', {
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
    document.getElementById('silmeMetni').innerHTML = `<strong>${isim}</strong> adlı kullanıcıyı silmek istediğinize emin misiniz? Bu işlem geri alınamaz!`;
    document.getElementById('silmeButonu').href = `kullanicilar.php?sil=${id}`;
    var silmeModal = new bootstrap.Modal(document.getElementById('silmeOnayi'));
    silmeModal.show();
}
</script>

<?php require_once 'templates/footer.php'; ?> 