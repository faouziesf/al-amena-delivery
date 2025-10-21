<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PickupRequest;
use App\Models\ReturnPackage;
use App\Models\Transaction;
use App\Models\WithdrawalRequest;
use App\Models\UserWallet;
use App\Models\Delegation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Contrôleur Principal Livreur - PWA Refonte Complète
 * 
 * Gère toutes les fonctionnalités du livreur avec:
 * - Filtrage automatique par gouvernorats assignés
 * - Run Sheet Unifié (livraisons, pickups, retours, paiements)
 * - Gestion des colis spéciaux
 * - Livraison directe après pickup
 */
class DelivererController extends Controller
{
    /**
     * Obtenir les gouvernorats assignés au livreur
     */
    protected function getDelivererGouvernorats()
    {
        $user = Auth::user();
        $gouvernorats = $user->deliverer_gouvernorats ?? [];
        
        // Si c'est une string JSON, décoder
        if (is_string($gouvernorats)) {
            $gouvernorats = json_decode($gouvernorats, true) ?? [];
        }
        
        // Si c'est vide ou null, retourner tableau vide
        return is_array($gouvernorats) ? $gouvernorats : [];
    }

    /**
     * Filtrer les packages par gouvernorats du livreur
     */
    protected function filterByGouvernorats($query)
    {
        $gouvernorats = $this->getDelivererGouvernorats();
        
        if (empty($gouvernorats)) {
            return $query;
        }

        return $query->whereHas('delegationTo', function($q) use ($gouvernorats) {
            $q->whereIn('governorate', $gouvernorats);
        });
    }

    /**
     * RUN SHEET UNIFIÉ - Interface Principale
     * 
     * Affiche toutes les tâches du jour:
     * 🚚 Livraisons standard
     * 📦 Ramassages (Pickups)
     * ↩️ Retours Fournisseur
     * 💰 Paiements Espèce
     * ⚡ Livraisons Directes
     */
    public function runSheetUnified()
    {
        $user = Auth::user();
        $gouvernorats = $this->getDelivererGouvernorats();
        
        $tasks = collect();

        // 1. LIVRAISONS STANDARD 🚚
        $deliveries = Package::where('assigned_deliverer_id', $user->id)
            ->whereIn('status', ['AVAILABLE', 'ACCEPTED', 'PICKED_UP', 'OUT_FOR_DELIVERY'])
            ->where(function($q) {
                $q->whereNull('package_type')
                  ->orWhere('package_type', Package::TYPE_NORMAL);
            })
            ->with(['delegationTo', 'sender'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($deliveries as $pkg) {
            $tasks->push([
                'id' => $pkg->id,
                'type' => 'livraison',
                'icon' => '🚚',
                'priority' => $pkg->est_echange ? 10 : 5,
                'package_code' => $pkg->package_code,
                'recipient_name' => $pkg->recipient_data['name'] ?? 'N/A',
                'recipient_address' => $pkg->recipient_data['address'] ?? 'N/A',
                'recipient_phone' => $pkg->recipient_data['phone'] ?? 'N/A',
                'cod_amount' => $pkg->cod_amount ?? 0,
                'status' => $pkg->status,
                'est_echange' => $pkg->est_echange ?? false,
                'requires_signature' => $pkg->requires_signature ?? true,
                'date' => $pkg->created_at,
                'delegation' => $pkg->delegationTo->name ?? 'N/A',
                'model' => $pkg
            ]);
        }

        // 2. RAMASSAGES (PICKUPS) 📦
        $pickups = PickupRequest::where('assigned_deliverer_id', $user->id)
            ->whereIn('status', ['assigned', 'awaiting_pickup', 'in_progress'])
            ->with(['delegation', 'client'])
            ->orderBy('requested_pickup_date', 'asc')
            ->get();

        foreach ($pickups as $pickup) {
            $tasks->push([
                'id' => 'pickup_' . $pickup->id,
                'type' => 'pickup',
                'icon' => '📦',
                'priority' => 8,
                'package_code' => $pickup->pickup_code ?? 'P-' . $pickup->id,
                'recipient_name' => $pickup->pickup_contact_name ?? 'Client',
                'recipient_address' => $pickup->pickup_address,
                'recipient_phone' => $pickup->pickup_phone,
                'cod_amount' => 0,
                'status' => $pickup->status,
                'est_echange' => false,
                'requires_signature' => true,
                'date' => $pickup->requested_pickup_date ?? $pickup->created_at,
                'delegation' => $pickup->delegation->name ?? 'N/A',
                'pickup_id' => $pickup->id,
                'model' => $pickup
            ]);
        }

        // 3. RETOURS FOURNISSEUR ↩️ (depuis table packages)
        $returns = Package::where('assigned_deliverer_id', $user->id)
            ->where('package_type', Package::TYPE_RETURN)
            ->whereIn('status', ['AT_DEPOT', 'ASSIGNED', 'AVAILABLE', 'ACCEPTED', 'OUT_FOR_DELIVERY'])
            ->with(['originalPackage.delegationFrom'])
            ->get();

        foreach ($returns as $returnPkg) {
            $recipientInfo = $returnPkg->recipient_data ?? [];
            
            $tasks->push([
                'id' => $returnPkg->id,
                'type' => 'retour',
                'icon' => '↩️',
                'priority' => 7,
                'package_code' => $returnPkg->return_package_code ?? $returnPkg->package_code,
                'recipient_name' => $recipientInfo['name'] ?? 'Fournisseur',
                'recipient_address' => $recipientInfo['address'] ?? 'N/A',
                'recipient_phone' => $recipientInfo['phone'] ?? 'N/A',
                'cod_amount' => 0, // Toujours 0 pour les retours
                'status' => $returnPkg->status,
                'est_echange' => false,
                'requires_signature' => true, // OBLIGATOIRE pour retours
                'date' => $returnPkg->created_at,
                'delegation' => $returnPkg->originalPackage?->delegationFrom?->name ?? 'N/A',
                'return_reason' => $returnPkg->return_reason,
                'model' => $returnPkg
            ]);
        }

        // 4. PAIEMENTS ESPÈCE 💰 (depuis table packages)
        $payments = Package::where('assigned_deliverer_id', $user->id)
            ->where('package_type', Package::TYPE_PAYMENT)
            ->whereIn('status', ['AVAILABLE', 'ACCEPTED', 'OUT_FOR_DELIVERY'])
            ->with(['paymentWithdrawal.client'])
            ->get();

        foreach ($payments as $paymentPkg) {
            $withdrawal = $paymentPkg->paymentWithdrawal;
            $recipientInfo = $paymentPkg->recipient_data ?? [];
            
            $tasks->push([
                'id' => $paymentPkg->id,
                'type' => 'paiement',
                'icon' => '💰',
                'priority' => 9,
                'package_code' => $paymentPkg->package_code,
                'recipient_name' => $recipientInfo['name'] ?? 'Client',
                'recipient_address' => $recipientInfo['address'] ?? 'N/A',
                'recipient_phone' => $recipientInfo['phone'] ?? 'N/A',
                'cod_amount' => 0, // Toujours 0 (c'est un paiement sortant)
                'payment_amount' => $withdrawal->amount ?? 0,
                'status' => $paymentPkg->status,
                'est_echange' => false,
                'requires_signature' => true, // OBLIGATOIRE pour paiements
                'date' => $paymentPkg->created_at,
                'delegation' => $paymentPkg->delegationTo?->name ?? 'N/A',
                'withdrawal_id' => $withdrawal->id ?? null,
                'model' => $paymentPkg
            ]);
        }

        // Trier par priorité puis par date
        $tasks = $tasks->sortByDesc('priority')->sortBy('date')->values();

        // Stats
        $stats = [
            'total' => $tasks->count(),
            'livraisons' => $deliveries->count(),
            'pickups' => $pickups->count(),
            'retours' => $returns->count(),
            'paiements' => $payments->count(),
            'completed_today' => Package::where('assigned_deliverer_id', $user->id)
                ->where('status', 'DELIVERED')
                ->whereDate('delivered_at', today())
                ->count()
        ];

        // Utiliser la vue avec layout deliverer
        return view('deliverer.tournee', compact('tasks', 'stats', 'gouvernorats'));
    }

    /**
     * DÉTAIL TÂCHE - Unifié pour tous types
     */
    public function taskDetail(Package $package)
    {
        $user = Auth::user();

        // Vérifier que le package est assigné au livreur
        if ($package->assigned_deliverer_id !== $user->id) {
            return redirect()->route('deliverer.tournee')
                ->with('error', 'Cette tâche ne vous est pas assignée.');
        }

        // Déterminer le type de colis
        $taskType = 'livraison'; // Par défaut
        $isSpecial = false;
        $requiresSignature = $package->requires_signature ?? true;

        if ($package->return_package_id) {
            $taskType = 'retour';
            $isSpecial = true;
            $requiresSignature = true; // OBLIGATOIRE
        } elseif ($package->payment_withdrawal_id) {
            $taskType = 'paiement';
            $isSpecial = true;
            $requiresSignature = true; // OBLIGATOIRE
        }

        // Afficher COD = 0 pour retours et paiements
        $displayCod = $isSpecial ? 0 : ($package->cod_amount ?? 0);

        return view('deliverer.task-detail', compact(
            'package',
            'taskType',
            'isSpecial',
            'requiresSignature',
            'displayCod'
        ));
    }

    /**
     * Menu Principal
     */
    public function menu()
    {
        $user = Auth::user();
        
        $activeCount = Package::where('assigned_deliverer_id', $user->id)
            ->whereNotIn('status', ['DELIVERED', 'CANCELLED', 'RETURNED'])
            ->count();
            
        $todayCount = Package::where('assigned_deliverer_id', $user->id)
            ->where('status', 'DELIVERED')
            ->whereDate('delivered_at', today())
            ->count();
            
        $balance = UserWallet::where('user_id', $user->id)->value('balance') ?? 0;
        
        return view('deliverer.menu-modern', compact('activeCount', 'todayCount', 'balance'));
    }

    /**
     * Wallet
     */
    public function wallet()
    {
        $user = Auth::user();
        $wallet = UserWallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'pending_amount' => 0, 'frozen_amount' => 0, 'advance_balance' => 0]
        );

        // Récupérer les transactions récentes (utiliser Transaction à la place)
        $transactions = \App\Models\Transaction::where('user_id', $user->id)
            ->whereIn('type', ['CREDIT', 'DEBIT', 'PAYMENT'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Calculer le montant collecté aujourd'hui (COD)
        $todayCollected = \App\Models\Package::where('assigned_deliverer_id', $user->id)
            ->where('status', 'DELIVERED')
            ->whereDate('delivered_at', today())
            ->sum('cod_amount');

        // Nombre total de transactions
        $transactionCount = \App\Models\Transaction::where('user_id', $user->id)
            ->whereIn('type', ['CREDIT', 'DEBIT', 'PAYMENT'])
            ->count();

        return view('deliverer.wallet-modern', compact('wallet', 'transactions', 'todayCollected', 'transactionCount'));
    }

    /**
     * API: Run Sheet
     */
    public function apiRunSheet()
    {
        // Réutiliser la logique du Run Sheet Unifié
        $user = Auth::user();
        $gouvernorats = $this->getDelivererGouvernorats();
        
        // ... (même logique que runSheetUnified mais retourne JSON)
        
        return response()->json([
            'success' => true,
            'tasks' => [], // À implémenter
            'stats' => []
        ]);
    }

    /**
     * API: Détail Tâche
     */
    public function apiTaskDetail($id)
    {
        $package = Package::findOrFail($id);
        
        if ($package->assigned_deliverer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tâche non assignée'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'task' => $package
        ]);
    }

    /**
     * API: Wallet Balance
     */
    public function apiWalletBalance()
    {
        $wallet = UserWallet::where('user_id', Auth::id())->first();
        
        return response()->json([
            'success' => true,
            'balance' => $wallet->balance ?? 0,
            'pending' => $wallet->pending_amount ?? 0,
            'frozen' => $wallet->frozen_amount ?? 0
        ]);
    }

    /**
     * API: Wallet Transactions
     */
    public function apiWalletTransactions()
    {
        $user = Auth::user();
        $transactions = \App\Models\Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
        
        return response()->json([
            'success' => true,
            'transactions' => $transactions
        ]);
    }

    /**
     * API: Returns (Retours fournisseur)
     */
    public function apiReturns()
    {
        $user = Auth::user();
        $gouvernorats = is_array($user->deliverer_gouvernorats) ? $user->deliverer_gouvernorats : [];
        
        $returns = \App\Models\ReturnPackage::where('assigned_deliverer_id', $user->id)
            ->whereIn('status', ['pending', 'assigned'])
            ->when(!empty($gouvernorats), function($q) use ($gouvernorats) {
                return $q->whereHas('delegation', function($subQ) use ($gouvernorats) {
                    $subQ->whereIn('governorate', $gouvernorats);
                });
            })
            ->with('delegation')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'returns' => $returns,
            'count' => $returns->count()
        ]);
    }

    /**
     * API: Payments (Paiements espèce)
     */
    public function apiPayments()
    {
        $user = Auth::user();
        $gouvernorats = is_array($user->deliverer_gouvernorats) ? $user->deliverer_gouvernorats : [];
        
        $payments = \App\Models\WithdrawalRequest::where('assigned_deliverer_id', $user->id)
            ->whereIn('status', ['pending', 'assigned'])
            ->when(!empty($gouvernorats), function($q) use ($gouvernorats) {
                return $q->whereHas('client', function($subQ) use ($gouvernorats) {
                    $subQ->whereIn('governorate', $gouvernorats);
                });
            })
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'payments' => $payments,
            'count' => $payments->count()
        ]);
    }

    /**
     * API: Stats générales
     */
    public function apiStats()
    {
        $user = Auth::user();
        
        $stats = [
            'total_deliveries' => Package::where('assigned_deliverer_id', $user->id)
                ->where('status', 'DELIVERED')
                ->count(),
            'total_pickups' => PickupRequest::where('assigned_deliverer_id', $user->id)
                ->where('status', 'picked_up')
                ->count(),
            'pending_tasks' => Package::where('assigned_deliverer_id', $user->id)
                ->whereIn('status', ['AVAILABLE', 'ACCEPTED', 'PICKED_UP'])
                ->count(),
            'today_deliveries' => Package::where('assigned_deliverer_id', $user->id)
                ->where('status', 'DELIVERED')
                ->whereDate('delivered_at', today())
                ->count(),
            'wallet_balance' => UserWallet::where('user_id', $user->id)->value('balance') ?? 0
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * API: Stats du jour
     */
    public function apiStatsToday()
    {
        $user = Auth::user();
        
        $stats = [
            'deliveries' => Package::where('assigned_deliverer_id', $user->id)
                ->where('status', 'DELIVERED')
                ->whereDate('delivered_at', today())
                ->count(),
            'pickups' => PickupRequest::where('assigned_deliverer_id', $user->id)
                ->where('status', 'picked_up')
                ->whereDate('picked_up_at', today())
                ->count(),
            'cod_collected' => Package::where('assigned_deliverer_id', $user->id)
                ->where('status', 'DELIVERED')
                ->whereDate('delivered_at', today())
                ->sum('cod_amount'),
            'pending' => Package::where('assigned_deliverer_id', $user->id)
                ->whereIn('status', ['AVAILABLE', 'ACCEPTED', 'PICKED_UP'])
                ->count()
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats,
            'date' => today()->format('Y-m-d')
        ]);
    }
}
