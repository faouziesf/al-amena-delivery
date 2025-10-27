<?php

/**
 * Script de Migration Frontend Superviseur
 * 
 * Ce script liste toutes les corrections à apporter
 */

return [
    'controllers_to_fix' => [
        'UserController' => [
            'status' => 'OK',
            'fixes' => [
                'index() utilise by-role.blade.php' => 'OK',
                'byRole() existe' => 'OK',
                'activity() existe' => 'OK',
                'impersonate() existe' => 'OK',
            ]
        ],
        'VehicleManagementController' => [
            'status' => 'À vérifier',
            'fixes_needed' => [
                'show() doit retourner vehicles.show',
                'edit() doit retourner vehicles.edit',
            ]
        ],
        'FinancialManagementController' => [
            'status' => 'À vérifier',
            'fixes_needed' => [
                'showCharge() doit retourner financial.charges.show',
                'editCharge() doit retourner financial.charges.edit',
                'indexAssets() doit être créé',
            ]
        ],
        'EnhancedActionLogController' => [
            'status' => 'OK',
            'fixes' => [
                'critical() utilise action-logs.critical' => 'OK',
            ]
        ],
        'GlobalSearchController' => [
            'status' => 'OK',
            'fixes' => [
                'index() utilise search.index' => 'OK',
            ]
        ],
    ],

    'views_to_update' => [
        // Vues à mettre à jour avec nouveau layout
        'supervisor/packages/index.blade.php' => 'Mettre à jour layout',
        'supervisor/packages/show.blade.php' => 'Mettre à jour layout',
        'supervisor/tickets/index.blade.php' => 'Mettre à jour layout',
        'supervisor/tickets/show.blade.php' => 'Mettre à jour layout',
        'supervisor/notifications/index.blade.php' => 'Mettre à jour layout',
        'supervisor/settings/index.blade.php' => 'Mettre à jour layout',
        'supervisor/users/create.blade.php' => 'Mettre à jour layout',
        'supervisor/users/edit.blade.php' => 'Mettre à jour layout',
        'supervisor/users/show.blade.php' => 'Mettre à jour layout',
    ],

    'views_to_delete' => [
        // Anciennes vues à supprimer
        'supervisor/dashboard.blade.php' => 'Remplacée par dashboard-new.blade.php',
        'supervisor/action-logs/index.blade.php' => 'Remplacée par critical.blade.php',
        'supervisor/action-logs/show.blade.php' => 'Non utilisée',
        'supervisor/delegations/index.blade.php' => 'Non utilisée',
        'supervisor/reports/*.blade.php' => 'Remplacées par financial/reports/',
        'supervisor/system/*.blade.php' => 'À revoir',
    ],

    'routes_to_verify' => [
        '/supervisor/dashboard' => 'SupervisorDashboardController@index -> dashboard-new',
        '/supervisor/users' => 'UserController@index -> by-role (ou garder index)',
        '/supervisor/users/by-role/{role}' => 'UserController@byRole -> by-role',
        '/supervisor/users/{user}/activity' => 'UserController@activity -> activity',
        '/supervisor/vehicles' => 'VehicleManagementController@index -> vehicles.index',
        '/supervisor/vehicles/{id}' => 'VehicleManagementController@show -> vehicles.show',
        '/supervisor/financial/charges' => 'FinancialManagementController@indexCharges -> charges.index',
        '/supervisor/action-logs/critical' => 'EnhancedActionLogController@critical -> critical',
        '/supervisor/search' => 'GlobalSearchController@index -> search.index',
    ],

    'api_routes_needed' => [
        '/supervisor/api/financial/dashboard' => 'Stats dashboard financier',
        '/supervisor/api/financial/trends' => 'Tendances 7 jours',
        '/supervisor/api/action-logs/recent' => 'Logs récents pour dashboard',
        '/supervisor/api/users/stats' => 'Stats utilisateurs',
        '/supervisor/api/vehicles/stats' => 'Stats véhicules',
        '/supervisor/api/vehicles/alerts-count' => 'Nombre d\'alertes',
        '/supervisor/api/notifications/unread-count' => 'Nombre notifications non lues',
    ],

    'database_checks' => [
        'tables' => [
            'fixed_charges' => 'Table charges fixes existe?',
            'depreciable_assets' => 'Table actifs amortissables existe?',
            'vehicles' => 'Table véhicules existe?',
            'vehicle_mileage_readings' => 'Table relevés kilométriques existe?',
            'vehicle_maintenance_alerts' => 'Table alertes maintenance existe?',
            'action_logs' => 'Table logs actions existe?',
        ],
        'data' => [
            'users_count' => 'Nombre d\'utilisateurs en base',
            'packages_count' => 'Nombre de colis en base',
            'vehicles_count' => 'Nombre de véhicules en base',
            'charges_count' => 'Nombre de charges en base',
        ]
    ],

    'commands_to_run' => [
        'php artisan migrate' => 'Appliquer toutes les migrations',
        'php artisan view:clear' => 'Vider cache des vues',
        'php artisan route:clear' => 'Vider cache des routes',
        'php artisan config:clear' => 'Vider cache config',
        'php artisan cache:clear' => 'Vider cache général',
    ]
];
