# 🚀 Quick Start - Compte Superviseur

## Étape 1: Exécuter les Migrations

```bash
php artisan migrate
```

Cette commande va créer 6 nouvelles tables:
- `fixed_charges` - Charges fixes
- `depreciable_assets` - Actifs amortissables  
- `vehicles` - Véhicules
- `vehicle_mileage_readings` - Relevés kilométriques
- `vehicle_maintenance_alerts` - Alertes de maintenance
- `critical_action_config` - Configuration des actions critiques

## Étape 2: Peupler les Actions Critiques (Recommandé)

Créer le seeder:
```bash
php artisan make:seeder CriticalActionConfigSeeder
```

Contenu du fichier `database/seeders/CriticalActionConfigSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CriticalActionConfig;

class CriticalActionConfigSeeder extends Seeder
{
    public function run()
    {
        $actions = [
            [
                'action_type' => 'USER_ROLE_CHANGED',
                'target_type' => 'User',
                'description' => 'Changement de rôle utilisateur',
                'is_critical' => true,
            ],
            [
                'action_type' => 'FINANCIAL_VALIDATION',
                'target_type' => 'Transaction',
                'description' => 'Validation financière importante',
                'is_critical' => true,
                'conditions' => [
                    'amount' => ['operator' => '>', 'value' => 1000]
                ],
            ],
            [
                'action_type' => 'IMPERSONATION_START',
                'target_type' => 'User',
                'description' => 'Début de session impersonation',
                'is_critical' => true,
            ],
            [
                'action_type' => 'IMPERSONATION_STOP',
                'target_type' => 'User',
                'description' => 'Fin de session impersonation',
                'is_critical' => true,
            ],
            [
                'action_type' => 'SYSTEM_SETTING_CHANGED',
                'target_type' => 'SystemSetting',
                'description' => 'Modification paramètre système',
                'is_critical' => true,
            ],
            [
                'action_type' => 'USER_CREATED',
                'target_type' => 'User',
                'description' => 'Création d\'un nouvel utilisateur',
                'is_critical' => false,
            ],
            [
                'action_type' => 'PACKAGE_STATUS_CHANGED',
                'target_type' => 'Package',
                'description' => 'Changement de statut colis important',
                'is_critical' => true,
                'conditions' => [
                    'new_status' => ['operator' => 'in', 'value' => ['CANCELLED', 'LOST']]
                ],
            ],
        ];

        foreach ($actions as $action) {
            CriticalActionConfig::create($action);
        }
    }
}
```

Exécuter le seeder:
```bash
php artisan db:seed --class=CriticalActionConfigSeeder
```

## Étape 3: Créer des Index pour la Recherche (Performance)

Exécuter ces requêtes SQL:

```sql
-- Index pour recherche packages
CREATE INDEX idx_packages_tracking ON packages(tracking_number);
CREATE INDEX idx_packages_recipient_name ON packages(recipient_name);
CREATE INDEX idx_packages_recipient_phone ON packages(recipient_phone);

-- Index pour recherche users
CREATE INDEX idx_users_name ON users(name);
CREATE INDEX idx_users_phone ON users(phone);

-- Index pour recherche tickets
CREATE INDEX idx_tickets_number ON tickets(ticket_number);
CREATE INDEX idx_tickets_subject ON tickets(subject);
```

## Étape 4: Tester les Endpoints Backend

### Test 1: Dashboard Financier (API)
```bash
curl http://localhost:8000/supervisor/api/financial/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Test 2: Créer une Charge Fixe
```bash
curl -X POST http://localhost:8000/supervisor/financial/charges \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Loyer Bureau",
    "description": "Loyer mensuel",
    "amount": 1500.000,
    "periodicity": "MONTHLY",
    "is_active": true
  }'
```

### Test 3: Créer un Véhicule
```bash
curl -X POST http://localhost:8000/supervisor/vehicles \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Peugeot Partner",
    "registration_number": "123TU1234",
    "purchase_price": 25000.000,
    "max_depreciation_km": 300000,
    "current_km": 50000,
    "oil_change_cost": 50.000,
    "oil_change_interval_km": 10000,
    "last_oil_change_km": 45000,
    "spark_plug_cost": 80.000,
    "spark_plug_interval_km": 30000,
    "last_spark_plug_change_km": 30000,
    "tire_unit_cost": 120.000,
    "tire_change_interval_km": 50000,
    "last_tire_change_km": 0,
    "fuel_price_per_liter": 2.150
  }'
```

### Test 4: Recherche Globale
```bash
curl -X POST http://localhost:8000/supervisor/search/api \
  -H "Content-Type: application/json" \
  -d '{
    "q": "John",
    "type": "all"
  }'
```

## Étape 5: Accéder aux Routes Superviseur

Une fois connecté en tant que superviseur:

### Dashboard
```
http://localhost:8000/supervisor/dashboard
```

### Gestion Financière
```
http://localhost:8000/supervisor/financial/charges
http://localhost:8000/supervisor/financial/assets
http://localhost:8000/supervisor/financial/reports
```

### Gestion Véhicules
```
http://localhost:8000/supervisor/vehicles
http://localhost:8000/supervisor/vehicles/alerts
```

### Recherche
```
http://localhost:8000/supervisor/search
```

### Logs
```
http://localhost:8000/supervisor/action-logs
http://localhost:8000/supervisor/action-logs/critical
```

### Utilisateurs
```
http://localhost:8000/supervisor/users
http://localhost:8000/supervisor/users/by-role/CLIENT
http://localhost:8000/supervisor/users/by-role/DELIVERER
```

## Étape 6: Exemples d'Utilisation

### Calculer le Bénéfice du Mois

```php
use App\Services\FinancialCalculationService;

$financialService = app(FinancialCalculationService::class);
$report = $financialService->getMonthSummary();

echo "Revenus: {$report['total_revenue']} DT\n";
echo "Charges: {$report['total_charges']} DT\n";
echo "Bénéfice: {$report['profit']} DT\n";
echo "Marge: {$report['profit_margin']}%\n";
```

### Enregistrer une Action Critique

```php
use App\Services\ActionLogService;

$actionLogService = app(ActionLogService::class);

// Enregistre et notifie automatiquement si critique
$actionLogService->logRoleChanged(
    $userId, 
    'CLIENT', 
    'COMMERCIAL',
    ['changed_by' => auth()->id()]
);
```

### Vérifier les Alertes Véhicule

```php
use App\Models\Vehicle;

$vehicle = Vehicle::find(1);

if ($vehicle->isMaintenanceDue('oil')) {
    echo "Vidange nécessaire dans {$vehicle->km_until_oil_change} km\n";
}

if ($vehicle->isMaintenanceDue('tire', 1000)) {
    echo "Changement pneus bientôt nécessaire\n";
}
```

### Impersonation

```php
// Dans le contrôleur ou route
use App\Services\ActionLogService;
use Illuminate\Support\Facades\Auth;

$actionLogService = app(ActionLogService::class);

// Démarrer impersonation
session()->put('impersonator_id', Auth::id());
$actionLogService->logImpersonation(Auth::id(), $targetUserId, 'START');
Auth::login($targetUser);

// Arrêter impersonation
$supervisorId = session()->get('impersonator_id');
$actionLogService->logImpersonation($supervisorId, Auth::id(), 'STOP');
session()->forget('impersonator_id');
Auth::login($supervisor);
```

## Étape 7: Vérifier les Logs

### Dans la base de données
```sql
-- Voir les dernières actions
SELECT * FROM action_logs 
ORDER BY created_at DESC 
LIMIT 10;

-- Voir les actions critiques
SELECT al.*, cac.description 
FROM action_logs al
JOIN critical_action_config cac ON al.action_type = cac.action_type
WHERE cac.is_critical = 1
ORDER BY al.created_at DESC;

-- Voir les alertes véhicules non lues
SELECT * FROM vehicle_maintenance_alerts
WHERE is_read = 0
ORDER BY severity DESC, created_at DESC;
```

### Via l'API
```bash
# Logs récents
curl http://localhost:8000/supervisor/api/action-logs/recent?limit=20

# Stats financières
curl http://localhost:8000/supervisor/api/financial/dashboard
```

## Commandes Utiles

### Nettoyer le cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Vérifier les routes
```bash
php artisan route:list --name=supervisor
```

### Créer des données de test
```bash
php artisan tinker
```

Puis:
```php
// Créer une charge fixe
App\Models\FixedCharge::create([
    'name' => 'Test Charge',
    'amount' => 100,
    'periodicity' => 'MONTHLY',
    'is_active' => true,
    'created_by' => 1
]);

// Créer un véhicule
App\Models\Vehicle::create([
    'name' => 'Test Vehicle',
    'purchase_price' => 20000,
    'max_depreciation_km' => 200000,
    'current_km' => 10000,
    'oil_change_cost' => 50,
    'oil_change_interval_km' => 10000,
    'last_oil_change_km' => 5000,
    'spark_plug_cost' => 80,
    'spark_plug_interval_km' => 30000,
    'last_spark_plug_change_km' => 0,
    'tire_unit_cost' => 100,
    'tire_change_interval_km' => 40000,
    'last_tire_change_km' => 0,
    'fuel_price_per_liter' => 2.0,
    'created_by' => 1
]);
```

## Troubleshooting

### Erreur: Table not found
```bash
php artisan migrate:status
php artisan migrate
```

### Erreur: Class not found
```bash
composer dump-autoload
```

### Routes ne fonctionnent pas
```bash
php artisan route:clear
php artisan route:cache
```

### Les calculs ne semblent pas corrects
Vérifier:
1. Les relevés kilométriques existent pour le véhicule
2. La consommation moyenne est calculée (après 2+ relevés)
3. Les jours ouvrables sont bien calculés (6j/semaine)

---

## 📚 Documentation Complète

- **`IMPLEMENTATION_SUPERVISEUR_COMPLETE.md`** - Documentation technique détaillée
- **`COMPTE_SUPERVISEUR_RESUME.md`** - Résumé de l'implémentation
- **`QUICK_START_SUPERVISEUR.md`** - Ce guide (démarrage rapide)

---

## ✅ Checklist de Démarrage

- [ ] Migrations exécutées
- [ ] Seeder actions critiques exécuté
- [ ] Index base de données créés
- [ ] Test endpoints API réussi
- [ ] Accès routes superviseur OK
- [ ] Prêt à développer les vues frontend

Une fois ces étapes complétées, le backend est 100% opérationnel et vous pouvez commencer à créer les interfaces frontend !
