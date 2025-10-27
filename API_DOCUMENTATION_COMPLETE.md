# 📘 Documentation API Al-Amena Delivery

**Version** : 1.0  
**Base URL** : `https://al-amena.tn/api/v1`

---

## 🚀 Quick Start

### 1. Obtenir votre Token API

1. Connectez-vous à votre espace client
2. Allez dans **Paramètres** → **API & Intégrations**
3. Cliquez sur **"Générer Mon Token"**
4. **Copiez et conservez** le token en lieu sûr

### 2. Premier Appel API

```bash
curl -X GET https://al-amena.tn/api/v1/client/packages \
  -H "Authorization: Bearer VOTRE_TOKEN" \
  -H "Accept: application/json"
```

---

## 🔐 Authentification

**Méthode** : Bearer Token

Incluez votre token dans le header `Authorization` de chaque requête :

```http
Authorization: Bearer alamena_live_a3f8d9c7b2e1f4a6d8c9b3e7f1a2d5c8
```

---

## 📦 Créer des Colis

### POST /api/v1/client/packages

**Rate Limit** : 60 requêtes/minute

#### Requête

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
      "recipient_address": "123 Avenue Habib Bourguiba",
      "package_content": "Vêtements",
      "package_price": 150.00,
      "delivery_type": "HOME",
      "payment_type": "COD",
      "cod_amount": 150.00,
      "is_fragile": false,
      "is_exchange": false,
      "comment": "Livrer entre 14h-18h",
      "external_reference": "CMD-2025-001"
    }
  ]
}
```

#### Champs

| Champ | Type | Requis | Description |
|-------|------|--------|-------------|
| `pickup_address_id` | integer | ✅ | ID de votre adresse de ramassage |
| `recipient_name` | string (max 100) | ✅ | Nom du destinataire |
| `recipient_phone` | string (8 chiffres) | ✅ | Téléphone principal |
| `recipient_phone_2` | string (8 chiffres) | ❌ | Téléphone secondaire |
| `recipient_gouvernorat` | string | ✅ | Gouvernorat (voir liste) |
| `recipient_delegation` | string | ✅ | Délégation (voir liste) |
| `recipient_address` | string (max 255) | ✅ | Adresse complète |
| `package_content` | string (max 100) | ✅ | Description du contenu |
| `package_price` | decimal | ✅ | Prix du colis |
| `delivery_type` | enum | ✅ | `HOME` ou `STOP_DESK` |
| `payment_type` | enum | ✅ | `COD` ou `PREPAID` |
| `cod_amount` | decimal | Si COD | Montant à collecter |
| `is_fragile` | boolean | ❌ | Colis fragile (défaut: false) |
| `is_exchange` | boolean | ❌ | Colis d'échange (défaut: false) |
| `comment` | string (max 500) | ❌ | Commentaire |
| `external_reference` | string (max 255) | ❌ | Votre référence interne |

#### Réponse (201 Created)

```json
{
  "success": true,
  "message": "1 colis créé avec succès",
  "data": {
    "created_count": 1,
    "packages": [
      {
        "id": 1234,
        "tracking_number": "PKG_A1B2C3_0001",
        "status": "CREATED",
        "recipient_name": "Ahmed Ben Ali",
        "created_at": "2025-10-24T10:30:00Z"
      }
    ]
  }
}
```

---

## 📋 Lister les Colis

### GET /api/v1/client/packages

#### Paramètres

| Paramètre | Type | Description | Exemple |
|-----------|------|-------------|---------|
| `page` | integer | Numéro de page (défaut: 1) | `page=2` |
| `per_page` | integer | Résultats par page (10-100, défaut: 50) | `per_page=25` |
| `status` | string | Filtrer par statut | `status=DELIVERED` |
| `tracking_number` | string | Rechercher par code | `tracking_number=PKG_A1B2C3_0001` |
| `date_from` | date | Date début (YYYY-MM-DD) | `date_from=2025-10-01` |
| `date_to` | date | Date fin (YYYY-MM-DD) | `date_to=2025-10-24` |
| `gouvernorat` | string | Filtrer par gouvernorat | `gouvernorat=Tunis` |
| `delegation` | string | Filtrer par délégation | `delegation=La Marsa` |
| `payment_type` | string | COD ou PREPAID | `payment_type=COD` |

#### Exemples

```bash
# Tous les colis livrés en octobre
GET /api/v1/client/packages?status=DELIVERED&date_from=2025-10-01&date_to=2025-10-31

# Colis COD à Tunis
GET /api/v1/client/packages?gouvernorat=Tunis&payment_type=COD&per_page=25
```

#### Réponse (200 OK)

```json
{
  "success": true,
  "data": {
    "packages": [
      {
        "id": 1234,
        "tracking_number": "PKG_A1B2C3_0001",
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
      "total": 247
    }
  }
}
```

---

## 🔍 Détail d'un Colis

### GET /api/v1/client/packages/{tracking_number}

#### Réponse (200 OK)

Retourne les détails complets incluant l'historique, le livreur et l'adresse de ramassage.

---

## 📊 Statistiques

### GET /api/v1/client/stats

#### Réponse (200 OK)

```json
{
  "success": true,
  "data": {
    "total_packages": 1247,
    "by_status": {
      "CREATED": 12,
      "DELIVERED": 1180,
      "RETURNED": 20
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

## 📄 Générer des Étiquettes PDF

### POST /api/v1/client/packages/labels

#### Requête

```json
{
  "tracking_numbers": [
    "PKG_A1B2C3_0001",
    "PKG_D4E5F6_0002"
  ]
}
```

#### Réponse

Retourne un fichier PDF avec toutes les étiquettes.

---

## 📚 RÉFÉRENCES

### Statuts de Colis

| Code | Description |
|------|-------------|
| `CREATED` | Colis créé |
| `ACCEPTED` | Accepté par livreur |
| `PICKED_UP` | Collecté |
| `OUT_FOR_DELIVERY` | En livraison |
| `DELIVERED` | Livré |
| `RETURNED` | Retourné |
| `CANCELLED` | Annulé |
| `LOST` | Perdu |
| `DAMAGED` | Endommagé |

### Gouvernorats de Tunisie

Tunis, Ariana, Ben Arous, Manouba, Nabeul, Zaghouan, Bizerte, Béja, Jendouba, Kef, Siliana, Kairouan, Kasserine, Sidi Bouzid, Sousse, Monastir, Mahdia, Sfax, Gafsa, Tozeur, Kebili, Gabès, Medenine, Tataouine

### Délégations Principales

**Tunis** : Tunis Ville, La Marsa, Carthage, Le Bardo, La Goulette, Ariana Ville, Soukra

**Ariana** : Ariana Ville, Soukra, Raoued, Kalaat El Andalous, Sidi Thabet, Ettadhamen, Mnihla

**Ben Arous** : Ben Arous, Hammam Lif, Radès, Ezzahra, Megrine, Mohamedia, Fouchana, Mornag

**Sousse** : Sousse Ville, Hammam Sousse, Msaken, Kalaa Kebira, Enfidha, Kondar

**Sfax** : Sfax Ville, Sakiet Ezzit, Sakiet Eddaier, Agareb, Jebiniana, El Hencha

---

## ⚠️ Codes d'Erreur

| Code | Message | Solution |
|------|---------|----------|
| 401 | Token invalide | Vérifier votre token |
| 403 | Compte non vérifié | Contacter le support |
| 422 | Erreur de validation | Corriger les données |
| 429 | Rate limit dépassé | Ralentir les requêtes |

---

## 🔒 Sécurité

- ✅ HTTPS obligatoire
- ✅ Token en variable d'environnement
- ✅ Ne jamais committer le token
- ✅ Régénérer si compromis

---

## 💻 Exemples de Code

### PHP

```php
$token = getenv('ALAMENA_API_TOKEN');
$url = 'https://al-amena.tn/api/v1/client/packages';

$data = [
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
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$result = json_decode($response, true);
curl_close($ch);

if ($result['success']) {
    echo "Tracking: " . $result['data']['packages'][0]['tracking_number'];
}
```

### Python

```python
import requests
import os

TOKEN = os.getenv('ALAMENA_API_TOKEN')
URL = 'https://al-amena.tn/api/v1/client/packages'

headers = {
    'Authorization': f'Bearer {TOKEN}',
    'Content-Type': 'application/json'
}

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

response = requests.post(URL, json=data, headers=headers)
result = response.json()

if result['success']:
    print(f"Tracking: {result['data']['packages'][0]['tracking_number']}")
```

---

## 📞 Support

**Email** : api-support@al-amena.tn  
**Documentation** : https://al-amena.tn/docs/api  
**Rate Limit** : 120 requêtes/minute
