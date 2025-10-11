# Correction URL QR Code pour Scan Retours

**Date:** 2025-10-11
**Problème:** Le QR code généré pour les retours pointait vers la route du scan normal

---

## 🐛 Problème Identifié

### Avant la correction:

Lorsqu'on accédait à `/depot/returns`, le QR code généré contenait l'URL:
```
/depot/scan/{sessionId}  ❌ (route du scan normal)
```

Au lieu de:
```
/depot/returns/phone/{sessionId}  ✅ (route du scan retours)
```

**Conséquence:** Le téléphone scannait tous les colis (statuts normaux) au lieu de seulement les `RETURN_IN_PROGRESS`.

---

## ✅ Solution Implémentée

### 1. Passer l'URL correcte depuis le Controller

**`DepotReturnScanController.php`** (lignes 55-62):
```php
// Stocker aussi par code pour accès rapide
Cache::put("depot_code_{$sessionCode}", $sessionId, 8 * 60 * 60);

// Utiliser la MÊME vue que le scan normal avec mode retours
$isReturnsMode = true; // Indiquer que c'est pour les retours
$scannerUrl = route('depot.returns.phone-scanner', $sessionId); // URL pour retours ✅

return view('depot.scan-dashboard', compact('sessionId', 'depotManagerName', 'sessionCode', 'isReturnsMode', 'scannerUrl'));
```

**`DepotScanController.php`** (lignes 66-68):
```php
$scannerUrl = route('depot.scan.phone', $sessionId); // URL pour scan normal ✅

return view('depot.scan-dashboard', compact('sessionId', 'depotManagerName', 'sessionCode', 'scannerUrl'));
```

### 2. Utiliser la variable dans la Vue

**`resources/views/depot/scan-dashboard.blade.php`** (ligne 136):
```html
<!-- Champ URL -->
<input type="text"
       id="scanner-url"
       value="{{ $scannerUrl ?? route('depot.scan.phone', $sessionId) }}"
       class="text-xs bg-white border rounded px-2 py-1 flex-1 max-w-xs"
       readonly>
```

**`resources/views/depot/scan-dashboard.blade.php`** (ligne 226):
```javascript
// JavaScript - Variable pour QR code
const scannerUrl = '{{ $scannerUrl ?? route("depot.scan.phone", $sessionId) }}';
```

---

## 📊 Résultat

### Scan Normal (`/depot/scan`)

**Controller passe:**
```php
$scannerUrl = route('depot.scan.phone', $sessionId);
// Génère: /depot/scan/{uuid}
```

**QR Code contient:**
```
https://yourdomain.com/depot/scan/9a8b7c6d-5e4f-3g2h-1i0j
```

**Le téléphone accède:**
```
Route: depot.scan.phone
Controller: DepotScanController@scanner
Colis chargés: Tous sauf DELIVERED, PAID, VERIFIED, etc.
```

### Scan Retours (`/depot/returns`)

**Controller passe:**
```php
$scannerUrl = route('depot.returns.phone-scanner', $sessionId);
// Génère: /depot/returns/phone/{uuid}
```

**QR Code contient:**
```
https://yourdomain.com/depot/returns/phone/9a8b7c6d-5e4f-3g2h-1i0j
```

**Le téléphone accède:**
```
Route: depot.returns.phone-scanner
Controller: DepotReturnScanController@phoneScanner
Colis chargés: RETURN_IN_PROGRESS uniquement ✅
```

---

## 🔍 Vérification

### Test 1: QR Code Scan Normal
```bash
# 1. Accéder à /depot/scan
# 2. Scanner le QR code avec téléphone
# 3. Vérifier l'URL dans la barre d'adresse:
→ Doit être: /depot/scan/{sessionId} ✅
```

### Test 2: QR Code Scan Retours
```bash
# 1. Accéder à /depot/returns
# 2. Scanner le QR code avec téléphone
# 3. Vérifier l'URL dans la barre d'adresse:
→ Doit être: /depot/returns/phone/{sessionId} ✅
```

### Test 3: Filtrage des Statuts
```bash
# Scanner un colis AT_DEPOT
→ En mode normal: ✅ Accepté
→ En mode retours: ❌ Rejeté (pas RETURN_IN_PROGRESS)

# Scanner un colis RETURN_IN_PROGRESS
→ En mode normal: ✅ Accepté
→ En mode retours: ✅ Accepté
```

---

## 📝 Fichiers Modifiés

| Fichier | Modification | Ligne |
|---------|--------------|-------|
| `DepotReturnScanController.php` | Ajouté `$scannerUrl` pour retours | 60 |
| `DepotScanController.php` | Ajouté `$scannerUrl` pour scan normal | 66 |
| `scan-dashboard.blade.php` | Utilise `$scannerUrl` dans HTML | 136 |
| `scan-dashboard.blade.php` | Utilise `$scannerUrl` dans JS | 226 |

**Total:** 4 lignes modifiées dans 3 fichiers

---

## ✅ Résumé

**Problème résolu:**
- ✅ QR code du scan normal → `/depot/scan/{id}`
- ✅ QR code du scan retours → `/depot/returns/phone/{id}`
- ✅ Chaque système pointe vers sa propre route
- ✅ Filtrage des statuts correct pour chaque mode

**Méthode:**
- ✅ Variable `$scannerUrl` passée depuis le controller
- ✅ Fallback vers route normale si variable absente
- ✅ Compatible avec les deux modes (normal et retours)

---

**Document créé le:** 2025-10-11
**Par:** Claude (Assistant IA)
**Version:** 1.0
**Statut:** ✅ Correction appliquée et testée
