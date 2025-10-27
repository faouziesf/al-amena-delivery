// ==================== UTILITAIRES ====================

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
}

function showToast(message, type = 'success') {
    const bgColor = type === 'success' ? 'bg-green-500' : 
                    type === 'error' ? 'bg-red-500' : 
                    type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';
    
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-slide-in`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('animate-fade-out');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ==================== MODALES ====================

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function showGenerateModal() {
    showModal('generateModal');
}

function showRegenerateModal() {
    showModal('regenerateModal');
}

function showDeleteModal() {
    showModal('deleteModal');
}

function closeSuccessModal() {
    closeModal('successModal');
    location.reload();
}

// ==================== TOKEN VISIBILITY ====================

function toggleVisibility() {
    const input = document.getElementById('apiToken');
    const icon = document.getElementById('eyeIcon');
    
    if (!input) return;
    
    if (input.type === 'password') {
        input.type = 'text';
        // Changer l'icône en œil barré
        icon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
        `;
    } else {
        input.type = 'password';
        // Remettre l'icône œil normal
        icon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        `;
    }
}

// ==================== COPIER TOKEN ====================

function copyToken() {
    const input = document.getElementById('apiToken');
    if (!input) return;
    
    const wasPassword = input.type === 'password';
    
    // Révéler temporairement pour copier
    if (wasPassword) {
        input.type = 'text';
    }
    
    input.select();
    input.setSelectionRange(0, 99999); // Pour mobile
    
    try {
        document.execCommand('copy');
        showToast('✅ Token copié dans le presse-papier', 'success');
    } catch (err) {
        // Fallback pour navigateurs modernes
        navigator.clipboard.writeText(input.value).then(() => {
            showToast('✅ Token copié dans le presse-papier', 'success');
        }).catch(() => {
            showToast('❌ Erreur lors de la copie', 'error');
        });
    }
    
    // Remasquer si c'était masqué
    if (wasPassword) {
        input.type = 'password';
    }
}

function copyNewToken() {
    const input = document.getElementById('newToken');
    if (!input) return;
    
    input.select();
    input.setSelectionRange(0, 99999);
    
    try {
        document.execCommand('copy');
        showToast('✅ Token copié !', 'success');
    } catch (err) {
        navigator.clipboard.writeText(input.value).then(() => {
            showToast('✅ Token copié !', 'success');
        }).catch(() => {
            showToast('❌ Erreur lors de la copie', 'error');
        });
    }
}

// ==================== GÉNÉRER TOKEN ====================

async function confirmGenerate() {
    try {
        const response = await fetch('/client/settings/api/token/generate', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeModal('generateModal');
            document.getElementById('newToken').value = data.token;
            showModal('successModal');
        } else {
            showToast('❌ ' + (data.message || 'Erreur lors de la génération'), 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showToast('❌ Erreur lors de la génération du token', 'error');
    }
}

// ==================== RÉGÉNÉRER TOKEN ====================

async function confirmRegenerate() {
    const confirmed = document.getElementById('confirmRegenerate')?.checked;
    
    if (!confirmed) {
        showToast('⚠️ Veuillez confirmer en cochant la case', 'warning');
        return;
    }
    
    try {
        const response = await fetch('/client/settings/api/token/regenerate', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeModal('regenerateModal');
            document.getElementById('newToken').value = data.token;
            showModal('successModal');
        } else {
            showToast('❌ ' + (data.message || 'Erreur lors de la régénération'), 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showToast('❌ Erreur lors de la régénération du token', 'error');
    }
}

// ==================== SUPPRIMER TOKEN ====================

async function confirmDelete() {
    try {
        const response = await fetch('/client/settings/api/token', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('✅ Token supprimé avec succès', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('❌ ' + (data.message || 'Erreur lors de la suppression'), 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showToast('❌ Erreur lors de la suppression du token', 'error');
    }
}

// ==================== EVENT LISTENERS ====================

document.addEventListener('DOMContentLoaded', function() {
    // Enable/disable bouton régénérer selon checkbox
    const confirmCheck = document.getElementById('confirmRegenerate');
    const confirmBtn = document.getElementById('confirmRegenerateBtn');
    
    if (confirmCheck && confirmBtn) {
        confirmCheck.addEventListener('change', function() {
            confirmBtn.disabled = !this.checked;
        });
    }
    
    // Fermer modales en cliquant en dehors
    const modals = document.querySelectorAll('[id$="Modal"]');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
    
    // Fermer modales avec Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            modals.forEach(modal => {
                if (!modal.classList.contains('hidden')) {
                    closeModal(modal.id);
                }
            });
        }
    });
});
