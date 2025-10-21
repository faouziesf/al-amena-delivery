# 🧪 Commandes Test & Déploiement Production

## ⚠️ ÉTAPE 1 : Migration Base de Données (OBLIGATOIRE)

```bash
# Exécuter les migrations
php artisan migrate

# Vérifier que les tables sont créées
php artisan tinker
>>> Schema::hasTable('action_logs')
>>> Schema::hasTable('notifications')  
>>> exit
```

**Tables Créées** :
- ✅ `action_logs` - Pour traçabilité complète
- ✅ `notifications` - Pour système notifications

---

## 🧪 ÉTAPE 2 : Tests Corrections Appliquées

### **Test 1 : Pickups Disponibles**

```bash
# 1. Créer un pickup test via Tinker
php artisan tinker
```

```php
$pickup = \App\Models\PickupRequest::create([
    'client_id' => 1, // ID client existant
    'pickup_address' => 'Test Address 123',
    'pickup_phone' => '50123456',
    'pickup_contact_name' => 'Contact Test',
    'delegation_id' => 1, // ID delegation existante
    'status' => 'pending',
    'assigned_deliverer_id' => null,
    'requested_pickup_date' => now()->addDay(),
]);
exit
```

**Puis tester** :
```
1. Se connecter comme livreur
2. Aller sur /deliverer/pickups/available
3. ✅ Le pickup doit apparaître
4. Cliquer "Accepter"
5. ✅ Doit être assigné au livreur
```

### **Test 2 : Pickups dans Tournée**

```
1. Après avoir accepté un pickup
2. Aller sur /deliverer/tournee
3. Cliquer sur filtre "📦 Pickups"
4. ✅ Le pickup assigné doit apparaître
```

### **Test 3 : Historique Automatique**

```bash
# 1. Modifier un colis existant
php artisan tinker
```

```php
$package = \App\Models\Package::first();
$package->status = 'OUT_FOR_DELIVERY';
$package->save();

// Vérifier historique créé
$history = \App\Models\PackageStatusHistory::where('package_id', $package->id)
    ->latest()
    ->first();
dd($history);

// Vérifier action log créé
$log = \App\Models\ActionLog::where('entity_type', 'Package')
    ->where('entity_id', $package->id)
    ->latest()
    ->first();
dd($log->old_values, $log->new_values);

exit
```

**Résultat Attendu** :
- ✅ Entrée dans `package_status_histories` avec ancien/nouveau statut
- ✅ Entrée dans `action_logs` avec détails complets
- ✅ User, IP, User-Agent enregistrés

---

## 📋 ÉTAPE 3 : Vérifications Interface

### **Client**
```
1. /client/packages
2. Cliquer sur un colis qui a un retour
3. ✅ Bouton "↩️ Suivre le Retour" doit apparaître
4. Cliquer dessus
5. ✅ Doit afficher détails retour
```

### **Livreur - Pickups**
```
1. /deliverer/pickups/available
2. ✅ Liste pickups disponibles
3. /deliverer/tournee
4. ✅ Pickups assignés dans section dédiée
```

### **Livreur - Wallet**
```
1. /deliverer/wallet
2. ⚠️ À optimiser (en cours)
3. Vérifier que transactions s'affichent
```

---

## 🚀 ÉTAPE 4 : Mise en Production

### **1. Cache & Optimisations**
```bash
# Clear tous les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Reconstruire caches optimisés
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **2. Permissions**
```bash
# Vérifier permissions storage et bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### **3. Vérification Finale**
```bash
# Vérifier aucune erreur
php artisan about

# Test connexion DB
php artisan tinker
>>> \DB::connection()->getPdo()
>>> exit
```

---

## 🔧 Commandes Utiles Debug

### **Voir Derniers Logs**
```bash
tail -f storage/logs/laravel.log
```

### **Vérifier Routes**
```bash
# Toutes les routes livreur
php artisan route:list --name=deliverer

# Routes pickups spécifiquement
php artisan route:list | grep pickup
```

### **Vérifier Migrations**
```bash
php artisan migrate:status
```

### **Rollback si Problème**
```bash
# Rollback dernière migration
php artisan migrate:rollback --step=1

# Rollback tout et recommencer
php artisan migrate:fresh # ⚠️ ATTENTION : Efface données
```

---

## 📊 Monitoring Production

### **Vérifier Nombre Action Logs**
```php
php artisan tinker
>>> \App\Models\ActionLog::count()
>>> \App\Models\ActionLog::today()->count()
>>> \App\Models\ActionLog::byAction('PACKAGE_UPDATED')->count()
>>> exit
```

### **Vérifier Historique Statuts**
```php
php artisan tinker
>>> \App\Models\PackageStatusHistory::count()
>>> \App\Models\PackageStatusHistory::whereDate('created_at', today())->count()
>>> exit
```

### **Performances**
```bash
# Analyser requêtes lentes
# Dans .env ajouter:
DB_LOG_QUERIES=true

# Puis vérifier logs
tail -f storage/logs/laravel.log | grep "SELECT"
```

---

## ⚠️ Problèmes Connus & Solutions

### **Problème 1 : Pickups ne chargent pas**
**Cause** : Statuts incohérents en DB  
**Solution** :
```php
php artisan tinker
// Corriger statuts
\App\Models\PickupRequest::whereNull('assigned_deliverer_id')
    ->where('status', 'created')
    ->update(['status' => 'pending']);
exit
```

### **Problème 2 : Observer ne se déclenche pas**
**Cause** : Cache pas vidé  
**Solution** :
```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

### **Problème 3 : Action logs trop nombreux**
**Cause** : Normal si beaucoup d'activité  
**Solution** :
```php
// Archiver vieux logs (>6 mois)
php artisan tinker
\App\Models\ActionLog::where('created_at', '<', now()->subMonths(6))->delete();
exit
```

---

## 📝 Checklist Finale Avant Production

- [ ] Migration exécutée (`php artisan migrate`)
- [ ] Caches vidés (`php artisan optimize:clear`)
- [ ] Tests pickups OK
- [ ] Tests historique automatique OK
- [ ] Permissions storage OK
- [ ] Vérification liens retour client OK
- [ ] Backup base de données fait
- [ ] Variables .env vérifiées
- [ ] SSL/HTTPS configuré
- [ ] Monitoring activé

---

## 🆘 Support

### **En cas de problème critique**
1. Vérifier `storage/logs/laravel.log`
2. Vérifier status migration : `php artisan migrate:status`
3. Test connexion : `php artisan tinker` puis `DB::connection()->getPdo()`
4. Clear tous caches : `php artisan optimize:clear`

### **Rollback sécurisé**
```bash
# 1. Backup DB
mysqldump -u user -p database > backup_$(date +%Y%m%d_%H%M%S).sql

# 2. Rollback migration
php artisan migrate:rollback --step=1

# 3. Clear caches
php artisan optimize:clear
```

---

**Document Version** : 1.0  
**Dernière MAJ** : 19 Janvier 2025, 15:35  
**Statut** : ✅ **PRÊT POUR TESTS**

---

**IMPORTANT** : Toujours tester sur environnement de dev avant production !
