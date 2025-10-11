# 🛣️ Routes - Système de Retours

## 📦 Routes Commercial

**Base URL:** `/commercial`
**Middleware:** `auth`, `verified`, `role:COMMERCIAL,SUPERVISOR`

### Gestion des Retours
| Méthode | Route | Nom | Description |
|---------|-------|-----|-------------|
| POST | `/packages/{package}/launch-fourth-attempt` | `commercial.packages.launch.fourth.attempt` | Lancer une 4ème tentative de livraison |
| PATCH | `/packages/{package}/change-status` | `commercial.packages.change.status` | Changement manuel de statut avec raison |

**Paramètres:**

**launch-fourth-attempt:**
- Pas de paramètres (formulaire vide)
- Conditions: statut = `AWAITING_RETURN`
- Action: Remet à `AT_DEPOT` avec 2 tentatives

**change-status:**
- `new_status` (required): Le nouveau statut
- `change_reason` (required, max 500): Raison du changement
- Validation: Empêche certaines transitions dangereuses

---

## 🏭 Routes Dépôt Retours

**Base URL:** `/depot/returns`
**Middleware:** `ngrok.cors`

### Interface PC
| Méthode | Route | Nom | Description |
|---------|-------|-----|-------------|
| GET | `/depot/returns` | `depot.returns.dashboard` | Dashboard PC scan retours |
| GET | `/depot/returns/enter-name` | `depot.returns.enter-manager-name` | Saisie nom chef dépôt |
| POST | `/depot/returns/new-session` | `depot.returns.new-session` | Démarrer nouvelle session |

### Interface Mobile
| Méthode | Route | Nom | Description |
|---------|-------|-----|-------------|
| GET | `/depot/returns/phone/{sessionId}` | `depot.returns.phone-scanner` | Scanner mobile pour retours |

### Gestion
| Méthode | Route | Nom | Description |
|---------|-------|-----|-------------|
| GET | `/depot/returns/manage` | `depot.returns.manage` | Liste des colis retours |
| GET | `/depot/returns/package/{returnPackage}` | `depot.returns.show` | Détails colis retour |
| GET | `/depot/returns/package/{returnPackage}/print` | `depot.returns.print` | Imprimer bordereau |

### API
| Méthode | Route | Nom | Description |
|---------|-------|-----|-------------|
| POST | `/depot/returns/api/session/{sessionId}/scan` | `depot.returns.api.scan` | Scanner un colis retour |
| GET | `/depot/returns/api/session/{sessionId}/status` | `depot.returns.api.status` | État de la session |
| GET | `/depot/returns/api/session/{sessionId}/check-activity` | `depot.returns.api.check-activity` | Vérifier activité session |
| POST | `/depot/returns/{sessionId}/validate` | `depot.returns.validate` | Valider et créer colis retours |

**Format sessionId:** `return_[0-9a-f]+` (ex: `return_67890abcdef`)

---

## 👤 Routes Client

**Base URL:** `/client`
**Middleware:** `auth`, `CheckRole:CLIENT`

### Gestion des Retours
| Méthode | Route | Nom | Description |
|---------|-------|-----|-------------|
| GET | `/client/returns` | `client.returns.index` | Page de gestion des retours |
| POST | `/client/returns/{package}/confirm` | `client.returns.confirm` | Confirmer réception retour |
| POST | `/client/returns/{package}/report-issue` | `client.returns.report-issue` | Signaler un problème |

**Paramètres:**

**confirm:**
- Pas de paramètres
- Conditions: statut = `RETURNED_TO_CLIENT`
- Action: Change statut → `RETURN_CONFIRMED`

**report-issue:**
- `issue_description` (required, max 1000): Description du problème
- Conditions: statut = `RETURNED_TO_CLIENT`
- Action:
  - Crée une réclamation (type: `RETURN_ISSUE`, priorité: `HIGH`)
  - Change statut → `RETURN_ISSUE`

---

## 📊 Exemples d'Utilisation

### 1. Commercial - Lancer 4ème Tentative

**Requête:**
```html
<form action="/commercial/packages/123/launch-fourth-attempt" method="POST">
    @csrf
    <button type="submit">Lancer 4ème Tentative</button>
</form>
```

**Réponse (succès):**
```
Redirection avec message flash:
"4ème tentative lancée avec succès."
```

### 2. Commercial - Changement Manuel de Statut

**Requête:**
```html
<form action="/commercial/packages/123/change-status" method="POST">
    @csrf
    @method('PATCH')
    <select name="new_status">
        <option value="AT_DEPOT">AT_DEPOT</option>
        <option value="DELIVERED">DELIVERED</option>
        <!-- ... -->
    </select>
    <textarea name="change_reason" required>
        Raison du changement...
    </textarea>
    <button type="submit">Confirmer</button>
</form>
```

### 3. Dépôt - Scanner un Colis Retour

**Requête:**
```javascript
fetch('/depot/returns/api/session/return_abc123/scan', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token
    },
    body: JSON.stringify({
        package_code: 'PKG-XYZ789'
    })
})
```

**Réponse (succès):**
```json
{
    "success": true,
    "message": "Colis PKG-XYZ789 scanné avec succès",
    "package": {
        "code": "PKG-XYZ789",
        "tracking": "TRK-123456",
        "cod": "150.00",
        "sender": "Client ABC",
        "reason": "Destinataire injoignable"
    },
    "total_scanned": 5
}
```

**Réponse (erreur - déjà scanné):**
```json
{
    "success": false,
    "message": "Ce colis a déjà été scanné",
    "already_scanned": true
}
```

### 4. Dépôt - Valider Session et Créer Retours

**Requête:**
```javascript
fetch('/depot/returns/return_abc123/validate', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token
    },
    body: JSON.stringify({})
})
```

**Réponse:**
```json
{
    "success": true,
    "message": "5 colis retours créés avec succès",
    "created_count": 5,
    "return_codes": [
        "RET-12345678",
        "RET-23456789",
        "RET-34567890",
        "RET-45678901",
        "RET-56789012"
    ]
}
```

### 5. Client - Confirmer Réception

**Requête:**
```html
<form action="/client/returns/123/confirm" method="POST">
    @csrf
    <button type="submit">Confirmer Réception</button>
</form>
```

**Réponse:**
```
Redirection avec message:
"Retour confirmé avec succès."
```

### 6. Client - Signaler Problème

**Requête:**
```html
<form action="/client/returns/123/report-issue" method="POST">
    @csrf
    <textarea name="issue_description" required>
        Le colis est arrivé endommagé, le contenu est cassé.
    </textarea>
    <button type="submit">Signaler le Problème</button>
</form>
```

**Réponse:**
```
Redirection avec message:
"Problème signalé avec succès. Notre équipe va vous contacter."

+ Création automatique d'une réclamation
```

---

## 🔐 Middleware et Sécurité

### Protection des Routes

**Commercial:**
- Authentification requise (`auth`)
- Compte vérifié (`verified`)
- Rôle: `COMMERCIAL` ou `SUPERVISOR`

**Dépôt:**
- Support Ngrok (`ngrok.cors`)
- Sessions temporaires avec expiration
- Vérification d'activité toutes les 3 secondes (mobile)

**Client:**
- Authentification requise (`auth`)
- Rôle: `CLIENT`
- Vérification ownership (sender_id)

### Validations

**Toutes les routes:**
- Token CSRF vérifié
- Vérification du statut avant action
- Logging de toutes les opérations importantes

---

## 📱 Support Mobile

### QR Code Session
Le QR code généré sur le PC contient l'URL complète:
```
https://example.com/depot/returns/phone/return_abc123def
```

### Headers Requis (Mobile)
```javascript
{
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    'X-Requested-With': 'XMLHttpRequest'
}
```

### Polling
- **PC Dashboard:** Toutes les 2 secondes
- **Mobile Scanner:** Toutes les 3 secondes (check activité)

---

## 🚦 Codes de Statut HTTP

| Code | Signification | Exemple |
|------|---------------|---------|
| 200 | Succès | Colis scanné avec succès |
| 400 | Requête invalide | Session expirée |
| 404 | Non trouvé | Colis introuvable |
| 422 | Validation échouée | Colis déjà scanné, mauvais statut |
| 500 | Erreur serveur | Erreur DB, exception |

---

## 📝 Notes Importantes

1. **Sessions Temporaires:**
   - Durée de vie: 24h
   - Stockage: Cache Laravel
   - Nettoyage automatique après validation

2. **Format des Codes:**
   - Colis retour: `RET-XXXXXXXX` (8 caractères alphanumériques)
   - Session: `return_` + uniqid()

3. **Webhook-Ready:**
   - Toutes les API retournent du JSON
   - Support des requêtes AJAX
   - Compatible avec les outils de monitoring

4. **Logs:**
   - Tous les événements sont loggés
   - Format: `[date] Message {context}`
   - Fichier: `storage/logs/laravel.log`

---

**Dernière mise à jour:** 11 Octobre 2025
**Version:** 1.0
**Status:** Production Ready ✅
