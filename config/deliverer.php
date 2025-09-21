<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration Livreur Al-Amena Delivery
    |--------------------------------------------------------------------------
    |
    | Configuration spécifique pour le système de livraison
    | Ajustée selon les spécifications du compte livreur
    |
    */

    // ==================== LIVRAISONS & COLIS ====================
    
    'delivery' => [
        'max_attempts' => 3,                           // Maximum 3 tentatives de livraison
        'attempt_interval_hours' => 24,                // Minimum 24h entre tentatives
        'urgent_threshold_attempts' => 3,              // Urgent à partir de 3 tentatives
        'auto_return_after_attempts' => 3,             // Retour auto après 3 tentatives
        'cod_tolerance' => 0.001,                      // Tolérance COD (1 millime)
        'require_exact_cod' => true,                   // COD exact obligatoire
    ],

    // ==================== SCANNER QR & CODES ====================
    
    'scanner' => [
        'timeout_seconds' => 30,                       // Timeout scan caméra
        'retry_attempts' => 3,                         // Tentatives de scan
        'supported_formats' => [
            'QR_CODE', 'CODE_128', 'CODE_39', 'EAN_13'
        ],
        'batch_scan_max' => 50,                        // Max colis par scan batch
        'auto_focus' => true,                          // Auto-focus caméra
        'torch_support' => true,                       // Support flash
        'beep_on_scan' => true,                        // Son lors du scan
    ],

    // ==================== WALLET & FINANCES ====================
    
    'wallet' => [
        'high_balance_threshold' => 200,               // Seuil wallet élevé (200 DT)
        'empty_request_threshold' => 500,              // Demande vidange auto (500 DT)
        'low_balance_warning' => 50,                   // Alerte solde faible
        'transaction_limit_daily' => 5000,             // Limite transactions/jour
        'auto_backup_transactions' => true,            // Backup auto des transactions
    ],

    // ==================== NOTIFICATIONS ====================
    
    'notifications' => [
        'push_enabled' => true,                        // Notifications push
        'sound_enabled' => true,                       // Son des notifications
        'vibration_enabled' => true,                   // Vibration mobile
        'show_on_lock_screen' => true,                 // Afficher sur écran verrouillé
        'urgent_override_silent' => true,              // Forcer son si urgent
        'retention_days' => 30,                        // Conserver 30 jours
    ],

    // ==================== PWA & OFFLINE ====================
    
    'pwa' => [
        'enable_offline_mode' => true,                 // Mode hors ligne
        'offline_sync_interval' => 300,                // Sync toutes les 5 min (en secondes)
        'cache_duration_hours' => 24,                  // Cache 24h
        'max_offline_actions' => 100,                  // Max actions en attente
        'auto_sync_on_connection' => true,             // Sync auto quand connecté
        'offline_storage_quota' => 50,                 // 50MB de stockage local
    ],

    // ==================== GÉOLOCALISATION ====================
    
    'location' => [
        'tracking_enabled' => false,                   // Tracking position (optionnel)
        'update_interval_seconds' => 60,               // Mise à jour position
        'high_accuracy' => false,                      // GPS haute précision
        'cache_duration_minutes' => 30,                // Cache position
        'share_with_commercial' => false,              // Partager avec commercial
    ],

    // ==================== INTERFACE & UX ====================
    
    'ui' => [
        'theme' => 'purple',                           // Thème couleur
        'compact_mode' => false,                       // Mode compact
        'show_package_photos' => true,                 // Afficher photos colis
        'auto_refresh_lists' => true,                  // Refresh auto des listes
        'refresh_interval_seconds' => 30,              // Intervalle refresh
        'haptic_feedback' => true,                     // Retour haptique
    ],

    // ==================== SÉCURITÉ ====================
    
    'security' => [
        'require_signature_above' => 100,              // Signature obligatoire > 100 DT
        'photo_required_delivery' => false,            // Photo obligatoire livraison
        'photo_required_pickup' => false,              // Photo obligatoire pickup
        'auto_logout_minutes' => 480,                  // Déconnexion auto (8h)
        'session_timeout_warning' => 30,               // Alerte timeout (30 min avant)
        'max_login_attempts' => 5,                     // Max tentatives connexion
    ],

    // ==================== PERFORMANCE ====================
    
    'performance' => [
        'lazy_load_images' => true,                    // Chargement différé images
        'compress_photos' => true,                     // Compression photos
        'max_photo_size_mb' => 5,                      // Taille max photo (5MB)
        'cache_package_lists' => true,                 // Cache des listes
        'preload_next_packages' => 5,                  // Précharger 5 colis suivants
    ],

    // ==================== IMPRESSIONS & REÇUS ====================
    
    'printing' => [
        'auto_print_receipts' => false,                // Impression auto reçus
        'receipt_format' => 'thermal',                 // Format reçu (thermal/A4)
        'include_qr_code' => true,                     // QR code sur reçus
        'include_barcode' => true,                     // Code-barres sur reçus
        'receipt_copies' => 1,                         // Nombre de copies
        'print_pickup_receipt' => true,                // Reçu de pickup
        'print_delivery_receipt' => true,              // Reçu de livraison
    ],

    // ==================== API & ENDPOINTS ====================
    
    'api' => [
        'timeout_seconds' => 30,                       // Timeout requêtes API
        'retry_attempts' => 3,                         // Tentatives de retry
        'rate_limit_per_minute' => 100,                // Limite requêtes/minute
        'cache_responses' => true,                     // Cache réponses API
        'log_api_calls' => false,                      // Logger appels API
    ],

    // ==================== MODES DE TRAVAIL ====================
    
    'work_modes' => [
        'allow_night_deliveries' => false,             // Livraisons nocturnes
        'work_hours_start' => '08:00',                  // Début horaires
        'work_hours_end' => '20:00',                    // Fin horaires
        'weekend_work' => true,                         // Travail weekend
        'holiday_work' => false,                        // Travail jours fériés
        'emergency_mode' => false,                      // Mode urgence
    ],

    // ==================== LIMITES & QUOTAS ====================
    
    'limits' => [
        'max_packages_per_day' => 200,                 // Max colis/jour
        'max_pickup_radius_km' => 50,                  // Rayon pickup max
        'max_delivery_radius_km' => 50,                // Rayon livraison max
        'max_concurrent_packages' => 50,               // Max colis simultanés
        'max_wallet_balance' => 2000,                  // Limite solde wallet
    ],

    // ==================== FEATURES EXPÉRIMENTALES ====================
    
    'experimental' => [
        'ai_route_optimization' => false,              // Optimisation IA routes
        'voice_commands' => false,                     // Commandes vocales
        'augmented_reality' => false,                  // Réalité augmentée
        'smart_notifications' => false,                // Notifications intelligentes
        'predictive_delivery' => false,                // Livraison prédictive
    ],

    // ==================== URGENCES & SUPPORT ====================
    
    'emergency' => [
        'commercial_phone' => '+216 XX XXX XXX',       // Téléphone commercial
        'support_phone' => '+216 XX XXX XXX',          // Support technique
        'emergency_phone' => '197',                    // Numéro urgence
        'auto_call_threshold' => 3,                    // Appel auto après 3 échecs
        'panic_button_enabled' => true,                // Bouton panique
    ],

    // ==================== STATISTIQUES & ANALYTICS ====================
    
    'analytics' => [
        'track_performance' => true,                   // Suivre performances
        'anonymous_usage_stats' => false,              // Stats anonymes
        'detailed_logs' => true,                       // Logs détaillés
        'export_stats' => true,                        // Export statistiques
        'real_time_dashboard' => true,                 // Dashboard temps réel
    ],
];