<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DepotScanController extends Controller
{
    /**
     * Afficher le tableau de bord PC avec QR code
     */
    public function dashboard(Request $request)
    {
        // Utiliser le nom de l'utilisateur connecté s'il existe
        $depotManagerName = null;

        if (auth()->check()) {
            // Utilisateur connecté - utiliser son nom
            $user = auth()->user();
            $depotManagerName = $user->name;
        } else {
            // Pas connecté - utiliser le formulaire ou la session
            $depotManagerName = $request->input('depot_manager_name', session('depot_manager_name'));
        }

        // Si toujours pas de nom, afficher le formulaire
        if (!$depotManagerName) {
            return view('depot.select-manager');
        }

        // Sauvegarder en session pour la prochaine fois
        session(['depot_manager_name' => $depotManagerName]);

        // Générer un ID de session unique
        $sessionId = Str::uuid();

        // Générer un code de session de 8 chiffres
        $sessionCode = $this->generateSessionCode();

        // Stocker la session en cache pour 8 heures
        Cache::put("depot_session_{$sessionId}", [
            'created_at' => now(),
            'status' => 'waiting',
            'scanned_packages' => [],
            'depot_manager_name' => $depotManagerName,
            'session_code' => $sessionCode
        ], 8 * 60 * 60);

        // Stocker aussi par code pour accès rapide
        Cache::put("depot_code_{$sessionCode}", $sessionId, 8 * 60 * 60);

        \Log::info("Session créée", [
            'sessionId' => $sessionId,
            'sessionCode' => $sessionCode,
            'depot_manager_name' => $depotManagerName,
            'cache_keys' => [
                'session' => "depot_session_{$sessionId}",
                'code' => "depot_code_{$sessionCode}"
            ]
        ]);

        $scannerUrl = route('depot.scan.phone', $sessionId); // URL pour scan normal

        return view('depot.scan-dashboard', compact('sessionId', 'depotManagerName', 'sessionCode', 'scannerUrl'));
    }

    /**
     * Interface téléphone - Scanner après scan du QR code
     * MÉTHODE DIRECTE - Charge tous les colis pour validation locale
     * CORRECTION : Bloquer si session terminée
     */
    public function scanner($sessionId)
    {
        // Vérifier que la session existe
        $session = Cache::get("depot_session_{$sessionId}");
        
        if (!$session) {
            return view('depot.session-expired', [
                'message' => 'Session expirée ou invalide',
                'reason' => 'La session a expiré ou n\'existe pas'
            ]);
        }

        // CORRECTION : Bloquer si session terminée
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

        // Charger TOUS les colis valides pour scan dépôt (validation locale)
        // Statuts REFUSÉS: DELIVERED, PAID, VERIFIED, RETURNED, CANCELLED, REFUSED, DELIVERED_PAID
        $packages = DB::table('packages')
            ->whereNotIn('status', ['DELIVERED', 'PAID', 'VERIFIED', 'RETURNED', 'CANCELLED', 'REFUSED', 'DELIVERED_PAID'])
            ->select('id', 'package_code as c', 'status as s', 'depot_manager_name as d')
            ->get()
            ->map(function($pkg) use ($session) {
                return [
                    'id' => $pkg->id,
                    'c' => $pkg->c, // Code principal
                    's' => $pkg->s, // Statut
                    'd' => $pkg->d, // Nom du chef dépôt (si AT_DEPOT)
                    'current_depot' => $session['depot_manager_name'] ?? null
                ];
            });

        $depotManagerName = $session['depot_manager_name'] ?? 'Dépôt';

        return view('depot.phone-scanner', compact('sessionId', 'packages', 'depotManagerName'));
    }

    /**
     * API - Obtenir le statut de la session
     */
    public function getSessionStatus($sessionId)
    {
        $session = Cache::get("depot_session_{$sessionId}");

        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        return response()->json([
            'status' => $session['status'],
            'scanned_packages' => $session['scanned_packages'] ?? [],
            'total_scanned' => count($session['scanned_packages'] ?? []),
            'last_heartbeat' => $session['last_heartbeat'] ?? null
        ]);
    }

    /**
     * SUPPRIMÉ - Non utilisé dans la nouvelle approche directe
     */

    /**
     * API - Obtenir la liste des colis scannés
     */
    public function getScannedPackages($sessionId)
    {
        $session = Cache::get("depot_session_{$sessionId}");
        
        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        return response()->json([
            'packages' => $session['scanned_packages'] ?? [],
            'total' => count($session['scanned_packages'] ?? []),
            'status' => $session['status']
        ]);
    }

    /**
     * Ajouter un code scanné au cache (APPROCHE DIRECTE - SANS API)
     * Recherche intelligente avec support de toutes les variantes
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
            $code,                                          // Code original en majuscules
            str_replace('_', '', $code),                    // Sans underscore
            str_replace('-', '', $code),                    // Sans tiret
            str_replace(['_', '-', ' '], '', $code),       // Nettoyé complètement
            strtolower($code),                              // Minuscules
            $originalCode,                                  // Code original (casse préservée)
        ];
        
        // Supprimer les doublons
        $searchVariants = array_unique($searchVariants);
        
        // Statuts ACCEPTÉS pour scan dépôt (exclusion des statuts finaux)
        $rejectedStatuses = ['DELIVERED', 'PAID', 'VERIFIED', 'RETURNED', 'CANCELLED', 'REFUSED', 'DELIVERED_PAID'];
        $acceptedStatuses = ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP', 'OUT_FOR_DELIVERY', 'UNAVAILABLE', 'AT_DEPOT'];
        
        // Rechercher avec TOUTES les variantes
        foreach ($searchVariants as $variant) {
            $package = DB::table('packages')
                ->where('package_code', $variant)
                ->whereIn('status', $acceptedStatuses)
                ->select('id', 'package_code', 'status')
                ->first();
            
            if ($package) {
                break; // Trouvé !
            }
        }
        
        // Si toujours pas trouvé, essayer une recherche LIKE (plus permissive)
        if (!$package) {
            $cleanCode = str_replace(['_', '-', ' '], '', $code);
            $package = DB::table('packages')
                ->where(DB::raw('REPLACE(REPLACE(REPLACE(UPPER(package_code), "_", ""), "-", ""), " ", "")'), $cleanCode)
                ->whereIn('status', $acceptedStatuses)
                ->select('id', 'package_code', 'status')
                ->first();
        }

        if ($package) {
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
            
            return response()->json([
                'success' => true,
                'code' => $package->package_code,
                'message' => 'Colis trouvé : ' . $package->package_code,
                'total' => count($scannedPackages)
            ]);
        }
        
        // Pas trouvé - Retourner des infos de debug
        return response()->json([
            'success' => false,
            'message' => 'Colis non trouvé dans la base de données',
            'searched_code' => $code,
            'variants_tried' => $searchVariants,
            'debug' => 'Vérifiez que le colis existe avec un statut accepté (CREATED, AVAILABLE, ACCEPTED, PICKED_UP, OUT_FOR_DELIVERY, UNAVAILABLE, AT_DEPOT)'
        ], 404);
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
     * Exporter les résultats de scan
     */
    public function exportScan($sessionId)
    {
        $session = Cache::get("depot_session_{$sessionId}");
        
        if (!$session) {
            return redirect()->back()->with('error', 'Session introuvable');
        }

        $packages = $session['scanned_packages'] ?? [];
        
        if (empty($packages)) {
            return redirect()->back()->with('error', 'Aucun colis scanné');
        }

        $filename = 'scan_depot_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($packages) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, ['Code', 'Package Code', 'Tracking', 'Ancien Statut', 'Nouveau Statut', 'Heure Scan']);
            
            // Données
            foreach ($packages as $package) {
                fputcsv($file, [
                    $package['code'] ?? '',
                    $package['package_code'] ?? '',
                    $package['tracking_number'] ?? '',
                    $package['old_status'] ?? '',
                    $package['new_status'] ?? 'AT_DEPOT',
                    $package['scanned_time'] ?? ''
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Valider tous les colis scannés depuis le PC Dashboard (MISE À JOUR DB)
     * CORRECTION : Statut AT_DEPOT + Terminer session téléphone
     */
    public function validateAllFromPC($sessionId)
    {
        $session = Cache::get("depot_session_{$sessionId}");
        
        if (!$session) {
            return redirect()->route('depot.scan')->with('error', 'Session introuvable');
        }

        $scannedPackages = $session['scanned_packages'] ?? [];
        
        if (empty($scannedPackages)) {
            return redirect()->back()->with('error', 'Aucun colis à valider');
        }

        $successCount = 0;
        $errorCount = 0;
        $updatedPackages = [];

        foreach ($scannedPackages as $pkg) {
            // Mettre à jour tous les colis scannés à AT_DEPOT
            $packageCode = $pkg['package_code'] ?? $pkg['code'];

            $package = DB::table('packages')
                ->where('package_code', $packageCode)
                ->first();

            if ($package) {
                $depotManagerName = $session['depot_manager_name'] ?? 'Dépôt';

                // CORRECTION : Toujours mettre à jour vers AT_DEPOT avec le nouveau nom de dépôt
                DB::table('packages')
                    ->where('id', $package->id)
                    ->update([
                        'status' => 'AT_DEPOT',
                        'depot_manager_name' => $depotManagerName,
                        'updated_at' => now()
                    ]);

                \Log::info("Package updated to AT_DEPOT", [
                    'package_id' => $package->id,
                    'package_code' => $packageCode,
                    'old_status' => $package->status,
                    'new_status' => 'AT_DEPOT',
                    'depot_manager_name' => $depotManagerName
                ]);

                $updatedPackages[] = [
                    'code' => $packageCode,
                    'package_code' => $packageCode,
                    'tracking_number' => $packageCode,
                    'old_status' => $package->status,
                    'new_status' => "AT_DEPOT ({$depotManagerName})",
                    'scanned_time' => $pkg['scanned_time'] ?? now()->format('H:i:s')
                ];

                $successCount++;
            } else {
                $errorCount++;
            }
        }

        // TERMINER la session téléphone (marquer comme terminée)
        $session['status'] = 'completed';
        $session['scanned_packages'] = [];
        $session['last_validated_packages'] = $updatedPackages;
        $session['validated_at'] = now();
        $session['validated_count'] = $successCount;
        $session['total_validated'] = ($session['total_validated'] ?? 0) + $successCount;
        $session['completed_at'] = now();
        
        // Garder en cache 1 heure pour historique, mais session terminée
        Cache::put("depot_session_{$sessionId}", $session, 60);

        $depotManagerName = $session['depot_manager_name'] ?? 'Dépôt';
        $message = "✅ {$successCount} colis validés et marqués AT_DEPOT ({$depotManagerName})";
        if ($errorCount > 0) {
            $message .= " ({$errorCount} erreurs)";
        }

        // CORRECTION NGROK : Retourner JSON pour éviter page noire
        // Si requête AJAX, retourner JSON
        if (request()->wantsJson() || request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'validated_count' => $successCount,
                'error_count' => $errorCount
            ]);
        }

        // Sinon, redirection classique
        return redirect()->back()->with('success', $message);
    }

    /**
     * Générer un code de session unique de 8 chiffres
     */
    private function generateSessionCode()
    {
        do {
            $code = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
            $exists = Cache::has("depot_code_{$code}");
        } while ($exists);

        return $code;
    }

    /**
     * Page de saisie manuelle du code de session (INTERFACE PUBLIQUE)
     */
    public function enterCode(Request $request)
    {
        return view('depot.enter-code');
    }

    /**
     * Valider le code de session via GET (pour ngrok - évite CSRF)
     */
    public function validateCodeGet($code)
    {
        // Nettoyer le code
        $code = preg_replace('/[^0-9]/', '', $code);

        if (strlen($code) !== 8) {
            return redirect()->route('depot.enter.code')->withErrors(['code' => 'Code invalide']);
        }

        // Récupérer le sessionId à partir du code
        $sessionId = Cache::get("depot_code_{$code}");

        \Log::info("Code validation GET", [
            'code' => $code,
            'sessionId' => $sessionId
        ]);

        if (!$sessionId) {
            return redirect()->route('depot.enter.code')->withErrors(['code' => "Code invalide ou expiré (Code: {$code})"]);
        }

        // Vérifier que la session existe
        $session = Cache::get("depot_session_{$sessionId}");

        if (!$session) {
            return redirect()->route('depot.enter.code')->withErrors(['code' => 'Session expirée']);
        }

        if (isset($session['status']) && $session['status'] === 'completed') {
            return redirect()->route('depot.enter.code')->withErrors(['code' => 'Session terminée. Entrez un nouveau code.']);
        }

        // Rediriger vers le scanner
        return redirect()->route('depot.scan.phone', ['sessionId' => $sessionId]);
    }

    /**
     * Valider le code de session via POST
     */
    public function validateCode(Request $request)
    {
        $code = $request->input('code');

        // Nettoyer le code (enlever espaces, etc.)
        $code = preg_replace('/[^0-9]/', '', $code);

        if (strlen($code) !== 8) {
            return back()->withErrors(['code' => 'Le code doit contenir 8 chiffres'])->withInput();
        }

        // Récupérer le sessionId à partir du code
        $sessionId = Cache::get("depot_code_{$code}");

        \Log::info("Code validation", [
            'code' => $code,
            'cache_key' => "depot_code_{$code}",
            'sessionId' => $sessionId
        ]);

        if (!$sessionId) {
            return back()->withErrors(['code' => "Code invalide ou expiré (Code: {$code})"])->withInput();
        }

        // Vérifier que la session existe
        $session = Cache::get("depot_session_{$sessionId}");

        \Log::info("Session check", [
            'sessionId' => $sessionId,
            'session_exists' => !is_null($session),
            'session_data' => $session
        ]);

        if (!$session) {
            return back()->withErrors(['code' => 'Session expirée. Veuillez demander un nouveau code.'])->withInput();
        }

        // Vérifier que la session n'est pas terminée
        if (isset($session['status']) && $session['status'] === 'completed') {
            return back()->withErrors(['code' => 'Cette session a déjà été terminée. Entrez un nouveau code.'])->withInput();
        }

        // Rediriger vers le scanner avec le sessionId
        return redirect()->route('depot.scan.phone', ['sessionId' => $sessionId]);
    }

    /**
     * Vérifier l'activité de la session (appelé périodiquement)
     */
    public function checkActivity($sessionId)
    {
        $session = Cache::get("depot_session_{$sessionId}");

        if (!$session) {
            return response()->json([
                'active' => false,
                'reason' => 'expired'
            ]);
        }

        // Vérifier si session terminée
        if (isset($session['status']) && $session['status'] === 'completed') {
            return response()->json([
                'active' => false,
                'reason' => 'completed'
            ]);
        }

        // Vérifier inactivité (30 minutes)
        $lastActivity = $session['last_activity'] ?? $session['created_at'];
        if (now()->diffInMinutes($lastActivity) > 30) {
            // Terminer la session automatiquement
            $session['status'] = 'completed';
            $session['completed_reason'] = 'inactivity';
            Cache::put("depot_session_{$sessionId}", $session, 60);

            return response()->json([
                'active' => false,
                'reason' => 'inactivity'
            ]);
        }

        return response()->json([
            'active' => true,
            'last_activity' => $lastActivity
        ]);
    }

    /**
     * Mettre à jour l'activité de la session
     */
    public function updateActivity($sessionId)
    {
        $session = Cache::get("depot_session_{$sessionId}");

        if ($session) {
            $session['last_activity'] = now();
            Cache::put("depot_session_{$sessionId}", $session, 8 * 60 * 60);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }
}
