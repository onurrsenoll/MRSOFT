/**
 * MR HASAR DANIŞMANLIK - JAVASCRIPT V2.0 FINAL
 * EXCELLENCE DISTINGUISHES US ALWAYS
 */

console.log('MR HASAR DANIŞMANLIK V2.0 - EXCELLENCE DISTINGUISHES US ALWAYS');

// Bildirim göster
function showNotification(message, type = 'info') {
    const div = document.createElement('div');
    div.className = 'alert alert-' + type;
    div.innerHTML = '<i class="fas fa-info-circle"></i> ' + message;
    div.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;min-width:300px;animation:slideIn 0.3s ease';
    document.body.appendChild(div);
    
    setTimeout(() => {
        div.remove();
    }, 3000);
}

// Silme onayı
function confirmDelete(message) {
    return confirm(message || 'Bu kaydı silmek istediğinizden emin misiniz?');
}

// Para formatı
function formatMoney(amount) {
    return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY'
    }).format(amount);
}

// Tarih formatı
function formatDate(date) {
    return new Date(date).toLocaleDateString('tr-TR');
}
