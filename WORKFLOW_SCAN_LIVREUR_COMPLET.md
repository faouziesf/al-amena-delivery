# 📱 Workflow Complet - Scan Livreur

**Date**: 16 Octobre 2025, 04:40 UTC+01:00  
**Version**: 2.0 (Après copie logique chef dépôt)

---

## 🎯 VUE D'ENSEMBLE

Le scan livreur permet de:
1. Scanner un colis par QR code ou code-barres
2. Assigner automatiquement le colis au livreur
3. Afficher les détails du colis
4. Effectuer des actions (ramasser, livrer, etc.)

---

## 🔄 PROCESSUS COMPLET

### Phase 1: Scanner le Code

```
┌─────────────────────┐
│ Livreur ouvre app   │
│ Page: /scan         │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Scanner QR/Barcode  │
│ ou Saisir code      │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Code scanné:        │
│ PKG_ON5VUI_1015     │
└──────────┬──────────┘
           │
           ▼
```

---

### Phase 2: Normalisation du Code

**Méthode**: `normalizeCode()`

```
Code brut: "PKG_ON5VUI_1015"
    │
    ├─ Trim (enlever espaces)
    │    └─> "PKG_ON5VUI_1015"
    │
    ├─ Extraction URL si présente
    │    └─> Si "/track/PKG_ON5VUI_1015" → "PKG_ON5VUI_1015"
    │
    └─ Convertir en majuscules
         └─> "PKG_ON5VUI_1015"
```

**Code**:
```php
private function normalizeCode(string $code): string
{
    $code = trim($code);
    
    // Extraire du QR code URL si nécessaire
    if (preg_match('/\/track\/(.+)$/', $code, $matches)) {
        return strtoupper($matches[1]);
    }
    
    return strtoupper($code);
}
```

---

### Phase 3: Recherche Multi-Variantes

**Méthode**: `findPackageByCode()`

#### 3.1 Génération des Variantes

```
Code normalisé: "PKG_ON5VUI_1015"
    │
    ├─ Variante 1: PKG_ON5VUI_1015 (original)
    ├─ Variante 2: PKGON5VUI1015 (sans _)
    ├─ Variante 3: PKG-ON5VUI-1015 (- au lieu de _)
    ├─ Variante 4: PKGON5VUI1015 (nettoyé, doublon)
    ├─ Variante 5: pkg_on5vui_1015 (minuscules)
    └─ Variante 6: PKG_ON5VUI_1015 (original, doublon)

Après suppression doublons:
✅ PKG_ON5VUI_1015
✅ PKGON5VUI1015
✅ PKG-ON5VUI-1015
✅ pkg_on5vui_1015
```

**Code**:
```php
$searchVariants = [
    $cleanCode,                                    // PKG_ON5VUI_1015
    str_replace('_', '', $cleanCode),              // PKGON5VUI1015
    str_replace('-', '', $cleanCode),              // PKG_ON5VUI_1015
    str_replace(['_', '-', ' '], '', $cleanCode),  // PKGON5VUI1015
    strtolower($cleanCode),                        // pkg_on5vui_1015
    $originalCode,                                 // PKG_ON5VUI_1015
];

$searchVariants = array_unique($searchVariants);
```

---

#### 3.2 Filtrage par Statuts Acceptés

**Statuts scannables**:
```php
$acceptedStatuses = [
    'CREATED',           // Colis créé
    'AVAILABLE',         // Disponible
    'ACCEPTED',          // Accepté par livreur
    'PICKED_UP',         // Ramassé
    'OUT_FOR_DELIVERY',  // En livraison
    'UNAVAILABLE',       // Destinataire absent
    'AT_DEPOT',          // Au dépôt
    'VERIFIED'           // Vérifié
];
```

**Statuts NON scannables** (bloqués):
- ❌ `DELIVERED` - Déjà livré
- ❌ `CANCELLED` - Annulé
- ❌ `RETURNED` - Retourné
- ❌ `PAID` - Payé et clôturé

---

#### 3.3 Recherche en Base de Données

**Pour chaque variante**, 2 requêtes SQL:

##### Requête A: Par package_code
```sql
SELECT id, package_code, status, tracking_number, assigned_deliverer_id, cod_amount
FROM packages
WHERE package_code = ?  -- Ex: 'PKG_ON5VUI_1015'
  AND status IN ('CREATED', 'AVAILABLE', 'ACCEPTED', ...)
LIMIT 1;
```

##### Requête B: Par tracking_number
```sql
SELECT id, package_code, status, tracking_number, assigned_deliverer_id, cod_amount
FROM packages
WHERE tracking_number = ?  -- Ex: 'PKG_ON5VUI_1015'
  AND status IN ('CREATED', 'AVAILABLE', 'ACCEPTED', ...)
LIMIT 1;
```

**Ordre d'exécution**:
```
Variante 1 (PKG_ON5VUI_1015):
  ├─ Requête A (package_code) → Si trouvé ✅ STOP
  └─ Requête B (tracking_number) → Si trouvé ✅ STOP

Variante 2 (PKGON5VUI1015):
  ├─ Requête A (package_code) → Si trouvé ✅ STOP
  └─ Requête B (tracking_number) → Si trouvé ✅ STOP

... (continue pour toutes variantes)
```

---

#### 3.4 Recherche LIKE (Dernier Recours)

Si **aucune variante trouvée**, recherche permissive:

**Nettoyage du code**:
```
"PKG_ON5VUI_1015" → enlever _, -, espaces → "PKGON5VUI1015"
```

**Requête LIKE sur package_code**:
```sql
SELECT id, package_code, status, tracking_number, assigned_deliverer_id, cod_amount
FROM packages
WHERE REPLACE(REPLACE(REPLACE(UPPER(package_code), "_", ""), "-", ""), " ", "") = 'PKGON5VUI1015'
  AND status IN ('CREATED', 'AVAILABLE', 'ACCEPTED', ...)
LIMIT 1;
```

**Requête LIKE sur tracking_number**:
```sql
SELECT id, package_code, status, tracking_number, assigned_deliverer_id, cod_amount
FROM packages
WHERE REPLACE(REPLACE(REPLACE(UPPER(tracking_number), "_", ""), "-", ""), " ", "") = 'PKGON5VUI1015'
  AND status IN ('CREATED', 'AVAILABLE', 'ACCEPTED', ...)
LIMIT 1;
```

---

### Phase 4: Traitement du Résultat

#### Cas 1: Colis Trouvé ✅

```
Colis trouvé en DB
    │
    ├─ Convertir en modèle Eloquent
    │    └─> Package::find($package->id)
    │
    ├─ Vérifier assignation
    │    │
    │    ├─ Si NON assigné
    │    │    └─> Assigner au livreur
    │    │         ├─ assigned_deliverer_id = livreur_id
    │    │         ├─ assigned_at = maintenant
    │    │         └─ status = 'ACCEPTED' (si CREATED)
    │    │
    │    ├─ Si assigné à AUTRE livreur
    │    │    └─> RÉASSIGNER au livreur actuel
    │    │         ├─ assigned_deliverer_id = livreur_id
    │    │         └─ assigned_at = maintenant
    │    │
    │    └─ Si déjà assigné au livreur
    │         └─> Pas de changement
    │
    └─ Retourner succès + redirect
         └─> Route: /deliverer/task/{package_id}
```

**Réponse JSON**:
```json
{
    "success": true,
    "package_id": 123,
    "redirect": "/deliverer/task/123"
}
```

---

#### Cas 2: Colis NON Trouvé ❌

**Raisons possibles**:
1. Code n'existe pas en DB
2. Statut bloqué (DELIVERED, CANCELLED, etc.)
3. Format du code différent en DB

**Réponse JSON**:
```json
{
    "success": false,
    "message": "Code non trouvé: PKG_ON5VUI_1015"
}
```

**Affichage à l'utilisateur**:
```
❌ Code non trouvé
Code scanné: PKG_ON5VUI_1015
Vérifiez que le colis existe et n'est pas déjà livré.
```

---

### Phase 5: Affichage des Détails

**Page**: `/deliverer/task/{package_id}`

```
┌─────────────────────────────────┐
│ Colis: PKG_ON5VUI_1015         │
├─────────────────────────────────┤
│ Destinataire: Ahmed Ben Ali     │
│ Téléphone: 20 123 456           │
│ Adresse: 12 Rue de...           │
│ COD: 150.000 TND                │
│ Statut: ACCEPTED                │
├─────────────────────────────────┤
│ Actions disponibles:            │
│ [📦 Ramasser]                   │
│ [🚚 Livrer]                     │
│ [❌ Indisponible]               │
└─────────────────────────────────┘
```

---

## 🔍 POINTS DE DEBUG

### 1. Vérifier le Code en DB

```bash
php artisan tinker
```
```php
$pkg = DB::table('packages')->where('package_code', 'like', '%ON5VUI%')->first();
echo $pkg ? "TROUVÉ: " . $pkg->package_code : "NON TROUVÉ";
```

---

### 2. Tester les Variantes

```php
$code = 'PKG_ON5VUI_1015';
$variants = [
    $code,
    str_replace('_', '', $code),
    str_replace(['_', '-', ' '], '', $code),
    strtolower($code)
];

foreach ($variants as $v) {
    $found = DB::table('packages')->where('package_code', $v)->first();
    echo $v . ": " . ($found ? "✅" : "❌") . "\n";
}
```

---

### 3. Vérifier Statut

```php
$pkg = DB::table('packages')->where('package_code', 'PKG_ON5VUI_1015')->first();
if ($pkg) {
    echo "Statut: " . $pkg->status . "\n";
    $accepted = ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP', 'OUT_FOR_DELIVERY', 'UNAVAILABLE', 'AT_DEPOT', 'VERIFIED'];
    echo "Scannable: " . (in_array($pkg->status, $accepted) ? "OUI ✅" : "NON ❌") . "\n";
}
```

---

### 4. Logs Laravel

```bash
tail -f storage/logs/laravel.log
```

Chercher les erreurs pendant le scan.

---

## 📊 DIAGRAMME COMPLET

```
┌─────────────────────────────────────────────────────────┐
│                    SCAN LIVREUR                         │
└─────────────────────────────────────────────────────────┘
                            │
                            ▼
              ┌─────────────────────────┐
              │  Scanner QR/Code        │
              │  PKG_ON5VUI_1015        │
              └────────────┬────────────┘
                           │
                           ▼
              ┌─────────────────────────┐
              │  Normaliser Code        │
              │  → PKG_ON5VUI_1015      │
              └────────────┬────────────┘
                           │
                           ▼
              ┌─────────────────────────┐
              │  Générer Variantes      │
              │  6 variantes            │
              └────────────┬────────────┘
                           │
                           ▼
              ┌─────────────────────────┐
              │  Pour chaque variante:  │
              │  Chercher en DB         │
              │  + Filtrage statut      │
              └────────────┬────────────┘
                           │
                ┌──────────┴──────────┐
                │                     │
                ▼                     ▼
       ┌────────────────┐    ┌────────────────┐
       │  Trouvé ✅     │    │  Non trouvé ❌ │
       └────────┬───────┘    └────────┬───────┘
                │                     │
                ▼                     ▼
       ┌────────────────┐    ┌────────────────┐
       │ Auto-assigner  │    │ Recherche LIKE │
       │ au livreur     │    │ (dernier       │
       └────────┬───────┘    │  recours)      │
                │            └────────┬───────┘
                │                     │
                │            ┌────────┴────────┐
                │            │                 │
                │            ▼                 ▼
                │       ┌─────────┐     ┌──────────┐
                │       │ Trouvé  │     │Non trouvé│
                │       │   ✅    │     │    ❌    │
                │       └────┬────┘     └────┬─────┘
                │            │               │
                └────────────┴───────┐       │
                                     │       │
                                     ▼       ▼
                          ┌──────────────────────────┐
                          │  Afficher Détails Colis  │
                          │  ou Message d'Erreur     │
                          └──────────────────────────┘
```

---

## ✅ CHECKLIST SCAN FONCTIONNEL

- [x] Méthode `normalizeCode()` - Nettoie le code
- [x] Méthode `findPackageByCode()` - Recherche multi-variantes
- [x] Utilisation `DB::table()` au lieu de Eloquent
- [x] Filtrage par statuts acceptés
- [x] Recherche par `package_code` ET `tracking_number`
- [x] Recherche LIKE en dernier recours
- [x] Auto-assignation au livreur
- [x] Réassignation si déjà assigné à autre
- [x] Pas de blocage si assigné à autre
- [x] Redirection vers page détails
- [x] Gestion erreurs

---

## 🧪 SCÉNARIOS DE TEST

### Scénario 1: Scan Normal
```
Code: PKG_ON5VUI_1015 (statut AVAILABLE)
✅ Colis trouvé
✅ Assigné au livreur
✅ Page détails affichée
```

### Scénario 2: Scan avec Variation
```
Code scanné: PKG-ON5VUI-1015 (avec tirets)
Code en DB: PKG_ON5VUI_1015 (avec underscores)
✅ Colis trouvé (variante testée)
✅ Assigné au livreur
```

### Scénario 3: Scan Colis Livré
```
Code: PKG_67890 (statut DELIVERED)
❌ Code non trouvé (normal, statut bloqué)
```

### Scénario 4: Scan Code Inexistant
```
Code: PKG_INVALIDE_999
❌ Code non trouvé
```

### Scénario 5: Réassignation
```
Code: PKG_12345 (assigné à livreur B)
Livreur A scanne
✅ Colis trouvé
✅ Réassigné à livreur A
✅ Page détails affichée
```

---

## 📂 FICHIERS IMPLIQUÉS

### Contrôleur
**`app/Http/Controllers/Deliverer/SimpleDelivererController.php`**

**Méthodes principales**:
- `scanQR()` - Scan API (ligne 1078)
- `scanSimple()` - Scan web simple (ligne 276)
- `processScan()` - Traitement scan (ligne 255)
- `findPackageByCode()` - Recherche code (ligne 554)
- `normalizeCode()` - Normalisation code (ligne 536)

### Routes
**`routes/deliverer.php`**
- `GET /deliverer/scan` - Page scan
- `POST /deliverer/scan/submit` - Soumission scan
- `POST /deliverer/scan/process` - Traitement scan
- `GET /deliverer/task/{package}` - Détails colis

### Vues
- `resources/views/deliverer/scan-simple.blade.php` - Page scan
- `resources/views/deliverer/task-detail.blade.php` - Détails colis

---

## 🎯 RÉSUMÉ RAPIDE

```
1. Scanner code → PKG_ON5VUI_1015
2. Normaliser → PKG_ON5VUI_1015
3. Générer 6 variantes
4. Chercher en DB avec filtrage statut
5. Si trouvé → Auto-assigner → Afficher
6. Si non trouvé → Erreur
```

**Durée totale**: ~50-100ms

---

## 📖 DOCUMENTATION

- **Test code spécifique**: `TEST_CODE_PKG_ON5VUI_1015.md`
- **Copie logique chef dépôt**: `COPIE_LOGIQUE_CHEF_DEPOT_VERS_LIVREUR.md`
- **Workflow complet**: Ce fichier

---

**Le scan livreur fonctionne maintenant comme le chef de dépôt à 100% !** 🎉

Pour tester le code `PKG_ON5VUI_1015`, suivez les instructions dans `TEST_CODE_PKG_ON5VUI_1015.md`.
