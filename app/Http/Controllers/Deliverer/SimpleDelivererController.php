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
     * Interface de scan multiple
     */
    public function multiScanner()
    {
        return view('deliverer.multi-scanner');
    }

    /**
     * Traitement du scan multiple
     */
    public function processMultiScan(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'action' => 'required|in:pickup,delivery',
            'scanned_ids' => 'nullable|array'
        ]);

        $user = Auth::user();
        $code = $this->normalizeCode($request->code);
        $action = $request->action;
        $scannedIds = $request->scanned_ids ?? [];

        try {
            // Rechercher le colis par code
            $package = $this->findPackageByCode($code);

            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code invalide ou colis non trouvé'
                ]);
            }

            // Vérifier si le colis est déjà dans la liste
            if (in_array($package->id, $scannedIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Colis déjà ajouté à la liste'
                ]);
            }

            // Valider le statut selon l'action
            $validationResult = $this->validatePackageStatus($package, $action);
            if (!$validationResult['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $validationResult['message']
                ]);
            }

            // Préparer les données du destinataire
            $recipientData = is_array($package->recipient_data) ? $package->recipient_data : [];
            $recipientName = $recipientData['name'] ?? $package->recipient_name ?? 'N/A';
            $recipientAddress = $recipientData['address'] ?? $package->recipient_address ?? 'N/A';

            return response()->json([
                'success' => true,
                'message' => 'Colis valide ajouté',
                'package' => [
                    'id' => $package->id,
                    'tracking_number' => $package->tracking_number ?? $package->package_code,
                    'recipient_name' => $recipientName,
                    'recipient_address' => $recipientAddress,
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
     * Validation de la liste des colis scannés
     */
    public function validateMultiScan(Request $request)
    {
        $request->validate([
            'action' => 'required|in:pickup,delivery',
            'package_ids' => 'required|array|min:1',
            'package_ids.*' => 'exists:packages,id'
        ]);

        $user = Auth::user();
        $action = $request->action;
        $packageIds = $request->package_ids;

        try {
            DB::beginTransaction();

            $packages = Package::whereIn('id', $packageIds)->get();

            foreach ($packages as $package) {
                if ($action === 'pickup') {
                    // Pickup chez fournisseur: changer le statut à PICKED_UP
                    $package->update([
                        'status' => 'PICKED_UP',
                        'picked_up_at' => now(),
                        'assigned_deliverer_id' => $user->id
                    ]);
                } else {
                    // Prêt pour livraison: changer le statut à PICKED_UP (en cours de livraison)
                    $package->update([
                        'status' => 'PICKED_UP',
                        'picked_up_at' => $package->picked_up_at ?? now(),
                        'assigned_deliverer_id' => $user->id
                    ]);
                }
            }

            DB::commit();

            $message = $action === 'pickup' 
                ? 'Colis collectés avec succès' 
                : 'Colis prêts pour la livraison';

            return response()->json([
                'success' => true,
                'message' => $message,
                'count' => count($packageIds)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la validation: ' . $e->getMessage()
            ], 500);
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
     */
    private function findPackageByCode(string $code): ?Package
    {
        $cleanCode = strtoupper(trim($code));
        
        // Si c'est une URL complète (QR code), extraire le code
        if (preg_match('/\/track\/(.+)$/i', $cleanCode, $matches)) {
            $cleanCode = strtoupper($matches[1]);
        }
        
        // Nettoyer les espaces et caractères spéciaux
        $cleanCode = preg_replace('/[^A-Z0-9_-]/', '', $cleanCode);
        
        // Liste des variations possibles du code
        $codeVariations = [$cleanCode];
        
        // Ajouter variation avec PKG_ si pas présent
        if (!str_starts_with($cleanCode, 'PKG_')) {
            $codeVariations[] = 'PKG_' . $cleanCode;
        }
        
        // Ajouter variation sans PKG_ si présent
        if (str_starts_with($cleanCode, 'PKG_')) {
            $codeVariations[] = substr($cleanCode, 4);
        }
        
        // Chercher dans toutes les variations
        foreach ($codeVariations as $variation) {
            // Chercher par tracking_number
            $package = Package::where('tracking_number', $variation)->first();
            if ($package) return $package;
            
            // Chercher par package_code
            $package = Package::where('package_code', $variation)->first();
            if ($package) return $package;
            
            // Recherche partielle (fin du code) pour codes longs
            if (strlen($variation) >= 8) {
                $package = Package::where('tracking_number', 'LIKE', '%' . substr($variation, -8))
                    ->orWhere('package_code', 'LIKE', '%' . substr($variation, -8))
                    ->first();
                if ($package) return $package;
            }
        }
        
        return null;
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

        $package->update([
            'status' => 'DELIVERED',
            'delivered_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Colis livré']);
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
        $qrCode = $request->qr_code;

        try {
            // Rechercher le colis par tracking number
            $package = Package::where('tracking_number', $qrCode)
                ->where('assigned_deliverer_id', $user->id)
                ->first();

            if ($package) {
                return response()->json([
                    'success' => true,
                    'type' => 'package',
                    'message' => 'Colis trouvé et assigné à vous',
                    'package' => [
                        'id' => $package->id,
                        'code' => $package->tracking_number,
                        'cod_amount' => $package->cod_amount,
                        'formatted_cod' => number_format($package->cod_amount, 3) . ' TND',
                        'status' => $package->status
                    ],
                    'delivery_info' => [
                        'name' => $package->recipient_name,
                        'address' => $package->recipient_address,
                        'phone' => $package->recipient_phone
                    ],
                    'redirect' => route('deliverer.task.detail', $package),
                    'action' => $this->getPackageAction($package->status)
                ]);
            }

            // Rechercher une collecte
            $pickup = PickupRequest::where('pickup_code', $qrCode)
                ->where('assigned_deliverer_id', $user->id)
                ->first();

            if ($pickup) {
                return response()->json([
                    'success' => true,
                    'type' => 'pickup',
                    'message' => 'Demande de collecte trouvée',
                    'package' => [
                        'id' => $pickup->id,
                        'code' => $pickup->pickup_code ?? 'PICKUP_' . $pickup->id,
                        'cod_amount' => 0,
                        'formatted_cod' => '0.000 TND',
                        'status' => $pickup->status
                    ],
                    'delivery_info' => [
                        'name' => $pickup->pickup_contact_name ?? $pickup->pickup_contact,
                        'address' => $pickup->pickup_address,
                        'phone' => $pickup->pickup_phone
                    ],
                    'redirect' => route('deliverer.run.sheet'),
                    'action' => 'pickup'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'QR code non trouvé ou non assigné à vous'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du scan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obtenir le solde wallet
     */
    public function apiWalletBalance()
    {
        $user = Auth::user();
        $wallet = UserWallet::where('user_id', $user->id)->first();

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
     */
    public function apiAvailablePickups()
    {
        $pickups = PickupRequest::where('status', 'pending')
            ->where('assigned_deliverer_id', null)
            ->orderBy('requested_pickup_date', 'asc')
            ->get()
            ->map(function($pickup) {
                return [
                    'id' => $pickup->id,
                    'pickup_address' => $pickup->pickup_address,
                    'pickup_contact_name' => $pickup->pickup_contact_name,
                    'pickup_phone' => $pickup->pickup_phone,
                    'pickup_notes' => $pickup->pickup_notes,
                    'delegation_from' => $pickup->delegation_from,
                    'requested_pickup_date' => $pickup->requested_pickup_date?->format('d/m/Y H:i'),
                    'status' => $pickup->status,
                    'client_name' => $pickup->client?->name,
                    'type' => 'available_pickup'
                ];
            });

        return response()->json($pickups);
    }

    /**
     * Accepter une pickup request
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

}