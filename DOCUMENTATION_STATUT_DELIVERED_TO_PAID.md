# Documentation: Changement de Statut DELIVERED → PAID

## Vue d'ensemble

Le système change automatiquement le statut des colis de **DELIVERED** (Livré) à **PAID** (Payé) via un processus automatisé qui s'exécute chaque nuit à 22h00.

## Processus Automatique

### 1. Commande Artisan
**Fichier**: `app/Console/Commands/ProcessNightlyTransactions.php`

**Commande**: `php artisan wallet:process-nightly`

**Planification**: Exécutée automatiquement chaque jour à 22h00 via le scheduler Laravel

### 2. Fonctionnement

#### Étape 1: Récupération des colis livrés
```php
$deliveredPackages = Package::where('status', 'DELIVERED')
    ->whereDate('updated_at', $date)
    ->with(['sender', 'sender.wallet'])
    ->get();
```

#### Étape 2: Vérification COD
- Le système vérifie que le COD a été validé par le commercial
- Recherche dans `cod_collection_logs` avec `commercial_validated = true`

#### Étape 3: Calcul du montant à créditer
- Calcul selon la logique métier X/Y (COD - frais)
- Prise en compte des frais de livraison et de retour

#### Étape 4: Traitement financier
- Crédit du wallet du client expéditeur
- Création de transactions financières
- Logging des opérations

#### Étape 5: Changement de statut
```php
$package->update([
    'status' => 'PAID',
    'paid_at' => now(),
    'processed_at_22h' => true,
    'updated_at' => now()
]);
```

## Wallet Livreur et COD

### Ajout automatique du COD au wallet livreur

**Fichier**: `app/Http/Controllers/Deliverer/SimpleDelivererController.php`

**Méthode**: `markDelivered()`

Lorsqu'un livreur marque un colis comme livré:

```php
// 1. Marquer le colis comme DELIVERED
$package->update([
    'status' => 'DELIVERED',
    'delivered_at' => now()
]);

// 2. Ajouter le COD au wallet du livreur
if ($package->cod_amount > 0) {
    $wallet = UserWallet::firstOrCreate(['user_id' => $user->id]);
    
    $wallet->addFunds(
        $package->cod_amount,
        "COD collecté - Colis #{$package->package_code}",
        "COD_DELIVERY_{$package->id}"
    );
}
```

### Flux complet

1. **Livraison** (Immédiat)
   - Livreur marque le colis comme DELIVERED
   - COD ajouté au wallet du livreur
   - Transaction enregistrée

2. **Traitement nocturne** (22h00)
   - Vérification COD validé par commercial
   - Calcul du montant à créditer au client
   - Changement statut DELIVERED → PAID
   - Crédit du wallet client

## Options de la commande

```bash
# Traitement normal
php artisan wallet:process-nightly

# Traitement pour une date spécifique
php artisan wallet:process-nightly --date=2025-01-15

# Mode simulation (sans modifications)
php artisan wallet:process-nightly --dry-run

# Forcer le traitement même si déjà effectué
php artisan wallet:process-nightly --force

# Affichage détaillé
php artisan wallet:process-nightly --verbose
```

## Logs et Traçabilité

### Transactions financières
- Toutes les opérations sont enregistrées dans `financial_transactions`
- Type: `CREDIT` pour les ajouts au wallet
- Référence: `COD_DELIVERY_{package_id}` pour les COD livreur

### Historique des statuts
- Changements de statut loggés dans `action_logs`
- Trigger: `NIGHTLY_PROCESSING`
- Métadonnées: montant crédité, date de traitement

### Logs système
- Fichier: `storage/logs/laravel.log`
- Niveau: INFO pour succès, ERROR pour échecs
- Recherche: "ProcessNightlyTransactions" ou "wallet:process-nightly"

## Gestion des erreurs

### Cas d'échec
1. **COD non validé**: Colis ignoré, reste en DELIVERED
2. **Wallet introuvable**: Création automatique du wallet
3. **Erreur de transaction**: Rollback, colis reste en DELIVERED
4. **Erreur critique**: Email d'alerte envoyé aux administrateurs

### Récupération
- La commande peut être relancée manuellement
- Option `--force` pour retraiter les colis déjà traités
- Système de récupération automatique des transactions échouées

## Surveillance

### Métriques importantes
- Nombre de colis traités
- Montant total crédité
- Nombre d'échecs
- Temps d'exécution

### Alertes
- Email en cas d'erreur critique
- Notification si aucun colis traité pendant plusieurs jours
- Alerte si taux d'échec > 5%

## Maintenance

### Vérification manuelle
```bash
# Vérifier les colis DELIVERED en attente
php artisan tinker
>>> Package::where('status', 'DELIVERED')->count()

# Vérifier les colis traités aujourd'hui
>>> Package::where('status', 'PAID')->whereDate('paid_at', today())->count()
```

### Retraitement manuel
```bash
# Retraiter une date spécifique
php artisan wallet:process-nightly --date=2025-01-15 --force

# Mode simulation pour tester
php artisan wallet:process-nightly --date=2025-01-15 --dry-run --verbose
```

## Sécurité

- Transaction atomique (DB::beginTransaction / commit / rollBack)
- Vérification des permissions
- Validation des montants
- Logging complet de toutes les opérations
- Backup automatique avant traitement

## Notes importantes

1. Le changement DELIVERED → PAID se fait **uniquement** via le traitement nocturne
2. Le COD est ajouté au wallet livreur **immédiatement** lors de la livraison
3. Le wallet client est crédité **après validation** du COD par le commercial
4. Les colis sans COD validé restent en DELIVERED jusqu'à validation
5. Le processus est idempotent: peut être relancé sans risque de doublon

## Contact Support

En cas de problème avec le traitement automatique:
1. Vérifier les logs: `storage/logs/laravel.log`
2. Vérifier le scheduler: `php artisan schedule:list`
3. Tester en mode dry-run: `php artisan wallet:process-nightly --dry-run --verbose`
4. Contacter l'équipe technique avec les logs d'erreur
