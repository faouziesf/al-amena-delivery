# ⚡ Commandes Rapides - Système de Retours

## 🚀 Démarrage et Tests

### Installation et Configuration

```bash
# 1. Exécuter les migrations
php artisan migrate

# 2. Vérifier que tout est OK
php check_return_system_health.php

# 3. Test complet du système
php test_complete_return_system.php

# 4. Configurer le scheduler (Windows - Task Scheduler)
# Créer une tâche qui exécute:
php artisan schedule:run

# 5. Ou pour Linux/Mac (crontab -e)
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🧪 Tests et Validation

### Tests Automatisés

```bash
# Test santé complet (40 vérifications)
php check_return_system_health.php

# Test workflow complet
php test_complete_return_system.php

# Test jobs uniquement
php test_return_jobs.php

# Tests Laravel
php artisan test
```

### Tests Manuels via Tinker

```bash
php artisan tinker
```

**Créer un colis de test en AWAITING_RETURN:**
```php
$client = User::where('role', 'CLIENT')->first();
$package = Package::create([
    'sender_id' => $client->id,
    'package_code' => 'TEST-RET-' . strtoupper(substr(md5(uniqid()), 0, 6)),
    'tracking_number' => 'TRK-' . time(),
    'status' => 'AWAITING_RETURN',
    'cod_amount' => 150.00,
    'delivery_type' => 'standard',
    'recipient_data' => [
        'name' => 'Test Destinataire',
        'phone' => '20123456',
        'address' => '123 Rue Test',
        'city' => 'Tunis'
    ],
    'unavailable_attempts' => 3,
    'awaiting_return_since' => now()->subHours(50),
    'return_reason' => 'Destinataire injoignable après 3 tentatives'
]);
echo "Colis créé: {$package->package_code}\n";
```

**Exécuter les jobs manuellement:**
```php
// Job 1: AWAITING_RETURN → RETURN_IN_PROGRESS
$job1 = new \App\Jobs\ProcessAwaitingReturnsJob();
$job1->handle();

// Job 2: RETURNED_TO_CLIENT → RETURN_CONFIRMED
$job2 = new \App\Jobs\ProcessReturnedPackagesJob();
$job2->handle();
```

**Créer un colis retour:**
```php
$package = Package::where('status', 'RETURN_IN_PROGRESS')->first();
$returnPackage = \App\Models\ReturnPackage::create([
    'original_package_id' => $package->id,
    'return_package_code' => \App\Models\ReturnPackage::generateReturnCode(),
    'cod' => 0,
    'status' => 'AT_DEPOT',
    'sender_info' => \App\Models\ReturnPackage::getCompanyInfo(),
    'recipient_info' => [
        'name' => $package->sender->name,
        'phone' => $package->sender->phone,
        'address' => 'Adresse client',
        'city' => 'Tunis'
    ],
    'return_reason' => $package->return_reason,
    'comment' => 'Test manuel',
    'created_by' => null
]);
echo "Colis retour créé: {$returnPackage->return_package_code}\n";
```

---

## 📊 Requêtes SQL Utiles

### Statistiques

```bash
php artisan tinker
```

**Compter les colis par statut de retour:**
```php
DB::table('packages')
    ->whereIn('status', ['AWAITING_RETURN', 'RETURN_IN_PROGRESS', 'RETURNED_TO_CLIENT', 'RETURN_CONFIRMED', 'RETURN_ISSUE'])
    ->groupBy('status')
    ->select('status', DB::raw('count(*) as count'))
    ->get();
```

**Colis en attente de retour depuis plus de 48h:**
```php
Package::where('status', 'AWAITING_RETURN')
    ->where('awaiting_return_since', '<=', now()->subHours(48))
    ->count();
```

**Retours clients en attente de validation:**
```php
Package::where('status', 'RETURNED_TO_CLIENT')
    ->where('returned_to_client_at', '>=', now()->subHours(48))
    ->count();
```

**Tous les colis retours créés:**
```php
ReturnPackage::with('originalPackage')->get();
```

**Colis retours non imprimés:**
```php
ReturnPackage::whereNull('printed_at')->count();
```

**Colis retours livrés:**
```php
ReturnPackage::where('status', 'DELIVERED')->count();
```

---

## 🔍 Monitoring et Logs

### Voir les Logs en Temps Réel

```bash
# Laravel Pail (moderne)
php artisan pail

# Tail classique
tail -f storage/logs/laravel.log

# Filtrer les logs de retours
tail -f storage/logs/laravel.log | grep -i "retour\|return"

# Logs du jour uniquement
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log
```

### Rechercher dans les Logs

```bash
# Rechercher tous les retours confirmés
grep "Retour confirmé" storage/logs/laravel.log

# Rechercher les colis retours créés
grep "Colis retour créé" storage/logs/laravel.log

# Rechercher les problèmes signalés
grep "Problème signalé" storage/logs/laravel.log

# Compter les retours d'aujourd'hui
grep "$(date +%Y-%m-%d)" storage/logs/laravel.log | grep -c "retour"
```

---

## 🛣️ Vérification des Routes

### Lister les Routes de Retours

```bash
# Toutes les routes retours
php artisan route:list | grep -i returns

# Routes dépôt retours
php artisan route:list | grep "depot.*return"

# Routes client retours
php artisan route:list | grep "client.*return"

# Routes commercial retours
php artisan route:list | grep "commercial.*fourth\|commercial.*change-status"
```

### Tester une Route

```bash
php artisan tinker
```

```php
// Vérifier qu'une route existe
Route::has('depot.returns.dashboard'); // true/false

// Générer l'URL d'une route
route('depot.returns.dashboard');

// Avec paramètres
route('client.returns.confirm', ['package' => 123]);
```

---

## 🗄️ Base de Données

### Vérifications Rapides

```bash
php artisan tinker
```

**Vérifier la structure de return_packages:**
```php
DB::select("PRAGMA table_info(return_packages)");
```

**Vérifier les index:**
```php
DB::select("PRAGMA index_list(return_packages)");
```

**Compter les enregistrements:**
```php
ReturnPackage::count();
Package::whereNotNull('return_package_id')->count();
```

### Nettoyage et Maintenance

```bash
# Réinitialiser les migrations (DANGER - Perte de données)
php artisan migrate:fresh

# Rollback dernière migration
php artisan migrate:rollback

# Statut des migrations
php artisan migrate:status

# Optimiser la base de données
php artisan db:optimize
```

---

## 🔧 Cache et Configuration

### Gestion du Cache

```bash
# Vider tous les caches
php artisan cache:clear

# Vider le cache de configuration
php artisan config:clear

# Vider le cache des routes
php artisan route:clear

# Vider le cache des vues
php artisan view:clear

# Reconstruire les caches (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Vérifier le Cache (Tinker)

```php
// Vérifier si le cache fonctionne
Cache::put('test', 'ok', 60);
Cache::get('test'); // "ok"

// Voir toutes les sessions de scan actives
Cache::get('depot_return_scan_session:return_abc123');
```

---

## 📱 Tests Mobile

### Ngrok (Développement)

```bash
# Démarrer ngrok
ngrok http 8000

# L'URL sera affichée, exemple:
# https://abc123.ngrok.io

# Utiliser cette URL pour tester sur mobile:
# https://abc123.ngrok.io/depot/returns
```

### Simuler Requêtes Mobile

```bash
# Avec curl
curl -X POST https://localhost:8000/depot/returns/api/session/return_abc123/scan \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-token" \
  -d '{"package_code":"PKG-TEST123"}'

# Vérifier statut session
curl https://localhost:8000/depot/returns/api/session/return_abc123/status
```

---

## 🚨 Dépannage Rapide

### Problème: Jobs ne s'exécutent pas

```bash
# Test manuel des jobs
php artisan schedule:run

# Vérifier la liste des jobs planifiés
php artisan schedule:list

# Forcer l'exécution
php artisan schedule:work
```

### Problème: Session Scanner Expirée

```php
// Via Tinker
Cache::forget('depot_return_scan_session:return_abc123');

// Recréer une session
Cache::put('depot_return_scan_session:return_abc123', [
    'depot_manager_name' => 'Test',
    'packages' => [],
    'active' => true,
    'created_at' => now(),
    'last_activity' => now()
], now()->addHours(24));
```

### Problème: Erreur de Migration

```bash
# Voir les erreurs
php artisan migrate --pretend

# Forcer la migration
php artisan migrate --force

# Rollback et re-migrer
php artisan migrate:refresh
```

---

## 📈 Performance

### Optimisations

```bash
# Compiler les classes
php artisan optimize

# Compiler les configurations
php artisan config:cache

# Compiler les routes
php artisan route:cache

# Optimiser l'autoloader Composer
composer dump-autoload -o
```

### Monitoring Performance

```php
// Via Tinker
// Mesurer le temps d'exécution d'un job
$start = microtime(true);
$job = new \App\Jobs\ProcessAwaitingReturnsJob();
$job->handle();
$time = microtime(true) - $start;
echo "Temps: {$time}s\n";
```

---

## 🎯 Raccourcis Utiles

### Accès Rapide aux Interfaces

```bash
# Ouvrir dans le navigateur (Windows)
start http://localhost:8000/depot/returns
start http://localhost:8000/client/returns
start http://localhost:8000/commercial/packages/1

# Linux/Mac
xdg-open http://localhost:8000/depot/returns
open http://localhost:8000/depot/returns
```

### Créer des Données de Test Rapidement

```bash
php artisan tinker
```

```php
// Factory pour créer plusieurs colis
for ($i = 0; $i < 10; $i++) {
    Package::create([
        'sender_id' => User::where('role', 'CLIENT')->first()->id,
        'package_code' => 'TEST-' . strtoupper(substr(md5(uniqid()), 0, 6)),
        'tracking_number' => 'TRK-' . (time() + $i),
        'status' => 'AWAITING_RETURN',
        'cod_amount' => rand(50, 500),
        'delivery_type' => 'standard',
        'recipient_data' => [
            'name' => 'Client Test ' . $i,
            'phone' => '2012345' . $i,
            'address' => 'Adresse ' . $i,
            'city' => 'Tunis'
        ],
        'unavailable_attempts' => 3,
        'awaiting_return_since' => now()->subHours(rand(24, 72)),
        'return_reason' => 'Test automatique'
    ]);
}
echo "10 colis créés\n";
```

---

## 📚 Documentation Rapide

### Ouvrir la Documentation

```bash
# Windows
start SYSTEME_RETOURS_FINAL_DOCUMENTATION.md
start ROUTES_SYSTEME_RETOURS.md
start README_SYSTEME_RETOURS.md

# Linux/Mac
xdg-open SYSTEME_RETOURS_FINAL_DOCUMENTATION.md
```

---

## 🔐 Sécurité

### Vérifier les Permissions

```bash
php artisan tinker
```

```php
// Vérifier qu'un utilisateur a le bon rôle
$user = User::find(1);
$user->role; // CLIENT, COMMERCIAL, etc.

// Tester les middleware
$request = new \Illuminate\Http\Request();
$request->setUserResolver(function() use ($user) { return $user; });
```

---

## 📊 Exports et Rapports

### Exporter des Données

```bash
php artisan tinker
```

```php
// Export CSV des retours
$returns = ReturnPackage::with('originalPackage')->get();
$csv = "Code Retour,Colis Original,Statut,Date\n";
foreach ($returns as $r) {
    $csv .= "{$r->return_package_code},{$r->originalPackage->package_code},{$r->status},{$r->created_at}\n";
}
file_put_contents('retours_export.csv', $csv);
echo "Export terminé: retours_export.csv\n";
```

---

## 🎉 Commandes de Démonstration

### Démo Complète pour Présentation

```bash
# 1. Vérifier la santé
php check_return_system_health.php

# 2. Créer des données de démo
php artisan tinker
```

```php
// Créer 5 colis en différents statuts de retour
$client = User::where('role', 'CLIENT')->first();

// 1. En attente de retour
Package::create([
    'sender_id' => $client->id,
    'package_code' => 'DEMO-AWAIT-001',
    'tracking_number' => 'TRK-DEMO-001',
    'status' => 'AWAITING_RETURN',
    'cod_amount' => 150,
    'delivery_type' => 'standard',
    'recipient_data' => ['name' => 'Demo Client', 'phone' => '12345678', 'address' => 'Test', 'city' => 'Tunis'],
    'unavailable_attempts' => 3,
    'awaiting_return_since' => now()->subHours(10),
    'return_reason' => 'Destinataire injoignable'
]);

// 2. Retour en cours
Package::create([
    'sender_id' => $client->id,
    'package_code' => 'DEMO-PROG-002',
    'tracking_number' => 'TRK-DEMO-002',
    'status' => 'RETURN_IN_PROGRESS',
    'cod_amount' => 200,
    'delivery_type' => 'standard',
    'recipient_data' => ['name' => 'Demo Client 2', 'phone' => '12345679', 'address' => 'Test', 'city' => 'Tunis'],
    'unavailable_attempts' => 3,
    'return_in_progress_since' => now()->subHours(5),
    'return_reason' => 'Adresse incorrecte'
]);

echo "Données de démo créées!\n";
```

```bash
# 3. Montrer les interfaces
start http://localhost:8000/depot/returns
start http://localhost:8000/client/returns
```

---

**Dernière mise à jour:** 11 Octobre 2025
**Référence:** SYSTEME_RETOURS_FINAL_DOCUMENTATION.md
