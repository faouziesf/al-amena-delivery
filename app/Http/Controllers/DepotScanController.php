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
    public function dashboard()
    {
        // Générer un ID de session unique
        $sessionId = Str::uuid();
        
        // Stocker la session en cache pour 8 heures
        Cache::put("depot_session_{$sessionId}", [
            'created_at' => now(),
            'status' => 'waiting',
            'scanned_packages' => []
        ], 8 * 60 * 60);

        return view('depot.scan-dashboard', compact('sessionId'));
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
        // CORRECTION : Accepter TOUS les statuts sauf DELIVERED, PAID, CANCELLED, RETURNED
        $packages = DB::table('packages')
            ->whereNotIn('status', ['DELIVERED', 'PAID', 'CANCELLED', 'RETURNED', 'REFUSED', 'DELIVERED_PAID'])
            ->select('id', 'package_code as c', 'status as s')
            ->get()
            ->map(function($pkg) {
                return [
                    'id' => $pkg->id,
                    'c' => $pkg->c, // Code principal
                    's' => $pkg->s, // Statut
                ];
            });

        return view('depot.phone-scanner', compact('sessionId', 'packages'));
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
            'total_scanned' => count($session['scanned_packages'] ?? [])
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
        
        // CORRECTION CRITIQUE : Accepter TOUS les statuts sauf DELIVERED, PAID, CANCELLED
        $acceptedStatuses = ['CREATED', 'UNAVAILABLE', 'VERIFIED', 'AVAILABLE', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERING', 'OUT_FOR_DELIVERY'];
        
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
            'debug' => 'Vérifiez que le colis existe avec statut CREATED, UNAVAILABLE ou VERIFIED'
        ], 404);
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
            // Mettre à jour tous les colis scannés à AT_DEPOT (pas AVAILABLE)
            $packageCode = $pkg['package_code'] ?? $pkg['code'];
            
            $package = DB::table('packages')
                ->where('package_code', $packageCode)
                ->first();

            if ($package) {
                DB::table('packages')
                    ->where('id', $package->id)
                    ->update([
                        'status' => 'AT_DEPOT', // CORRECTION : AT_DEPOT au lieu de AVAILABLE
                        'updated_at' => now()
                    ]);
                
                $updatedPackages[] = [
                    'code' => $packageCode,
                    'package_code' => $packageCode,
                    'tracking_number' => $packageCode,
                    'old_status' => $package->status,
                    'new_status' => 'AT_DEPOT',
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

        $message = "✅ {$successCount} colis validés et marqués AT_DEPOT (au dépôt)";
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
     * Terminer la session (quand PC rafraîchi ou quitté)
     */
    public function terminateSession($sessionId)
    {
        $session = Cache::get("depot_session_{$sessionId}");
        
        if ($session) {
            // Marquer la session comme terminée
            $session['status'] = 'completed';
            $session['terminated_at'] = now();
            $session['terminated_reason'] = 'PC dashboard fermé ou rafraîchi';
            
            // Garder en cache 1 heure pour historique
            Cache::put("depot_session_{$sessionId}", $session, 60);
        }

        return response()->json([
            'success' => true,
            'message' => 'Session terminée'
        ]);
    }
}
