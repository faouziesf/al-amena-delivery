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
use App\Http\Requests\Deliverer\ScanPackageRequest;
use App\Services\PackageScannerService;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class DelivererPackageController extends Controller
{
    /**
     * Index principal des colis - NOUVELLE MÉTHODE
     */
    public function index(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            abort(403, 'Accès réservé aux livreurs.');
        }

        $delivererId = Auth::id();

        // Statistiques globales pour le dashboard
        $stats = [
            'available_pickups' => Package::where('status', 'AVAILABLE')->count(),
            'my_pickups' => Package::where('assigned_deliverer_id', $delivererId)
                                 ->where('status', 'ACCEPTED')->count(),
            'deliveries' => Package::where('assigned_deliverer_id', $delivererId)
                                 ->whereIn('status', ['PICKED_UP', 'UNAVAILABLE'])->count(),
            'returns' => Package::where('assigned_deliverer_id', $delivererId)
                               ->where('status', 'VERIFIED')->count(),
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

        // Colis urgents (3ème tentative)
        $urgentPackages = Package::where('assigned_deliverer_id', $delivererId)
                                ->whereIn('status', ['PICKED_UP', 'UNAVAILABLE'])
                                ->where('delivery_attempts', '>=', 3)
                                ->with(['sender', 'delegationFrom', 'delegationTo'])
                                ->orderBy('updated_at', 'asc')
                                ->limit(5)
                                ->get();

        // Activité récente
        $recentActivity = Package::where('assigned_deliverer_id', $delivererId)
                                ->whereIn('status', ['DELIVERED', 'RETURNED', 'PICKED_UP'])
                                ->with(['sender', 'delegationFrom', 'delegationTo'])
                                ->orderBy('updated_at', 'desc')
                                ->limit(10)
                                ->get();

        return view('deliverer.packages.index', compact('stats', 'urgentPackages', 'recentActivity'));
    }

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
     * Interface de livraison une par une - MODE RAPIDE
     */
    public function singleDelivery(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            abort(403, 'Accès réservé aux livreurs.');
        }

        $packages = Package::where('assigned_deliverer_id', Auth::id())
                           ->whereIn('status', ['PICKED_UP', 'UNAVAILABLE'])
                           ->with(['sender', 'delegationFrom', 'delegationTo'])
                           ->orderByRaw('CASE
                               WHEN delivery_attempts >= 3 THEN 0
                               WHEN delivery_attempts = 2 THEN 1
                               WHEN delivery_attempts = 1 THEN 2
                               ELSE 3 END')
                           ->orderBy('cod_amount', 'desc')
                           ->get()
                           ->map(function ($package) {
                               // S'assurer que les données JSON sont décodées
                               if (is_string($package->recipient_data)) {
                                   $package->recipient_data = json_decode($package->recipient_data, true);
                               }
                               if (is_string($package->sender_data)) {
                                   $package->sender_data = json_decode($package->sender_data, true);
                               }
                               return $package;
                           });

        // Stats rapides
        $stats = [
            'total' => $packages->count(),
            'urgent' => $packages->where('delivery_attempts', '>=', 3)->count(),
            'retry' => $packages->where('delivery_attempts', '>', 0)->count(),
            'total_cod' => $packages->sum('cod_amount')
        ];

        return view('deliverer.packages.single-delivery', compact('packages', 'stats'));
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
     * Scanner QR/Code - VERSION OPTIMISÉE
     */
    public function scanPackage(ScanPackageRequest $request)
    {
        try {
            $scannerService = app(PackageScannerService::class);
            $result = $scannerService->scanCode($request->validated()['code']);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error('Erreur scan package', [
                'code' => $request->code,
                'deliverer_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement du code.'
            ], 500);
        }
    }

    /**
     * Réassigner un colis à un nouveau livreur
     */
    public function reassignPackage(Request $request, Package $package)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            $scannerService = app(PackageScannerService::class);
            $result = $scannerService->reassignPackage(
                $package,
                Auth::id(),
                $validated['reason'] ?? 'Réassignation via scan'
            );

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Erreur réassignation package', [
                'package_id' => $package->id,
                'deliverer_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réassignation.'
            ], 500);
        }
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

        // Validation adaptée selon le montant COD
        $expectedCod = (float) $package->cod_amount;
        $isCodZero = $expectedCod == 0;

        $validated = $request->validate([
            'cod_collected' => $isCodZero ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'recipient_name' => $isCodZero ? 'nullable|string|max:100' : 'required|string|max:100',
            'delivery_notes' => 'nullable|string|max:500',
            'recipient_signature' => 'nullable|string',
            'delivery_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'print_receipt' => 'nullable|boolean'
        ]);

        // Pour les colis à 0 DT, définir des valeurs par défaut
        if ($isCodZero) {
            $validated['cod_collected'] = $validated['cod_collected'] ?? 0;
            $validated['recipient_name'] = $validated['recipient_name'] ?? ($package->recipient_data['name'] ?? 'Destinataire');
        }

        // Vérification COD exact avec tolérance de 0.001 DT
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

                $response = [
                    'success' => true,
                    'message' => "Colis {$package->package_code} livré avec succès! COD {$validated['cod_collected']} DT ajouté à votre wallet.",
                    'cod_amount' => $validated['cod_collected'],
                    'new_wallet_balance' => $newBalance,
                    'formatted_balance' => number_format($newBalance, 3) . ' DT'
                ];

                // Ajouter l'URL du reçu si demandé
                if ($request->input('print_receipt')) {
                    $response['receipt_url'] = route('deliverer.packages.delivery.receipt', $package);
                }

                return response()->json($response);
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
     * Marquer client non disponible - VERSION COMPLÈTE ET AMÉLIORÉE
     */
    public function markUnavailable(Package $package, Request $request)
    {
        Log::info('markUnavailable called', [
            'package_id' => $package->id,
            'package_status' => $package->status,
            'user_id' => Auth::id(),
            'user_role' => Auth::user()->role ?? 'not_authenticated',
            'assigned_deliverer' => $package->assigned_deliverer_id,
            'request_data' => $request->all()
        ]);

        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            Log::warning('markUnavailable: Access denied - not deliverer');
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $canScan = $package->canBeScanBy(Auth::user());
        $action = $package->getActionFor(Auth::user());

        Log::info('markUnavailable: Permission check', [
            'can_scan' => $canScan,
            'action' => $action,
            'expected_action' => 'deliver'
        ]);

        if (!$canScan || $action !== 'deliver') {
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée pour ce colis.',
                'debug' => [
                    'can_scan' => $canScan,
                    'action' => $action,
                    'package_status' => $package->status,
                    'assigned_to' => $package->assigned_deliverer_id,
                    'current_user' => Auth::id()
                ]
            ], 403);
        }

        // Validation des données
        $validated = $request->validate([
            'reason' => 'required|string|in:CLIENT_ABSENT,ADDRESS_NOT_FOUND,CLIENT_REFUSES,PHONE_OFF,OTHER',
            'attempt_notes' => 'required|string|min:10|max:500',
            'next_attempt_date' => 'nullable|date|after:+1 hour|before:+7 days',
            'attempt_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120'
        ]);

        try {
            return DB::transaction(function () use ($package, $validated, $request) {
                // Verrouillage pessimiste pour éviter les conditions de course
                $package = Package::where('id', $package->id)
                                 ->whereIn('status', ['PICKED_UP', 'UNAVAILABLE'])
                                 ->where('assigned_deliverer_id', Auth::id())
                                 ->lockForUpdate()
                                 ->first();

                if (!$package) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Colis introuvable ou déjà traité.'
                    ], 404);
                }

                // Vérification limite tentatives par jour (max 1 tentative par jour)
                $today = Carbon::now()->format('Y-m-d');
                $lastAttemptDate = $package->updated_at ? $package->updated_at->format('Y-m-d') : null;
                
                $canIncrementAttempts = ($lastAttemptDate !== $today);
                
                // Incrémenter le compteur de tentatives si c'est un nouveau jour
                if ($canIncrementAttempts) {
                    $package->increment('delivery_attempts');
                }

                // Récupérer le nouveau compteur
                $attemptCount = $package->fresh()->delivery_attempts;

                // Préparer les données de mise à jour
                $updateData = [
                    'unavailable_reason' => $validated['reason'],
                    'unavailable_notes' => $validated['attempt_notes'],
                    'next_attempt_planned' => $validated['next_attempt_date'] ?? null,
                    'last_attempt_date' => now()
                ];

                // Gérer l'upload de photo de tentative
                if ($request->hasFile('attempt_photo')) {
                    try {
                        $photo = $request->file('attempt_photo');
                        $filename = 'attempt_' . $package->id . '_' . $attemptCount . '_' . time() . '.' . $photo->getClientOriginalExtension();
                        $path = $photo->storeAs('attempts/' . $package->id, $filename, 'public');
                        $updateData['attempt_photo'] = $path;
                    } catch (\Exception $e) {
                        Log::warning('Erreur upload photo tentative', [
                            'package_id' => $package->id,
                            'error' => $e->getMessage()
                        ]);
                        // Continue sans la photo si upload échoue
                    }
                }

                // Déterminer le nouveau statut selon le nombre de tentatives
                if ($attemptCount >= 3) {
                    // 3ème tentative = marquage pour retour
                    $updateData['status'] = 'VERIFIED';
                    $updateData['verification_notes'] = "3 tentatives de livraison échouées - Marqué pour retour à l'expéditeur";
                    $updateData['verified_at'] = now();
                    
                    $statusMessage = "3ème tentative échouée - Motif: {$this->getReasonLabel($validated['reason'])}";
                    $this->updatePackageStatus($package, 'VERIFIED', $statusMessage);
                    
                    $responseMessage = "3ème tentative enregistrée. Colis #{$package->package_code} marqué pour retour obligatoire.";
                    
                    // Notification pour le commercial
                    $this->createNotificationForCommercial($package, 'PACKAGE_REQUIRES_RETURN', [
                        'message' => "Le colis {$package->package_code} nécessite un retour après 3 tentatives",
                        'package_id' => $package->id,
                        'deliverer_name' => Auth::user()->name,
                        'last_reason' => $this->getReasonLabel($validated['reason'])
                    ]);
                    
                } else {
                    // Tentative 1 ou 2 = statut UNAVAILABLE
                    $updateData['status'] = 'UNAVAILABLE';
                    
                    $statusMessage = "Tentative #{$attemptCount} échouée - Motif: {$this->getReasonLabel($validated['reason'])}";
                    $this->updatePackageStatus($package, 'UNAVAILABLE', $statusMessage);
                    
                    $responseMessage = "Tentative #{$attemptCount}/3 enregistrée. " . 
                                     ($attemptCount == 2 ? "⚠️ Prochaine tentative sera la dernière!" : "Prochaine tentative possible.");
                    
                    // Programmer une notification de rappel si date fournie
                    if (isset($validated['next_attempt_date']) && $validated['next_attempt_date']) {
                        $this->scheduleAttemptReminder($package, $validated['next_attempt_date']);
                    }
                }

                // Mettre à jour le colis
                $package->update($updateData);

                // Log détaillé de l'action
                $this->logAction('DELIVERY_ATTEMPT_FAILED', 'Package', $package->id,
                               $package->getOriginal('status'), $updateData['status'], [
                    'attempt_count' => $attemptCount,
                    'reason' => $validated['reason'],
                    'reason_label' => $this->getReasonLabel($validated['reason']),
                    'notes' => $validated['attempt_notes'],
                    'has_photo' => $request->hasFile('attempt_photo'),
                    'next_attempt' => $validated['next_attempt_date'] ?? null,
                    'is_final_attempt' => $attemptCount >= 3,
                    'deliverer_id' => Auth::id(),
                    'package_code' => $package->package_code,
                    'recipient_phone' => $package->recipient_data['phone'] ?? null,
                    'delegation_to' => $package->delegationTo->name ?? null
                ]);

                // Mise à jour des métriques du livreur
                $this->updateDelivererMetrics(Auth::id(), 'attempt_failed', [
                    'attempt_count' => $attemptCount,
                    'reason' => $validated['reason']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $responseMessage,
                    'data' => [
                        'attempt_count' => $attemptCount,
                        'max_attempts' => 3,
                        'status' => $package->fresh()->status,
                        'status_message' => $package->status_message,
                        'is_final_attempt' => $attemptCount >= 3,
                        'next_action' => $attemptCount >= 3 ? 'return' : 'retry',
                        'can_retry_today' => !$canIncrementAttempts,
                        'next_attempt_date' => $validated['next_attempt_date'] ?? null,
                        'reason_label' => $this->getReasonLabel($validated['reason'])
                    ]
                ]);
            });
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides.',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Erreur critique lors tentative livraison', [
                'package_id' => $package->id,
                'deliverer_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $validated ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur technique lors de l\'enregistrement. Veuillez réessayer ou contacter le support.'
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
                // Déterminer la raison du retour
                $returnReason = $package->getReturnReason() ?? 'OTHER';
                $oldStatus = $package->status;

                $updateData = [
                    'status' => 'RETURNED',
                    'returned_at' => now(),
                    'return_reason' => $validated['return_reason'],
                    'return_notes' => $validated['return_notes'] ?? null,
                    'auto_return_reason' => $returnReason
                ];

                if ($request->hasFile('return_photo')) {
                    $path = $request->file('return_photo')->store('returns/' . $package->id, 'public');
                    $updateData['return_photo'] = $path;
                }

                $package->update($updateData);

                // Message basé sur la raison du retour
                $statusMessage = match($returnReason) {
                    'CANCELLED_BY_CLIENT' => "Retourné à l'expéditeur - Colis annulé par le client: {$validated['return_reason']}",
                    'MAX_ATTEMPTS_REACHED' => "Retourné à l'expéditeur - 3 tentatives échouées: {$validated['return_reason']}",
                    'VERIFIED_FOR_RETURN' => "Retourné à l'expéditeur - Vérifié pour retour: {$validated['return_reason']}",
                    default => "Retourné à l'expéditeur - Motif: {$validated['return_reason']}"
                };

                $this->updatePackageStatus($package, 'RETURNED', $statusMessage);

                $this->logAction('PACKAGE_RETURNED', 'Package', $package->id,
                               $oldStatus, 'RETURNED', [
                    'return_reason' => $validated['return_reason'],
                    'return_notes' => $validated['return_notes'] ?? null,
                    'auto_return_reason' => $returnReason,
                    'has_photo' => $request->hasFile('return_photo'),
                    'deliverer_id' => Auth::id(),
                    'delivery_attempts' => $package->delivery_attempts,
                    'was_cancelled_by_client' => $package->isCancelledByClient()
                ]);

                // Notification au client selon la raison
                $this->notifyClientOfReturn($package, $returnReason, $validated['return_reason']);

                return response()->json([
                    'success' => true,
                    'message' => "Colis {$package->package_code} retourné avec succès.",
                    'redirect' => route('deliverer.packages.index'),
                    'reason' => $returnReason
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

    /**
     * Afficher les détails d'un colis
     */
    public function show(Package $package)
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            abort(403, 'Accès réservé aux livreurs.');
        }

        // Vérifier que le livreur peut voir ce colis
        if ($package->status === 'AVAILABLE' || $package->assigned_deliverer_id === Auth::id()) {
            // Le livreur peut voir les colis disponibles ou qui lui sont assignés
        } else {
            abort(403, 'Vous n\'avez pas accès à ce colis.');
        }

        // Charger toutes les relations nécessaires
        $package->load([
            'sender',
            'delegationFrom', 
            'delegationTo',
            'assignedDeliverer',
            'statusHistory' => function($query) {
                $query->with('changedBy')->orderBy('created_at', 'desc')->limit(10);
            },
            'pickupDelegation'
        ]);

        // Déterminer les actions possibles
        $availableActions = $this->getAvailableActions($package);
        
        // Récupérer l'historique des tentatives
        $deliveryHistory = $this->getDeliveryHistory($package);
        
        // Calculer les statistiques
        $stats = [
            'is_urgent' => $package->delivery_attempts >= 3,
            'days_since_created' => $package->created_at->diffInDays(now()),
            'time_since_last_update' => $package->updated_at ? $package->updated_at->diffForHumans() : 'Inconnue',
            'can_be_modified' => $package->canBeModified(),
            'estimated_delivery_time' => $this->estimateDeliveryTime($package)
        ];

        return view('deliverer.packages.show', compact(
            'package', 
            'availableActions', 
            'deliveryHistory',
            'stats'
        ));
    }

    // ==================== MÉTHODES PRIVÉES UTILITAIRES ====================

    /**
     * Rechercher un package par TOUS les formats de codes possibles
     */
    private function findPackageByCode($code)
    {
        $code = trim($code);
        
        // 1. Extraire le package_code depuis URL de tracking (QR code)
        if (preg_match('/\/track\/(.+)$/', $code, $matches)) {
            $extractedCode = $matches[1];
            $package = Package::where('package_code', $extractedCode)->first();
            if ($package) {
                Log::info("Code trouvé via URL tracking: {$extractedCode}");
                return $package;
            }
        }
        
        // 2. Nettoyer et normaliser le code
        $cleanCode = strtoupper(trim($code));
        
        // 3. Recherche exacte par package_code
        $package = Package::where('package_code', $cleanCode)->first();
        if ($package) {
            Log::info("Code trouvé par recherche exacte: {$cleanCode}");
            return $package;
        }
        
        // 4. Recherche avec préfixe PKG_ ajouté
        if (!str_starts_with($cleanCode, 'PKG_')) {
            $withPrefix = 'PKG_' . $cleanCode;
            $package = Package::where('package_code', $withPrefix)->first();
            if ($package) {
                Log::info("Code trouvé avec préfixe ajouté: {$withPrefix}");
                return $package;
            }
        }
        
        // 5. Recherche sans préfixe PKG_
        if (str_starts_with($cleanCode, 'PKG_')) {
            $withoutPrefix = substr($cleanCode, 4);
            $package = Package::where('package_code', 'LIKE', '%' . $withoutPrefix . '%')->first();
            if ($package) {
                Log::info("Code trouvé sans préfixe: {$withoutPrefix}");
                return $package;
            }
        }
        
        // 6. Recherche partielle intelligente (derniers 8-12 caractères)
        if (strlen($cleanCode) >= 8) {
            $partialCode = substr($cleanCode, -min(12, strlen($cleanCode)));
            $package = Package::where('package_code', 'LIKE', '%' . $partialCode)
                              ->orderBy('created_at', 'desc')
                              ->first();
            if ($package) {
                Log::info("Code trouvé par recherche partielle: {$partialCode}");
                return $package;
            }
        }
        
        // 7. Recherche approximative si code ressemble à un format valide
        if ($this->looksLikeValidCode($cleanCode)) {
            $package = Package::where('package_code', 'LIKE', '%' . substr($cleanCode, 0, 8) . '%')
                              ->orderBy('created_at', 'desc')
                              ->first();
            if ($package) {
                Log::info("Code trouvé par recherche approximative");
                return $package;
            }
        }
        
        Log::warning("Aucun package trouvé pour le code: {$code}");
        return null;
    }

    /**
     * Détecter le format du code scanné
     */
    private function detectCodeFormat($code)
    {
        // URL de tracking (QR code)
        if (preg_match('/^https?:\/\/.*\/track\//', $code)) {
            return 'QR_TRACKING_URL';
        }
        
        // Package code complet
        if (preg_match('/^PKG_[A-Z0-9]{8,}_\d{8}$/', $code)) {
            return 'FULL_PACKAGE_CODE';
        }
        
        // Code court (code-barres)
        if (preg_match('/^[A-Z0-9]{8,16}$/', $code)) {
            return 'SHORT_BARCODE';
        }
        
        // Code numérique pur
        if (preg_match('/^\d{8,}$/', $code)) {
            return 'NUMERIC_CODE';
        }
        
        // Autre format
        return 'UNKNOWN_FORMAT';
    }

    /**
     * Vérifier si un code ressemble à un format valide
     */
    private function looksLikeValidCode($code)
    {
        // Au moins 8 caractères alphanumériques
        if (strlen($code) < 8) return false;
        
        // Contient seulement des lettres, chiffres et underscores
        if (!preg_match('/^[A-Z0-9_]+$/', $code)) return false;
        
        // Format probable de package code
        if (preg_match('/^PKG_|^[A-Z0-9]{8}|_\d{8}$/', $code)) return true;
        
        return false;
    }

    /**
     * Suggestions de codes similaires améliorées
     */
    private function getCodeSuggestions($code)
    {
        $suggestions = [];
        $cleanCode = strtoupper(trim($code));
        
        // Recherche par partie de code (derniers 6-8 caractères)
        if (strlen($cleanCode) >= 6) {
            $partial = substr($cleanCode, -8);
            $similar = Package::where('package_code', 'LIKE', '%' . $partial . '%')
                             ->limit(3)
                             ->pluck('package_code')
                             ->toArray();
            $suggestions = array_merge($suggestions, $similar);
        }
        
        // Recherche par date récente si format contient une date
        if (preg_match('/(\d{8})/', $cleanCode, $matches)) {
            $dateStr = $matches[1];
            $recent = Package::where('package_code', 'LIKE', '%' . $dateStr . '%')
                            ->orderBy('created_at', 'desc')
                            ->limit(2)
                            ->pluck('package_code')
                            ->toArray();
            $suggestions = array_merge($suggestions, $recent);
        }
        
        return array_unique(array_slice($suggestions, 0, 5));
    }

    /**
     * Déterminer l'action contextuelle pour un scan - VERSION ULTRA OPTIMISÉE
     */
    private function determineScanAction(Package $package)
    {
        $delivererId = Auth::id();
        $statusMessages = $this->getStatusMessages();
        
        // Cas 1: Colis disponible (non assigné)
        if ($package->status === 'AVAILABLE') {
            return [
                'success' => true,
                'message' => "✅ Colis #{$package->package_code} disponible pour acceptation",
                'action' => 'accept',
                'redirect' => route('deliverer.packages.show', $package),
                'package' => $this->formatPackageForScan($package),
                'can_accept' => true,
                'instructions' => "Appuyez sur 'Accepter' pour prendre en charge ce colis"
            ];
        }

        // Cas 2: Colis assigné à ce livreur
        if ($package->assigned_deliverer_id === $delivererId) {
            switch ($package->status) {
                case 'CREATED':
                    return [
                        'success' => true,
                        'message' => "⏳ Colis #{$package->package_code} en attente de traitement",
                        'action' => 'view',
                        'redirect' => route('deliverer.packages.show', $package),
                        'package' => $this->formatPackageForScan($package),
                        'instructions' => "Ce colis est en cours de traitement"
                    ];
                    
                case 'ACCEPTED':
                    return [
                        'success' => true,
                        'message' => "📦 Colis #{$package->package_code} prêt pour collecte",
                        'action' => 'pickup',
                        'redirect' => route('deliverer.packages.show', $package),
                        'package' => $this->formatPackageForScan($package),
                        'instructions' => "Rendez-vous chez l'expéditeur pour collecter ce colis",
                        'pickup_info' => $this->getPickupInfo($package)
                    ];
                    
                case 'PICKED_UP':
                    $urgentBadge = $package->delivery_attempts >= 3 ? ' 🚨 URGENT' : '';
                    return [
                        'success' => true,
                        'message' => "🚚 Colis #{$package->package_code} prêt pour livraison{$urgentBadge}",
                        'action' => 'deliver',
                        'redirect' => route('deliverer.packages.show', $package),
                        'package' => $this->formatPackageForScan($package),
                        'cod_warning' => '💰 COD EXACT requis: ' . number_format($package->cod_amount, 3) . ' DT',
                        'is_urgent' => $package->delivery_attempts >= 3,
                        'instructions' => $package->delivery_attempts >= 3 ? 
                            "⚠️ 3ème tentative - Livraison prioritaire!" : 
                            "Livrer chez le destinataire et encaisser le COD",
                        'delivery_info' => $this->getDeliveryInfo($package)
                    ];
                    
                case 'UNAVAILABLE':
                    $attemptInfo = "Tentative #{$package->delivery_attempts}/3";
                    return [
                        'success' => true,
                        'message' => "🔄 Colis #{$package->package_code} - Nouvelle tentative ({$attemptInfo})",
                        'action' => 'deliver',
                        'redirect' => route('deliverer.packages.show', $package),
                        'package' => $this->formatPackageForScan($package),
                        'is_urgent' => $package->delivery_attempts >= 3,
                        'cod_warning' => '💰 COD EXACT requis: ' . number_format($package->cod_amount, 3) . ' DT',
                        'instructions' => $package->delivery_attempts >= 2 ? 
                            "⚠️ Dernière tentative avant retour!" : 
                            "Nouvelle tentative de livraison",
                        'previous_attempt' => $this->getPreviousAttemptInfo($package)
                    ];
                    
                case 'DELIVERED':
                    return [
                        'success' => true,
                        'message' => "✅ Colis #{$package->package_code} déjà livré le " . $package->delivered_at?->format('d/m/Y à H:i'),
                        'action' => 'view',
                        'redirect' => route('deliverer.packages.show', $package),
                        'package' => $this->formatPackageForScan($package),
                        'delivery_details' => [
                            'delivered_at' => $package->delivered_at?->format('d/m/Y H:i'),
                            'cod_amount' => number_format($package->cod_amount, 3) . ' DT'
                        ]
                    ];
                    
                case 'VERIFIED':
                    return [
                        'success' => true,
                        'message' => "↩️ Colis #{$package->package_code} à retourner à l'expéditeur",
                        'action' => 'return',
                        'redirect' => route('deliverer.packages.show', $package),
                        'package' => $this->formatPackageForScan($package),
                        'instructions' => "Retourner ce colis chez l'expéditeur après 3 tentatives échouées",
                        'return_info' => $this->getReturnInfo($package)
                    ];
                    
                case 'RETURNED':
                    return [
                        'success' => true,
                        'message' => "↩️ Colis #{$package->package_code} déjà retourné le " . $package->returned_at?->format('d/m/Y à H:i'),
                        'action' => 'view',
                        'package' => $this->formatPackageForScan($package),
                        'return_details' => [
                            'returned_at' => $package->returned_at?->format('d/m/Y H:i'),
                            'return_reason' => $package->return_reason
                        ]
                    ];
                    
                case 'PAID':
                    return [
                        'success' => true,
                        'message' => "💰 Colis #{$package->package_code} livré et payé",
                        'action' => 'view',
                        'package' => $this->formatPackageForScan($package)
                    ];
                    
                case 'CANCELLED':
                    return [
                        'success' => false,
                        'message' => "❌ Colis #{$package->package_code} annulé",
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
                'message' => "🔒 Colis #{$package->package_code} assigné à un autre livreur",
                'assigned_to' => $otherDeliverer->name ?? 'Livreur inconnu',
                'package' => $this->formatPackageForScan($package),
                'instructions' => "Ce colis ne vous est pas assigné"
            ];
        }

        // Cas 4: Statut non géré
        return [
            'success' => false,
            'message' => "❓ Colis #{$package->package_code} - Statut: {$package->status}",
            'package' => $this->formatPackageForScan($package),
            'instructions' => "Statut non reconnu, contactez votre superviseur"
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
                       in_array($package->status, ['VERIFIED', 'PICKED_UP', 'UNAVAILABLE']) &&
                       ($package->delivery_attempts >= 3 || $package->isCancelledByClient());
                       
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
     * Obtenir les informations de collecte
     */
    private function getPickupInfo(Package $package)
    {
        $senderData = $package->supplier_data ?? $package->sender_data;
        
        return [
            'name' => $senderData['name'] ?? 'N/A',
            'phone' => $senderData['phone'] ?? 'N/A',
            'address' => $package->pickup_address ?? ($senderData['address'] ?? 'N/A'),
            'delegation' => $package->delegationFrom->name ?? 'N/A',
            'notes' => $package->pickup_notes
        ];
    }

    /**
     * Obtenir les informations de livraison
     */
    private function getDeliveryInfo(Package $package)
    {
        return [
            'name' => $package->recipient_data['name'] ?? 'N/A',
            'phone' => $package->recipient_data['phone'] ?? 'N/A',
            'address' => $package->recipient_data['address'] ?? 'N/A',
            'delegation' => $package->delegationTo->name ?? 'N/A',
            'cod_amount' => number_format($package->cod_amount, 3) . ' DT',
            'attempts' => $package->delivery_attempts,
            'is_fragile' => $package->is_fragile,
            'requires_signature' => $package->requires_signature,
            'special_instructions' => $package->special_instructions
        ];
    }

    /**
     * Obtenir les informations de retour
     */
    private function getReturnInfo(Package $package)
    {
        $senderData = $package->supplier_data ?? $package->sender_data;
        
        return [
            'name' => $senderData['name'] ?? 'N/A',
            'phone' => $senderData['phone'] ?? 'N/A',
            'address' => $package->pickup_address ?? ($senderData['address'] ?? 'N/A'),
            'delegation' => $package->delegationFrom->name ?? 'N/A',
            'attempts_made' => $package->delivery_attempts,
            'unavailable_reason' => $package->unavailable_reason
        ];
    }

    /**
     * Obtenir les informations de la tentative précédente
     */
    private function getPreviousAttemptInfo(Package $package)
    {
        return [
            'attempt_number' => $package->delivery_attempts,
            'reason' => $package->unavailable_reason,
            'notes' => $package->unavailable_notes,
            'last_attempt' => $package->updated_at?->format('d/m/Y H:i')
        ];
    }

    /**
     * Messages de statut standardisés
     */
    private function getStatusMessages()
    {
        return [
            'CREATED' => '⏳ En attente',
            'AVAILABLE' => '📦 Disponible',
            'ACCEPTED' => '✅ Accepté',
            'PICKED_UP' => '🚚 Collecté',
            'DELIVERED' => '✅ Livré',
            'PAID' => '💰 Payé',
            'REFUSED' => '❌ Refusé',
            'RETURNED' => '↩️ Retourné',
            'UNAVAILABLE' => '🔄 Indisponible',
            'VERIFIED' => '✔️ Vérifié',
            'CANCELLED' => '❌ Annulé'
        ];
    }

    /**
     * Formater les données package pour le scan avec infos complètes
     */
    private function formatPackageForScan(Package $package)
    {
        return [
            'id' => $package->id,
            'code' => $package->package_code,
            'status' => $package->status,
            'status_label' => $package->status_message,
            'cod_amount' => $package->cod_amount,
            'formatted_cod' => number_format($package->cod_amount, 3) . ' DT',
            
            // Informations expéditeur/fournisseur
            'sender_name' => $package->sender->name ?? ($package->sender_data['name'] ?? 'N/A'),
            'supplier_name' => $package->supplier_data['name'] ?? null,
            
            // Informations destinataire
            'recipient_name' => $package->recipient_data['name'] ?? 'N/A',
            'recipient_phone' => $package->recipient_data['phone'] ?? 'N/A',
            'recipient_address' => $package->recipient_data['address'] ?? 'N/A',
            
            // Délégations
            'delegation_from' => $package->delegationFrom->name ?? 'N/A',
            'delegation_to' => $package->delegationTo->name ?? 'N/A',
            
            // Détails du colis
            'content_description' => $package->content_description,
            'package_weight' => $package->package_weight,
            'package_value' => $package->package_value,
            'is_fragile' => $package->is_fragile,
            'requires_signature' => $package->requires_signature,
            'special_instructions' => $package->special_instructions,
            
            // Statut livraison
            'delivery_attempts' => $package->delivery_attempts ?? 0,
            'is_urgent' => $package->delivery_attempts >= 3,
            'unavailable_reason' => $package->unavailable_reason,
            
            // Dates
            'created_at' => $package->created_at->toISOString(),
            'assigned_at' => $package->assigned_at?->toISOString(),
            'delivered_at' => $package->delivered_at?->toISOString(),
            'returned_at' => $package->returned_at?->toISOString(),
            
            // URLs de tracking
            'tracking_url' => url('/track/' . $package->package_code),
            'public_tracking_url' => route('public.track.package', $package->package_code)
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
     * Obtenir le label français d'une raison d'indisponibilité
     */
    private function getReasonLabel(string $reason): string
    {
        $labels = [
            'CLIENT_ABSENT' => 'Client absent du domicile',
            'ADDRESS_NOT_FOUND' => 'Adresse introuvable ou incorrecte',
            'CLIENT_REFUSES' => 'Client refuse de recevoir le colis',
            'PHONE_OFF' => 'Téléphone éteint ou injoignable',
            'OTHER' => 'Autre motif'
        ];

        return $labels[$reason] ?? $reason;
    }

    /**
     * Créer une notification pour le commercial
     */
    private function createNotificationForCommercial(Package $package, string $type, array $data)
    {
        try {
            // Si le modèle Notification existe
            if (class_exists(\App\Models\Notification::class)) {
                // Trouver un commercial disponible (logique à adapter selon vos besoins)
                $commercial = User::where('role', 'COMMERCIAL')
                                 ->where('account_status', 'ACTIVE')
                                 ->first();
                
                if ($commercial) {
                    \App\Models\Notification::create([
                        'user_id' => $commercial->id,
                        'type' => $type,
                        'title' => 'Colis nécessite attention',
                        'message' => $data['message'],
                        'data' => $data,
                        'priority' => 'HIGH',
                        'related_type' => 'Package',
                        'related_id' => $package->id
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Erreur création notification commercial', [
                'error' => $e->getMessage(),
                'package_id' => $package->id
            ]);
        }
    }

    /**
     * Programmer un rappel pour la prochaine tentative
     */
    private function scheduleAttemptReminder(Package $package, string $nextAttemptDate)
    {
        try {
            // Si système de jobs/queues implémenté
            if (class_exists(\App\Jobs\SendAttemptReminder::class)) {
                $reminderTime = Carbon::parse($nextAttemptDate)->subHour(); // 1h avant
                
                \App\Jobs\SendAttemptReminder::dispatch($package, Auth::user())
                    ->delay($reminderTime);
            }
        } catch (\Exception $e) {
            Log::warning('Erreur programmation rappel', [
                'error' => $e->getMessage(),
                'package_id' => $package->id,
                'next_attempt' => $nextAttemptDate
            ]);
        }
    }

    /**
     * Mettre à jour les métriques du livreur
     */
    private function updateDelivererMetrics(int $delivererId, string $action, array $data = [])
    {
        try {
            // Si système de métriques implémenté
            if (class_exists(\App\Models\DelivererMetrics::class)) {
                \App\Models\DelivererMetrics::updateMetrics($delivererId, $action, $data);
            }
        } catch (\Exception $e) {
            Log::warning('Erreur mise à jour métriques', [
                'error' => $e->getMessage(),
                'deliverer_id' => $delivererId,
                'action' => $action
            ]);
        }
    }

    /**
     * Déterminer les actions disponibles pour ce package
     */
    private function getAvailableActions(Package $package)
    {
        $actions = [];
        $delivererId = Auth::id();

        switch ($package->status) {
            case 'AVAILABLE':
                $actions[] = [
                    'key' => 'accept',
                    'label' => 'Accepter ce pickup',
                    'icon' => 'check-circle',
                    'color' => 'emerald',
                    'primary' => true
                ];
                break;

            case 'ACCEPTED':
                if ($package->assigned_deliverer_id === $delivererId) {
                    $actions[] = [
                        'key' => 'pickup',
                        'label' => 'Marquer comme collecté',
                        'icon' => 'truck',
                        'color' => 'blue',
                        'primary' => true
                    ];
                }
                break;

            case 'PICKED_UP':
            case 'UNAVAILABLE':
                if ($package->assigned_deliverer_id === $delivererId) {
                    $actions[] = [
                        'key' => 'deliver',
                        'label' => 'Livrer le colis',
                        'icon' => 'check',
                        'color' => 'green',
                        'primary' => true
                    ];
                    $actions[] = [
                        'key' => 'unavailable',
                        'label' => 'Marquer indisponible',
                        'icon' => 'clock',
                        'color' => 'orange',
                        'primary' => false
                    ];
                }
                break;

            case 'VERIFIED':
                if ($package->assigned_deliverer_id === $delivererId) {
                    $actions[] = [
                        'key' => 'return',
                        'label' => 'Retourner à l\'expéditeur',
                        'icon' => 'arrow-left',
                        'color' => 'red',
                        'primary' => true
                    ];
                }
                break;
        }

        // Actions secondaires toujours disponibles
        $actions[] = [
            'key' => 'scan',
            'label' => 'Scanner ce colis',
            'icon' => 'qrcode',
            'color' => 'purple',
            'primary' => false
        ];

        if ($package->recipient_data && isset($package->recipient_data['address'])) {
            $actions[] = [
                'key' => 'navigate',
                'label' => 'Navigation GPS',
                'icon' => 'map',
                'color' => 'indigo',
                'primary' => false
            ];
        }

        return $actions;
    }

    /**
     * Récupérer l'historique des tentatives de livraison
     */
    private function getDeliveryHistory(Package $package)
    {
        if (!$package->statusHistory) {
            return [];
        }

        return $package->statusHistory->map(function($history) {
            return [
                'status' => $history->new_status,
                'date' => $history->created_at,
                'user' => $history->changedBy->name ?? 'Système',
                'notes' => $history->notes,
                'formatted_date' => $history->created_at->format('d/m/Y H:i')
            ];
        })->toArray();
    }

    /**
     * Estimer le temps de livraison
     */
    private function estimateDeliveryTime(Package $package)
    {
        if ($package->status === 'DELIVERED') {
            return 'Livré';
        }

        $baseTime = 24; // heures

        // Ajuster selon les tentatives précédentes
        if ($package->delivery_attempts > 0) {
            $baseTime += ($package->delivery_attempts * 12);
        }

        // Ajuster selon la délégation
        if ($package->delegationTo) {
            // Logique personnalisée selon les zones
            $baseTime += rand(0, 12);
        }

        return $baseTime . ' heures';
    }

    // ==================== NOUVELLES MÉTHODES POUR SCAN PAR LOT ====================

    /**
     * Page de scan par lot pour les pickups
     */
    public function batchPickup()
    {
        // Test temporaire pour debug
        try {
            return view('deliverer.packages.batch-pickup');
        } catch (\Exception $e) {
            return response("Debug Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine(), 500);
        }
    }

    /**
     * Vérifier si un package peut être accepté en pickup
     */
    public function checkPickup(Request $request)
    {
        $code = strtoupper(trim($request->input('code', '')));

        if (empty($code)) {
            return response()->json([
                'success' => false,
                'message' => 'Code vide ou invalide'
            ]);
        }

        // Chercher le package
        $package = $this->findPackageByCode($code);

        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Colis introuvable'
            ]);
        }

        // Vérifier si le package peut être accepté
        if ($package->status !== 'AVAILABLE') {
            return response()->json([
                'success' => false,
                'message' => "Ce colis n'est pas disponible pour pickup (statut: {$package->status})"
            ]);
        }

        // Vérifier la géolocalisation si nécessaire
        if ($package->pickup_delegation_id && $package->pickup_delegation_id !== auth()->user()->delegation_id) {
            return response()->json([
                'success' => false,
                'message' => 'Ce colis est dans une autre délégation'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Colis disponible pour pickup',
            'package' => [
                'id' => $package->id,
                'code' => $package->package_code,
                'destination' => $package->delegationTo->name ?? 'N/A',
                'cod_amount' => $package->cod_amount,
                'content' => $package->content_description,
                'pickup_address' => $package->pickup_data['address'] ?? 'N/A'
            ]
        ]);
    }

    /**
     * Accepter plusieurs packages en lot
     */
    public function batchAccept(Request $request)
    {
        $codes = $request->input('codes', []);

        if (empty($codes)) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun code fourni'
            ]);
        }

        $acceptedCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($codes as $code) {
                $package = $this->findPackageByCode($code);

                if (!$package) {
                    $errors[] = "Code {$code}: Colis introuvable";
                    continue;
                }

                if ($package->status !== 'AVAILABLE') {
                    $errors[] = "Code {$code}: Colis non disponible";
                    continue;
                }

                // Accepter le package
                $package->status = 'ACCEPTED';
                $package->assigned_deliverer_id = auth()->id();
                $package->accepted_at = now();
                $package->save();

                // Log de l'action
                $this->actionLogService->logAction(
                    auth()->user(),
                    'PICKUP_ACCEPTED',
                    $package,
                    "Pickup accepté via scan par lot",
                    ['batch_operation' => true]
                );

                $acceptedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Opération terminée avec succès",
                'accepted_count' => $acceptedCount,
                'errors' => $errors,
                'total_processed' => count($codes)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement: ' . $e->getMessage()
            ]);
        }
    }

    // ==================== MÉTHODES POUR SCAN EN LOT AVEC ACTIONS ====================

    /**
     * Validation en lot pour Pickup (Available/Accepted → Picked Up)
     */
    public function bulkPickup(Request $request)
    {
        $validated = $request->validate([
            'package_ids' => 'required|array|min:1|max:50',
            'package_ids.*' => 'required'
        ]);

        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $delivererId = Auth::id();
        $successCount = 0;
        $errorCount = 0;
        $results = [];

        DB::beginTransaction();

        try {
            foreach ($validated['package_ids'] as $packageId) {
                // Chercher le package
                $package = Package::where(function($query) use ($packageId) {
                    $query->where('id', $packageId)
                          ->orWhere('package_code', $packageId);
                })->first();

                if (!$package) {
                    $results[] = [
                        'package_id' => $packageId,
                        'success' => false,
                        'message' => 'Colis introuvable'
                    ];
                    $errorCount++;
                    continue;
                }

                // Vérifier le statut pour action Pickup
                if (!in_array($package->status, ['AVAILABLE', 'ACCEPTED'])) {
                    $results[] = [
                        'package_id' => $packageId,
                        'package_code' => $package->package_code,
                        'success' => false,
                        'message' => "Statut invalide: {$package->status} (requis: Available ou Accepted)"
                    ];
                    $errorCount++;
                    continue;
                }

                // Si Available, assigner d'abord au livreur
                if ($package->status === 'AVAILABLE') {
                    $package->update([
                        'assigned_deliverer_id' => $delivererId,
                        'assigned_at' => now(),
                        'status' => 'ACCEPTED'
                    ]);

                    $this->updatePackageStatus($package, 'ACCEPTED', 'Assigné via scan en lot pour pickup');
                }

                // Vérifier que le package est assigné à ce livreur
                if ($package->assigned_deliverer_id !== $delivererId) {
                    $results[] = [
                        'package_id' => $packageId,
                        'package_code' => $package->package_code,
                        'success' => false,
                        'message' => 'Colis assigné à un autre livreur'
                    ];
                    $errorCount++;
                    continue;
                }

                // Marquer comme Picked Up
                $package->update([
                    'status' => 'PICKED_UP',
                    'picked_up_at' => now(),
                    'pickup_notes' => 'Collecté via scan en lot'
                ]);

                $this->updatePackageStatus($package, 'PICKED_UP', 'Collecté via scan en lot');

                $this->logAction('BULK_PICKUP', 'Package', $package->id,
                               'ACCEPTED', 'PICKED_UP', [
                    'package_code' => $package->package_code,
                    'deliverer_id' => $delivererId,
                    'bulk_operation' => true
                ]);

                $results[] = [
                    'package_id' => $packageId,
                    'package_code' => $package->package_code,
                    'success' => true,
                    'message' => 'Pickup réussi'
                ];
                $successCount++;
            }

            if ($successCount > 0) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "Pickup en lot terminé: {$successCount} succès, {$errorCount} échecs",
                    'results' => $results,
                    'summary' => [
                        'total' => count($validated['package_ids']),
                        'success' => $successCount,
                        'errors' => $errorCount
                    ]
                ]);
            } else {
                DB::rollback();

                return response()->json([
                    'success' => false,
                    'message' => 'Aucun colis n\'a pu être traité',
                    'results' => $results
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Erreur bulk pickup', [
                'deliverer_id' => $delivererId,
                'package_ids' => $validated['package_ids'],
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du pickup en lot: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Changement de livreur en lot (Available/Picked Up/Accepted)
     */
    public function bulkChangeDeliverer(Request $request)
    {
        $validated = $request->validate([
            'package_ids' => 'required|array|min:1|max:50',
            'package_ids.*' => 'required',
            'new_deliverer_id' => 'nullable|exists:users,id' // Optionnel, si null = retour disponible
        ]);

        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $delivererId = Auth::id();
        $newDelivererId = $validated['new_deliverer_id'] ?? null;
        $successCount = 0;
        $errorCount = 0;
        $results = [];

        // Vérifier le nouveau livreur si spécifié
        if ($newDelivererId) {
            $newDeliverer = User::where('id', $newDelivererId)
                                ->where('role', 'DELIVERER')
                                ->where('account_status', 'ACTIVE')
                                ->first();

            if (!$newDeliverer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nouveau livreur invalide ou inactif'
                ], 400);
            }
        }

        DB::beginTransaction();

        try {
            foreach ($validated['package_ids'] as $packageId) {
                // Chercher le package
                $package = Package::where(function($query) use ($packageId) {
                    $query->where('id', $packageId)
                          ->orWhere('package_code', $packageId);
                })->first();

                if (!$package) {
                    $results[] = [
                        'package_id' => $packageId,
                        'success' => false,
                        'message' => 'Colis introuvable'
                    ];
                    $errorCount++;
                    continue;
                }

                // Vérifier le statut pour changement de livreur
                if (!in_array($package->status, ['AVAILABLE', 'PICKED_UP', 'ACCEPTED'])) {
                    $results[] = [
                        'package_id' => $packageId,
                        'package_code' => $package->package_code,
                        'success' => false,
                        'message' => "Statut invalide: {$package->status} (requis: Available, Picked Up ou Accepted)"
                    ];
                    $errorCount++;
                    continue;
                }

                // Vérifier les autorisations
                if ($package->assigned_deliverer_id && $package->assigned_deliverer_id !== $delivererId) {
                    $results[] = [
                        'package_id' => $packageId,
                        'package_code' => $package->package_code,
                        'success' => false,
                        'message' => 'Colis assigné à un autre livreur'
                    ];
                    $errorCount++;
                    continue;
                }

                $oldStatus = $package->status;
                $oldDelivererId = $package->assigned_deliverer_id;

                // Effectuer le changement
                if ($newDelivererId) {
                    // Transférer à un nouveau livreur
                    $package->update([
                        'assigned_deliverer_id' => $newDelivererId,
                        'assigned_at' => now(),
                        'status' => 'ACCEPTED' // Reset au statut ACCEPTED pour le nouveau livreur
                    ]);

                    $newDelivererName = User::find($newDelivererId)->name ?? 'Inconnu';
                    $statusMessage = "Transféré au livreur: {$newDelivererName}";
                } else {
                    // Remettre en disponible
                    $package->update([
                        'assigned_deliverer_id' => null,
                        'assigned_at' => null,
                        'status' => 'AVAILABLE'
                    ]);

                    $statusMessage = "Remis en disponible - Libéré par le livreur";
                }

                $this->updatePackageStatus($package, $package->status, $statusMessage);

                $this->logAction('BULK_CHANGE_DELIVERER', 'Package', $package->id,
                               $oldStatus, $package->status, [
                    'package_code' => $package->package_code,
                    'old_deliverer_id' => $oldDelivererId,
                    'new_deliverer_id' => $newDelivererId,
                    'action_by_deliverer_id' => $delivererId,
                    'bulk_operation' => true
                ]);

                $results[] = [
                    'package_id' => $packageId,
                    'package_code' => $package->package_code,
                    'success' => true,
                    'message' => $newDelivererId ? 'Transféré avec succès' : 'Remis en disponible'
                ];
                $successCount++;
            }

            if ($successCount > 0) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "Changement de livreur terminé: {$successCount} succès, {$errorCount} échecs",
                    'results' => $results,
                    'summary' => [
                        'total' => count($validated['package_ids']),
                        'success' => $successCount,
                        'errors' => $errorCount,
                        'action' => $newDelivererId ? 'transfer' : 'release'
                    ]
                ]);
            } else {
                DB::rollback();

                return response()->json([
                    'success' => false,
                    'message' => 'Aucun colis n\'a pu être traité',
                    'results' => $results
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Erreur bulk change deliverer', [
                'deliverer_id' => $delivererId,
                'new_deliverer_id' => $newDelivererId,
                'package_ids' => $validated['package_ids'],
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Génère et affiche le reçu de livraison
     */
    public function deliveryReceipt(Package $package)
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            abort(403, 'Accès réservé aux livreurs.');
        }

        // Vérifier que le colis est livré et assigné au livreur connecté
        if ($package->status !== 'DELIVERED' || $package->assigned_deliverer_id !== Auth::id()) {
            abort(404, 'Reçu de livraison non disponible pour ce colis.');
        }

        // Décoder les données JSON si nécessaire
        $recipientData = is_string($package->recipient_data)
            ? json_decode($package->recipient_data, true)
            : $package->recipient_data;

        $senderData = is_string($package->sender_data)
            ? json_decode($package->sender_data, true)
            : $package->sender_data;

        return view('deliverer.receipts.delivery-receipt', compact('package', 'recipientData', 'senderData'));
    }

    /**
     * MÉTHODE TEMPORAIRE - Tester le reçu sans authentification
     * À SUPPRIMER après le test
     */
    public function testDeliveryReceipt(Package $package)
    {
        // Décoder les données JSON si nécessaire
        $recipientData = is_string($package->recipient_data)
            ? json_decode($package->recipient_data, true)
            : $package->recipient_data;

        $senderData = is_string($package->sender_data)
            ? json_decode($package->sender_data, true)
            : $package->sender_data;

        return view('deliverer.receipts.delivery-receipt', compact('package', 'recipientData', 'senderData'));
    }

    /**
     * Reçu de livraison simplifié sans vérifications strictes d'authentification
     */
    public function simpleDeliveryReceipt(Package $package)
    {
        // Vérifier que le colis est au moins livré
        if ($package->status !== 'DELIVERED') {
            return redirect()->back()->with('error', 'Ce colis n\'a pas encore été livré.');
        }

        // Décoder les données JSON si nécessaire
        $recipientData = is_string($package->recipient_data)
            ? json_decode($package->recipient_data, true)
            : $package->recipient_data;

        $senderData = is_string($package->sender_data)
            ? json_decode($package->sender_data, true)
            : $package->sender_data;

        return view('deliverer.receipts.delivery-receipt', compact('package', 'recipientData', 'senderData'));
    }

    /**
     * Notifier le client du retour du colis
     */
    private function notifyClientOfReturn(Package $package, string $returnReason, string $returnReasonDetails)
    {
        $message = match($returnReason) {
            'CANCELLED_BY_CLIENT' => "Votre colis {$package->package_code} a été retourné suite à votre annulation.",
            'MAX_ATTEMPTS_REACHED' => "Votre colis {$package->package_code} a été retourné après 3 tentatives de livraison échouées.",
            'VERIFIED_FOR_RETURN' => "Votre colis {$package->package_code} a été retourné à l'expéditeur.",
            default => "Votre colis {$package->package_code} a été retourné à l'expéditeur."
        };

        // Ici, vous pouvez ajouter la logique de notification
        // Par exemple: envoyer un email, SMS, ou notification push
        Log::info('Notification client retour colis', [
            'package_id' => $package->id,
            'client_id' => $package->sender_id,
            'return_reason' => $returnReason,
            'message' => $message
        ]);
    }

    /**
     * Annuler un colis par le client (via commercial ou superviseur)
     */
    public function cancelByClient(Package $package, Request $request)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
            'cancelled_by_role' => 'required|in:CLIENT,COMMERCIAL,SUPERVISOR'
        ]);

        if (!in_array($package->status, ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP', 'UNAVAILABLE'])) {
            return response()->json([
                'success' => false,
                'message' => 'Ce colis ne peut plus être annulé car il est déjà livré ou retourné.'
            ], 400);
        }

        try {
            return DB::transaction(function () use ($package, $validated) {
                $package->update([
                    'status' => 'CANCELLED',
                    'cancelled_by_client' => true,
                    'cancellation_reason' => $validated['cancellation_reason'],
                    'cancelled_at' => now(),
                    'cancelled_by' => Auth::id()
                ]);

                $this->updatePackageStatus($package, 'CANCELLED',
                    "Colis annulé par le client - Motif: {$validated['cancellation_reason']}");

                $this->logAction('PACKAGE_CANCELLED_BY_CLIENT', 'Package', $package->id,
                               $package->getOriginal('status'), 'CANCELLED', [
                    'cancellation_reason' => $validated['cancellation_reason'],
                    'cancelled_by_role' => $validated['cancelled_by_role'],
                    'cancelled_by' => Auth::id()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Colis {$package->package_code} annulé avec succès."
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Erreur annulation colis client', [
                'package_id' => $package->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation du colis.'
            ], 500);
        }
    }
}