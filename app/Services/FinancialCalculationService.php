<?php

namespace App\Services;

use App\Models\FixedCharge;
use App\Models\DepreciableAsset;
use App\Models\Vehicle;
use App\Models\Package;
use App\Models\FinancialTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialCalculationService
{
    /**
     * Calcule le chiffre d'affaires pour une période donnée
     * 
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    public function calculateRevenue($startDate, $endDate): float
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Chiffre d'affaires = transactions CREDIT dans la période
        return FinancialTransaction::where('type', 'CREDIT')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');
    }

    /**
     * Calcule le chiffre d'affaires prévisionnel basé sur les colis
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function calculateProjectedRevenue($startDate, $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Colis livrés dans la période
        $deliveredPackages = Package::whereBetween('delivered_at', [$start, $end])
            ->whereIn('status', ['DELIVERED'])
            ->get();

        $deliveryRevenue = 0;
        $codRevenue = 0;

        foreach ($deliveredPackages as $package) {
            // Frais de livraison
            $deliveryRevenue += $package->delivery_price ?? 0;
            
            // COD (si payé par le client)
            if ($package->cod_amount > 0 && $package->payment_status === 'PAID') {
                $codRevenue += $package->cod_amount;
            }
        }

        // Colis retournés (frais de retour)
        $returnedPackages = Package::whereBetween('updated_at', [$start, $end])
            ->where('status', 'RETURNED')
            ->get();

        $returnRevenue = 0;
        foreach ($returnedPackages as $package) {
            $returnRevenue += $package->return_price ?? 0;
        }

        return [
            'delivery_revenue' => round($deliveryRevenue, 3),
            'cod_revenue' => round($codRevenue, 3),
            'return_revenue' => round($returnRevenue, 3),
            'total_revenue' => round($deliveryRevenue + $returnRevenue, 3), // COD n'est pas un revenu pour la plateforme
        ];
    }

    /**
     * Calcule les charges fixes totales pour une période
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function calculateFixedCharges($startDate, $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $fixedCharges = FixedCharge::active()->get();
        $depreciableAssets = DepreciableAsset::active()->notFullyDepreciated()->get();

        $totalFixedCharges = 0;
        $totalDepreciation = 0;
        $breakdown = [];

        // Charges fixes
        foreach ($fixedCharges as $charge) {
            $amount = $charge->calculateForPeriod($start, $end);
            $totalFixedCharges += $amount;
            
            $breakdown['charges'][] = [
                'name' => $charge->name,
                'periodicity' => $charge->periodicity,
                'amount' => round($amount, 3),
            ];
        }

        // Amortissements
        foreach ($depreciableAssets as $asset) {
            $amount = $asset->calculateForPeriod($start, $end);
            $totalDepreciation += $amount;
            
            $breakdown['depreciation'][] = [
                'name' => $asset->name,
                'amount' => round($amount, 3),
            ];
        }

        return [
            'total_fixed_charges' => round($totalFixedCharges, 3),
            'total_depreciation' => round($totalDepreciation, 3),
            'total' => round($totalFixedCharges + $totalDepreciation, 3),
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Calcule les charges variables (véhicules) pour une période
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function calculateVariableCharges($startDate, $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $vehicles = Vehicle::active()->get();
        $workingDays = $this->calculateWorkingDays($start, $end);

        $totalVariableCharges = 0;
        $breakdown = [];

        foreach ($vehicles as $vehicle) {
            $avgDailyKm = $vehicle->calculateAverageDailyKm();
            
            if ($avgDailyKm <= 0) {
                continue; // Pas de données pour ce véhicule
            }

            $totalKm = $workingDays * $avgDailyKm;
            
            $depreciation = $totalKm * $vehicle->depreciation_cost_per_km;
            $oilChange = $totalKm * $vehicle->oil_change_cost_per_km;
            $sparkPlugs = $totalKm * $vehicle->spark_plug_cost_per_km;
            $tires = $totalKm * $vehicle->tire_cost_per_km;
            $fuel = $totalKm * $vehicle->fuel_cost_per_km;
            
            $vehicleTotal = $depreciation + $oilChange + $sparkPlugs + $tires + $fuel;
            $totalVariableCharges += $vehicleTotal;

            $breakdown[] = [
                'vehicle_name' => $vehicle->name,
                'avg_daily_km' => round($avgDailyKm, 2),
                'working_days' => $workingDays,
                'total_km' => round($totalKm, 2),
                'costs' => [
                    'depreciation' => round($depreciation, 3),
                    'oil_change' => round($oilChange, 3),
                    'spark_plugs' => round($sparkPlugs, 3),
                    'tires' => round($tires, 3),
                    'fuel' => round($fuel, 3),
                ],
                'total' => round($vehicleTotal, 3),
            ];
        }

        return [
            'total_variable_charges' => round($totalVariableCharges, 3),
            'working_days' => $workingDays,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Calcule le bénéfice prévisionnel pour une période
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function calculateProjectedProfit($startDate, $endDate): array
    {
        $revenue = $this->calculateProjectedRevenue($startDate, $endDate);
        $fixedCharges = $this->calculateFixedCharges($startDate, $endDate);
        $variableCharges = $this->calculateVariableCharges($startDate, $endDate);

        $totalRevenue = $revenue['total_revenue'];
        $totalCharges = $fixedCharges['total'] + $variableCharges['total_variable_charges'];
        $profit = $totalRevenue - $totalCharges;

        $profitMargin = $totalRevenue > 0 ? ($profit / $totalRevenue) * 100 : 0;

        return [
            'revenue' => $revenue,
            'fixed_charges' => $fixedCharges,
            'variable_charges' => $variableCharges,
            'total_revenue' => round($totalRevenue, 3),
            'total_charges' => round($totalCharges, 3),
            'profit' => round($profit, 3),
            'profit_margin' => round($profitMargin, 2),
        ];
    }

    /**
     * Génère un rapport financier complet pour une période
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function generateFinancialReport($startDate, $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $profitData = $this->calculateProjectedProfit($start, $end);
        $packageStats = $this->getPackageStatistics($start, $end);
        $trends = $this->calculateTrends($start, $end);

        return [
            'period' => [
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'days' => $end->diffInDays($start) + 1,
                'working_days' => $this->calculateWorkingDays($start, $end),
            ],
            'financial' => $profitData,
            'packages' => $packageStats,
            'trends' => $trends,
            'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Obtient les statistiques des colis pour une période
     * 
     * @param Carbon $start
     * @param Carbon $end
     * @return array
     */
    private function getPackageStatistics($start, $end): array
    {
        $created = Package::whereBetween('created_at', [$start, $end])->count();
        $delivered = Package::whereBetween('delivered_at', [$start, $end])
            ->where('status', 'DELIVERED')
            ->count();
        $returned = Package::whereBetween('updated_at', [$start, $end])
            ->where('status', 'RETURNED')
            ->count();
        $inProgress = Package::whereBetween('created_at', [$start, $end])
            ->whereNotIn('status', ['DELIVERED', 'RETURNED', 'CANCELLED'])
            ->count();

        $deliveryRate = $created > 0 ? ($delivered / $created) * 100 : 0;

        return [
            'created' => $created,
            'delivered' => $delivered,
            'returned' => $returned,
            'in_progress' => $inProgress,
            'delivery_rate' => round($deliveryRate, 2),
        ];
    }

    /**
     * Calcule les tendances (comparaison avec la période précédente)
     * 
     * @param Carbon $start
     * @param Carbon $end
     * @return array
     */
    private function calculateTrends($start, $end): array
    {
        $days = $end->diffInDays($start) + 1;
        
        // Période précédente
        $prevStart = $start->copy()->subDays($days);
        $prevEnd = $start->copy()->subDay();

        $currentProfit = $this->calculateProjectedProfit($start, $end);
        $previousProfit = $this->calculateProjectedProfit($prevStart, $prevEnd);

        $revenueChange = $this->calculatePercentageChange(
            $previousProfit['total_revenue'],
            $currentProfit['total_revenue']
        );

        $profitChange = $this->calculatePercentageChange(
            $previousProfit['profit'],
            $currentProfit['profit']
        );

        $currentPackages = $this->getPackageStatistics($start, $end);
        $previousPackages = $this->getPackageStatistics($prevStart, $prevEnd);

        $packagesChange = $this->calculatePercentageChange(
            $previousPackages['created'],
            $currentPackages['created']
        );

        return [
            'revenue_change' => round($revenueChange, 2),
            'profit_change' => round($profitChange, 2),
            'packages_change' => round($packagesChange, 2),
        ];
    }

    /**
     * Calcule le pourcentage de changement entre deux valeurs
     * 
     * @param float $oldValue
     * @param float $newValue
     * @return float
     */
    private function calculatePercentageChange($oldValue, $newValue): float
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }

        return (($newValue - $oldValue) / abs($oldValue)) * 100;
    }

    /**
     * Calcule le nombre de jours ouvrables (6 jours/semaine, excluant dimanche)
     * 
     * @param Carbon $start
     * @param Carbon $end
     * @return int
     */
    private function calculateWorkingDays($start, $end): int
    {
        $workingDays = 0;
        $current = $start->copy();
        
        while ($current <= $end) {
            // Exclure le dimanche (0)
            if ($current->dayOfWeek !== 0) {
                $workingDays++;
            }
            $current->addDay();
        }
        
        return $workingDays;
    }

    /**
     * Obtient un résumé financier rapide pour aujourd'hui
     * 
     * @return array
     */
    public function getTodaySummary(): array
    {
        $today = now()->startOfDay();
        $endOfDay = now()->endOfDay();

        return $this->calculateProjectedProfit($today, $endOfDay);
    }

    /**
     * Obtient un résumé financier pour ce mois
     * 
     * @return array
     */
    public function getMonthSummary(): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        return $this->calculateProjectedProfit($startOfMonth, $endOfMonth);
    }

    /**
     * Export des données financières au format CSV
     * 
     * @param string $startDate
     * @param string $endDate
     * @return string Path to the CSV file
     */
    public function exportToCSV($startDate, $endDate): string
    {
        $report = $this->generateFinancialReport($startDate, $endDate);
        
        $filename = 'financial_report_' . date('Y-m-d_His') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);
        
        // Créer le répertoire si nécessaire
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $file = fopen($filepath, 'w');
        
        // En-têtes
        fputcsv($file, ['Rapport Financier']);
        fputcsv($file, ['Période', $report['period']['start_date'] . ' - ' . $report['period']['end_date']]);
        fputcsv($file, []);
        
        // Revenus
        fputcsv($file, ['REVENUS']);
        fputcsv($file, ['Livraisons', $report['financial']['revenue']['delivery_revenue']]);
        fputcsv($file, ['Retours', $report['financial']['revenue']['return_revenue']]);
        fputcsv($file, ['Total Revenus', $report['financial']['revenue']['total_revenue']]);
        fputcsv($file, []);
        
        // Charges fixes
        fputcsv($file, ['CHARGES FIXES']);
        fputcsv($file, ['Charges fixes', $report['financial']['fixed_charges']['total_fixed_charges']]);
        fputcsv($file, ['Amortissements', $report['financial']['fixed_charges']['total_depreciation']]);
        fputcsv($file, ['Total Charges Fixes', $report['financial']['fixed_charges']['total']]);
        fputcsv($file, []);
        
        // Charges variables
        fputcsv($file, ['CHARGES VARIABLES']);
        foreach ($report['financial']['variable_charges']['breakdown'] as $vehicle) {
            fputcsv($file, [$vehicle['vehicle_name'], $vehicle['total']]);
        }
        fputcsv($file, ['Total Charges Variables', $report['financial']['variable_charges']['total_variable_charges']]);
        fputcsv($file, []);
        
        // Résultat
        fputcsv($file, ['RESULTAT']);
        fputcsv($file, ['Total Revenus', $report['financial']['total_revenue']]);
        fputcsv($file, ['Total Charges', $report['financial']['total_charges']]);
        fputcsv($file, ['Bénéfice', $report['financial']['profit']]);
        fputcsv($file, ['Marge (%)', $report['financial']['profit_margin']]);
        
        fclose($file);
        
        return $filepath;
    }
}
