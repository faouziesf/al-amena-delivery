<?php

namespace App\Http\Controllers\Deliverer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Package;
use App\Models\Delegation;
use App\Models\User;
use App\Models\FinancialTransaction;
use App\Models\PackageStatusHistory;
use App\Models\ActionLog;
use Carbon\Carbon;

class DelivererPackageController
{
    /**
     * LISTE 1: Pickups disponibles
     */
    public function availablePickups(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            abort(403, 'Accès réservé aux livreurs.');
        }

        $query = Package::where('status', 'AVAILABLE')
                        ->with(['sender', 'delegationFrom', 'delegationTo'])
                        ->orderBy('created_at', 'asc');

        // Filtres
        if ($request->filled('delegation')) {
            $query->where(function($q) use ($request) {
                $q->where('delegation_from', $request->delegation)
                  ->orWhere('delegation_to', $request->delegation);
            });
        }

        if ($request->filled('cod_min')) {
            $query->where('cod_amount', '>=', $request->cod_min);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('package_code', 'LIKE', "%{$search}%")
                  ->orWhereJsonContains('recipient_data->name', $search)
                  ->orWhereJsonContains('recipient_data->phone', $search)
                  ->orWhereJsonContains('sender_data->name', $search);
            });
        }

        $packages = $query->paginate(20);
        $delegations = Delegation::where('active', true)->orderBy('name')->get();

        // Réponse AJAX si demandée
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'packages' => $packages->items(),
                'pagination' => [
                    'current_page' => $packages->currentPage(),
                    'last_page' => $packages->lastPage(),
                    'total' => $packages->total()
                ]
            ]);
        }

        return view('deliverer.packages.available', compact('packages', 'delegations'));
    }

    /**
     * LISTE 2: Mes pickups acceptés
     */
    public function myPickups(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            abort(403, 'Accès réservé aux livreurs.');
        }

        $packages = Package::where('assigned_deliverer_id', Auth::id())
                          ->where('status', 'ACCEPTED')
                          ->with(['sender', 'delegationFrom', 'delegationTo'])
                          ->orderBy('assigned_at', 'asc')
                          ->paginate(20);

        // Réponse AJAX si demandée
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'packages' => $packages->items(),
                'pagination' => [
                    'current_page' => $packages->currentPage(),
                    'last_page' => $packages->lastPage(),
                    'total' => $packages->total()
                ]
            ]);
        }

        return view('deliverer.packages.my-pickups', compact('packages'));
    }

    /**
     * LISTE 3: Livraisons à effectuer
     */
    public function deliveries(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            abort(403, 'Accès réservé aux livreurs.');
        }

        $query = Package::where('assigned_deliverer_id', Auth::id())
                        ->whereIn('status', ['PICKED_UP', 'UNAVAILABLE'])
                        ->with(['sender', 'delegationFrom', 'delegationTo']);

        // Tri par priorité : 4ème tentatives en premier
        $query->orderByRaw("CASE WHEN delivery_attempts >= 3 THEN 0 ELSE 1 END")
              ->orderBy('updated_at', 'asc');

        $packages = $query->paginate(20);

        return view('deliverer.packages.deliveries', compact('packages'));
    }

    /**
     * LISTE 4: Retours à effectuer
     */
    public function returns(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            abort(403, 'Accès réservé aux livreurs.');
        }

        $packages = Package::where('assigned_deliverer_id', Auth::id())
                          ->where('status', 'VERIFIED')
                          ->with(['sender', 'delegationFrom', 'delegationTo'])
                          ->orderBy('updated_at', 'asc')
                          ->paginate(20);

        return view('deliverer.packages.returns', compact('packages'));
    }

    /**
     * Accepter un pickup (premier arrivé = premier servi)
     */
    public function acceptPickup(Package $package)
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            return response()->json(['success' => false, 'message' => 'Accès refusé.']);
        }

        try {
            return DB::transaction(function () use ($package) {
                // Vérification avec verrouillage pessimiste
                $package = Package::where('id', $package->id)
                                 ->where('status', 'AVAILABLE')
                                 ->lockForUpdate()
                                 ->first();

                if (!$package) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Pickup déjà accepté par un autre livreur.'
                    ]);
                }

                // Accepter le pickup
                $package->update([
                    'assigned_deliverer_id' => Auth::id(),
                    'assigned_at' => now(),
                    'status' => 'ACCEPTED'
                ]);

                // Log de l'action
                $this->logAction('PICKUP_ACCEPTED', 'Package', $package->id, 
                               'AVAILABLE', 'ACCEPTED', [
                    'package_code' => $package->package_code,
                    'cod_amount' => $package->cod_amount
                ]);

                // Historique statut
                $this->updatePackageStatus($package, 'ACCEPTED', 'Pickup accepté par le livreur');

                return response()->json([
                    'success' => true, 
                    'message' => "Pickup {$package->package_code} accepté avec succès!",
                    'redirect' => route('deliverer.packages.show', $package)
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Erreur acceptation pickup', [
                'package_id' => $package->id,
                'deliverer_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false, 
                'message' => 'Erreur lors de l\'acceptation. Veuillez réessayer.'
            ]);
        }
    }

    /**
     * Scanner QR/Code et traiter selon le contexte - VERSION AMÉLIORÉE
     */
    public function scanPackage(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100'
        ]);

        $code = trim($validated['code']);
        
        try {
            // Rechercher le package par code avec différents formats
            $package = $this->findPackageByCode($code);
            
            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => "Aucun colis trouvé avec le code: {$code}",
                    'code_scanned' => $code,
                    'suggestions' => $this->getCodeSuggestions($code)
                ], 404);
            }

            // Log du scan
            $this->logAction('PACKAGE_SCANNED', 'Package', $package->id, 
                           null, $code, [
                'deliverer_id' => Auth::id(),
                'package_status' => $package->status,
                'scan_context' => $this->determineScanContext($package),
                'user_agent' => $request->userAgent()
            ]);

            // Déterminer l'action contextuelle selon le statut et l'assignation
            $result = $this->determineScanAction($package);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error('Erreur scan package', [
                'code' => $code,
                'deliverer_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement du code scanné.',
                'code_scanned' => $code
            ], 500);
        }
    }

    /**
     * Rechercher un package par différents formats de codes
     */
    private function findPackageByCode($code)
    {
        $code = strtoupper(trim($code));
        
        // 1. Recherche exacte
        $package = Package::where('package_code', $code)->first();
        if ($package) return $package;
        
        // 2. Recherche avec préfixe PKG_
        if (!str_starts_with($code, 'PKG_')) {
            $package = Package::where('package_code', 'PKG_' . $code)->first();
            if ($package) return $package;
        }
        
        // 3. Recherche sans préfixe
        if (str_starts_with($code, 'PKG_')) {
            $codeWithoutPrefix = substr($code, 4);
            $package = Package::where('package_code', 'LIKE', '%' . $codeWithoutPrefix . '%')->first();
            if ($package) return $package;
        }
        
        // 4. Recherche partielle (last resort)
        $package = Package::where('package_code', 'LIKE', '%' . $code . '%')
                          ->orderBy('created_at', 'desc')
                          ->first();
        
        return $package;
    }

    /**
     * Suggestions de codes similaires
     */
    private function getCodeSuggestions($code)
    {
        $suggestions = Package::where('package_code', 'LIKE', '%' . substr($code, -6) . '%')
                              ->limit(3)
                              ->pluck('package_code')
                              ->toArray();
        
        return $suggestions;
    }

    /**
     * Marquer comme collecté (Picked Up) - VERSION AMÉLIORÉE
     */
    public function markPickedUp(Package $package, Request $request)
    {
        if (!$this->canPerformAction($package, 'pickup')) {
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée pour ce colis.'
            ], 403);
        }

        $validated = $request->validate([
            'pickup_notes' => 'nullable|string|max:500',
            'pickup_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120'
        ]);

        try {
            return DB::transaction(function () use ($package, $validated, $request) {
                $updateData = [
                    'status' => 'PICKED_UP',
                    'pickup_notes' => $validated['pickup_notes'] ?? null,
                    'picked_up_at' => now()
                ];

                // Photo de pickup si fournie
                if ($request->hasFile('pickup_photo')) {
                    $path = $request->file('pickup_photo')->store('pickups/' . $package->id, 'public');
                    $updateData['pickup_photo'] = $path;
                }

                $package->update($updateData);

                $this->updatePackageStatus($package, 'PICKED_UP', 'Colis collecté chez l\'expéditeur' . 
                    ($validated['pickup_notes'] ? ' - Notes: ' . $validated['pickup_notes'] : ''));

                // Log avec détails
                $this->logAction('PACKAGE_PICKED_UP', 'Package', $package->id,
                               'ACCEPTED', 'PICKED_UP', [
                    'pickup_notes' => $validated['pickup_notes'] ?? null,
                    'has_photo' => $request->hasFile('pickup_photo'),
                    'package_code' => $package->package_code
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Colis {$package->package_code} collecté avec succès!",
                    'redirect' => route('deliverer.packages.show', $package)
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Erreur pickup', [
                'package_id' => $package->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la collecte.'
            ], 500);
        }
    }

    /**
     * Livrer le colis avec COD sécurisé - VERSION AMÉLIORÉE
     */
    public function deliverPackage(Package $package, Request $request)
    {
        if (!$this->canPerformAction($package, 'deliver')) {
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée pour ce colis.'
            ], 403);
        }

        $validated = $request->validate([
            'cod_collected' => 'required|numeric|min:0',
            'recipient_name' => 'required|string|max:100',
            'delivery_notes' => 'nullable|string|max:500',
            'recipient_signature' => 'nullable|string',
            'delivery_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120'
        ]);

        // Vérification COD exact avec tolérance de 0.001 DT
        $expectedCod = (float) $package->cod_amount;
        $collectedCod = (float) $validated['cod_collected'];
        
        if (abs($collectedCod - $expectedCod) > 0.001) {
            return response()->json([
                'success' => false,
                'message' => "COD incorrect! Attendu: {$expectedCod} DT, Saisi: {$collectedCod} DT. Vérifiez le montant ou contactez le commercial.",
                'expected_cod' => $expectedCod,
                'collected_cod' => $collectedCod
            ], 400);
        }

        try {
            return DB::transaction(function () use ($package, $validated, $request) {
                // 1. Marquer colis comme livré
                $updateData = [
                    'status' => 'DELIVERED',
                    'delivered_at' => now(),
                    'delivery_notes' => $validated['delivery_notes'] ?? null,
                    'recipient_signature' => $validated['recipient_signature'] ?? null
                ];

                // 2. Photo de livraison
                if ($request->hasFile('delivery_photo')) {
                    $path = $request->file('delivery_photo')->store('deliveries/' . $package->id, 'public');
                    $updateData['delivery_photo'] = $path;
                }

                $package->update($updateData);

                // 3. COD → Wallet livreur immédiatement
                $this->addCodToDelivererWallet($package, $validated['cod_collected']);

                // 4. Historique
                $this->updatePackageStatus($package, 'DELIVERED', 
                    "Livré à {$validated['recipient_name']} - COD: {$validated['cod_collected']} DT");

                // 5. Log détaillé
                $this->logAction('PACKAGE_DELIVERED', 'Package', $package->id,
                               'PICKED_UP', 'DELIVERED', [
                    'cod_collected' => $validated['cod_collected'],
                    'recipient_name' => $validated['recipient_name'],
                    'has_signature' => !empty($validated['recipient_signature']),
                    'has_photo' => $request->hasFile('delivery_photo'),
                    'deliverer_id' => Auth::id(),
                    'delivery_location' => [
                        'delegation' => $package->delegationTo->name ?? null,
                        'address' => $package->recipient_data['address'] ?? null
                    ]
                ]);

                $newBalance = Auth::user()->wallet->fresh()->balance;

                return response()->json([
                    'success' => true,
                    'message' => "Colis {$package->package_code} livré avec succès! COD {$validated['cod_collected']} DT ajouté à votre wallet.",
                    'cod_amount' => $validated['cod_collected'],
                    'new_wallet_balance' => $newBalance,
                    'formatted_balance' => number_format($newBalance, 3) . ' DT'
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Erreur livraison', [
                'package_id' => $package->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la livraison. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * Marquer client non disponible - VERSION AMÉLIORÉE
     */
    public function markUnavailable(Package $package, Request $request)
    {
        if (!$this->canPerformAction($package, 'attempt')) {
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée.'
            ], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|in:CLIENT_ABSENT,ADDRESS_NOT_FOUND,CLIENT_REFUSES,PHONE_OFF,OTHER',
            'attempt_notes' => 'required|string|max:500',
            'next_attempt_date' => 'nullable|date|after:now',
            'attempt_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120'
        ]);

        try {
            return DB::transaction(function () use ($package, $validated, $request) {
                // Incrémenter compteur tentatives (max 1 par jour)
                $today = Carbon::now()->format('Y-m-d');
                $lastAttemptDate = $package->updated_at->format('Y-m-d');
                
                if ($lastAttemptDate !== $today) {
                    $package->increment('delivery_attempts');
                }

                $attemptCount = $package->fresh()->delivery_attempts;
                
                $updateData = [
                    'unavailable_reason' => $validated['reason'],
                    'unavailable_notes' => $validated['attempt_notes'],
                    'next_attempt_planned' => $validated['next_attempt_date'] ?? null
                ];

                // Photo de tentative si fournie
                if ($request->hasFile('attempt_photo')) {
                    $path = $request->file('attempt_photo')->store('attempts/' . $package->id, 'public');
                    $updateData['attempt_photo'] = $path;
                }

                // Après 3 tentatives → VERIFIED (à retourner)
                if ($attemptCount >= 3) {
                    $updateData['status'] = 'VERIFIED';
                    $updateData['verification_notes'] = "3 tentatives échouées - Prêt pour retour";
                    
                    $message = "3ème tentative échouée. Colis #{$package->package_code} marqué pour retour.";
                    $this->updatePackageStatus($package, 'VERIFIED', $message);
                } else {
                    $updateData['status'] = 'UNAVAILABLE';
                    
                    $message = "Tentative #{$attemptCount} - {$this->getReasonLabel($validated['reason'])}";
                    $this->updatePackageStatus($package, 'UNAVAILABLE', $message);
                }

                $package->update($updateData);

                $this->logAction('DELIVERY_ATTEMPT_FAILED', 'Package', $package->id,
                               null, null, [
                    'attempt_count' => $attemptCount,
                    'reason' => $validated['reason'],
                    'notes' => $validated['attempt_notes'],
                    'has_photo' => $request->hasFile('attempt_photo'),
                    'next_attempt' => $validated['next_attempt_date'] ?? null
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $attemptCount >= 3 ? 
                        "Colis marqué pour retour après 3 tentatives" : 
                        "Tentative #{$attemptCount} enregistrée",
                    'attempt_count' => $attemptCount,
                    'status' => $package->fresh()->status,
                    'is_final_attempt' => $attemptCount >= 3
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Erreur tentative livraison', [
                'package_id' => $package->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement de la tentative.'
            ], 500);
        }
    }

    /**
     * Retourner à l'expéditeur - VERSION AMÉLIORÉE
     */
    public function returnToSender(Package $package, Request $request)
    {
        if (!$this->canPerformAction($package, 'return')) {
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée.'
            ], 403);
        }

        $validated = $request->validate([
            'return_reason' => 'required|string|max:500',
            'return_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'return_notes' => 'nullable|string|max:500'
        ]);

        try {
            return DB::transaction(function () use ($package, $validated, $request) {
                $updateData = [
                    'status' => 'RETURNED',
                    'returned_at' => now(),
                    'return_reason' => $validated['return_reason'],
                    'return_notes' => $validated['return_notes'] ?? null
                ];

                if ($request->hasFile('return_photo')) {
                    $path = $request->file('return_photo')->store('returns/' . $package->id, 'public');
                    $updateData['return_photo'] = $path;
                }

                $package->update($updateData);

                $this->updatePackageStatus($package, 'RETURNED', 
                    "Retourné à l'expéditeur - Motif: {$validated['return_reason']}");

                $this->logAction('PACKAGE_RETURNED', 'Package', $package->id,
                               'VERIFIED', 'RETURNED', [
                    'return_reason' => $validated['return_reason'],
                    'return_notes' => $validated['return_notes'] ?? null,
                    'has_photo' => $request->hasFile('return_photo'),
                    'deliverer_id' => Auth::id()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Colis {$package->package_code} retourné avec succès.",
                    'redirect' => route('deliverer.returns.index')
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Erreur retour', [
                'package_id' => $package->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du retour.'
            ], 500);
        }
    }

    /**
     * Scan par lot (manifestes) - VERSION AMÉLIORÉE
     */
    public function scanBatch(Request $request)
    {
        $validated = $request->validate([
            'codes' => 'required|array|min:1|max:50',
            'codes.*' => 'required|string|max:100',
            'action' => 'required|string|in:pickup,deliver,return'
        ]);

        $results = [];
        $successCount = 0;
        $failureCount = 0;

        DB::beginTransaction();

        try {
            foreach ($validated['codes'] as $code) {
                $package = $this->findPackageByCode($code);
                
                if (!$package) {
                    $results[] = [
                        'code' => $code,
                        'success' => false,
                        'message' => 'Colis introuvable'
                    ];
                    $failureCount++;
                    continue;
                }

                // Vérifier les autorisations
                if (!$this->canPerformAction($package, $validated['action'])) {
                    $results[] = [
                        'code' => $code,
                        'success' => false,
                        'message' => 'Action non autorisée pour ce colis'
                    ];
                    $failureCount++;
                    continue;
                }

                // Exécuter l'action
                $actionResult = $this->executeBatchAction($package, $validated['action']);
                
                $results[] = [
                    'code' => $code,
                    'success' => $actionResult['success'],
                    'message' => $actionResult['message'],
                    'package_id' => $package->id
                ];

                if ($actionResult['success']) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            }

            if ($successCount > 0) {
                DB::commit();
                
                $this->logAction('BATCH_SCAN_' . strtoupper($validated['action']), 'Package',
                               null, null, null, [
                    'deliverer_id' => Auth::id(),
                    'codes_count' => count($validated['codes']),
                    'success_count' => $successCount,
                    'failure_count' => $failureCount,
                    'action' => $validated['action'],
                    'codes_processed' => $validated['codes']
                ]);
            } else {
                DB::rollback();
            }

            return response()->json([
                'success' => $successCount > 0,
                'message' => "{$successCount} colis traités avec succès, {$failureCount} échecs.",
                'results' => $results,
                'summary' => [
                    'total' => count($validated['codes']),
                    'success' => $successCount,
                    'failures' => $failureCount
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Erreur scan par lot', [
                'codes' => $validated['codes'],
                'action' => $validated['action'],
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement par lot.'
            ], 500);
        }
    }

    /**
     * API - Statistiques dashboard - VERSION AMÉLIORÉE
     */
    public function apiDashboardStats()
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $delivererId = Auth::id();
            
            $stats = [
                'available_pickups' => Package::where('status', 'AVAILABLE')->count(),
                'my_pickups' => Package::where('assigned_deliverer_id', $delivererId)
                                     ->where('status', 'ACCEPTED')->count(),
                'deliveries' => Package::where('assigned_deliverer_id', $delivererId)
                                     ->whereIn('status', ['PICKED_UP', 'UNAVAILABLE'])->count(),
                'returns' => Package::where('assigned_deliverer_id', $delivererId)
                                   ->where('status', 'VERIFIED')->count(),
                'payments' => 0, // À implémenter selon les paiements clients
                'deliveries_today' => Package::where('assigned_deliverer_id', $delivererId)
                                            ->where('status', 'DELIVERED')
                                            ->whereDate('delivered_at', today())->count(),
                'cod_collected_today' => Package::where('assigned_deliverer_id', $delivererId)
                                               ->where('status', 'DELIVERED')
                                               ->whereDate('delivered_at', today())
                                               ->sum('cod_amount'),
                'urgent_deliveries' => Package::where('assigned_deliverer_id', $delivererId)
                                             ->where('status', 'UNAVAILABLE')
                                             ->where('delivery_attempts', '>=', 3)
                                             ->count()
            ];

            return response()->json($stats);
            
        } catch (\Exception $e) {
            Log::error('Erreur chargement stats dashboard', [
                'deliverer_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'available_pickups' => 0,
                'my_pickups' => 0,
                'deliveries' => 0,
                'returns' => 0,
                'payments' => 0,
                'deliveries_today' => 0,
                'cod_collected_today' => 0,
                'urgent_deliveries' => 0
            ]);
        }
    }

    /**
     * API - Solde wallet
     */
    public function apiWalletBalance()
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $user = Auth::user();
            $user->ensureWallet();

            return response()->json([
                'balance' => $user->wallet->balance ?? 0,
                'formatted_balance' => number_format($user->wallet->balance ?? 0, 3) . ' DT'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur chargement wallet balance', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'balance' => 0,
                'formatted_balance' => '0.000 DT'
            ]);
        }
    }

    /**
     * API - Délégations disponibles
     */
    public function apiDelegations()
    {
        try {
            $delegations = Delegation::where('active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'zone']);

            return response()->json([
                'success' => true,
                'delegations' => $delegations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'delegations' => []
            ]);
        }
    }

    // ==================== MÉTHODES PRIVÉES UTILITAIRES AMÉLIORÉES ====================

    /**
     * Déterminer l'action contextuelle pour un scan - VERSION AMÉLIORÉE
     */
    private function determineScanAction(Package $package)
    {
        $delivererId = Auth::id();
        
        // Cas 1: Colis disponible (non assigné)
        if ($package->status === 'AVAILABLE') {
            return [
                'success' => true,
                'message' => "Colis #{$package->package_code} disponible pour acceptation",
                'action' => 'accept',
                'redirect' => route('deliverer.packages.show', $package),
                'package' => $this->formatPackageForScan($package),
                'can_accept' => true
            ];
        }

        // Cas 2: Colis assigné à ce livreur
        if ($package->assigned_deliverer_id === $delivererId) {
            switch ($package->status) {
                case 'ACCEPTED':
                    return [
                        'success' => true,
                        'message' => "Colis #{$package->package_code} prêt pour collecte",
                        'action' => 'pickup',
                        'redirect' => route('deliverer.packages.show', $package),
                        'package' => $this->formatPackageForScan($package)
                    ];
                    
                case 'PICKED_UP':
                case 'UNAVAILABLE':
                    $urgentBadge = $package->delivery_attempts >= 3 ? ' ⚠️ URGENT' : '';
                    return [
                        'success' => true,
                        'message' => "Colis #{$package->package_code} prêt pour livraison{$urgentBadge}",
                        'action' => 'deliver',
                        'redirect' => route('deliverer.packages.show', $package),
                        'package' => $this->formatPackageForScan($package),
                        'cod_warning' => 'COD EXACT requis: ' . number_format($package->cod_amount, 3) . ' DT',
                        'is_urgent' => $package->delivery_attempts >= 3
                    ];
                    
                case 'DELIVERED':
                    return [
                        'success' => true,
                        'message' => "Colis #{$package->package_code} déjà livré le " . $package->delivered_at->format('d/m/Y'),
                        'action' => 'view',
                        'redirect' => route('deliverer.packages.show', $package),
                        'package' => $this->formatPackageForScan($package)
                    ];
                    
                case 'VERIFIED':
                    return [
                        'success' => true,
                        'message' => "Colis #{$package->package_code} à retourner à l'expéditeur",
                        'action' => 'return',
                        'redirect' => route('deliverer.packages.show', $package),
                        'package' => $this->formatPackageForScan($package)
                    ];
                    
                case 'RETURNED':
                    return [
                        'success' => true,
                        'message' => "Colis #{$package->package_code} déjà retourné le " . $package->returned_at->format('d/m/Y'),
                        'action' => 'view',
                        'package' => $this->formatPackageForScan($package)
                    ];
            }
        }

        // Cas 3: Colis assigné à un autre livreur
        if ($package->assigned_deliverer_id && $package->assigned_deliverer_id !== $delivererId) {
            $otherDeliverer = User::find($package->assigned_deliverer_id);
            
            return [
                'success' => false,
                'message' => "Colis #{$package->package_code} assigné à {$otherDeliverer->name}",
                'assigned_to' => $otherDeliverer->name,
                'package' => $this->formatPackageForScan($package)
            ];
        }

        // Cas 4: Statut non géré
        return [
            'success' => false,
            'message' => "Colis #{$package->package_code} - Statut: {$package->status}",
            'package' => $this->formatPackageForScan($package)
        ];
    }

    /**
     * Déterminer le contexte du scan
     */
    private function determineScanContext(Package $package)
    {
        if ($package->status === 'AVAILABLE') {
            return 'available_package';
        }
        
        if ($package->assigned_deliverer_id === Auth::id()) {
            return 'assigned_package';
        }
        
        return 'other_package';
    }

    /**
     * Vérifier si une action peut être effectuée sur un package
     */
    private function canPerformAction(Package $package, string $action)
    {
        $delivererId = Auth::id();
        
        switch ($action) {
            case 'pickup':
                return $package->assigned_deliverer_id === $delivererId && 
                       $package->status === 'ACCEPTED';
                       
            case 'deliver':
                return $package->assigned_deliverer_id === $delivererId && 
                       in_array($package->status, ['PICKED_UP', 'UNAVAILABLE']);
                       
            case 'return':
                return $package->assigned_deliverer_id === $delivererId && 
                       in_array($package->status, ['VERIFIED', 'PICKED_UP']);
                       
            case 'attempt':
                return $package->assigned_deliverer_id === $delivererId && 
                       in_array($package->status, ['PICKED_UP', 'UNAVAILABLE']);
                       
            default:
                return false;
        }
    }

    /**
     * Exécuter une action dans le cadre d'un traitement par lot
     */
    private function executeBatchAction(Package $package, string $action)
    {
        try {
            switch ($action) {
                case 'pickup':
                    $package->update([
                        'status' => 'PICKED_UP',
                        'picked_up_at' => now(),
                        'pickup_notes' => 'Collecté via scan par lot'
                    ]);
                    
                    $this->updatePackageStatus($package, 'PICKED_UP', 'Collecté via scan par lot');
                    
                    return ['success' => true, 'message' => 'Collecté'];
                    
                case 'deliver':
                    return ['success' => false, 'message' => 'Livraison par lot non autorisée (COD requis)'];
                    
                case 'return':
                    $package->update([
                        'status' => 'RETURNED',
                        'returned_at' => now(),
                        'return_reason' => 'Retourné via scan par lot'
                    ]);
                    
                    $this->updatePackageStatus($package, 'RETURNED', 'Retourné via scan par lot');
                    
                    return ['success' => true, 'message' => 'Retourné'];
                    
                default:
                    return ['success' => false, 'message' => 'Action non reconnue'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Erreur lors de l\'exécution: ' . $e->getMessage()];
        }
    }

    /**
     * Formater les données package pour le scan
     */
    private function formatPackageForScan(Package $package)
    {
        return [
            'id' => $package->id,
            'code' => $package->package_code,
            'status' => $package->status,
            'cod_amount' => $package->cod_amount,
            'recipient_name' => $package->recipient_data['name'] ?? 'N/A',
            'recipient_phone' => $package->recipient_data['phone'] ?? 'N/A',
            'recipient_address' => $package->recipient_data['address'] ?? 'N/A',
            'sender_name' => $package->sender->name ?? $package->sender_data['name'] ?? 'N/A',
            'delegation_from' => $package->delegationFrom->name ?? 'N/A',
            'delegation_to' => $package->delegationTo->name ?? 'N/A',
            'delivery_attempts' => $package->delivery_attempts ?? 0,
            'created_at' => $package->created_at->toISOString(),
            'assigned_at' => $package->assigned_at ? $package->assigned_at->toISOString() : null,
            'is_urgent' => $package->delivery_attempts >= 3
        ];
    }

    /**
     * Ajouter COD au wallet livreur (wallet = caisse physique)
     */
    private function addCodToDelivererWallet(Package $package, float $codAmount)
    {
        $deliverer = Auth::user();
        $deliverer->ensureWallet();

        $oldBalance = $deliverer->wallet->balance;
        $newBalance = $oldBalance + $codAmount;

        // Créer transaction COD
        $transaction = FinancialTransaction::create([
            'transaction_id' => 'COD_' . $package->package_code . '_' . time(),
            'user_id' => $deliverer->id,
            'type' => 'COD_COLLECTION',
            'amount' => $codAmount,
            'status' => 'COMPLETED',
            'package_id' => $package->id,
            'description' => "COD collecté - Colis #{$package->package_code}",
            'wallet_balance_before' => $oldBalance,
            'wallet_balance_after' => $newBalance,
            'completed_at' => now(),
            'metadata' => [
                'package_code' => $package->package_code,
                'recipient_name' => $package->recipient_data['name'] ?? null,
                'delivery_location' => $package->delegationTo->name ?? null
            ]
        ]);

        // Mettre à jour wallet
        $deliverer->wallet->update([
            'balance' => $newBalance,
            'last_transaction_at' => now(),
            'last_transaction_id' => $transaction->transaction_id
        ]);
    }

    /**
     * Mettre à jour le statut du package
     */
    private function updatePackageStatus(Package $package, string $newStatus, string $notes = null)
    {
        PackageStatusHistory::create([
            'package_id' => $package->id,
            'previous_status' => $package->status,
            'new_status' => $newStatus,
            'changed_by' => Auth::id(),
            'changed_by_role' => 'DELIVERER',
            'notes' => $notes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Enregistrer une action dans les logs
     */
    private function logAction(string $actionType, string $targetType, $targetId, 
                              $oldValue = null, $newValue = null, array $additionalData = [])
    {
        ActionLog::create([
            'user_id' => Auth::id(),
            'user_role' => 'DELIVERER',
            'action_type' => $actionType,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'additional_data' => $additionalData
        ]);
    }

    /**
     * Obtenir le label d'une raison d'indisponibilité
     */
    private function getReasonLabel(string $reason): string
    {
        $labels = [
            'CLIENT_ABSENT' => 'Client absent',
            'ADDRESS_NOT_FOUND' => 'Adresse introuvable',
            'CLIENT_REFUSES' => 'Client refuse',
            'PHONE_OFF' => 'Téléphone éteint',
            'OTHER' => 'Autre'
        ];

        return $labels[$reason] ?? $reason;
    }
}