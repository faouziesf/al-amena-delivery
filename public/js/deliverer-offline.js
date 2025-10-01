// ===== GESTIONNAIRE OFFLINE POUR LIVREURS =====
// Al-Amena Delivery - Système offline avancé

class DelivererOfflineManager {
  constructor() {
    this.sw = null;
    this.isOnline = navigator.onLine;
    this.pendingSync = 0;
    this.init();
  }

  async init() {
    // Enregistrer le service worker
    if ('serviceWorker' in navigator) {
      try {
        const registration = await navigator.serviceWorker.register('/sw-deliverer.js');
        console.log('[Offline] Service Worker enregistré');

        // Obtenir le service worker actif
        this.sw = registration.active || registration.waiting || registration.installing;

        // Écouter les mises à jour
        registration.addEventListener('updatefound', () => {
          const newWorker = registration.installing;
          newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
              this.showUpdateNotification();
            }
          });
        });

        // Écouter les changements de statut réseau
        this.setupNetworkListeners();

        // Vérifier le statut offline initial
        await this.checkOfflineStatus();

      } catch (error) {
        console.error('[Offline] Erreur Service Worker:', error);
      }
    }
  }

  setupNetworkListeners() {
    window.addEventListener('online', () => {
      this.isOnline = true;
      this.showToast('Connexion rétablie - Synchronisation...', 'success');
      this.triggerSync();
    });

    window.addEventListener('offline', () => {
      this.isOnline = false;
      this.showToast('Mode hors ligne activé', 'warning');
    });
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

  // ===== GESTION DES LIVRAISONS OFFLINE =====

  async saveDeliveryOffline(packageId, deliveryData) {
    try {
      // Sauvegarder localement
      const result = await this.sendMessage({
        type: 'SAVE_DELIVERY_OFFLINE',
        packageId,
        deliveryData
      });

      // Ajouter à la file de synchronisation
      await this.queueSyncData({
        type: 'delivery_update',
        url: `/deliverer/api/packages/${packageId}/status`,
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(deliveryData)
      });

      this.showToast('Livraison sauvegardée (mode offline)', 'info');
      return result;

    } catch (error) {
      console.error('[Offline] Erreur saveDeliveryOffline:', error);
      this.showToast('Erreur sauvegarde offline', 'error');
    }
  }

  async getDeliveryOffline(packageId) {
    try {
      const result = await this.sendMessage({
        type: 'GET_DELIVERY_OFFLINE',
        packageId
      });
      return result.deliveryData;
    } catch (error) {
      console.error('[Offline] Erreur getDeliveryOffline:', error);
      return null;
    }
  }

  // ===== GESTION DES MÉDIAS OFFLINE =====

  async savePhotoOffline(packageId, photoBlob, type = 'delivery_photo') {
    try {
      // Convertir le blob en base64
      const photoData = await this.blobToBase64(photoBlob);

      const result = await this.sendMessage({
        type: 'SAVE_MEDIA_OFFLINE',
        packageId,
        mediaData: photoData,
        mediaType: type
      });

      // Ajouter à la file de synchronisation
      await this.queueSyncData({
        type: 'media_upload',
        url: `/deliverer/api/packages/${packageId}/media`,
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        formData: {
          type,
          photo: photoData
        }
      });

      this.showToast('Photo sauvegardée (mode offline)', 'info');
      return result.mediaId;

    } catch (error) {
      console.error('[Offline] Erreur savePhotoOffline:', error);
      this.showToast('Erreur sauvegarde photo', 'error');
    }
  }

  async saveSignatureOffline(packageId, signatureData) {
    return this.savePhotoOffline(packageId, signatureData, 'signature');
  }

  // ===== GESTION DE LA SYNCHRONISATION =====

  async queueSyncData(syncData) {
    try {
      await this.sendMessage({
        type: 'QUEUE_SYNC_DATA',
        syncData
      });
      this.pendingSync++;
      this.updateSyncStatus();
    } catch (error) {
      console.error('[Offline] Erreur queueSyncData:', error);
    }
  }

  async triggerSync() {
    if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
      try {
        const registration = await navigator.serviceWorker.ready;
        await registration.sync.register('background-sync-deliveries');
      } catch (error) {
        console.log('[Offline] Sync manuel impossible:', error);
      }
    }
  }

  async checkOfflineStatus() {
    try {
      const status = await this.sendMessage({
        type: 'GET_OFFLINE_STATUS'
      });

      if (status) {
        this.pendingSync = status.pendingSync;
        this.updateSyncStatus();
      }
    } catch (error) {
      console.error('[Offline] Erreur checkOfflineStatus:', error);
    }
  }

  // ===== INTERFACE UTILISATEUR =====

  updateSyncStatus() {
    const syncIndicator = document.getElementById('sync-indicator');
    if (syncIndicator) {
      if (this.pendingSync > 0) {
        syncIndicator.innerHTML = `
          <div class="flex items-center space-x-2 text-amber-600">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
              <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" class="opacity-75"></path>
            </svg>
            <span class="text-sm font-medium">${this.pendingSync} en attente</span>
          </div>
        `;
        syncIndicator.classList.remove('hidden');
      } else {
        syncIndicator.classList.add('hidden');
      }
    }

    // Mettre à jour l'indicateur de statut réseau
    const networkStatus = document.getElementById('network-status');
    if (networkStatus) {
      networkStatus.className = this.isOnline ?
        'w-3 h-3 bg-green-500 rounded-full animate-pulse' :
        'w-3 h-3 bg-red-500 rounded-full';
    }
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
          <p class="font-semibold">Mise à jour disponible</p>
          <p class="text-sm opacity-90">Redémarrer pour appliquer</p>
        </div>
        <button onclick="window.location.reload()" class="bg-white text-blue-500 px-3 py-1 rounded font-medium">
          Redémarrer
        </button>
      </div>
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
      notification.remove();
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

  async blobToBase64(blob) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = () => resolve(reader.result);
      reader.onerror = reject;
      reader.readAsDataURL(blob);
    });
  }

  // ===== API PUBLIQUE =====

  async clearOfflineData() {
    try {
      await this.sendMessage({ type: 'CLEAR_OFFLINE_DATA' });
      this.pendingSync = 0;
      this.updateSyncStatus();
      this.showToast('Données offline supprimées', 'success');
    } catch (error) {
      console.error('[Offline] Erreur clearOfflineData:', error);
    }
  }

  // Vérifier si l'application peut fonctionner offline
  canWorkOffline() {
    return 'serviceWorker' in navigator && 'indexedDB' in window;
  }

  // Obtenir les statistiques offline
  async getOfflineStats() {
    return await this.sendMessage({ type: 'GET_OFFLINE_STATUS' });
  }
}

// Instance globale
window.delivererOffline = new DelivererOfflineManager();

// API simplifiée pour l'utilisation dans les vues
window.saveDeliveryOffline = (packageId, data) => window.delivererOffline.saveDeliveryOffline(packageId, data);
window.savePhotoOffline = (packageId, photo) => window.delivererOffline.savePhotoOffline(packageId, photo);
window.saveSignatureOffline = (packageId, signature) => window.delivererOffline.saveSignatureOffline(packageId, signature);

console.log('[Offline] Gestionnaire livreur initialisé');