// Al-Amena Client Service Worker
// Version 2.0.0 - Optimisé pour PWA Offline

const CACHE_NAME = 'alamena-client-v2.0.0';
const API_CACHE_NAME = 'alamena-client-api-v2.0.0';
const STATIC_CACHE_NAME = 'alamena-client-static-v2.0.0';
const DB_NAME = 'Al-Amena-Client-DB';
const DB_VERSION = 2;

// Fichiers critiques à mettre en cache
const CRITICAL_CACHE = [
    '/client/dashboard',
    '/client/packages',
    '/client/packages/index',
    '/client/packages/create',
    '/client/packages/create-fast',
    '/client/wallet',
    '/client/wallet/index',
    '/client/wallet/topup',
    '/client/manifests',
    '/client/manifests/index',
    '/client/manifests/create',
    '/client/pickup-requests',
    '/client/notifications',
    '/client/profile',
    '/manifest-client.json',
    'https://cdn.tailwindcss.com',
    'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js'
];

// APIs critiques à mettre en cache
const API_CACHE_URLS = [
    '/client/api/dashboard-stats',
    '/client/api/wallet-balance',
    '/client/api/notifications/unread-count',
    '/client/api/notifications/recent',
    '/client/api/packages/stats',
    '/client/api/packages/recent'
];

// URLs à ne jamais mettre en cache
const NEVER_CACHE = [
    '/logout',
    '/login',
    '/csrf-token',
    '/client/api/location/update',
    '/client/api/live-updates'
];

// ===== SYSTÈME INDEXEDDB POUR CLIENT =====

// Initialisation IndexedDB
function initClientDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(DB_NAME, DB_VERSION);

    request.onerror = () => reject(request.error);
    request.onsuccess = () => resolve(request.result);

    request.onupgradeneeded = (event) => {
      const db = event.target.result;

      // Store pour les données de packages
      if (!db.objectStoreNames.contains('packages')) {
        const packagesStore = db.createObjectStore('packages', { keyPath: 'id' });
        packagesStore.createIndex('status', 'status', { unique: false });
        packagesStore.createIndex('createdAt', 'created_at', { unique: false });
      }

      // Store pour les données de portefeuille
      if (!db.objectStoreNames.contains('wallet')) {
        const walletStore = db.createObjectStore('wallet', { keyPath: 'id' });
        walletStore.createIndex('lastUpdate', 'lastUpdate', { unique: false });
      }

      // Store pour les brouillons
      if (!db.objectStoreNames.contains('drafts')) {
        const draftsStore = db.createObjectStore('drafts', { keyPath: 'id', autoIncrement: true });
        draftsStore.createIndex('type', 'type', { unique: false });
        draftsStore.createIndex('timestamp', 'timestamp', { unique: false });
      }

      // Store pour les actions en attente
      if (!db.objectStoreNames.contains('pendingActions')) {
        const actionsStore = db.createObjectStore('pendingActions', { keyPath: 'id', autoIncrement: true });
        actionsStore.createIndex('type', 'type', { unique: false });
        actionsStore.createIndex('timestamp', 'timestamp', { unique: false });
      }

      console.log('[SW Client] IndexedDB initialisée');
    };
  });
}

// Installation du Service Worker
self.addEventListener('install', event => {
    console.log('[SW Client] Installation v2.0.0...');

    event.waitUntil(
        Promise.all([
            // Cache des fichiers critiques
            caches.open(CACHE_NAME).then(cache => {
                console.log('[SW Client] Mise en cache des fichiers critiques');
                return cache.addAll(CRITICAL_CACHE);
            }),
            // Pre-cache des APIs importantes
            caches.open(API_CACHE_NAME).then(cache => {
                console.log('[SW Client] Pre-cache des APIs');
                return Promise.allSettled(
                    API_CACHE_URLS.map(url =>
                        fetch(url).then(response => {
                            if (response.ok) {
                                return cache.put(url, response);
                            }
                        }).catch(() => {})
                    )
                );
            })
        ]).then(() => {
            console.log('[SW Client] Installation terminée');
            // Passer immédiatement à l'activation
            return self.skipWaiting();
        })
    );
});

// Activation du Service Worker
self.addEventListener('activate', event => {
    console.log('[SW Client] Activation en cours...');

    event.waitUntil(
        Promise.all([
            // Nettoyage des anciens caches
            caches.keys().then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName.startsWith('alamena-client-') &&
                            cacheName !== CACHE_NAME &&
                            cacheName !== API_CACHE_NAME) {
                            console.log('[SW Client] Suppression ancien cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            }),
            // Prendre le contrôle immédiatement
            self.clients.claim()
        ]).then(() => {
            console.log('[SW Client] Activation terminée');
        })
    );
});

// Interception des requêtes
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Ignorer les requêtes non-GET
    if (event.request.method !== 'GET') {
        return;
    }

    // Ignorer les URLs à ne jamais mettre en cache
    if (NEVER_CACHE.some(path => url.pathname.startsWith(path))) {
        return;
    }

    // Stratégies de cache selon le type de ressource
    if (url.pathname.startsWith('/client/api/')) {
        // API: Network First avec fallback cache
        event.respondWith(handleAPIRequest(event.request));
    } else if (url.pathname.startsWith('/client/')) {
        // Pages client: Cache First avec fallback network
        event.respondWith(handlePageRequest(event.request));
    } else if (url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff2?)$/)) {
        // Assets statiques: Cache First
        event.respondWith(handleAssetRequest(event.request));
    }
});

// Gestion des requêtes API (Network First)
async function handleAPIRequest(request) {
    const cache = await caches.open(API_CACHE_NAME);

    try {
        // Essayer le réseau d'abord
        const networkResponse = await fetch(request);

        if (networkResponse.ok) {
            // Mettre en cache la réponse fraîche
            cache.put(request, networkResponse.clone());
            return networkResponse;
        }

        // Si la réponse réseau n'est pas OK, utiliser le cache
        const cachedResponse = await cache.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }

        return networkResponse;
    } catch (error) {
        console.log('[SW Client] Erreur réseau API, utilisation du cache:', error);

        // Utiliser le cache en cas d'erreur réseau
        const cachedResponse = await cache.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }

        // Retourner une réponse par défaut pour les APIs critiques
        if (request.url.includes('/api/dashboard-stats')) {
            return new Response(JSON.stringify({
                packages_count: 0,
                wallet_balance: 0,
                pending_topups: 0,
                active_complaints: 0
            }), {
                headers: { 'Content-Type': 'application/json' }
            });
        }

        throw error;
    }
}

// Gestion des pages (Cache First)
async function handlePageRequest(request) {
    const cache = await caches.open(CACHE_NAME);

    try {
        // Vérifier le cache d'abord
        const cachedResponse = await cache.match(request);
        if (cachedResponse) {
            // Mettre à jour en arrière-plan
            updateCacheInBackground(request, cache);
            return cachedResponse;
        }

        // Si pas en cache, essayer le réseau
        const networkResponse = await fetch(request);

        if (networkResponse.ok) {
            // Mettre en cache la nouvelle réponse
            cache.put(request, networkResponse.clone());
            return networkResponse;
        }

        return networkResponse;
    } catch (error) {
        console.log('[SW Client] Erreur réseau page, fallback offline:', error);

        // En cas d'erreur, essayer la page offline
        const offlineResponse = await cache.match(OFFLINE_PAGE);
        if (offlineResponse) {
            return offlineResponse;
        }

        throw error;
    }
}

// Gestion des assets statiques (Cache First)
async function handleAssetRequest(request) {
    const cache = await caches.open(CACHE_NAME);

    const cachedResponse = await cache.match(request);
    if (cachedResponse) {
        return cachedResponse;
    }

    try {
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        console.log('[SW Client] Erreur asset:', error);
        throw error;
    }
}

// Mise à jour du cache en arrière-plan
async function updateCacheInBackground(request, cache) {
    try {
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            await cache.put(request, networkResponse);
        }
    } catch (error) {
        console.log('[SW Client] Erreur mise à jour arrière-plan:', error);
    }
}

// Gestion des notifications push
self.addEventListener('push', event => {
    if (!event.data) return;

    try {
        const data = event.data.json();
        const options = {
            body: data.body || 'Nouvelle notification',
            icon: data.icon || '/images/icons/client-icon-192x192.png',
            badge: '/images/icons/badge-72x72.png',
            vibrate: data.vibrate || [200, 100, 200],
            data: data.data || {},
            actions: data.actions || [],
            requireInteraction: data.requireInteraction || false,
            tag: data.tag || 'alamena-client-notification'
        };

        event.waitUntil(
            self.registration.showNotification(
                data.title || 'Al-Amena Client',
                options
            )
        );
    } catch (error) {
        console.error('[SW Client] Erreur notification push:', error);
    }
});

// Gestion des clics sur notifications
self.addEventListener('notificationclick', event => {
    event.notification.close();

    const data = event.notification.data;
    let url = '/client/dashboard';

    // Déterminer l'URL selon le type de notification
    if (data.type === 'package_status') {
        url = `/client/packages/${data.package_id}`;
    } else if (data.type === 'wallet_low') {
        url = '/client/topup';
    } else if (data.type === 'support_response') {
        url = `/client/tickets/${data.ticket_id}`;
    } else if (data.url) {
        url = data.url;
    }

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clientList => {
            // Vérifier si l'app est déjà ouverte
            for (const client of clientList) {
                if (client.url.includes('/client/') && 'focus' in client) {
                    client.postMessage({
                        type: 'NAVIGATE_TO',
                        url: url
                    });
                    return client.focus();
                }
            }

            // Ouvrir une nouvelle fenêtre si l'app n'est pas ouverte
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});

// Synchronisation en arrière-plan
self.addEventListener('sync', event => {
    if (event.tag === 'client-background-sync') {
        event.waitUntil(performBackgroundSync());
    }
});

// Effectuer la synchronisation en arrière-plan
async function performBackgroundSync() {
    try {
        console.log('[SW Client] Synchronisation en arrière-plan...');

        // Synchroniser les données critiques
        const syncPromises = API_CACHE_URLS.map(async url => {
            try {
                const response = await fetch(url);
                if (response.ok) {
                    const cache = await caches.open(API_CACHE_NAME);
                    await cache.put(url, response);
                }
            } catch (error) {
                console.log(`[SW Client] Erreur sync ${url}:`, error);
            }
        });

        await Promise.allSettled(syncPromises);
        console.log('[SW Client] Synchronisation terminée');

        // Notifier les clients connectés
        const clients = await self.clients.matchAll();
        clients.forEach(client => {
            client.postMessage({
                type: 'SYNC_COMPLETE',
                timestamp: Date.now()
            });
        });

    } catch (error) {
        console.error('[SW Client] Erreur synchronisation:', error);
    }
}

// Messages des clients
self.addEventListener('message', event => {
    const { type, data } = event.data;

    switch (type) {
        case 'SKIP_WAITING':
            self.skipWaiting();
            break;

        case 'GET_VERSION':
            event.ports[0].postMessage({ version: CACHE_NAME });
            break;

        case 'CLEAR_CACHE':
            clearAllCaches().then(() => {
                event.ports[0].postMessage({ success: true });
            });
            break;

        case 'FORCE_UPDATE':
            forceCacheUpdate().then(() => {
                event.ports[0].postMessage({ success: true });
            });
            break;
    }
});

// Effacer tous les caches
async function clearAllCaches() {
    const cacheNames = await caches.keys();
    await Promise.all(
        cacheNames
            .filter(name => name.startsWith('alamena-client-'))
            .map(name => caches.delete(name))
    );
}

// Forcer la mise à jour du cache
async function forceCacheUpdate() {
    const cache = await caches.open(CACHE_NAME);
    const apiCache = await caches.open(API_CACHE_NAME);

    // Supprimer et recharger les fichiers critiques
    await Promise.all(CRITICAL_CACHE.map(url => cache.delete(url)));
    await cache.addAll(CRITICAL_CACHE);

    // Recharger les APIs
    await Promise.all(API_CACHE_URLS.map(async url => {
        await apiCache.delete(url);
        try {
            const response = await fetch(url);
            if (response.ok) {
                await apiCache.put(url, response);
            }
        } catch (error) {
            console.log(`[SW Client] Erreur rechargement ${url}:`, error);
        }
    }));
}

console.log('[SW Client] Service Worker client chargé');