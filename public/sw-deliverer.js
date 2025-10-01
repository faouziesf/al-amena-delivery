// Service Worker pour Al-Amena Delivery - Livreur PWA
// Version ultra-optimisée pour le terrain

const CACHE_NAME = 'al-amena-deliverer-v1.0.0';
const API_CACHE = 'al-amena-api-v1.0.0';

// Ressources essentielles à mettre en cache
const ESSENTIAL_CACHE = [
  '/deliverer/simple',
  '/manifest-deliverer.json',
  '/images/icons/icon-192x192.png',
  '/images/icons/icon-512x512.png',
  'https://cdn.tailwindcss.com',
  'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js'
];

// Routes API à mettre en cache avec stratégie Network First
const API_ROUTES = [
  '/deliverer/api/simple/pickups',
  '/deliverer/api/simple/deliveries',
  '/deliverer/api/simple/wallet/balance'
];

// Installation du Service Worker
self.addEventListener('install', event => {
  console.log('[SW] Installation...');

  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('[SW] Cache ouvert');
        return cache.addAll(ESSENTIAL_CACHE);
      })
      .then(() => {
        console.log('[SW] Ressources essentielles mises en cache');
        return self.skipWaiting();
      })
  );
});

// Activation du Service Worker
self.addEventListener('activate', event => {
  console.log('[SW] Activation...');

  event.waitUntil(
    caches.keys()
      .then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            if (cacheName !== CACHE_NAME && cacheName !== API_CACHE) {
              console.log('[SW] Suppression ancien cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('[SW] Activé');
        return self.clients.claim();
      })
  );
});

// Gestion des requêtes
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Ignorer les requêtes non-HTTP
  if (!request.url.startsWith('http')) {
    return;
  }

  // Stratégie pour les routes API critiques
  if (isApiRoute(request.url)) {
    event.respondWith(networkFirstWithCache(request));
    return;
  }

  // Stratégie pour les ressources statiques
  if (isStaticResource(request.url)) {
    event.respondWith(cacheFirstWithNetwork(request));
    return;
  }

  // Stratégie par défaut pour les pages
  event.respondWith(networkFirstWithFallback(request));
});

// Vérifie si c'est une route API critique
function isApiRoute(url) {
  return API_ROUTES.some(route => url.includes(route));
}

// Vérifie si c'est une ressource statique
function isStaticResource(url) {
  return url.includes('.css') ||
         url.includes('.js') ||
         url.includes('.png') ||
         url.includes('.jpg') ||
         url.includes('.ico') ||
         url.includes('tailwindcss.com') ||
         url.includes('alpinejs');
}

// Stratégie Network First avec cache de secours
async function networkFirstWithCache(request) {
  try {
    const networkResponse = await fetch(request);

    if (networkResponse.ok) {
      // Mettre en cache la réponse pour usage hors ligne
      const cache = await caches.open(API_CACHE);
      cache.put(request, networkResponse.clone());
    }

    return networkResponse;
  } catch (error) {
    console.log('[SW] Réseau échoué, utilisation du cache:', request.url);
    const cachedResponse = await caches.match(request);

    if (cachedResponse) {
      return cachedResponse;
    }

    // Retourner une réponse d'erreur structurée pour l'API
    return new Response(
      JSON.stringify({
        success: false,
        error: 'Hors ligne - Données non disponibles',
        offline: true
      }),
      {
        status: 503,
        headers: { 'Content-Type': 'application/json' }
      }
    );
  }
}

// Stratégie Cache First avec réseau de secours
async function cacheFirstWithNetwork(request) {
  const cachedResponse = await caches.match(request);

  if (cachedResponse) {
    return cachedResponse;
  }

  try {
    const networkResponse = await fetch(request);

    if (networkResponse.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }

    return networkResponse;
  } catch (error) {
    console.log('[SW] Ressource non disponible:', request.url);

    // Retourner une page d'erreur pour les ressources manquantes
    return new Response('Ressource non disponible hors ligne', {
      status: 503,
      headers: { 'Content-Type': 'text/plain' }
    });
  }
}

// Stratégie Network First avec fallback
async function networkFirstWithFallback(request) {
  try {
    return await fetch(request);
  } catch (error) {
    const cachedResponse = await caches.match(request);

    if (cachedResponse) {
      return cachedResponse;
    }

    // Fallback vers la page principale en mode hors ligne
    if (request.mode === 'navigate') {
      const mainPage = await caches.match('/deliverer/simple');
      if (mainPage) {
        return mainPage;
      }
    }

    return new Response('Page non disponible hors ligne', {
      status: 503,
      headers: { 'Content-Type': 'text/plain' }
    });
  }
}

// Synchronisation en arrière-plan
self.addEventListener('sync', event => {
  console.log('[SW] Synchronisation en arrière-plan:', event.tag);

  if (event.tag === 'background-sync-deliveries') {
    event.waitUntil(syncDeliveryData());
  }
});

// Synchroniser les données de livraison en attente
async function syncDeliveryData() {
  try {
    // Récupérer les données en attente depuis IndexedDB
    const pendingData = await getPendingDeliveryData();

    for (const data of pendingData) {
      try {
        await fetch(data.url, {
          method: data.method,
          headers: data.headers,
          body: data.body
        });

        // Supprimer de la file d'attente après succès
        await removePendingData(data.id);

        console.log('[SW] Données synchronisées:', data.id);
      } catch (error) {
        console.log('[SW] Échec sync données:', data.id, error);
      }
    }
  } catch (error) {
    console.log('[SW] Erreur synchronisation:', error);
  }
}

// ===== SYSTÈME INDEXEDDB AVANCÉ POUR OFFLINE =====

const DB_NAME = 'Al-Amena-Deliverer-DB';
const DB_VERSION = 2;

// Initialisation de la base de données IndexedDB
function initDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(DB_NAME, DB_VERSION);

    request.onerror = () => reject(request.error);
    request.onsuccess = () => resolve(request.result);

    request.onupgradeneeded = (event) => {
      const db = event.target.result;

      // Store pour les données en attente de synchronisation
      if (!db.objectStoreNames.contains('pendingSync')) {
        const syncStore = db.createObjectStore('pendingSync', { keyPath: 'id', autoIncrement: true });
        syncStore.createIndex('timestamp', 'timestamp', { unique: false });
        syncStore.createIndex('type', 'type', { unique: false });
      }

      // Store pour les données de livraison offline
      if (!db.objectStoreNames.contains('deliveryData')) {
        const deliveryStore = db.createObjectStore('deliveryData', { keyPath: 'id' });
        deliveryStore.createIndex('status', 'status', { unique: false });
        deliveryStore.createIndex('date', 'date', { unique: false });
      }

      // Store pour les données du portefeuille
      if (!db.objectStoreNames.contains('walletData')) {
        const walletStore = db.createObjectStore('walletData', { keyPath: 'id' });
        walletStore.createIndex('lastUpdate', 'lastUpdate', { unique: false });
      }

      // Store pour les médias (photos, signatures)
      if (!db.objectStoreNames.contains('mediaCache')) {
        const mediaStore = db.createObjectStore('mediaCache', { keyPath: 'id' });
        mediaStore.createIndex('type', 'type', { unique: false });
        mediaStore.createIndex('packageId', 'packageId', { unique: false });
      }

      console.log('[SW] IndexedDB initialisée avec succès');
    };
  });
}

// Récupérer les données en attente de synchronisation
async function getPendingDeliveryData() {
  try {
    const db = await initDB();
    const transaction = db.transaction(['pendingSync'], 'readonly');
    const store = transaction.objectStore('pendingSync');
    const request = store.getAll();

    return new Promise((resolve, reject) => {
      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
    });
  } catch (error) {
    console.error('[SW] Erreur getPendingDeliveryData:', error);
    return [];
  }
}

// Supprimer les données synchronisées
async function removePendingData(id) {
  try {
    const db = await initDB();
    const transaction = db.transaction(['pendingSync'], 'readwrite');
    const store = transaction.objectStore('pendingSync');

    return new Promise((resolve, reject) => {
      const request = store.delete(id);
      request.onsuccess = () => resolve();
      request.onerror = () => reject(request.error);
    });
  } catch (error) {
    console.error('[SW] Erreur removePendingData:', error);
  }
}

// Ajouter des données à synchroniser plus tard
async function addPendingSyncData(data) {
  try {
    const db = await initDB();
    const transaction = db.transaction(['pendingSync'], 'readwrite');
    const store = transaction.objectStore('pendingSync');

    const syncData = {
      ...data,
      timestamp: Date.now(),
      retryCount: 0
    };

    return new Promise((resolve, reject) => {
      const request = store.add(syncData);
      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
    });
  } catch (error) {
    console.error('[SW] Erreur addPendingSyncData:', error);
  }
}

// Sauvegarder les données de livraison pour usage offline
async function saveDeliveryDataOffline(packageId, data) {
  try {
    const db = await initDB();
    const transaction = db.transaction(['deliveryData'], 'readwrite');
    const store = transaction.objectStore('deliveryData');

    const deliveryData = {
      id: packageId,
      ...data,
      lastUpdate: Date.now(),
      offline: true
    };

    return new Promise((resolve, reject) => {
      const request = store.put(deliveryData);
      request.onsuccess = () => resolve();
      request.onerror = () => reject(request.error);
    });
  } catch (error) {
    console.error('[SW] Erreur saveDeliveryDataOffline:', error);
  }
}

// Récupérer les données de livraison offline
async function getDeliveryDataOffline(packageId) {
  try {
    const db = await initDB();
    const transaction = db.transaction(['deliveryData'], 'readonly');
    const store = transaction.objectStore('deliveryData');

    return new Promise((resolve, reject) => {
      const request = store.get(packageId);
      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
    });
  } catch (error) {
    console.error('[SW] Erreur getDeliveryDataOffline:', error);
    return null;
  }
}

// Sauvegarder les médias (photos, signatures) offline
async function saveMediaOffline(packageId, mediaData, type) {
  try {
    const db = await initDB();
    const transaction = db.transaction(['mediaCache'], 'readwrite');
    const store = transaction.objectStore('mediaCache');

    const media = {
      id: `${packageId}-${type}-${Date.now()}`,
      packageId,
      type,
      data: mediaData,
      timestamp: Date.now()
    };

    return new Promise((resolve, reject) => {
      const request = store.add(media);
      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
    });
  } catch (error) {
    console.error('[SW] Erreur saveMediaOffline:', error);
  }
}

// ===== GESTION AVANCÉE DES MESSAGES =====
self.addEventListener('message', async event => {
  const { data } = event;

  console.log('[SW] Message reçu:', data);

  switch (data.type) {
    case 'SKIP_WAITING':
      self.skipWaiting();
      break;

    case 'GET_VERSION':
      event.ports[0].postMessage({ version: CACHE_NAME });
      break;

    case 'SAVE_DELIVERY_OFFLINE':
      await saveDeliveryDataOffline(data.packageId, data.deliveryData);
      event.ports[0].postMessage({ success: true });
      break;

    case 'GET_DELIVERY_OFFLINE':
      const deliveryData = await getDeliveryDataOffline(data.packageId);
      event.ports[0].postMessage({ deliveryData });
      break;

    case 'SAVE_MEDIA_OFFLINE':
      const mediaId = await saveMediaOffline(data.packageId, data.mediaData, data.mediaType);
      event.ports[0].postMessage({ mediaId });
      break;

    case 'QUEUE_SYNC_DATA':
      await addPendingSyncData(data.syncData);
      // Déclencher une sync immédiate si possible
      if ('serviceWorker' in self.registration) {
        try {
          await self.registration.sync.register('background-sync-deliveries');
        } catch (error) {
          console.log('[SW] Sync registration failed:', error);
        }
      }
      event.ports[0].postMessage({ success: true });
      break;

    case 'GET_OFFLINE_STATUS':
      const pendingData = await getPendingDeliveryData();
      event.ports[0].postMessage({
        offline: !navigator.onLine,
        pendingSync: pendingData.length,
        cacheVersion: CACHE_NAME
      });
      break;

    case 'CLEAR_OFFLINE_DATA':
      await clearOfflineData();
      event.ports[0].postMessage({ success: true });
      break;

    default:
      console.log('[SW] Type de message non géré:', data.type);
  }
});

// Nettoyer les données offline
async function clearOfflineData() {
  try {
    const db = await initDB();
    const transaction = db.transaction(['deliveryData', 'mediaCache', 'pendingSync'], 'readwrite');

    await Promise.all([
      transaction.objectStore('deliveryData').clear(),
      transaction.objectStore('mediaCache').clear(),
      transaction.objectStore('pendingSync').clear()
    ]);

    console.log('[SW] Données offline nettoyées');
  } catch (error) {
    console.error('[SW] Erreur clearOfflineData:', error);
  }
}

console.log('[SW] Service Worker Al-Amena Deliverer chargé');