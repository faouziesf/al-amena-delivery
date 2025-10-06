# 🗄️ Migration et Seeder - Base de Données

**Date**: 2025-01-06  
**Version**: 2.0

---

## ✅ Ce qui a été fait

### 1. **Migration Consolidée**
✅ Suppression de toutes les anciennes migrations (33 fichiers)  
✅ Création d'une migration unique : `2025_01_06_000000_create_complete_database_schema.php`

**Contient toutes les tables** :
- users
- client_profiles
- packages
- pickup_requests
- user_wallets
- financial_transactions
- withdrawal_requests
- topup_requests
- delegations
- tickets, complaints
- run_sheets, manifests
- transit_routes, transit_boxes
- Et toutes les tables système

### 2. **Seeder Basé sur Données Actuelles**
✅ Suppression de tous les anciens seeders  
✅ Création d'un nouveau `DatabaseSeeder.php` intelligent

**Caractéristiques** :
- ✅ **Mot de passe uniforme : `12345678`** pour TOUS les utilisateurs
- ✅ Utilise les données exportées de votre DB actuelle (`database_export.json`)
- ✅ Crée des comptes par défaut si pas d'export
- ✅ Maintient les IDs et relations

---

## 🔑 Comptes Utilisateurs

### Tous les mots de passe sont : `12345678`

**Comptes par défaut créés** :
```
📧 admin@alamena.com          - ADMIN
📧 commercial@alamena.com     - COMMERCIAL  
📧 client@alamena.com         - CLIENT
📧 deliverer@alamena.com      - DELIVERER
📧 depot@alamena.com          - DEPOT_MANAGER
```

**Si vous utilisez vos données exportées** :
- supervisor@test.com
- commercial1@test.com
- commercial2@test.com
- deliverer1@test.com
- client1@test.com
- ... etc (40 utilisateurs au total)

---

## 🚀 Comment Utiliser

### Option A : Nouvelle Installation (Base Vide)

```bash
# 1. Créer la base de données
php artisan migrate

# 2. Peupler avec des données
php artisan db:seed
```

### Option B : Réinitialiser Complètement

```bash
# ⚠️ ATTENTION : Supprime toutes les données !
php artisan migrate:fresh --seed
```

### Option C : Garder la Base Actuelle

**Si votre base fonctionne déjà, NE RIEN FAIRE !**

La migration est fournie uniquement pour référence ou nouvelles installations.

---

## 📊 Données Créées par le Seeder

### Si données exportées disponibles :
- ✅ 40 utilisateurs (existants)
- ✅ 24 délégations
- ✅ 13 profils clients
- ✅ Relations préservées

### Si pas d'export (mode par défaut) :
- ✅ 5 utilisateurs de test (1 par rôle)
- ✅ 10 délégations tunisiennes
- ✅ 1 profil client de test

---

## 🔧 Structure de la Migration

### Tables Principales

**Utilisateurs**
- `users` - Comptes utilisateurs (tous rôles)
- `client_profiles` - Profils clients détaillés

**Colis**
- `packages` - Colis avec tracking
- `pickup_requests` - Demandes de collecte
- `package_status_histories` - Historique statuts

**Finances**
- `user_wallets` - Portefeuilles utilisateurs
- `financial_transactions` - Transactions
- `withdrawal_requests` - Demandes de retrait
- `topup_requests` - Demandes de recharge
- `cod_modifications` - Modifications COD

**Opérations**
- `delegations` - Délégations tunisiennes
- `run_sheets` - Feuilles de route
- `manifests` - Manifestes
- `transit_routes` - Routes de transit
- `transit_boxes` - Caisses de transit

**Support**
- `tickets` - Tickets support
- `ticket_messages` - Messages tickets
- `complaints` - Réclamations
- `saved_addresses` - Adresses sauvegardées

**Système**
- `action_logs` - Logs d'actions
- `notifications` - Notifications
- `import_batches` - Imports en masse
- `sessions`, `cache`, `jobs` - Tables Laravel

---

## ⚠️ Correction Effectuée

### Problème Résolu : Index Duplicate

**Erreur originale** :
```
SQLSTATE[HY000]: General error: 1 index notifications_notifiable_type_notifiable_id_index already exists
```

**Cause** :
La méthode `$table->morphs('notifiable')` crée automatiquement un index.
L'ajouter manuellement créait un doublon.

**Solution** :
```php
// ❌ AVANT (causait l'erreur)
$table->morphs('notifiable');
$table->index(['notifiable_type', 'notifiable_id']); // Doublon !

// ✅ APRÈS (corrigé)
$table->morphs('notifiable'); // Index créé automatiquement
```

---

## 📝 Fichiers Créés/Modifiés

### Migrations
- ✅ `database/migrations/2025_01_06_000000_create_complete_database_schema.php` (créé)
- ✅ 33 anciennes migrations supprimées

### Seeders  
- ✅ `database/seeders/DatabaseSeeder.php` (recréé)
- ✅ 7 anciens seeders supprimés

### Données
- ✅ `database_export.json` - Export des données actuelles (conservé pour référence)

---

## 🧪 Tests

### Vérifier la migration

```bash
# Voir les tables créées
php artisan db:show

# Voir le statut des migrations
php artisan migrate:status
```

### Vérifier le seeder

```bash
# Tester le seeder (sur une DB de test)
php artisan db:seed --class=DatabaseSeeder

# Vérifier les utilisateurs créés
php artisan tinker
>>> User::all()->pluck('email', 'role')
```

### Test de connexion

```bash
# Tester un login
Email: admin@alamena.com
Password: 12345678
```

---

## 🔐 Sécurité

### ⚠️ IMPORTANT : Changez les mots de passe en production !

Le mot de passe `12345678` est pour le **développement uniquement**.

**En production** :
1. Changez tous les mots de passe
2. Utilisez des mots de passe forts
3. Activez 2FA si disponible

---

## 📋 Checklist de Déploiement

### Pour nouvelle machine (exemple: Syrine)

```bash
# 1. Clone du repo
git clone https://github.com/faouziesf/al-amena-delivery.git
cd al-amena-delivery

# 2. Installation
composer install
npm install

# 3. Configuration
cp .env.example .env
php artisan key:generate

# 4. Base de données
# Éditer .env pour configurer la DB
php artisan migrate
php artisan db:seed

# 5. Build assets
npm run build

# 6. Démarrer serveur
php artisan serve
```

### Pour machine existante (exemple: DELL)

```bash
# 1. Pull les changements
git pull origin main

# 2. Update dependencies
composer install
npm install

# 3. Ne PAS migrer si la DB fonctionne !
# Seulement si besoin :
# php artisan migrate

# 4. Build assets
npm run build
```

---

## 🐛 Résolution de Problèmes

### Erreur : "Table already exists"

**Cause** : Migration déjà exécutée ou table existante

**Solution** :
```bash
# Option 1: Ne rien faire (DB déjà OK)

# Option 2: Reset complet (⚠️ perte de données)
php artisan migrate:fresh --seed
```

### Erreur : "Foreign key constraint"

**Cause** : Ordre des tables incorrect

**Solution** : La migration respecte l'ordre des dépendances.
Si problème, vérifier les `foreignId()` dans la migration.

### Seeder ne trouve pas les données

**Cause** : `database_export.json` manquant

**Solution** : Le seeder crée des données par défaut automatiquement.

---

## 📊 Statistiques

**Avant** :
- 33 fichiers de migration
- 8 fichiers de seeder
- Maintenance complexe

**Après** :
- 1 fichier de migration
- 1 fichier de seeder  
- Maintenance simplifiée
- ✅ Tous les mots de passe : `12345678`

---

## 🎯 Prochaines Étapes

1. ✅ Tester la migration sur machine de Syrine
2. ✅ Vérifier tous les comptes avec mot de passe `12345678`
3. ✅ Tester les scanners (corrections déjà appliquées)
4. ✅ Déployer en production (avec mots de passe sécurisés)

---

**Tout est maintenant prêt ! 🚀**

**Mot de passe pour TOUS les utilisateurs : `12345678`**
