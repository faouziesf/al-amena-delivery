<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur de DEBUG pour le scan dépôt
 * À utiliser temporairement pour diagnostiquer les problèmes
 */
class DepotScanDebugController extends Controller
{
    /**
     * Afficher les informations de debug sur les colis
     */
    public function debugPackages()
    {
        // Compter les colis par statut
        $packagesByStatus = DB::table('packages')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Obtenir quelques exemples de colis scannables (TOUS sauf DELIVERED, PAID, etc.)
        $samplePackages = DB::table('packages')
            ->whereNotIn('status', ['DELIVERED', 'PAID', 'CANCELLED', 'RETURNED', 'REFUSED', 'DELIVERED_PAID'])
            ->select('id', 'package_code', 'status')
            ->limit(20)
            ->get();

        // Total de colis
        $totalPackages = DB::table('packages')->count();

        // Colis scannables (statuts acceptés)
        $scannablePackages = DB::table('packages')
            ->whereNotIn('status', ['DELIVERED', 'PAID', 'CANCELLED', 'RETURNED', 'REFUSED', 'DELIVERED_PAID'])
            ->count();

        return response()->json([
            'total_packages' => $totalPackages,
            'scannable_packages' => $scannablePackages,
            'packages_by_status' => $packagesByStatus,
            'sample_packages' => $samplePackages,
            'message' => 'Utilisez ces codes pour tester le scan',
        ]);
    }

    /**
     * Tester la recherche d'un code spécifique
     */
    public function testSearch(Request $request)
    {
        $code = $request->input('code');
        
        if (!$code) {
            return response()->json(['error' => 'Paramètre "code" requis'], 400);
        }

        $originalCode = trim($code);
        $code = strtoupper($originalCode);
        
        // Essayer toutes les variantes
        $searchVariants = [
            $code,
            str_replace('_', '', $code),
            str_replace('-', '', $code),
            str_replace(['_', '-', ' '], '', $code),
            strtolower($code),
            $originalCode,
        ];
        
        $searchVariants = array_unique($searchVariants);
        
        $results = [];
        
        foreach ($searchVariants as $variant) {
            $package = DB::table('packages')
                ->where('package_code', $variant)
                ->select('id', 'package_code', 'status')
                ->first();
            
            $results[$variant] = $package ? [
                'found' => true,
                'package_code' => $package->package_code,
                'status' => $package->status,
                'scannable' => !in_array($package->status, ['DELIVERED', 'PAID', 'CANCELLED', 'RETURNED', 'REFUSED', 'DELIVERED_PAID'])
            ] : [
                'found' => false
            ];
        }
        
        // Recherche LIKE
        $cleanCode = str_replace(['_', '-', ' '], '', $code);
        $likeResult = DB::table('packages')
            ->where(DB::raw('REPLACE(REPLACE(REPLACE(UPPER(package_code), "_", ""), "-", ""), " ", "")'), $cleanCode)
            ->select('id', 'package_code', 'status')
            ->first();
        
        return response()->json([
            'searched_code' => $code,
            'original_code' => $originalCode,
            'variants_tested' => $results,
            'like_search' => $likeResult ? [
                'found' => true,
                'package_code' => $likeResult->package_code,
                'status' => $likeResult->status,
                'scannable' => !in_array($likeResult->status, ['DELIVERED', 'PAID', 'CANCELLED', 'RETURNED', 'REFUSED', 'DELIVERED_PAID'])
            ] : ['found' => false],
        ]);
    }

    /**
     * Créer des colis de test avec différents statuts
     * ROUTE: POST /depot/debug/create-test-packages
     */
    public function createTestPackages()
    {
        // Colis de test avec différents statuts
        $testPackages = [
            ['code' => 'DEPOT_TEST_001', 'status' => 'CREATED'],
            ['code' => 'DEPOT_TEST_002', 'status' => 'AWAITING_PICKUP'],
            ['code' => 'DEPOT_TEST_003', 'status' => 'IN_TRANSIT'],
            ['code' => 'DEPOT_TEST_004', 'status' => 'OUT_FOR_DELIVERY'],
            ['code' => 'DEPOT_TEST_005', 'status' => 'DELIVERED'], // Ne devrait PAS être scannable
            
            ['code' => 'RETURN_TEST_001', 'status' => 'AWAITING_RETURN'],
            ['code' => 'RETURN_TEST_002', 'status' => 'RETURN_IN_PROGRESS'],
            ['code' => 'RETURN_TEST_003', 'status' => 'RETURN_IN_PROGRESS'],
            ['code' => 'RETURN_TEST_004', 'status' => 'DELIVERED'], // Statut invalide pour retours
            ['code' => 'RETURN_TEST_005', 'status' => 'AT_DEPOT'], // Statut invalide pour retours
        ];

        $created = [];
        $skipped = [];

        // Trouver un sender_id valide
        $senderId = DB::table('users')->where('role', 'client')->first()->id ?? 1;

        foreach ($testPackages as $pkg) {
            // Vérifier si existe déjà
            $exists = DB::table('packages')->where('package_code', $pkg['code'])->exists();
            
            if (!$exists) {
                DB::table('packages')->insert([
                    'package_code' => $pkg['code'],
                    'sender_id' => $senderId,
                    'status' => $pkg['status'],
                    'cod_amount' => 50.00,
                    'delivery_fee' => 7.00,
                    'return_fee' => 7.00,
                    'delivery_attempts' => 0,
                    'recipient_name' => 'Test Recipient',
                    'recipient_phone' => '12345678',
                    'recipient_address' => 'Test Address',
                    'recipient_city' => 'Tunis',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $created[] = $pkg;
            } else {
                $skipped[] = $pkg['code'];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Colis de test créés avec succès',
            'created_count' => count($created),
            'created_packages' => $created,
            'skipped' => $skipped,
            'instructions' => [
                'depot_scan' => 'Utilisez DEPOT_TEST_001 à DEPOT_TEST_004 pour tester le scan dépôt',
                'depot_invalid' => 'DEPOT_TEST_005 (DELIVERED) devrait être rejeté',
                'return_scan' => 'Utilisez RETURN_TEST_002 et RETURN_TEST_003 pour tester le scan retours',
                'return_invalid' => 'RETURN_TEST_004 et RETURN_TEST_005 devraient afficher le statut invalide'
            ]
        ]);
    }

    /**
     * Obtenir l'état d'une session depuis le cache
     * ROUTE: GET /depot/debug/session-status/{sessionId}
     */
    public function sessionStatus($sessionId)
    {
        $session = \Illuminate\Support\Facades\Cache::get("depot_session_{$sessionId}");

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session non trouvée',
                'sessionId' => $sessionId
            ], 404);
        }

        return response()->json([
            'success' => true,
            'sessionId' => $sessionId,
            'session_data' => [
                'status' => $session['status'] ?? 'unknown',
                'scan_type' => $session['scan_type'] ?? 'depot',
                'depot_manager' => $session['depot_manager_name'] ?? 'N/A',
                'session_code' => $session['session_code'] ?? 'N/A',
                'created_at' => $session['created_at'] ?? null,
                'total_scanned' => count($session['scanned_packages'] ?? []),
                'scanned_packages' => $session['scanned_packages'] ?? [],
                'last_heartbeat' => $session['last_heartbeat'] ?? null,
            ]
        ]);
    }

    /**
     * Lister toutes les sessions actives
     * ROUTE: GET /depot/debug/active-sessions
     */
    public function activeSessions()
    {
        // Note: Cette méthode nécessite Redis ou une méthode pour lister toutes les clés
        // Pour file cache, c'est plus complexe
        
        return response()->json([
            'message' => 'Fonctionnalité limitée avec file cache',
            'note' => 'Utilisez sessionStatus avec un sessionId spécifique',
            'example' => '/depot/debug/session-status/{uuid}'
        ]);
    }

    /**
     * Nettoyer les colis de test
     * ROUTE: DELETE /depot/debug/clean-test-packages
     */
    public function cleanTestPackages()
    {
        $deleted = DB::table('packages')
            ->where('package_code', 'LIKE', 'DEPOT_TEST_%')
            ->orWhere('package_code', 'LIKE', 'RETURN_TEST_%')
            ->orWhere('package_code', 'LIKE', 'TEST_%')
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Colis de test supprimés',
            'deleted_count' => $deleted
        ]);
    }
}
