<?php

namespace App\Http\Controllers\DepotManager;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\ReturnPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExchangePackageController extends Controller
{
    /**
     * Liste des colis échanges à traiter
     */
    public function index()
    {
        $user = Auth::user();
        
        // Récupérer les gouvernorats du chef dépôt
        $gouvernorats = is_array($user->depot_manager_gouvernorats) 
            ? $user->depot_manager_gouvernorats 
            : json_decode($user->depot_manager_gouvernorats ?? '[]', true);
        
        if (!is_array($gouvernorats)) {
            $gouvernorats = [];
        }
        
        // Normaliser les gouvernorats (UPPERCASE + underscores)
        $gouvernorats = array_map(function($gov) {
            return strtoupper(str_replace(' ', '_', trim($gov)));
        }, $gouvernorats);
        
        // Récupérer les colis échanges livrés dans les gouvernorats du chef dépôt
        $exchangePackages = Package::where('est_echange', true)
            ->where('status', 'DELIVERED')
            ->whereNull('return_package_id') // Pas encore traité
            ->when(!empty($gouvernorats), function($q) use ($gouvernorats) {
                return $q->whereHas('delegationTo', function($subQ) use ($gouvernorats) {
                    $subQ->whereIn('zone', $gouvernorats);  // Utiliser 'zone' au lieu de 'governorate'
                });
            })
            ->with(['sender', 'delegationFrom', 'delegationTo', 'assignedDeliverer'])
            ->orderBy('delivered_at', 'desc')
            ->paginate(20);
        
        return view('depot-manager.exchanges.index', compact('exchangePackages', 'gouvernorats'));
    }
    
    /**
     * Traiter un colis échange (créer le retour)
     */
    public function processExchange(Package $package)
    {
        $user = Auth::user();
        
        try {
            DB::beginTransaction();
            
            // Vérifications
            if (!$package->est_echange) {
                return redirect()->back()->with('error', 'Ce colis n\'est pas un échange');
            }
            
            if ($package->status !== 'DELIVERED') {
                return redirect()->back()->with('error', 'Ce colis n\'est pas encore livré');
            }
            
            if ($package->return_package_id) {
                return redirect()->back()->with('error', 'Ce colis a déjà été traité');
            }
            
            // Créer le colis retour
            $returnPackage = ReturnPackage::create([
                'original_package_id' => $package->id,
                'return_package_code' => 'RET_EX_' . $package->package_code . '_' . time(),
                'return_reason' => 'ÉCHANGE',
                'status' => 'AT_DEPOT',
                'created_by_depot_manager_id' => $user->id,
                'depot_manager_name' => $user->name,
                'recipient_info' => [
                    'name' => $package->sender_data['name'] ?? 'Fournisseur',
                    'phone' => $package->sender_data['phone'] ?? 'N/A',
                    'address' => $package->sender_data['address'] ?? 'N/A'
                ]
            ]);
            
            // Lier le colis original au retour
            $package->update([
                'return_package_id' => $returnPackage->id
            ]);
            
            DB::commit();
            
            return redirect()->back()->with('success', '✅ Échange traité avec succès ! Colis retour créé : ' . $returnPackage->return_package_code);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur processExchange:', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur lors du traitement : ' . $e->getMessage());
        }
    }
    
    /**
     * Historique des échanges traités
     */
    public function history()
    {
        $user = Auth::user();
        
        $gouvernorats = is_array($user->depot_manager_gouvernorats) 
            ? $user->depot_manager_gouvernorats 
            : json_decode($user->depot_manager_gouvernorats ?? '[]', true);
        
        if (!is_array($gouvernorats)) {
            $gouvernorats = [];
        }
        
        // Normaliser les gouvernorats (UPPERCASE + underscores)
        $gouvernorats = array_map(function($gov) {
            return strtoupper(str_replace(' ', '_', trim($gov)));
        }, $gouvernorats);
        
        $processedExchanges = Package::where('est_echange', true)
            ->where('status', 'DELIVERED')
            ->whereNotNull('return_package_id') // Déjà traité
            ->when(!empty($gouvernorats), function($q) use ($gouvernorats) {
                return $q->whereHas('delegationTo', function($subQ) use ($gouvernorats) {
                    $subQ->whereIn('zone', $gouvernorats);  // Utiliser 'zone' au lieu de 'governorate'
                });
            })
            ->with(['sender', 'returnPackage'])
            ->orderBy('delivered_at', 'desc')
            ->paginate(20);
        
        return view('depot-manager.exchanges.history', compact('processedExchanges'));
    }
    
    /**
     * Imprimer le bon de livraison du retour
     */
    public function printReturnReceipt(ReturnPackage $returnPackage)
    {
        $returnPackage->load(['originalPackage.sender', 'originalPackage.delegationFrom']);
        
        return view('depot-manager.exchanges.print-receipt', compact('returnPackage'));
    }
}
