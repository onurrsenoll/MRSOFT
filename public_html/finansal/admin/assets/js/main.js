/**
 * MR HASAR DANIŞMANLIK - Ana JavaScript Dosyası
 * HERZAMAN FARKEDER
 */

$(document).ready(function() {
    // ═══════════════════════════════════════════════════════════════
    // SIDEBAR TOGGLE
    // ═══════════════════════════════════════════════════════════════

    $('.sidebar-toggle').on('click', function() {
        const sidebar = $('#sidebar');
        if (window.innerWidth <= 992) {
            sidebar.toggleClass('show');
        } else {
            sidebar.toggleClass('collapsed');
        }
    });

    // Close sidebar on mobile when clicking outside
    $(document).on('click', function(e) {
        if (window.innerWidth <= 992) {
            if (!$(e.target).closest('#sidebar, .sidebar-toggle').length) {
                $('#sidebar').removeClass('show');
            }
        }
    });

    // ═══════════════════════════════════════════════════════════════
    // DATATABLES INITIALIZATION
    // ═══════════════════════════════════════════════════════════════

    if ($.fn.DataTable) {
        $('.datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json',
            },
            pageLength: 25,
            responsive: true,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
            order: [[0, 'desc']]
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // SELECT2 INITIALIZATION
    // ═══════════════════════════════════════════════════════════════

    if ($.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap-5',
            allowClear: true,
            placeholder: 'Seçiniz...'
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // FLATPICKR INITIALIZATION
    // ═══════════════════════════════════════════════════════════════

    if (typeof flatpickr !== 'undefined') {
        flatpickr.localize(flatpickr.l10ns.tr);

        // Date picker
        flatpickr('.datepicker', {
            dateFormat: 'd.m.Y',
            allowInput: true
        });

        // Date time picker
        flatpickr('.datetimepicker', {
            dateFormat: 'd.m.Y H:i',
            enableTime: true,
            time_24hr: true,
            allowInput: true
        });

        // Time picker
        flatpickr('.timepicker', {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // FORM VALIDATION
    // ═══════════════════════════════════════════════════════════════

    // TC Kimlik validation
    $.fn.validateTC = function() {
        return this.each(function() {
            $(this).on('blur', function() {
                const tc = $(this).val();
                if (tc && !isValidTC(tc)) {
                    $(this).addClass('is-invalid');
                    showToast('error', 'Geçersiz TC Kimlik numarası');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
        });
    };

    $('[data-validate="tc"]').validateTC();

    // Phone formatting
    $('[data-format="phone"]').on('input', function() {
        let phone = $(this).val().replace(/\D/g, '');
        if (phone.length > 10) phone = phone.substr(0, 10);
        $(this).val(phone);
    });

    // Money formatting
    $('[data-format="money"]').on('blur', function() {
        let value = $(this).val().replace(/[^\d,\.]/g, '');
        value = value.replace(',', '.');
        if (value) {
            value = parseFloat(value).toFixed(2);
            $(this).val(formatMoney(value));
        }
    });

    // ═══════════════════════════════════════════════════════════════
    // DELETE CONFIRMATION
    // ═══════════════════════════════════════════════════════════════

    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        const url = $(this).attr('href') || $(this).data('url');
        const name = $(this).data('name') || 'Bu kaydı';

        Swal.fire({
            title: 'Emin misiniz?',
            text: name + ' silmek istediğinizden emin misiniz?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Evet, Sil',
            cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });

    // ═══════════════════════════════════════════════════════════════
    // AJAX FORM SUBMIT
    // ═══════════════════════════════════════════════════════════════

    $(document).on('submit', '.ajax-form', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('[type="submit"]');
        const originalText = submitBtn.html();

        // Disable submit button
        submitBtn.prop('disabled', true).html('<span class="loading-spinner"></span>');

        $.ajax({
            url: form.attr('action'),
            method: form.attr('method') || 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message || 'İşlem başarılı');
                    if (response.redirect) {
                        setTimeout(() => window.location.href = response.redirect, 1000);
                    } else if (response.reload) {
                        setTimeout(() => location.reload(), 1000);
                    }
                } else {
                    showToast('error', response.message || 'Bir hata oluştu');
                }
            },
            error: function(xhr) {
                showToast('error', 'Bir hata oluştu. Lütfen tekrar deneyin.');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // ═══════════════════════════════════════════════════════════════
    // FILE UPLOAD PREVIEW
    // ═══════════════════════════════════════════════════════════════

    $(document).on('change', '.file-upload-input', function() {
        const input = this;
        const preview = $(this).closest('.file-upload').find('.file-preview');

        if (input.files && input.files[0]) {
            const file = input.files[0];
            const fileName = file.name;
            const fileSize = formatFileSize(file.size);

            preview.html(`
                <div class="alert alert-info d-flex align-items-center">
                    <i class="bi bi-file-earmark me-2"></i>
                    <span>${fileName} (${fileSize})</span>
                </div>
            `);
        }
    });

    // ═══════════════════════════════════════════════════════════════
    // TOOLTIPS
    // ═══════════════════════════════════════════════════════════════

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // ═══════════════════════════════════════════════════════════════
    // AUTO DISMISS ALERTS
    // ═══════════════════════════════════════════════════════════════

    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
});

// ═══════════════════════════════════════════════════════════════
// HELPER FUNCTIONS
// ═══════════════════════════════════════════════════════════════

/**
 * TC Kimlik doğrulama
 */
function isValidTC(tc) {
    if (tc.length !== 11) return false;
    if (tc[0] === '0') return false;
    if (!/^\d+$/.test(tc)) return false;

    let odds = 0, evens = 0, total = 0;
    for (let i = 0; i < 10; i++) {
        total += parseInt(tc[i]);
        if (i % 2 === 0) odds += parseInt(tc[i]);
        else evens += parseInt(tc[i]);
    }

    const digit10 = ((odds * 7) - evens) % 10;
    const digit11 = total % 10;

    return (parseInt(tc[9]) === (digit10 < 0 ? digit10 + 10 : digit10)) &&
           (parseInt(tc[10]) === digit11);
}

/**
 * Para formatla
 */
function formatMoney(amount, currency = '₺') {
    return new Intl.NumberFormat('tr-TR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount) + ' ' + currency;
}

/**
 * Dosya boyutu formatla
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Toast notification
 */
function showToast(type, message) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    Toast.fire({
        icon: type,
        title: message
    });
}

/**
 * Confirm dialog
 */
function confirmAction(title, text, callback) {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#1e3a5f',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Evet',
        cancelButtonText: 'Hayır'
    }).then((result) => {
        if (result.isConfirmed && typeof callback === 'function') {
            callback();
        }
    });
}

/**
 * Print element
 */
function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>Yazdır</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { padding: 20px; }
                @media print {
                    .no-print { display: none !important; }
                }
            </style>
        </head>
        <body>
            ${element.innerHTML}
            <script>
                setTimeout(function() { window.print(); window.close(); }, 500);
            </script>
        </body>
        </html>
    `);
    printWindow.document.close();
}

/**
 * Export to Excel
 */
function exportToExcel(tableId, fileName) {
    const table = document.getElementById(tableId);
    if (!table) return;

    let html = table.outerHTML;
    const blob = new Blob(['\ufeff' + html], {
        type: 'application/vnd.ms-excel'
    });

    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = fileName + '.xls';
    link.click();
}
