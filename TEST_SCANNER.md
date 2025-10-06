# 🧪 Guide de Test - Système de Scan

## 📍 URLs à Tester

### 1. Scanner Simple (Scan Unique)
**URL** : `http://localhost:8000/deliverer/scan`

**Test à effectuer** :
1. Ouvrir la page
2. Autoriser l'accès à la caméra
3. Scanner un QR code ou code-barres d'un colis
4. **Résultat attendu** : Redirection immédiate vers `/deliverer/task/{id}`

**Codes de test** :
```
PKG_001
PKG_002
https://domain.com/track/PKG_003
```

---

### 2. Scanner Multiple
**URL** : `http://localhost:8000/deliverer/scan/multi`

#### Test Scenario 1 : Pickup chez Fournisseur
1. Cliquer sur "Pickup chez Fournisseur"
2. Scanner/Saisir des codes de colis avec statut AVAILABLE ou CREATED
3. Chaque scan valide doit :
   - ✅ Ajouter le colis à la liste
   - ✅ Jouer un son de succès
   - ✅ Afficher un toast vert
4. Scanner le même colis 2 fois :
   - ❌ Doit afficher "Colis déjà ajouté à la liste"
   - ❌ Jouer un son d'erreur
5. Scanner un colis avec statut PICKED_UP :
   - ❌ Doit afficher "Statut erroné. Pour pickup, le colis doit être AVAILABLE ou CREATED"
6. Cliquer sur "Valider (X colis)"
7. **Résultat attendu** : Tous les colis passent à statut PICKED_UP

#### Test Scenario 2 : Prêt pour Livraison
1. Cliquer sur "Prêt pour Livraison"
2. Scanner des colis avec n'importe quel statut sauf DELIVERED et PAID
3. Chaque scan valide doit être ajouté
4. Scanner un colis DELIVERED :
   - ❌ Doit afficher "Statut erroné. Ce colis est déjà livré"
5. Valider → Tous passent à PICKED_UP

---

### 3. Scanner Collecte
**URL** : `http://localhost:8000/deliverer/pickups/scan`

**Test à effectuer** :
1. Scanner un QR code de demande de collecte
2. **Résultat attendu** : Redirection vers `/deliverer/pickups`

---

## 🛠️ Commandes Utiles

### Créer des colis de test
```php
php artisan tinker

// Créer un colis AVAILABLE
$package = new App\Models\Package();
$package->package_code = 'PKG_TEST_001';
$package->tracking_number = 'PKG_TEST_001';
$package->status = 'AVAILABLE';
$package->client_id = 1; // Ajuster selon votre DB
$package->recipient_name = 'Test User';
$package->recipient_address = 'Adresse Test';
$package->recipient_phone = '12345678';
$package->recipient_city = 'Tunis';
$package->cod_amount = 50.000;
$package->save();

// Créer un colis CREATED
$package2 = new App\Models\Package();
$package2->package_code = 'PKG_TEST_002';
$package2->tracking_number = 'PKG_TEST_002';
$package2->status = 'CREATED';
$package2->client_id = 1;
$package2->recipient_name = 'Test User 2';
$package2->recipient_address = 'Adresse Test 2';
$package2->recipient_phone = '87654321';
$package2->recipient_city = 'Sfax';
$package2->cod_amount = 75.000;
$package2->save();

// Créer un colis DELIVERED (pour tester le refus)
$package3 = new App\Models\Package();
$package3->package_code = 'PKG_TEST_003';
$package3->tracking_number = 'PKG_TEST_003';
$package3->status = 'DELIVERED';
$package3->client_id = 1;
$package3->recipient_name = 'Test User 3';
$package3->recipient_address = 'Adresse Test 3';
$package3->recipient_phone = '11111111';
$package3->recipient_city = 'Sousse';
$package3->cod_amount = 100.000;
$package3->save();
```

### Vérifier les routes
```bash
php artisan route:list --path=deliverer/scan
php artisan route:list --path=deliverer/pickups
```

### Nettoyer le cache
```bash
php artisan route:clear
php artisan view:clear
php artisan config:clear
```

---

## 🎯 Checklist de Vérification

### Scanner Simple ✅
- [ ] La caméra s'active correctement
- [ ] Les QR codes sont détectés
- [ ] Les codes-barres sont détectés
- [ ] Redirection immédiate après scan valide
- [ ] Message d'erreur pour code invalide
- [ ] Saisie manuelle fonctionne
- [ ] Délai de 2 secondes entre scans respecté

### Scanner Multiple ✅
- [ ] Choix de l'action s'affiche
- [ ] Mode Pickup : accepte AVAILABLE et CREATED
- [ ] Mode Pickup : refuse les autres statuts
- [ ] Mode Livraison : accepte tous sauf DELIVERED et PAID
- [ ] Détection des doublons fonctionne
- [ ] Son de succès joue correctement
- [ ] Son d'erreur joue correctement
- [ ] Toast notifications s'affichent
- [ ] Liste des colis s'affiche correctement
- [ ] Suppression d'un colis de la liste fonctionne
- [ ] Validation change les statuts correctement
- [ ] Redirection vers Run Sheet après validation

### Scanner Collecte ✅
- [ ] Caméra fonctionne
- [ ] QR codes détectés
- [ ] Saisie manuelle fonctionne
- [ ] Redirection après scan

---

## 🐛 Problèmes Courants et Solutions

### Problème : "Impossible d'accéder à la caméra"
**Solutions** :
1. Vérifier que l'application est en HTTPS (requis pour caméra sur mobile)
2. Vérifier les permissions du navigateur
3. Utiliser la saisie manuelle en attendant

### Problème : "Code non reconnu"
**Solutions** :
1. Vérifier que le colis existe dans la base de données
2. Vérifier que le `package_code` ou `tracking_number` correspond
3. Tester avec la saisie manuelle pour voir l'erreur exacte

### Problème : "Statut erroné"
**Solutions** :
1. Vérifier le statut du colis dans la base de données
2. S'assurer d'avoir choisi la bonne action (Pickup vs Livraison)
3. Consulter le message d'erreur pour le statut actuel

---

## 📊 Matrice de Validation des Statuts

| Statut Colis | Pickup Fournisseur | Prêt Livraison |
|--------------|-------------------|----------------|
| CREATED      | ✅ Accepté        | ✅ Accepté     |
| AVAILABLE    | ✅ Accepté        | ✅ Accepté     |
| ACCEPTED     | ❌ Refusé         | ✅ Accepté     |
| PICKED_UP    | ❌ Refusé         | ✅ Accepté     |
| IN_TRANSIT   | ❌ Refusé         | ✅ Accepté     |
| DELIVERED    | ❌ Refusé         | ❌ Refusé      |
| PAID         | ❌ Refusé         | ❌ Refusé      |
| CANCELLED    | ❌ Refusé         | ✅ Accepté     |
| RETURNED     | ❌ Refusé         | ✅ Accepté     |

---

## 📹 Enregistrement des Tests

**Recommandation** : Enregistrer une vidéo de chaque scénario de test pour documentation.

**Points à capturer** :
1. Ouverture de la page
2. Activation de la caméra
3. Scan d'un code valide
4. Affichage du feedback (toast, son)
5. Résultat final (redirection ou ajout à la liste)

---

**Dernière mise à jour** : 2025-01-06
