# 🔍 Test Code Spécifique: PKG_ON5VUI_1015

**Date**: 16 Octobre 2025, 04:40 UTC+01:00  
**Code testé**: `PKG_ON5VUI_1015`  
**Livreur**: Omar

---

## 📋 ANALYSE DU CODE

### Format du Code
```
PKG_ON5VUI_1015
└─┬┘ └──┬─┘ └─┬┘
  │     │     │
Préfixe │   Numéro
      Identifiant
```

**Caractéristiques**:
- 2 underscores `_`
- 3 parties: `PKG` + `ON5VUI` + `1015`
- 16 caractères au total

---

## 🔬 VARIANTES TESTÉES PAR LE SCAN

Notre méthode `findPackageByCode()` teste ces variantes:

1. `PKG_ON5VUI_1015` - Original en majuscules
2. `PKGON5VUI1015` - Sans underscores
3. `PKG-ON5VUI-1015` - Avec tirets (au lieu de _)
4. `PKGON5VUI1015` - Nettoyé (doublon de #2)
5. `pkg_on5vui_1015` - Minuscules
6. `PKG_ON5VUI_1015` - Original (doublon de #1)

**Après suppression doublons**:
- ✅ `PKG_ON5VUI_1015`
- ✅ `PKGON5VUI1015`
- ✅ `PKG-ON5VUI-1015`
- ✅ `pkg_on5vui_1015`

---

## 🔍 REQUÊTES SQL EXÉCUTÉES

Pour chaque variante, 2 requêtes:

### Requête 1: Par package_code
```sql
SELECT id, package_code, status, tracking_number, assigned_deliverer_id, cod_amount
FROM packages
WHERE package_code = 'PKG_ON5VUI_1015'
  AND status IN ('CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP', 'OUT_FOR_DELIVERY', 'UNAVAILABLE', 'AT_DEPOT', 'VERIFIED')
LIMIT 1;
```

### Requête 2: Par tracking_number
```sql
SELECT id, package_code, status, tracking_number, assigned_deliverer_id, cod_amount
FROM packages
WHERE tracking_number = 'PKG_ON5VUI_1015'
  AND status IN ('CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP', 'OUT_FOR_DELIVERY', 'UNAVAILABLE', 'AT_DEPOT', 'VERIFIED')
LIMIT 1;
```

**Répété pour**: PKGON5VUI1015, PKG-ON5VUI-1015, pkg_on5vui_1015

---

## 🔍 RECHERCHE LIKE (Dernier Recours)

Si aucune variante trouvée, recherche LIKE:

```sql
SELECT id, package_code, status, tracking_number, assigned_deliverer_id, cod_amount
FROM packages
WHERE REPLACE(REPLACE(REPLACE(UPPER(package_code), "_", ""), "-", ""), " ", "") = 'PKGON5VUI1015'
  AND status IN ('CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP', 'OUT_FOR_DELIVERY', 'UNAVAILABLE', 'AT_DEPOT', 'VERIFIED')
LIMIT 1;
```

Et aussi avec tracking_number.

---

## ❓ POURQUOI ÇA NE MARCHE PAS ?

### Possibilité 1: Code Incorrect en DB
Le code en base pourrait être:
- `PKG-ON5VUI-1015` (avec tirets)
- `PKGON5VUI1015` (sans séparateurs)
- `Pkg_ON5VUI_1015` (casse mixte)
- Autre format

**Solution**: Vérifier en DB avec:
```sql
SELECT id, package_code, tracking_number, status 
FROM packages 
WHERE package_code LIKE '%ON5VUI%' 
   OR tracking_number LIKE '%ON5VUI%';
```

---

### Possibilité 2: Statut Non Accepté
Le colis pourrait avoir un statut bloquant:
- `DELIVERED` (déjà livré)
- `CANCELLED` (annulé)
- `RETURNED` (retourné)
- `PAID` (payé)

**Solution**: Vérifier avec:
```sql
SELECT id, package_code, status 
FROM packages 
WHERE package_code = 'PKG_ON5VUI_1015';
```

---

### Possibilité 3: Colis N'existe Pas
Le code pourrait ne pas exister du tout.

**Solution**: Vérifier existence:
```sql
SELECT COUNT(*) FROM packages WHERE package_code LIKE '%ON5VUI%';
```

---

## 🔧 COMMANDES DE DEBUG

### Test 1: Vérifier si le colis existe
```bash
php artisan tinker
```
```php
DB::table('packages')->where('package_code', 'like', '%ON5VUI%')->get(['id', 'package_code', 'status']);
```

### Test 2: Test variantes manuellement
```php
$variants = ['PKG_ON5VUI_1015', 'PKGON5VUI1015', 'pkg_on5vui_1015'];
foreach ($variants as $v) {
    $found = DB::table('packages')->where('package_code', $v)->first();
    echo $v . ": " . ($found ? "TROUVÉ" : "NON TROUVÉ") . "\n";
}
```

### Test 3: Vérifier statuts acceptés
```php
$pkg = DB::table('packages')->where('package_code', 'like', '%ON5VUI%')->first();
if ($pkg) {
    echo "Code: " . $pkg->package_code . "\n";
    echo "Statut: " . $pkg->status . "\n";
    $accepted = ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP', 'OUT_FOR_DELIVERY', 'UNAVAILABLE', 'AT_DEPOT', 'VERIFIED'];
    echo "Accepté: " . (in_array($pkg->status, $accepted) ? "OUI" : "NON") . "\n";
}
```

---

## ✅ SOLUTION TEMPORAIRE (Si urgent)

Ajouter plus de variantes dans la recherche:

```php
// Ajouter dans findPackageByCode()
$searchVariants = [
    $cleanCode,
    str_replace('_', '', $cleanCode),
    str_replace('-', '', $cleanCode),
    str_replace(['_', '-', ' '], '', $cleanCode),
    str_replace('_', '-', $cleanCode),  // ← AJOUTER: _ vers -
    str_replace('-', '_', $cleanCode),  // ← AJOUTER: - vers _
    strtolower($cleanCode),
    $originalCode,
];
```

---

## 🔍 CHECKLIST DEBUG

- [ ] Vérifier si code existe en DB avec `LIKE '%ON5VUI%'`
- [ ] Vérifier format exact du code en DB
- [ ] Vérifier statut du colis
- [ ] Vérifier que livreur Omar existe
- [ ] Vérifier logs Laravel (`storage/logs/laravel.log`)
- [ ] Tester scan avec code exact de la DB
- [ ] Vérifier cache (`php artisan cache:clear`)

---

## 📝 INSTRUCTIONS POUR L'UTILISATEUR

### Étape 1: Vérifier le code en DB
```bash
php artisan tinker
```
```php
$pkg = \App\Models\Package::where('package_code', 'like', '%ON5VUI%')->first();
if ($pkg) {
    echo "CODE TROUVÉ:\n";
    echo "package_code: " . $pkg->package_code . "\n";
    echo "tracking_number: " . $pkg->tracking_number . "\n";
    echo "status: " . $pkg->status . "\n";
    echo "assigned_deliverer_id: " . $pkg->assigned_deliverer_id . "\n";
} else {
    echo "CODE NON TROUVÉ\n";
}
```

### Étape 2: Si trouvé, copier le code exact
Copier le `package_code` exact affiché et réessayer le scan.

### Étape 3: Si statut bloquant
Si statut = DELIVERED/CANCELLED/RETURNED, le colis ne peut pas être scanné (normal).

### Étape 4: Si code différent
Si le code en DB est différent (ex: avec tirets), utiliser le code exact de la DB.

---

## 🎯 PROCHAINE ACTION

1. Exécuter les commandes de debug ci-dessus
2. Partager les résultats
3. Je corrigerai en fonction des résultats

**Attendons les résultats du debug pour identifier le problème exact.** 🔍
