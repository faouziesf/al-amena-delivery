# Réutilisation du Système de Scan pour les Retours

**Date:** 2025-10-11
**Objectif:** Utiliser la même page et méthodes de scan PC/téléphone pour les retours

---

## 🎯 Objectif

Au lieu d'avoir deux systèmes de scan séparés (normal et retours), **réutiliser le système existant** en adaptant simplement:
1. Les **statuts acceptés** (uniquement `RETURN_IN_PROGRESS` pour les retours)
2. L'**action de validation** (créer `ReturnPackage` au lieu de marquer `AT_DEPOT`)

---

## ✅ Modifications Effectuées

### 1. Controller: `DepotReturnScanController.php`

#### A. Méthode `dashboard()` - Réutilise la vue scan-dashboard

**Avant:** Utilisait `depot.returns.scan-dashboard` (vue dédiée)

**Après:** Utilise `depot.scan-dashboard` (même vue que scan normal)

```php
public function dashboard(Request $request)
{
    // ... logique identique au scan normal ...

    // Stocker session avec format identique
    Cache::put("depot_session_{$sessionId}", [
        'created_at' => now(),
        'status' => 'waiting',
        'scanned_packages' => [],
        'depot_manager_name' => $depotManagerName,
        'session_code' => $sessionCode,
        'scan_type' => 'returns', // Indicateur pour différencier
    ], 8 * 60 * 60);

    // MÊME VUE que scan normal + indicateur mode retours
    $isReturnsMode = true;
    return view('depot.scan-dashboard', compact('sessionId', 'depotManagerName', 'sessionCode', 'isReturnsMode'));
}
```

**Changements clés:**
- ✅ Utilise `depot_session_{$sessionId}` (même format que scan normal)
- ✅ Ajoute `scan_type => 'returns'` pour identification
- ✅ Passe `$isReturnsMode = true` à la vue
- ✅ Utilise exactement la même vue

#### B. Méthode `phoneScanner()` - Filtre sur RETURN_IN_PROGRESS

**Avant:** Vue dédiée `depot.returns.phone-scanner`

**Après:** Utilise `depot.phone-scanner` (même vue) avec filtre statuts

```php
public function phoneScanner($sessionId)
{
    // Vérifier session (même logique que scan normal)
    $session = Cache::get("depot_session_{$sessionId}");

    if (!$session) {
        return view('depot.session-expired', [...]);
    }

    // DIFFÉRENCE: Charger UNIQUEMENT les colis RETURN_IN_PROGRESS
    $packages = DB::table('packages')
        ->where('status', 'RETURN_IN_PROGRESS') // ← FILTRE SPÉCIFIQUE RETOURS
        ->select('id', 'package_code as c', 'status as s', 'depot_manager_name as d')
        ->get()
        ->map(function($pkg) use ($session) {
            return [
                'id' => $pkg->id,
                'c' => $pkg->c,
                's' => $pkg->s,
                'd' => $pkg->d,
                'current_depot' => $session['depot_manager_name'] ?? null
            ];
        });

    // MÊME VUE que scan normal
    return view('depot.phone-scanner', compact('sessionId', 'packages', 'depotManagerName'));
}
```

**Comparaison avec scan normal:**

| Aspect | Scan Normal | Scan Retours |
|--------|-------------|--------------|
| Vue | `depot.phone-scanner` | `depot.phone-scanner` ✅ (identique) |
| Statuts refusés | `DELIVERED`, `PAID`, `VERIFIED`, etc. | N/A |
| Statuts acceptés | Tous sauf refusés | **`RETURN_IN_PROGRESS` uniquement** |
| Logique | `whereNotIn('status', [refusés])` | `where('status', 'RETURN_IN_PROGRESS')` |

#### C. Méthode `validateAndCreate()` - Crée ReturnPackage

**Avant:** Méthode personnalisée différente du scan normal

**Après:** Format identique à `validateAllFromPC()` du scan normal

```php
public function validateAndCreate($sessionId)
{
    $session = Cache::get("depot_session_{$sessionId}");

    if (!$session) {
        return redirect()->route('depot.returns.dashboard')->with('error', 'Session introuvable');
    }

    $scannedPackages = $session['scanned_packages'] ?? [];

    if (empty($scannedPackages)) {
        return redirect()->back()->with('error', 'Aucun colis à valider');
    }

    $successCount = 0;
    $errorCount = 0;
    $createdReturnPackages = [];

    DB::beginTransaction();

    try {
        foreach ($scannedPackages as $pkg) {
            $packageCode = $pkg['package_code'] ?? $pkg['code'];
            $originalPackage = Package::where('package_code', $packageCode)->first();

            if (!$originalPackage) {
                $errorCount++;
                continue;
            }

            // DIFFÉRENCE: Créer ReturnPackage au lieu de marquer AT_DEPOT
            $returnPackage = ReturnPackage::create([
                'original_package_id' => $originalPackage->id,
                'return_package_code' => ReturnPackage::generateReturnCode(),
                'cod' => 0,
                'status' => 'AT_DEPOT',
                'sender_info' => ReturnPackage::getCompanyInfo(),
                'recipient_info' => [
                    'name' => $originalPackage->sender->name ?? 'Client',
                    'phone' => $originalPackage->sender->phone ?? '',
                    'address' => $originalPackage->sender->address ?? '',
                    'city' => $originalPackage->sender->city ?? '',
                ],
                'return_reason' => $originalPackage->return_reason,
                'comment' => "Colis retour créé suite au scan dépôt",
                'created_by' => auth()->id(),
            ]);

            // Lier au colis original
            $originalPackage->update([
                'return_package_id' => $returnPackage->id,
            ]);

            $createdReturnPackages[] = [
                'code' => $returnPackage->return_package_code,
                'original_code' => $packageCode,
            ];

            $successCount++;
        }

        // IDENTIQUE: Terminer session comme scan normal
        $session['status'] = 'completed';
        $session['scanned_packages'] = [];
        $session['last_validated_packages'] = $createdReturnPackages;
        $session['validated_at'] = now();
        $session['validated_count'] = $successCount;
        $session['completed_at'] = now();

        Cache::put("depot_session_{$sessionId}", $session, 60);

        DB::commit();

        $message = "✅ {$successCount} colis retours créés avec succès";

        // IDENTIQUE: Retour JSON ou redirect
        if (request()->wantsJson() || request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'validated_count' => $successCount,
                'error_count' => $errorCount,
                'return_codes' => array_column($createdReturnPackages, 'code'),
            ]);
        }

        return redirect()->back()->with('success', $message);

    } catch (\Exception $e) {
        DB::rollBack();
        // ... gestion erreur ...
    }
}
```

**Comparaison avec scan normal:**

| Aspect | Scan Normal | Scan Retours |
|--------|-------------|--------------|
| Format méthode | `validateAllFromPC($sessionId)` | `validateAndCreate($sessionId)` ✅ (identique) |
| Session | `depot_session_{$sessionId}` | `depot_session_{$sessionId}` ✅ (identique) |
| Action | `UPDATE packages SET status='AT_DEPOT'` | `CREATE ReturnPackage` ← Différence |
| Terminer session | Marque `status='completed'` | Marque `status='completed'` ✅ (identique) |
| Retour | JSON ou redirect | JSON ou redirect ✅ (identique) |

---

### 2. Vue: `depot/scan-dashboard.blade.php`

**Modification JavaScript pour supporter les deux modes:**

```javascript
// Valider depuis PC (AJAX - sans formulaire)
async function validateFromPC() {
    if (totalScanned === 0) {
        alert('Aucun colis à valider');
        return;
    }

    // NOUVEAU: Détecter le mode
    const isReturnsMode = {{ isset($isReturnsMode) && $isReturnsMode ? 'true' : 'false' }};

    // NOUVEAU: Message selon le mode
    const confirmMessage = isReturnsMode
        ? `Confirmer la création de ${totalScanned} colis retour(s) ?\n\nDes nouveaux colis retours seront créés pour chaque colis scanné.`
        : `Confirmer la réception de ${totalScanned} colis au dépôt ?\n\nTous les colis seront marqués comme "AT_DEPOT" (au dépôt).`;

    if (!confirm(confirmMessage)) {
        return;
    }

    const btn = document.getElementById('validate-btn');
    btn.disabled = true;
    btn.innerHTML = '⏳ Validation en cours...';

    // NOUVEAU: URL selon le mode
    const validateUrl = isReturnsMode
        ? `/depot/returns/${sessionId}/validate`      // ← Pour retours
        : `/depot/scan/${sessionId}/validate-all`;    // ← Pour scan normal

    try {
        const response = await fetch(validateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({})
        });

        const data = await response.json();

        if (data.success) {
            showValidationSuccessPopup(data.validated_count);
        } else {
            alert('❌ Erreur lors de la validation');
            btn.innerHTML = '✅ Valider Réception au Dépôt';
            btn.disabled = false;
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('❌ Erreur réseau');
        btn.innerHTML = '✅ Valider Réception au Dépôt';
        btn.disabled = false;
    }
}
```

**Changements clés:**
- ✅ Détecte si `$isReturnsMode` est défini
- ✅ Message de confirmation adapté selon le mode
- ✅ URL de validation adaptée selon le mode
- ✅ **Tout le reste est identique** (interface, polling, affichage, etc.)

---

## 📊 Comparaison Finale

### Points Communs (Réutilisés) ✅

| Composant | Scan Normal | Scan Retours |
|-----------|-------------|--------------|
| **Vue PC** | `depot.scan-dashboard` | `depot.scan-dashboard` ✅ |
| **Vue Mobile** | `depot.phone-scanner` | `depot.phone-scanner` ✅ |
| **Session Cache** | `depot_session_{$sessionId}` | `depot_session_{$sessionId}` ✅ |
| **Code Session** | 8 chiffres | 8 chiffres ✅ |
| **Durée Session** | 8 heures | 8 heures ✅ |
| **Polling** | Temps réel | Temps réel ✅ |
| **Interface** | QR code + liste + validation | QR code + liste + validation ✅ |
| **Terminer Session** | `status='completed'` | `status='completed'` ✅ |

### Différences (Adaptées) 🔧

| Aspect | Scan Normal | Scan Retours |
|--------|-------------|--------------|
| **Route Dashboard** | `/depot/scan` | `/depot/returns` |
| **Route Validation** | `/depot/scan/{id}/validate-all` | `/depot/returns/{id}/validate` |
| **Filtre Statuts** | `whereNotIn('status', [refusés])` | `where('status', 'RETURN_IN_PROGRESS')` |
| **Action Validation** | `UPDATE packages SET status='AT_DEPOT'` | `CREATE ReturnPackage` |
| **Variable Vue** | (aucune) | `$isReturnsMode = true` |
| **Message Confirm** | "Réception au dépôt" | "Création colis retours" |

---

## 🎯 Avantages de cette Approche

### 1. **Réutilisation Maximale**
- ✅ Une seule vue PC (`scan-dashboard`)
- ✅ Une seule vue mobile (`phone-scanner`)
- ✅ Même logique de session
- ✅ Même interface utilisateur
- ✅ Maintenance simplifiée

### 2. **Différenciation Minimale**
- 🔧 Seulement 3 lignes changées dans la vue (détection mode + URL + message)
- 🔧 Filtre statuts différent dans `phoneScanner()`
- 🔧 Action de validation différente (CREATE vs UPDATE)

### 3. **Cohérence UX**
- ✅ Même expérience utilisateur
- ✅ Formation une seule fois
- ✅ Pas de confusion entre deux systèmes

### 4. **Évolutivité**
- ✅ Facile d'ajouter d'autres types de scan (expéditions, inventaire, etc.)
- ✅ Même approche: changer filtre + action validation
- ✅ Pas besoin de dupliquer code

---

## 🧪 Tests à Effectuer

### 1. Test Scan Normal (Vérification non-régression)
```bash
# Accéder au scan normal
GET /depot/scan

# Vérifier que:
✅ QR code s'affiche
✅ Code 8 chiffres visible
✅ Mobile peut scanner
✅ Colis s'ajoutent à la liste
✅ Validation marque AT_DEPOT
✅ Session se termine
```

### 2. Test Scan Retours
```bash
# Accéder au scan retours
GET /depot/returns

# Vérifier que:
✅ QR code s'affiche
✅ Code 8 chiffres visible
✅ Mobile peut scanner
✅ Seuls colis RETURN_IN_PROGRESS acceptés
✅ Validation crée ReturnPackage
✅ Session se termine
```

### 3. Test Différenciation
```bash
# Scanner un colis AT_DEPOT en mode retours
→ ❌ Doit être refusé (pas RETURN_IN_PROGRESS)

# Scanner un colis RETURN_IN_PROGRESS en mode normal
→ ✅ Doit être accepté (car dans les statuts acceptés normaux)

# Valider en mode retours
→ ✅ Doit créer ReturnPackage
→ ✅ Ne doit PAS marquer AT_DEPOT
```

---

## 📝 Routes Actives

### Scan Normal
```
GET  /depot/scan                      → Dashboard PC
GET  /depot/scan/phone/{sessionId}    → Scanner mobile
POST /depot/scan/{sessionId}/validate-all → Validation
```

### Scan Retours
```
GET  /depot/returns                   → Dashboard PC (même vue)
GET  /depot/returns/phone/{sessionId} → Scanner mobile (même vue)
POST /depot/returns/{sessionId}/validate → Validation (crée ReturnPackage)
```

---

## 🔍 Fichiers Modifiés

| Fichier | Type | Modification |
|---------|------|--------------|
| `DepotReturnScanController.php` | Controller | dashboard(), phoneScanner(), validateAndCreate() |
| `scan-dashboard.blade.php` | Vue | Ajout détection `$isReturnsMode` + URL conditionnelle |

**Total:** 2 fichiers modifiés

---

## ✅ Résumé

**Mission accomplie:**
- ✅ Réutilisation complète du système de scan existant
- ✅ Seulement 3 différences: filtre statuts, action validation, message
- ✅ Maintenance simplifiée (une seule vue, une seule logique)
- ✅ Expérience utilisateur cohérente
- ✅ Code minimal ajouté

**État actuel:**
- 🟢 Scan normal fonctionne (inchangé)
- 🟢 Scan retours utilise les mêmes vues
- 🟢 Différenciation via `$isReturnsMode` et filtres statuts
- ⏳ Tests à effectuer pour validation finale

---

**Document créé le:** 2025-10-11
**Par:** Claude (Assistant IA)
**Version:** 1.0
**Statut:** ✅ Modifications terminées, tests requis
