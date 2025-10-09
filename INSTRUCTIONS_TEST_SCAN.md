# 📱 Instructions de Test - Scan QR Manager

## 🚀 Démarrage Rapide

### Option 1 : Test Automatique (Recommandé)

Exécutez le script PowerShell de test :

```powershell
cd C:\Users\DELL\OneDrive\Documents\GitHub\al-amena-delivery
.\test-scan-depot.ps1
```

Ce script va :
1. ✅ Vérifier les colis disponibles
2. ✅ Créer des colis de test si nécessaire
3. ✅ Tester la recherche d'un code
4. ✅ Vous donner les codes à utiliser pour tester

### Option 2 : Test Manuel

#### Étape 1 : Vérifier les colis disponibles

Ouvrez dans votre navigateur :
```
http://127.0.0.1:8000/depot/debug/packages
```

**Vous verrez** :
- Nombre total de colis
- Nombre de colis scannables
- Liste d'exemples de codes

**Notez** quelques codes de la liste `sample_packages`.

#### Étape 2 : Créer des colis de test (si besoin)

Si `scannable_packages` = 0, créez des colis de test :

**Via PowerShell** :
```powershell
Invoke-RestMethod -Uri "http://127.0.0.1:8000/depot/debug/create-test-packages" -Method POST
```

**Via navigateur** (avec extension REST) :
```
POST http://127.0.0.1:8000/depot/debug/create-test-packages
```

**Codes créés** :
- TEST_001
- TEST_002
- TEST-003
- TEST004
- test_005

#### Étape 3 : Tester un code spécifique

Testez si un code est trouvable :
```
http://127.0.0.1:8000/depot/debug/test-search?code=TEST_001
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

#### Étape 4 : Test du Scan Complet

1. **Sur PC** : Ouvrez `http://127.0.0.1:8000/depot/scan`
   - Un QR code s'affiche
   - Notez l'URL alternative en bas

2. **Sur Téléphone** :
   - Scannez le QR code AVEC l'appareil photo natif
   - OU ouvrez l'URL manuellement
   - L'interface de scan s'ouvre

3. **Scanner un colis** :
   - Tapez manuellement : `TEST_001`
   - OU scannez avec la caméra (si vous avez un code-barres)
   - Appuyez sur "Ajouter"

4. **Vérification** :
   - ✅ Le colis doit apparaître dans la liste
   - ✅ Le compteur doit augmenter
   - ✅ Sur le PC, la liste doit se mettre à jour automatiquement

5. **Validation** :
   - Cliquez sur "Valider Réception au Dépôt"
   - Confirmez
   - ✅ Les colis passent au statut `AVAILABLE`

## 🔍 Diagnostic des Problèmes

### Problème : "Colis non trouvé"

**Vérifications** :

1. **Le colis existe-t-il ?**
   ```
   http://127.0.0.1:8000/depot/debug/test-search?code=VOTRE_CODE
   ```

2. **Le colis a-t-il le bon statut ?**
   - Statuts acceptés : `CREATED`, `UNAVAILABLE`, `VERIFIED`
   - Autres statuts = non scannable

3. **Le code est-il exact ?**
   - Essayez avec/sans underscore
   - Essayez majuscules/minuscules
   - Le système teste automatiquement les variantes

### Problème : "Session expirée"

**Solution** :
- Régénérez une nouvelle session sur `/depot/scan`
- Scannez le nouveau QR code

### Problème : Le PC ne se met pas à jour

**Vérifications** :
1. Vérifiez la console du navigateur (F12)
2. Vérifiez que le polling fonctionne (requêtes toutes les secondes)
3. Rechargez la page

## 📊 Codes de Test Disponibles

Après avoir exécuté `create-test-packages`, vous pouvez utiliser :

| Code | Format | Variantes acceptées |
|------|--------|---------------------|
| TEST_001 | Avec underscore | TEST_001, TEST001, test_001, test001 |
| TEST_002 | Avec underscore | TEST_002, TEST002, test_002, test002 |
| TEST-003 | Avec tiret | TEST-003, TEST003, test-003, test003 |
| TEST004 | Sans séparateur | TEST004, test004 |
| test_005 | Minuscules | test_005, TEST_005, test005, TEST005 |

**Tous ces formats devraient fonctionner !**

## 🎯 Scénarios de Test

### Scénario 1 : Scan Basique

1. Créer des colis de test
2. Scanner TEST_001
3. Scanner TEST_002
4. Valider
5. ✅ Vérifier que les 2 colis sont passés à AVAILABLE

### Scénario 2 : Variantes de Code

1. Scanner `TEST_001` (avec underscore)
2. Scanner `TEST002` (sans underscore)
3. Scanner `test-003` (minuscules avec tiret)
4. ✅ Tous doivent être trouvés

### Scénario 3 : Détection de Doublons

1. Scanner TEST_001
2. Essayer de scanner TEST_001 à nouveau
3. ✅ Doit afficher "Déjà scanné"

### Scénario 4 : Statut Invalide

1. Changer manuellement le statut d'un colis à `DELIVERED`
2. Essayer de le scanner
3. ✅ Doit être rejeté (statut non scannable)

## 🛠️ Commandes Utiles

### Voir tous les colis
```sql
SELECT id, package_code, status FROM packages LIMIT 20;
```

### Créer un colis manuellement
```sql
INSERT INTO packages (package_code, sender_id, status, cod_amount, delivery_fee, return_fee, delivery_attempts, created_at, updated_at)
VALUES ('MON_COLIS_001', 1, 'CREATED', 0, 0, 0, 0, NOW(), NOW());
```

### Réinitialiser les statuts de test
```sql
UPDATE packages 
SET status = 'CREATED' 
WHERE package_code LIKE 'TEST%';
```

### Supprimer les colis de test
```sql
DELETE FROM packages WHERE package_code LIKE 'TEST%';
```

## 📝 Checklist de Validation

Avant de considérer le système comme fonctionnel :

- [ ] Les colis de test sont créés
- [ ] La recherche de code fonctionne (`/depot/debug/test-search`)
- [ ] Le QR code se génère correctement
- [ ] Le téléphone peut accéder à l'interface de scan
- [ ] Les colis scannés apparaissent dans la liste
- [ ] Le PC se met à jour automatiquement
- [ ] La validation change le statut à `AVAILABLE`
- [ ] Les doublons sont détectés
- [ ] Les variantes de code fonctionnent

## 🔒 Nettoyage (Production)

Avant de déployer en production, **SUPPRIMEZ** les routes de debug :

Dans `routes/depot.php`, commentez ou supprimez :
```php
// Route::prefix('depot/debug')->group(function () {
//     ...
// });
```

OU protégez-les :
```php
Route::prefix('depot/debug')
    ->middleware(['auth', 'role:SUPERVISOR'])
    ->group(function () {
        // ... routes de debug
    });
```

## 📞 Support

Si le problème persiste après tous ces tests :

1. Vérifiez les logs : `storage/logs/laravel.log`
2. Vérifiez la console navigateur (F12)
3. Vérifiez que le serveur Laravel tourne : `php artisan serve`
4. Vérifiez la base de données SQLite existe et est accessible

---

**Bonne chance avec les tests ! 🚀**
