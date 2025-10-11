# ✅ Règles Finales - Statuts Scanner Dépôt

## 🎯 Classification Définitive

### ✅ **STATUTS ACCEPTÉS**

| Statut | Description | Raison |
|--------|-------------|--------|
| **CREATED** | Colis créé | Réception initiale au dépôt |
| **AVAILABLE** | Disponible pour pickup | Colis arrive au dépôt |
| **ACCEPTED** | Accepté par livreur | Livreur ramène au dépôt |
| **PICKED_UP** | Ramassé | Retour temporaire au dépôt |
| **OUT_FOR_DELIVERY** | En livraison | Retour au dépôt |
| **UNAVAILABLE** | Client indisponible | Livraison planifiée, retour dépôt |
| **AT_DEPOT** | Au dépôt | Si dépôt différent (transfert) ✅<br>Si même dépôt ❌ |

### ❌ **STATUTS REFUSÉS**

| Statut | Message Refus |
|--------|---------------|
| **DELIVERED** | Statut invalide: DELIVERED |
| **PAID** | Statut invalide: PAID |
| **VERIFIED** | Statut invalide: VERIFIED |
| **RETURNED** | Statut invalide: RETURNED |
| **CANCELLED** | Statut invalide: CANCELLED |
| **REFUSED** | Statut invalide: REFUSED |
| **DELIVERED_PAID** | Statut invalide: DELIVERED_PAID |

---

## 🔍 Statuts à Vérifier/Corriger

### 1. **VERIFIED** (À retourner)
**Utilisation actuelle:**
- Jobs: Sync offline actions → status = 'VERIFIED'
- Models: Package → action 'return' si VERIFIED
- Controllers: Accepté dans scanner dépôt

**Problème:** Ce statut ne devrait pas exister selon vos règles

**Action:** À remplacer par **RETURNING_TO_SUPPLIER** ?

### 2. **DELIVERED_PAID**
**Utilisation actuelle:**
- Complaints: Vérifie si PAID ou DELIVERED_PAID
- DepotScan: Rejeté

**Problème:** Doublon avec PAID

**Action:** À supprimer ou fusionner avec PAID

---

## 🆕 Nouveau Statut: RETURNING_TO_SUPPLIER

### Définition
- Colis refusé en retour vers fournisseur
- Activé après 24-48h
- Concerne:
  - Colis avec 3 tentatives de livraison
  - Colis refusés par client

### Logique
```
REFUSED (jour J) → RETURNING_TO_SUPPLIER (J+1 ou J+2)
UNAVAILABLE (3 tentatives) → RETURNING_TO_SUPPLIER (J+1 ou J+2)
```

### Scanner Dépôt
- **Acceptation:** ✅ ou ❌ ?
- Votre instruction: ___________

---

## 📝 Configuration Scanner Dépôt

### Code Frontend (phone-scanner.blade.php)

```javascript
// Statuts ACCEPTÉS
const acceptedStatuses = [
    'CREATED',
    'AVAILABLE',
    'ACCEPTED',
    'PICKED_UP',
    'OUT_FOR_DELIVERY',
    'UNAVAILABLE'
];

// Statuts REJETÉS
const rejectedStatuses = [
    'DELIVERED',
    'PAID',
    'VERIFIED',
    'RETURNED',
    'CANCELLED',
    'REFUSED',
    'DELIVERED_PAID',
    'RETURNING_TO_SUPPLIER'  // Si refusé
];

// Messages personnalisés
const rejectedMessages = {
    'DELIVERED': 'Statut invalide: DELIVERED',
    'PAID': 'Statut invalide: PAID',
    'VERIFIED': 'Statut invalide: VERIFIED',
    'RETURNED': 'Statut invalide: RETURNED',
    'CANCELLED': 'Statut invalide: CANCELLED',
    'REFUSED': 'Statut invalide: REFUSED',
    'DELIVERED_PAID': 'Statut invalide: DELIVERED_PAID',
    'RETURNING_TO_SUPPLIER': 'Statut invalide: RETURNING_TO_SUPPLIER'
};

// Logique AT_DEPOT (inchangée)
if (status === 'AT_DEPOT') {
    if (depotName === currentDepot) {
        return reject(`Déjà au dépôt ${depotName}`);
    }
    // Transfert accepté
}
```

### Code Backend (DepotScanController.php)

```php
// Scanner - Charger colis actifs
$packages = DB::table('packages')
    ->whereNotIn('status', [
        'DELIVERED',
        'PAID',
        'VERIFIED',
        'RETURNED',
        'CANCELLED',
        'REFUSED',
        'DELIVERED_PAID',
        'RETURNING_TO_SUPPLIER'
    ])
    ->select('id', 'package_code as c', 'status as s', 'depot_manager_name as d')
    ->get();
```

---

## 🧪 Exemples de Scénarios

### Scénario 1: CREATED
```
Colis: PKG_001 (CREATED)
Scanner: Omar
Résultat: ✅ Accepté → AT_DEPOT (Omar)
```

### Scénario 2: AVAILABLE
```
Colis: PKG_002 (AVAILABLE)
Scanner: Omar
Résultat: ✅ Accepté → AT_DEPOT (Omar)
```

### Scénario 3: DELIVERED
```
Colis: PKG_003 (DELIVERED)
Scanner: Omar
Résultat: ❌ "Statut invalide: DELIVERED"
```

### Scénario 4: REFUSED
```
Colis: PKG_004 (REFUSED)
Scanner: Omar
Résultat: ❌ "Statut invalide: REFUSED"
```

### Scénario 5: UNAVAILABLE
```
Colis: PKG_005 (UNAVAILABLE)
Scanner: Omar
Résultat: ✅ Accepté → AT_DEPOT (Omar)
```

---

## ✅ IMPLÉMENTATION EFFECTUÉE

### Date: 2025-10-09

### Modifications Appliquées

#### 1. Backend - DepotScanController.php

**Méthode `scanner()` - Ligne 77-78:**
```php
->whereNotIn('status', ['DELIVERED', 'PAID', 'VERIFIED', 'RETURNED', 'CANCELLED', 'REFUSED', 'DELIVERED_PAID'])
```

**Méthode `addScannedCode()` - Ligne 185-186:**
```php
$rejectedStatuses = ['DELIVERED', 'PAID', 'VERIFIED', 'RETURNED', 'CANCELLED', 'REFUSED', 'DELIVERED_PAID'];
$acceptedStatuses = ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP', 'OUT_FOR_DELIVERY', 'UNAVAILABLE', 'AT_DEPOT'];
```

#### 2. Frontend - phone-scanner.blade.php

**Validation Manuelle + Caméra (Lignes 441-449 et 778-787):**
```javascript
const rejectedStatuses = ['DELIVERED', 'PAID', 'VERIFIED', 'RETURNED', 'CANCELLED', 'REFUSED', 'DELIVERED_PAID'];
const rejectedMessages = {
    'DELIVERED': 'Statut invalide: DELIVERED',
    'PAID': 'Statut invalide: PAID',
    'VERIFIED': 'Statut invalide: VERIFIED',
    'RETURNED': 'Statut invalide: RETURNED',
    'CANCELLED': 'Statut invalide: CANCELLED',
    'REFUSED': 'Statut invalide: REFUSED',
    'DELIVERED_PAID': 'Statut invalide: DELIVERED_PAID'
};
```

**Logique AT_DEPOT (Lignes 424-438 et 756-775):**
- Vérifie si `depotName === currentDepot`
- Si OUI → Rejette avec message "Déjà au dépôt {nom}"
- Si NON → Accepte comme transfert entre dépôts

### Résultat Final

#### ✅ Statuts ACCEPTÉS (7)
1. **CREATED** - Colis créé
2. **AVAILABLE** - Disponible pour pickup
3. **ACCEPTED** - Accepté par livreur
4. **PICKED_UP** - Ramassé
5. **OUT_FOR_DELIVERY** - En livraison
6. **UNAVAILABLE** - Client indisponible
7. **AT_DEPOT** - Au dépôt (si dépôt différent)

#### ❌ Statuts REFUSÉS (7)
1. **DELIVERED** → "Statut invalide: DELIVERED"
2. **PAID** → "Statut invalide: PAID"
3. **VERIFIED** → "Statut invalide: VERIFIED"
4. **RETURNED** → "Statut invalide: RETURNED"
5. **CANCELLED** → "Statut invalide: CANCELLED"
6. **REFUSED** → "Statut invalide: REFUSED"
7. **DELIVERED_PAID** → "Statut invalide: DELIVERED_PAID"

#### ⚠️ Cas Spécial
- **AT_DEPOT (même dépôt)** → "Déjà au dépôt {nom}"

---

## ⚠️ Notes sur RETURNING_TO_SUPPLIER

Ce statut a été mentionné par l'utilisateur mais n'est **pas encore implémenté** dans le système.

**Logique prévue:**
- REFUSED (jour J) → RETURNING_TO_SUPPLIER (J+1 ou J+2)
- UNAVAILABLE (3 tentatives) → RETURNING_TO_SUPPLIER (J+1 ou J+2)

**Action requise:** Créer migration + job automatique si nécessaire

---

## ⚠️ Notes sur VERIFIED

Ce statut est actuellement **REJETÉ** dans le scanner dépôt.

**Utilisations trouvées:**
- `app/Jobs/SyncOfflineActions.php:597` - Sets status to 'VERIFIED'
- `app/Models/Package.php:522` - Action 'return' for VERIFIED
- `app/Http/Controllers/DepotScanController.php:185` - Now in rejected list

**Suggestion:** Peut-être remplacer par RETURNING_TO_SUPPLIER selon workflow

---

## 📊 Résumé Final

### Acceptés (7 statuts)
✅ CREATED
✅ AVAILABLE
✅ ACCEPTED
✅ PICKED_UP
✅ OUT_FOR_DELIVERY
✅ UNAVAILABLE
✅ AT_DEPOT (si dépôt différent)

### Refusés (7+ statuts)
❌ DELIVERED - "Statut invalide: DELIVERED"
❌ PAID - "Statut invalide: PAID"
❌ VERIFIED - "Statut invalide: VERIFIED"
❌ RETURNED - "Statut invalide: RETURNED"
❌ CANCELLED - "Statut invalide: CANCELLED"
❌ REFUSED - "Statut invalide: REFUSED"
❌ DELIVERED_PAID - "Statut invalide: DELIVERED_PAID"
❌ AT_DEPOT (si même dépôt) - "Déjà au dépôt {nom}"

**Merci de confirmer pour RETURNING_TO_SUPPLIER et VERIFIED, puis j'applique tout immédiatement! 🚀**
