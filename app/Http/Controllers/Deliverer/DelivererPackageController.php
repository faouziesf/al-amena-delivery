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
                        ->whereIn('status', ['PICKED_UP', 'UNAVAILABLE']) // Inclut 4ème tentatives
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
     * Scanner QR/Code et traiter selon le contexte
     */
    public function scanPackage(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100'
        ]);

        $code = trim($validated['code']);
        
        try {
            // Rechercher le package par code
            $package = Package::where('package_code', $code)->first();
            
            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => "Colis introuvable avec le code: {$code}",
                    'code_scanned' => $code
                ], 404);
            }

            // Log du scan
            $this->logAction('PACKAGE_SCANNED', 'Package', $package->id, 
                           null, $code, [
                'deliverer_id' => Auth::id(),
                'package_status' => $package->status,
                'scan_context' => $this->determineScanContext($package)
            ]);

            // Déterminer l'action contextuelle selon le statut et l'assignation
            $result = $this->determineScanAction($package);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error('Erreur scan package', [
                'code' => $code,
                'deliverer_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement du code scanné.'
            ], 500);
        }
    }

    /**
     * Marquer comme collecté (Picked Up)
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
            'pickup_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        try {
            return DB::transaction(function () use ($package, $validated, $request) {
                $package->update([
                    'status' => 'PICKED_UP',
                    'pickup_notes' => $validated['pickup_notes'] ?? null,
                    'picked_up_at' => now()
                ]);

                // Photo de pickup si fournie
                if ($request->hasFile('pickup_photo')) {
                    $path = $request->file('pickup_photo')->store('pickups/' . $package->id, 'public');
                    $package->update(['pickup_photo' => $path]);
                }

                $this->updatePackageStatus($package, 'PICKED_UP', 'Colis collecté chez l\'expéditeur');

                return response()->json([
                    'success' => true,
                    'message' => "Colis {$package->package_code} collecté avec succès!"
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
     * Livrer le colis avec COD sécurisé
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
            'delivery_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        // Vérification COD exact
        if (abs($validated['cod_collected'] - $package->cod_amount) > 0.001) {
            return response()->json([
                'success' => false,
                'message' => "COD incorrect! Attendu: {$package->cod_amount} DT, Saisi: {$validated['cod_collected']} DT. Contactez le commercial si problème."
            ], 400);
        }

        try {
            return DB::transaction(function () use ($package, $validated, $request) {
                // 1. Marquer colis comme livré
                $package->update([
                    'status' => 'DELIVERED',
                    'delivered_at' => now(),
                    'delivery_notes' => $validated['delivery_notes'] ?? null,
                    'recipient_signature' => $validated['recipient_signature'] ?? null
                ]);

                // 2. Photo de livraison
                if ($request->hasFile('delivery_photo')) {
                    $path = $request->file('delivery_photo')->store('deliveries/' . $package->id, 'public');
                    $package->update(['delivery_photo' => $path]);
                }

                // 3. COD → Wallet livreur immédiatement
                $this->addCodToDelivererWallet($package, $validated['cod_collected']);

                // 4. Historique
                $this->updatePackageStatus($package, 'DELIVERED', 
                    "Livré à {$validated['recipient_name']} - COD: {$validated['cod_collected']} DT");

                // 5. Log
                $this->logAction('PACKAGE_DELIVERED', 'Package', $package->id,
                               'PICKED_UP', 'DELIVERED', [
                    'cod_collected' => $validated['cod_collected'],
                    'recipient_name' => $validated['recipient_name'],
                    'deliverer_id' => Auth::id()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Colis {$package->package_code} livré avec succès! COD {$validated['cod_collected']} DT ajouté à votre wallet.",
                    'cod_amount' => $validated['cod_collected'],
                    'new_wallet_balance' => Auth::user()->wallet->fresh()->balance
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Erreur livraison', [
                'package_id' => $package->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la livraison.'
            ], 500);
        }
    }

    /**
     * Marquer client non disponible
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
            'next_attempt_date' => 'nullable|date|after:now'
        ]);

        try {
            return DB::transaction(function () use ($package, $validated) {
                // Incrémenter compteur tentatives (max 1 par jour)
                $today = Carbon::now()->format('Y-m-d');
                $lastAttemptDate = $package->updated_at->format('Y-m-d');
                
                if ($lastAttemptDate !== $today) {
                    $package->increment('delivery_attempts');
                }

                $attemptCount = $package->fresh()->delivery_attempts;
                
                // Après 3 tentatives → VERIFIED (à retourner)
                if ($attemptCount >= 3) {
                    $package->update([
                        'status' => 'VERIFIED',
                        'verification_notes' => "3 tentatives échouées - Prêt pour retour"
                    ]);
                    
                    $message = "3ème tentative échouée. Colis #{$package->package_code} marqué pour retour.";
                    $this->updatePackageStatus($package, 'VERIFIED', $message);
                } else {
                    $package->update([
                        'status' => 'UNAVAILABLE',
                        'unavailable_reason' => $validated['reason'],
                        'unavailable_notes' => $validated['attempt_notes'],
                        'next_attempt_planned' => $validated['next_attempt_date'] ?? null
                    ]);
                    
                    $message = "Tentative #{$attemptCount} - {$validated['reason']}";
                    $this->updatePackageStatus($package, 'UNAVAILABLE', $message);
                }

                $this->logAction('DELIVERY_ATTEMPT_FAILED', 'Package', $package->id,
                               null, null, [
                    'attempt_count' => $attemptCount,
                    'reason' => $validated['reason'],
                    'notes' => $validated['attempt_notes']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $attemptCount >= 3 ? 
                        "Colis marqué pour retour après 3 tentatives" : 
                        "Tentative #{$attemptCount} enregistrée",
                    'attempt_count' => $attemptCount,
                    'status' => $package->fresh()->status
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
     * Retourner à l'expéditeur
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
            'return_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        try {
            return DB::transaction(function () use ($package, $validated, $request) {
                $package->update([
                    'status' => 'RETURNED',
                    'returned_at' => now(),
                    'return_reason' => $validated['return_reason']
                ]);

                if ($request->hasFile('return_photo')) {
                    $path = $request->file('return_photo')->store('returns/' . $package->id, 'public');
                    $package->update(['return_photo' => $path]);
                }

                $this->updatePackageStatus($package, 'RETURNED', 
                    "Retourné à l'expéditeur - Motif: {$validated['return_reason']}");

                return response()->json([
                    'success' => true,
                    'message' => "Colis {$package->package_code} retourné avec succès."
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
     * Recherche avancée par critères multiples
     */
    public function searchAdvanced(Request $request)
    {
        $validated = $request->validate([
            'clientName' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:200',
            'delegation' => 'nullable|exists:delegations,id',
            'status' => 'nullable|string|in:AVAILABLE,ACCEPTED,PICKED_UP,DELIVERED,RETURNED'
        ]);

        try {
            $query = Package::with(['sender', 'delegationFrom', 'delegationTo']);

            // Filtres selon les critères
            if ($validated['clientName']) {
                $query->where(function($q) use ($validated) {
                    $q->whereJsonContains('recipient_data->name', $validated['clientName'])
                      ->orWhereJsonContains('sender_data->name', $validated['clientName'])
                      ->orWhereHas('sender', function($sq) use ($validated) {
                          $sq->where('name', 'LIKE', "%{$validated['clientName']}%");
                      });
                });
            }

            if ($validated['phone']) {
                $query->where(function($q) use ($validated) {
                    $q->whereJsonContains('recipient_data->phone', $validated['phone'])
                      ->orWhereJsonContains('sender_data->phone', $validated['phone']);
                });
            }

            if ($validated['address']) {
                $query->where(function($q) use ($validated) {
                    $q->whereJsonContains('recipient_data->address', $validated['address'])
                      ->orWhereJsonContains('sender_data->address', $validated['address']);
                });
            }

            if ($validated['delegation']) {
                $query->where(function($q) use ($validated) {
                    $q->where('delegation_from', $validated['delegation'])
                      ->orWhere('delegation_to', $validated['delegation']);
                });
            }

            if ($validated['status']) {
                $query->where('status', $validated['status']);
            }

            // Limiter aux colis pertinents pour le livreur
            $query->where(function($q) {
                $q->where('status', 'AVAILABLE')
                  ->orWhere('assigned_deliverer_id', Auth::id());
            });

            $packages = $query->orderBy('created_at', 'desc')->limit(20)->get();

            return response()->json([
                'success' => true,
                'packages' => $packages,
                'count' => $packages->count(),
                'search_criteria' => $validated
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur recherche avancée', [
                'criteria' => $validated,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche.'
            ], 500);
        }
    }

    /**
     * Scan par lot (manifestes)
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
                $package = Package::where('package_code', $code)->first();
                
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
                    'action' => $validated['action']
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
     * API - Statistiques dashboard
     */
    public function apiDashboardStats()
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'available_pickups' => Package::where('status', 'AVAILABLE')->count(),
            'my_pickups' => Package::where('assigned_deliverer_id', Auth::id())->where('status', 'ACCEPTED')->count(),
            'deliveries' => Package::where('assigned_deliverer_id', Auth::id())->where('status', 'PICKED_UP')->count(),
            'returns' => Package::where('assigned_deliverer_id', Auth::id())->where('status', 'VERIFIED')->count(),
            'payments' => 0, // À implémenter selon les paiements clients
            'deliveries_today' => Package::where('assigned_deliverer_id', Auth::id())
                                        ->where('status', 'DELIVERED')
                                        ->whereDate('delivered_at', today())->count(),
            'cod_collected_today' => Package::where('assigned_deliverer_id', Auth::id())
                                           ->where('status', 'DELIVERED')
                                           ->whereDate('delivered_at', today())
                                           ->sum('cod_amount')
        ]);
    }

    /**
     * API - Solde wallet
     */
    public function apiWalletBalance()
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = Auth::user();
        $user->ensureWallet();

        return response()->json([
            'balance' => $user->wallet->balance ?? 0,
            'formatted_balance' => number_format($user->wallet->balance ?? 0, 3) . ' DT'
        ]);
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

    // ==================== MÉTHODES PRIVÉES UTILITAIRES ====================

    /**
     * Déterminer l'action contextuelle pour un scan
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
                'package' => $this->formatPackageForScan($package)
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
                    return [
                        'success' => true,
                        'message' => "Colis #{$package->package_code} collecté - Prêt pour livraison",
                        'action' => 'deliver',
                        'redirect' => route('deliverer.packages.show', $package),
                        'package' => $this->formatPackageForScan($package),
                        'cod_warning' => 'COD EXACT requis: ' . number_format($package->cod_amount, 3) . ' DT'
                    ];
                    
                case 'DELIVERED':
                    return [
                        'success' => true,
                        'message' => "Colis #{$package->package_code} déjà livré",
                        'action' => 'view',
                        'redirect' => route('deliverer.packages.show', $package),
                        'package' => $this->formatPackageForScan($package)
                    ];
                    
                case 'VERIFIED':
                    return [
                        'success' => true,
                        'message' => "Colis #{$package->package_code} à retourner expéditeur",
                        'action' => 'return',
                        'redirect' => route('deliverer.packages.show', $package),
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
                       $package->status === 'PICKED_UP';
                       
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
                        'picked_up_at' => now()
                    ]);
                    
                    $this->updatePackageStatus($package, 'PICKED_UP', 'Collecté via scan par lot');
                    
                    return ['success' => true, 'message' => 'Collecté'];
                    
                case 'deliver':
                    return ['success' => false, 'message' => 'Livraison par lot non autorisée (COD requis)'];
                    
                case 'return':
                    $package->update([
                        'status' => 'RETURNED',
                        'returned_at' => now()
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
            'sender_name' => $package->sender->name ?? 'N/A',
            'delegation_from' => $package->delegationFrom->name ?? 'N/A',
            'delegation_to' => $package->delegationTo->name ?? 'N/A',
            'created_at' => $package->created_at->toISOString(),
            'assigned_at' => $package->assigned_at ? $package->assigned_at->toISOString() : null
        ];
    }

    /**
     * Ajouter COD au wallet livreur (wallet = caisse physique)
     */
    private function addCodToDelivererWallet(Package $package, float $codAmount)
    {
        $deliverer = Auth::user();
        $deliverer->ensureWallet();

        // Créer transaction COD
        $transaction = FinancialTransaction::create([
            'transaction_id' => 'COD_' . $package->package_code . '_' . time(),
            'user_id' => $deliverer->id,
            'type' => 'COD_COLLECTION',
            'amount' => $codAmount,
            'status' => 'COMPLETED',
            'package_id' => $package->id,
            'description' => "COD collecté - Colis #{$package->package_code}",
            'wallet_balance_before' => $deliverer->wallet->balance,
            'wallet_balance_after' => $deliverer->wallet->balance + $codAmount,
            'completed_at' => now(),
            'metadata' => [
                'package_code' => $package->package_code,
                'recipient_name' => $package->recipient_data['name'] ?? null
            ]
        ]);

        // Mettre à jour wallet
        $deliverer->wallet->update([
            'balance' => $deliverer->wallet->balance + $codAmount,
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
}