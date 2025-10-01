// ===== GESTIONNAIRE OFFLINE POUR CLIENTS =====
// Al-Amena Delivery - Système offline client

class ClientOfflineManager {
  constructor() {
    this.sw = null;
    this.isOnline = navigator.onLine;
    this.pendingDrafts = 0;
    this.lastSync = null;
    this.init();
  }

  async init() {
    console.log('[Client Offline] Initialisation...');

    // Enregistrer le service worker
    if ('serviceWorker' in navigator) {
      try {
        const registration = await navigator.serviceWorker.register('/sw-client.js');
        console.log('[Client Offline] Service Worker enregistré');

        this.sw = registration.active || registration.waiting || registration.installing;

        // Gérer les mises à jour
        registration.addEventListener('updatefound', () => {
          const newWorker = registration.installing;
          newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
              this.showUpdateNotification();
            }
          });
        });

        // Configuration des listeners réseau
        this.setupNetworkListeners();

        // Initialisation de l'interface
        await this.initOfflineInterface();

      } catch (error) {
        console.error('[Client Offline] Erreur Service Worker:', error);
      }
    }
  }

  setupNetworkListeners() {
    window.addEventListener('online', () => {
      this.isOnline = true;
      this.showToast('Connexion rétablie - Synchronisation...', 'success');
      this.syncPendingData();
      this.updateOfflineStatus();
    });

    window.addEventListener('offline', () => {
      this.isOnline = false;
      this.showToast('Mode hors ligne activé', 'warning');
      this.updateOfflineStatus();
    });
  }

  async initOfflineInterface() {
    // Ajouter l'indicateur de statut offline
    this.createOfflineIndicator();

    // Charger les brouillons existants
    await this.loadDrafts();

    // Mettre à jour le statut
    this.updateOfflineStatus();
  }

  // ===== COMMUNICATION AVEC LE SERVICE WORKER =====

  async sendMessage(message) {
    if (!this.sw) return null;

    return new Promise((resolve) => {
      const messageChannel = new MessageChannel();
      messageChannel.port1.onmessage = (event) => {
        resolve(event.data);
      };

      this.sw.postMessage(message, [messageChannel.port2]);
    });
  }

  // ===== GESTION DES BROUILLONS OFFLINE =====

  async saveDraft(type, data) {
    try {
      const draft = {
        type,
        data,
        timestamp: Date.now(),
        offline: true
      };

      const result = await this.sendMessage({
        type: 'SAVE_DRAFT',
        draft
      });

      this.pendingDrafts++;
      this.updateOfflineStatus();

      this.showToast(`${this.getDraftTypeName(type)} sauvegardé(e) en brouillon`, 'info');

      return result?.draftId;

    } catch (error) {
      console.error('[Client Offline] Erreur saveDraft:', error);
      this.showToast('Erreur sauvegarde brouillon', 'error');
    }
  }

  async loadDrafts() {
    try {
      const result = await this.sendMessage({
        type: 'GET_DRAFTS'
      });

      if (result?.drafts) {
        this.pendingDrafts = result.drafts.length;
        this.displayDrafts(result.drafts);
      }

    } catch (error) {
      console.error('[Client Offline] Erreur loadDrafts:', error);
    }
  }

  // ===== GESTION DES COLIS OFFLINE =====

  async savePackageDraft(packageData) {
    const enrichedData = {
      ...packageData,
      created_offline: true,
      temp_id: 'temp_' + Date.now(),
      status: 'draft'
    };

    return await this.saveDraft('package', enrichedData);
  }

  async saveManifestDraft(manifestData) {
    const enrichedData = {
      ...manifestData,
      created_offline: true,
      temp_id: 'temp_manifest_' + Date.now()
    };

    return await this.saveDraft('manifest', enrichedData);
  }

  async saveTopupRequest(topupData) {
    const enrichedData = {
      ...topupData,
      created_offline: true,
      temp_id: 'temp_topup_' + Date.now(),
      status: 'pending'
    };

    return await this.saveDraft('topup', enrichedData);
  }

  // ===== SYNCHRONISATION =====

  async syncPendingData() {
    try {
      // Déclencher la synchronisation en arrière-plan
      if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
        const registration = await navigator.serviceWorker.ready;
        await registration.sync.register('client-background-sync');
      }

      this.lastSync = new Date();
      this.updateOfflineStatus();

    } catch (error) {
      console.error('[Client Offline] Erreur syncPendingData:', error);
    }
  }

  async clearDrafts() {
    try {
      await this.sendMessage({
        type: 'CLEAR_DRAFTS'
      });

      this.pendingDrafts = 0;
      this.updateOfflineStatus();
      this.displayDrafts([]);

      this.showToast('Brouillons supprimés', 'success');

    } catch (error) {
      console.error('[Client Offline] Erreur clearDrafts:', error);
    }
  }

  // ===== INTERFACE UTILISATEUR =====

  createOfflineIndicator() {
    const indicator = document.createElement('div');
    indicator.id = 'offline-indicator';
    indicator.className = 'fixed top-20 right-4 z-50 transition-all duration-300';

    document.body.appendChild(indicator);
  }

  updateOfflineStatus() {
    const indicator = document.getElementById('offline-indicator');
    if (!indicator) return;

    if (!this.isOnline) {
      indicator.innerHTML = `
        <div class="bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center space-x-2">
          <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
          <span class="font-medium">Mode Offline</span>
        </div>
      `;
      indicator.classList.remove('hidden');
    } else if (this.pendingDrafts > 0) {
      indicator.innerHTML = `
        <div class="bg-amber-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center space-x-2">
          <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
            <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" class="opacity-75"></path>
          </svg>
          <span class="font-medium">${this.pendingDrafts} brouillon(s)</span>
        </div>
      `;
      indicator.classList.remove('hidden');
    } else {
      indicator.classList.add('hidden');
    }

    // Mettre à jour d'autres éléments de l'interface
    this.updateNetworkIndicators();
  }

  updateNetworkIndicators() {
    // Mise à jour des indicateurs de réseau dans l'interface
    const networkStatus = document.querySelectorAll('[data-network-status]');
    networkStatus.forEach(element => {
      element.className = this.isOnline ?
        'w-3 h-3 bg-green-500 rounded-full animate-pulse' :
        'w-3 h-3 bg-red-500 rounded-full';
    });

    // Désactiver les boutons qui nécessitent une connexion
    const onlineOnlyButtons = document.querySelectorAll('[data-requires-online]');
    onlineOnlyButtons.forEach(button => {
      button.disabled = !this.isOnline;
      if (!this.isOnline) {
        button.classList.add('opacity-50', 'cursor-not-allowed');
        button.title = 'Connexion internet requise';
      } else {
        button.classList.remove('opacity-50', 'cursor-not-allowed');
        button.title = '';
      }
    });
  }

  displayDrafts(drafts) {
    const container = document.getElementById('drafts-container');
    if (!container) return;

    if (drafts.length === 0) {
      container.innerHTML = `
        <div class="text-center py-4 text-gray-500">
          <p>Aucun brouillon</p>
        </div>
      `;
      return;
    }

    container.innerHTML = drafts.map(draft => `
      <div class="bg-white border border-gray-200 rounded-lg p-4 mb-3">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="font-medium text-gray-900">${this.getDraftTypeName(draft.type)}</h4>
            <p class="text-sm text-gray-600">${this.formatDate(draft.timestamp)}</p>
          </div>
          <div class="flex space-x-2">
            <button onclick="clientOffline.editDraft(${draft.id})"
                    class="text-blue-600 hover:text-blue-800 text-sm">
              Éditer
            </button>
            <button onclick="clientOffline.deleteDraft(${draft.id})"
                    class="text-red-600 hover:text-red-800 text-sm">
              Supprimer
            </button>
          </div>
        </div>
      </div>
    `).join('');
  }

  showUpdateNotification() {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-blue-500 text-white p-4 rounded-lg shadow-lg z-50';
    notification.innerHTML = `
      <div class="flex items-center space-x-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        <div>
          <p class="font-semibold">Nouvelle version disponible</p>
          <p class="text-sm opacity-90">Actualiser pour appliquer</p>
        </div>
        <button onclick="window.location.reload()" class="bg-white text-blue-500 px-3 py-1 rounded font-medium">
          Actualiser
        </button>
      </div>
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
      if (notification.parentNode) {
        notification.remove();
      }
    }, 10000);
  }

  showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const bgColor = {
      success: 'bg-green-500',
      warning: 'bg-amber-500',
      error: 'bg-red-500',
      info: 'bg-blue-500'
    }[type] || 'bg-gray-500';

    toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-4 py-2 rounded-lg shadow-lg z-50 transform transition-all duration-300`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(20px)';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  // ===== UTILITAIRES =====

  getDraftTypeName(type) {
    const names = {
      package: 'Nouveau Colis',
      manifest: 'Manifeste',
      topup: 'Demande de Recharge',
      pickup: 'Demande de Collecte'
    };
    return names[type] || type;
  }

  formatDate(timestamp) {
    return new Date(timestamp).toLocaleString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  // ===== API PUBLIQUE =====

  canWorkOffline() {
    return 'serviceWorker' in navigator && 'indexedDB' in window;
  }

  isOffline() {
    return !this.isOnline;
  }

  async getOfflineStats() {
    return {
      isOnline: this.isOnline,
      pendingDrafts: this.pendingDrafts,
      lastSync: this.lastSync,
      canWorkOffline: this.canWorkOffline()
    };
  }

  // Méthodes pour gérer les brouillons individuels
  async editDraft(draftId) {
    // À implémenter selon les besoins spécifiques
    this.showToast('Fonctionnalité en développement', 'info');
  }

  async deleteDraft(draftId) {
    if (confirm('Supprimer ce brouillon ?')) {
      try {
        await this.sendMessage({
          type: 'DELETE_DRAFT',
          draftId
        });

        this.pendingDrafts = Math.max(0, this.pendingDrafts - 1);
        this.updateOfflineStatus();
        await this.loadDrafts();

        this.showToast('Brouillon supprimé', 'success');

      } catch (error) {
        console.error('[Client Offline] Erreur deleteDraft:', error);
        this.showToast('Erreur suppression', 'error');
      }
    }
  }
}

// Instance globale
window.clientOffline = new ClientOfflineManager();

// API simplifiée pour l'utilisation dans les vues
window.savePackageOffline = (data) => window.clientOffline.savePackageDraft(data);
window.saveManifestOffline = (data) => window.clientOffline.saveManifestDraft(data);
window.saveTopupOffline = (data) => window.clientOffline.saveTopupRequest(data);

console.log('[Client Offline] Gestionnaire initialisé');