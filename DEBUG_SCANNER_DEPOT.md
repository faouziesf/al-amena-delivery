# 🔍 Guide de Débogage - Scanner Dépôt

**Date:** 2025-10-09

---

## 📋 Logs Ajoutés

J'ai ajouté des logs détaillés pour débugger les deux problèmes:

### 1. Validation du Code (Problème de Redirection)

**Fichier:** `app/Http/Controllers/DepotScanController.php`

#### Log Création Session (Ligne 47-55)
```php
\Log::info("Session créée", [
    'sessionId' => $sessionId,
    'sessionCode' => $sessionCode,
    'depot_manager_name' => $depotManagerName,
    'cache_keys' => [
        'session' => "depot_session_{$sessionId}",
        'code' => "depot_code_{$sessionCode}"
    ]
]);
```

#### Log Validation Code (Ligne 484-488)
```php
\Log::info("Code validation", [
    'code' => $code,
    'cache_key' => "depot_code_{$code}",
    'sessionId' => $sessionId
]);
```

#### Log Vérification Session (Ligne 497-501)
```php
\Log::info("Session check", [
    'sessionId' => $sessionId,
    'session_exists' => !is_null($session),
    'session_data' => $session
]);
```

---

### 2. Mise à Jour Statut (Problème AT_DEPOT)

#### Log Update Package (Ligne 397-403)
```php
\Log::info("Package updated to AT_DEPOT", [
    'package_id' => $package->id,
    'package_code' => $packageCode,
    'old_status' => $package->status,
    'new_status' => 'AT_DEPOT',
    'depot_manager_name' => $depotManagerName
]);
```

---

## 🧪 Comment Tester

### Test 1: Validation du Code

```bash
# 1. Créer une session PC
Aller sur: /depot/scan
Saisir nom: Omar
Noter le code affiché: ex 12345678

# 2. Vérifier les logs de création
tail -f storage/logs/laravel.log | grep "Session créée"

# Output attendu:
[2025-10-09 ...] local.INFO: Session créée {
    "sessionId": "9c8e4567-...",
    "sessionCode": "12345678",
    "depot_manager_name": "Omar",
    "cache_keys": {
        "session": "depot_session_9c8e4567-...",
        "code": "depot_code_12345678"
    }
}

# 3. Sur mobile, saisir le code
Mobile: /depot/enter-code
Saisir: 1-2-3-4-5-6-7-8
Cliquer: Valider

# 4. Vérifier les logs de validation
tail -f storage/logs/laravel.log | grep "Code validation"

# Output attendu:
[2025-10-09 ...] local.INFO: Code validation {
    "code": "12345678",
    "cache_key": "depot_code_12345678",
    "sessionId": "9c8e4567-..."  # Doit correspondre à la session créée
}

[2025-10-09 ...] local.INFO: Session check {
    "sessionId": "9c8e4567-...",
    "session_exists": true,
    "session_data": {
        "created_at": "...",
        "status": "waiting",
        "depot_manager_name": "Omar",
        ...
    }
}

# 5. Vérifier redirection
Si sessionId trouvé → Redirection vers /depot/scan/{sessionId}
Si sessionId null → Message d'erreur "Code invalide (Code: 12345678)"
```

---

### Test 2: Mise à Jour Statut AT_DEPOT

```bash
# 1. Scanner des colis
Mobile: Scanner 3 colis (PKG_001, PKG_002, PKG_003)

# 2. Valider
Mobile ou PC: Cliquer "Valider"

# 3. Vérifier les logs d'update
tail -f storage/logs/laravel.log | grep "Package updated to AT_DEPOT"

# Output attendu:
[2025-10-09 ...] local.INFO: Package updated to AT_DEPOT {
    "package_id": 123,
    "package_code": "PKG_001",
    "old_status": "CREATED",
    "new_status": "AT_DEPOT",
    "depot_manager_name": "Omar"
}

[2025-10-09 ...] local.INFO: Package updated to AT_DEPOT {
    "package_id": 124,
    "package_code": "PKG_002",
    "old_status": "PICKED_UP",
    "new_status": "AT_DEPOT",
    "depot_manager_name": "Omar"
}

# 4. Vérifier en base de données
php artisan tinker
>>> \DB::table('packages')->where('package_code', 'PKG_001')->first()

# Résultat attendu:
{
    "id": 123,
    "package_code": "PKG_001",
    "status": "AT_DEPOT",  # ✅ Statut changé
    "depot_manager_name": "Omar",  # ✅ Nom enregistré
    ...
}
```

---

## 🔧 Commandes de Débogage

### Voir les Logs en Temps Réel

```bash
# Tous les logs
tail -f storage/logs/laravel.log

# Filtrer session créée
tail -f storage/logs/laravel.log | grep "Session créée"

# Filtrer validation code
tail -f storage/logs/laravel.log | grep "Code validation"

# Filtrer update package
tail -f storage/logs/laravel.log | grep "Package updated"
```

---

### Vérifier le Cache

```bash
# Vérifier qu'une session existe
php artisan tinker
>>> Cache::get('depot_session_9c8e4567-...')  # Remplacer par vrai ID

# Vérifier qu'un code existe
>>> Cache::get('depot_code_12345678')  # Remplacer par vrai code

# Lister toutes les clés cache (si Redis)
>>> Redis::keys('depot_*')
```

---

### Vérifier Base de Données

```bash
php artisan tinker

# Vérifier un package
>>> \DB::table('packages')->where('package_code', 'PKG_001')->first()

# Vérifier tous les AT_DEPOT
>>> \DB::table('packages')->where('status', 'AT_DEPOT')->get()

# Vérifier avec nom manager
>>> \DB::table('packages')
    ->where('status', 'AT_DEPOT')
    ->whereNotNull('depot_manager_name')
    ->get(['package_code', 'status', 'depot_manager_name'])
```

---

## 🐛 Problèmes Possibles

### Problème 1: Code Invalide ou Expiré

**Symptôme:**
```
Message d'erreur: "Code invalide ou expiré (Code: 12345678)"
```

**Causes possibles:**

1. **Cache vide/expiré**
   ```bash
   # Vérifier
   php artisan tinker
   >>> Cache::get('depot_code_12345678')
   => null  # ❌ Code n'existe pas
   ```

   **Solution:** Recréer la session PC

2. **Mauvais driver de cache**
   ```bash
   # Vérifier .env
   CACHE_DRIVER=file  # ou redis, database

   # Si file, vérifier permissions
   ls -la storage/framework/cache/data/
   ```

   **Solution:** `php artisan cache:clear`

3. **Code pas créé**
   ```bash
   # Vérifier les logs
   tail storage/logs/laravel.log | grep "Session créée"

   # Si rien → generateSessionCode() a échoué
   ```

   **Solution:** Vérifier méthode `generateSessionCode()`

---

### Problème 2: Session Expirée

**Symptôme:**
```
Message d'erreur: "Session expirée. Veuillez demander un nouveau code."
```

**Causes possibles:**

1. **sessionId trouvé mais session null**
   ```bash
   php artisan tinker
   >>> Cache::get('depot_code_12345678')
   => "9c8e4567-..."  # ✅ sessionId existe

   >>> Cache::get('depot_session_9c8e4567-...')
   => null  # ❌ Session n'existe pas
   ```

   **Solution:** Bug de synchronisation - recréer session

2. **TTL expiré**
   ```bash
   # Vérifier TTL (si Redis)
   >>> Redis::ttl('depot_session_9c8e4567-...')
   => -2  # -2 = expiré, -1 = pas de TTL
   ```

   **Solution:** Augmenter TTL ou recréer session

---

### Problème 3: Statut Non Changé

**Symptôme:**
```
Validation effectuée mais status reste CREATED au lieu de AT_DEPOT
```

**Causes possibles:**

1. **Package déjà AT_DEPOT ou AVAILABLE**
   ```php
   // Ligne 388: Skip si déjà AT_DEPOT
   if (!in_array($package->status, ['AT_DEPOT', 'AVAILABLE'])) {
       // Update
   }
   ```

   **Vérifier:**
   ```bash
   >>> \DB::table('packages')
       ->where('package_code', 'PKG_001')
       ->value('status')
   => "AT_DEPOT"  # Déjà AT_DEPOT, pas de update
   ```

2. **Update échoue silencieusement**
   ```bash
   # Vérifier les logs
   tail storage/logs/laravel.log | grep "Package updated"

   # Si rien → Update pas exécuté
   ```

   **Solution:** Vérifier conditions if/else

3. **Champ depot_manager_name n'existe pas**
   ```bash
   php artisan migrate:status

   # Vérifier migration
   >>> Schema::hasColumn('packages', 'depot_manager_name')
   => false  # ❌ Colonne manquante
   ```

   **Solution:**
   ```bash
   php artisan migrate
   ```

---

## ✅ Checklist de Vérification

### Avant de Tester

- [ ] `php artisan cache:clear`
- [ ] `php artisan config:clear`
- [ ] Vérifier `.env` → `CACHE_DRIVER=file` ou `redis`
- [ ] Vérifier permissions `storage/`
- [ ] `php artisan migrate` (pour depot_manager_name)

### Créer Session PC

- [ ] Ouvrir `/depot/scan`
- [ ] Saisir nom chef (ex: Omar)
- [ ] Noter code 8 chiffres affiché
- [ ] Vérifier log "Session créée"
- [ ] Vérifier `sessionId` et `sessionCode` dans log

### Valider Code Mobile

- [ ] Ouvrir `/depot/enter-code`
- [ ] Saisir les 8 chiffres
- [ ] Cliquer "Valider"
- [ ] Vérifier log "Code validation"
- [ ] Vérifier log "Session check"
- [ ] Vérifier redirection `/depot/scan/{sessionId}`

### Scanner et Valider

- [ ] Scanner 2-3 colis
- [ ] Cliquer "Valider"
- [ ] Vérifier logs "Package updated to AT_DEPOT"
- [ ] Vérifier DB: `status = 'AT_DEPOT'`
- [ ] Vérifier DB: `depot_manager_name = 'Omar'`

---

## 📊 Logs Attendus (Workflow Complet)

```
# 1. Création Session PC
[2025-10-09 10:00:00] local.INFO: Session créée {
    "sessionId": "9c8e4567-e89b-12d3-a456-426614174000",
    "sessionCode": "12345678",
    "depot_manager_name": "Omar",
    "cache_keys": {
        "session": "depot_session_9c8e4567-e89b-12d3-a456-426614174000",
        "code": "depot_code_12345678"
    }
}

# 2. Validation Code Mobile
[2025-10-09 10:01:00] local.INFO: Code validation {
    "code": "12345678",
    "cache_key": "depot_code_12345678",
    "sessionId": "9c8e4567-e89b-12d3-a456-426614174000"
}

[2025-10-09 10:01:00] local.INFO: Session check {
    "sessionId": "9c8e4567-e89b-12d3-a456-426614174000",
    "session_exists": true,
    "session_data": {
        "created_at": "2025-10-09T10:00:00.000000Z",
        "status": "waiting",
        "scanned_packages": [],
        "depot_manager_name": "Omar",
        "session_code": "12345678"
    }
}

# 3. Update Packages
[2025-10-09 10:05:00] local.INFO: Package updated to AT_DEPOT {
    "package_id": 123,
    "package_code": "PKG_001",
    "old_status": "CREATED",
    "new_status": "AT_DEPOT",
    "depot_manager_name": "Omar"
}

[2025-10-09 10:05:00] local.INFO: Package updated to AT_DEPOT {
    "package_id": 124,
    "package_code": "PKG_002",
    "old_status": "PICKED_UP",
    "new_status": "AT_DEPOT",
    "depot_manager_name": "Omar"
}
```

---

## 🚨 Actions Immédiates

### Si Code Ne Fonctionne Pas

```bash
# 1. Vérifier création session
tail -f storage/logs/laravel.log | grep "Session créée"

# 2. Si rien, ouvrir PC et créer session
# Ouvrir: /depot/scan
# Saisir: Omar

# 3. Noter le code affiché (ex: 45678912)

# 4. Vérifier cache
php artisan tinker
>>> Cache::get('depot_code_45678912')
# Doit retourner un UUID

# 5. Tester validation
# Mobile: /depot/enter-code
# Saisir: 4-5-6-7-8-9-1-2
# Cliquer: Valider

# 6. Vérifier logs validation
tail -f storage/logs/laravel.log | grep "Code validation"
```

---

### Si Statut Ne Change Pas

```bash
# 1. Vérifier migration
php artisan migrate:status

# 2. Si migration manquante
php artisan migrate

# 3. Vérifier colonne existe
php artisan tinker
>>> Schema::hasColumn('packages', 'depot_manager_name')
# Doit retourner: true

# 4. Scanner un colis et valider

# 5. Vérifier logs update
tail -f storage/logs/laravel.log | grep "Package updated"

# 6. Vérifier en DB
>>> \DB::table('packages')
    ->where('package_code', 'PKG_001')
    ->first(['status', 'depot_manager_name'])
```

---

## ✅ Fichiers Modifiés avec Logs

- `app/Http/Controllers/DepotScanController.php`
  - Ligne 47-55: Log création session
  - Ligne 484-488: Log validation code
  - Ligne 497-501: Log vérification session
  - Ligne 397-403: Log update package

---

**🔍 Utilisez ces logs pour identifier exactement où le problème se produit!**
