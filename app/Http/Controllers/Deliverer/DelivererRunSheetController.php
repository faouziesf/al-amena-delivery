<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Delegation;
use App\Models\RunSheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DelivererRunSheetController extends Controller
{
    /**
     * Liste des feuilles de route
     */
    public function index(Request $request)
    {
        $query = RunSheet::where('deliverer_id', Auth::id())
                         ->with(['delegation'])
                         ->orderBy('created_at', 'desc');

        if ($request->filled('delegation')) {
            $query->where('delegation_id', $request->delegation);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $runSheets = $query->paginate(15)->appends($request->query());

        $delegations = Delegation::where('active', true)
                                ->orderBy('name')
                                ->get(['id', 'name']);

        // Stats pour aujourd'hui
        $todayStats = [
            'total_sheets' => RunSheet::where('deliverer_id', Auth::id())
                                    ->whereDate('date', today())
                                    ->count(),
            'completed_sheets' => RunSheet::where('deliverer_id', Auth::id())
                                        ->whereDate('date', today())
                                        ->where('status', 'COMPLETED')
                                        ->count(),
            'pending_packages' => Package::where('assigned_deliverer_id', Auth::id())
                                        ->whereIn('status', ['ACCEPTED', 'PICKED_UP'])
                                        ->count(),
            'total_packages_today' => Package::where('assigned_deliverer_id', Auth::id())
                                            ->whereDate('updated_at', today())
                                            ->whereIn('status', ['DELIVERED', 'RETURNED'])
                                            ->count()
        ];

        return view('deliverer.runsheets.index', compact('runSheets', 'delegations', 'todayStats'));
    }

    /**
     * Générer une nouvelle feuille de route
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'delegation_id' => 'required|exists:delegations,id',
            'package_types' => 'required|array',
            'package_types.*' => 'in:pickups,deliveries,returns',
            'include_cod_summary' => 'boolean',
            'sort_by' => 'in:address,cod_amount,created_at'
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $delegation = Delegation::findOrFail($validated['delegation_id']);
                
                // Collecter les colis selon les types sélectionnés
                $packages = collect();
                
                if (in_array('pickups', $validated['package_types'])) {
                    $pickups = Package::where('assigned_deliverer_id', Auth::id())
                                    ->where('status', 'ACCEPTED')
                                    ->where('delegation_from', $delegation->id)
                                    ->with(['sender', 'delegationFrom', 'delegationTo'])
                                    ->get();
                    $packages = $packages->concat($pickups);
                }
                
                if (in_array('deliveries', $validated['package_types'])) {
                    $deliveries = Package::where('assigned_deliverer_id', Auth::id())
                                        ->where('status', 'PICKED_UP')
                                        ->where('delegation_to', $delegation->id)
                                        ->with(['sender', 'delegationFrom', 'delegationTo'])
                                        ->get();
                    $packages = $packages->concat($deliveries);
                }
                
                if (in_array('returns', $validated['package_types'])) {
                    $returns = Package::where('assigned_deliverer_id', Auth::id())
                                     ->where('status', 'VERIFIED')
                                     ->where('delegation_from', $delegation->id)
                                     ->with(['sender', 'delegationFrom', 'delegationTo'])
                                     ->get();
                    $packages = $packages->concat($returns);
                }

                if ($packages->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Aucun colis trouvé pour cette délégation et ces critères.'
                    ]);
                }

                // Tri des colis
                switch ($validated['sort_by'] ?? 'address') {
                    case 'cod_amount':
                        $packages = $packages->sortByDesc('cod_amount');
                        break;
                    case 'created_at':
                        $packages = $packages->sortBy('created_at');
                        break;
                    default:
                        $packages = $packages->sortBy(function($package) {
                            if ($package->status === 'ACCEPTED') {
                                return $package->sender_data['address'] ?? '';
                            } else {
                                return $package->recipient_data['address'] ?? '';
                            }
                        });
                }

                // Créer la feuille de route
                $runSheet = RunSheet::create([
                    'sheet_code' => $this->generateSheetCode(),
                    'deliverer_id' => Auth::id(),
                    'delegation_id' => $delegation->id,
                    'date' => today(),
                    'status' => 'PENDING',
                    'package_types' => $validated['package_types'],
                    'packages_data' => $packages->toArray(),
                    'packages_count' => $packages->count(),
                    'total_cod_amount' => $packages->sum('cod_amount'),
                    'include_cod_summary' => $validated['include_cod_summary'] ?? false,
                    'sort_criteria' => $validated['sort_by'] ?? 'address'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Feuille de route #{$runSheet->sheet_code} générée avec succès ({$packages->count()} colis).",
                    'run_sheet_id' => $runSheet->id,
                    'redirect' => route('deliverer.runsheets.print', $runSheet)
                ]);
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération de la feuille de route.'
            ], 500);
        }
    }

    /**
     * Imprimer/Télécharger feuille de route
     */
    public function print(RunSheet $runSheet)
    {
        if ($runSheet->deliverer_id !== Auth::id()) {
            abort(403, 'Cette feuille de route ne vous appartient pas.');
        }

        $runSheet->load(['deliverer', 'delegation']);

        // Marquer comme imprimée
        if (!$runSheet->printed_at) {
            $runSheet->update([
                'printed_at' => now(),
                'status' => 'IN_PROGRESS'
            ]);
        }

        $packages = collect($runSheet->packages_data);
        $codSummary = $this->calculateCodSummary($packages);

        $pdf = Pdf::loadView('deliverer.runsheets.print', compact(
            'runSheet',
            'packages', 
            'codSummary'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("feuille_route_{$runSheet->sheet_code}.pdf");
    }

    /**
     * Marquer feuille comme terminée
     */
    public function complete(RunSheet $runSheet, Request $request)
    {
        if ($runSheet->deliverer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette feuille de route ne vous appartient pas.'
            ], 403);
        }

        if ($runSheet->status === 'COMPLETED') {
            return response()->json([
                'success' => false,
                'message' => 'Cette feuille est déjà marquée comme terminée.'
            ], 400);
        }

        $validated = $request->validate([
            'completion_notes' => 'nullable|string|max:1000',
            'packages_delivered' => 'required|integer|min:0',
            'packages_returned' => 'required|integer|min:0',
            'total_cod_collected' => 'required|numeric|min:0'
        ]);

        try {
            $runSheet->update([
                'status' => 'COMPLETED',
                'completed_at' => now(),
                'completion_notes' => $validated['completion_notes'],
                'completion_stats' => [
                    'packages_delivered' => $validated['packages_delivered'],
                    'packages_returned' => $validated['packages_returned'],
                    'total_cod_collected' => $validated['total_cod_collected'],
                    'completion_rate' => $runSheet->packages_count > 0
                        ? round(($validated['packages_delivered'] / $runSheet->packages_count) * 100, 2)
                        : 0
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => "Feuille de route #{$runSheet->sheet_code} marquée comme terminée."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la finalisation.'
            ], 500);
        }
    }

    /**
     * Téléchargement avec token de sécurité
     */
    public function downloadWithToken(RunSheet $runSheet, string $token)
    {
        // Vérifier le token de sécurité
        if (!hash_equals(sha1($runSheet->id . $runSheet->created_at), $token)) {
            abort(404);
        }

        return $this->print($runSheet);
    }

    /**
     * API - Statistiques feuilles de route
     */
    public function apiStats()
    {
        $delivererId = Auth::id();
        
        return response()->json([
            'today' => [
                'total_sheets' => RunSheet::where('deliverer_id', $delivererId)
                                        ->whereDate('date', today())
                                        ->count(),
                'completed_sheets' => RunSheet::where('deliverer_id', $delivererId)
                                            ->whereDate('date', today())
                                            ->where('status', 'COMPLETED')
                                            ->count(),
                'packages_in_sheets' => RunSheet::where('deliverer_id', $delivererId)
                                              ->whereDate('date', today())
                                              ->sum('packages_count')
            ],
            'this_week' => [
                'total_sheets' => RunSheet::where('deliverer_id', $delivererId)
                                        ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
                                        ->count(),
                'avg_packages_per_sheet' => RunSheet::where('deliverer_id', $delivererId)
                                                  ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
                                                  ->avg('packages_count')
            ]
        ]);
    }

    // ==================== MÉTHODES PRIVÉES ====================

    private function generateSheetCode()
    {
        do {
            $code = 'RS_' . Auth::id() . '_' . strtoupper(substr(uniqid(), -6));
        } while (RunSheet::where('sheet_code', $code)->exists());

        return $code;
    }

    private function calculateCodSummary($packages)
    {
        return [
            'total_packages' => $packages->count(),
            'packages_with_cod' => $packages->where('cod_amount', '>', 0)->count(),
            'total_cod_amount' => $packages->sum('cod_amount'),
            'average_cod' => $packages->where('cod_amount', '>', 0)->avg('cod_amount') ?: 0,
            'by_status' => $packages->groupBy('status')->map(function($group, $status) {
                return [
                    'count' => $group->count(),
                    'cod_amount' => $group->sum('cod_amount')
                ];
            })
        ];
    }
}