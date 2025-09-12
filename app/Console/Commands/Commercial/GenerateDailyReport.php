<?php

namespace App\Console\Commands\Commercial;

use Illuminate\Console\Command;
use App\Models\Package;
use App\Models\Complaint;
use App\Models\WithdrawalRequest;
use App\Models\FinancialTransaction;
use App\Models\DelivererWalletEmptying;

class GenerateDailyReport extends Command
{
    protected $signature = 'commercial:daily-report {--date= : Date pour le rapport (YYYY-MM-DD)}';
    protected $description = 'Générer un rapport quotidien des activités commercial';

    public function handle()
    {
        $date = $this->option('date') ? date($this->option('date')) : today();
        
        $this->info("📊 Rapport quotidien pour : {$date}");
        $this->info("==========================================");
        
        // Statistiques colis
        $packagesCreated = Package::whereDate('created_at', $date)->count();
        $packagesDelivered = Package::whereDate('updated_at', $date)->where('status', 'DELIVERED')->count();
        $totalCodToday = Package::whereDate('created_at', $date)->sum('cod_amount');
        
        $this->info("📦 COLIS :");
        $this->info("   Créés aujourd'hui : {$packagesCreated}");
        $this->info("   Livrés aujourd'hui : {$packagesDelivered}");
        $this->info("   COD total : " . number_format($totalCodToday, 3) . " DT");
        
        // Statistiques réclamations
        $complaintsCreated = Complaint::whereDate('created_at', $date)->count();
        $complaintsResolved = Complaint::whereDate('resolved_at', $date)->count();
        $urgentComplaints = Complaint::whereDate('created_at', $date)->where('priority', 'URGENT')->count();
        
        $this->info("🚨 RÉCLAMATIONS :");
        $this->info("   Nouvelles : {$complaintsCreated}");
        $this->info("   Résolues : {$complaintsResolved}");
        $this->info("   Urgentes : {$urgentComplaints}");
        
        // Statistiques retraits
        $withdrawalRequests = WithdrawalRequest::whereDate('created_at', $date)->count();
        $withdrawalAmount = WithdrawalRequest::whereDate('created_at', $date)->sum('amount');
        $withdrawalsProcessed = WithdrawalRequest::whereDate('processed_at', $date)->count();
        
        $this->info("💰 RETRAITS :");
        $this->info("   Demandes : {$withdrawalRequests}");
        $this->info("   Montant demandé : " . number_format($withdrawalAmount, 3) . " DT");
        $this->info("   Traitées : {$withdrawalsProcessed}");
        
        // Statistiques transactions
        $transactionsCount = FinancialTransaction::whereDate('created_at', $date)->count();
        $transactionsAmount = FinancialTransaction::whereDate('created_at', $date)->where('status', 'COMPLETED')->sum('amount');
        $pendingTransactions = FinancialTransaction::where('status', 'PENDING')->count();
        
        $this->info("💳 TRANSACTIONS :");
        $this->info("   Total : {$transactionsCount}");
        $this->info("   Montant : " . number_format($transactionsAmount, 3) . " DT");
        $this->info("   En attente : {$pendingTransactions}");
        
        // Statistiques vidages
        $emptyingsCount = DelivererWalletEmptying::whereDate('emptying_date', $date)->count();
        $emptyingsAmount = DelivererWalletEmptying::whereDate('emptying_date', $date)->sum('wallet_amount');
        $discrepancies = DelivererWalletEmptying::whereDate('emptying_date', $date)->where('discrepancy_amount', '!=', 0)->count();
        
        $this->info("🏦 VIDAGES WALLET :");
        $this->info("   Nombres : {$emptyingsCount}");
        $this->info("   Montant total : " . number_format($emptyingsAmount, 3) . " DT");
        $this->info("   Avec écarts : {$discrepancies}");
        
        $this->info("==========================================");
        $this->info("✅ Rapport généré avec succès !");
        
        return Command::SUCCESS;
    }
}