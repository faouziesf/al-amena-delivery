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
     * Créer des colis de test
     */
    public function createTestPackages()
    {
        $testCodes = [
            'TEST_001',
            'TEST_002',
            'TEST-003',
            'TEST004',
            'test_005',
        ];

        $created = [];

        foreach ($testCodes as $code) {
            // Vérifier si existe déjà
            $exists = DB::table('packages')->where('package_code', $code)->exists();
            
            if (!$exists) {
                // Créer un colis de test minimal
                DB::table('packages')->insert([
                    'package_code' => $code,
                    'sender_id' => 1, // Assumant qu'un user avec ID 1 existe
                    'status' => 'CREATED',
                    'cod_amount' => 0,
                    'delivery_fee' => 0,
                    'return_fee' => 0,
                    'delivery_attempts' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $created[] = $code;
            }
        }

        return response()->json([
            'message' => 'Colis de test créés',
            'created_packages' => $created,
            'note' => 'Utilisez ces codes pour tester le scan'
        ]);
    }
}
