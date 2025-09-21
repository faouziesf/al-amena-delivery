<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration PWA Al-Amena Livreur
    |--------------------------------------------------------------------------
    |
    | Configuration pour les fonctionnalités Progressive Web App
    | spécifiques à l'application livreur
    |
    */

    'enabled' => env('PWA_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Métadonnées de l'application
    |--------------------------------------------------------------------------
    */

    'manifest' => [
        'name' => env('PWA_NAME', 'Al-Amena Livreur'),
        'short_name' => env('PWA_SHORT_NAME', 'Al-Amena'),
        'description' => 'Application livreur Al-Amena Delivery - Scanner, Livrer, Gérer',
        'start_url' => '/deliverer/dashboard',
        'display' => 'standalone',
        'theme_color' => '#7C3AED',
        'background_color' => '#8B5CF6',
        'orientation' => 'portrait-primary',
        'scope' => '/deliverer/',
        'lang' => 'fr',
        'dir' => 'ltr',
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Worker
    |--------------------------------------------------------------------------
    */

    'service_worker' => [
        'enabled' => env('PWA_SW_ENABLED', true),
        'file' => 'sw.js',
        'scope' => '/deliverer/',
        'cache_name' => 'alamena-deliverer',
        'version' => '1.0.0',
        
        // Stratégies de cache
        'cache_strategies' => [
            'pages' => 'cache_first',      // Pages: cache d'abord
            'api' => 'network_first',      // API: réseau d'abord
            'assets' => 'cache_first',     // Assets: cache d'abord
            'images' => 'cache_first',     // Images: cache d'abord
        ],
        
        // Fichiers à mettre en cache lors de l'installation
        'precache' => [
            '/deliverer/dashboard',
            '/deliverer/pickups/available',
            '/deliverer/pickups/mine',
            '/deliverer/deliveries',
            '/deliverer/returns',
            '/deliverer/payments',
            '/deliverer/wallet',
            '/css/app.css',
            '/js/app.js',
            '/manifest.json',
        ],
        
        // APIs critiques à mettre en cache
        'api_cache' => [
            '/deliverer/api/dashboard-stats',
            '/deliverer/api/wallet/balance',
            '/deliverer/api/notifications/unread-count',
            '/deliverer/api/packages/available-count',
            '/deliverer/api/packages/my-pickups-count',
            '/deliverer/api/packages/deliveries-count',
            '/deliverer/api/packages/returns-count',
            '/deliverer/api/payments-count',
        ],
        
        // URLs à ne jamais mettre en cache
        'never_cache' => [
            '/deliverer/packages/scan',
            '/deliverer/api/location/update',
            '/logout',
            '/login',
            '/csrf-token',
        ],
        
        // Durée de vie du cache (en secondes)
        'cache_ttl' => [
            'pages' => 86400,      // 24 heures
            'api' => 300,          // 5 minutes
            'assets' => 604800,    // 7 jours
            'images' => 2592000,   // 30 jours
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fonctionnalités Offline
    |--------------------------------------------------------------------------
    */

    'offline' => [
        'enabled' => env('PWA_OFFLINE_ENABLED', true),
        
        // Page offline de fallback
        'fallback_page' => '/deliverer/offline',
        
        // Stockage offline (IndexedDB)
        'storage' => [
            'database' => 'alamena-offline',
            'version' => 1,
            'stores' => [
                'actions' => [
                    'keyPath' => 'id',
                    'autoIncrement' => true,
                    'indexes' => ['timestamp', 'type']
                ],
                'cache_data' => [
                    'keyPath' => 'url',
                    'indexes' => ['timestamp']
                ],
                'scan_queue' => [
                    'keyPath' => 'id',
                    'autoIncrement' => true,
                    'indexes' => ['timestamp', 'status']
                ]
            ]
        ],
        
        // Actions supportées en mode offline
        'supported_actions' => [
            'SCAN_PACKAGE',
            'MARK_PICKUP',
            'MARK_DELIVERED',
            'MARK_UNAVAILABLE',
            'MARK_RETURNED',
            'UPDATE_LOCATION',
        ],
        
        // Intervalle de synchronisation (en millisecondes)
        'sync_interval' => 300000, // 5 minutes
        
        // Nombre maximum d'actions en queue
        'max_queue_size' => 1000,
        
        // Durée de rétention des actions en queue (en jours)
        'queue_retention_days' => 7,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications Push
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        'enabled' => env('PWA_NOTIFICATIONS_ENABLED', true),
        
        // Configuration VAPID pour Web Push
        'vapid' => [
            'public_key' => env('VAPID_PUBLIC_KEY'),
            'private_key' => env('VAPID_PRIVATE_KEY'),
            'subject' => env('VAPID_SUBJECT', 'mailto:support@alamena.com'),
        ],
        
        // Types de notifications
        'types' => [
            'new_pickup' => [
                'title' => 'Nouveau pickup disponible',
                'icon' => '/images/notifications/pickup.png',
                'vibrate' => [200, 100, 200],
                'actions' => [
                    ['action' => 'view', 'title' => 'Voir'],
                    ['action' => 'dismiss', 'title' => 'Ignorer']
                ]
            ],
            'urgent_delivery' => [
                'title' => 'Livraison urgente',
                'icon' => '/images/notifications/urgent.png',
                'vibrate' => [300, 100, 300, 100, 300],
                'actions' => [
                    ['action' => 'navigate', 'title' => 'Y aller'],
                    ['action' => 'view', 'title' => 'Détails']
                ]
            ],
            'wallet_high' => [
                'title' => 'Wallet élevé - Vidange recommandée',
                'icon' => '/images/notifications/wallet.png',
                'vibrate' => [200, 100, 200],
                'actions' => [
                    ['action' => 'view_wallet', 'title' => 'Voir wallet'],
                    ['action' => 'dismiss', 'title' => 'Plus tard']
                ]
            ],
        ],
        
        // Paramètres par défaut des notifications
        'defaults' => [
            'icon' => '/images/icons/icon-192x192.png',
            'badge' => '/images/icons/badge-72x72.png',
            'requireInteraction' => false,
            'silent' => false,
            'renotify' => false,
            'tag' => 'alamena-deliverer',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Scanner QR/Code-barres
    |--------------------------------------------------------------------------
    */

    'scanner' => [
        'enabled' => env('PWA_SCANNER_ENABLED', true),
        
        // Configuration caméra
        'camera' => [
            'facing_mode' => 'environment',  // Caméra arrière par défaut
            'width' => ['ideal' => 1280],
            'height' => ['ideal' => 720],
            'auto_focus' => true,
            'torch' => true,  // Support du flash
        ],
        
        // Formats de codes supportés
        'formats' => [
            'qr_code',
            'code_128',
            'code_39',
            'ean_13',
            'ean_8',
            'data_matrix'
        ],
        
        // Configuration scan
        'scan_options' => [
            'try_harder' => true,
            'return_codabar_start_end' => false,
            'assume_gs1' => false,
            'decode_hints' => [
                'TRY_HARDER' => true,
                'PURE_BARCODE' => false,
            ]
        ],
        
        // Timeout du scan (en millisecondes)
        'timeout' => 30000,
        
        // Vibration lors d'un scan réussi
        'vibration' => [200, 100, 200],
        
        // Son lors d'un scan réussi
        'beep' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Géolocalisation
    |--------------------------------------------------------------------------
    */

    'location' => [
        'enabled' => env('PWA_LOCATION_ENABLED', true),
        
        // Options de géolocalisation
        'options' => [
            'enableHighAccuracy' => true,
            'timeout' => 10000,
            'maximumAge' => 300000,  // 5 minutes
        ],
        
        // Intervalles de tracking
        'tracking' => [
            'interval' => 60000,      // Toutes les minutes
            'distance_filter' => 50,  // Minimum 50m de mouvement
        ],
        
        // Stockage des positions
        'storage' => [
            'max_positions' => 1000,
            'retention_hours' => 24,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance et Optimisation
    |--------------------------------------------------------------------------
    */

    'performance' => [
        // Compression des assets
        'compression' => env('PWA_COMPRESSION', true),
        
        // Préchargement des ressources critiques
        'preload' => [
            '/css/app.css',
            '/js/app.js',
            '/images/icons/icon-192x192.png',
        ],
        
        // Lazy loading des images
        'lazy_loading' => true,
        
        // Optimisation des images
        'image_optimization' => [
            'webp' => true,
            'avif' => false,
            'quality' => 85,
        ],
        
        // Bundling et minification
        'minification' => env('APP_ENV') === 'production',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sécurité PWA
    |--------------------------------------------------------------------------
    */

    'security' => [
        // HTTPS obligatoire
        'force_https' => env('PWA_FORCE_HTTPS', true),
        
        // Headers de sécurité
        'headers' => [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ],
        
        // CSP pour Service Worker
        'csp' => [
            'script-src' => "'self' 'unsafe-inline'",
            'worker-src' => "'self'",
            'connect-src' => "'self' wss:",
        ],
        
        // Validation des tokens
        'csrf_protection' => true,
        
        // Chiffrement local des données sensibles
        'encrypt_offline_data' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Développement et Debug
    |--------------------------------------------------------------------------
    */

    'debug' => [
        'enabled' => env('PWA_DEBUG', env('APP_DEBUG', false)),
        
        // Logs Service Worker
        'service_worker_logs' => env('PWA_SW_LOGS', false),
        
        // Inspection des caches
        'cache_inspection' => env('PWA_CACHE_DEBUG', false),
        
        // Simulation mode offline
        'offline_simulation' => env('PWA_OFFLINE_SIM', false),
        
        // Métriques de performance
        'performance_metrics' => env('PWA_PERF_METRICS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Intégrations spécifiques livreur
    |--------------------------------------------------------------------------
    */

    'deliverer' => [
        // Seuils de notification automatique
        'thresholds' => [
            'wallet_high' => 200.000,
            'urgent_delivery_attempts' => 3,
            'low_battery_warning' => 20,
        ],
        
        // Raccourcis clavier
        'shortcuts' => [
            'scan' => 'Ctrl+S',
            'dashboard' => 'Ctrl+D',
            'wallet' => 'Ctrl+W',
            'deliveries' => 'Ctrl+L',
        ],
        
        // Actions rapides dans le manifest
        'quick_actions' => [
            'scanner' => '/deliverer/packages?scan=1',
            'deliveries' => '/deliverer/deliveries',
            'wallet' => '/deliverer/wallet',
            'pickups' => '/deliverer/pickups/available',
        ],
        
        // Configuration offline spécifique
        'offline_config' => [
            'max_scan_queue' => 100,
            'max_delivery_queue' => 50,
            'sync_retry_attempts' => 3,
            'sync_retry_delay' => 5000,  // 5 secondes
        ],
    ],
];