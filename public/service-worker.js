// Service Worker - Cache Agressif pour Performance Maximale
const CACHE_NAME = 'al-amena-v1';
const CACHE_URLS = [
    '/deliverer/tournee',
    '/deliverer/scan',
    '/deliverer/wallet',
    '/deliverer/menu',
    '/manifest.json',
    '/icon-192.png',
    '/icon-512.png',
    'https://cdn.tailwindcss.com',
    'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js',
    'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js'
];

// Installation - Précharge tout
self.addEventListener('install', (event) => {
    console.log('[SW] Installation...');
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('[SW] Cache ouvert, préchargement...');
            return cache.addAll(CACHE_URLS.map(url => new Request(url, {cache: 'reload'})));
        }).then(() => {
            console.log('[SW] Préchargement terminé');
            return self.skipWaiting();
        })
    );
});

// Activation - Nettoie ancien cache
self.addEventListener('activate', (event) => {
    console.log('[SW] Activation...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('[SW] Suppression cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => {
            console.log('[SW] Activation terminée');
            return self.clients.claim();
        })
    );
});

// Fetch - Cache First (ultra-rapide)
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);
    
    // Ignorer requêtes non-GET
    if (event.request.method !== 'GET') {
        return;
    }
    
    // Ignorer API calls (POST data)
    if (url.pathname.includes('/api/') || url.pathname.includes('/scan/submit')) {
        return;
    }
    
    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                // Cache hit - retour immédiat
                console.log('[SW] Cache HIT:', url.pathname);
                
                // Update cache en arrière-plan
                fetch(event.request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, networkResponse.clone());
                        });
                    }
                }).catch(() => {});
                
                return cachedResponse;
            }
            
            // Cache miss - fetch + cache
            console.log('[SW] Cache MISS:', url.pathname);
            return fetch(event.request).then((networkResponse) => {
                if (!networkResponse || networkResponse.status !== 200) {
                    return networkResponse;
                }
                
                const responseToCache = networkResponse.clone();
                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, responseToCache);
                });
                
                return networkResponse;
            }).catch(() => {
                // Offline - retourner page offline si disponible
                if (event.request.mode === 'navigate') {
                    return caches.match('/deliverer/tournee');
                }
            });
        })
    );
});

// Messages depuis clients
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'CLEAR_CACHE') {
        caches.delete(CACHE_NAME).then(() => {
            console.log('[SW] Cache vidé');
        });
    }
});

console.log('[SW] Service Worker chargé');
