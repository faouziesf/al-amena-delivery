<?php

namespace App\Http\Controllers\DepotManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Package;
use App\Models\Delegation;

class DepotManagerDashboardController extends Controller
{
    /**
     * Dashboard principal du chef dépôt
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        // Statistiques générales du dépôt
        $stats = $user->getDepotStats();

        // Livreurs gérés
        $deliverers = $user->getManagedDeliverers();

        // Colis urgents dans les gouvernorats gérés
        $urgentPackages = Package::whereIn('assigned_deliverer_id', $deliverers->pluck('id'))
                                ->whereIn('status', ['PICKED_UP', 'UNAVAILABLE'])
                                ->where('delivery_attempts', '>=', 3)
                                ->with(['assignedDeliverer', 'delegationFrom', 'delegationTo'])
                                ->orderBy('updated_at', 'asc')
                                ->limit(10)
                                ->get();

        // Activité récente dans le dépôt
        $recentActivity = Package::whereIn('assigned_deliverer_id', $deliverers->pluck('id'))
                                ->whereIn('status', ['DELIVERED', 'RETURNED', 'PICKED_UP'])
                                ->with(['assignedDeliverer', 'sender', 'delegationFrom', 'delegationTo'])
                                ->orderBy('updated_at', 'desc')
                                ->limit(15)
                                ->get();

        // Performance des livreurs (livraisons du jour)
        $delivererPerformance = $deliverers->map(function($deliverer) {
            $todaysDeliveries = Package::where('assigned_deliverer_id', $deliverer->id)
                                     ->where('status', 'DELIVERED')
                                     ->whereDate('delivered_at', today())
                                     ->count();

            $todaysCOD = Package::where('assigned_deliverer_id', $deliverer->id)
                              ->where('status', 'DELIVERED')
                              ->whereDate('delivered_at', today())
                              ->sum('cod_amount');

            return [
                'deliverer' => $deliverer,
                'deliveries_today' => $todaysDeliveries,
                'cod_collected_today' => $todaysCOD,
                'packages_in_progress' => $deliverer->assignedPackages->count()
            ];
        })->sortByDesc('deliveries_today');

        // Colis d'échange livrés nécessitant un retour
        $exchangePackages = Package::whereIn('assigned_deliverer_id', $deliverers->pluck('id'))
                                  ->where('est_echange', true)
                                  ->where('status', 'DELIVERED')
                                  ->with(['assignedDeliverer', 'delegationFrom', 'delegationTo'])
                                  ->orderBy('updated_at', 'desc')
                                  ->limit(10)
                                  ->get();

        return view('depot-manager.dashboard', compact(
            'stats',
            'deliverers',
            'urgentPackages',
            'recentActivity',
            'delivererPerformance',
            'exchangePackages'
        ));
    }

    /**
     * API pour les statistiques en temps réel
     */
    public function apiStats()
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $stats = $user->getDepotStats();
        $stats['depot_wallet_balance'] = $user->depot_wallet_balance ?? 0;

        // Récupérer les gouvernorats assignés au chef de dépôt
        $assignedGouvernorats = $user->assigned_gouvernorats_array ?? [];

        // Récupérer les IDs de délégations correspondant aux gouvernorats
        $delegationIds = [];
        if (!empty($assignedGouvernorats)) {
            $delegationIds = \App\Models\Delegation::whereIn('gouvernorat', $assignedGouvernorats)
                ->pluck('id')
                ->toArray();
        }

        // Charger les paiements en espèce à préparer
        $paymentsQuery = \App\Models\WithdrawalRequest::with([
            'client' => function($q) {
                $q->select('id', 'name', 'email', 'phone', 'address', 'city', 'delegation_id', 'assigned_delegation');
            }
        ])
        ->whereIn('method', ['CASH_DELIVERY', 'CASH', 'COD'])
        ->whereIn('status', ['PENDING', 'APPROVED', 'READY_FOR_DELIVERY']);

        // Filtrer par délégation si des gouvernorats sont assignés
        if (!empty($delegationIds)) {
            $paymentsQuery->where(function($q) use ($delegationIds) {
                // Paiements dont le client a une délégation dans les gouvernorats gérés
                $q->whereHas('client', function($clientQuery) use ($delegationIds) {
                    $clientQuery->whereIn('delegation_id', $delegationIds)
                        ->orWhereIn('assigned_delegation', $delegationIds);
                })
                // OU paiements dont le client a des colis vers ces délégations
                ->orWhereHas('client', function($clientQuery) use ($delegationIds) {
                    $clientQuery->whereHas('sentPackages', function($packageQuery) use ($delegationIds) {
                        $packageQuery->whereIn('delegation_to', $delegationIds);
                    });
                });
            });
        }

        $payments = $paymentsQuery->orderBy('created_at', 'desc')
            ->get()
            ->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'request_code' => $payment->request_code,
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                    'method' => $payment->method,
                    'created_at' => $payment->created_at->toDateTimeString(),
                    'created_at_formatted' => $payment->created_at->format('d/m/Y H:i'),
                    'client' => $payment->client ? [
                        'id' => $payment->client->id,
                        'name' => $payment->client->name,
                        'phone' => $payment->client->phone,
                        'email' => $payment->client->email,
                        'address' => $payment->client->address,
                        'city' => $payment->client->city,
                    ] : null,
                    'assigned_package' => $payment->assignedPackage ? [
                        'id' => $payment->assignedPackage->id,
                        'package_code' => $payment->assignedPackage->package_code,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'depot_info' => [
                'name' => $user->depot_name,
                'address' => $user->depot_address,
                'gouvernorats' => $user->assigned_gouvernorats
            ],
            'payments_to_prep' => $payments,
            'updated_at' => now()->format('H:i:s')
        ]);
    }

    /**
     * Détails d'un gouvernorat géré
     */
    public function showGouvernorat($gouvernorat)
    {
        $user = Auth::user();

        if (!$user->canManageGouvernorat($gouvernorat)) {
            abort(403, 'Vous n\'êtes pas autorisé à gérer ce gouvernorat.');
        }

        // Livreurs de ce gouvernorat
        $deliverers = User::where('role', 'DELIVERER')
                         ->where('assigned_delegation', $gouvernorat)
                         ->where('account_status', 'ACTIVE')
                         ->with(['assignedPackages' => function($q) {
                             $q->whereIn('status', ['ACCEPTED', 'PICKED_UP', 'UNAVAILABLE']);
                         }])
                         ->get();

        // Statistiques du gouvernorat
        $stats = [
            'total_deliverers' => $deliverers->count(),
            'active_packages' => Package::whereIn('assigned_deliverer_id', $deliverers->pluck('id'))
                                      ->whereIn('status', ['ACCEPTED', 'PICKED_UP', 'UNAVAILABLE'])
                                      ->count(),
            'delivered_today' => Package::whereIn('assigned_deliverer_id', $deliverers->pluck('id'))
                                      ->where('status', 'DELIVERED')
                                      ->whereDate('delivered_at', today())
                                      ->count(),
            'urgent_packages' => Package::whereIn('assigned_deliverer_id', $deliverers->pluck('id'))
                                      ->whereIn('status', ['PICKED_UP', 'UNAVAILABLE'])
                                      ->where('delivery_attempts', '>=', 3)
                                      ->count()
        ];

        return view('depot-manager.gouvernorat.show', compact('gouvernorat', 'deliverers', 'stats'));
    }

    /**
     * Traiter le retour d'un colis d'échange
     */
    public function processExchangeReturn(Request $request, $packageId)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            return response()->json(['success' => false, 'message' => 'Accès réservé aux chefs dépôt.'], 403);
        }

        $package = Package::find($packageId);

        if (!$package) {
            return response()->json(['success' => false, 'message' => 'Colis non trouvé.'], 404);
        }

        // Vérifier que c'est bien un colis d'échange livré
        if (!$package->est_echange || $package->status !== 'DELIVERED') {
            return response()->json(['success' => false, 'message' => 'Ce colis ne peut pas être traité comme un retour d\'échange.'], 400);
        }

        // Vérifier que le chef dépôt peut gérer ce colis
        $deliverer = $package->assignedDeliverer;
        if (!$deliverer || !$user->getManagedDeliverers()->contains($deliverer->id)) {
            return response()->json(['success' => false, 'message' => 'Vous n\'êtes pas autorisé à gérer ce colis.'], 403);
        }

        try {
            // Mettre à jour le statut du colis pour indiquer qu'il doit être retourné
            $package->update([
                'status' => 'RETURNED',
                'auto_return_reason' => 'EXCHANGE_RETURN_PROCESSED',
                'updated_at' => now()
            ]);

            // Log de l'action
            if (method_exists($package, 'updateStatus')) {
                $package->updateStatus('RETURNED', $user, 'Retour d\'échange traité par le chef dépôt', [
                    'exchange_return' => true,
                    'processed_by_depot_manager' => $user->id
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Colis d\'échange marqué comme retourné avec succès.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement : ' . $e->getMessage()
            ], 500);
        }
    }
}