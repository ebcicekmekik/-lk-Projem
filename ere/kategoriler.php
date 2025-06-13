<?php
require_once 'templates/header.php';

// Tüm kategorileri getir
$kategoriler = tumKategorileriGetir();
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Ana Sayfa</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tüm Kategoriler</li>
                </ol>
            </nav>
            
            <h1 class="border-bottom pb-2 mb-4">Tüm Kategoriler</h1>
        </div>
    </div>
    
    <div class="row">
        <?php if (empty($kategoriler)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Henüz kategori bulunmamaktadır.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($kategoriler as $kategori): ?>
                <div class="col-6 col-md-4 col-lg-3 mb-4">
                    <a href="/kategori.php?id=<?php echo $kategori['id']; ?>" class="text-decoration-none">
                        <div class="card h-100">
                            <div class="category-box">
                                <?php if (!empty($kategori['resim'])): ?>
                                    <img src="/uploads/kategoriler/<?php echo $kategori['resim']; ?>" alt="<?php echo $kategori['ad']; ?>">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/300x150.png?text=<?php echo urlencode($kategori['ad']); ?>" alt="<?php echo $kategori['ad']; ?>">
                                <?php endif; ?>
                                <div class="category-name"><?php echo $kategori['ad']; ?></div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title text-center"><?php echo $kategori['ad']; ?></h5>
                                <?php if (!empty($kategori['aciklama'])): ?>
                                    <p class="card-text text-muted small"><?php echo mb_substr($kategori['aciklama'], 0, 100); ?><?php echo strlen($kategori['aciklama']) > 100 ? '...' : ''; ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white text-center">
                                <span class="btn btn-sm btn-outline-primary">Ürünleri Görüntüle</span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?> 