// Al-Amena Livreur Service Worker
// Version: 1.0.0

const CACHE_NAME = 'alamena-deliverer-v1.0.0';
const OFFLINE_CACHE = 'alamena-offline-v1.0.0';
const DYNAMIC_CACHE = 'alamena-dynamic-v1.0.0';

// Fichiers essentiels à mettre en cache lors de l'installation
const ESSENTIAL_CACHE = [
  '/deliverer/dashboard',
  '/deliverer/pickups/available',
  '/deliverer/pickups/mine',
  '/deliverer/deliveries',
  '/deliverer/returns',
  '/deliverer/payments',
  '/deliverer/wallet',
  '/deliverer/offline',
  '/css/app.css',
  '/js/app.js',
  '/images/icons/icon-192x192.png',
  '/images/offline-placeholder.png',
  '/manifest.json'
];

// APIs critiques à mettre en cache
const API_CACHE = [
  '/deliverer/api/dashboard-stats',
  '/deliverer/api/wallet/balance',
  '/deliverer/api/notifications/unread-count'
];

// Patterns d'URLs à mettre en cache dynamiquement
const DYNAMIC_CACHE_PATTERNS = [
  /^\/deliverer\/packages\/\d+$/,
  /^\/deliverer\/payments\/\d+$/,
  /^\/deliverer\/api\//
];

// URLs qui ne doivent JAMAIS être mises en cache
const NEVER_CACHE = [
  '/deliverer/packages/scan',
  '/deliverer/api/location/update',
  '/logout',
  '/login'
];

// ==================== INSTALLATION ====================

self.addEventListener('install', event => {
  console.log('[SW] Installation Service Worker...');
  
  event.waitUntil(
    Promise.all([
      // Cache essentiel
      caches.open(CACHE_NAME).then(cache => {
        console.log('[SW] Mise en cache des fichiers essentiels...');
        return cache.addAll(ESSENTIAL_CACHE.map(url => new Request(url, { cache: 'reload' })));
      }),
      
      // Cache API
      caches.open(DYNAMIC_CACHE).then(cache => {
        console.log('[SW] Pré-cache des APIs critiques...');
        return Promise.allSettled(
          API_CACHE.map(url => 
            fetch(url, { credentials: 'include' })
              .then(response => response.ok ? cache.put(url, response.clone()) : null)
              .catch(() => null)
          )
        );
      })
    ]).then(() => {
      console.log('[SW] Installation terminée');
      return self.skipWaiting();
    })
  );
});

// ==================== ACTIVATION ====================

self.addEventListener('activate', event => {
  console.log('[SW] Activation Service Worker...');
  
  event.waitUntil(
    Promise.all([
      // Nettoyer les anciens caches
      caches.keys().then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            if (cacheName !== CACHE_NAME && 
                cacheName !== OFFLINE_CACHE && 
                cacheName !== DYNAMIC_CACHE) {
              console.log('[SW] Suppression ancien cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      }),
      
      // Initialiser la base IndexedDB pour le stockage offline
      initializeOfflineStorage()
    ]).then(() => {
      console.log('[SW] Activation terminée');
      return self.clients.claim();
    })
  );
});

// ==================== INTERCEPTION DES REQUÊTES ====================

self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Ignorer les requêtes non-HTTP
  if (!request.url.startsWith('http')) return;
  
  // Ignorer les URLs à ne jamais mettre en cache
  if (NEVER_CACHE.some(pattern => request.url.includes(pattern))) {
    event.respondWith(fetch(request));
    return;
  }
  
  // Stratégies différentes selon le type de requête
  if (request.method === 'GET') {
    if (url.pathname.startsWith('/deliverer/api/')) {
      event.respondWith(handleApiRequest(request));
    } else if (url.pathname.startsWith('/deliverer/')) {
      event.respondWith(handlePageRequest(request));
    } else {
      event.respondWith(handleAssetRequest(request));
    }
  } else if (request.method === 'POST') {
    event.respondWith(handlePostRequest(request));
  }
});

// ==================== GESTION DES DIFFÉRENTS TYPES DE REQUÊTES ====================

// Requêtes API - Network First avec fallback cache
async function handleApiRequest(request) {
  try {
    const networkResponse = await fetch(request.clone());
    
    if (networkResponse.ok) {
      // Mettre en cache si succès
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, networkResponse.clone());
      return networkResponse;
    }
    
    throw new Error('Network response not ok');
  } catch (error) {
    console.log('[SW] API offline, tentative cache:', request.url);
    
    // Fallback vers le cache
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    
    // Réponse d'erreur avec structure JSON
    return new Response(JSON.stringify({
      success: false,
      message: 'Mode hors ligne - Données non disponibles',
      offline: true,
      timestamp: new Date().toISOString()
    }), {
      status: 503,
      headers: { 'Content-Type': 'application/json' }
    });
  }
}

// Requêtes de pages - Cache First avec mise à jour en arrière-plan
async function handlePageRequest(request) {
  try {
    const cachedResponse = await caches.match(request);
    
    if (cachedResponse) {
      // Mise à jour en arrière-plan
      fetch(request).then(response => {
        if (response.ok) {
          caches.open(CACHE_NAME).then(cache => cache.put(request, response));
        }
      }).catch(() => {});
      
      return cachedResponse;
    }
    
    // Pas en cache, essayer le réseau
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
      return networkResponse;
    }
    
    throw new Error('Network error');
  } catch (error) {
    console.log('[SW] Page offline:', request.url);
    
    // Page offline par défaut
    return caches.match('/deliverer/offline') || new Response(`
      <!DOCTYPE html>
      <html><head><title>Hors ligne</title></head>
      <body style="font-family:Arial,sans-serif;padding:20px;text-align:center;">
        <h1>🚫 Mode hors ligne</h1>
        <p>Cette page n'est pas disponible hors ligne.</p>
        <button onclick="window.history.back()">← Retour</button>
        <button onclick="location.reload()">🔄 Réessayer</button>
      </body></html>
    `, { headers: { 'Content-Type': 'text/html' } });
  }
}

// Assets - Cache First
async function handleAssetRequest(request) {
  const cachedResponse = await caches.match(request);
  if (cachedResponse) return cachedResponse;
  
  try {
    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (error) {
    // Asset de fallback si nécessaire
    return new Response('', { status: 404 });
  }
}

// Requêtes POST - Gestion avec queue offline
async function handlePostRequest(request) {
  try {
    return await fetch(request);
  } catch (error) {
    console.log('[SW] POST offline, mise en queue:', request.url);
    
    // Stocker la requête pour synchronisation ultérieure
    await storeOfflineAction(request);
    
    return new Response(JSON.stringify({
      success: true,
      message: 'Action mise en queue pour synchronisation',
      queued: true,
      timestamp: new Date().toISOString()
    }), {
      status: 200,
      headers: { 'Content-Type': 'application/json' }
    });
  }
}

// ==================== SYNCHRONISATION EN ARRIÈRE-PLAN ====================

self.addEventListener('sync', event => {
  console.log('[SW] Synchronisation en arrière-plan:', event.tag);
  
  if (event.tag === 'offline-actions-sync') {
    event.waitUntil(syncOfflineActions());
  } else if (event.tag === 'location-sync') {
    event.waitUntil(syncLocation());
  }
});

// ==================== NOTIFICATIONS PUSH ====================

self.addEventListener('push', event => {
  console.log('[SW] Notification push reçue');
  
  const options = {
    body: 'Nouveau pickup disponible!',
    icon: '/images/icons/icon-192x192.png',
    badge: '/images/icons/badge-72x72.png',
    vibrate: [200, 100, 200],
    data: {
      url: '/deliverer/pickups/available'
    },
    actions: [
      {
        action: 'view',
        title: 'Voir',
        icon: '/images/actions/view.png'
      },
      {
        action: 'dismiss',
        title: 'Ignorer'
      }
    ]
  };
  
  if (event.data) {
    try {
      const payload = event.data.json();
      options.body = payload.message || options.body;
      options.data = { ...options.data, ...payload.data };
    } catch (e) {
      console.log('[SW] Erreur parsing notification payload');
    }
  }
  
  event.waitUntil(
    self.registration.showNotification('Al-Amena Livreur', options)
  );
});

// Gestion des clics sur notifications
self.addEventListener('notificationclick', event => {
  console.log('[SW] Clic sur notification');
  
  event.notification.close();
  
  if (event.action === 'dismiss') return;
  
  const url = event.notification.data?.url || '/deliverer/dashboard';
  
  event.waitUntil(
    self.clients.matchAll({ type: 'window' }).then(clients => {
      // Chercher une fenêtre existante
      for (const client of clients) {
        if (client.url.includes('/deliverer/') && 'focus' in client) {
          client.navigate(url);
          return client.focus();
        }
      }
      
      // Ouvrir nouvelle fenêtre
      return self.clients.openWindow(url);
    })
  );
});

// ==================== STOCKAGE OFFLINE ====================

async function initializeOfflineStorage() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('alamena-offline', 1);
    
    request.onerror = () => reject(request.error);
    request.onsuccess = () => resolve(request.result);
    
    request.onupgradeneeded = event => {
      const db = event.target.result;
      
      // Store pour les actions offline
      if (!db.objectStoreNames.contains('actions')) {
        const actionStore = db.createObjectStore('actions', { 
          keyPath: 'id', 
          autoIncrement: true 
        });
        actionStore.createIndex('timestamp', 'timestamp');
        actionStore.createIndex('type', 'type');
      }
      
      // Store pour les données mises en cache
      if (!db.objectStoreNames.contains('cache_data')) {
        const cacheStore = db.createObjectStore('cache_data', { keyPath: 'url' });
        cacheStore.createIndex('timestamp', 'timestamp');
      }
    };
  });
}

async function storeOfflineAction(request) {
  try {
    const db = await initializeOfflineStorage();
    const transaction = db.transaction(['actions'], 'readwrite');
    const store = transaction.objectStore('actions');
    
    const actionData = {
      url: request.url,
      method: request.method,
      headers: Object.fromEntries(request.headers.entries()),
      body: await request.clone().text(),
      timestamp: Date.now(),
      type: getActionType(request.url)
    };
    
    await store.add(actionData);
    
    // Programmer synchronisation
    if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
      await self.registration.sync.register('offline-actions-sync');
    }
  } catch (error) {
    console.error('[SW] Erreur stockage action offline:', error);
  }
}

async function syncOfflineActions() {
  try {
    const db = await initializeOfflineStorage();
    const transaction = db.transaction(['actions'], 'readwrite');
    const store = transaction.objectStore('actions');
    const actions = await store.getAll();
    
    for (const action of actions) {
      try {
        const response = await fetch(action.url, {
          method: action.method,
          headers: action.headers,
          body: action.body,
          credentials: 'include'
        });
        
        if (response.ok) {
          await store.delete(action.id);
          console.log('[SW] Action synchronisée:', action.url);
        }
      } catch (error) {
        console.log('[SW] Échec sync action:', action.url, error);
      }
    }
  } catch (error) {
    console.error('[SW] Erreur synchronisation:', error);
  }
}

async function syncLocation() {
  // Synchroniser les données de géolocalisation si disponibles
  if ('geolocation' in navigator) {
    try {
      const position = await getCurrentPosition();
      await fetch('/deliverer/api/location/update', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': await getCSRFToken()
        },
        body: JSON.stringify({
          latitude: position.coords.latitude,
          longitude: position.coords.longitude,
          accuracy: position.coords.accuracy
        }),
        credentials: 'include'
      });
    } catch (error) {
      console.log('[SW] Erreur sync géolocalisation:', error);
    }
  }
}

// ==================== UTILITAIRES ====================

function getActionType(url) {
  if (url.includes('/scan')) return 'SCAN';
  if (url.includes('/deliver')) return 'DELIVER';
  if (url.includes('/pickup')) return 'PICKUP';
  if (url.includes('/unavailable')) return 'UNAVAILABLE';
  if (url.includes('/return')) return 'RETURN';
  return 'OTHER';
}

function getCurrentPosition() {
  return new Promise((resolve, reject) => {
    navigator.geolocation.getCurrentPosition(resolve, reject, {
      timeout: 10000,
      maximumAge: 300000
    });
  });
}

async function getCSRFToken() {
  try {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : null;
  } catch (error) {
    return null;
  }
}

// ==================== GESTION DES ERREURS ====================

self.addEventListener('error', event => {
  console.error('[SW] Erreur Service Worker:', event.error);
});

self.addEventListener('unhandledrejection', event => {
  console.error('[SW] Promise rejetée:', event.reason);
});

console.log('[SW] Service Worker Al-Amena Livreur chargé ✅');