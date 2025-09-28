// Al-Amena Client Service Worker
// Version 1.0.0

const CACHE_NAME = 'alamena-client-v1.0.0';
const API_CACHE_NAME = 'alamena-client-api-v1.0.0';
const OFFLINE_PAGE = '/client/offline';

// Fichiers critiques à mettre en cache
const CRITICAL_CACHE = [
    '/client/dashboard',
    '/client/packages',
    '/client/packages/create',
    '/client/wallet',
    '/client/topup',
    '/client/complaints',
    '/client/tickets',
    '/css/app.css',
    '/js/app.js',
    '/manifest-client.json',
    OFFLINE_PAGE
];

// APIs à mettre en cache
const API_CACHE_URLS = [
    '/client/api/dashboard-stats',
    '/client/api/wallet/balance',
    '/client/api/notifications/unread-count',
    '/client/api/packages/count',
    '/client/api/topup/pending-count',
    '/client/api/complaints/count',
    '/client/api/tickets/count'
];

// URLs à ne jamais mettre en cache
const NEVER_CACHE = [
    '/logout',
    '/login',
    '/csrf-token',
    '/client/api/location/update'
];

// Installation du Service Worker
self.addEventListener('install', event => {
    console.log('[SW Client] Installation en cours...');

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