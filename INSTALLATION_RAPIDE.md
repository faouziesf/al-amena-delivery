# ⚡ Installation Rapide - Nouveau Compte Livreur

## 🔧 ÉTAPE 1: Activer les Nouvelles Routes (30 secondes)

Ouvrez `routes/web.php` et ajoutez à la fin:

```php
// Nouvelles routes modernes livreur
require __DIR__.'/deliverer-modern.php';
```

---

## 🔧 ÉTAPE 2: Enregistrer Middleware Ngrok (1 minute)

Ouvrez `app/Http/Kernel.php` et ajoutez dans `$routeMiddleware`:

```php
protected $routeMiddleware = [
    // ... routes existantes
    'ngrok.cors' => \App\Http\Middleware\NgrokCorsMiddleware::class,
];
```

Puis ouvrez `routes/deliverer-modern.php` et modifiez la première ligne:

```php
Route::middleware(['auth', 'verified', 'role:DELIVERER', 'ngrok.cors'])
    ->prefix('deliverer')->name('deliverer.')->group(function () {
```

---

## 🔧 ÉTAPE 3: Ajouter Méthodes API au Controller (2 minutes)

Ouvrez `app/Http/Controllers/Deliverer/SimpleDelivererController.php`

À la fin de la classe (avant le dernier `}`), ajoutez:

```php
/**
 * API: Détail d'une tâche
 */
public function apiTaskDetail($id)
{
    $package = Package::find($id);
    
    if (!$package) {
        return response()->json(['error' => 'Package non trouvé'], 404);
    }

    return response()->json([
        'id' => $package->id,
        'type' => 'livraison',
        'tracking_number' => $package->tracking_number,
        'package_code' => $package->package_code,
        'recipient_name' => $package->recipient_name,
        'recipient_phone' => $package->recipient_phone,
        'recipient_address' => $package->recipient_address,
        'cod_amount' => $package->cod_amount,
        'status' => $package->status,
        'est_echange' => $package->est_echange ?? false,
        'notes' => $package->delivery_notes
    ]);
}

/**
 * API: Rechercher un client
 */
public function searchClient(Request $request)
{
    $phone = $request->input('phone');
    
    if (!$phone || strlen($phone) < 8) {
        return response()->json([]);
    }

    $clients = \App\Models\User::where('role', 'CLIENT')
        ->where(function($query) use ($phone) {
            $query->where('phone', 'LIKE', '%' . $phone . '%')
                  ->orWhere('mobile', 'LIKE', '%' . $phone . '%');
        })
        ->limit(10)
        ->get()
        ->map(function($client) {
            $wallet = \App\Models\UserWallet::where('user_id', $client->id)->first();
            return [
                'id' => $client->id,
                'name' => $client->name,
                'phone' => $client->phone ?? $client->mobile,
                'balance' => $wallet ? $wallet->balance : 0
            ];
        });

    return response()->json($clients);
}

/**
 * API: Recharger compte client
 */
public function rechargeClient(Request $request)
{
    $request->validate([
        'client_id' => 'required|exists:users,id',
        'amount' => 'required|numeric|min:1',
        'signature' => 'required|string'
    ]);

    $user = Auth::user();
    $client = \App\Models\User::find($request->client_id);

    try {
        DB::beginTransaction();

        $wallet = \App\Models\UserWallet::firstOrCreate(
            ['user_id' => $client->id],
            ['balance' => 0, 'available_balance' => 0, 'pending_amount' => 0]
        );

        $wallet->increment('balance', $request->amount);
        $wallet->increment('available_balance', $request->amount);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Recharge effectuée',
            'new_balance' => $wallet->balance
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Erreur: ' . $e->getMessage()
        ], 500);
    }
}
```

---

## 🔧 ÉTAPE 4: Vider le Cache (30 secondes)

```bash
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```

---

## 🔧 ÉTAPE 5: Tester (2 minutes)

### Sur PC (localhost):
```
http://localhost:8000/deliverer/tournee
```

### Sur iPhone (ngrok):
```bash
ngrok http 8000
```

Puis ouvrir l'URL ngrok fournie + `/deliverer/tournee`

---

## ✅ VÉRIFICATIONS RAPIDES

1. **Ma Tournée charge ?** ✅
2. **Stats affichées ?** ✅
3. **Détail tâche fonctionne ?** ✅
4. **Scanner fonctionne ?** ✅
5. **Wallet affiche vraies données ?** ✅
6. **Pas d'erreur connexion serveur ?** ✅
7. **Safe areas iPhone OK ?** ✅

---

## 🐛 SI PROBLÈME

### Erreur "Route not found"
→ Vérifier `routes/web.php` contient `require __DIR__.'/deliverer-modern.php';`
→ `php artisan route:clear`

### Erreur "Method not found"
→ Vérifier méthodes API ajoutées au controller
→ `php artisan config:clear`

### Erreur connexion sur ngrok
→ Vérifier middleware ngrok enregistré
→ Vérifier routes utilisent middleware 'ngrok.cors'

### Page blanche
→ `php artisan view:clear`
→ Vérifier fichiers .blade.php dans `resources/views/deliverer/`

---

## 📞 SUPPORT

Tous les fichiers créés:
- ✅ Layout: `layouts/deliverer-modern.blade.php`
- ✅ Tournée: `deliverer/tournee.blade.php`
- ✅ Détail: `deliverer/task-detail-modern.blade.php`
- ✅ Pickups: `deliverer/pickups-available.blade.php`
- ✅ Wallet: `deliverer/wallet-modern.blade.php`
- ✅ Recharge: `deliverer/recharge-client.blade.php`
- ✅ Signature: `deliverer/signature-modern.blade.php`
- ✅ Menu: `deliverer/menu.blade.php`
- ✅ Routes: `routes/deliverer-modern.php`
- ✅ Middleware: `app/Http/Middleware/NgrokCorsMiddleware.php`

---

**Temps total installation**: 5 minutes  
**Difficulté**: Facile  
**Résultat**: Application moderne ultra-rapide  

**C'EST PRÊT ! 🚀**
