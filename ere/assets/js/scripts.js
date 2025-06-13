// SportMağazası JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Bootstrap Tooltip aktifleştirme
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Sepet miktar butonları için fonksiyon
    const quantityInputs = document.querySelectorAll('.quantity-input');
    if (quantityInputs.length > 0) {
        quantityInputs.forEach(function(input) {
            const decrementBtn = input.previousElementSibling;
            const incrementBtn = input.nextElementSibling;
            
            if (decrementBtn) {
                decrementBtn.addEventListener('click', function() {
                    if (input.value > 1) {
                        input.value = parseInt(input.value) - 1;
                        if (input.dataset.action === 'update-cart') {
                            updateCartItemQuantity(input);
                        }
                    }
                });
            }
            
            if (incrementBtn) {
                incrementBtn.addEventListener('click', function() {
                    const maxStock = parseInt(input.dataset.maxStock || 100);
                    if (parseInt(input.value) < maxStock) {
                        input.value = parseInt(input.value) + 1;
                        if (input.dataset.action === 'update-cart') {
                            updateCartItemQuantity(input);
                        }
                    }
                });
            }
            
            input.addEventListener('change', function() {
                const maxStock = parseInt(input.dataset.maxStock || 100);
                if (parseInt(input.value) < 1) {
                    input.value = 1;
                } else if (parseInt(input.value) > maxStock) {
                    input.value = maxStock;
                }
                
                if (input.dataset.action === 'update-cart') {
                    updateCartItemQuantity(input);
                }
            });
        });
    }
    
    // Sepet güncelleme fonksiyonu
    function updateCartItemQuantity(input) {
        const form = input.closest('form');
        if (form) {
            form.submit();
        }
    }
    
    // Ürün filtreleme için fiyat slider'ı
    const priceRangeSlider = document.getElementById('price-range');
    if (priceRangeSlider) {
        const minPriceInput = document.getElementById('min-price');
        const maxPriceInput = document.getElementById('max-price');
        
        priceRangeSlider.addEventListener('input', function(e) {
            const values = priceRangeSlider.value.split(',');
            minPriceInput.value = values[0];
            maxPriceInput.value = values[1];
        });
    }
    
    // Ürün detay sayfasındaki resim galerisi
    const productMainImg = document.getElementById('product-main-img');
    const productThumbs = document.querySelectorAll('.product-thumb');
    
    if (productMainImg && productThumbs.length > 0) {
        productThumbs.forEach(function(thumb) {
            thumb.addEventListener('click', function() {
                const imgSrc = this.getAttribute('data-src');
                productMainImg.src = imgSrc;
                
                // Aktif thumbnail'i güncelle
                productThumbs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }
    
    // Ürün detay sayfası yıldız puanlaması
    const starRating = document.querySelector('.star-rating-input');
    const ratingValue = document.getElementById('rating-value');
    
    if (starRating && ratingValue) {
        const stars = starRating.querySelectorAll('.star');
        
        stars.forEach(function(star, index) {
            star.addEventListener('click', function() {
                const value = index + 1;
                ratingValue.value = value;
                
                // Yıldızları güncelle
                stars.forEach((s, i) => {
                    s.classList.toggle('active', i < value);
                });
            });
        });
    }
    
    // Mesaj otomatik kapatma
    const alerts = document.querySelectorAll('.alert-dismissible');
    
    if (alerts.length > 0) {
        alerts.forEach(function(alert) {
            setTimeout(function() {
                const closeBtn = new bootstrap.Alert(alert);
                closeBtn.close();
            }, 5000); // 5 saniye sonra kapat
        });
    }
}); 