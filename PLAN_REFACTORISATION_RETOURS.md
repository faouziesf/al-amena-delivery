# 🔄 PLAN DE REFACTORISATION - SYSTÈME DE RETOURS

**Date de début** : 10/10/2025
**Objectif** : Refonte complète du système de retours avec nouveau workflow automatisé

---

## 📋 PHASE 1 : ANALYSE & NETTOYAGE

### ✅ Tâches Complétées
- [x] Documentation complète du nouveau système
- [x] Identification des fichiers à supprimer/modifier

### 🔍 Fichiers Identifiés pour Suppression/Modification

#### Routes à Supprimer
```
routes/depot-manager.php:
- POST /depot-manager/packages/{package}/process-return
- POST /depot-manager/packages/process-all-returns
- POST /depot-manager/packages/process-return-dashboard
- GET /depot-manager/packages/returns-exchanges
- POST /depot-manager/packages/print-batch-returns
```

#### Vues à Archiver/Supprimer
```
resources/views/depot-manager/packages/:
- returns-exchanges.blade.php
- supplier-returns.blade.php (sections retours)
```

#### Contrôleurs à Modifier
```
app/Http/Controllers/DepotManager/DepotManagerPackageController.php:
- Méthodes processReturn()
- Méthodes processAllReturns()
- Méthodes processReturnDashboard()
- Méthodes returnsExchanges()
```

---

## 📋 PHASE 2 : NOUVEAUX STATUTS

### 🎯 Statuts à Ajouter
```php
// Nouveaux statuts
'AWAITING_RETURN'           // À retourner au fournisseur (48h)
'RETURN_IN_PROGRESS'        // Retour en cours au fournisseur
'RETURNED_TO_CLIENT'        // Retourné (colis retour livré)
'RETURN_CONFIRMED'          // Retour confirmé par client
'RETURN_ISSUE'              // Problème avec le retour
```

### 🗑️ Statuts à Supprimer
```php
'ACCEPTED'    // Remplacé par assignation directe
'CANCELLED'   // Géré différemment maintenant
```

### 🔄 Migration de Données
```sql
-- Convertir les anciens statuts
UPDATE packages SET status = 'PICKED_UP' WHERE status = 'ACCEPTED';
UPDATE packages SET status = 'RETURNED_TO_CLIENT' WHERE status = 'CANCELLED' AND has_return = true;
```

---

## 📋 PHASE 3 : BASE DE DONNÉES

### 🆕 Nouvelle Table : `return_packages`
```php
Schema::create('return_packages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('original_package_id')->constrained('packages');
    $table->string('return_package_code')->unique();
    $table->decimal('cod', 10, 2)->default(0);
    $table->string('status')->default('AT_DEPOT');

    // Sender = Votre société
    $table->json('sender_info');

    // Recipient = Fournisseur original
    $table->json('recipient_info');

    $table->text('return_reason')->nullable();
    $table->text('comment')->nullable();

    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->timestamp('printed_at')->nullable();
    $table->timestamp('delivered_at')->nullable();

    $table->timestamps();
    $table->softDeletes();
});
```

### 🔧 Modifications Table `packages`
```php
Schema::table('packages', function (Blueprint $table) {
    // Gestion des retours
    $table->integer('unavailable_attempts')->default(0)->after('status');
    $table->timestamp('awaiting_return_since')->nullable();
    $table->timestamp('return_in_progress_since')->nullable();
    $table->timestamp('returned_to_client_at')->nullable();
    $table->string('return_reason')->nullable();
    $table->foreignId('return_package_id')->nullable()->constrained('return_packages');

    // Supprimer si existe
    $table->dropColumn(['exchange_status', 'return_status', 'dispose_action']);
});
```

---

## 📋 PHASE 4 : LOGIQUE AUTOMATIQUE

### ⏰ Job Automatisé : `ProcessAwaitingReturnsJob`
**Fréquence** : Toutes les heures
**Fonction** : Passer les colis de `AWAITING_RETURN` → `RETURN_IN_PROGRESS` après 48h

```php
// app/Jobs/ProcessAwaitingReturnsJob.php
public function handle()
{
    $packages = Package::where('status', 'AWAITING_RETURN')
        ->where('awaiting_return_since', '<=', now()->subHours(48))
        ->get();

    foreach ($packages as $package) {
        $package->update([
            'status' => 'RETURN_IN_PROGRESS',
            'return_in_progress_since' => now()
        ]);

        // Notification commercial
        event(new PackageStatusChanged($package, 'AWAITING_RETURN', 'RETURN_IN_PROGRESS'));
    }
}
```

### ⏰ Job Automatisé : `ProcessReturnedPackagesJob`
**Fréquence** : Toutes les heures
**Fonction** : Auto-confirmer les retours après 48h sans action client

```php
// app/Jobs/ProcessReturnedPackagesJob.php
public function handle()
{
    $packages = Package::where('status', 'RETURNED_TO_CLIENT')
        ->where('returned_to_client_at', '<=', now()->subHours(48))
        ->get();

    foreach ($packages as $package) {
        $package->update([
            'status' => 'RETURN_CONFIRMED'
        ]);

        event(new ReturnAutoConfirmed($package));
    }
}
```

### 🔧 Scheduler Configuration
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->job(new ProcessAwaitingReturnsJob)->hourly();
    $schedule->job(new ProcessReturnedPackagesJob)->hourly();
}
```

---

## 📋 PHASE 5 : INTERFACE COMMERCIAL

### 🎯 Fonctionnalité 1 : 4ème Tentative
**Fichier** : `resources/views/commercial/packages/show.blade.php`

```blade
@if($package->status === 'AWAITING_RETURN' && $package->unavailable_attempts >= 3)
<div class="bg-orange-50 border-2 border-orange-200 rounded-xl p-6">
    <h3 class="font-bold text-orange-900 mb-2">⚠️ Colis en Attente de Retour</h3>
    <p class="text-sm text-orange-700 mb-4">
        Ce colis a atteint 3 tentatives infructueuses.
        Délai restant avant retour :
        <strong>{{ $package->awaiting_return_since->addHours(48)->diffForHumans() }}</strong>
    </p>

    <form method="POST" action="{{ route('commercial.packages.launch-fourth-attempt', $package) }}">
        @csrf
        <button type="submit" class="btn-primary">
            🔄 Lancer une 4ème Tentative
        </button>
    </form>
</div>
@endif
```

**Route**
```php
Route::post('packages/{package}/launch-fourth-attempt',
    [CommercialPackageController::class, 'launchFourthAttempt'])
    ->name('commercial.packages.launch-fourth-attempt');
```

**Contrôleur**
```php
public function launchFourthAttempt(Package $package)
{
    if ($package->status !== 'AWAITING_RETURN') {
        return back()->with('error', 'Statut invalide');
    }

    $package->update([
        'status' => 'AT_DEPOT',
        'unavailable_attempts' => 2,
        'awaiting_return_since' => null
    ]);

    ActivityLog::create([
        'user_id' => auth()->id(),
        'action' => 'FOURTH_ATTEMPT_LAUNCHED',
        'package_id' => $package->id,
        'comment' => 'Lancement 4ème tentative par commercial'
    ]);

    return back()->with('success', '4ème tentative lancée avec succès');
}
```

---

### 🎯 Fonctionnalité 2 : Changement Statut Manuel
**Fichier** : `resources/views/commercial/packages/show.blade.php`

```blade
<div class="bg-gray-50 border-2 border-gray-200 rounded-xl p-6">
    <h3 class="font-bold text-gray-900 mb-4">🛠️ Administration - Changement de Statut</h3>

    <form method="POST"
          action="{{ route('commercial.packages.change-status', $package) }}"
          x-data="{ selectedStatus: '{{ $package->status }}' }">
        @csrf
        @method('PATCH')

        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Nouveau Statut</label>
            <select name="new_status"
                    x-model="selectedStatus"
                    class="form-select w-full">
                <option value="CREATED">🆕 Créé</option>
                <option value="AVAILABLE">📋 Disponible</option>
                <option value="PICKED_UP">🚚 Collecté</option>
                <option value="AT_DEPOT">🏭 Au Dépôt</option>
                <option value="IN_TRANSIT">🚛 En Transit</option>
                <option value="OUT_FOR_DELIVERY">📍 En Livraison</option>
                <option value="DELIVERED">📦 Livré</option>
                <option value="PAID">💰 Payé</option>
                <option value="REFUSED">❌ Refusé</option>
                <option value="UNAVAILABLE">⏳ Indisponible</option>
                <option value="AWAITING_RETURN">↩️ À Retourner</option>
                <option value="RETURN_IN_PROGRESS">🔄 Retour en Cours</option>
                <option value="RETURNED_TO_CLIENT">✅ Retourné</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Raison du Changement</label>
            <textarea name="change_reason"
                      class="form-textarea w-full"
                      rows="3"
                      required></textarea>
        </div>

        <button type="submit"
                class="btn-primary"
                onclick="return confirm('Êtes-vous sûr de vouloir changer le statut ?')">
            ✅ Mettre à Jour le Statut
        </button>
    </form>
</div>
```

---

## 📋 PHASE 6 : INTERFACE CHEF DÉPÔT - SCAN RETOURS

### 🆕 Nouveau Contrôleur : `DepotReturnScanController`

**Routes**
```php
// routes/depot-returns.php (nouveau fichier)
Route::middleware(['auth', 'role:DEPOT_MANAGER'])->group(function () {
    Route::get('/depot/returns/scan',
        [DepotReturnScanController::class, 'dashboard'])
        ->name('depot.returns.scan.dashboard');

    Route::get('/depot/returns/scan/{sessionId}',
        [DepotReturnScanController::class, 'scanner'])
        ->name('depot.returns.scan.phone');

    Route::post('/depot/returns/scan/{sessionId}/add',
        [DepotReturnScanController::class, 'addReturnPackage'])
        ->name('depot.returns.scan.add');

    Route::post('/depot/returns/scan/{sessionId}/validate-all',
        [DepotReturnScanController::class, 'validateReturns'])
        ->name('depot.returns.scan.validate.all');

    // Gestion des colis retours créés
    Route::get('/depot/returns/manage',
        [DepotReturnScanController::class, 'manageReturns'])
        ->name('depot.returns.manage');

    Route::post('/depot/returns/{returnPackage}/print',
        [DepotReturnScanController::class, 'printReturnSlip'])
        ->name('depot.returns.print');
});
```

### 📱 Interface Scan (Basée sur Scan Dépôt)

**Différence clé** : Filtre uniquement `RETURN_IN_PROGRESS`

```php
public function scanner($sessionId)
{
    $session = Cache::get("depot_return_session_{$sessionId}");

    if (!$session || $session['status'] === 'completed') {
        return view('depot.session-expired');
    }

    // FILTRE : Seulement les colis en retour
    $packages = DB::table('packages')
        ->where('status', 'RETURN_IN_PROGRESS')
        ->whereNull('return_package_id') // Pas encore traité
        ->select('id', 'package_code as c', 'status as s', 'return_reason as r')
        ->get();

    return view('depot.returns.phone-scanner', compact('sessionId', 'packages'));
}
```

---

## 📋 PHASE 7 : CRÉATION COLIS RETOUR

### 🔧 Logique dans `validateReturns()`

```php
public function validateReturns($sessionId)
{
    $session = Cache::get("depot_return_session_{$sessionId}");
    $scannedPackages = $session['scanned_packages'] ?? [];

    $returnPackages = [];

    DB::transaction(function () use ($scannedPackages, &$returnPackages) {
        foreach ($scannedPackages as $pkg) {
            $originalPackage = Package::find($pkg['id']);

            // Créer le colis retour
            $returnPackage = ReturnPackage::create([
                'original_package_id' => $originalPackage->id,
                'return_package_code' => $this->generateReturnCode(),
                'cod' => 0,
                'status' => 'AT_DEPOT',
                'sender_info' => $this->getCompanyInfo(),
                'recipient_info' => $originalPackage->sender_info,
                'return_reason' => $originalPackage->return_reason,
                'comment' => $originalPackage->return_reason,
                'created_by' => auth()->id()
            ]);

            // Lier au package original
            $originalPackage->update([
                'return_package_id' => $returnPackage->id
            ]);

            $returnPackages[] = $returnPackage;
        }
    });

    // Rediriger vers gestion retours
    return redirect()->route('depot.returns.manage')
        ->with('success', count($returnPackages) . ' colis retours créés')
        ->with('new_returns', $returnPackages);
}
```

---

## 📋 PHASE 8 : INTERFACE CLIENT - RETOURS À TRAITER

### 🆕 Section Dashboard Client

```blade
{{-- resources/views/client/dashboard.blade.php --}}

@if($returnedPackages->count() > 0)
<div class="bg-orange-50 border-2 border-orange-300 rounded-xl p-6 mb-6">
    <h2 class="text-xl font-bold text-orange-900 mb-4">
        ↩️ Retours à Traiter ({{ $returnedPackages->count() }})
    </h2>

    <div class="space-y-4">
        @foreach($returnedPackages as $package)
        <div class="bg-white rounded-lg p-4 border-2 border-orange-200">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h3 class="font-bold">{{ $package->package_code }}</h3>
                    <p class="text-sm text-gray-600">
                        Retourné le {{ $package->returned_to_client_at->format('d/m/Y') }}
                    </p>
                    <p class="text-xs text-orange-600">
                        ⏰ Action requise avant
                        {{ $package->returned_to_client_at->addHours(48)->format('d/m/Y H:i') }}
                    </p>
                </div>
                <span class="text-2xl">{{ number_format($package->cod_amount, 3) }} DT</span>
            </div>

            <div class="flex gap-2">
                <form method="POST"
                      action="{{ route('client.returns.confirm', $package) }}"
                      class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full btn-success"
                            onclick="return confirm('Confirmer la réception du retour ?')">
                        ✅ Valider la Réception
                    </button>
                </form>

                <button onclick="openComplaintModal({{ $package->id }})"
                        class="flex-1 btn-warning">
                    ⚠️ Réclamer un Problème
                </button>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
```

---

## 📋 PHASE 9 : CHECKLIST DÉVELOPPEMENT

### 🗃️ Migrations
- [ ] `2025_10_10_001_remove_old_return_statuses.php`
- [ ] `2025_10_10_002_create_return_packages_table.php`
- [ ] `2025_10_10_003_add_return_fields_to_packages.php`
- [ ] `2025_10_10_004_migrate_old_return_data.php`

### 🎛️ Backend
- [ ] Supprimer routes obsolètes (depot-manager.php)
- [ ] Créer `DepotReturnScanController`
- [ ] Créer `CommercialPackageController` méthodes retours
- [ ] Créer `ClientReturnController`
- [ ] Créer Jobs automatiques
- [ ] Créer Events/Listeners
- [ ] Modifier Models (Package, ReturnPackage)

### 🎨 Frontend
- [ ] Archiver anciennes vues retours
- [ ] Créer `depot/returns/scan-dashboard.blade.php`
- [ ] Créer `depot/returns/phone-scanner.blade.php`
- [ ] Créer `depot/returns/manage.blade.php`
- [ ] Modifier `commercial/packages/show.blade.php`
- [ ] Modifier `client/dashboard.blade.php`
- [ ] Créer `client/returns/index.blade.php`

### 🧪 Tests
- [ ] Test automatisation 48h (awaiting → in_progress)
- [ ] Test automatisation 48h (returned → confirmed)
- [ ] Test 4ème tentative
- [ ] Test changement statut manuel
- [ ] Test scan retours
- [ ] Test création colis retour
- [ ] Test impression bon retour
- [ ] Test validation client

---

## 🚀 ORDRE D'EXÉCUTION

### Semaine 1 : Fondations
1. Créer migrations
2. Créer models
3. Supprimer ancien code
4. Créer jobs automatiques

### Semaine 2 : Interfaces
5. Interface Commercial (4ème tentative + changement statut)
6. Interface Chef Dépôt Scan
7. Interface Chef Dépôt Gestion

### Semaine 3 : Finalisation
8. Interface Client Retours
9. Tests complets
10. Déploiement progressif

---

## ⚠️ POINTS D'ATTENTION

### Sécurité
- ✅ Vérifier permissions sur changement statut manuel
- ✅ Audit log sur toutes les actions sensibles
- ✅ Validation stricte des transitions de statuts

### Performance
- ✅ Index sur colonnes de filtrage retours
- ✅ Queue pour jobs automatiques
- ✅ Cache pour statistiques retours

### UX
- ✅ Messages clairs sur délais 48h
- ✅ Confirmations avant actions irréversibles
- ✅ États de chargement sur toutes les actions AJAX

---

**Prochaine Étape** : Commencer Phase 1 - Créer les migrations
