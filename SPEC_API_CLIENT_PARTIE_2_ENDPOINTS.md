# 🔐 SPÉCIFICATION API CLIENT - PARTIE 2 : ENDPOINTS API

**Version** : 1.0  
**Date** : 24 Octobre 2025

---

## 🌐 ARCHITECTURE

### **Base URL**

```
Production : https://api.al-amena.tn/v1
Sandbox :    https://sandbox.al-amena.tn/v1
```

### **Authentification**

**Méthode** : Bearer Token

**Header** :
```http
Authorization: Bearer alamena_live_a3f8d9c7b2e1f4a6d8c9b3e7f1a2d5c8e9f7b3a1d4c6
```

**Format Token** :
```
alamena_{env}_{64_chars}

Exemples :
- alamena_live_a3f8d9c7b2e1f4a6d8c9b3e7f1a2d5c8e9f7b3a1d4c6
- alamena_test_x7y9z2w1q3e5r7t9u2i4o6p8a1s3d5f7g9h2j4
```

---

## 📦 ENDPOINTS

### **1. Créer des Colis**

#### **POST /api/v1/client/packages**

**Description** : Crée un ou plusieurs colis

**Headers** :
```http
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json
Accept: application/json
```

**Rate Limit** : 120 requêtes/minute

**Request Body** :

```json
{
  "packages": [
    {
      "pickup_address_id": 12,
      "recipient_name": "Ahmed Ben Ali",
      "recipient_phone": "21234567",
      "recipient_phone_2": "98765432",
      "recipient_gouvernorat": "Tunis",
      "recipient_delegation": "La Marsa",
      "recipient_address": "123 Avenue Habib Bourguiba, Résidence Essaada",
      "package_content": "Vêtements",
      "package_price": 150.00,
      "cod_amount": 150.00,
      "delivery_type": "HOME",
      "payment_type": "COD",
      "is_fragile": false,
      "is_exchange": false,
      "comment": "Livrer entre 14h-18h"
    }
  ]
}
```

**Champs Obligatoires** :
- `pickup_address_id` (integer) - ID adresse ramassage
- `recipient_name` (string, max 100)
- `recipient_phone` (string, 8 chiffres)
- `recipient_gouvernorat` (string)
- `recipient_delegation` (string)
- `recipient_address` (string, max 255)
- `package_content` (string, max 100)
- `package_price` (decimal, 2 décimales)
- `delivery_type` (enum: HOME, STOP_DESK)
- `payment_type` (enum: COD, PREPAID)

**Champs Optionnels** :
- `recipient_phone_2` (string, 8 chiffres)
- `cod_amount` (decimal) - Si payment_type = COD
- `is_fragile` (boolean, default: false)
- `is_exchange` (boolean, default: false)
- `comment` (string, max 500)

**Validation** :
```php
'packages' => 'required|array|min:1|max:100',
'packages.*.pickup_address_id' => 'required|exists:client_pickup_addresses,id',
'packages.*.recipient_name' => 'required|string|max:100',
'packages.*.recipient_phone' => 'required|regex:/^[0-9]{8}$/',
'packages.*.recipient_phone_2' => 'nullable|regex:/^[0-9]{8}$/',
'packages.*.recipient_gouvernorat' => 'required|string',
'packages.*.recipient_delegation' => 'required|string',
'packages.*.recipient_address' => 'required|string|max:255',
'packages.*.package_content' => 'required|string|max:100',
'packages.*.package_price' => 'required|numeric|min:0',
'packages.*.delivery_type' => 'required|in:HOME,STOP_DESK',
'packages.*.payment_type' => 'required|in:COD,PREPAID',
'packages.*.cod_amount' => 'required_if:payment_type,COD|numeric|min:0',
'packages.*.is_fragile' => 'boolean',
'packages.*.is_exchange' => 'boolean',
'packages.*.comment' => 'nullable|string|max:500'
```

**Réponse Succès (201 Created)** :

```json
{
  "success": true,
  "message": "3 colis créés avec succès",
  "data": {
    "created_count": 3,
    "packages": [
      {
        "id": 1234,
        "tracking_number": "AL2025-001234",
        "status": "CREATED",
        "recipient_name": "Ahmed Ben Ali",
        "created_at": "2025-10-24T10:30:00Z"
      },
      {
        "id": 1235,
        "tracking_number": "AL2025-001235",
        "status": "CREATED",
        "recipient_name": "Fatma Trabelsi",
        "created_at": "2025-10-24T10:30:01Z"
      },
      {
        "id": 1236,
        "tracking_number": "AL2025-001236",
        "status": "CREATED",
        "recipient_name": "Mohamed Gharbi",
        "created_at": "2025-10-24T10:30:02Z"
      }
    ]
  }
}
```

**Réponse Erreur Validation (422)** :

```json
{
  "success": false,
  "message": "Erreur de validation",
  "errors": {
    "packages.0.recipient_phone": [
      "Le numéro doit contenir exactement 8 chiffres"
    ],
    "packages.1.cod_amount": [
      "Le montant COD est requis pour le paiement à la livraison"
    ]
  }
}
```

**Réponse Erreur Auth (401)** :

```json
{
  "success": false,
  "message": "Token invalide ou expiré",
  "error_code": "UNAUTHORIZED"
}
```

**Réponse Rate Limit (429)** :

```json
{
  "success": false,
  "message": "Trop de requêtes. Limite: 120/minute",
  "retry_after": 45
}
```

---

### **2. Lister/Exporter des Colis**

#### **GET /api/v1/client/packages**

**Description** : Récupère la liste des colis avec filtres

**Headers** :
```http
Authorization: Bearer YOUR_TOKEN
Accept: application/json
```

**Query Parameters** :

| Paramètre | Type | Description | Exemple |
|-----------|------|-------------|---------|
| `page` | integer | Numéro de page (défaut: 1) | `page=2` |
| `per_page` | integer | Résultats par page (10-100, défaut: 50) | `per_page=25` |
| `status` | string | Filtrer par statut | `status=DELIVERED` |
| `tracking_number` | string | Rechercher par numéro | `tracking_number=AL2025-001234` |
| `date_from` | date | Date début (YYYY-MM-DD) | `date_from=2025-10-01` |
| `date_to` | date | Date fin (YYYY-MM-DD) | `date_to=2025-10-24` |
| `gouvernorat` | string | Filtrer par gouvernorat | `gouvernorat=Tunis` |
| `delegation` | string | Filtrer par délégation | `delegation=La Marsa` |
| `payment_type` | string | Filtrer par type paiement | `payment_type=COD` |
| `sort` | string | Tri (created_at, status) | `sort=created_at` |
| `order` | string | Ordre (asc, desc) | `order=desc` |

**Exemples de Requêtes** :

```bash
# Tous les colis, page 1
GET /api/v1/client/packages

# Colis livrés en octobre
GET /api/v1/client/packages?status=DELIVERED&date_from=2025-10-01&date_to=2025-10-31

# Recherche par tracking
GET /api/v1/client/packages?tracking_number=AL2025-001234

# Colis COD à Tunis, 25 par page
GET /api/v1/client/packages?gouvernorat=Tunis&payment_type=COD&per_page=25
```

**Réponse Succès (200 OK)** :

```json
{
  "success": true,
  "data": {
    "packages": [
      {
        "id": 1234,
        "tracking_number": "AL2025-001234",
        "status": "DELIVERED",
        "recipient_name": "Ahmed Ben Ali",
        "recipient_phone": "21234567",
        "recipient_gouvernorat": "Tunis",
        "recipient_delegation": "La Marsa",
        "recipient_address": "123 Avenue Habib Bourguiba",
        "package_content": "Vêtements",
        "package_price": 150.00,
        "cod_amount": 150.00,
        "delivery_type": "HOME",
        "payment_type": "COD",
        "is_fragile": false,
        "is_exchange": false,
        "created_at": "2025-10-20T14:30:00Z",
        "delivered_at": "2025-10-22T16:45:00Z",
        "deliverer_name": "Mohamed Livreur",
        "history": [
          {
            "status": "CREATED",
            "date": "2025-10-20T14:30:00Z",
            "note": "Colis créé"
          },
          {
            "status": "ACCEPTED",
            "date": "2025-10-21T09:15:00Z",
            "note": "Accepté par le livreur"
          },
          {
            "status": "PICKED_UP",
            "date": "2025-10-21T11:30:00Z",
            "note": "Collecté"
          },
          {
            "status": "OUT_FOR_DELIVERY",
            "date": "2025-10-22T10:00:00Z",
            "note": "En livraison"
          },
          {
            "status": "DELIVERED",
            "date": "2025-10-22T16:45:00Z",
            "note": "Livré avec succès"
          }
        ]
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 50,
      "total": 247,
      "from": 1,
      "to": 50
    },
    "links": {
      "first": "https://api.al-amena.tn/v1/client/packages?page=1",
      "last": "https://api.al-amena.tn/v1/client/packages?page=5",
      "prev": null,
      "next": "https://api.al-amena.tn/v1/client/packages?page=2"
    }
  }
}
```

**Réponse Succès (Aucun Résultat - 200 OK)** :

```json
{
  "success": true,
  "data": {
    "packages": [],
    "meta": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 50,
      "total": 0,
      "from": 0,
      "to": 0
    }
  }
}
```

---

### **3. Détail d'un Colis**

#### **GET /api/v1/client/packages/{tracking_number}**

**Description** : Récupère les détails complets d'un colis

**Exemple** :
```bash
GET /api/v1/client/packages/AL2025-001234
```

**Réponse Succès (200 OK)** :

```json
{
  "success": true,
  "data": {
    "package": {
      "id": 1234,
      "tracking_number": "AL2025-001234",
      "status": "DELIVERED",
      "recipient_name": "Ahmed Ben Ali",
      "recipient_phone": "21234567",
      "recipient_phone_2": "98765432",
      "recipient_gouvernorat": "Tunis",
      "recipient_delegation": "La Marsa",
      "recipient_address": "123 Avenue Habib Bourguiba, Résidence Essaada",
      "package_content": "Vêtements",
      "package_price": 150.00,
      "cod_amount": 150.00,
      "delivery_type": "HOME",
      "payment_type": "COD",
      "is_fragile": false,
      "is_exchange": false,
      "comment": "Livrer entre 14h-18h",
      "created_at": "2025-10-20T14:30:00Z",
      "delivered_at": "2025-10-22T16:45:00Z",
      "deliverer": {
        "id": 45,
        "name": "Mohamed Livreur",
        "phone": "55123456"
      },
      "pickup_address": {
        "id": 12,
        "name": "Boutique Centre-Ville",
        "address": "456 Rue de la République",
        "gouvernorat": "Tunis",
        "delegation": "Bab Bhar"
      },
      "history": [
        {
          "status": "CREATED",
          "date": "2025-10-20T14:30:00Z",
          "changed_by": "Client API",
          "note": "Colis créé via API"
        },
        {
          "status": "ACCEPTED",
          "date": "2025-10-21T09:15:00Z",
          "changed_by": "Mohamed Livreur",
          "note": "Accepté par le livreur"
        },
        {
          "status": "PICKED_UP",
          "date": "2025-10-21T11:30:00Z",
          "changed_by": "Mohamed Livreur",
          "note": "Collecté à l'adresse de ramassage"
        },
        {
          "status": "OUT_FOR_DELIVERY",
          "date": "2025-10-22T10:00:00Z",
          "changed_by": "Mohamed Livreur",
          "note": "En cours de livraison"
        },
        {
          "status": "DELIVERED",
          "date": "2025-10-22T16:45:00Z",
          "changed_by": "Mohamed Livreur",
          "note": "Livré avec succès - Signé par le destinataire"
        }
      ]
    }
  }
}
```

**Réponse Erreur (404 Not Found)** :

```json
{
  "success": false,
  "message": "Colis non trouvé",
  "error_code": "PACKAGE_NOT_FOUND"
}
```

---

### **4. Statistiques Client**

#### **GET /api/v1/client/stats**

**Description** : Récupère les statistiques du client

**Réponse (200 OK)** :

```json
{
  "success": true,
  "data": {
    "total_packages": 1247,
    "by_status": {
      "CREATED": 12,
      "ACCEPTED": 8,
      "PICKED_UP": 5,
      "OUT_FOR_DELIVERY": 15,
      "DELIVERED": 1180,
      "RETURNED": 20,
      "CANCELLED": 7
    },
    "this_month": {
      "total": 87,
      "delivered": 75,
      "pending": 12
    },
    "total_revenue": 45670.50,
    "total_cod_collected": 42100.00
  }
}
```

---

## 🔒 CODES D'ERREUR

| Code HTTP | Message | Description |
|-----------|---------|-------------|
| 200 | OK | Succès |
| 201 | Created | Ressource créée |
| 400 | Bad Request | Requête invalide |
| 401 | Unauthorized | Token manquant/invalide |
| 403 | Forbidden | Accès refusé |
| 404 | Not Found | Ressource introuvable |
| 422 | Validation Error | Données invalides |
| 429 | Too Many Requests | Rate limit dépassé |
| 500 | Server Error | Erreur serveur |

---

## 🚀 EXEMPLES COMPLETS

### **cURL**

```bash
# Créer un colis
curl -X POST https://api.al-amena.tn/v1/client/packages \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "packages": [{
      "pickup_address_id": 12,
      "recipient_name": "Ahmed Ben Ali",
      "recipient_phone": "21234567",
      "recipient_gouvernorat": "Tunis",
      "recipient_delegation": "La Marsa",
      "recipient_address": "123 Avenue Bourguiba",
      "package_content": "Vêtements",
      "package_price": 150.00,
      "delivery_type": "HOME",
      "payment_type": "COD",
      "cod_amount": 150.00
    }]
  }'

# Lister les colis
curl -X GET "https://api.al-amena.tn/v1/client/packages?status=DELIVERED&per_page=25" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### **PHP**

```php
$token = 'YOUR_API_TOKEN';
$baseUrl = 'https://api.al-amena.tn/v1';

// Créer un colis
$ch = curl_init($baseUrl . '/client/packages');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'packages' => [[
        'pickup_address_id' => 12,
        'recipient_name' => 'Ahmed Ben Ali',
        'recipient_phone' => '21234567',
        'recipient_gouvernorat' => 'Tunis',
        'recipient_delegation' => 'La Marsa',
        'recipient_address' => '123 Avenue Bourguiba',
        'package_content' => 'Vêtements',
        'package_price' => 150.00,
        'delivery_type' => 'HOME',
        'payment_type' => 'COD',
        'cod_amount' => 150.00
    ]]
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$result = json_decode($response, true);
curl_close($ch);
```

### **Python**

```python
import requests

TOKEN = 'YOUR_API_TOKEN'
BASE_URL = 'https://api.al-amena.tn/v1'

headers = {
    'Authorization': f'Bearer {TOKEN}',
    'Content-Type': 'application/json'
}

# Créer un colis
data = {
    'packages': [{
        'pickup_address_id': 12,
        'recipient_name': 'Ahmed Ben Ali',
        'recipient_phone': '21234567',
        'recipient_gouvernorat': 'Tunis',
        'recipient_delegation': 'La Marsa',
        'recipient_address': '123 Avenue Bourguiba',
        'package_content': 'Vêtements',
        'package_price': 150.00,
        'delivery_type': 'HOME',
        'payment_type': 'COD',
        'cod_amount': 150.00
    }]
}

response = requests.post(f'{BASE_URL}/client/packages', json=data, headers=headers)
print(response.json())

# Lister les colis
params = {'status': 'DELIVERED', 'per_page': 25}
response = requests.get(f'{BASE_URL}/client/packages', params=params, headers=headers)
print(response.json())
```

---

**Suite** : Voir `SPEC_API_CLIENT_PARTIE_3_SECURITE.md`
