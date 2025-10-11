# 📦 DOCUMENTATION COMPLÈTE - SYSTÈME DE LIVRAISON AL-AMENA

---

## 📋 TABLE DES MATIÈRES

1. [Cycle de Vie des Commandes](#1-cycle-de-vie-des-commandes)
2. [Interface Scan Dépôt PC/Tel](#2-interface-scan-dépôt-pctel)
3. [Proposition: Interface Retours Fournisseur](#3-proposition-interface-retours-fournisseur)

---

# 1. CYCLE DE VIE DES COMMANDES

## 🔄 Vue d'Ensemble

Le système gère **10 statuts principaux** pour les colis, avec plusieurs flux possibles selon les situations.

---

## 📊 STATUTS DISPONIBLES

| Statut | Icône | Description | Acteur Principal |
|--------|-------|-------------|------------------|
| **CREATED** | 🆕 | Colis créé dans le système | Client/Commercial |
| **AVAILABLE** | 📋 | Disponible pour assignation | Dépôt Manager |
| **ACCEPTED** | ✅ | Accepté par un livreur | Livreur |
| **PICKED_UP** | 🚚 | Collecté par le livreur | Livreur |
| **AT_DEPOT** | 🏭 | Arrivé au dépôt | Chef Dépôt |
| **IN_TRANSIT** | 🚛 | En cours de livraison | Livreur |
| **OUT_FOR_DELIVERY** | 📍 | En route vers client | Livreur |
| **DELIVERED** | 📦 | Livré avec succès | Livreur |
| **PAID** | 💰 | COD payé au client | Commercial |
| **REFUSED** | ❌ | Refusé par client | Livreur |
| **RETURNED** | ↩️ | Retourné au fournisseur | Commercial |
| **CANCELLED** | 🚫 | Annulé | Client/Commercial |
| **UNAVAILABLE** | ⏳ | Client indisponible (tentative) | Livreur |

---

## 🎯 FLUX PRINCIPAL (Livraison Réussie)

```
┌─────────────┐
│   CREATED   │  Client crée la commande
│     🆕      │
└─────┬───────┘
      │
      ↓
┌─────────────┐
│  AVAILABLE  │  Commercial valide et met disponible
│     📋      │  OU Scan au dépôt → AT_DEPOT
└─────┬───────┘
      │
      ↓
┌─────────────┐
│  ACCEPTED   │  Livreur accepte la mission
│     ✅      │
└─────┬───────┘
      │
      ↓
┌─────────────┐
│ PICKED_UP   │  Livreur collecte le colis
│     🚚      │
└─────┬───────┘
      │
      ↓ (Peut passer par AT_DEPOT si retour au dépôt)
      │
┌─────────────┐
│ IN_TRANSIT  │  En route vers le client
│     🚛      │
└─────┬───────┘
      │
      ↓
┌─────────────┐
│ DELIVERED   │  ✅ Livré avec succès au client
│     📦      │  Signature + Preuve de livraison
└─────┬───────┘
      │
      ↓
┌─────────────┐
│    PAID     │  💰 COD versé au client
│     💰      │  Transaction finalisée
└─────────────┘

    ✅ FIN - SUCCÈS
```

---

## ❌ FLUX ALTERNATIFS

### **CAS 1 : Client Indisponible (1ère tentative)**

```
IN_TRANSIT → UNAVAILABLE (1)
      │           ⏳
      │       "Client absent"
      │
      ↓
  AT_DEPOT
      │
      ↓
 Nouvelle tentative → IN_TRANSIT
```

**Règle :** Maximum **3 tentatives** UNAVAILABLE

---

### **CAS 2 : Client Indisponible (3ème tentative)**

```
UNAVAILABLE (3) → RETURNED
      ⏳              ↩️
   "3 tentatives"  "Retour fournisseur"

      ↓
   CANCELLED (optionnel)
      🚫
```

**Action :**
- Après **3 tentatives UNAVAILABLE** → Statut automatique **RETURNED**
- Commercial décide : Nouvelle livraison OU Annulation

---

### **CAS 3 : Client Refuse le Colis**

```
IN_TRANSIT → REFUSED
      🚛         ❌
              "Client refuse"
      │
      ↓
  AT_DEPOT (retour dépôt)
      │
      ↓
   RETURNED (retour fournisseur)
      ↩️
```

**Action :**
- Photo de preuve obligatoire
- Raison du refus enregistrée
- Retour immédiat au dépôt puis fournisseur

---

### **CAS 4 : Colis Annulé**

```
CREATED/AVAILABLE/ACCEPTED → CANCELLED
         🆕/📋/✅                 🚫
                            "Demande client"

                                ↓
                            RETURNED (si déjà expédié)
                                ↩️
```

**Règles :**
- Annulation gratuite si **CREATED** ou **AVAILABLE**
- Frais si **ACCEPTED** ou après
- Si déjà en livraison → Retour dépôt puis fournisseur

---

### **CAS 5 : Problème de Livraison**

```
IN_TRANSIT → AT_DEPOT
      🚛         🏭
            "Retour technique"

      Exemples :
      - Adresse incorrecte
      - Colis endommagé
      - Erreur d'assignation

      ↓

  Options :
  1. Réassigner → ACCEPTED (nouveau livreur)
  2. Retour → RETURNED
  3. Annuler → CANCELLED
```

---

## 🔁 FLUX RETOURS (Détaillé)

### **Scénario A : Retour Normal**

```
   (Refusé OU 3x Indisponible)
              ↓
         AT_DEPOT
         🏭 (Scan retour)
              ↓
     RETURNED_TO_DEPOT
     ↩️ "Attente traitement"
              ↓
   [COMMERCIAL TRAITE]
              ↓
    ┌────────┴────────┐
    │                 │
CANCELLED        RETURNED
   🚫          ↩️ "Vers fournisseur"

              ↓
      SUPPLIER_PICKUP
      📤 "Récupéré par fournisseur"
```

---

### **Scénario B : Retour Express (Demande Client)**

```
Client demande retour après livraison
              ↓
        DELIVERED
           📦
              ↓
    RETURN_REQUESTED
    ↩️ "Demande retour client"
              ↓
  Livreur récupère → PICKED_UP_FOR_RETURN
                          🚚
              ↓
         AT_DEPOT
              ↓
        RETURNED
```

---

## 📈 STATISTIQUES PAR STATUT

### **Statuts Actifs (en cours)**
- CREATED, AVAILABLE, ACCEPTED, PICKED_UP, AT_DEPOT, IN_TRANSIT, OUT_FOR_DELIVERY, UNAVAILABLE

### **Statuts Terminés (finalisés)**
- **DELIVERED** ✅ Succès
- **PAID** ✅ Succès + Payé
- **RETURNED** ↩️ Échec (retour fournisseur)
- **CANCELLED** 🚫 Échec (annulé)
- **REFUSED** ❌ Échec (refusé client)

### **Taux de Réussite**
```
Taux de livraison = (DELIVERED + PAID) / Total colis × 100%
Taux d'échec = (RETURNED + CANCELLED + REFUSED) / Total colis × 100%
Taux indisponibilité = UNAVAILABLE / Total tentatives × 100%
```

---

## 🔐 RÈGLES MÉTIER IMPORTANTES

### **1. Transitions Interdites**

❌ **DELIVERED** → **UNAVAILABLE** (logiquement impossible)
❌ **PAID** → **RETURNED** (transaction finalisée)
❌ **CANCELLED** → **DELIVERED** (commande annulée)

### **2. Compteur UNAVAILABLE**

```php
// Pseudo-code
if ($package->unavailable_attempts >= 3) {
    $package->status = 'RETURNED';
    $package->return_reason = 'Client indisponible après 3 tentatives';
}
```

### **3. Validation COD**

```php
// Le COD doit être payé avant PAID
if ($package->status === 'DELIVERED' && $codPaid) {
    $package->status = 'PAID';
}
```

### **4. Retours Multiples**

Un colis peut être scanné plusieurs fois au dépôt :
- 1ère fois : CREATED → **AT_DEPOT** (réception initiale)
- 2ème fois : PICKED_UP → **AT_DEPOT** (retour après échec)
- 3ème fois : AT_DEPOT → **RETURNED** (retour fournisseur confirmé)

---

## 📊 TABLEAU RÉCAPITULATIF DES TRANSITIONS

| De | Vers | Condition | Acteur |
|----|------|-----------|--------|
| CREATED | AVAILABLE | Validation | Commercial |
| CREATED | AT_DEPOT | Scan dépôt | Chef Dépôt |
| AVAILABLE | ACCEPTED | Assignation | Livreur |
| ACCEPTED | PICKED_UP | Confirmation collecte | Livreur |
| PICKED_UP | AT_DEPOT | Retour dépôt | Chef Dépôt |
| PICKED_UP | IN_TRANSIT | Départ livraison | Livreur |
| IN_TRANSIT | DELIVERED | Livraison réussie | Livreur |
| IN_TRANSIT | UNAVAILABLE | Client absent | Livreur |
| IN_TRANSIT | REFUSED | Client refuse | Livreur |
| IN_TRANSIT | AT_DEPOT | Problème technique | Livreur |
| DELIVERED | PAID | COD versé | Commercial |
| UNAVAILABLE | AT_DEPOT | Retour après échec | Chef Dépôt |
| AT_DEPOT | RETURNED | Retour fournisseur | Commercial |
| REFUSED | AT_DEPOT | Retour après refus | Chef Dépôt |
| * | CANCELLED | Annulation | Client/Commercial |

---

# 2. INTERFACE SCAN DÉPÔT PC/TEL

## 🎯 Objectif

Système de scan collaboratif **PC + Téléphone** pour réceptionner rapidement les colis au dépôt avec mise à jour en temps réel.

---

## 🖥️ ARCHITECTURE

```
┌─────────────────────────────────────────────────────┐
│               PC - DASHBOARD CHEF DÉPÔT              │
│  ┌───────────────────────────────────────────────┐  │
│  │  📱 QR Code + Code 8 chiffres                 │  │
│  │  ┌─────────────┐                              │  │
│  │  │   ██████    │  CODE: 12345678              │  │
│  │  │   ██████    │                              │  │
│  │  │   ██████    │                              │  │
│  │  └─────────────┘                              │  │
│  │                                                │  │
│  │  📊 Statistiques                               │  │
│  │  ┏━━━━━━━━━━━━━━━━┓                           │  │
│  │  ┃ 45 Colis       ┃                           │  │
│  │  ┃ Scannés        ┃                           │  │
│  │  ┗━━━━━━━━━━━━━━━━┛                           │  │
│  │                                                │  │
│  │  📋 Liste en Temps Réel                        │  │
│  │  [COL001] - 14:32:15                          │  │
│  │  [COL002] - 14:32:18                          │  │
│  │  [COL003] - 14:32:20                          │  │
│  │                                                │  │
│  │  [✅ Valider Réception au Dépôt]               │  │
│  └───────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────┘
                        │
                        │ WebSocket / Polling
                        │ (toutes les 1 seconde)
                        ↓
┌─────────────────────────────────────────────────────┐
│             📱 TÉLÉPHONE - SCANNER                   │
│  ┌───────────────────────────────────────────────┐  │
│  │  📷 Caméra Active                             │  │
│  │  ┌───────────────────────────────┐            │  │
│  │  │                               │            │  │
│  │  │      [Viseur Caméra]          │            │  │
│  │  │      Scannez le colis         │            │  │
│  │  │                               │            │  │
│  │  └───────────────────────────────┘            │  │
│  │                                                │  │
│  │  📝 Saisie Manuelle                            │  │
│  │  [____________] ➕ Ajouter                     │  │
│  │                                                │  │
│  │  📋 45 colis scanné(s)                         │  │
│  │  ✅ COL001 - Colis valide                     │  │
│  │  ✅ COL002 - Colis valide                     │  │
│  │  ✅ COL003 - Colis valide                     │  │
│  │                                                │  │
│  │  [✅ Valider Réception (45 colis)]             │  │
│  └───────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────┘
```

---

## 📱 FONCTIONNALITÉS

### **A. Interface PC (Dashboard)**

**1. Génération de Session**
```
- QR Code unique généré automatiquement
- Code de session à 8 chiffres aléatoires
- Nom du chef de dépôt (récupéré depuis compte connecté)
- Session UUID pour sécurité
```

**2. Affichage en Temps Réel**
```
✅ Connexion téléphone détectée (indicateur vert)
📊 Compteur de colis scannés
📋 Liste actualisée toutes les 1 seconde
⏱️ Heure de scan de chaque colis
📈 Taux de scan par minute
```

**3. Actions Disponibles**
```
✅ Valider Réception au Dépôt → Tous les colis → status = AT_DEPOT
📄 Exporter CSV (code, heure, statut)
🔄 Nouvelle Session (génère nouveau QR + code)
```

**4. Popup après Validation**
```
┌─────────────────────────────────────┐
│            ✅                        │
│     Validation Réussie !            │
│                                     │
│  45 colis validés et marqués       │
│         AT_DEPOT                    │
│                                     │
│  La session téléphone a été        │
│  automatiquement terminée.         │
│                                     │
│  [🔄 Démarrer une Nouvelle Session]│
│  [❌ Fermer]                        │
└─────────────────────────────────────┘
```

---

### **B. Interface Téléphone (Scanner)**

**1. Connexion à la Session**
```
Méthode 1: Scanner le QR Code sur PC
Méthode 2: Saisir le code à 8 chiffres
    → Accès à /depot/enter-code
    → Saisie du code
    → Redirection vers scanner
```

**2. Scan des Colis**
```
📷 Caméra Arrière Activée
   - Scan QR Code (jsQR library)
   - Scan Code-Barres (Quagga.js)
   - Alternance automatique (600ms)

📝 Saisie Manuelle
   - Input texte avec validation temps réel
   - Vérification dans base de données locale
   - Indicateurs couleur (vert=valide, rouge=invalide)
```

**3. Validation Locale (Offline-Ready)**
```javascript
// Colis chargés au démarrage (JSON)
PACKAGES_DATA = [
    {c: 'COL001', s: 'PICKED_UP', id: 123},
    {c: 'COL002', s: 'CREATED', id: 124},
    ...
]

// Validation instantanée (pas d'API)
✅ Statuts ACCEPTÉS : CREATED, AVAILABLE, ACCEPTED,
                      PICKED_UP, OUT_FOR_DELIVERY,
                      UNAVAILABLE, AT_DEPOT (autre dépôt)

❌ Statuts REFUSÉS : DELIVERED, PAID, VERIFIED,
                      RETURNED, CANCELLED, REFUSED
```

**4. Gestion des Doublons**
```
- Recherche dans liste locale scannée
- Comparaison avec variantes (avec/sans underscore)
- Feedback visuel + vibration si doublon
- Message : "✅ Déjà scanné"
```

**5. Synchronisation Serveur**
```
- Ajout local immédiat (UX rapide)
- Sync serveur en arrière-plan (non bloquant)
- Mise à jour cache Laravel toutes les 1s
- Heartbeat pour maintenir session active
```

**6. Validation Finale**
```
[✅ Valider Réception (45 colis)]

Confirmation → Envoi AJAX → Mise à jour BD
              ↓
         status = AT_DEPOT
         depot_manager_name = "Omar"
              ↓
         Session terminée
              ↓
         Popup "Session Terminée"
```

---

## 🔄 FLUX DE DONNÉES

```
1. PC génère session
   ├─ Session ID (UUID)
   ├─ Code 8 chiffres
   ├─ Nom chef dépôt
   └─ Cache Laravel (8h TTL)

2. Tel se connecte
   ├─ Scan QR ou saisie code
   ├─ Charge TOUS les colis (JSON)
   ├─ Validation 100% locale
   └─ Sync serveur en background

3. Scan colis (Tel)
   ├─ Vérifie dans PACKAGES_DATA
   ├─ Vérifie statuts autorisés
   ├─ Vérifie doublons
   ├─ Ajoute à scannedCodes[]
   ├─ POST /depot/scan/{sessionId}/add
   └─ Cache mis à jour

4. PC polling (1s)
   ├─ GET /depot/api/session/{sessionId}/status
   ├─ Récupère scanned_packages
   ├─ Met à jour compteur
   └─ Affiche liste

5. Validation (PC OU Tel)
   ├─ POST /depot/scan/{sessionId}/validate-all
   ├─ UPDATE packages SET status='AT_DEPOT'
   ├─ Session marquée 'completed'
   ├─ Tel détecte en 3s max
   └─ Popups affichés des 2 côtés
```

---

## 🛡️ SÉCURITÉ & ROBUSTESSE

### **1. Session Management**
```
- UUID unique par session
- Code 8 chiffres (collision minimale)
- TTL 8 heures (expire automatiquement)
- Vérification existence session à chaque requête
```

### **2. Détection Session Terminée**
```
Tel vérifie toutes les 3 secondes :
GET /depot/api/session/{sessionId}/check-activity

Réponse :
{
    "active": false,
    "reason": "completed" | "expired" | "inactivity"
}

→ Popup automatique
→ Désactivation interface
→ Redirection vers saisie code
```

### **3. Heartbeat**
```
PC : Toutes les 3s → POST /heartbeat
Tel : Toutes les 10s → POST /update-activity

Si inactif 30 min → Session auto-terminée
```

### **4. Gestion Erreurs**
```
❌ Code non trouvé → Message explicite
❌ Statut invalide → Raison affichée
❌ Session expirée → Page dédiée
❌ Doublon → Feedback visuel
```

---

## 📊 AVANTAGES DU SYSTÈME

✅ **Performance** : Validation locale = scan ultra-rapide
✅ **Fiabilité** : Fonctionne même avec connexion lente
✅ **UX** : Feedback immédiat, pas d'attente
✅ **Scalabilité** : Peut gérer 100+ colis/minute
✅ **Collaboration** : Plusieurs scanners sur 1 session
✅ **Traçabilité** : Heure précise + nom chef dépôt
✅ **Mobile-First** : PWA, caméra arrière, offline

---

## 🔧 TECHNOLOGIES UTILISÉES

### **Backend**
- Laravel 11 (PHP)
- Cache (sessions temporaires)
- Eloquent ORM
- AJAX/JSON API

### **Frontend PC**
- Tailwind CSS
- Alpine.js
- qrcode-generator.js
- Polling JavaScript

### **Frontend Mobile**
- Alpine.js
- Quagga.js (code-barres)
- jsQR (QR codes)
- PWA (service worker)
- getUserMedia API (caméra)

---

# 3. PROPOSITION: INTERFACE RETOURS FOURNISSEUR

## 🎯 Objectif

Interface dédiée pour gérer les colis avec statut **"RETURNED"** ou **"En cours de retour fournisseur"**, similaire au scan dépôt mais avec des fonctionnalités spécifiques aux retours.

---

## 📋 FONCTIONNALITÉS SPÉCIFIQUES

### **Différences vs Scan Dépôt**

| Fonctionnalité | Scan Dépôt | Scan Retours Fournisseur |
|----------------|------------|--------------------------|
| **Statuts acceptés** | CREATED, PICKED_UP, etc. | REFUSED, UNAVAILABLE (3x), AT_DEPOT |
| **Statut final** | AT_DEPOT | RETURNED |
| **Informations captées** | Nom chef dépôt | Raison retour + Fournisseur |
| **Actions post-scan** | Assignation livreur | Contact fournisseur |
| **Validation** | Réception dépôt | Retour confirmé fournisseur |

---

## 🖥️ INTERFACE PROPOSÉE

### **A. PC - Dashboard Retours**

```
┌────────────────────────────────────────────────────┐
│  🏭 SCAN RETOURS FOURNISSEUR - Chef: Omar          │
├────────────────────────────────────────────────────┤
│                                                    │
│  📱 QR Code Session                                │
│  ┌─────────────┐                                  │
│  │   ██████    │  CODE: 87654321                  │
│  │   ██████    │                                  │
│  └─────────────┘                                  │
│                                                    │
│  📊 Statistiques Retours                           │
│  ┌──────────┬──────────┬──────────┐              │
│  │ Refusés  │ 3x Indis.│ Défauts  │              │
│  │   12     │    8     │    3     │              │
│  └──────────┴──────────┴──────────┘              │
│                                                    │
│  📋 Colis en Attente Retour                        │
│  ┌────────────────────────────────────────────┐  │
│  │ COL001 - REFUSED - "Client refuse"         │  │
│  │   → Fournisseur: Shop ABC                  │  │
│  │   → Date: 15/01/2025                       │  │
│  │                                             │  │
│  │ COL002 - UNAVAILABLE (3) - "Absent 3x"    │  │
│  │   → Fournisseur: Boutique XYZ             │  │
│  │   → Date: 14/01/2025                       │  │
│  └────────────────────────────────────────────┘  │
│                                                    │
│  📦 Scannés pour Retour (23)                       │
│  [COL003] Refusé - 14:32:15                       │
│  [COL004] Indispo 3x - 14:32:18                   │
│                                                    │
│  [✅ Valider Retours Fournisseur (23)]             │
│  [📄 Exporter Bordereau Retour]                   │
│  [📧 Notifier Fournisseurs]                       │
└────────────────────────────────────────────────────┘
```

---

### **B. Téléphone - Scanner Retours**

```
┌────────────────────────────────────────────────┐
│  ↩️ Scanner Retours Fournisseur                │
│  Chef: Omar                                    │
├────────────────────────────────────────────────┤
│                                                │
│  📷 Caméra Active                              │
│  ┌──────────────────────────────┐             │
│  │                              │             │
│  │    [Viseur Scan]             │             │
│  │    Scannez colis retour      │             │
│  └──────────────────────────────┘             │
│                                                │
│  📝 Saisie Manuelle                            │
│  [____________] ➕                             │
│                                                │
│  ✅ 23 retours scanné(s)                       │
│                                                │
│  📋 Liste                                      │
│  ┌──────────────────────────────────┐         │
│  │ ✅ COL001                        │         │
│  │    ❌ REFUSED - Shop ABC         │         │
│  │    📝 Client refuse              │         │
│  ├──────────────────────────────────┤         │
│  │ ✅ COL002                        │         │
│  │    ⏳ UNAVAILABLE (3) - XYZ      │         │
│  │    📝 Absent 3 tentatives        │         │
│  └──────────────────────────────────┘         │
│                                                │
│  [✅ Valider Retours (23 colis)]               │
└────────────────────────────────────────────────┘
```

---

## 🔄 FLUX SPÉCIFIQUE RETOURS

```
1. Filtre Colis Éligibles
   ├─ REFUSED (client refuse)
   ├─ UNAVAILABLE (3 tentatives)
   ├─ AT_DEPOT (retour technique)
   └─ CANCELLED (avec frais)

2. Scan Retour
   ├─ Vérifie statut éligible
   ├─ Capture raison retour
   ├─ Identifie fournisseur (depuis BD)
   └─ Ajoute à liste retours

3. Informations Complémentaires
   ├─ Raison retour (obligatoire)
   ├─ État colis (intact/endommagé)
   ├─ Photos (optionnel)
   └─ Commentaire chef dépôt

4. Validation Retours
   ├─ UPDATE packages SET status='RETURNED'
   ├─ Génération bordereau retour PDF
   ├─ Notification fournisseurs (email/SMS)
   └─ Log historique retour

5. Actions Post-Validation
   ├─ Impression bordereau
   ├─ Groupement par fournisseur
   ├─ Suivi récupération fournisseur
   └─ Statistiques retours
```

---

## 📄 BORDEREAU DE RETOUR (Auto-Généré)

```
╔════════════════════════════════════════════════╗
║     BORDEREAU DE RETOUR FOURNISSEUR            ║
╠════════════════════════════════════════════════╣
║                                                ║
║  Date: 15/01/2025 14:35                       ║
║  Chef Dépôt: Omar                             ║
║  Session: RET-12345678                        ║
║                                                ║
╠════════════════════════════════════════════════╣
║  FOURNISSEUR: Shop ABC                         ║
║  Contact: +216 XX XXX XXX                     ║
╠════════════════════════════════════════════════╣
║                                                ║
║  📦 COLIS À RÉCUPÉRER (8)                      ║
║                                                ║
║  1. COL001 - REFUSED                          ║
║     Raison: Client refuse                     ║
║     COD: 125.500 DT                           ║
║                                                ║
║  2. COL005 - UNAVAILABLE                      ║
║     Raison: 3 tentatives échouées             ║
║     COD: 89.000 DT                            ║
║                                                ║
║  ... (6 autres)                               ║
║                                                ║
╠════════════════════════════════════════════════╣
║  TOTAL: 8 colis                               ║
║  MONTANT COD: 1,234.500 DT                    ║
╠════════════════════════════════════════════════╣
║                                                ║
║  Signature Chef Dépôt:    Signature Fournisseur:  ║
║                                                ║
║  _________________        _________________   ║
║                                                ║
╚════════════════════════════════════════════════╝
```

---

## 📊 TABLEAU DE BORD RETOURS

### **Statistiques Affichées**

```
┌─────────────────────────────────────────┐
│  📊 STATISTIQUES RETOURS DU MOIS        │
├─────────────────────────────────────────┤
│                                         │
│  Total Retours: 234                     │
│                                         │
│  Par Raison:                            │
│  ├─ Refusé Client: 98 (42%)            │
│  ├─ Indisponible 3x: 76 (32%)          │
│  ├─ Adresse Erronée: 34 (15%)          │
│  ├─ Colis Endommagé: 16 (7%)           │
│  └─ Autre: 10 (4%)                     │
│                                         │
│  Par Fournisseur:                       │
│  ├─ Shop ABC: 45 retours               │
│  ├─ Boutique XYZ: 32 retours           │
│  └─ Autres: 157 retours                │
│                                         │
│  Temps Moyen Traitement: 2.3 jours     │
│  Taux Récupération: 87%                │
└─────────────────────────────────────────┘
```

---

## 🛠️ ROUTES PROPOSÉES

```php
// routes/depot-returns.php

// Dashboard PC
Route::get('/depot/returns', [DepotReturnController::class, 'dashboard'])
    ->name('depot.returns.dashboard');

// Interface téléphone
Route::get('/depot/returns/{sessionId}', [DepotReturnController::class, 'scanner'])
    ->name('depot.returns.phone');

// Saisie code
Route::get('/depot/returns/enter-code', [DepotReturnController::class, 'enterCode'])
    ->name('depot.returns.enter.code');

// Validation code
Route::post('/depot/returns/validate-code', [DepotReturnController::class, 'validateCode'])
    ->name('depot.returns.validate.code');

// Scan colis retour
Route::post('/depot/returns/{sessionId}/add', [DepotReturnController::class, 'addReturnPackage'])
    ->name('depot.returns.add');

// Valider retours
Route::post('/depot/returns/{sessionId}/validate-all', [DepotReturnController::class, 'validateReturns'])
    ->name('depot.returns.validate.all');

// Exporter bordereau
Route::get('/depot/returns/{sessionId}/export-slip', [DepotReturnController::class, 'exportSlip'])
    ->name('depot.returns.export.slip');

// Notifier fournisseurs
Route::post('/depot/returns/{sessionId}/notify-suppliers', [DepotReturnController::class, 'notifySuppliers'])
    ->name('depot.returns.notify');

// API
Route::prefix('depot/returns/api')->group(function () {
    Route::get('/session/{sessionId}/status', [DepotReturnController::class, 'getSessionStatus']);
    Route::get('/session/{sessionId}/check-activity', [DepotReturnController::class, 'checkActivity']);
    Route::post('/session/{sessionId}/heartbeat', [DepotReturnController::class, 'heartbeat']);
});
```

---

## 💡 AMÉLIORATIONS SUGGÉRÉES

### **1. Traçabilité Photos**
```
- Photo colis avant retour
- Photo preuve retour fournisseur
- Stockage cloud (S3/local)
```

### **2. Workflow Fournisseur**
```
- Portal fournisseur pour consulter retours
- Planification rendez-vous récupération
- QR code bordereau pour validation
```

### **3. Notifications Automatiques**
```
- SMS fournisseur quand colis prêt
- Email avec bordereau PDF
- Rappels si pas récupéré (7 jours)
```

### **4. Gestion Stock Retours**
```
- Zone dépôt dédiée retours
- Scan zone rangement
- Alerte si >30 jours non récupéré
```

---

## ✅ CHECKLIST DÉVELOPPEMENT

- [ ] Créer `DepotReturnController`
- [ ] Créer vues `depot/returns/*`
- [ ] Ajouter routes `depot-returns.php`
- [ ] Créer migration `add_return_fields_to_packages`
- [ ] Implémenter validation statuts retours
- [ ] Générer PDF bordereau
- [ ] Système notification fournisseurs
- [ ] Tests unitaires
- [ ] Tests e2e (scan → validation)

---

## 📝 NOTES IMPORTANTES

1. **Compatibilité** : Réutiliser maximum de code du scan dépôt
2. **Sécurité** : Même système session UUID + code 8 chiffres
3. **Performance** : Validation locale identique
4. **UX** : Interface cohérente avec scan dépôt
5. **Mobile-First** : PWA, offline-ready

---

**FIN DE LA DOCUMENTATION**

---

*Document créé le 10/10/2025*
*Version 1.0*
*Système Al-Amena Delivery*
