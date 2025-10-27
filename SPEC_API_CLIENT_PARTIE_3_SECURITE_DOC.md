# 🔐 SPÉCIFICATION API CLIENT - PARTIE 3 : SÉCURITÉ & DOCUMENTATION

**Version** : 1.0  
**Date** : 24 Octobre 2025

---

## 🔒 PARTIE 5 : SÉCURITÉ

### **1. Stockage et Gestion des Tokens**

#### **Côté Serveur**

**Base de données** : Table `api_tokens`

```sql
CREATE TABLE api_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) DEFAULT 'API Token',
    token VARCHAR(64) UNIQUE NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token_hash (token_hash),
    INDEX idx_user_id (user_id)
);
```

**Modèle Laravel** :

```php
// app/Models/ApiToken.php
class ApiToken extends Model
{
    protected $fillable = ['user_id', 'name', 'token', 'token_hash', 'last_used_at', 'expires_at'];
    
    protected $hidden = ['token_hash'];
    
    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Générer un token unique
    public static function generate($userId)
    {
        $token = 'alamena_' . (app()->environment('production') ? 'live' : 'test') . '_' . bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        
        return self::create([
            'user_id' => $userId,
            'token' => $token,
            'token_hash' => $hash
        ]);
    }
    
    // Vérifier un token
    public static function verify($token)
    {
        $hash = hash('sha256', $token);
        return self::where('token_hash', $hash)->first();
    }
    
    // Mettre à jour la dernière utilisation
    public function touch()
    {
        $this->update(['last_used_at' => now()]);
    }
}
```

**Bonnes Pratiques** :
- ✅ **Hachage SHA-256** : Ne jamais stocker le token en clair complet
- ✅ **Token unique** : 64 caractères aléatoires cryptographiquement sûrs
- ✅ **Index** : Sur `token_hash` pour performance
- ✅ **Cascade Delete** : Suppression automatique si user supprimé
- ✅ **Préfixe** : `alamena_live_` ou `alamena_test_` pour identification

---

#### **Côté Client (Stockage)**

**❌ À NE PAS FAIRE** :
```javascript
// JAMAIS dans le code source
const API_TOKEN = 'alamena_live_abc123';

// JAMAIS dans Git
// config.js avec token en dur

// JAMAIS en localStorage (risque XSS)
localStorage.setItem('api_token', token);
```

**✅ BONNES PRATIQUES** :

**Variables d'environnement** :
```env
# .env
ALAMENA_API_TOKEN=alamena_live_a3f8d9c7b2e1f4a6d8c9b3e7f1a2d5c8
```

**PHP** :
```php
$token = env('ALAMENA_API_TOKEN');
```

**Node.js** :
```javascript
const token = process.env.ALAMENA_API_TOKEN;
```

**Python** :
```python
import os
token = os.getenv('ALAMENA_API_TOKEN')
```

---

### **2. Middleware de Sécurité**

#### **Authentification API**

```php
// app/Http/Middleware/ApiTokenAuth.php
class ApiTokenAuth
{
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token manquant',
                'error_code' => 'TOKEN_MISSING'
            ], 401);
        }
        
        $apiToken = ApiToken::verify($token);
        
        if (!$apiToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalide',
                'error_code' => 'TOKEN_INVALID'
            ], 401);
        }
        
        // Vérifier expiration
        if ($apiToken->expires_at && $apiToken->expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Token expiré',
                'error_code' => 'TOKEN_EXPIRED'
            ], 401);
        }
        
        // Vérifier que l'utilisateur est actif
        if ($apiToken->user->status !== 'ACTIVE') {
            return response()->json([
                'success' => false,
                'message' => 'Compte inactif',
                'error_code' => 'ACCOUNT_INACTIVE'
            ], 403);
        }
        
        // Authentifier l'utilisateur
        Auth::setUser($apiToken->user);
        
        // Mettre à jour la dernière utilisation
        $apiToken->touch();
        
        return $next($request);
    }
}
```

#### **Rate Limiting**

```php
// config/api.php
return [
    'rate_limits' => [
        'default' => '120:1', // 120 requêtes par minute
        'create_packages' => '60:1', // 60 créations par minute
    ]
];

// routes/api.php
Route::middleware(['api.token.auth', 'throttle:120,1'])->group(function () {
    Route::post('/client/packages', [ApiPackageController::class, 'store'])
        ->middleware('throttle:60,1');
    Route::get('/client/packages', [ApiPackageController::class, 'index']);
    Route::get('/client/packages/{tracking}', [ApiPackageController::class, 'show']);
});
```

#### **Validation & Sanitization**

```php
class CreatePackageRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->status === 'ACTIVE';
    }
    
    public function rules()
    {
        return [
            'packages' => 'required|array|min:1|max:100',
            'packages.*.pickup_address_id' => [
                'required',
                'exists:client_pickup_addresses,id',
                // Vérifier que l'adresse appartient au client
                function ($attribute, $value, $fail) {
                    $address = ClientPickupAddress::find($value);
                    if ($address && $address->client_id !== auth()->id()) {
                        $fail('Adresse de ramassage non autorisée');
                    }
                }
            ],
            'packages.*.recipient_phone' => [
                'required',
                'regex:/^[0-9]{8}$/',
                'not_in:00000000,11111111' // Bloquer numéros bidons
            ],
            // ... autres règles
        ];
    }
    
    protected function prepareForValidation()
    {
        // Nettoyer les données entrantes
        $packages = $this->packages;
        
        foreach ($packages as &$package) {
            // Trim tous les strings
            $package['recipient_name'] = trim($package['recipient_name'] ?? '');
            $package['recipient_address'] = trim($package['recipient_address'] ?? '');
            
            // Supprimer caractères dangereux
            $package['comment'] = strip_tags($package['comment'] ?? '');
        }
        
        $this->merge(['packages' => $packages]);
    }
}
```

---

### **3. Isolation des Données**

**Middleware Scope** :

```php
// Assurer que chaque client ne voit que ses données
class EnsureApiScope
{
    public function handle($request, Closure $next)
    {
        // Ajouter automatiquement le filtre par client
        $request->merge(['client_id' => auth()->id()]);
        
        return $next($request);
    }
}

// Dans le contrôleur
public function index(Request $request)
{
    $packages = Package::where('sender_id', auth()->id()) // Toujours filtrer
        ->when($request->status, fn($q, $status) => $q->where('status', $status))
        ->paginate($request->per_page ?? 50);
    
    return response()->json($packages);
}
```

**Politique d'Accès** :

```php
// app/Policies/PackagePolicy.php
class PackagePolicy
{
    public function view(User $user, Package $package)
    {
        // Un client peut voir uniquement ses colis
        return $user->id === $package->sender_id;
    }
    
    public function viewAny(User $user)
    {
        return $user->role === 'CLIENT' && $user->status === 'ACTIVE';
    }
}

// Dans le contrôleur
public function show($tracking)
{
    $package = Package::where('package_code', $tracking)->firstOrFail();
    
    $this->authorize('view', $package);
    
    return response()->json(['success' => true, 'data' => $package]);
}
```

---

### **4. Logging & Monitoring**

**Log des Requêtes API** :

```php
// app/Models/ApiLog.php
class ApiLog extends Model
{
    protected $fillable = [
        'user_id',
        'endpoint',
        'method',
        'ip_address',
        'user_agent',
        'response_status',
        'response_time',
        'request_data',
        'response_data'
    ];
    
    public static function logRequest($user, $request, $response, $startTime)
    {
        self::create([
            'user_id' => $user->id,
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'response_status' => $response->status(),
            'response_time' => microtime(true) - $startTime,
            'request_data' => json_encode($request->all()),
            'response_data' => $response->content()
        ]);
    }
}

// Middleware
class ApiLogger
{
    public function handle($request, Closure $next)
    {
        $start = microtime(true);
        $response = $next($request);
        
        if (auth()->check()) {
            ApiLog::logRequest(auth()->user(), $request, $response, $start);
        }
        
        return $response;
    }
}
```

**Alertes de Sécurité** :

```php
// Détecter activité suspecte
class SecurityMonitor
{
    public static function checkSuspiciousActivity($user)
    {
        // Trop de requêtes en erreur
        $recentErrors = ApiLog::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->where('response_status', '>=', 400)
            ->count();
        
        if ($recentErrors > 20) {
            // Notifier admin
            Notification::send(
                User::where('role', 'SUPERVISOR')->get(),
                new SuspiciousApiActivity($user, $recentErrors)
            );
            
            // Optionnel : suspendre temporairement
            // $user->api_tokens()->update(['is_suspended' => true]);
        }
        
        // Tentatives d'accès non autorisé
        $unauthorizedAttempts = ApiLog::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHour())
            ->where('response_status', 403)
            ->count();
        
        if ($unauthorizedAttempts > 10) {
            // Alerte sécurité
            Log::warning("User {$user->id} has {$unauthorizedAttempts} unauthorized attempts");
        }
    }
}
```

---

### **5. HTTPS & Encryption**

**Force HTTPS** :

```php
// app/Http/Middleware/ForceHttps.php
class ForceHttps
{
    public function handle($request, Closure $next)
    {
        if (!$request->secure() && app()->environment('production')) {
            return redirect()->secure($request->getRequestUri(), 301);
        }
        
        return $next($request);
    }
}

// Dans AppServiceProvider
public function boot()
{
    if (app()->environment('production')) {
        URL::forceScheme('https');
    }
}
```

**Headers de Sécurité** :

```php
// app/Http/Middleware/SecurityHeaders.php
class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        
        return $response;
    }
}
```

---

## 📖 PARTIE 4 : DOCUMENTATION API POUR CLIENTS

### **Structure de la Documentation**

```
Documentation API Al-Amena Delivery
├── 1. Introduction
│   ├── Vue d'ensemble
│   ├── Prérequis
│   └── Environnements (Production/Sandbox)
├── 2. Authentification
│   ├── Obtenir votre token
│   ├── Utiliser le token
│   └── Régénérer/Supprimer
├── 3. Endpoints
│   ├── Créer des colis (POST)
│   ├── Lister des colis (GET)
│   ├── Détail d'un colis (GET)
│   └── Statistiques (GET)
├── 4. Exemples de Code
│   ├── cURL
│   ├── PHP
│   ├── Python
│   ├── Node.js
│   └── WooCommerce Plugin
├── 5. Codes d'Erreur
├── 6. Rate Limiting
├── 7. Webhooks (futur)
└── 8. FAQ & Support
```

---

### **Exemple de Page Documentation**

```markdown
# 🚀 API Al-Amena Delivery - Guide Rapide

## Introduction

Bienvenue dans la documentation de l'API Al-Amena Delivery. 
Cette API REST vous permet d'intégrer automatiquement notre 
système de livraison avec votre boutique en ligne ou ERP.

### Fonctionnalités

- ✅ Créer des colis automatiquement
- ✅ Suivre l'état de livraison en temps réel
- ✅ Exporter vos données de colis
- ✅ Recevoir des webhooks (bientôt)

### Prérequis

- Un compte client Al-Amena Delivery vérifié
- Un token API généré depuis votre espace client
- Connexion HTTPS obligatoire

---

## Authentification

### 1. Obtenir votre Token

1. Connectez-vous à votre espace client
2. Accédez à **Paramètres** → **API & Intégrations**
3. Cliquez sur **"Générer Mon Token"**
4. **Copiez et conservez** le token en lieu sûr

### 2. Utiliser le Token

Incluez votre token dans le header `Authorization` de chaque requête :

```http
Authorization: Bearer alamena_live_votre_token_ici
```

### 3. Sécurité

⚠️ **IMPORTANT** :
- Ne partagez JAMAIS votre token
- Utilisez des variables d'environnement
- Régénérez immédiatement si compromis
- Utilisez HTTPS uniquement

---

## Quick Start

### Créer votre Premier Colis

**Requête** :
```bash
curl -X POST https://api.al-amena.tn/v1/client/packages \
  -H "Authorization: Bearer VOTRE_TOKEN" \
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
```

**Réponse** :
```json
{
  "success": true,
  "message": "1 colis créé",
  "data": {
    "packages": [{
      "id": 1234,
      "tracking_number": "AL2025-001234",
      "status": "CREATED"
    }]
  }
}
```

---

## Endpoints Disponibles

### 📦 Créer des Colis

**`POST /api/v1/client/packages`**

Crée un ou plusieurs colis (max 100 par requête).

[Voir la documentation complète →](#create-packages)

### 📋 Lister les Colis

**`GET /api/v1/client/packages`**

Récupère la liste de vos colis avec filtres et pagination.

[Voir la documentation complète →](#list-packages)

### 🔍 Détail d'un Colis

**`GET /api/v1/client/packages/{tracking_number}`**

Récupère les détails et l'historique complet d'un colis.

[Voir la documentation complète →](#show-package)

---

## Exemples par Langage

<details>
<summary><strong>🐘 PHP</strong></summary>

```php
<?php
$token = 'VOTRE_TOKEN';
$url = 'https://api.al-amena.tn/v1/client/packages';

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

if ($result['success']) {
    echo "Colis créé : " . $result['data']['packages'][0]['tracking_number'];
}

curl_close($ch);
?>
```
</details>

<details>
<summary><strong>🐍 Python</strong></summary>

```python
import requests
import json

TOKEN = 'VOTRE_TOKEN'
URL = 'https://api.al-amena.tn/v1/client/packages'

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
    print(f"Colis créé : {result['data']['packages'][0]['tracking_number']}")
```
</details>

---

## Rate Limiting

| Limite | Valeur |
|--------|--------|
| Requêtes globales | 120/minute |
| Création de colis | 60/minute |
| Colis par requête | Max 100 |

En cas de dépassement : `HTTP 429 Too Many Requests`

---

## Codes d'Erreur

| Code | Description | Solution |
|------|-------------|----------|
| 401 | Token invalide | Vérifier votre token |
| 403 | Accès refusé | Vérifier statut compte |
| 422 | Validation error | Corriger les données |
| 429 | Rate limit | Ralentir les requêtes |

---

## Support

📧 Email : api-support@al-amena.tn  
📞 Téléphone : +216 XX XXX XXX  
💬 Chat : Disponible dans votre espace client

**Temps de réponse moyen** : 24h
```

---

### **Page Interactive (Swagger/Redoc)**

Utiliser **Laravel Scramble** ou **L5-Swagger** pour générer automatiquement une documentation interactive :

```bash
composer require dedoc/scramble
```

```php
// config/scramble.php
return [
    'api_path' => 'api/v1',
    'api_domain' => null,
    'info' => [
        'title' => 'Al-Amena Delivery API',
        'description' => 'API officielle pour intégrer Al-Amena Delivery',
        'version' => '1.0.0',
    ],
];
```

Accessible sur : `https://al-amena.tn/docs/api`

---

## 🎯 CHECKLIST SÉCURITÉ

- [ ] Tokens stockés hashés (SHA-256) en BDD
- [ ] Rate limiting activé (120 req/min)
- [ ] Middleware authentification sur routes API
- [ ] HTTPS forcé en production
- [ ] Headers sécurité (HSTS, X-Frame-Options, etc.)
- [ ] Validation stricte des données entrantes
- [ ] Isolation données par client (sender_id)
- [ ] Logging toutes requêtes API
- [ ] Alertes activité suspecte
- [ ] Documentation sécurité fournie
- [ ] Possibilité régénération token
- [ ] Expiration automatique optionnelle
- [ ] Audit logs conservés 12 mois
- [ ] Tests de pénétration effectués

---

**Fin de la Spécification** 🎉

**Documents créés** :
1. `SPEC_API_CLIENT_PARTIE_1_UI.md`
2. `SPEC_API_CLIENT_PARTIE_2_ENDPOINTS.md`
3. `SPEC_API_CLIENT_PARTIE_3_SECURITE_DOC.md`
