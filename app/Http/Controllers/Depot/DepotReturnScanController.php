<?php

namespace App\Http\Controllers\Depot;

use App\Http\Controllers\Controller;
use App\Models\Package;
// ReturnPackage model n'existe plus - tout est dans Package maintenant
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepotReturnScanController extends Controller
{
    /**
     * Afficher le dashboard PC pour le scan des retours
     * Utilise la même page que le scan normal (depot.scan-dashboard)
     */
    public function dashboard(Request $request)
    {
        // Utiliser le nom de l'utilisateur connecté s'il existe
        $depotManagerName = null;

        if (auth()->check()) {
            $user = auth()->user();
            $depotManagerName = $user->name;
        } else {
            $depotManagerName = $request->input('depot_manager_name', session('depot_manager_name_returns'));
        }

        // Si pas de nom, rediriger vers saisie
        if (!$depotManagerName) {
            return redirect()->route('depot.returns.enter-manager-name');
        }

        // Sauvegarder en session
        session(['depot_manager_name_returns' => $depotManagerName]);

        // Générer un ID de session unique
        $sessionId = \Illuminate\Support\Str::uuid();

        // Générer un code de session de 8 chiffres
        $sessionCode = str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);

        // Stocker la session en cache pour 8 heures (même format que scan normal)
        Cache::put("depot_session_{$sessionId}", [
            'created_at' => now(),
            'status' => 'waiting',
            'scanned_packages' => [],
            'depot_manager_name' => $depotManagerName,
            'session_code' => $sessionCode,
            'scan_type' => 'returns', // Indicateur pour différencier
        ], 8 * 60 * 60);

        // Stocker aussi par code pour accès rapide
        Cache::put("depot_code_{$sessionCode}", $sessionId, 8 * 60 * 60);

        // Utiliser la vue SPÉCIFIQUE pour les retours (orange/rouge)
        $isReturnsMode = true; // Indiquer que c'est pour les retours
        $scannerUrl = route('depot.returns.phone-scanner', $sessionId); // URL pour retours

        return view('depot.returns.scan-dashboard-returns', compact('sessionId', 'depotManagerName', 'sessionCode', 'isReturnsMode', 'scannerUrl'));
    }

    /**
     * Afficher le formulaire de saisie du nom du gestionnaire
     */
    public function enterManagerName()
    {
        // Si connecté, rediriger directement au dashboard
        if (auth()->check()) {
            return redirect()->route('depot.returns.dashboard');
        }

        return view('depot.returns.enter-manager-name');
    }

    /**
     * Scanner pour mobile (connexion via QR code)
     * Utilise la même page que le scan normal (depot.phone-scanner)
     */
    public function phoneScanner($sessionId)
    {
        // Vérifier que la session existe
        $session = Cache::get("depot_session_{$sessionId}");

        if (!$session) {
            return view('depot.session-expired', [
                'message' => 'Session expirée ou invalide',
                'reason' => 'La session a expiré ou n\'existe pas'
            ]);
        }

        // Bloquer si session terminée
        if (isset($session['status']) && $session['status'] === 'completed') {
            return view('depot.session-expired', [
                'message' => 'Session terminée',
                'reason' => 'Cette session de scan a été terminée. Le chef de dépôt a validé les colis.',
                'validated_count' => $session['validated_count'] ?? 0,
                'validated_at' => $session['validated_at'] ?? null
            ]);
        }

        // Marquer la session comme connectée
        $session['status'] = 'connected';
        $session['connected_at'] = now();
        Cache::put("depot_session_{$sessionId}", $session, 8 * 60 * 60);

        // Charger UNIQUEMENT les colis RETURN_IN_PROGRESS (pour les retours)
        // Statuts ACCEPTÉS: RETURN_IN_PROGRESS seulement
        $packages = DB::table('packages')
            ->where('status', 'RETURN_IN_PROGRESS')
            ->select('id', 'package_code as c', 'status as s', 'depot_manager_name as d')
            ->get()
            ->map(function($pkg) use ($session) {
                return [
                    'id' => $pkg->id,
                    'c' => $pkg->c, // Code principal
                    's' => $pkg->s, // Statut
                    'd' => $pkg->d, // Nom du chef dépôt
                    'current_depot' => $session['depot_manager_name'] ?? null
                ];
            });

        $depotManagerName = $session['depot_manager_name'] ?? 'Dépôt';
        $isReturnsMode = true; // Indicateur pour la vue

        // Utiliser la vue SPÉCIFIQUE pour les retours (orange/rouge)
        return view('depot.phone-scanner-returns', compact('sessionId', 'packages', 'depotManagerName', 'isReturnsMode'));
    }

    /**
     * Ajouter un code scanné au cache (APPROCHE DIRECTE - TEMPS RÉEL)
     * Identique au scan dépôt mais pour les retours
     */
    public function addScannedCode(Request $request, $sessionId)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $session = Cache::get("depot_session_{$sessionId}");
        
        if (!$session) {
            return response()->json(['error' => 'Session expirée'], 404);
        }

        // Normaliser le code scanné
        $originalCode = trim($request->code);
        $code = strtoupper($originalCode);
        
        // Vérifier si déjà scanné dans cette session
        $scannedPackages = $session['scanned_packages'] ?? [];
        foreach ($scannedPackages as $scanned) {
            $scannedCode = $scanned['package_code'] ?? $scanned['code'];
            if (strtoupper($scannedCode) === $code || 
                str_replace(['_', '-', ' '], '', strtoupper($scannedCode)) === str_replace(['_', '-', ' '], '', $code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Déjà scanné dans cette session'
                ]);
            }
        }
        
        // RECHERCHE INTELLIGENTE : Essayer plusieurs variantes du code
        $package = null;
        $searchVariants = [
            $code,
            str_replace('_', '', $code),
            str_replace('-', '', $code),
            str_replace(['_', '-', ' '], '', $code),
            strtolower($code),
            $originalCode,
        ];
        
        $searchVariants = array_unique($searchVariants);
        
        // Rechercher avec TOUTES les variantes
        foreach ($searchVariants as $variant) {
            $package = DB::table('packages')
                ->where('package_code', $variant)
                ->select('id', 'package_code', 'status')
                ->first();
            
            if ($package) {
                break;
            }
        }
        
        // Si toujours pas trouvé, essayer une recherche LIKE
        if (!$package) {
            $cleanCode = str_replace(['_', '-', ' '], '', $code);
            $package = DB::table('packages')
                ->where(DB::raw('REPLACE(REPLACE(REPLACE(UPPER(package_code), "_", ""), "-", ""), " ", "")'), $cleanCode)
                ->select('id', 'package_code', 'status')
                ->first();
        }

        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Colis introuvable dans la base de données',
                'searched_code' => $code,
            ], 404);
        }

        // VÉRIFICATION SPÉCIFIQUE RETOURS : Doit être RETURN_IN_PROGRESS
        if ($package->status !== 'RETURN_IN_PROGRESS') {
            return response()->json([
                'success' => false,
                'message' => "Statut invalide: {$package->status}. Seuls les colis RETURN_IN_PROGRESS peuvent être scannés.",
                'current_status' => $package->status,
                'expected_status' => 'RETURN_IN_PROGRESS'
            ], 422);
        }

        // Ajouter au cache (SANS mettre à jour la DB)
        $scannedPackages[] = [
            'code' => $code,
            'package_code' => $package->package_code,
            'tracking_number' => $package->package_code,
            'status' => $package->status,
            'scanned_at' => now()->toISOString(),
            'scanned_time' => now()->format('H:i:s')
        ];
        
        $session['scanned_packages'] = $scannedPackages;
        $session['last_scan'] = now();
        Cache::put("depot_session_{$sessionId}", $session, 8 * 60 * 60);
        
        Log::info('Colis retour scanné', [
            'session_id' => $sessionId,
            'package_code' => $package->package_code,
            'status' => $package->status,
        ]);
        
        return response()->json([
            'success' => true,
            'code' => $package->package_code,
            'message' => 'Colis retour trouvé : ' . $package->package_code,
            'total' => count($scannedPackages)
        ]);
    }

    /**
     * API: Obtenir l'état de la session (FORMAT COMPATIBLE AVEC DASHBOARD PC)
     */
    public function getSessionStatus($sessionId)
    {
        $sessionData = Cache::get("depot_session_{$sessionId}");

        if (!$sessionData) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        $scannedPackages = $sessionData['scanned_packages'] ?? [];

        // Format compatible avec le polling du dashboard PC
        return response()->json([
            'status' => $sessionData['status'] ?? 'waiting',
            'scanned_packages' => $scannedPackages,
            'total_scanned' => count($scannedPackages),
            'last_heartbeat' => $sessionData['last_heartbeat'] ?? null,
        ]);
    }

    /**
     * API: Vérifier si la session est toujours active (pour le mobile)
     */
    public function checkSessionActivity($sessionId)
    {
        $sessionData = Cache::get("depot_session_{$sessionId}");

        if (!$sessionData) {
            return response()->json([
                'active' => false,
                'reason' => 'Session expirée',
            ]);
        }

        return response()->json([
            'active' => ($sessionData['status'] ?? 'waiting') !== 'completed',
            'reason' => ($sessionData['status'] ?? 'waiting') === 'completed' ? 'Session terminée par validation' : null,
        ]);
    }

    /**
     * API: Mettre à jour l'activité de la session (heartbeat mobile)
     */
    public function updateActivity($sessionId)
    {
        $sessionData = Cache::get("depot_session_{$sessionId}");

        if (!$sessionData) {
            return response()->json([
                'success' => false,
                'message' => 'Session introuvable',
            ], 404);
        }

        $sessionData['last_activity'] = now();
        Cache::put("depot_session_{$sessionId}", $sessionData, 8 * 60 * 60);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Valider et créer les colis retours
     * Format identique à validateAllFromPC du scan normal
     */
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
                    Log::warning("Colis introuvable lors de la validation", ['package_code' => $packageCode]);
                    continue;
                }

                // Générer le code retour
                $returnCode = 'RET-' . strtoupper(substr(str_replace('-', '', \Illuminate\Support\Str::uuid()), 0, 8));
                
                // Créer le colis retour dans la table packages (nouvelle structure)
                $returnPackage = Package::create([
                    'package_code' => $returnCode,
                    'package_type' => Package::TYPE_RETURN, // Type RETURN
                    'return_package_code' => $returnCode,
                    'original_package_id' => $originalPackage->id,
                    'sender_id' => auth()->id(), // Chef de dépôt
                    'sender_data' => [
                        'name' => 'AL-AMENA DELIVERY',
                        'phone' => '+216 50 127 192',
                        'address' => 'Dépôt Principal',
                    ],
                    'delegation_from' => $originalPackage->delegation_to, // Inversion
                    'recipient_data' => [
                        'name' => $originalPackage->sender->name ?? 'Client',
                        'phone' => $originalPackage->sender->phone ?? '',
                        'address' => $originalPackage->sender->address ?? $originalPackage->sender_data['address'] ?? '',
                        'city' => $originalPackage->sender->city ?? $originalPackage->sender_data['city'] ?? '',
                    ],
                    'delegation_to' => $originalPackage->delegation_from, // Inversion
                    'content_description' => 'Colis de retour - ' . ($originalPackage->content_description ?? ''),
                    'notes' => "Colis retour créé suite au scan dépôt",
                    'return_reason' => $originalPackage->return_reason ?? 'Retour standard',
                    'return_accepted_at' => now(),
                    'cod_amount' => 0, // Pas de COD sur les retours
                    'delivery_fee' => $originalPackage->return_fee ?? 0,
                    'return_fee' => 0,
                    'status' => 'AT_DEPOT',
                    'requires_signature' => true,
                ]);

                $createdReturnPackages[] = [
                    'code' => $returnPackage->return_package_code,
                    'original_code' => $packageCode,
                ];

                Log::info('Colis retour créé', [
                    'return_code' => $returnPackage->return_package_code,
                    'original_code' => $packageCode,
                    'depot_manager' => $session['depot_manager_name'],
                ]);

                $successCount++;
            }

            // TERMINER la session (marquer comme terminée)
            $session['status'] = 'completed';
            $session['scanned_packages'] = [];
            $session['last_validated_packages'] = $createdReturnPackages;
            $session['validated_at'] = now();
            $session['validated_count'] = $successCount;
            $session['completed_at'] = now();

            // Garder en cache 1 heure pour historique
            Cache::put("depot_session_{$sessionId}", $session, 60);

            DB::commit();

            $depotManagerName = $session['depot_manager_name'] ?? 'Dépôt';
            $message = "✅ {$successCount} colis retours créés avec succès";
            if ($errorCount > 0) {
                $message .= " ({$errorCount} erreurs)";
            }

            // Retourner JSON pour éviter page noire
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

            Log::error('Erreur lors de la création des colis retours', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            if (request()->wantsJson() || request()->ajax() || request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Afficher la liste des colis retours créés
     */
    public function manageReturns()
    {
        // Utiliser Package avec filter sur package_type = 'RETURN'
        $returnPackages = Package::where('package_type', Package::TYPE_RETURN)
            ->with(['originalPackage', 'sender', 'assignedDeliverer'])
            ->latest()
            ->paginate(20);

        return view('depot.returns.manage', [
            'returnPackages' => $returnPackages,
        ]);
    }

    /**
     * Détails d'un colis retour
     */
    public function showReturnPackage(Package $returnPackage)
    {
        // Vérifier que c'est bien un retour
        if ($returnPackage->package_type !== Package::TYPE_RETURN) {
            abort(404, 'Colis de retour non trouvé');
        }
        
        $returnPackage->load(['originalPackage.sender', 'sender', 'assignedDeliverer']);

        return view('depot.returns.show', [
            'returnPackage' => $returnPackage,
        ]);
    }

    /**
     * Imprimer le bordereau d'un colis retour
     */
    public function printReturnLabel(Package $returnPackage)
    {
        // Vérifier que c'est bien un retour
        if ($returnPackage->package_type !== Package::TYPE_RETURN) {
            abort(404, 'Colis de retour non trouvé');
        }
        
        // Charger les relations nécessaires
        $returnPackage->load(['originalPackage.pickupAddress', 'originalPackage.sender']);
        
        // Récupérer les informations de pickup depuis le colis original
        $pickupInfo = null;
        if ($returnPackage->originalPackage && $returnPackage->originalPackage->pickupAddress) {
            $pickupAddress = $returnPackage->originalPackage->pickupAddress;
            $pickupInfo = [
                'name' => $pickupAddress->name,
                'contact_name' => $pickupAddress->contact_name,
                'phone' => $pickupAddress->phone,
                'tel2' => $pickupAddress->tel2,
                'address' => $pickupAddress->address,
                'city' => $pickupAddress->city ?? '',
                'postal_code' => $pickupAddress->postal_code ?? '',
            ];
        } elseif ($returnPackage->originalPackage) {
            // Fallback vers le sender_data du colis original
            $package = $returnPackage->originalPackage;
            $senderData = $package->sender_data ?? [];
            $pickupInfo = [
                'name' => $package->sender->name ?? $senderData['name'] ?? 'N/A',
                'contact_name' => $package->sender->name ?? $senderData['name'] ?? 'N/A',
                'phone' => $package->sender->phone ?? $senderData['phone'] ?? 'N/A',
                'tel2' => '',
                'address' => $senderData['address'] ?? 'N/A',
                'city' => $senderData['city'] ?? '',
                'postal_code' => '',
            ];
        }
        
        // Marquer comme imprimé (mettre à jour un champ si nécessaire)
        // $returnPackage->update(['printed_at' => now()]);

        return view('depot.returns.print-label', [
            'returnPackage' => $returnPackage,
            'pickupInfo' => $pickupInfo,
        ]);
    }

    /**
     * Heartbeat du PC pour indiquer que la session est active
     */
    public function heartbeat($sessionId)
    {
        $session = Cache::get("depot_session_{$sessionId}");

        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        // Mettre à jour le timestamp du heartbeat
        $session['last_heartbeat'] = now();
        Cache::put("depot_session_{$sessionId}", $session, 8 * 60 * 60);

        return response()->json(['success' => true]);
    }

    /**
     * Terminer la session (PC fermé/rafraîchi SANS validation)
     */
    public function terminateSession($sessionId)
    {
        $session = Cache::get("depot_session_{$sessionId}");

        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        // NE TERMINER QUE SI PAS DE COLIS SCANNÉS (éviter perte de données)
        $scannedCount = count($session['scanned_packages'] ?? []);

        if ($scannedCount > 0) {
            // Ne pas terminer si des colis sont scannés (attendre validation)
            return response()->json(['success' => true, 'kept_alive' => true]);
        }

        // Marquer comme terminée seulement si aucun colis scanné
        $session['status'] = 'terminated';
        $session['terminated_at'] = now();
        Cache::put("depot_session_{$sessionId}", $session, 8 * 60 * 60);

        return response()->json(['success' => true, 'kept_alive' => false]);
    }

    /**
     * Démarrer une nouvelle session (réinitialiser)
     */
    public function startNewSession(Request $request)
    {
        // Supprimer l'ancienne session
        $oldSessionId = session('depot_return_scan_session_id');
        if ($oldSessionId) {
            Cache::forget("depot_session_{$oldSessionId}");
        }

        // Supprimer les sessions stockées
        session()->forget(['depot_return_scan_session_id', 'depot_manager_name_returns']);

        return redirect()->route('depot.returns.dashboard');
    }
}
