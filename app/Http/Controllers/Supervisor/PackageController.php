<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $query = Package::with(['sender', 'assignedDeliverer', 'delegationFrom', 'delegationTo']);

        // Filtres
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->delivery_type) {
            $query->where('delivery_type', $request->delivery_type);
        }

        if ($request->delegation_id) {
            $query->where(function($q) use ($request) {
                $q->where('delegation_from', $request->delegation_id)
                  ->orWhere('delegation_to', $request->delegation_id);
            });
        }

        if ($request->client_id) {
            $query->where('sender_id', $request->client_id);
        }

        if ($request->deliverer_id) {
            $query->where('assigned_deliverer_id', $request->deliverer_id);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('package_code', 'like', "%{$request->search}%")
                  ->orWhereJsonContains('recipient_data->name', "%{$request->search}%")
                  ->orWhereJsonContains('recipient_data->phone', "%{$request->search}%");
            });
        }

        $packages = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total_packages' => Package::count(),
            'created_packages' => Package::where('status', 'CREATED')->count(),
            'available_packages' => Package::where('status', 'AVAILABLE')->count(),
            'accepted_packages' => Package::where('status', 'ACCEPTED')->count(),
            'picked_up_packages' => Package::where('status', 'PICKED_UP')->count(),
            'delivered_packages' => Package::where('status', 'DELIVERED')->count(),
            'returned_packages' => Package::where('status', 'RETURNED')->count(),
            'cancelled_packages' => Package::where('status', 'CANCELLED')->count(),
        ];

        $delegations = \App\Models\Delegation::where('active', true)->get();
        $clients = User::where('role', 'CLIENT')->limit(100)->get(['id', 'name']);
        $deliverers = User::where('role', 'DELIVERER')->limit(100)->get(['id', 'name']);

        return view('supervisor.packages.index', compact('packages', 'stats', 'delegations', 'clients', 'deliverers'));
    }

    public function show(Package $package)
    {
        $package->load(['sender', 'assignedDeliverer', 'delegationFrom', 'delegationTo']);

        $timeline = collect();

        // Ajouter les événements de package
        $timeline->push([
            'type' => 'package_created',
            'icon' => 'plus',
            'title' => 'Colis créé',
            'description' => "Colis créé par {$package->sender->name}",
            'date' => $package->created_at,
            'color' => 'blue',
        ]);

        if ($package->assigned_deliverer_id && $package->assigned_at) {
            $timeline->push([
                'type' => 'package_accepted',
                'icon' => 'check',
                'title' => 'Colis accepté',
                'description' => "Accepté par {$package->assignedDeliverer->name}",
                'date' => $package->assigned_at,
                'color' => 'green',
            ]);
        }

        if ($package->pickup_date) {
            $timeline->push([
                'type' => 'package_picked_up',
                'icon' => 'truck',
                'title' => 'Colis collecté',
                'description' => 'Colis collecté par le livreur',
                'date' => $package->pickup_date,
                'color' => 'orange',
            ]);
        }

        if ($package->delivery_date) {
            $timeline->push([
                'type' => 'package_delivered',
                'icon' => 'check-circle',
                'title' => 'Colis livré',
                'description' => 'Colis livré au destinataire',
                'date' => $package->delivery_date,
                'color' => 'green',
            ]);
        }

        // Ajouter les transactions (si la relation existe)
        if ($package->relationLoaded('transactions') && $package->transactions) {
            foreach ($package->transactions as $transaction) {
                $timeline->push([
                    'type' => 'transaction',
                    'icon' => 'dollar-sign',
                    'title' => 'Transaction',
                    'description' => "{$transaction->type}: {$transaction->amount} TND",
                    'date' => $transaction->created_at,
                    'color' => 'green',
                ]);
            }
        }

        // Ajouter les réclamations (si la relation existe)
        if ($package->relationLoaded('complaints') && $package->complaints) {
            foreach ($package->complaints as $complaint) {
                $timeline->push([
                    'type' => 'complaint',
                    'icon' => 'alert-triangle',
                    'title' => 'Réclamation',
                    'description' => $complaint->type,
                    'date' => $complaint->created_at,
                    'color' => 'red',
                ]);
            }
        }

        $timeline = $timeline->sortBy('date');

        return view('supervisor.packages.show', compact('package', 'timeline'));
    }

    public function updateStatus(Request $request, Package $package)
    {
        $request->validate([
            'status' => 'required|in:CREATED,AVAILABLE,ACCEPTED,PICKED_UP,DELIVERED,RETURNED,REFUSED,CANCELLED',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldStatus = $package->status;

        DB::transaction(function () use ($request, $package, $oldStatus) {
            $package->update([
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            // Mettre à jour les dates selon le statut
            switch ($request->status) {
                case 'ACCEPTED':
                    $package->update(['acceptance_date' => now()]);
                    break;
                case 'PICKED_UP':
                    $package->update(['pickup_date' => now()]);
                    break;
                case 'DELIVERED':
                    $package->update(['delivery_date' => now()]);
                    break;
            }

                // Créer une notification pour le changement de statut (si le modèle existe)
            if (class_exists(\App\Models\Notification::class)) {
                \App\Models\Notification::create([
                    'user_id' => $package->sender_id,
                    'type' => 'PACKAGE_STATUS_CHANGED',
                    'title' => 'Statut du colis modifié',
                    'message' => "Le statut de votre colis #{$package->package_code} a été modifié de {$oldStatus} vers {$request->status}",
                    'priority' => 'NORMAL',
                    'data' => [
                        'package_id' => $package->id,
                        'old_status' => $oldStatus,
                        'new_status' => $request->status,
                        'changed_by' => auth()->id(),
                    ]
                ]);
            }
        });

        return back()->with('success', 'Statut du colis mis à jour avec succès.');
    }

    public function assignDeliverer(Request $request, Package $package)
    {
        $request->validate([
            'deliverer_id' => 'required|exists:users,id',
        ]);

        $deliverer = User::findOrFail($request->deliverer_id);

        if ($deliverer->role !== 'DELIVERER') {
            return back()->with('error', 'L\'utilisateur sélectionné n\'est pas un livreur.');
        }

        $package->update([
            'assigned_deliverer_id' => $request->deliverer_id,
            'status' => 'ACCEPTED',
            'assigned_at' => now(),
        ]);

        // Créer une notification pour le livreur (si le modèle existe)
        if (class_exists(\App\Models\Notification::class)) {
            \App\Models\Notification::create([
                'user_id' => $request->deliverer_id,
                'type' => 'PACKAGE_ASSIGNED',
                'title' => 'Nouveau colis assigné',
                'message' => "Un nouveau colis #{$package->package_code} vous a été assigné",
                'priority' => 'HIGH',
                'data' => [
                    'package_id' => $package->id,
                    'assigned_by' => auth()->id(),
                ]
            ]);
        }

        return back()->with('success', 'Livreur assigné avec succès.');
    }

    public function modifyCod(Request $request, Package $package)
    {
        $request->validate([
            'cod_amount' => 'required|numeric|min:0|max:5000',
            'reason' => 'required|string|max:500',
        ]);

        $oldAmount = $package->cod_amount;

        DB::transaction(function () use ($request, $package, $oldAmount) {
            $package->update([
                'cod_amount' => $request->cod_amount,
            ]);

            // Créer une transaction pour tracer la modification
            \App\Models\FinancialTransaction::create([
                'transaction_id' => 'COD_MOD_' . $package->id . '_' . time(),
                'user_id' => $package->sender_id,
                'type' => 'COD_MODIFICATION',
                'amount' => $request->cod_amount - $oldAmount,
                'description' => "Modification COD: {$request->reason}",
                'status' => 'COMPLETED',
                'metadata' => [
                    'package_id' => $package->id,
                    'old_amount' => $oldAmount,
                    'new_amount' => $request->cod_amount,
                    'modified_by' => auth()->id(),
                    'reason' => $request->reason,
                ]
            ]);

            // Créer une notification
            \App\Models\Notification::create([
                'user_id' => $package->client_id,
                'type' => 'COD_MODIFIED',
                'title' => 'Montant COD modifié',
                'message' => "Le montant COD du colis #{$package->tracking_number} a été modifié de {$oldAmount} à {$request->cod_amount} TND",
                'priority' => 'NORMAL',
                'data' => [
                    'package_id' => $package->id,
                    'old_amount' => $oldAmount,
                    'new_amount' => $request->cod_amount,
                    'reason' => $request->reason,
                ]
            ]);
        });

        return back()->with('success', 'Montant COD modifié avec succès.');
    }

    public function resetDeliveryAttempts(Package $package)
    {
        $package->update([
            'delivery_attempts' => 0,
            'notes' => 'Tentatives de livraison remises à zéro par le superviseur',
        ]);

        return back()->with('success', 'Tentatives de livraison remises à zéro.');
    }

    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'package_ids' => 'required|array',
            'package_ids.*' => 'exists:packages,id',
            'status' => 'required|in:CREATED,AVAILABLE,ACCEPTED,PICKED_UP,DELIVERED,RETURNED,REFUSED,CANCELLED',
        ]);

        DB::transaction(function () use ($request) {
            Package::whereIn('id', $request->package_ids)->update([
                'status' => $request->status,
                'updated_at' => now(),
            ]);

            // Créer des notifications pour les clients (si le modèle existe)
            if (class_exists(\App\Models\Notification::class)) {
                $packages = Package::whereIn('id', $request->package_ids)->with('sender')->get();
                foreach ($packages as $package) {
                    \App\Models\Notification::create([
                        'user_id' => $package->sender_id,
                        'type' => 'PACKAGE_STATUS_CHANGED',
                        'title' => 'Statut du colis modifié',
                        'message' => "Le statut de votre colis #{$package->package_code} a été modifié vers {$request->status}",
                        'priority' => 'NORMAL',
                        'data' => [
                            'package_id' => $package->id,
                            'new_status' => $request->status,
                            'changed_by' => auth()->id(),
                        ]
                    ]);
                }
            }
        });

        return back()->with('success', count($request->package_ids) . ' colis mis à jour avec succès.');
    }

    public function bulkAssignDeliverer(Request $request)
    {
        $request->validate([
            'package_ids' => 'required|array',
            'package_ids.*' => 'exists:packages,id',
            'deliverer_id' => 'required|exists:users,id',
        ]);

        $deliverer = User::findOrFail($request->deliverer_id);

        if ($deliverer->role !== 'DELIVERER') {
            return back()->with('error', 'L\'utilisateur sélectionné n\'est pas un livreur.');
        }

        DB::transaction(function () use ($request) {
            Package::whereIn('id', $request->package_ids)->update([
                'deliverer_id' => $request->deliverer_id,
                'status' => 'ACCEPTED',
                'acceptance_date' => now(),
            ]);

            // Créer une notification pour le livreur
            \App\Models\Notification::create([
                'user_id' => $request->deliverer_id,
                'type' => 'PACKAGES_ASSIGNED',
                'title' => 'Nouveaux colis assignés',
                'message' => count($request->package_ids) . ' nouveaux colis vous ont été assignés',
                'priority' => 'HIGH',
                'data' => [
                    'package_ids' => $request->package_ids,
                    'assigned_by' => auth()->id(),
                ]
            ]);
        });

        return back()->with('success', count($request->package_ids) . ' colis assignés avec succès.');
    }

    public function generateRunSheet(Request $request)
    {
        $request->validate([
            'package_ids' => 'required|array',
            'package_ids.*' => 'exists:packages,id',
        ]);

        // Cette fonctionnalité sera implémentée plus tard
        return back()->with('info', 'Génération de feuille de route en cours de développement.');
    }

    // API Methods
    public function apiSearch(Request $request)
    {
        $query = Package::with(['client', 'deliverer']);

        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where('tracking_number', 'like', "%{$request->q}%")
                  ->orWhere('recipient_name', 'like', "%{$request->q}%")
                  ->orWhere('recipient_phone', 'like', "%{$request->q}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $packages = $query->limit(20)->get();

        return response()->json($packages);
    }

    public function apiStats()
    {
        return response()->json([
            'total_packages' => Package::count(),
            'by_status' => [
                'CREATED' => Package::where('status', 'CREATED')->count(),
                'AVAILABLE' => Package::where('status', 'AVAILABLE')->count(),
                'ACCEPTED' => Package::where('status', 'ACCEPTED')->count(),
                'PICKED_UP' => Package::where('status', 'PICKED_UP')->count(),
                'DELIVERED' => Package::where('status', 'DELIVERED')->count(),
                'RETURNED' => Package::where('status', 'RETURNED')->count(),
                'CANCELLED' => Package::where('status', 'CANCELLED')->count(),
            ],
            'by_delivery_type' => [
                'fast' => Package::where('delivery_type', 'fast')->count(),
                'advanced' => Package::where('delivery_type', 'advanced')->count(),
            ]
        ]);
    }

    public function apiBlockedPackages()
    {
        $blockedPackages = Package::where(function ($query) {
            $query->where('delivery_attempts', '>=', 3)
                  ->orWhere('status', 'RETURNED')
                  ->orWhereRaw('(julianday("now") - julianday(created_at)) > 7 AND status != "DELIVERED"');
        })->with(['sender', 'assignedDeliverer'])->get();

        return response()->json($blockedPackages);
    }

    public function apiByDelegation()
    {
        $packagesByDelegation = Package::select(
                'delegations.name as delegation_name',
                DB::raw('COUNT(packages.id) as packages_count'),
                DB::raw('SUM(CASE WHEN packages.status = "DELIVERED" THEN 1 ELSE 0 END) as delivered_count')
            )
            ->join('delegations', 'packages.delegation_to', '=', 'delegations.id')
            ->groupBy('delegations.id', 'delegations.name')
            ->get();

        return response()->json($packagesByDelegation);
    }

    public function codHistory(Package $package)
    {
        $codHistory = \App\Models\FinancialTransaction::whereJsonContains('metadata->package_id', $package->id)
                               ->where('type', 'COD_MODIFICATION')
                               ->orderBy('created_at', 'desc')
                               ->get();

        return response()->json($codHistory);
    }
}