<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Services\CommercialService;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\DelivererWalletEmptying;
use App\Models\Package;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DelivererController extends Controller
{
    protected $commercialService;

    public function __construct(CommercialService $commercialService)
    {
        $this->commercialService = $commercialService;
    }

    public function index(Request $request)
    {
        $query = User::with(['wallet'])
                    ->where('role', 'DELIVERER')
                    ->where('account_status', 'ACTIVE');

        // Filtres
        if ($request->filled('min_balance')) {
            $query->whereHas('wallet', function ($wallet) use ($request) {
                $wallet->where('balance', '>=', $request->min_balance);
            });
        }

        if ($request->filled('high_balance_only') && $request->high_balance_only) {
            $query->whereHas('wallet', function ($wallet) {
                $wallet->where('balance', '>', 100); // Seuil configurable
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $deliverers = $query->orderBy('name')->paginate(20);

        // Ajouter des informations supplémentaires pour chaque livreur
        $deliverers->getCollection()->transform(function ($deliverer) {
            $deliverer->wallet_needs_emptying = ($deliverer->wallet->balance ?? 0) > 100;
            $deliverer->last_emptying = DelivererWalletEmptying::where('deliverer_id', $deliverer->id)
                                                             ->orderBy('emptying_date', 'desc')
                                                             ->first();
            $deliverer->packages_today = Package::where('assigned_deliverer_id', $deliverer->id)
                                               ->whereDate('assigned_at', today())
                                               ->count();
            return $deliverer;
        });

        $stats = [
            'total_deliverers' => User::where('role', 'DELIVERER')->where('account_status', 'ACTIVE')->count(),
            'high_balance_count' => User::join('user_wallets', 'users.id', '=', 'user_wallets.user_id')
                                       ->where('users.role', 'DELIVERER')
                                       ->where('user_wallets.balance', '>', 100)
                                       ->count(),
            'total_wallet_amount' => UserWallet::join('users', 'user_wallets.user_id', '=', 'users.id')
                                              ->where('users.role', 'DELIVERER')
                                              ->sum('user_wallets.balance'),
            'emptyings_today' => DelivererWalletEmptying::whereDate('emptying_date', today())->count(),
            'total_emptied_today' => DelivererWalletEmptying::whereDate('emptying_date', today())->sum('wallet_amount'),
        ];

        return view('commercial.deliverers.index', compact('deliverers', 'stats'));
    }

    public function show(User $deliverer)
    {
        if ($deliverer->role !== 'DELIVERER') {
            abort(404);
        }

        $deliverer->load([
            'wallet',
            'transactions' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(20);
            }
        ]);

        // Historique des vidages
        $emptyings = DelivererWalletEmptying::where('deliverer_id', $deliverer->id)
                                           ->with('commercial')
                                           ->orderBy('emptying_date', 'desc')
                                           ->paginate(10);

        // Colis assignés
        $packages = Package::where('assigned_deliverer_id', $deliverer->id)
                          ->with(['sender', 'delegationFrom', 'delegationTo'])
                          ->orderBy('created_at', 'desc')
                          ->limit(20)
                          ->get();

        // Retraits assignés
        $cashDeliveries = WithdrawalRequest::where('assigned_deliverer_id', $deliverer->id)
                                         ->where('method', 'CASH_DELIVERY')
                                         ->with('client')
                                         ->orderBy('processed_at', 'desc')
                                         ->limit(10)
                                         ->get();

        $stats = [
            'wallet_balance' => $deliverer->wallet->balance ?? 0,
            'total_packages' => Package::where('assigned_deliverer_id', $deliverer->id)->count(),
            'packages_delivered' => Package::where('assigned_deliverer_id', $deliverer->id)
                                          ->whereIn('status', ['DELIVERED', 'PAID'])
                                          ->count(),
            'packages_today' => Package::where('assigned_deliverer_id', $deliverer->id)
                                      ->whereDate('assigned_at', today())
                                      ->count(),
            'cash_deliveries_pending' => WithdrawalRequest::where('assigned_deliverer_id', $deliverer->id)
                                                         ->where('status', 'IN_PROGRESS')
                                                         ->count(),
            'total_emptyings' => DelivererWalletEmptying::where('deliverer_id', $deliverer->id)->count(),
            'last_emptying' => DelivererWalletEmptying::where('deliverer_id', $deliverer->id)
                                                     ->orderBy('emptying_date', 'desc')
                                                     ->first(),
        ];

        return view('commercial.deliverers.show', compact('deliverer', 'packages', 'cashDeliveries', 'stats'))->with('walletEmptyings', $emptyings);
    }

    public function walletDetails(User $deliverer)
    {
        if ($deliverer->role !== 'DELIVERER') {
            abort(404);
        }

        $deliverer->load('wallet');
        
        // Transactions récentes avec détails
        $transactions = $deliverer->transactions()
                                 ->orderBy('created_at', 'desc')
                                 ->paginate(50);

        // Sources du solde actuel (approximation basée sur les transactions récentes)
        $walletSources = $this->getWalletSources($deliverer);

        return view('commercial.deliverers.wallet', compact('deliverer', 'transactions', 'walletSources'));
    }

    public function emptyWallet(Request $request, User $deliverer)
    {
        if ($deliverer->role !== 'DELIVERER') {
            abort(404);
        }

        $request->validate([
            'physical_amount' => 'nullable|numeric|min:0|max:' . ($deliverer->wallet->balance ?? 0),
            'notes' => 'nullable|string|max:1000',
            'force_empty' => 'boolean',
        ]);

        try {
            $walletBalance = $deliverer->wallet->balance ?? 0;
            
            if ($walletBalance == 0) {
                return back()->with('info', 'Le wallet est déjà vide.');
            }

            $physicalAmount = $request->filled('physical_amount') 
                ? $request->physical_amount 
                : $walletBalance;

            $discrepancy = $walletBalance - $physicalAmount;

            // Vérification de sécurité pour les écarts importants
            if (abs($discrepancy) > 50 && !$request->boolean('force_empty')) {
                return back()->withErrors([
                    'error' => "Écart important détecté: " . number_format($discrepancy, 3) . " DT. " .
                               "Vérifiez les montants ou activez 'Force Empty' si vous êtes sûr."
                ]);
            }

            $emptying = $this->commercialService->emptyDelivererWallet(
                $deliverer,
                Auth::user(),
                $physicalAmount
            );

            $message = "Wallet du livreur {$deliverer->name} vidé avec succès. ";
            $message .= "Montant wallet: " . number_format($emptying->wallet_amount, 3) . " DT, ";
            $message .= "Montant physique: " . number_format($emptying->physical_amount, 3) . " DT";
            
            if ($emptying->discrepancy_amount != 0) {
                $message .= ", Écart: " . $emptying->formatted_discrepancy;
            }

            return redirect()->route('commercial.deliverers.show', $deliverer)
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors du vidage: ' . $e->getMessage()]);
        }
    }

    public function generateEmptyingReceipt(DelivererWalletEmptying $emptying)
    {
        if ($emptying->commercial_id !== Auth::id() && !Auth::user()->isSupervisor()) {
            abort(403);
        }

        $emptying->load(['deliverer', 'commercial']);

        // TODO: Implémenter la génération PDF avec DomPDF
        // $pdf = PDF::loadView('commercial.receipts.wallet_emptying', compact('emptying'));
        // return $pdf->stream("recu_vidage_{$emptying->id}.pdf");

        // Pour l'instant, retourner une vue simple
        return view('commercial.receipts.wallet_emptying', compact('emptying'));
    }

    public function bulkEmpty(Request $request)
    {
        $request->validate([
            'deliverer_ids' => 'required|array|min:1',
            'deliverer_ids.*' => 'exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $emptiedCount = 0;
            $totalAmount = 0;
            $errors = [];
            
            $deliverers = User::whereIn('id', $request->deliverer_ids)
                             ->where('role', 'DELIVERER')
                             ->with('wallet')
                             ->get();

            foreach ($deliverers as $deliverer) {
                try {
                    $walletBalance = $deliverer->wallet->balance ?? 0;
                    
                    if ($walletBalance == 0) {
                        continue; // Skip empty wallets
                    }

                    $emptying = $this->commercialService->emptyDelivererWallet(
                        $deliverer,
                        Auth::user(),
                        $walletBalance // Assume physical amount = wallet amount for bulk
                    );

                    $emptiedCount++;
                    $totalAmount += $emptying->wallet_amount;
                } catch (\Exception $e) {
                    $errors[] = "{$deliverer->name}: {$e->getMessage()}";
                }
            }

            $message = "{$emptiedCount} wallets vidés avec succès. Total: " . number_format($totalAmount, 3) . " DT";
            if (!empty($errors)) {
                $message .= ". Erreurs: " . implode(', ', $errors);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors du vidage groupé: ' . $e->getMessage()]);
        }
    }

    public function assignCashDelivery(Request $request, User $deliverer)
    {
        if ($deliverer->role !== 'DELIVERER') {
            abort(404);
        }

        $request->validate([
            'withdrawal_id' => 'required|exists:withdrawal_requests,id',
        ]);

        try {
            $withdrawal = WithdrawalRequest::where('id', $request->withdrawal_id)
                                         ->where('status', 'APPROVED')
                                         ->where('method', 'CASH_DELIVERY')
                                         ->firstOrFail();

            $this->commercialService->assignWithdrawalToDeliverer($withdrawal, $deliverer);

            return back()->with('success', 
                "Retrait {$withdrawal->request_code} assigné à {$deliverer->name}. " .
                "Code: {$withdrawal->delivery_receipt_code}"
            );
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de l\'assignation: ' . $e->getMessage()]);
        }
    }

    // Méthode privée pour analyser les sources du wallet
    private function getWalletSources(User $deliverer)
    {
        $transactions = $deliverer->transactions()
                                 ->where('status', 'COMPLETED')
                                 ->where('amount', '>', 0)
                                 ->where('created_at', '>=', now()->subDays(30))
                                 ->orderBy('created_at', 'desc')
                                 ->get();

        $sources = [
            'COD_COLLECTION' => 0,
            'WALLET_RECHARGE' => 0,
            'DELIVERER_PAYMENT' => 0,
            'OTHER' => 0,
        ];

        foreach ($transactions as $transaction) {
            $type = $transaction->type;
            if (array_key_exists($type, $sources)) {
                $sources[$type] += $transaction->amount;
            } else {
                $sources['OTHER'] += $transaction->amount;
            }
        }

        return $sources;
    }

    // ==================== API ENDPOINTS ====================

    public function apiSearch(Request $request)
    {
        $search = $request->input('q', '');
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        
    }

    public function apiHighBalanceDeliverers()
    {
        $deliverers = User::join('user_wallets', 'users.id', '=', 'user_wallets.user_id')
                         ->where('users.role', 'DELIVERER')
                         ->where('users.account_status', 'ACTIVE')
                         ->where('user_wallets.balance', '>', 100)
                         ->select('users.*', 'user_wallets.balance as wallet_balance')
                         ->orderBy('user_wallets.balance', 'desc')
                         ->get()
                         ->map(function ($deliverer) {
                             return [
                                 'id' => $deliverer->id,
                                 'name' => $deliverer->name,
                                 'phone' => $deliverer->phone,
                                 'wallet_balance' => number_format($deliverer->wallet_balance, 3),
                                 'needs_urgent_emptying' => $deliverer->wallet_balance > 200,
                                 'show_url' => route('commercial.deliverers.show', $deliverer->id),
                             ];
                         });

        return response()->json($deliverers);
    }

    public function apiStats()
    {
        $stats = [
            'total_active' => User::where('role', 'DELIVERER')->where('account_status', 'ACTIVE')->count(),
            'high_balance_count' => User::join('user_wallets', 'users.id', '=', 'user_wallets.user_id')
                                       ->where('users.role', 'DELIVERER')
                                       ->where('user_wallets.balance', '>', 100)
                                       ->count(),
            'urgent_emptying_count' => User::join('user_wallets', 'users.id', '=', 'user_wallets.user_id')
                                          ->where('users.role', 'DELIVERER')
                                          ->where('user_wallets.balance', '>', 200)
                                          ->count(),
            'total_wallet_amount' => UserWallet::join('users', 'user_wallets.user_id', '=', 'users.id')
                                              ->where('users.role', 'DELIVERER')
                                              ->sum('user_wallets.balance'),
            'emptyings_today' => DelivererWalletEmptying::whereDate('emptying_date', today())->count(),
            'amount_emptied_today' => DelivererWalletEmptying::whereDate('emptying_date', today())->sum('wallet_amount'),
        ];

        return response()->json($stats);
    }

    public function apiRecentEmptyings()
    {
        $emptyings = DelivererWalletEmptying::with(['deliverer', 'commercial'])
                                           ->orderBy('emptying_date', 'desc')
                                           ->limit(20)
                                           ->get()
                                           ->map(function ($emptying) {
                                               return [
                                                   'id' => $emptying->id,
                                                   'deliverer_name' => $emptying->deliverer->name,
                                                   'commercial_name' => $emptying->commercial->name,
                                                   'wallet_amount' => number_format($emptying->wallet_amount, 3),
                                                   'physical_amount' => number_format($emptying->physical_amount, 3),
                                                   'discrepancy' => $emptying->formatted_discrepancy,
                                                   'discrepancy_color' => $emptying->discrepancy_color,
                                                   'emptying_date' => $emptying->emptying_date->diffForHumans(),
                                                   'has_discrepancy' => $emptying->hasDiscrepancy(),
                                               ];
                                           });

        return response()->json($emptyings);
    }

    public function apiAvailableDeliverers()
    {
        $deliverers = User::where('role', 'DELIVERER')
                         ->where('account_status', 'ACTIVE')
                         ->orderBy('name', 'asc')
                         ->get()
                         ->map(function ($deliverer) {
                             return [
                                 'id' => $deliverer->id,
                                 'name' => $deliverer->first_name ? ($deliverer->first_name . ' ' . ($deliverer->last_name ?? '')) : $deliverer->name,
                                 'phone' => $deliverer->phone,
                                 'email' => $deliverer->email,
                             ];
                         });

        return response()->json($deliverers);
    }

    public function apiActiveDeliverers()
    {
        // Alias pour apiAvailableDeliverers
        return $this->apiAvailableDeliverers();
    }
}