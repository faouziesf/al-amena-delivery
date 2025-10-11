<?php

namespace App\Http\Controllers\Depot;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\ReturnPackage;
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

        // Utiliser la MÊME vue que le scan normal avec mode retours
        $isReturnsMode = true; // Indiquer que c'est pour les retours
        $scannerUrl = route('depot.returns.phone-scanner', $sessionId); // URL pour retours

        return view('depot.scan-dashboard', compact('sessionId', 'depotManagerName', 'sessionCode', 'isReturnsMode', 'scannerUrl'));
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

        // Utiliser la vue SPÉCIFIQUE pour les retours (orange/rouge)
        return view('depot.phone-scanner-returns', compact('sessionId', 'packages', 'depotManagerName'));
    }

    /**
     * API: Scanner un colis retour (mobile)
     */
    public function scanPackage(Request $request, $sessionId)
    {
        $request->validate([
            'package_code' => 'required|string',
        ]);

        $sessionData = Cache::get("depot_session_{$sessionId}");

        if (!$sessionData) {
            return response()->json([
                'success' => false,
                'message' => 'Session expirée ou introuvable',
            ], 400);
        }

        // Vérifier si session terminée
        if (isset($sessionData['status']) && $sessionData['status'] === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Session terminée',
            ], 400);
        }

        $packageCode = trim($request->package_code);

        // Rechercher le colis
        $package = Package::where(function($query) use ($packageCode) {
            $query->where('package_code', $packageCode)
                  ->orWhere('tracking_number', $packageCode);
        })->first();

        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => "Colis {$packageCode} introuvable",
            ], 404);
        }

        // Vérifier que le colis est en RETURN_IN_PROGRESS
        if ($package->status !== 'RETURN_IN_PROGRESS') {
            return response()->json([
                'success' => false,
                'message' => "Ce colis n'est pas en retour (statut: {$package->status})",
                'current_status' => $package->status,
            ], 422);
        }

        // Vérifier si déjà scanné dans cette session
        $scannedPackages = $sessionData['scanned_packages'] ?? [];

        // Vérifier si le code existe déjà
        foreach ($scannedPackages as $scanned) {
            if (($scanned['package_code'] ?? $scanned['code']) === $package->package_code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce colis a déjà été scanné',
                    'already_scanned' => true,
                ], 422);
            }
        }

        // Ajouter le colis à la session (format compatible avec scan normal)
        $scannedPackages[] = [
            'id' => $package->id,
            'package_code' => $package->package_code,
            'code' => $package->package_code,
            'tracking_number' => $package->tracking_number,
            'cod_amount' => $package->cod_amount,
            'sender_id' => $package->sender_id,
            'sender_name' => $package->sender->name ?? 'N/A',
            'return_reason' => $package->return_reason,
            'scanned_at' => now()->toDateTimeString(),
        ];

        $sessionData['scanned_packages'] = $scannedPackages;
        $sessionData['last_activity'] = now();
        Cache::put("depot_session_{$sessionId}", $sessionData, 8 * 60 * 60);

        Log::info('Colis retour scanné', [
            'session_id' => $sessionId,
            'package_id' => $package->id,
            'package_code' => $package->package_code,
            'depot_manager' => $sessionData['depot_manager_name'],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Colis {$package->package_code} scanné avec succès",
            'package' => [
                'code' => $package->package_code,
                'tracking' => $package->tracking_number,
                'cod' => number_format($package->cod_amount, 2),
                'sender' => $package->sender->name ?? 'N/A',
                'reason' => $package->return_reason,
            ],
            'total_scanned' => count($scannedPackages),
        ]);
    }

    /**
     * API: Obtenir l'état de la session
     */
    public function getSessionStatus($sessionId)
    {
        $sessionData = Cache::get("depot_session_{$sessionId}");

        if (!$sessionData) {
            return response()->json([
                'exists' => false,
                'active' => false,
            ]);
        }

        $scannedPackages = $sessionData['scanned_packages'] ?? [];

        return response()->json([
            'exists' => true,
            'active' => ($sessionData['status'] ?? 'waiting') !== 'completed',
            'depot_manager' => $sessionData['depot_manager_name'],
            'total_packages' => count($scannedPackages),
            'packages' => array_map(function($pkg) {
                return [
                    'code' => $pkg['package_code'] ?? $pkg['code'],
                    'sender' => $pkg['sender_name'] ?? 'N/A',
                    'cod' => number_format($pkg['cod_amount'] ?? 0, 2),
                    'reason' => $pkg['return_reason'] ?? '',
                    'scanned_at' => $pkg['scanned_at'],
                ];
            }, $scannedPackages),
            'last_activity' => $sessionData['last_activity']->diffForHumans(),
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

                // Créer le colis retour
                $returnPackage = ReturnPackage::create([
                    'original_package_id' => $originalPackage->id,
                    'return_package_code' => ReturnPackage::generateReturnCode(),
                    'cod' => 0, // Pas de COD sur les retours
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

                // Lier le colis retour au colis original
                $originalPackage->update([
                    'return_package_id' => $returnPackage->id,
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
        $returnPackages = ReturnPackage::with(['originalPackage', 'createdBy', 'assignedDeliverer'])
            ->latest()
            ->paginate(20);

        return view('depot.returns.manage', [
            'returnPackages' => $returnPackages,
        ]);
    }

    /**
     * Détails d'un colis retour
     */
    public function showReturnPackage(ReturnPackage $returnPackage)
    {
        $returnPackage->load(['originalPackage.sender', 'createdBy', 'assignedDeliverer']);

        return view('depot.returns.show', [
            'returnPackage' => $returnPackage,
        ]);
    }

    /**
     * Imprimer le bordereau d'un colis retour
     */
    public function printReturnLabel(ReturnPackage $returnPackage)
    {
        // Marquer comme imprimé
        $returnPackage->markAsPrinted();

        return view('depot.returns.print-label', [
            'returnPackage' => $returnPackage,
        ]);
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
