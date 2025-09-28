<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DelivererReassignmentController extends Controller
{
    /**
     * Afficher les colis pour changement de livreur
     */
    public function index(Request $request)
    {
        $deliverer = Auth::user();

        // Vérifier que l'utilisateur est un livreur TRANSIT
        if (!$deliverer->canReassignPackages()) {
            return view('deliverer.reassignment.index', [
                'packages' => collect(),
                'error' => 'Votre type de compte ne permet pas de changer les livreurs.',
                'deliverer_type' => $deliverer->deliverer_type
            ]);
        }

        // Récupérer tous les colis assignés à des livreurs (pas encore livrés)
        $query = Package::with(['assignedDeliverer', 'sender'])
            ->whereNotNull('assigned_deliverer_id')
            ->whereIn('status', ['ACCEPTED', 'PICKED_UP', 'OUT_FOR_DELIVERY']);

        // Filtres optionnels
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('package_code', 'like', "%{$search}%")
                  ->orWhere('tracking_number', 'like', "%{$search}%")
                  ->orWhereHas('assignedDeliverer', function ($delivererQuery) use ($search) {
                      $delivererQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('delegation_to')) {
            $query->where('delegation_to', $request->delegation_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $packages = $query->orderBy('created_at', 'desc')->paginate(15);

        // Récupérer tous les livreurs actifs pour le changement
        $availableDeliverers = User::where('role', 'DELIVERER')
            ->where('account_status', 'ACTIVE')
            ->whereIn('deliverer_type', ['DELEGATION', 'JOKER']) // Exclure les TRANSIT
            ->select(['id', 'name', 'deliverer_type', 'assigned_delegation'])
            ->orderBy('name')
            ->get();

        $delegations = User::getAvailableDelegations();

        return view('deliverer.reassignment.index', compact(
            'packages',
            'availableDeliverers',
            'delegations'
        ));
    }

    /**
     * Changer le livreur d'un colis
     */
    public function reassign(Request $request, Package $package)
    {
        $deliverer = Auth::user();

        // Vérifier que l'utilisateur est un livreur TRANSIT
        if (!$deliverer->canReassignPackages()) {
            return back()->withErrors(['permission' => 'Vous n\'avez pas l\'autorisation de changer les livreurs.']);
        }

        $request->validate([
            'new_deliverer_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $newDeliverer = User::where('role', 'DELIVERER')
            ->where('account_status', 'ACTIVE')
            ->findOrFail($request->new_deliverer_id);

        // Vérifier que le nouveau livreur n'est pas un TRANSIT
        if ($newDeliverer->deliverer_type === 'TRANSIT') {
            return back()->withErrors(['deliverer' => 'Impossible d\'assigner un colis à un livreur de type TRANSIT.']);
        }

        // Vérifier la compatibilité des délégations pour les livreurs DELEGATION
        if ($newDeliverer->deliverer_type === 'DELEGATION') {
            if (!$newDeliverer->hasAssignedDelegation() ||
                $newDeliverer->assigned_delegation !== $package->delegation_to) {
                return back()->withErrors(['delegation' =>
                    'Le livreur sélectionné ne couvre pas la délégation de destination du colis.']);
            }
        }

        DB::transaction(function () use ($package, $newDeliverer, $request, $deliverer) {
            $oldDeliverer = $package->assignedDeliverer;

            // Mettre à jour le colis
            $package->update([
                'assigned_deliverer_id' => $newDeliverer->id,
                'status' => 'ACCEPTED', // Réinitialiser le statut
                'reassigned_at' => now(),
                'reassigned_by' => $deliverer->id,
                'reassignment_reason' => $request->reason,
            ]);

            // Log de l'activité (optionnel)
            \Log::info("Colis {$package->package_code} réassigné", [
                'old_deliverer' => $oldDeliverer ? $oldDeliverer->name : 'Aucun',
                'new_deliverer' => $newDeliverer->name,
                'reassigned_by' => $deliverer->name,
                'reason' => $request->reason,
            ]);
        });

        return back()->with('success',
            "Colis {$package->package_code} réassigné avec succès au livreur {$newDeliverer->name}."
        );
    }

    /**
     * API pour rechercher des livreurs compatibles
     */
    public function searchDeliverers(Request $request)
    {
        $search = $request->input('q', '');
        $delegationTo = $request->input('delegation_to');

        $query = User::where('role', 'DELIVERER')
            ->where('account_status', 'ACTIVE')
            ->whereIn('deliverer_type', ['DELEGATION', 'JOKER']);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Filtrer par délégation si spécifiée
        if ($delegationTo) {
            $query->where(function ($q) use ($delegationTo) {
                $q->where('deliverer_type', 'JOKER') // Les JOKER acceptent toutes les délégations
                  ->orWhere(function ($subQ) use ($delegationTo) {
                      $subQ->where('deliverer_type', 'DELEGATION')
                           ->where('assigned_delegation', $delegationTo);
                  });
            });
        }

        $deliverers = $query->limit(10)
            ->get(['id', 'name', 'deliverer_type', 'assigned_delegation'])
            ->map(function ($deliverer) {
                return [
                    'id' => $deliverer->id,
                    'name' => $deliverer->name,
                    'type' => $deliverer->deliverer_type,
                    'delegation' => $deliverer->assigned_delegation,
                    'display_name' => $deliverer->name . ' (' .
                        ($deliverer->deliverer_type === 'JOKER' ? 'Joker' : $deliverer->assigned_delegation) . ')'
                ];
            });

        return response()->json($deliverers);
    }
}