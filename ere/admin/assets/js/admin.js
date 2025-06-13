/**
 * Admin Panel Ana JavaScript - SportMağazası
 */

document.addEventListener('DOMContentLoaded', function() {
    // Kenar çubuğunu aç/kapat
    initSidebar();
    
    // DataTables
    initDataTables();
    
    // SummerNote
    initSummerNote();
    
    // Form Doğrulama
    initFormValidation();
    
    // Resim Önizleme
    initImagePreview();
    
    // Tooltip ve Popover'ları etkinleştir
    initTooltipsAndPopovers();
    
    // Animasyonlu sayaçları etkinleştir
    animateCounters();
    
    // Grafikleri başlat
    initCharts();
    
    // Kolay sıralama için seçilen tabloları başlat
    initSortableTables();
    
    // Bildirim kartı sayacını ayarla
    updateNotificationCount();
    
    // Modal form doğrulamasını ayarla
    initModalFormValidation();
    
    // Otomatik kapanan uyarılar
    initAutoCloseAlerts();
    
    // Tarih seçicileri başlat
    initDatePickers();
});

/**
 * Kenar çubuğu davranışlarını başlat
 */
function initSidebar() {
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        // Sayfa açıldığında localStorage'daki değeri kontrol et
        if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
            document.body.classList.add('sb-sidenav-toggled');
        }
        
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        });
    }
    
    // Aktif sayfayı işaretle
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.sb-sidenav-menu .nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
            
            // Ana menüyü aç
            const parentCollapseDiv = link.closest('.collapse');
            if (parentCollapseDiv) {
                parentCollapseDiv.classList.add('show');
                const controlElement = document.querySelector(`[data-bs-target="#${parentCollapseDiv.id}"]`);
                if (controlElement) {
                    controlElement.setAttribute('aria-expanded', 'true');
                    controlElement.classList.remove('collapsed');
                }
            }
        }
    });
}

/**
 * DataTables başlatma
 */
function initDataTables() {
    const datatablesSimple = document.getElementById('datatablesSimple');
    if (datatablesSimple) {
        new DataTable('#datatablesSimple', {
            language: {
                url: '//cdn.datatables.net/plug-ins/2.0.0/i18n/tr.json',
            },
            responsive: true,
            stateSave: true,
            dom: '<"top"<"left-tools"B><"right-tools"f>>rt<"bottom"<"left-tools"i><"right-tools"p>>',
            buttons: [
                {
                    extend: 'collection',
                    text: '<i class="fas fa-download"></i> Dışa Aktar',
                    buttons: [
                        'copy', 'excel', 'csv', 'pdf', 'print'
                    ]
                }
            ]
        });
    }
}

/**
 * SummerNote başlatma
 */
function initSummerNote() {
    const summernoteEditor = document.querySelector('.summernote');
    if (summernoteEditor) {
        $(summernoteEditor).summernote({
            height: 300,
            minHeight: 200,
            maxHeight: 500,
            lang: 'tr-TR',
            placeholder: 'İçeriğinizi buraya yazın...',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            callbacks: {
                onImageUpload: function(files) {
                    for(let i = 0; i < files.length; i++) {
                        uploadSummernoteImage(files[i], this);
                    }
                }
            }
        });
    }
}

/**
 * Summernote için resim yükleme fonksiyonu
 */
function uploadSummernoteImage(file, editor) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('action', 'upload_editor_image');
    
    fetch('ajax/upload.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $(editor).summernote('insertImage', data.url);
        } else {
            alert('Resim yükleme hatası: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Resim yükleme hatası:', error);
        alert('Resim yüklenirken bir hata oluştu.');
    });
}

/**
 * Form doğrulama başlatma
 */
function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
}

/**
 * Resim Önizleme Fonksiyonu
 */
function initImagePreview() {
    // Tekli resim önizleme
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Çoklu resim önizleme
    const multiImageInput = document.getElementById('multiImages');
    const multiImagePreviewContainer = document.getElementById('multiImagePreview');
    
    if (multiImageInput && multiImagePreviewContainer) {
        multiImageInput.addEventListener('change', function() {
            multiImagePreviewContainer.innerHTML = '';
            
            for (let i = 0; i < this.files.length; i++) {
                const file = this.files[i];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imgWrapper = document.createElement('div');
                        imgWrapper.className = 'preview-image-wrapper col-md-3 mb-3';
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'img-thumbnail preview-image';
                        imgWrapper.appendChild(img);
                        
                        const removeBtn = document.createElement('div');
                        removeBtn.className = 'remove-image';
                        removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                        removeBtn.addEventListener('click', function() {
                            imgWrapper.remove();
                        });
                        imgWrapper.appendChild(removeBtn);
                        
                        multiImagePreviewContainer.appendChild(imgWrapper);
                    }
                    reader.readAsDataURL(file);
                }
            }
        });
    }
}

/**
 * Tooltip ve Popover'ları başlat
 */
function initTooltipsAndPopovers() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

/**
 * Animasyonlu sayaçları etkinleştir
 */
function animateCounters() {
    const counters = document.querySelectorAll('.counter-value');
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const duration = 1000; // ms
        const step = target / (duration / 16); // 16ms is approx 60fps
        
        let current = 0;
        const updateCounter = () => {
            current += step;
            if (current < target) {
                counter.innerText = Math.floor(current).toLocaleString('tr-TR');
                requestAnimationFrame(updateCounter);
            } else {
                counter.innerText = target.toLocaleString('tr-TR');
            }
        };
        
        updateCounter();
    });
}

/**
 * Grafikleri başlat
 */
function initCharts() {
    // Satış Grafiği
    const salesChartCanvas = document.getElementById('salesChart');
    if (salesChartCanvas) {
        const salesChart = new Chart(salesChartCanvas, {
            type: 'line',
            data: {
                labels: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
                datasets: [{
                    label: 'Aylık Satış',
                    data: [2500, 3800, 3000, 5000, 6000, 5500, 7000, 8500, 7800, 9000, 11000, 13000],
                    backgroundColor: 'rgba(26, 188, 156, 0.2)',
                    borderColor: 'rgba(26, 188, 156, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    pointBackgroundColor: 'rgba(26, 188, 156, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            // TL formatında
                            callback: function(value) {
                                return value.toLocaleString('tr-TR') + ' ₺';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw.toLocaleString('tr-TR') + ' ₺';
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Kategori Dağılımı Grafiği
    const categoryChartCanvas = document.getElementById('categoryChart');
    if (categoryChartCanvas) {
        const categoryChart = new Chart(categoryChartCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Ayakkabı', 'Giyim', 'Ekipman', 'Aksesuarlar', 'Diğer'],
                datasets: [{
                    data: [35, 25, 20, 15, 5],
                    backgroundColor: [
                        'rgba(26, 188, 156, 0.8)',
                        'rgba(52, 152, 219, 0.8)',
                        'rgba(155, 89, 182, 0.8)',
                        'rgba(241, 196, 15, 0.8)',
                        'rgba(231, 76, 60, 0.8)'
                    ],
                    borderColor: [
                        'rgba(26, 188, 156, 1)',
                        'rgba(52, 152, 219, 1)',
                        'rgba(155, 89, 182, 1)',
                        'rgba(241, 196, 15, 1)',
                        'rgba(231, 76, 60, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': %' + context.raw;
                            }
                        }
                    }
                }
            }
        });
    }
}

/**
 * Kolay sıralama için seçilen tabloları başlat
 */
function initSortableTables() {
    // Sortable.js kütüphanesi yüklenmişse
    if (typeof Sortable !== 'undefined') {
        const sortableLists = document.querySelectorAll('.sortable-list');
        
        sortableLists.forEach(list => {
            Sortable.create(list, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onEnd: function(evt) {
                    // Sıralama değişikliğini sakla
                    saveSortOrder(evt.target);
                }
            });
        });
    }
}

/**
 * Sıralama değişikliğini sunucuya kaydet
 */
function saveSortOrder(container) {
    const items = container.querySelectorAll('[data-id]');
    const itemIds = Array.from(items).map((item, index) => {
        return {
            id: item.getAttribute('data-id'),
            order: index + 1
        };
    });
    
    // Sunucuya gönder
    if (container.getAttribute('data-save-url')) {
        fetch(container.getAttribute('data-save-url'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ items: itemIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Sıralama başarıyla kaydedildi', 'success');
            } else {
                showToast('Sıralama kaydedilirken bir hata oluştu', 'danger');
            }
        })
        .catch(error => {
            console.error('Sıralama kaydetme hatası:', error);
            showToast('Sıralama kaydedilirken bir hata oluştu', 'danger');
        });
    }
}

/**
 * Bildirim kartı sayacını güncelle
 */
function updateNotificationCount() {
    const notificationBadge = document.querySelector('.badge-notification');
    const notificationItems = document.querySelectorAll('.notification-item:not(.notification-read)');
    
    if (notificationBadge && notificationItems.length > 0) {
        notificationBadge.textContent = notificationItems.length;
        notificationBadge.style.display = 'inline-block';
    } else if (notificationBadge) {
        notificationBadge.style.display = 'none';
    }
}

/**
 * Modal form doğrulamasını ayarla
 */
function initModalFormValidation() {
    // Form içeren modallar
    const modalForms = document.querySelectorAll('.modal form');
    
    modalForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    });
    
    // Mod al açıldığında form sıfırlama
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function() {
            const form = this.querySelector('form');
            if (form) {
                form.reset();
                form.classList.remove('was-validated');
            }
        });
    });
}

/**
 * Otomatik kapanan uyarılar
 */
function initAutoCloseAlerts() {
    const autoAlerts = document.querySelectorAll('.alert-auto-dismiss');
    autoAlerts.forEach(alert => {
        const delay = alert.getAttribute('data-delay') || 5000;
        
        setTimeout(() => {
            // Bootstrap uyarısını otomatik kapat
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, parseInt(delay));
    });
}

/**
 * Toast mesajı göster
 */
function showToast(message, type = 'info') {
    // Toast konteynerini kontrol et, yoksa oluştur
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    // Toast element ID'si
    const toastId = 'toast-' + new Date().getTime();
    
    // Toast HTML
    const toastHtml = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-${type} text-white">
                <strong class="me-auto">${type === 'success' ? 'Başarılı' : type === 'danger' ? 'Hata' : type === 'warning' ? 'Uyarı' : 'Bilgi'}</strong>
                <small>Şimdi</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Kapat"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    // Toast'u konteyner içine ekle
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Toast'u göster
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 5000
    });
    toast.show();
    
    // Kapandığında DOM'dan kaldır
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

/**
 * Tarih seçicileri başlat
 */
function initDatePickers() {
    // Flatpickr kütüphanesi yüklenmişse
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.datepicker', {
            dateFormat: 'd.m.Y',
            locale: 'tr',
            allowInput: true
        });
        
        flatpickr('.datetimepicker', {
            dateFormat: 'd.m.Y H:i',
            enableTime: true,
            time_24hr: true,
            locale: 'tr',
            allowInput: true
        });
        
        // Tarih aralığı seçici
        flatpickr('.daterange', {
            dateFormat: 'd.m.Y',
            locale: 'tr',
            mode: 'range',
            allowInput: true
        });
    }
} 