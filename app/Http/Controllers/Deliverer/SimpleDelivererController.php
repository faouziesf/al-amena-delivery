<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PickupRequest;
use App\Models\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SimpleDelivererController extends Controller
{
    /**
     * Dashboard simplifié - Vue principale "Ma Tournée" (Legacy)
     */
    public function dashboard()
    {
        return view('deliverer.simple-dashboard');
    }

    /**
     * PWA Optimisée: Run Sheet - Centre de contrôle principal
     */
    public function runSheet()
    {
        return view('deliverer.run-sheet');
    }

    /**
     * Menu Principal Optimisé
     */
    public function menu()
    {
        $user = Auth::user();
        
        // Stats rapides
        $activeCount = Package::where('assigned_deliverer_id', $user->id)
            ->whereNotIn('status', ['DELIVERED', 'CANCELLED', 'RETURNED'])
            ->count();
            
        $todayCount = Package::where('assigned_deliverer_id', $user->id)
            ->whereDate('delivered_at', today())
            ->count();
            
        $balance = UserWallet::where('user_id', $user->id)->value('balance') ?? 0;
        
        return view('deliverer.menu-modern', compact('activeCount', 'todayCount', 'balance'));
    }

    /**
     * Ma Tournée - MVC Direct (sans APIs)
     */
    public function tournee()
    {
        $user = Auth::user();
        
        // Récupérer packages (livraisons)
        $packages = Package::where('assigned_deliverer_id', $user->id)
            ->whereIn('status', ['AVAILABLE', 'ACCEPTED', 'PICKED_UP'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Récupérer pickups (ramassages) - Filtrer par gouvernorat du livreur
        $pickups = PickupRequest::where('assigned_deliverer_id', $user->id)
            ->whereIn('status', ['assigned', 'pending'])
            ->forDelivererGovernorate($user)
            ->orderBy('requested_pickup_date', 'asc')
            ->get();
        
        // Fusionner en une seule liste
        $tasks = collect();
        
        foreach ($packages as $pkg) {
            $tasks->push([
                'id' => $pkg->id,
                'type' => 'livraison',
                'tracking_number' => $pkg->tracking_number,
                'package_code' => $pkg->package_code,
                'recipient_name' => $pkg->recipient_name,
                'recipient_address' => $pkg->recipient_address,
                'recipient_phone' => $pkg->recipient_phone,
                'cod_amount' => $pkg->cod_amount ?? 0,
                'status' => $pkg->status,
                'est_echange' => $pkg->est_echange ?? false,
                'date' => $pkg->created_at
            ]);
        }
        
        foreach ($pickups as $pickup) {
            $tasks->push([
                'id' => 'P' . $pickup->id,
                'type' => 'pickup',
                'tracking_number' => 'PICKUP-' . $pickup->id,
                'package_code' => $pickup->pickup_code ?? 'P' . $pickup->id,
                'recipient_name' => $pickup->pickup_contact_name ?? 'Client',
                'recipient_address' => $pickup->pickup_address,
                'recipient_phone' => $pickup->pickup_phone,
                'cod_amount' => 0,
                'status' => $pickup->status,
                'est_echange' => false,
                'date' => $pickup->requested_pickup_date ?? $pickup->created_at,
                'pickup_id' => $pickup->id
            ]);
        }
        
        // Stats
        $stats = [
            'total' => $tasks->count(),
            'livraisons' => $packages->count(),
            'pickups' => $pickups->count(),
            'completed' => $packages->where('status', 'DELIVERED')->count()
        ];
        
        return view('deliverer.tournee-direct', compact('tasks', 'stats'));
    }

    /**
     * PWA Optimisée: Détail d'une tâche
     */
    public function taskDetail(Package $package)
    {
        $user = Auth::user();

        // Vérifier que le package est assigné au livreur
        if ($package->assigned_deliverer_id !== $user->id) {
            abort(403, 'Tâche non assignée à vous');
        }

        return view('deliverer.task-detail', compact('package'));
    }

    /**
     * PWA Optimisée: Détail d'une tâche par ID personnalisé
     */
    public function taskByCustomId($taskId)
    {
        $user = Auth::user();

        // Pour les IDs personnalisés comme "task_pickup1", créer des tâches de démonstration
        if (str_starts_with($taskId, 'task_pickup')) {
            // Tâche de collecte de démonstration
            $mockTask = (object) [
                'id' => $taskId,
                'type' => 'pickup',
                'code' => strtoupper($taskId),
                'status' => 'AVAILABLE',
                'client_name' => 'Al-Amena Express',
                'recipient_name' => 'Démonstration Pickup',
                'recipient_phone' => '+216 20 123 456',
                'recipient_address' => '15 Avenue Habib Bourguiba, Tunis',
                'recipient_city' => 'Tunis',
                'cod_amount' => 0,
                'created_at' => now(),
                'pickup_address' => '25 Rue de la République, Ariana',
                'packages_count' => 3,
                'total_value' => 250.500,
                'pickup_notes' => 'Collecte de 3 colis. Contactez le responsable avant l\'arrivée.'
            ];
        } elseif (str_starts_with($taskId, 'task_delivery')) {
            // Tâche de livraison de démonstration
            $mockTask = (object) [
                'id' => $taskId,
                'type' => 'delivery',
                'code' => strtoupper($taskId),
                'status' => 'PICKED_UP',
                'client_name' => 'Boutique Mode',
                'recipient_name' => 'Ahmed Ben Ali',
                'recipient_phone' => '+216 50 789 123',
                'recipient_address' => '42 Avenue Mohamed V, Sfax',
                'recipient_city' => 'Sfax',
                'cod_amount' => 125.750,
                'created_at' => now(),
                'content' => 'Vêtements',
                'delivery_notes' => 'Appartement au 3ème étage, sonnette de droite.'
            ];
        } else {
            abort(404, 'Tâche non trouvée');
        }

        return view('deliverer.task-detail-custom', compact('mockTask'));
    }

    /**
     * PWA Optimisée: Capture de signature
     */
    public function signatureCapture(Package $package)
    {
        $user = Auth::user();

        // Vérifier que le package est assigné au livreur
        if ($package->assigned_deliverer_id !== $user->id) {
            abort(403, 'Livraison non assignée à vous');
        }

        // Vérifier que le package est livré
        if ($package->status !== 'DELIVERED') {
            return redirect()->route('deliverer.task.detail', $package)
                ->with('error', 'La livraison doit être confirmée avant la signature');
        }

        return view('deliverer.signature-capture', compact('package'));
    }

    /**
     * PWA Optimisée: Wallet optimisé
     */
    public function walletOptimized()
    {
        $user = Auth::user();

        // Données du wallet
        $wallet = UserWallet::where('user_id', $user->id)->first();
        $walletData = [
            'balance' => $wallet ? $wallet->balance : 0,
            'available_balance' => $wallet ? $wallet->available_balance : 0
        ];

        // Statistiques du wallet
        $walletStats = [
            'collected_today' => $this->getTodayCollectedAmount(),
            'pending_cod' => $this->getPendingCodAmount(),
            'transactions_count' => $this->getMonthlyTransactionCount()
        ];

        // Transactions récentes
        $recentTransactions = $this->getRecentTransactions();

        // Dernier vidage
        $lastEmptying = $this->getLastEmptying();

        return view('deliverer.wallet-optimized', compact(
            'walletData', 'walletStats', 'recentTransactions', 'lastEmptying'
        ));
    }

    /**
     * PWA Optimisée: Scanner les collectes
     */
    public function scanPickups()
    {
        return view('deliverer.pickups.scan');
    }

    /**
     * Interface scanner avec caméra intégrée
     */
    public function scanCamera()
    {
        return view('deliverer.scan-camera');
    }

    /**
     * Traitement du scan QR
     */
    public function processScan(Request $request)
    {
        return $this->scanQR($request);
    }

    /**
     * Scan Direct - MVC (POST form) - Support Single & Batch
     */
    public function scanSubmit(Request $request)
    {
        $user = Auth::user();
        
        // Mode vérification uniquement (pour scan temps réel)
        if ($request->verify_only === true) {
            return $this->verifyCodeOnly($request, $user);
        }
        
        // Support pour scan multiple (batch)
        if ($request->has('batch') && $request->batch === true) {
            return $this->scanBatch($request, $user);
        }
        
        // Scan simple (single)
        $request->validate([
            'code' => 'required|string'
        ]);

        $code = $this->normalizeCode(trim($request->code));

        // Rechercher le colis
        $package = $this->findPackageByCode($code);

        if ($package) {
            // Auto-assigner ou réassigner au livreur qui scanne
            if (!$package->assigned_deliverer_id || $package->assigned_deliverer_id !== $user->id) {
                $package->update([
                    'assigned_deliverer_id' => $user->id,
                    'assigned_at' => now(),
                    'status' => $package->status === 'CREATED' ? 'ACCEPTED' : $package->status
                ]);
            }
            
            // PLUS DE VÉRIFICATION - Le livreur peut scanner tous les colis

            // Sauvegarder en session (historique)
            $lastScans = session('last_scans', []);
            array_unshift($lastScans, [
                'code' => $code,
                'package_id' => $package->id,
                'time' => now()->format('H:i')
            ]);
            session(['last_scans' => array_slice($lastScans, 0, 5)]);

            // Rediriger vers détail
            return redirect()->route('deliverer.task.detail', $package)
                ->with('success', 'Colis trouvé et assigné !');
        }

        // Rechercher pickup
        $pickup = PickupRequest::where('pickup_code', $code)->first();
        if ($pickup) {
            return redirect()->route('deliverer.tournee')
                ->with('success', 'Pickup trouvé !');
        }

        return redirect()->route('deliverer.scan.simple')
            ->with('error', 'Code non trouvé: ' . $code);
    }

    /**
     * Détail Pickup
     */
    public function pickupDetail($id)
    {
        $user = Auth::user();
        $pickup = PickupRequest::findOrFail($id);
        
        // Vérifier assignation
        if ($pickup->assigned_deliverer_id !== $user->id) {
            abort(403, 'Ce ramassage n\'est pas assigné à vous');
        }
        
        return view('deliverer.pickup-detail', compact('pickup'));
    }

    /**
     * Marquer pickup comme collecté
     */
    public function markPickupCollect($id)
    {
        $user = Auth::user();
        $pickup = PickupRequest::findOrFail($id);
        
        // Vérifier assignation
        if ($pickup->assigned_deliverer_id !== $user->id) {
            return redirect()->back()
                ->with('error', 'Ce ramassage n\'est pas assigné à vous');
        }
        
        $pickup->update([
            'status' => 'picked_up',
            'picked_up_at' => now()
        ]);
        
        return redirect()->route('deliverer.tournee')
            ->with('success', 'Ramassage marqué comme effectué !');
    }

    /**
     * Interface de scan multiple avec données OPTIMISÉES pour vérification locale
     */
    public function multiScanner()
    {
        $user = Auth::user();
        
        // CORRECTION: Charger TOUS les colis actifs (PAS de vérification d'assignation)
        // Le livreur peut scanner n'importe quel colis
        $packages = Package::whereNotIn('status', ['DELIVERED', 'CANCELLED', 'RETURNED', 'PAID']) // Exclure uniquement les terminés
            ->select('id', 'package_code', 'status', 'assigned_deliverer_id') // Garder assigned_deliverer_id pour info
            ->get()
            ->map(function($pkg) use ($user) {
                // CORRECTION: Nettoyer le code (enlever espaces, tirets potentiels)
                $cleanCode = strtoupper(trim(str_replace([' ', '-', '_'], '', $pkg->package_code)));
                $originalCode = strtoupper(trim($pkg->package_code));
                
                return [
                    'c' => $originalCode, // Code original
                    'c2' => $cleanCode, // Code nettoyé (pour recherche alternative)
                    's' => $pkg->status,
                    'p' => in_array($pkg->status, ['AVAILABLE', 'ACCEPTED', 'CREATED', 'VERIFIED']) ? 1 : 0,
                    'd' => in_array($pkg->status, ['PICKED_UP', 'OUT_FOR_DELIVERY']) ? 1 : 0,
                    'id' => $pkg->id, // Pour debug
                    'assigned' => $pkg->assigned_deliverer_id === $user->id ? 1 : 0 // Info: assigné ou non
                ];
            });
        
        // Pickups (optimisé)
        $pickups = PickupRequest::where('assigned_deliverer_id', $user->id)
            ->whereIn('status', ['assigned', 'pending'])
            ->select('id', 'pickup_code')
            ->get()
            ->map(function($pickup) {
                return [
                    'c' => strtoupper($pickup->pickup_code ?? 'P' . $pickup->id),
                    's' => 'assigned',
                    'p' => 1,
                    'd' => 0
                ];
            });
        
        // Fusionner
        $allPackages = $packages->concat($pickups);
        
        // OPTIMISATION 4: Passer un flag pour indiquer qu'on utilise le format court
        return view('deliverer.multi-scanner-production', [
            'packages' => $allPackages,
            'packagesCount' => $allPackages->count()
        ]);
    }

    /**
     * Traitement du scan multiple
     */
    public function processMultiScan(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string'
        ]);

        $user = Auth::user();
        $code = $this->normalizeCode($request->qr_code);

        try {
            // Rechercher le colis par code
            $package = $this->findPackageByCode($code);

            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code invalide ou colis non trouvé'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Colis trouvé',
                'package' => [
                    'id' => $package->id,
                    'package_code' => $package->tracking_number ?? $package->package_code,
                    'recipient_name' => $package->recipient_name ?? 'N/A',
                    'recipient_address' => $package->recipient_address ?? 'N/A',
                    'cod_amount' => $package->cod_amount ?? 0,
                    'status' => $package->status
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validation de la liste des colis scannés (MULTI SCAN)
     */
    public function validateMultiScan(Request $request)
    {
        $validated = $request->validate([
            'codes' => 'required|array|min:1',
            'codes.*' => 'required|string',
            'action' => 'required|in:pickup,delivery'
        ]);

        $user = Auth::user();
        
        // Gérer les codes (peuvent être array ou string JSON)
        $codes = $request->codes;
        if (is_string($codes)) {
            $codes = json_decode($codes, true);
        }
        if (!is_array($codes)) {
            $codes = [];
        }
        
        $action = $request->action;

        if (empty($codes)) {
            return redirect()->back()->with('error', 'Aucun code à traiter');
        }

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($codes as $code) {
                $cleanCode = $this->normalizeCode(trim($code));
                $package = $this->findPackageByCode($cleanCode);

                if (!$package) {
                    $errorCount++;
                    $errors[] = "$cleanCode : Non trouvé";
                    continue;
                }

                // Auto-assigner au livreur
                if (!$package->assigned_deliverer_id || $package->assigned_deliverer_id !== $user->id) {
                    $package->assigned_deliverer_id = $user->id;
                    $package->assigned_at = now();
                }

                // Appliquer l'action
                if ($action === 'pickup') {
                    // Ramassage : CREATED, AVAILABLE → PICKED_UP
                    if (in_array($package->status, ['CREATED', 'AVAILABLE'])) {
                        $package->status = 'PICKED_UP';
                        // Définir picked_up_at seulement s'il n'est pas déjà défini
                        if (!$package->picked_up_at) {
                            $package->picked_up_at = now();
                        }
                        $package->save();
                        $successCount++;
                    } else {
                        $errorCount++;
                        $errors[] = "$cleanCode : Statut incompatible ({$package->status})";
                    }
                } else {
                    // Livraison : AVAILABLE, CREATED, AT_DEPOT, OUT_FOR_DELIVERY, PICKED_UP → OUT_FOR_DELIVERY
                    if (in_array($package->status, ['AVAILABLE', 'CREATED', 'AT_DEPOT', 'OUT_FOR_DELIVERY', 'PICKED_UP'])) {
                        $package->status = 'OUT_FOR_DELIVERY';
                        $package->save();
                        $successCount++;
                    } else {
                        $errorCount++;
                        $errors[] = "$cleanCode : Statut incompatible ({$package->status})";
                    }
                }
            }

            DB::commit();

            $actionLabel = $action === 'pickup' ? 'ramassés' : 'en livraison';
            $message = "✅ $successCount colis $actionLabel";
            
            if ($errorCount > 0) {
                $message .= " | ⚠️ $errorCount erreurs";
                if (count($errors) <= 3) {
                    $message .= " : " . implode(', ', $errors);
                }
            }

            return redirect()->route('deliverer.tournee')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur validateMultiScan:', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Valider le statut du colis selon l'action
     */
    private function validatePackageStatus(Package $package, string $action): array
    {
        if ($action === 'pickup') {
            // Pour pickup: accepter AVAILABLE ou CREATED
            $validStatuses = ['AVAILABLE', 'CREATED'];
            if (!in_array($package->status, $validStatuses)) {
                return [
                    'valid' => false,
                    'message' => 'Statut erroné. Pour pickup, le colis doit être AVAILABLE ou CREATED (statut actuel: ' . $package->status . ')'
                ];
            }
        } else {
            // Pour livraison: accepter tous sauf DELIVERED et PAID
            $invalidStatuses = ['DELIVERED', 'PAID'];
            if (in_array($package->status, $invalidStatuses)) {
                return [
                    'valid' => false,
                    'message' => 'Statut erroné. Ce colis est déjà livré (statut: ' . $package->status . ')'
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * Normaliser le code (QR ou code-barres)
     */
    private function normalizeCode(string $code): string
    {
        $code = trim($code);
        
        // Si c'est une URL de tracking, extraire le code
        if (preg_match('/\/track\/(.+)$/', $code, $matches)) {
            return strtoupper($matches[1]);
        }
        
        return strtoupper($code);
    }

    /**
     * Trouver un colis par code (tracking_number ou package_code)
     * Support multiple formats: QR codes, barcodes, avec/sans préfixe
     * COPIÉ EXACTEMENT du chef de dépôt (DepotScanController) - FONCTIONNE À 100%
     */
    private function findPackageByCode(string $code): ?Package
    {
        $originalCode = trim($code);
        $cleanCode = strtoupper($originalCode);
        
        // Si c'est une URL complète (QR code), extraire le code
        if (preg_match('/\/track\/(.+)$/i', $cleanCode, $matches)) {
            $cleanCode = strtoupper($matches[1]);
        }
        
        // RECHERCHE INTELLIGENTE : Essayer plusieurs variantes du code (EXACTEMENT comme chef dépôt)
        $searchVariants = [
            $cleanCode,                                          // Code original en majuscules
            str_replace('_', '', $cleanCode),                    // Sans underscore
            str_replace('-', '', $cleanCode),                    // Sans tiret
            str_replace(['_', '-', ' '], '', $cleanCode),       // Nettoyé complètement
            strtolower($cleanCode),                              // Minuscules
            $originalCode,                                       // Code original (casse préservée)
        ];
        
        // Supprimer les doublons
        $searchVariants = array_unique($searchVariants);
        
        // Statuts ACCEPTÉS pour scan livreur (MÊME LOGIQUE que chef dépôt)
        $acceptedStatuses = ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP', 'OUT_FOR_DELIVERY', 'UNAVAILABLE', 'AT_DEPOT', 'VERIFIED'];
        
        // Rechercher avec TOUTES les variantes (EXACTEMENT comme chef dépôt)
        foreach ($searchVariants as $variant) {
            $package = DB::table('packages')
                ->where('package_code', $variant)
                ->whereIn('status', $acceptedStatuses)
                ->select('id', 'package_code', 'status', 'tracking_number', 'assigned_deliverer_id', 'cod_amount')
                ->first();
            
            if ($package) {
                // Convertir en modèle Eloquent
                return Package::find($package->id);
            }
            
            // Chercher aussi par tracking_number
            $package = DB::table('packages')
                ->where('tracking_number', $variant)
                ->whereIn('status', $acceptedStatuses)
                ->select('id', 'package_code', 'status', 'tracking_number', 'assigned_deliverer_id', 'cod_amount')
                ->first();
            
            if ($package) {
                // Convertir en modèle Eloquent
                return Package::find($package->id);
            }
        }
        
        // Si toujours pas trouvé, essayer une recherche LIKE (EXACTEMENT comme chef dépôt)
        $cleanForLike = str_replace(['_', '-', ' '], '', $cleanCode);
        if (strlen($cleanForLike) >= 6) {
            $package = DB::table('packages')
                ->whereRaw('REPLACE(REPLACE(REPLACE(UPPER(package_code), "_", ""), "-", ""), " ", "") = ?', [$cleanForLike])
                ->whereIn('status', $acceptedStatuses)
                ->select('id', 'package_code', 'status', 'tracking_number', 'assigned_deliverer_id', 'cod_amount')
                ->first();
            
            if ($package) {
                return Package::find($package->id);
            }
            
            // Essayer aussi avec tracking_number
            $package = DB::table('packages')
                ->whereRaw('REPLACE(REPLACE(REPLACE(UPPER(tracking_number), "_", ""), "-", ""), " ", "") = ?', [$cleanForLike])
                ->whereIn('status', $acceptedStatuses)
                ->select('id', 'package_code', 'status', 'tracking_number', 'assigned_deliverer_id', 'cod_amount')
                ->first();
            
            if ($package) {
                return Package::find($package->id);
            }
        }
        
        return null;
    }

    /**
     * Vérifier un code sans l'enregistrer (pour feedback temps réel)
     */
    private function verifyCodeOnly(Request $request, $user)
    {
        $request->validate([
            'code' => 'required|string',
            'action' => 'nullable|in:pickup,delivering'
        ]);

        $code = $this->normalizeCode(trim($request->code));
        $action = $request->action ?? 'pickup';
        
        // Chercher le colis
        $package = $this->findPackageByCode($code);

        if (!$package) {
            return response()->json([
                'valid' => false,
                'message' => 'Code invalide - Colis introuvable'
            ]);
        }

        // PLUS DE VÉRIFICATION - Le livreur peut vérifier tous les colis
        // Auto-assigner si nécessaire
        if (!$package->assigned_deliverer_id || $package->assigned_deliverer_id != $user->id) {
            $package->update([
                'assigned_deliverer_id' => $user->id,
                'assigned_at' => now()
            ]);
        }

        // Valider le statut
        $statusCheck = $this->validatePackageStatus($package, $action);
        if (!$statusCheck['valid']) {
            return response()->json([
                'valid' => false,
                'message' => $statusCheck['message']
            ]);
        }

        // Tout est OK
        return response()->json([
            'valid' => true,
            'message' => 'Colis valide',
            'package' => [
                'id' => $package->id,
                'tracking_number' => $package->tracking_number,
                'recipient_name' => $package->recipient_name,
                'status' => $package->status
            ]
        ]);
    }

    /**
     * Scan Batch - Pour scanner multiple avec support d'actions
     */
    private function scanBatch(Request $request, $user)
    {
        \Log::info('scanBatch - Début', [
            'user_id' => $user->id,
            'request_data' => $request->all()
        ]);

        try {
            $request->validate([
                'codes' => 'required|array|min:1',
                'codes.*' => 'required|string',
                'action' => 'nullable|in:pickup,delivering'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation échouée', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);
        }

        $codes = $request->codes;
        $action = $request->action ?? 'pickup';
        $results = [];
        $successCount = 0;
        $errorCount = 0;
        $invalidStatusCount = 0;

        \Log::info('scanBatch - Traitement', [
            'codes_count' => count($codes),
            'action' => $action
        ]);

        DB::beginTransaction();

        try {
            foreach ($codes as $code) {
                $cleanCode = $this->normalizeCode(trim($code));
                $package = $this->findPackageByCode($cleanCode);

                if (!$package) {
                    $results[] = [
                        'code' => $cleanCode,
                        'status' => 'error',
                        'error_type' => 'not_found',
                        'message' => 'Code non trouvé'
                    ];
                    $errorCount++;
                    continue;
                }

                // Auto-assigner ou réassigner au livreur qui scanne
                if (!$package->assigned_deliverer_id || $package->assigned_deliverer_id !== $user->id) {
                    $package->update([
                        'assigned_deliverer_id' => $user->id,
                        'assigned_at' => now()
                    ]);
                }

                // PLUS DE VÉRIFICATION - Le livreur peut scanner tous les colis

                // Validation du statut selon l'action
                $statusValidation = $this->validateStatusForAction($package->status, $action);
                if (!$statusValidation['valid']) {
                    $results[] = [
                        'code' => $cleanCode,
                        'status' => 'error',
                        'error_type' => 'invalid_status',
                        'message' => $statusValidation['message'],
                        'current_status' => $package->status
                    ];
                    $invalidStatusCount++;
                    continue;
                }

                // Appliquer l'action
                $updateData = $this->getStatusUpdateForAction($action);
                $package->update($updateData);

                $results[] = [
                    'code' => $cleanCode,
                    'status' => 'success',
                    'message' => $this->getActionMessage($action),
                    'package_id' => $package->id
                ];
                $successCount++;
            }

            DB::commit();

            \Log::info('scanBatch - Succès', [
                'success' => $successCount,
                'errors' => $errorCount,
                'invalid_status' => $invalidStatusCount
            ]);

            return response()->json([
                'success' => true,
                'message' => "$successCount colis traités, $errorCount erreurs, $invalidStatusCount statuts invalides",
                'results' => $results,
                'summary' => [
                    'total' => count($codes),
                    'success' => $successCount,
                    'errors' => $errorCount,
                    'invalid_status' => $invalidStatusCount,
                    'action' => $action
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('scanBatch - Erreur', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valider le statut du colis selon l'action
     */
    private function validateStatusForAction(string $currentStatus, string $action): array
    {
        if ($action === 'pickup') {
            // Pour ramassage: accepter uniquement CREATED ou AVAILABLE
            $validStatuses = ['CREATED', 'AVAILABLE'];
            if (!in_array($currentStatus, $validStatuses)) {
                return [
                    'valid' => false,
                    'message' => "Statut invalide pour ramassage (actuellement: $currentStatus)"
                ];
            }
        }
        // Pour delivering: accepter tous les statuts (pas de validation)
        
        return ['valid' => true];
    }

    /**
     * Obtenir les données de mise à jour selon l'action
     */
    private function getStatusUpdateForAction(string $action): array
    {
        switch ($action) {
            case 'pickup':
                return [
                    'status' => 'PICKED_UP',
                    'picked_up_at' => now()
                ];
            
            case 'delivering':
                return [
                    'status' => 'IN_TRANSIT',
                    'in_transit_at' => now()
                ];
            
            default:
                return [
                    'status' => 'ACCEPTED'
                ];
        }
    }

    /**
     * Obtenir le message selon l'action
     */
    private function getActionMessage(string $action): string
    {
        switch ($action) {
            case 'pickup':
                return 'Colis ramassé';
            case 'delivering':
                return 'En cours de livraison';
            default:
                return 'Colis traité';
        }
    }

    /**
     * Marquer un colis comme collecté
     */
    public function markPickup(Package $package)
    {
        $user = Auth::user();

        if ($package->assigned_deliverer_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Colis non assigné'], 403);
        }

        $package->update([
            'status' => 'PICKED_UP',
            'picked_up_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Colis collecté']);
    }

    /**
     * Marquer un colis comme livré
     */
    public function markDelivered(Package $package)
    {
        $user = Auth::user();

        if ($package->assigned_deliverer_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Colis non assigné'], 403);
        }

        DB::beginTransaction();
        try {
            // Marquer le colis comme livré
            $package->update([
                'status' => 'DELIVERED',
                'delivered_at' => now()
            ]);

            // Ajouter le montant COD au wallet du livreur
            if ($package->cod_amount > 0) {
                $wallet = UserWallet::firstOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => 0, 'pending_amount' => 0, 'frozen_amount' => 0, 'advance_balance' => 0]
                );

                $wallet->addFunds(
                    $package->cod_amount,
                    "COD collecté - Colis #{$package->package_code}",
                    "COD_DELIVERY_{$package->id}"
                );
            }

            DB::commit();
            return response()->json([
                'success' => true, 
                'message' => 'Colis livré' . ($package->cod_amount > 0 ? ' - COD ajouté au wallet' : '')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Marquer un colis comme indisponible
     */
    public function markUnavailable(Package $package)
    {
        $user = Auth::user();

        if ($package->assigned_deliverer_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Colis non assigné'], 403);
        }

        $package->update([
            'status' => 'UNAVAILABLE',
            'unavailable_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Colis marqué indisponible']);
    }

    /**
     * Marquer un colis comme annulé
     */
    public function markCancelled(Package $package)
    {
        $user = Auth::user();

        if ($package->assigned_deliverer_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Colis non assigné'], 403);
        }

        $package->update([
            'status' => 'CANCELLED',
            'cancelled_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Colis annulé']);
    }

    /**
     * Sauvegarder la signature
     */
    public function saveSignature(Request $request, Package $package)
    {
        $user = Auth::user();

        if ($package->assigned_deliverer_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Colis non assigné'], 403);
        }

        $request->validate([
            'signature' => 'required|string'
        ]);

        try {
            $signatureData = $request->signature;
            if (preg_match('/^data:image\/(\w+);base64,/', $signatureData, $type)) {
                $signatureData = substr($signatureData, strpos($signatureData, ',') + 1);
                $type = strtolower($type[1]);

                if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                    throw new \Exception('Type de signature invalide');
                }

                $signatureData = str_replace(' ', '+', $signatureData);
                $signatureData = base64_decode($signatureData);

                if ($signatureData === false) {
                    throw new \Exception('Impossible de décoder la signature');
                }

                $signaturePath = 'signatures/' . $package->tracking_number . '_' . time() . '.' . $type;
                Storage::disk('public')->put($signaturePath, $signatureData);

                $package->update([
                    'delivery_signature' => $signaturePath,
                    'status' => 'DELIVERED',
                    'delivered_at' => now()
                ]);

                return response()->json(['success' => true, 'message' => 'Signature sauvegardée']);
            }

            throw new \Exception('Format de signature invalide');

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Récupérer les collectes du jour
     */
    public function apiPickups()
    {
        $user = Auth::user();

        $pickups = PickupRequest::where('assigned_deliverer_id', $user->id)
            ->whereIn('status', ['assigned', 'picked_up'])
            ->get()
            ->map(function($pickup) {
                return [
                    'id' => $pickup->id,
                    'pickup_address' => $pickup->pickup_address,
                    'pickup_contact' => $pickup->pickup_contact,
                    'pickup_phone' => $pickup->pickup_phone,
                    'packages_count' => $pickup->packages->count(),
                    'status' => $pickup->status,
                    'type' => 'pickup'
                ];
            });

        return response()->json($pickups);
    }

    /**
     * API: Récupérer les livraisons du jour
     */
    public function apiDeliveries()
    {
        $user = Auth::user();

        $deliveries = Package::where('assigned_deliverer_id', $user->id)
            ->whereIn('status', ['ACCEPTED', 'PICKED_UP', 'UNAVAILABLE'])
            ->select([
                'id', 'tracking_number', 'recipient_name', 'recipient_address',
                'recipient_phone', 'cod_amount', 'status'
            ])
            ->get()
            ->map(function($package) {
                return [
                    'id' => $package->id,
                    'tracking_number' => $package->tracking_number,
                    'recipient_name' => $package->recipient_name,
                    'recipient_address' => $package->recipient_address,
                    'recipient_phone' => $package->recipient_phone,
                    'cod_amount' => $package->cod_amount,
                    'status' => $package->status,
                    'type' => 'delivery'
                ];
            });

        return response()->json($deliveries);
    }




    /**
     * API: Scanner un QR code
     */
    public function scanQR(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string'
        ]);

        $user = Auth::user();
        $code = $this->normalizeCode($request->qr_code);

        try {
            // Rechercher le colis par code (OPTIMISÉ - seulement champs nécessaires)
            $package = $this->findPackageByCode($code);

            if ($package) {
                // Auto-assigner si pas encore assigné OU réassigner au livreur actuel
                if (!$package->assigned_deliverer_id || $package->assigned_deliverer_id !== $user->id) {
                    $package->update([
                        'assigned_deliverer_id' => $user->id,
                        'assigned_at' => now(),
                        'status' => $package->status === 'CREATED' ? 'ACCEPTED' : $package->status
                    ]);
                }
                
                // PLUS DE VÉRIFICATION - Le livreur peut scanner tous les colis
                // Le colis est automatiquement assigné au livreur qui le scanne
                
                // OPTIMISÉ - Réponse minimaliste pour rapidité
                return response()->json([
                    'success' => true,
                    'package_id' => $package->id,
                    'redirect' => route('deliverer.task.detail', $package)
                ]);
            }

            // Rechercher une collecte
            $pickup = PickupRequest::where('pickup_code', $code)->first();

            if (!$pickup) {
                // Essayer aussi avec l'ID si c'est un nombre
                if (is_numeric($code)) {
                    $pickup = PickupRequest::find($code);
                }
            }

            if ($pickup) {
                return response()->json([
                    'success' => true,
                    'type' => 'pickup',
                    'package_id' => $pickup->id,
                    'message' => 'Demande de collecte trouvée',
                    'package' => [
                        'id' => $pickup->id,
                        'code' => $pickup->pickup_code ?? 'PICKUP_' . $pickup->id,
                        'cod_amount' => 0,
                        'formatted_cod' => '0.000 TND',
                        'status' => $pickup->status
                    ],
                    'redirect' => route('deliverer.run.sheet')
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Code non trouvé: ' . $code
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obtenir le solde wallet
     */
    public function apiWalletBalance()
    {
        $user = Auth::user();
        
        // OPTIMISÉ - Sélection colonnes seulement
        $wallet = UserWallet::select(['balance', 'available_balance', 'pending_amount'])
            ->where('user_id', $user->id)
            ->first();

        return response()->json([
            'balance' => $wallet ? $wallet->balance : 0,
            'available_balance' => $wallet ? $wallet->available_balance : 0,
            'pending_amount' => $wallet ? $wallet->pending_amount : 0
        ]);
    }

    // =================================================================================
    // MÉTHODES UTILITAIRES POUR WALLET OPTIMISÉ
    // =================================================================================

    /**
     * Obtenir le montant collecté aujourd'hui
     */
    private function getTodayCollectedAmount()
    {
        $user = Auth::user();

        return Package::where('assigned_deliverer_id', $user->id)
            ->where('status', 'DELIVERED')
            ->where('cod_amount', '>', 0)
            ->whereDate('delivered_at', today())
            ->sum('cod_amount');
    }

    /**
     * Obtenir le montant COD en attente
     */
    private function getPendingCodAmount()
    {
        $user = Auth::user();

        return Package::where('assigned_deliverer_id', $user->id)
            ->whereIn('status', ['ACCEPTED', 'PICKED_UP'])
            ->where('cod_amount', '>', 0)
            ->sum('cod_amount');
    }

    /**
     * Obtenir le nombre de transactions du mois
     */
    private function getMonthlyTransactionCount()
    {
        $user = Auth::user();

        return Package::where('assigned_deliverer_id', $user->id)
            ->where('status', 'DELIVERED')
            ->where('cod_amount', '>', 0)
            ->whereMonth('delivered_at', now()->month)
            ->whereYear('delivered_at', now()->year)
            ->count();
    }

    /**
     * Obtenir les transactions récentes
     */
    private function getRecentTransactions()
    {
        $user = Auth::user();

        return Package::where('assigned_deliverer_id', $user->id)
            ->where('status', 'DELIVERED')
            ->where('cod_amount', '>', 0)
            ->orderBy('delivered_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($package) {
                return [
                    'id' => $package->id,
                    'description' => "COD Collecté - Colis #{$package->tracking_number}",
                    'amount' => $package->cod_amount,
                    'date' => $package->delivered_at,
                    'status_display' => 'Terminé',
                    'is_credit' => true
                ];
            });
    }

    /**
     * Obtenir le dernier vidage de wallet
     */
    private function getLastEmptying()
    {
        // À implémenter selon votre logique de vidage de wallet
        // Pour l'instant retourner null
        return null;
    }

    // =================================================================================
    // MÉTHODES D'IMPRESSION
    // =================================================================================

    /**
     * Imprimer le run sheet du livreur
     */
    public function printRunSheet()
    {
        $user = Auth::user();

        // Récupérer tous les packages assignés au livreur pour aujourd'hui
        $packages = Package::with('client')
            ->where('assigned_deliverer_id', $user->id)
            ->whereIn('status', ['AVAILABLE', 'ACCEPTED', 'PICKED_UP'])
            ->whereDate('created_at', today())
            ->orderBy('recipient_city')
            ->orderBy('recipient_address')
            ->get();

        // Déterminer le secteur (basé sur la première ville)
        $sector = $packages->pluck('recipient_city')->filter()->first() ?? 'Multiple';

        return view('deliverer.run-sheet-print', compact('packages', 'sector'));
    }

    /**
     * Imprimer le reçu de livraison pour un colis
     */
    public function printDeliveryReceipt(Package $package)
    {
        $user = Auth::user();

        // Vérifier que le package appartient au livreur
        if ($package->assigned_deliverer_id !== $user->id) {
            abort(403, 'Accès non autorisé à ce colis');
        }

        // Vérifier que le colis a été livré ou collecté
        if (!in_array($package->status, ['DELIVERED', 'PICKED_UP'])) {
            abort(400, 'Ce colis n\'a pas encore été livré ou collecté');
        }

        return view('deliverer.delivery-receipt-print', compact('package'));
    }

    /**
     * PWA Optimisée: Client recharge interface (pour future fonctionnalité)
     */
    public function clientRecharge()
    {
        return view('deliverer.client-recharge');
    }

    /**
     * Vue de la liste des collectes
     */
    public function pickupsIndex()
    {
        // TODO: Créer une vue pour lister les collectes
        return "<h1>Page des collectes à venir</h1>";
    }

    /**
     * Traiter le scan d'un QR code de collecte
     */
    public function processPickupScan(Request $request)
    {
        $request->validate([
            'qr_code' => 'required',
        ]);

        $user = Auth::user();
        // Le qr-scanner retourne un objet, on prend la donnée brute
        $qrCodeValue = is_array($request->qr_code) ? $request->qr_code['data'] : $request->qr_code;
        $qrCode = $this->normalizeCode($qrCodeValue);

        // Recherche de la demande de collecte
        $pickup = \App\Models\PickupRequest::where('pickup_code', $qrCode)->first();

        if ($pickup) {
            // Logique de succès
            return response()->json([
                'success' => true,
                'message' => 'Collecte ' . $pickup->pickup_code . ' trouvée.',
                'redirect_url' => route('deliverer.pickups.index') // Rediriger vers la liste des collectes
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'QR code de collecte non valide ou non trouvé.'
        ], 404);
    }

    /**
     * API: Récupérer les pickup requests disponibles (pending)
     * Filtré par gouvernorats du livreur
     */
    public function apiAvailablePickups()
    {
        try {
            $user = Auth::user();
            
            // Gérer les gouvernorats (array ou JSON)
            $gouvernorats = $user->deliverer_gouvernorats ?? [];
            if (is_string($gouvernorats)) {
                $gouvernorats = json_decode($gouvernorats, true) ?? [];
            }
            if (!is_array($gouvernorats)) {
                $gouvernorats = [];
            }
            
            $pickups = PickupRequest::where('status', 'pending')
                ->where('assigned_deliverer_id', null)
                ->when(!empty($gouvernorats), function($q) use ($gouvernorats) {
                    return $q->whereHas('delegation', function($subQ) use ($gouvernorats) {
                        $subQ->whereIn('governorate', $gouvernorats);
                    });
                })
                ->with(['delegation', 'client'])
                ->orderBy('requested_pickup_date', 'asc')
                ->get()
                ->map(function($pickup) {
                    return [
                        'id' => $pickup->id,
                        'pickup_address' => $pickup->pickup_address ?? 'N/A',
                        'pickup_contact_name' => $pickup->pickup_contact_name ?? 'N/A',
                        'pickup_phone' => $pickup->pickup_phone ?? 'N/A',
                        'pickup_notes' => $pickup->pickup_notes,
                        'delegation_name' => $pickup->delegation?->name ?? 'N/A',
                        'governorate' => $pickup->delegation?->governorate ?? 'N/A',
                        'requested_pickup_date' => $pickup->requested_pickup_date ? $pickup->requested_pickup_date->format('d/m/Y H:i') : null,
                        'status' => $pickup->status,
                        'client_name' => $pickup->client?->name ?? 'N/A',
                        'type' => 'available_pickup'
                    ];
                });

            return response()->json($pickups);
            
        } catch (\Exception $e) {
            \Log::error('Erreur apiAvailablePickups:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Erreur lors du chargement des pickups: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Accepter une pickup request
     * Avec vérification gouvernorat
     */
    public function acceptPickup(PickupRequest $pickupRequest)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est un livreur
        if (!$user || $user->role !== 'DELIVERER') {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        // Vérifier que la pickup request est disponible
        if ($pickupRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cette demande de collecte n\'est plus disponible'
            ], 400);
        }

        if ($pickupRequest->assigned_deliverer_id !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Cette demande de collecte a déjà été acceptée par un autre livreur'
            ], 400);
        }
        
        // Vérifier que le pickup est dans les gouvernorats du livreur
        $gouvernorats = is_array($user->deliverer_gouvernorats) ? $user->deliverer_gouvernorats : [];
        if (!empty($gouvernorats)) {
            $pickupGouvernorat = $pickupRequest->delegation?->governorate;
            if (!in_array($pickupGouvernorat, $gouvernorats)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce ramassage n\'est pas dans votre zone de travail'
                ], 403);
            }
        }

        try {
            // Accepter la pickup request
            $pickupRequest->update([
                'status' => 'assigned',
                'assigned_deliverer_id' => $user->id,
                'assigned_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Demande de collecte acceptée avec succès',
                'pickup' => [
                    'id' => $pickupRequest->id,
                    'pickup_address' => $pickupRequest->pickup_address,
                    'pickup_contact_name' => $pickupRequest->pickup_contact_name,
                    'pickup_phone' => $pickupRequest->pickup_phone,
                    'status' => 'assigned',
                    'assigned_deliverer_id' => $user->id
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'acceptation de la demande de collecte'
            ], 500);
        }
    }

    /**
     * Marquer une pickup request comme collectée
     */
    public function markPickupCollected(PickupRequest $pickupRequest)
    {
        $user = Auth::user();

        // Vérifier que la pickup request appartient au livreur
        if ($pickupRequest->assigned_deliverer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à modifier cette demande de collecte'
            ], 403);
        }

        // Vérifier que la pickup request est assignée
        if ($pickupRequest->status !== 'assigned') {
            return response()->json([
                'success' => false,
                'message' => 'Cette demande de collecte ne peut pas être marquée comme collectée'
            ], 400);
        }

        // Marquer comme collectée
        $pickupRequest->markAsPickedUp($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Collecte marquée comme effectuée',
            'pickup' => [
                'id' => $pickupRequest->id,
                'status' => $pickupRequest->status,
                'picked_up_at' => $pickupRequest->picked_up_at?->format('d/m/Y H:i')
            ]
        ]);
    }

    /**
     * Vue des retraits assignés au livreur
     */
    public function myWithdrawals()
    {
        $user = Auth::user();

        $withdrawals = \App\Models\WithdrawalRequest::where('assigned_deliverer_id', $user->id)
            ->whereIn('status', ['READY_FOR_DELIVERY', 'IN_PROGRESS'])
            ->with(['client'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('deliverer.withdrawals', compact('withdrawals'));
    }

    /**
     * Déterminer l'action appropriée selon le statut du package
     */
    private function getPackageAction($status)
    {
        switch ($status) {
            case 'AVAILABLE':
                return 'accept';
            case 'ACCEPTED':
                return 'pickup';
            case 'PICKED_UP':
                return 'deliver';
            case 'DELIVERED':
                return 'view';
            case 'RETURNED':
                return 'view';
            default:
                return 'view';
        }
    }

    /**
     * API: Récupérer les packages actifs du livreur
     */
    public function apiActivePackages()
    {
        $user = Auth::user();

        // OPTIMISÉ - Sélection seulement colonnes nécessaires + limit
        $packages = Package::select([
                'id', 'tracking_number', 'package_code', 'recipient_name', 
                'recipient_address', 'recipient_phone', 'cod_amount', 'status', 
                'est_echange', 'created_at'
            ])
            ->where('assigned_deliverer_id', $user->id)
            ->whereIn('status', ['AVAILABLE', 'ACCEPTED', 'PICKED_UP'])
            ->orderBy('created_at', 'desc')
            ->limit(100) // Limiter résultats
            ->get()
            ->map(function($package) {
                return [
                    'id' => $package->id,
                    'type' => 'livraison',
                    'tracking_number' => $package->tracking_number,
                    'package_code' => $package->package_code,
                    'recipient_name' => $package->recipient_name,
                    'recipient_address' => $package->recipient_address,
                    'recipient_phone' => $package->recipient_phone,
                    'cod_amount' => $package->cod_amount ?? 0,
                    'status' => $package->status,
                    'est_echange' => $package->est_echange ?? false,
                    'created_at' => $package->created_at?->format('d/m/Y H:i')
                ];
            });

        return response()->json($packages);
    }

    /**
     * API: Récupérer les packages livrés
     */
    public function apiDeliveredPackages()
    {
        $user = Auth::user();

        $packages = Package::where('assigned_deliverer_id', $user->id)
            ->where('status', 'DELIVERED')
            ->orderBy('delivered_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function($package) {
                return [
                    'id' => $package->id,
                    'tracking_number' => $package->tracking_number,
                    'package_code' => $package->package_code,
                    'recipient_name' => $package->recipient_name,
                    'cod_amount' => $package->cod_amount ?? 0,
                    'status' => $package->status,
                    'delivered_at' => $package->delivered_at?->format('d/m/Y H:i')
                ];
            });

        return response()->json($packages);
    }

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
            'cod_amount' => $package->cod_amount ?? 0,
            'status' => $package->status,
            'est_echange' => $package->est_echange ?? false,
            'notes' => $package->delivery_notes,
            'delivery_notes' => $package->delivery_notes
        ]);
    }

    /**
     * API: Rechercher un client par téléphone
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
                $wallet = UserWallet::where('user_id', $client->id)->first();
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
     * API: Recharger le compte d'un client
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

            // Créer ou mettre à jour le wallet du client
            $wallet = UserWallet::firstOrCreate(
                ['user_id' => $client->id],
                ['balance' => 0, 'available_balance' => 0, 'pending_amount' => 0]
            );

            $wallet->increment('balance', $request->amount);
            $wallet->increment('available_balance', $request->amount);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Recharge effectuée avec succès',
                'new_balance' => $wallet->balance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recharge: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pickups disponibles - Vue
     */
    public function availablePickups()
    {
        $user = Auth::user();
        $gouvernorats = $user->deliverer_gouvernorats ?? [];
        
        $pickups = PickupRequest::where('assigned_deliverer_id', $user->id)
            ->whereIn('status', ['assigned', 'pending'])
            ->when(!empty($gouvernorats), function($q) use ($gouvernorats) {
                return $q->whereHas('delegation', function($subQ) use ($gouvernorats) {
                    $subQ->whereIn('governorate', $gouvernorats);
                });
            })
            ->with('delegation', 'client')
            ->orderBy('requested_pickup_date', 'asc')
            ->get();
        
        return view('deliverer.pickups-available', compact('pickups'));
    }

    /**
     * Scanner simple - Vue (AFFICHE TOUS LES COLIS)
     * 
     * STATUTS RAMASSAGE (pickup): CREATED, AVAILABLE
     * STATUTS LIVRAISON (delivery): AVAILABLE, CREATED, AT_DEPOT, OUT_FOR_DELIVERY, PICKED_UP
     */
    public function scanSimple()
    {
        $user = Auth::user();
        
        // Charger TOUS les colis (peu importe statut ou assignation)
        $packages = Package::select('id', 'package_code', 'status', 'assigned_deliverer_id')
            ->get()
            ->map(function($pkg) use ($user) {
                $cleanCode = str_replace(['_', '-', ' '], '', strtoupper($pkg->package_code));
                return [
                    'c' => $pkg->package_code,
                    'c2' => $cleanCode,
                    's' => $pkg->status,
                    'p' => in_array($pkg->status, ['CREATED', 'AVAILABLE']) ? 1 : 0,
                    'd' => in_array($pkg->status, ['AVAILABLE', 'CREATED', 'AT_DEPOT', 'OUT_FOR_DELIVERY', 'PICKED_UP']) ? 1 : 0,
                    'id' => $pkg->id,
                    'assigned' => $pkg->assigned_deliverer_id === $user->id ? 1 : 0
                ];
            });
        
        return view('deliverer.scan-production', compact('packages'));
    }

    /**
     * Scanner multi - Vue (AFFICHE TOUS LES COLIS)
     * 
     * STATUTS RAMASSAGE (pickup): CREATED, AVAILABLE
     * STATUTS LIVRAISON (delivery): AVAILABLE, CREATED, AT_DEPOT, OUT_FOR_DELIVERY, PICKED_UP
     */
    public function scanMulti()
    {
        $user = Auth::user();
        
        // Charger TOUS les colis (peu importe statut ou assignation)
        $packages = Package::select('id', 'package_code', 'status', 'assigned_deliverer_id')
            ->get()
            ->map(function($pkg) use ($user) {
                $cleanCode = str_replace(['_', '-', ' '], '', strtoupper($pkg->package_code));
                return [
                    'c' => $pkg->package_code,
                    'c2' => $cleanCode,
                    's' => $pkg->status,
                    'p' => in_array($pkg->status, ['CREATED', 'AVAILABLE']) ? 1 : 0,
                    'd' => in_array($pkg->status, ['AVAILABLE', 'CREATED', 'AT_DEPOT', 'OUT_FOR_DELIVERY', 'PICKED_UP']) ? 1 : 0,
                    'id' => $pkg->id,
                    'assigned' => $pkg->assigned_deliverer_id === $user->id ? 1 : 0
                ];
            });
        
        return view('deliverer.multi-scanner-production', compact('packages'));
    }

    /**
     * Ramassage simple d'un colis (depuis task-detail)
     */
    public function simplePickup(Package $package)
    {
        $user = Auth::user();

        try {
            DB::beginTransaction();

            // Vérifier que le colis peut être ramassé
            if (!in_array($package->status, ['AVAILABLE', 'ACCEPTED', 'CREATED', 'VERIFIED'])) {
                return redirect()->back()->with('error', 'Ce colis ne peut pas être ramassé (statut: ' . $package->status . ')');
            }

            // Assigner au livreur et changer le statut
            $updateData = [
                'status' => 'PICKED_UP',
                'assigned_deliverer_id' => $user->id,
                'assigned_at' => now()
            ];
            
            // Ajouter picked_up_at seulement s'il n'est pas déjà défini
            if (!$package->picked_up_at) {
                $updateData['picked_up_at'] = now();
            }
            
            $package->update($updateData);

            DB::commit();

            return redirect()->back()->with('success', '✅ Colis ramassé avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur simplePickup:', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur lors du ramassage: ' . $e->getMessage());
        }
    }

    /**
     * Livraison simple d'un colis (depuis task-detail)
     */
    public function simpleDeliver(Package $package)
    {
        $user = Auth::user();

        try {
            DB::beginTransaction();

            // Vérifier que le colis peut être livré
            if (!in_array($package->status, ['PICKED_UP', 'OUT_FOR_DELIVERY'])) {
                return redirect()->back()->with('error', 'Ce colis ne peut pas être livré (statut: ' . $package->status . ')');
            }

            // Vérifier que le colis est assigné au livreur
            if ($package->assigned_deliverer_id !== $user->id) {
                return redirect()->back()->with('error', 'Ce colis n\'est pas assigné à vous');
            }

            // Changer le statut en DELIVERED
            $package->update([
                'status' => 'DELIVERED',
                'delivered_at' => now()
            ]);

            // Ajouter le COD au wallet du livreur si applicable
            if ($package->cod_amount > 0) {
                $wallet = \App\Models\UserWallet::firstOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => 0, 'pending_amount' => 0, 'frozen_amount' => 0, 'advance_balance' => 0]
                );

                $wallet->addFunds(
                    $package->cod_amount,
                    "COD collecté - Colis #{$package->package_code}",
                    "COD_DELIVERY_{$package->id}"
                );
            }

            DB::commit();

            return redirect()->route('deliverer.tournee')->with('success', '✅ Colis livré avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur simpleDeliver:', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur lors de la livraison: ' . $e->getMessage());
        }
    }

    /**
     * Marquer un colis comme client indisponible (avec commentaire obligatoire)
     */
    public function simpleUnavailable(Package $package, Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'comment' => 'required|string|min:5|max:500'
        ], [
            'comment.required' => 'Le commentaire est obligatoire',
            'comment.min' => 'Le commentaire doit contenir au moins 5 caractères',
            'comment.max' => 'Le commentaire ne peut pas dépasser 500 caractères'
        ]);

        try {
            DB::beginTransaction();

            // Vérifier que le colis peut être marqué indisponible
            if (!in_array($package->status, ['PICKED_UP', 'OUT_FOR_DELIVERY'])) {
                return redirect()->back()->with('error', 'Ce colis ne peut pas être marqué indisponible (statut: ' . $package->status . ')');
            }

            // Vérifier que le colis est assigné au livreur
            if ($package->assigned_deliverer_id !== $user->id) {
                return redirect()->back()->with('error', 'Ce colis n\'est pas assigné à vous');
            }

            // Mettre à jour avec commentaire
            $package->update([
                'status' => 'UNAVAILABLE',
                'unavailable_attempts' => ($package->unavailable_attempts ?? 0) + 1,
                'notes' => ($package->notes ? $package->notes . "\n\n" : '') . 
                          '❗ Indisponible (' . now()->format('d/m/Y H:i') . ') par ' . $user->name . ': ' . $request->comment
            ]);

            DB::commit();

            return redirect()->route('deliverer.tournee')->with('warning', '⚠️ Client marqué indisponible');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur simpleUnavailable:', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Marquer un colis comme refusé par le client (avec commentaire obligatoire)
     */
    public function simpleRefused(Package $package, Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'comment' => 'required|string|min:5|max:500'
        ], [
            'comment.required' => 'Le commentaire est obligatoire',
            'comment.min' => 'Le commentaire doit contenir au moins 5 caractères',
            'comment.max' => 'Le commentaire ne peut pas dépasser 500 caractères'
        ]);

        try {
            DB::beginTransaction();

            // Vérifier que le colis peut être marqué refusé
            if (!in_array($package->status, ['PICKED_UP', 'OUT_FOR_DELIVERY'])) {
                return redirect()->back()->with('error', 'Ce colis ne peut pas être marqué refusé (statut: ' . $package->status . ')');
            }

            // Vérifier que le colis est assigné au livreur
            if ($package->assigned_deliverer_id !== $user->id) {
                return redirect()->back()->with('error', 'Ce colis n\'est pas assigné à vous');
            }

            // Marquer comme refusé avec commentaire
            $package->update([
                'status' => 'REFUSED',
                'delivery_attempts' => ($package->delivery_attempts ?? 0) + 1,
                'notes' => ($package->notes ? $package->notes . "\n\n" : '') . 
                          '❌ Refusé (' . now()->format('d/m/Y H:i') . ') par ' . $user->name . ': ' . $request->comment
            ]);

            DB::commit();

            return redirect()->route('deliverer.tournee')->with('error', '❌ Colis refusé par le client');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur simpleRefused:', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Reporter la livraison à une date ultérieure (dans les 7 prochains jours)
     */
    public function simpleScheduled(Package $package, Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'scheduled_date' => 'required|date|after:today|before:' . date('Y-m-d', strtotime('+8 days')),
            'comment' => 'required|string|min:5|max:500'
        ], [
            'scheduled_date.required' => 'La date de livraison est obligatoire',
            'scheduled_date.after' => 'La date doit être ultérieure à aujourd\'hui',
            'scheduled_date.before' => 'La date ne peut pas dépasser 7 jours',
            'comment.required' => 'Le commentaire est obligatoire',
            'comment.min' => 'Le commentaire doit contenir au moins 5 caractères'
        ]);

        try {
            DB::beginTransaction();

            // Vérifier que le colis peut être reporté
            if (!in_array($package->status, ['PICKED_UP', 'OUT_FOR_DELIVERY'])) {
                return redirect()->back()->with('error', 'Ce colis ne peut pas être reporté (statut: ' . $package->status . ')');
            }

            // Vérifier que le colis est assigné au livreur
            if ($package->assigned_deliverer_id !== $user->id) {
                return redirect()->back()->with('error', 'Ce colis n\'est pas assigné à vous');
            }

            // Reporter la livraison
            $package->update([
                'status' => 'SCHEDULED',
                'scheduled_delivery_date' => $request->scheduled_date,
                'notes' => ($package->notes ? $package->notes . "\n\n" : '') . 
                          '📅 Reporté au ' . date('d/m/Y', strtotime($request->scheduled_date)) . 
                          ' (' . now()->format('d/m/Y H:i') . ') par ' . $user->name . ': ' . $request->comment
            ]);

            DB::commit();

            return redirect()->route('deliverer.tournee')->with('success', '📅 Livraison reportée au ' . date('d/m/Y', strtotime($request->scheduled_date)));

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur simpleScheduled:', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

}