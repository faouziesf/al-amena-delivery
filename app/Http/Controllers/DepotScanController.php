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
        // Statuts acceptés: COLLECTED, IN_TRANSIT (pour arrivée au dépôt)
        $packages = DB::table('packages')
            ->whereIn('status', ['COLLECTED', 'IN_TRANSIT', 'PENDING'])
            ->select('id', 'package_code as c', 'tracking_number as c2', 'status as s')
            ->get()
            ->map(function($pkg) {
                return [
                    'id' => $pkg->id,
                    'c' => $pkg->c, // Code principal
                    'c2' => $pkg->c2, // Code alternatif (tracking_number)
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
        if (!in_array($package->status, ['PENDING', 'COLLECTED', 'IN_TRANSIT', 'AT_DEPOT'])) {
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
     * Soumettre les codes scannés - MÉTHODE DIRECTE (pas d'API)
     */
    public function submitScans(Request $request, $sessionId)
    {
        $request->validate([
            'codes' => 'required|string',
        ]);

        // Décoder les codes JSON
        $codes = json_decode($request->codes, true);
        
        if (!is_array($codes) || empty($codes)) {
            return redirect()->back()->with('error', 'Aucun code à traiter');
        }

        $session = Cache::get("depot_session_{$sessionId}");
        
        if (!$session) {
            return redirect()->route('depot.scan')->with('error', 'Session expirée');
        }

        $scannedPackages = [];
        $errors = [];
        $successCount = 0;

        foreach ($codes as $code) {
            // Rechercher le colis par code ou tracking_number
            $package = DB::table('packages')
                ->where(function($query) use ($code) {
                    $query->where('package_code', $code)
                          ->orWhere('tracking_number', $code);
                })
                ->whereIn('status', ['COLLECTED', 'IN_TRANSIT', 'PENDING'])
                ->first();

            if ($package) {
                // Mettre à jour le statut à AT_DEPOT
                DB::table('packages')
                    ->where('id', $package->id)
                    ->update([
                        'status' => 'AT_DEPOT',
                        'updated_at' => now()
                    ]);

                $scannedPackages[] = [
                    'code' => $code,
                    'package_code' => $package->package_code,
                    'tracking_number' => $package->tracking_number,
                    'old_status' => $package->status,
                    'new_status' => 'AT_DEPOT',
                    'scanned_at' => now()->toISOString(),
                    'scanned_time' => now()->format('H:i:s')
                ];
                
                $successCount++;
            } else {
                $errors[] = $code;
            }
        }

        // Mettre à jour la session cache
        $session['scanned_packages'] = array_merge(
            $session['scanned_packages'] ?? [],
            $scannedPackages
        );
        $session['last_scan'] = now();
        Cache::put("depot_session_{$sessionId}", $session, 8 * 60 * 60);

        // Message de retour
        $message = "$successCount colis enregistrés au dépôt";
        if (!empty($errors)) {
            $message .= " (" . count($errors) . " erreurs)";
        }

        return redirect()->route('depot.scan.phone', $sessionId)
            ->with('success', $message);
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
}
