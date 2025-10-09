# 🌐 Guide Complet - Scan Dépôt avec Ngrok

## 🎯 Objectif

Permettre au manager de scanner des colis depuis son téléphone via ngrok, avec le PC comme serveur central.

## 📋 Prérequis

1. ✅ Ngrok installé sur votre PC
2. ✅ Laravel en cours d'exécution (`php artisan serve`)
3. ✅ Téléphone connecté à Internet

## 🚀 Configuration Étape par Étape

### Étape 1 : Démarrer Laravel

```powershell
cd C:\Users\DELL\OneDrive\Documents\GitHub\al-amena-delivery
php artisan serve
```

**Résultat attendu** :
```
Server running on [http://127.0.0.1:8000]
```

### Étape 2 : Démarrer Ngrok

Dans un **nouveau terminal PowerShell** :

```powershell
ngrok http 8000
```

**Résultat attendu** :
```
Forwarding  https://xxxx-xxxx-xxxx.ngrok-free.app -> http://localhost:8000
```

**IMPORTANT** : Notez l'URL ngrok (ex: `https://abc123.ngrok-free.app`)

### Étape 3 : Configurer l'URL dans .env

Ouvrez `.env` et modifiez :

```env
APP_URL=https://votre-url-ngrok.ngrok-free.app
```

**Exemple** :
```env
APP_URL=https://abc123.ngrok-free.app
```

### Étape 4 : Vider le cache Laravel

```powershell
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Étape 5 : Tester l'accès

**Sur PC** :
```
https://votre-url-ngrok.ngrok-free.app/depot/scan
```

**Sur Téléphone** :
```
https://votre-url-ngrok.ngrok-free.app/depot/scan
```

## 🧪 Test Complet du Scan

### Test 1 : Créer des Colis de Test

**Via navigateur** (PC ou téléphone) :
```
https://votre-url-ngrok.ngrok-free.app/depot/debug/create-test-packages
```

**Via PowerShell** :
```powershell
$ngrokUrl = "https://votre-url-ngrok.ngrok-free.app"
Invoke-RestMethod -Uri "$ngrokUrl/depot/debug/create-test-packages" -Method POST
```

**Résultat** : 5 colis de test créés (TEST_001, TEST_002, etc.)

### Test 2 : Vérifier les Colis Disponibles

**Via navigateur** :
```
https://votre-url-ngrok.ngrok-free.app/depot/debug/packages
```

**Résultat attendu** :
```json
{
  "total_packages": 5,
  "scannable_packages": 5,
  "sample_packages": [
    {"package_code": "TEST_001", "status": "CREATED"},
    ...
  ]
}
```

### Test 3 : Tester la Recherche

**Via navigateur** :
```
https://votre-url-ngrok.ngrok-free.app/depot/debug/test-search?code=TEST_001
```

**Résultat attendu** :
```json
{
  "searched_code": "TEST_001",
  "variants_tested": {
    "TEST_001": {
      "found": true,
      "package_code": "TEST_001",
      "status": "CREATED",
      "scannable": true
    }
  }
}
```

### Test 4 : Scan Complet PC → Téléphone

#### Sur PC :

1. Ouvrez : `https://votre-url-ngrok.ngrok-free.app/depot/scan`
2. Un QR code s'affiche
3. Notez l'URL alternative en bas

#### Sur Téléphone :

1. **Option A** : Scannez le QR code avec l'appareil photo natif
2. **Option B** : Ouvrez manuellement l'URL du QR code
3. L'interface de scan s'ouvre

#### Scanner un Colis :

1. Tapez `TEST_001` dans le champ
2. Attendez la validation (✅ Colis valide)
3. Cliquez sur "Ajouter"
4. ✅ Le colis apparaît dans la liste

#### Vérification PC :

1. La liste sur le PC se met à jour automatiquement
2. Le compteur augmente
3. Le colis apparaît dans la liste

#### Validation :

1. Cliquez sur "Valider Réception au Dépôt"
2. Confirmez
3. ✅ Les colis passent au statut `AVAILABLE`

## 🔧 Corrections Appliquées

### 1. Middleware Ngrok Activé

**Fichier** : `routes/depot.php`

Toutes les routes depot sont maintenant dans un groupe avec middleware `ngrok.cors` :

```php
Route::middleware(['ngrok.cors'])->group(function () {
    // Toutes les routes depot
});
```

### 2. Headers CORS Configurés

**Fichier** : `app/Http/Middleware/NgrokCorsMiddleware.php`

Headers ajoutés :
- `Access-Control-Allow-Origin: *`
- `Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS`
- `Access-Control-Allow-Headers: Content-Type, X-CSRF-TOKEN, ...`
- `ngrok-skip-browser-warning: true` (évite l'avertissement ngrok)

### 3. Recherche Intelligente

**Fichier** : `app/Http/Controllers/DepotScanController.php`

Méthode `addScannedCode()` améliorée :
- Recherche par `package_code` uniquement
- Support de 6+ variantes de format
- Messages de debug détaillés

## 🐛 Résolution des Problèmes

### Problème : "Colis non trouvé"

**Solutions** :

1. **Vérifier que des colis existent** :
   ```
   https://votre-url-ngrok.ngrok-free.app/depot/debug/packages
   ```

2. **Créer des colis de test** :
   ```
   https://votre-url-ngrok.ngrok-free.app/depot/debug/create-test-packages
   ```

3. **Tester un code spécifique** :
   ```
   https://votre-url-ngrok.ngrok-free.app/depot/debug/test-search?code=TEST_001
   ```

4. **Vérifier le statut** :
   - Seuls les colis avec statut `CREATED`, `UNAVAILABLE`, `VERIFIED` sont scannables

### Problème : "Session expirée"

**Solutions** :

1. Régénérez une nouvelle session :
   ```
   https://votre-url-ngrok.ngrok-free.app/depot/scan
   ```

2. Scannez le nouveau QR code

### Problème : "Erreur CORS" ou "Blocked by CORS policy"

**Solutions** :

1. **Vérifier que le middleware est actif** :
   - Vérifiez `routes/depot.php` ligne 18
   - Doit contenir : `Route::middleware(['ngrok.cors'])->group(...)`

2. **Vider le cache** :
   ```powershell
   php artisan config:clear
   php artisan route:clear
   php artisan cache:clear
   ```

3. **Redémarrer Laravel** :
   ```powershell
   # Arrêter (Ctrl+C)
   php artisan serve
   ```

### Problème : "ngrok warning page"

**Solutions** :

1. Le header `ngrok-skip-browser-warning: true` est déjà configuré

2. Si le problème persiste, ajoutez manuellement dans les requêtes fetch :
   ```javascript
   headers: {
       'ngrok-skip-browser-warning': 'true'
   }
   ```

### Problème : Le PC ne se met pas à jour

**Solutions** :

1. **Vérifier la console du navigateur** (F12)
2. **Vérifier que le polling fonctionne** :
   - Doit faire des requêtes toutes les secondes
   - URL : `/depot/api/session/{sessionId}/status`

3. **Recharger la page PC**

## 📊 Architecture du Système

```
┌─────────────┐
│     PC      │
│  (Serveur)  │
│  Laravel    │
└──────┬──────┘
       │
       │ php artisan serve
       │ (localhost:8000)
       │
┌──────▼──────┐
│    Ngrok    │
│   Tunnel    │
└──────┬──────┘
       │
       │ https://xxxx.ngrok-free.app
       │
┌──────▼──────┐
│  Téléphone  │
│  (Scanner)  │
└─────────────┘
```

### Flux de Données

```
1. PC génère QR code avec URL ngrok
   ↓
2. Téléphone scanne QR code
   ↓
3. Téléphone ouvre URL ngrok
   ↓
4. Ngrok redirige vers Laravel (localhost:8000)
   ↓
5. Laravel charge les colis en mémoire
   ↓
6. Téléphone scanne un colis
   ↓
7. POST /depot/scan/{sessionId}/add (via ngrok)
   ↓
8. Laravel valide et stocke dans cache
   ↓
9. PC polling GET /depot/api/session/{sessionId}/status
   ↓
10. PC affiche le colis scanné
```

## 🔒 Sécurité

### En Développement

- ✅ Ngrok gratuit suffit
- ✅ URLs temporaires (changent à chaque redémarrage)
- ✅ Pas de configuration DNS nécessaire

### En Production

**NE PAS utiliser ngrok gratuit en production !**

**Alternatives** :
1. **Ngrok payant** avec domaine personnalisé
2. **VPN** entre PC et téléphone
3. **Serveur cloud** (AWS, DigitalOcean, etc.)
4. **Réseau local** (WiFi même réseau)

## 📝 Checklist de Validation

- [ ] Laravel démarre sur `http://127.0.0.1:8000`
- [ ] Ngrok démarre et affiche l'URL
- [ ] `.env` mis à jour avec l'URL ngrok
- [ ] Cache Laravel vidé
- [ ] PC accède à l'URL ngrok
- [ ] Téléphone accède à l'URL ngrok
- [ ] Colis de test créés
- [ ] Recherche de code fonctionne
- [ ] QR code généré correctement
- [ ] Téléphone scanne le QR code
- [ ] Interface de scan s'ouvre
- [ ] Scan manuel fonctionne
- [ ] Colis apparaît dans la liste
- [ ] PC se met à jour automatiquement
- [ ] Validation fonctionne
- [ ] Statut change à `AVAILABLE`

## 🎯 Commandes Rapides

### Démarrage Complet

```powershell
# Terminal 1 : Laravel
cd C:\Users\DELL\OneDrive\Documents\GitHub\al-amena-delivery
php artisan serve

# Terminal 2 : Ngrok
ngrok http 8000

# Terminal 3 : Configuration
$ngrokUrl = "https://VOTRE-URL.ngrok-free.app"  # Remplacer par votre URL

# Mettre à jour .env (manuellement)
# APP_URL=https://VOTRE-URL.ngrok-free.app

# Vider cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Créer colis de test
Invoke-RestMethod -Uri "$ngrokUrl/depot/debug/create-test-packages" -Method POST

# Ouvrir dans navigateur
Start-Process "$ngrokUrl/depot/scan"
```

### Vérification Rapide

```powershell
$ngrokUrl = "https://VOTRE-URL.ngrok-free.app"

# Voir colis
Invoke-RestMethod -Uri "$ngrokUrl/depot/debug/packages" -Method GET

# Tester code
Invoke-RestMethod -Uri "$ngrokUrl/depot/debug/test-search?code=TEST_001" -Method GET
```

## 📞 Support

Si le problème persiste :

1. Vérifiez les logs : `storage/logs/laravel.log`
2. Vérifiez la console navigateur (F12)
3. Vérifiez que ngrok est actif
4. Vérifiez que l'URL ngrok dans `.env` est correcte
5. Redémarrez tout (Laravel + Ngrok)

---

**Date** : 2025-10-09  
**Version** : 3.0 - Solution Ngrok Complète  
**Statut** : ✅ Prêt pour tests avec ngrok
