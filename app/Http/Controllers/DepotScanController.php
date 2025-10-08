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
     */
    public function scanner($sessionId)
    {
        // Vérifier que la session existe
        $session = Cache::get("depot_session_{$sessionId}");
        
        if (!$session) {
            return redirect()->route('depot.scan')
                ->with('error', 'Session expirée ou invalide');
        }

        // Marquer la session comme connectée
        $session['status'] = 'connected';
        $session['connected_at'] = now();
        Cache::put("depot_session_{$sessionId}", $session, 8 * 60 * 60);

        // Charger TOUS les colis valides pour scan dépôt (validation locale)
        // Statuts acceptés: CREATED, UNAVAILABLE, VERIFIED
        $packages = DB::table('packages')
            ->whereIn('status', ['CREATED', 'UNAVAILABLE', 'VERIFIED'])
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
     * API - Scanner un colis depuis le téléphone
     */
    public function scanPackage(Request $request, $sessionId)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $session = Cache::get("depot_session_{$sessionId}");
        
        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        if ($session['status'] !== 'connected') {
            return response()->json(['error' => 'Session not connected'], 400);
        }

        $code = trim($request->code);
        
        // Vérifier si le colis existe dans la base de données
        $package = DB::table('packages')
            ->where('tracking_number', $code)
            ->orWhere('barcode', $code)
            ->first();

        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Colis Introuvable',
                'code' => $code
            ]);
        }

        // Vérifier si déjà scanné dans cette session
        $scannedPackages = $session['scanned_packages'] ?? [];
        $alreadyScanned = collect($scannedPackages)->contains('code', $code);

        if ($alreadyScanned) {
            return response()->json([
                'success' => false,
                'message' => 'Déjà Scanné',
                'code' => $code
            ]);
        }

        // Vérifier le statut du colis
        if (!in_array($package->status, ['CREATED', 'AVAILABLE', 'PICKED_UP', 'DELIVERING', 'OUT_FOR_DELIVERY', 'IN_TRANSIT', 'UNKNOWN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Statut Invalide',
                'code' => $code,
                'status' => $package->status
            ]);
        }

        // Ajouter le colis à la liste des scannés
        $scannedPackages[] = [
            'code' => $code,
            'tracking_number' => $package->tracking_number,
            'status' => $package->status,
            'scanned_at' => now()->toISOString(),
            'scanned_time' => now()->format('H:i:s')
        ];

        // Mettre à jour la session
        $session['scanned_packages'] = $scannedPackages;
        $session['last_scan'] = now();
        Cache::put("depot_session_{$sessionId}", $session, 8 * 60 * 60);

        // Optionnel : Mettre à jour le statut du colis
        DB::table('packages')
            ->where('id', $package->id)
            ->update([
                'status' => 'AT_DEPOT',
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Scanné avec Succès',
            'code' => $code,
            'tracking_number' => $package->tracking_number,
            'total_scanned' => count($scannedPackages)
        ]);
    }

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
     * Ajouter un code scanné au cache (SANS mise à jour DB)
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

        $code = trim($request->code);
        
        // Rechercher le colis
        $package = DB::table('packages')
            ->where('package_code', $code)
            ->whereIn('status', ['CREATED', 'UNAVAILABLE', 'VERIFIED'])
            ->select('id', 'package_code', 'status')
            ->first();

        if ($package) {
            // Ajouter au cache (SANS mettre à jour la DB)
            $scannedPackages = $session['scanned_packages'] ?? [];
            
            $scannedPackages[] = [
                'code' => $code,
                'package_code' => $package->package_code,
                'tracking_number' => $package->package_code, // Utiliser package_code
                'status' => $package->status,
                'scanned_at' => now()->toISOString(),
                'scanned_time' => now()->format('H:i:s')
            ];
            
            $session['scanned_packages'] = $scannedPackages;
            $session['last_scan'] = now();
            Cache::put("depot_session_{$sessionId}", $session, 8 * 60 * 60);
            
            return response()->json([
                'success' => true,
                'code' => $code,
                'total' => count($scannedPackages)
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Colis non trouvé'
        ]);
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
                ->whereIn('status', ['CREATED', 'UNAVAILABLE', 'VERIFIED'])
                ->first();

            if ($package) {
                DB::table('packages')
                    ->where('id', $package->id)
                    ->update([
                        'status' => 'AVAILABLE',
                        'updated_at' => now()
                    ]);
                
                $updatedPackages[] = [
                    'code' => $packageCode,
                    'package_code' => $packageCode,
                    'tracking_number' => $packageCode,
                    'old_status' => $package->status,
                    'new_status' => 'AVAILABLE',
                    'scanned_time' => $pkg['scanned_time'] ?? now()->format('H:i:s')
                ];
                
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        // RÉINITIALISER la liste après validation (session reste active)
        $session['scanned_packages'] = []; // Vider la liste pour pouvoir scanner de nouveaux colis
        $session['last_validated_packages'] = $updatedPackages; // Garder trace des derniers validés
        $session['validated_at'] = now();
        $session['validated_count'] = $successCount;
        $session['total_validated'] = ($session['total_validated'] ?? 0) + $successCount;
        Cache::put("depot_session_{$sessionId}", $session, 8 * 60 * 60);

        $message = "✅ {$successCount} colis validés et marqués AVAILABLE (disponibles pour livraison)";
        if ($errorCount > 0) {
            $message .= " ({$errorCount} erreurs)";
        }

        return redirect()->back()->with('success', $message);
    }
}
