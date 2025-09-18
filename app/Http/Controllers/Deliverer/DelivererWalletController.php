<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\DelivererWalletEmptying;
use App\Services\WalletHelperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DelivererWalletController extends Controller
{
    /**
     * Page principale wallet livreur
     */
    public function index()
    {
        $user = Auth::user();
        $user->ensureWallet();
        $wallet = $user->wallet;

        // Sources détaillées du wallet
        $walletSources = $this->getWalletSources();
        
        // Historique récent (10 dernières transactions)
        $recentTransactions = $this->getRecentTransactions(10);
        
        // Stats wallet
        $walletStats = WalletHelperService::getWalletStats($user, 30);
        $walletStatus = WalletHelperService::getWalletStatus($wallet);
        $walletSummary = WalletHelperService::getWalletSummary($wallet);
        
        // Dernière vidange
        $lastEmptying = DelivererWalletEmptying::where('deliverer_id', $user->id)
            ->with('commercial')
            ->orderBy('emptying_date', 'desc')
            ->first();

        return view('deliverer.wallet.index', compact(
            'wallet',
            'walletSources',
            'recentTransactions',
            'walletStats',
            'walletStatus',
            'walletSummary',
            'lastEmptying'
        ));
    }

    /**
     * Historique complet des transactions
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->input('per_page', 20);
        
        $query = FinancialTransaction::where('user_id', $user->id)
            ->with('package')
            ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }

        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }

        $transactions = $query->paginate($perPage);

        // Stats pour la période filtrée
        $filteredStats = [
            'total_transactions' => $query->count(),
            'total_credits' => $query->clone()->where('amount', '>', 0)->sum('amount'),
            'total_debits' => abs($query->clone()->where('amount', '<', 0)->sum('amount')),
            'net_amount' => $query->clone()->sum('amount')
        ];

        return view('deliverer.wallet.history', compact('transactions', 'filteredStats'));
    }

    /**
     * Sources détaillées du wallet (COD par colis, fonds clients, etc.)
     */
    public function sources()
    {
        $user = Auth::user();
        $walletSources = $this->getDetailedWalletSources();
        
        // Grouper par type de source
        $sourcesByType = $walletSources->groupBy('source_type');
        
        return view('deliverer.wallet.sources', compact('walletSources', 'sourcesByType'));
    }

    /**
     * Demander vidange wallet auprès du commercial
     */
    public function requestEmptying(Request $request)
    {
        $user = Auth::user();
        $wallet = $user->wallet;
        
        if (!$wallet || $wallet->balance <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Votre wallet est vide, aucune vidange nécessaire.'
            ], 400);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
            'urgent' => 'boolean'
        ]);

        try {
            // Créer la demande de vidange
            $emptyingRequest = DelivererWalletEmptying::create([
                'deliverer_id' => $user->id,
                'wallet_amount' => $wallet->balance,
                'physical_amount' => 0, // Sera rempli par le commercial
                'discrepancy_amount' => 0,
                'emptying_date' => now(),
                'notes' => $validated['notes'] ?? null,
                'emptying_details' => $this->getWalletSources()->toArray(),
                'deliverer_acknowledged' => false
            ]);

            // Notification au commercial (TODO: implémenter le système de notifications)
            // NotificationService::notifyCommercialWalletEmptyingRequest($user, $emptyingRequest);

            return response()->json([
                'success' => true,
                'message' => "Demande de vidange envoyée (Solde: {$wallet->getFormattedBalanceAttribute()}). Un commercial vous contactera bientôt."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la demande de vidange. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * API - Solde wallet temps réel
     */
    public function apiBalance()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        return response()->json([
            'balance' => $wallet ? $wallet->balance : 0,
            'formatted_balance' => $wallet ? $wallet->getFormattedBalanceAttribute() : '0.000 DT',
            'available_balance' => $wallet ? WalletHelperService::getAvailableBalance($wallet) : 0,
            'status' => $wallet ? WalletHelperService::getWalletStatus($wallet) : null
        ]);
    }

    /**
     * API - Historique graphique des revenus
     */
    public function apiEarningsChart(Request $request)
    {
        $user = Auth::user();
        $days = $request->input('days', 7); // 7 jours par défaut
        
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
        
        // Revenus par jour
        $dailyEarnings = FinancialTransaction::where('user_id', $user->id)
            ->where('type', 'COD_COLLECTION')
            ->where('status', 'COMPLETED')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Générer les données pour tous les jours
        $chartData = [];
        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::now()->subDays($days - 1 - $i);
            $dateStr = $date->format('Y-m-d');
            
            $chartData[] = [
                'date' => $dateStr,
                'formatted_date' => $date->format('d/m'),
                'day_name' => $date->format('D'),
                'amount' => isset($dailyEarnings[$dateStr]) ? floatval($dailyEarnings[$dateStr]->total) : 0
            ];
        }

        return response()->json([
            'chart_data' => $chartData,
            'total_period' => collect($chartData)->sum('amount'),
            'average_daily' => round(collect($chartData)->avg('amount'), 3),
            'best_day' => collect($chartData)->sortByDesc('amount')->first()
        ]);
    }

    /**
     * Obtenir les sources du wallet (résumé)
     */
    private function getWalletSources()
    {
        $user = Auth::user();
        
        // COD collectés (non encore vidangés)
        $codSources = FinancialTransaction::where('user_id', $user->id)
            ->where('type', 'COD_COLLECTION')
            ->where('status', 'COMPLETED')
            ->with('package')
            ->get()
            ->map(function ($transaction) {
                return [
                    'source_type' => 'COD',
                    'source_id' => $transaction->package_id,
                    'description' => "COD colis #{$transaction->package->package_code}",
                    'amount' => $transaction->amount,
                    'date' => $transaction->created_at
                ];
            });

        // Fonds clients ajoutés
        $clientFunds = FinancialTransaction::where('user_id', $user->id)
            ->where('type', 'CLIENT_FUND_ADD')
            ->where('status', 'COMPLETED')
            ->get()
            ->map(function ($transaction) {
                return [
                    'source_type' => 'CLIENT_FUND',
                    'source_id' => $transaction->id,
                    'description' => $transaction->description ?: 'Recharge client',
                    'amount' => $transaction->amount,
                    'date' => $transaction->created_at
                ];
            });

        return $codSources->concat($clientFunds)->sortByDesc('date');
    }

    /**
     * Obtenir les sources détaillées du wallet
     */
    private function getDetailedWalletSources()
    {
        $user = Auth::user();
        
        return FinancialTransaction::where('user_id', $user->id)
            ->where('status', 'COMPLETED')
            ->where('amount', '>', 0) // Seulement les crédits
            ->with(['package'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($transaction) {
                $sourceType = match($transaction->type) {
                    'COD_COLLECTION' => 'COD',
                    'CLIENT_FUND_ADD' => 'CLIENT_FUND',
                    'DELIVERY_COMMISSION' => 'COMMISSION',
                    default => 'OTHER'
                };

                return [
                    'id' => $transaction->id,
                    'source_type' => $sourceType,
                    'source_id' => $transaction->package_id,
                    'transaction_id' => $transaction->transaction_id,
                    'description' => $transaction->description,
                    'amount' => $transaction->amount,
                    'date' => $transaction->created_at,
                    'package_code' => $transaction->package ? $transaction->package->package_code : null,
                    'formatted_amount' => WalletHelperService::formatAmount($transaction->amount),
                    'icon' => WalletHelperService::getTransactionIcon($transaction->type)
                ];
            });
    }

    /**
     * Obtenir les transactions récentes
     */
    private function getRecentTransactions($limit = 10)
    {
        $user = Auth::user();
        
        return FinancialTransaction::where('user_id', $user->id)
            ->with('package')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'type_display' => $transaction->getTypeDisplayAttribute(),
                    'description' => $transaction->description,
                    'amount' => $transaction->amount,
                    'formatted_amount' => $transaction->getFormattedAmountAttribute(),
                    'status' => $transaction->status,
                    'status_display' => $transaction->getStatusDisplayAttribute(),
                    'status_color' => $transaction->getStatusColorAttribute(),
                    'date' => $transaction->created_at,
                    'package_code' => $transaction->package ? $transaction->package->package_code : null,
                    'is_credit' => $transaction->amount > 0
                ];
            });
    }

    /**
     * Export wallet données (CSV)
     */
    public function exportTransactions(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $transactions = FinancialTransaction::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('package')
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = "wallet_transactions_{$user->id}_{$startDate}_to_{$endDate}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // Headers CSV
            fputcsv($file, [
                'Date',
                'Transaction ID',
                'Type',
                'Description',
                'Montant (DT)',
                'Statut',
                'Code Colis',
                'Solde Avant',
                'Solde Après'
            ]);

            // Données
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->created_at->format('d/m/Y H:i:s'),
                    $transaction->transaction_id,
                    $transaction->getTypeDisplayAttribute(),
                    $transaction->description,
                    number_format($transaction->amount, 3),
                    $transaction->getStatusDisplayAttribute(),
                    $transaction->package ? $transaction->package->package_code : '',
                    number_format($transaction->wallet_balance_before ?? 0, 3),
                    number_format($transaction->wallet_balance_after ?? 0, 3)
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }
}