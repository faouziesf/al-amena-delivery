<?php

namespace App\Http\Controllers\DepotManager;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\ActionLog;
use App\Notifications\DepotExchangeToProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExchangeController extends Controller
{
    /**
     * Liste des échanges livrés à traiter
     */
    public function index()
    {
        $exchanges = Package::where('est_echange', true)
            ->where('status', 'DELIVERED')
            ->whereDoesntHave('returnPackages')
            ->with(['sender', 'delegationFrom', 'delegationTo'])
            ->latest('delivered_at')
            ->paginate(20);

        return view('depot-manager.exchanges.index', compact('exchanges'));
    }

    /**
     * Créer les retours pour les échanges sélectionnés
     */
    public function createReturns(Request $request)
    {
        $request->validate([
            'package_ids' => 'required|array',
            'package_ids.*' => 'exists:packages,id',
        ]);

        $packageIds = $request->package_ids;
        $returns = [];
        $errors = [];

        DB::transaction(function() use ($packageIds, &$returns, &$errors) {
            foreach ($packageIds as $packageId) {
                try {
                    $original = Package::findOrFail($packageId);
                    
                    // Vérifier que c'est bien un échange livré
                    if (!$original->est_echange || $original->status !== 'DELIVERED') {
                        $errors[] = "Le colis {$original->package_code} n'est pas un échange livré";
                        continue;
                    }
                    
                    // Vérifier qu'il n'a pas déjà un retour
                    if ($original->returnPackages()->exists()) {
                        $errors[] = "Le colis {$original->package_code} a déjà un retour créé";
                        continue;
                    }
                    
                    // Créer le colis retour
                    $return = Package::create([
                        'package_code' => 'RET-' . strtoupper(Str::random(8)),
                        'package_type' => Package::TYPE_RETURN,
                        'original_package_id' => $original->id,
                        'sender_id' => $original->sender_id,
                        'status' => 'AT_DEPOT',
                        'cod_amount' => 0,
                        'recipient_data' => $original->sender_data,
                        'sender_data' => $original->recipient_data,
                        'delegation_from' => $original->delegation_to,
                        'delegation_to' => $original->delegation_from,
                        'return_reason' => 'ÉCHANGE',
                        'return_package_code' => 'RET-' . $original->package_code,
                        'created_by' => Auth::id(),
                    ]);

                    $returns[] = $return;
                    
                    // IMPORTANT: Ne PAS modifier le statut du colis original (reste DELIVERED)
                    // Car l'échange a été réussi
                    
                    // Logger l'action
                    ActionLog::create([
                        'user_id' => Auth::id(),
                        'user_name' => Auth::user()->name,
                        'user_role' => Auth::user()->role,
                        'action' => 'EXCHANGE_RETURN_CREATED',
                        'entity_type' => 'Package',
                        'entity_id' => $return->id,
                        'description' => "Retour d'échange créé pour {$original->package_code}",
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'old_values' => null,
                        'new_values' => [
                            'return_package_code' => $return->package_code,
                            'original_package_code' => $original->package_code,
                        ],
                    ]);
                    
                } catch (\Exception $e) {
                    $errors[] = "Erreur pour le colis {$packageId}: " . $e->getMessage();
                }
            }
        });

        if (count($returns) > 0) {
            $returnIds = array_column($returns, 'id');
            return redirect()->route('depot-manager.exchanges.print-returns', ['ids' => implode(',', $returnIds)])
                ->with('success', count($returns) . ' retour(s) créé(s) avec succès')
                ->with('errors', $errors);
        }

        return redirect()->back()
            ->with('error', 'Aucun retour créé')
            ->with('errors', $errors);
    }

    /**
     * Imprimer les bordereaux de retours
     */
    public function printReturns(Request $request)
    {
        $ids = explode(',', $request->ids);
        
        $returns = Package::whereIn('id', $ids)
            ->where('package_type', Package::TYPE_RETURN)
            ->with(['originalPackage.sender', 'sender'])
            ->get();

        if ($returns->isEmpty()) {
            return redirect()->route('depot-manager.exchanges.index')
                ->with('error', 'Aucun retour trouvé');
        }

        return view('depot-manager.exchanges.print-returns', compact('returns'));
    }

    /**
     * Afficher les détails d'un échange
     */
    public function show(Package $exchange)
    {
        if (!$exchange->est_echange) {
            abort(404, 'Cet colis n\'est pas un échange');
        }

        $exchange->load([
            'sender',
            'delegationFrom',
            'delegationTo',
            'assignedDeliverer',
            'returnPackages'
        ]);

        return view('depot-manager.exchanges.show', compact('exchange'));
    }
}
