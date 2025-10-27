<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Services\FinancialCalculationService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FinancialReportController extends Controller
{
    protected $financialService;

    public function __construct(FinancialCalculationService $financialService)
    {
        $this->financialService = $financialService;
    }

    /**
     * Page principale du reporting financier
     */
    public function index()
    {
        // Résumé du jour
        $todaySummary = $this->financialService->getTodaySummary();
        
        // Résumé du mois
        $monthSummary = $this->financialService->getMonthSummary();

        return view('supervisor.financial.reports.index', compact('todaySummary', 'monthSummary'));
    }

    /**
     * Génère un rapport personnalisé pour une période
     */
    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $report = $this->financialService->generateFinancialReport($startDate, $endDate);

        return view('supervisor.financial.reports.detailed', compact('report'));
    }

    /**
     * Aperçu rapide du rapport (pour sélection de période)
     */
    public function preview(Request $request)
    {
        $period = $request->get('period', 'today');
        
        [$startDate, $endDate] = $this->getPeriodDates($period, $request);

        $profit = $this->financialService->calculateProjectedProfit($startDate, $endDate);

        return response()->json([
            'period' => [
                'start' => $startDate->format('d/m/Y'),
                'end' => $endDate->format('d/m/Y'),
                'days' => $endDate->diffInDays($startDate) + 1,
            ],
            'summary' => [
                'revenue' => $profit['total_revenue'],
                'charges' => $profit['total_charges'],
                'profit' => $profit['profit'],
                'profit_margin' => $profit['profit_margin'],
            ],
        ]);
    }

    /**
     * Export du rapport en CSV
     */
    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $filepath = $this->financialService->exportToCSV(
            $request->start_date,
            $request->end_date
        );

        return response()->download($filepath)->deleteFileAfterSend(true);
    }

    /**
     * Export des rapports prédéfinis
     */
    public function exportPredefined(Request $request)
    {
        $period = $request->get('period', 'month');
        
        [$startDate, $endDate] = $this->getPeriodDates($period, $request);

        $filepath = $this->financialService->exportToCSV($startDate, $endDate);

        return response()->download($filepath)->deleteFileAfterSend(true);
    }

    /**
     * Vue comparative entre plusieurs périodes
     */
    public function compare(Request $request)
    {
        $request->validate([
            'period1_start' => 'required|date',
            'period1_end' => 'required|date|after_or_equal:period1_start',
            'period2_start' => 'required|date',
            'period2_end' => 'required|date|after_or_equal:period2_start',
        ]);

        $period1 = $this->financialService->generateFinancialReport(
            $request->period1_start,
            $request->period1_end
        );

        $period2 = $this->financialService->generateFinancialReport(
            $request->period2_start,
            $request->period2_end
        );

        $comparison = $this->calculateComparison($period1, $period2);

        return view('supervisor.financial.reports.compare', compact('period1', 'period2', 'comparison'));
    }

    /**
     * Graphiques et visualisations
     */
    public function charts(Request $request)
    {
        $period = $request->get('period', '30days');
        
        [$startDate, $endDate] = $this->getPeriodDates($period, $request);

        // Données pour les graphiques
        $days = $endDate->diffInDays($startDate) + 1;
        $chartData = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dayEnd = $date->copy()->endOfDay();
            
            $dayData = $this->financialService->calculateProjectedProfit($date, $dayEnd);
            
            $chartData[] = [
                'date' => $date->format('d/m'),
                'revenue' => $dayData['total_revenue'],
                'charges' => $dayData['total_charges'],
                'profit' => $dayData['profit'],
            ];
        }

        return response()->json($chartData);
    }

    /**
     * Tableau de bord financier en temps réel (API)
     */
    public function dashboard()
    {
        $today = $this->financialService->getTodaySummary();
        $month = $this->financialService->getMonthSummary();

        // Statistiques de tendance
        $yesterday = $this->financialService->calculateProjectedProfit(
            now()->subDay()->startOfDay(),
            now()->subDay()->endOfDay()
        );

        $revenueChange = $today['total_revenue'] - $yesterday['total_revenue'];
        $profitChange = $today['profit'] - $yesterday['profit'];

        return response()->json([
            'today' => [
                'revenue' => round($today['total_revenue'], 3),
                'charges' => round($today['total_charges'], 3),
                'profit' => round($today['profit'], 3),
                'margin' => round($today['profit_margin'], 2),
            ],
            'month' => [
                'revenue' => round($month['total_revenue'], 3),
                'charges' => round($month['total_charges'], 3),
                'profit' => round($month['profit'], 3),
                'margin' => round($month['profit_margin'], 2),
            ],
            'trends' => [
                'revenue_change' => round($revenueChange, 3),
                'profit_change' => round($profitChange, 3),
                'revenue_change_percent' => $yesterday['total_revenue'] > 0 
                    ? round(($revenueChange / $yesterday['total_revenue']) * 100, 2) 
                    : 0,
                'profit_change_percent' => $yesterday['profit'] > 0 
                    ? round(($profitChange / $yesterday['profit']) * 100, 2) 
                    : 0,
            ],
        ]);
    }

    /**
     * Analyse des charges par catégorie
     */
    public function chargesBreakdown(Request $request)
    {
        $period = $request->get('period', 'month');
        [$startDate, $endDate] = $this->getPeriodDates($period, $request);

        $fixedCharges = $this->financialService->calculateFixedCharges($startDate, $endDate);
        $variableCharges = $this->financialService->calculateVariableCharges($startDate, $endDate);

        return response()->json([
            'fixed' => $fixedCharges,
            'variable' => $variableCharges,
            'total' => round($fixedCharges['total'] + $variableCharges['total_variable_charges'], 3),
        ]);
    }

    /**
     * Calcule les dates de début et fin selon la période
     */
    private function getPeriodDates($period, $request)
    {
        return match($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            '7days' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            '30days' => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            '90days' => [now()->subDays(89)->startOfDay(), now()->endOfDay()],
            'custom' => [
                Carbon::parse($request->get('start_date', now()->startOfMonth())),
                Carbon::parse($request->get('end_date', now()->endOfMonth()))
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    /**
     * Calcule la comparaison entre deux périodes
     */
    private function calculateComparison($period1, $period2)
    {
        $revenueChange = $period1['financial']['total_revenue'] - $period2['financial']['total_revenue'];
        $profitChange = $period1['financial']['profit'] - $period2['financial']['profit'];
        $chargesChange = $period1['financial']['total_charges'] - $period2['financial']['total_charges'];

        return [
            'revenue' => [
                'change' => round($revenueChange, 3),
                'percent' => $period2['financial']['total_revenue'] > 0 
                    ? round(($revenueChange / $period2['financial']['total_revenue']) * 100, 2) 
                    : 0,
            ],
            'profit' => [
                'change' => round($profitChange, 3),
                'percent' => $period2['financial']['profit'] > 0 
                    ? round(($profitChange / $period2['financial']['profit']) * 100, 2) 
                    : 0,
            ],
            'charges' => [
                'change' => round($chargesChange, 3),
                'percent' => $period2['financial']['total_charges'] > 0 
                    ? round(($chargesChange / $period2['financial']['total_charges']) * 100, 2) 
                    : 0,
            ],
            'packages' => [
                'change' => $period1['packages']['created'] - $period2['packages']['created'],
                'percent' => $period2['packages']['created'] > 0 
                    ? round((($period1['packages']['created'] - $period2['packages']['created']) / $period2['packages']['created']) * 100, 2) 
                    : 0,
            ],
        ];
    }
}
