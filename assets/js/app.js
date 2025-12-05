// Toast üzenetek
function showToast(type, title, message) {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger', 
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';
    
    const toastHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 1055; min-width: 300px;">
            <strong>${title}</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', toastHtml);
    
    // Automatikus eltávolítás
    setTimeout(() => {
        const alert = document.querySelector('.alert:last-child');
        if (alert) {
            alert.remove();
        }
    }, 5000); 
}

// AJAX kérések
async function apiCall(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                ...options.headers
            },
            ...options
        });
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('API hiba:', error);
        showToast('error', 'Hiba', 'Hálózati hiba történt');
        return { error: 'Hálózati hiba' };
    }
}

// Kosár mennyiség frissítése
function updateCartCount(count) {
    const cartCount = document.querySelector('.cart-count');
    if (cartCount) {
        cartCount.textContent = count;
        if (count > 0) {
            cartCount.style.display = 'inline-block';
        } else {
            cartCount.style.display = 'none';
        }
    }
    
    // Session storage frissítése
    sessionStorage.setItem('cartCount', count);
}

// FormData objektum átalakítása URL encoded formátumba
function formDataToUrlEncoded(formData) {
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
        params.append(key, value);
    }
    return params.toString();
}

// Kosár számláló betöltése oldal betöltésekor
function loadCartCount() {
    const savedCount = sessionStorage.getItem('cartCount');
    if (savedCount !== null) {
        updateCartCount(parseInt(savedCount));
    }
}

// Dokumentum betöltése
document.addEventListener('DOMContentLoaded', function() {
    // Kosár számláló betöltése
    loadCartCount();
    
    // Kosárhoz adás formok
    document.querySelectorAll('form[action*="cart_add"]').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            
            // Loading állapot
            submitButton.innerHTML = '<i class="bi bi-arrow-repeat spinner"></i> Feldolgozás...';
            submitButton.disabled = true;
            
            try {
                const formData = new FormData(this);
                const response = await apiCall(this.action, {
                    method: 'POST',
                    body: formDataToUrlEncoded(formData)
                });
                
                if (response.success) {
                    showToast('success', 'Siker', response.message);
                    if (response.cart_count !== undefined) {
                        updateCartCount(response.cart_count);
                    }
                    
                    // Kis késleltetés után frissítjük a lapot, ha szükséges
                    setTimeout(() => {
                        if (window.location.pathname.includes('cart.php')) {
                            window.location.reload();
                        }
                    }, 1000);
                } else {
                    showToast('error', 'Hiba', response.error);
                }
            } catch (error) {
                showToast('error', 'Hiba', 'Váratlan hiba történt');
            } finally {
                // Visszaállítás
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
            }
        });
    });
    
    // Automatikus modal megnyitás URL paraméter alapján
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('edit') || urlParams.has('create')) {
        const modal = new bootstrap.Modal(document.getElementById('bookModal'));
        modal.show();
    }
    
    // Mennyiség módosítás a kosárban
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const form = this.closest('form');
            if (form) {
                form.submit();
            }
        });
    });
});

// Bootstrap tooltip-ek inicializálása
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});