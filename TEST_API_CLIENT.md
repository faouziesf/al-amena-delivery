# 🧪 TESTS API CLIENT - GUIDE PRATIQUE

**Date** : 24 Octobre 2025

---

## 🚀 DÉMARRAGE RAPIDE

### 1. Exécuter les Migrations

```bash
php artisan migrate
```

### 2. Clear Cache

```bash
php artisan optimize:clear
```

### 3. Lancer le Serveur

```bash
php artisan serve
```

---

## 👤 PRÉPARER UN CLIENT TEST

### Option A : Utiliser un Client Existant

1. Connectez-vous avec un compte client existant
2. Accédez à `/client/settings/api`
3. Générez un token

### Option B : Créer un Nouveau Client via Tinker

```bash
php artisan tinker
```

```php
$user = new App\Models\User();
$user->name = "Test API Client";
$user->email = "api-test@example.com";
$user->password = bcrypt('password123');
$user->role = 'CLIENT';
$user->status = 'VERIFIED';
$user->phone = '12345678';
$user->save();

// Créer une adresse de ramassage
$address = new App\Models\ClientPickupAddress();
$address->client_id = $user->id;
$address->name = "Adresse Test";
$address->address = "123 Test Street";
$address->gouvernorat = "Tunis";
$address->delegation = "La Marsa";
$address->phone = "12345678";
$address->save();

echo "Client ID: " . $user->id . "\n";
echo "Pickup Address ID: " . $address->id . "\n";
```

---

## 🔑 GÉNÉRER UN TOKEN

### Via l'Interface Web

1. Connectez-vous : http://localhost:8000/login
2. Allez sur : http://localhost:8000/client/settings/api
3. Cliquez "Générer Mon Token"
4. Copiez le token

### Via API (si déjà connecté)

```bash
curl -X POST http://localhost:8000/client/settings/api/token/generate \
  -H "Cookie: laravel_session=YOUR_SESSION" \
  -H "X-CSRF-TOKEN: YOUR_CSRF_TOKEN"
```

**Conservez le token** : `alamena_test_xxxxxxxxxxxxxx`

---

## 📦 TESTS DES ENDPOINTS

### Variables d'Environnement

```bash
# Définir vos variables
export API_TOKEN="alamena_test_votre_token_ici"
export API_URL="http://localhost:8000/api/v1"
export PICKUP_ID="1"  # Remplacer par votre ID
```

---

### TEST 1 : Créer un Colis

```bash
curl -X POST $API_URL/client/packages \
  -H "Authorization: Bearer $API_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "packages": [{
      "pickup_address_id": 1,
      "recipient_name": "Ahmed Ben Ali",
      "recipient_phone": "21234567",
      "recipient_gouvernorat": "Tunis",
      "recipient_delegation": "La Marsa",
      "recipient_address": "123 Avenue Habib Bourguiba",
      "package_content": "Vêtements",
      "package_price": 150.00,
      "delivery_type": "HOME",
      "payment_type": "COD",
      "cod_amount": 150.00,
      "external_reference": "TEST-001"
    }]
  }'
```

**Réponse Attendue** : `201 Created` avec tracking number

**Vérifier** :
```bash
# Dans la BDD
php artisan tinker
App\Models\Package::latest()->first();
```

---

### TEST 2 : Créer Plusieurs Colis

```bash
curl -X POST $API_URL/client/packages \
  -H "Authorization: Bearer $API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "packages": [
      {
        "pickup_address_id": 1,
        "recipient_name": "Client 1",
        "recipient_phone": "11111111",
        "recipient_gouvernorat": "Tunis",
        "recipient_delegation": "Carthage",
        "recipient_address": "Address 1",
        "package_content": "Item 1",
        "package_price": 100.00,
        "delivery_type": "HOME",
        "payment_type": "PREPAID"
      },
      {
        "pickup_address_id": 1,
        "recipient_name": "Client 2",
        "recipient_phone": "22222222",
        "recipient_gouvernorat": "Sousse",
        "recipient_delegation": "Sousse Ville",
        "recipient_address": "Address 2",
        "package_content": "Item 2",
        "package_price": 200.00,
        "delivery_type": "HOME",
        "payment_type": "COD",
        "cod_amount": 200.00
      }
    ]
  }'
```

---

### TEST 3 : Lister Tous les Colis

```bash
curl -X GET "$API_URL/client/packages" \
  -H "Authorization: Bearer $API_TOKEN" \
  -H "Accept: application/json"
```

**Avec Pagination** :
```bash
curl -X GET "$API_URL/client/packages?per_page=10&page=1" \
  -H "Authorization: Bearer $API_TOKEN"
```

---

### TEST 4 : Filtrer par Statut

```bash
curl -X GET "$API_URL/client/packages?status=CREATED" \
  -H "Authorization: Bearer $API_TOKEN"
```

---

### TEST 5 : Filtrer par Date

```bash
curl -X GET "$API_URL/client/packages?date_from=2025-10-01&date_to=2025-10-31" \
  -H "Authorization: Bearer $API_TOKEN"
```

---

### TEST 6 : Rechercher par Tracking Number

```bash
# Remplacer PKG_XXX par un vrai tracking number
curl -X GET "$API_URL/client/packages?tracking_number=PKG_A1B2C3_0001" \
  -H "Authorization: Bearer $API_TOKEN"
```

---

### TEST 7 : Détail d'un Colis

```bash
# Remplacer par un tracking number réel
curl -X GET "$API_URL/client/packages/PKG_A1B2C3_0001" \
  -H "Authorization: Bearer $API_TOKEN"
```

---

### TEST 8 : Statistiques

```bash
curl -X GET "$API_URL/client/stats" \
  -H "Authorization: Bearer $API_TOKEN"
```

---

### TEST 9 : Générer Étiquettes PDF

```bash
curl -X POST "$API_URL/client/packages/labels" \
  -H "Authorization: Bearer $API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "tracking_numbers": [
      "PKG_A1B2C3_0001",
      "PKG_D4E5F6_0002"
    ]
  }' \
  --output etiquettes.pdf
```

---

## ❌ TESTS D'ERREUR

### TEST E1 : Sans Token

```bash
curl -X GET "$API_URL/client/packages"
```

**Attendu** : `401 Unauthorized` - "Token API manquant"

---

### TEST E2 : Token Invalide

```bash
curl -X GET "$API_URL/client/packages" \
  -H "Authorization: Bearer token_invalide_123"
```

**Attendu** : `401 Unauthorized` - "Token API invalide"

---

### TEST E3 : Validation - Téléphone Invalide

```bash
curl -X POST $API_URL/client/packages \
  -H "Authorization: Bearer $API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "packages": [{
      "pickup_address_id": 1,
      "recipient_name": "Test",
      "recipient_phone": "123",
      "recipient_gouvernorat": "Tunis",
      "recipient_delegation": "La Marsa",
      "recipient_address": "Address",
      "package_content": "Content",
      "package_price": 100,
      "delivery_type": "HOME",
      "payment_type": "COD",
      "cod_amount": 100
    }]
  }'
```

**Attendu** : `422 Validation Error` - "Le téléphone doit contenir exactement 8 chiffres"

---

### TEST E4 : Validation - COD Sans Montant

```bash
curl -X POST $API_URL/client/packages \
  -H "Authorization: Bearer $API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "packages": [{
      "pickup_address_id": 1,
      "recipient_name": "Test",
      "recipient_phone": "12345678",
      "recipient_gouvernorat": "Tunis",
      "recipient_delegation": "La Marsa",
      "recipient_address": "Address",
      "package_content": "Content",
      "package_price": 100,
      "delivery_type": "HOME",
      "payment_type": "COD"
    }]
  }'
```

**Attendu** : `422 Validation Error` - "Le montant COD est requis"

---

### TEST E5 : Rate Limiting

```bash
# Exécuter plus de 60 requêtes en 1 minute
for i in {1..65}; do
  curl -X POST $API_URL/client/packages \
    -H "Authorization: Bearer $API_TOKEN" \
    -H "Content-Type: application/json" \
    -d '{"packages":[{"pickup_address_id":1,"recipient_name":"Test","recipient_phone":"12345678","recipient_gouvernorat":"Tunis","recipient_delegation":"La Marsa","recipient_address":"Addr","package_content":"C","package_price":10,"delivery_type":"HOME","payment_type":"PREPAID"}]}'
  echo "Request $i"
done
```

**Attendu** : Après 60 requêtes → `429 Too Many Requests`

---

## 📊 VÉRIFICATIONS

### Vérifier les Logs API

```bash
php artisan tinker
```

```php
// Logs d'aujourd'hui
App\Models\ApiLog::whereDate('created_at', today())->count();

// Derniers 10 logs
App\Models\ApiLog::latest()->limit(10)->get();

// Logs par endpoint
App\Models\ApiLog::where('endpoint', 'api/v1/client/packages')
    ->where('method', 'POST')
    ->count();
```

---

### Vérifier le Token

```php
// Token du client
$user = App\Models\User::find(1);
$token = App\Models\ApiToken::where('user_id', $user->id)->first();

echo "Token: " . $token->token . "\n";
echo "Dernière utilisation: " . $token->last_used_at . "\n";
```

---

### Vérifier les Statistiques

```php
$stats = App\Models\ApiLog::getStatsForUser(1);
print_r($stats);
```

---

## 🐛 DEBUGGING

### Activer le Debug Laravel

Dans `.env` :
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Logs Laravel

```bash
tail -f storage/logs/laravel.log
```

### Tester avec Postman

1. Importer la collection (créer un fichier JSON)
2. Configurer la variable `{{api_token}}`
3. Tester tous les endpoints

---

## ✅ CHECKLIST DE TEST

### Fonctionnalités
- [ ] Génération token via interface
- [ ] Copie token
- [ ] Régénération token
- [ ] Suppression token
- [ ] Affichage statistiques

### Endpoints
- [ ] POST /packages - 1 colis
- [ ] POST /packages - Multiple colis
- [ ] GET /packages - Liste complète
- [ ] GET /packages - Avec filtres
- [ ] GET /packages/{tracking} - Détail
- [ ] GET /stats - Statistiques
- [ ] POST /packages/labels - PDF

### Sécurité
- [ ] Auth sans token refuse
- [ ] Auth token invalide refuse
- [ ] Rate limiting fonctionne
- [ ] Logs enregistrés
- [ ] Isolation données (client ne voit que ses colis)

### Validation
- [ ] Téléphone 8 chiffres
- [ ] COD amount requis si COD
- [ ] Adresse ramassage valide
- [ ] Max 100 colis par requête

---

## 📈 PERFORMANCE

### Tester le Temps de Réponse

```bash
time curl -X GET "$API_URL/client/packages" \
  -H "Authorization: Bearer $API_TOKEN"
```

**Objectif** : < 200ms pour liste, < 500ms pour création

---

## 🎉 SUCCÈS

Si tous les tests passent :
✅ **L'API Client est opérationnelle !**

Prochaine étape : **Déploiement en production**
