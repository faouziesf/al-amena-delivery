<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DelivererApiController extends Controller
{
    /**
     * Obtenir les collectes COD du jour
     */
    public function getCodToday(Request $request)
    {
        $deliverer = Auth::user();
        $page = $request->get('page', 1);
        $perPage = 20;

        // Récupérer les colis livrés avec COD aujourd'hui
        $query = Package::where('assigned_deliverer_id', $deliverer->id)
            ->where('status', 'DELIVERED')
            ->where('cod_amount', '>', 0)
            ->whereDate('delivered_at', today())
            ->orderBy('delivered_at', 'desc');

        $total = $query->count();
        $transactions = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(function($package) {
                return [
                    'id' => $package->id,
                    'package_code' => $package->package_code,
                    'client_name' => $package->sender->name ?? 'Client',
                    'amount' => floatval($package->cod_amount),
                    'delivery_address' => $package->recipient_address,
                    'delivered_at' => $package->delivered_at->toISOString(),
                ];
            });

        $totalCod = Package::where('assigned_deliverer_id', $deliverer->id)
            ->where('status', 'DELIVERED')
            ->where('cod_amount', '>', 0)
            ->whereDate('delivered_at', today())
            ->sum('cod_amount');

        $deliveredCount = Package::where('assigned_deliverer_id', $deliverer->id)
            ->where('status', 'DELIVERED')
            ->whereDate('delivered_at', today())
            ->count();

        return response()->json([
            'success' => true,
            'total_cod' => floatval($totalCod),
            'delivered_count' => $deliveredCount,
            'cod_count' => $total,
            'transactions' => $transactions,
            'has_more' => $total > ($page * $perPage),
            'current_page' => $page,
        ]);
    }

    /**
     * Obtenir le solde wallet simple
     */
    public function getWalletBalance()
    {
        $deliverer = Auth::user();
        
        // COD non remis du jour
        $balance = Package::where('assigned_deliverer_id', $deliverer->id)
            ->where('status', 'DELIVERED')
            ->where('cod_amount', '>', 0)
            ->whereDate('delivered_at', today())
            ->sum('cod_amount');

        return response()->json([
            'success' => true,
            'balance' => floatval($balance),
            'currency' => 'TND',
            'updated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Vérifier un code scanné
     */
    public function verifyScan(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $code = trim($request->code);
        
        // Chercher le colis par code
        $package = Package::where('package_code', $code)
            ->orWhere('tracking_number', $code)
            ->first();

        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Colis introuvable',
                'code' => $code,
            ]);
        }

        $deliverer = Auth::user();

        // Vérifier si le livreur peut scanner ce colis
        if ($package->assigned_deliverer_id != $deliverer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Ce colis n\'est pas assigné à vous',
                'package_code' => $package->package_code,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Colis trouvé',
            'package_id' => $package->id,
            'package_code' => $package->package_code,
            'tracking_number' => $package->tracking_number,
            'status' => $package->status,
            'cod_amount' => floatval($package->cod_amount),
            'recipient_name' => $package->recipient_name,
            'recipient_phone' => $package->recipient_phone,
            'recipient_address' => $package->recipient_address,
        ]);
    }

    /**
     * Statistiques du dashboard
     */
    public function getDashboardStats()
    {
        $deliverer = Auth::user();

        $stats = [
            'pending' => Package::where('assigned_deliverer_id', $deliverer->id)
                ->whereIn('status', ['ACCEPTED', 'PICKED_UP'])
                ->count(),
            
            'delivered_today' => Package::where('assigned_deliverer_id', $deliverer->id)
                ->where('status', 'DELIVERED')
                ->whereDate('delivered_at', today())
                ->count(),
            
            'cod_today' => Package::where('assigned_deliverer_id', $deliverer->id)
                ->where('status', 'DELIVERED')
                ->where('cod_amount', '>', 0)
                ->whereDate('delivered_at', today())
                ->sum('cod_amount'),
            
            'returned_today' => Package::where('assigned_deliverer_id', $deliverer->id)
                ->where('status', 'RETURNED')
                ->whereDate('returned_at', today())
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'updated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Obtenir les colis en cours
     */
    public function getPendingPackages()
    {
        $deliverer = Auth::user();

        $packages = Package::where('assigned_deliverer_id', $deliverer->id)
            ->whereIn('status', ['ACCEPTED', 'PICKED_UP', 'UNAVAILABLE'])
            ->with(['sender:id,name', 'delegation:id,name'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($package) {
                return [
                    'id' => $package->id,
                    'package_code' => $package->package_code,
                    'tracking_number' => $package->tracking_number,
                    'status' => $package->status,
                    'recipient_name' => $package->recipient_name,
                    'recipient_phone' => $package->recipient_phone,
                    'recipient_address' => $package->recipient_address,
                    'cod_amount' => floatval($package->cod_amount),
                    'attempt_count' => $package->delivery_attempts ?? 0,
                    'sender_name' => $package->sender->name ?? null,
                    'delegation' => $package->delegation->name ?? null,
                ];
            });

        return response()->json([
            'success' => true,
            'packages' => $packages,
            'count' => $packages->count(),
        ]);
    }

    /**
     * Mettre à jour la localisation du livreur
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'nullable|numeric',
        ]);

        $deliverer = Auth::user();

        // Enregistrer la position (à implémenter selon votre logique)
        // Par exemple dans une table deliverer_locations

        return response()->json([
            'success' => true,
            'message' => 'Position mise à jour',
            'timestamp' => now()->toISOString(),
        ]);
    }
}
