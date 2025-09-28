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

// Fonctions helper pour IndexedDB (à implémenter selon besoins)
function getPendingDeliveryData() {
  // TODO: Implémenter la récupération depuis IndexedDB
  return Promise.resolve([]);
}

function removePendingData(id) {
  // TODO: Implémenter la suppression depuis IndexedDB
  return Promise.resolve();
}

// Gestion des messages du client
self.addEventListener('message', event => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }

  if (event.data && event.data.type === 'GET_VERSION') {
    event.ports[0].postMessage({ version: CACHE_NAME });
  }
});

// Notification de mise à jour disponible
self.addEventListener('message', event => {
  if (event.data.action === 'skipWaiting') {
    self.skipWaiting();
  }
});

console.log('[SW] Service Worker Al-Amena Deliverer chargé');