<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\FinancialTransaction;
use App\Models\TopupRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DelivererProfileController extends Controller
{
    /**
     * Afficher profil
     */
    public function show()
    {
        $user = Auth::user();
        $user->load('wallet');

        return view('deliverer.profile.show', compact('user'));
    }

    /**
     * Mettre à jour profil
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone,' . Auth::id(),
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
            'address' => 'nullable|string|max:500',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed'
        ]);

        try {
            $user = Auth::user();

            // Vérifier mot de passe actuel si nouveau mot de passe fourni
            if ($validated['new_password']) {
                if (!Hash::check($validated['current_password'], $user->password)) {
                    return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect.']);
                }
                $validated['password'] = Hash::make($validated['new_password']);
            }

            $user->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'], 
                'email' => $validated['email'],
                'address' => $validated['address'] ?? $user->address,
                'password' => $validated['password'] ?? $user->password
            ]);

            return back()->with('success', 'Profil mis à jour avec succès.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la mise à jour.']);
        }
    }

    /**
     * Statistiques détaillées
     */
    public function statistics()
    {
        $delivererId = Auth::id();

        // Stats packages
        $packageStats = [
            'total_delivered' => Package::where('assigned_deliverer_id', $delivererId)
                                       ->where('status', 'DELIVERED')
                                       ->count(),
            'total_returned' => Package::where('assigned_deliverer_id', $delivererId)
                                      ->where('status', 'RETURNED')
                                      ->count(),
            'success_rate' => 0,
            'avg_deliveries_per_day' => 0,
            'total_cod_collected' => FinancialTransaction::where('user_id', $delivererId)
                                                       ->where('type', 'COD_COLLECTION')
                                                       ->where('status', 'COMPLETED')
                                                       ->sum('amount')
        ];

        $totalPackages = $packageStats['total_delivered'] + $packageStats['total_returned'];
        if ($totalPackages > 0) {
            $packageStats['success_rate'] = round(($packageStats['total_delivered'] / $totalPackages) * 100, 2);
        }

        // Stats par mois (3 derniers mois)
        $monthlyStats = [];
        for ($i = 2; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyStats[] = [
                'month' => $month->format('M Y'),
                'delivered' => Package::where('assigned_deliverer_id', $delivererId)
                                     ->where('status', 'DELIVERED')
                                     ->whereYear('updated_at', $month->year)
                                     ->whereMonth('updated_at', $month->month)
                                     ->count(),
                'returned' => Package::where('assigned_deliverer_id', $delivererId)
                                    ->where('status', 'RETURNED')
                                    ->whereYear('updated_at', $month->year)
                                    ->whereMonth('updated_at', $month->month)
                                    ->count(),
                'cod_collected' => FinancialTransaction::where('user_id', $delivererId)
                                                     ->where('type', 'COD_COLLECTION')
                                                     ->whereYear('created_at', $month->year)
                                                     ->whereMonth('created_at', $month->month)
                                                     ->sum('amount')
            ];
        }

        return view('deliverer.profile.statistics', compact('packageStats', 'monthlyStats'));
    }

    /**
     * Mettre à jour l'avatar
     */
    public function updateAvatar(Request $request)
    {
        $validated = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            $user = Auth::user();

            // Supprimer l'ancien avatar s'il existe
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            // Stocker le nouvel avatar
            $path = $request->file('avatar')->store('avatars/deliverers', 'public');

            $user->update(['avatar_path' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar mis à jour avec succès.',
                'avatar_url' => Storage::url($path)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'avatar.'
            ], 500);
        }
    }

    /**
     * Mettre à jour les préférences
     */
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'notification_package_assigned' => 'boolean',
            'notification_new_pickups' => 'boolean',
            'notification_payment_ready' => 'boolean',
            'notification_wallet_threshold' => 'boolean',
            'wallet_threshold_amount' => 'nullable|numeric|min:0|max:1000',
            'work_start_time' => 'nullable|date_format:H:i',
            'work_end_time' => 'nullable|date_format:H:i',
            'preferred_zones' => 'nullable|array',
            'preferred_zones.*' => 'exists:delegations,id',
            'language' => 'nullable|in:fr,ar,en',
            'dark_mode' => 'boolean',
            'sound_notifications' => 'boolean'
        ]);

        try {
            $user = Auth::user();

            $preferences = array_merge($user->preferences ?? [], $validated);

            $user->update(['preferences' => $preferences]);

            return response()->json([
                'success' => true,
                'message' => 'Préférences mises à jour avec succès.',
                'preferences' => $preferences
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des préférences.'
            ], 500);
        }
    }

    /**
     * Upload de documents
     */
    public function uploadDocument(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|in:license,insurance,id_card,vehicle_permit',
            'document' => 'required|file|mimes:pdf,jpeg,png,jpg|max:5120'
        ]);

        try {
            $user = Auth::user();

            $path = $request->file('document')->store(
                'documents/deliverers/' . $user->id,
                'public'
            );

            $documents = $user->documents ?? [];
            $documents[$validated['document_type']] = [
                'path' => $path,
                'uploaded_at' => now()->toISOString(),
                'original_name' => $request->file('document')->getClientOriginalName()
            ];

            $user->update(['documents' => $documents]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploadé avec succès.',
                'document_url' => Storage::url($path)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload du document.'
            ], 500);
        }
    }

    /**
     * Exporter les données personnelles
     */
    public function exportData()
    {
        try {
            $user = Auth::user();
            $user->load('wallet');

            $packages = Package::where('assigned_deliverer_id', $user->id)
                              ->with(['sender', 'recipient'])
                              ->get();

            $transactions = FinancialTransaction::where('user_id', $user->id)
                                              ->orderBy('created_at', 'desc')
                                              ->get();

            $topups = TopupRequest::where('processed_by_id', $user->id)
                                 ->with('client')
                                 ->get();

            $exportData = [
                'user_info' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'created_at' => $user->created_at->toISOString(),
                    'role' => $user->role
                ],
                'wallet' => [
                    'current_balance' => $user->wallet->balance ?? 0,
                    'total_earned' => $transactions->where('type', 'COD_COLLECTION')->sum('amount'),
                    'total_client_funds' => $transactions->where('type', 'CLIENT_FUND_ADD')->sum('amount')
                ],
                'packages_stats' => [
                    'total_packages' => $packages->count(),
                    'delivered' => $packages->where('status', 'DELIVERED')->count(),
                    'returned' => $packages->where('status', 'RETURNED')->count(),
                    'in_progress' => $packages->whereIn('status', ['ACCEPTED', 'PICKED_UP'])->count()
                ],
                'topups_stats' => [
                    'total_topups' => $topups->count(),
                    'total_amount_processed' => $topups->where('status', 'VALIDATED')->sum('amount'),
                    'unique_clients_helped' => $topups->unique('client_id')->count()
                ],
                'exported_at' => now()->toISOString()
            ];

            $filename = 'export_deliverer_' . $user->id . '_' . now()->format('Y_m_d_H_i') . '.json';

            return response()->json($exportData)
                           ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export des données.'
            ], 500);
        }
    }

    /**
     * Afficher le formulaire de changement de mot de passe
     */
    public function showPasswordForm()
    {
        return view('deliverer.profile.password');
    }

    /**
     * Mettre à jour le mot de passe
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed'
        ]);

        try {
            $user = Auth::user();

            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
            }

            $user->update([
                'password' => Hash::make($validated['new_password'])
            ]);

            return back()->with('success', 'Mot de passe modifié avec succès.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la modification du mot de passe.']);
        }
    }

    /**
     * Statistiques par période
     */
    public function statisticsByPeriod($period)
    {
        $delivererId = Auth::id();

        $dateRange = $this->getDateRangeForPeriod($period);

        if (!$dateRange) {
            return response()->json(['error' => 'Période invalide'], 400);
        }

        $stats = [
            'packages' => [
                'delivered' => Package::where('assigned_deliverer_id', $delivererId)
                                     ->where('status', 'DELIVERED')
                                     ->whereBetween('updated_at', $dateRange)
                                     ->count(),
                'returned' => Package::where('assigned_deliverer_id', $delivererId)
                                    ->where('status', 'RETURNED')
                                    ->whereBetween('updated_at', $dateRange)
                                    ->count(),
                'cod_collected' => FinancialTransaction::where('user_id', $delivererId)
                                                     ->where('type', 'COD_COLLECTION')
                                                     ->whereBetween('created_at', $dateRange)
                                                     ->sum('amount')
            ],
            'topups' => [
                'count' => TopupRequest::where('processed_by_id', $delivererId)
                                      ->where('method', 'CASH')
                                      ->whereBetween('processed_at', $dateRange)
                                      ->count(),
                'amount' => TopupRequest::where('processed_by_id', $delivererId)
                                       ->where('method', 'CASH')
                                       ->where('status', 'VALIDATED')
                                       ->whereBetween('processed_at', $dateRange)
                                       ->sum('amount'),
                'unique_clients' => TopupRequest::where('processed_by_id', $delivererId)
                                               ->where('method', 'CASH')
                                               ->whereBetween('processed_at', $dateRange)
                                               ->distinct('client_id')
                                               ->count()
            ],
            'period' => $period,
            'date_range' => [
                'start' => $dateRange[0]->toDateString(),
                'end' => $dateRange[1]->toDateString()
            ]
        ];

        $totalPackages = $stats['packages']['delivered'] + $stats['packages']['returned'];
        $stats['packages']['success_rate'] = $totalPackages > 0
            ? round(($stats['packages']['delivered'] / $totalPackages) * 100, 2)
            : 0;

        return response()->json($stats);
    }

    /**
     * Exporter les statistiques
     */
    public function exportStatistics(Request $request, $format)
    {
        $validated = $request->validate([
            'period' => 'required|in:today,week,month,quarter,year,custom',
            'start_date' => 'nullable|date|required_if:period,custom',
            'end_date' => 'nullable|date|required_if:period,custom|after_or_equal:start_date'
        ]);

        try {
            $delivererId = Auth::id();
            $period = $validated['period'];

            if ($period === 'custom') {
                $dateRange = [Carbon::parse($validated['start_date']), Carbon::parse($validated['end_date'])];
            } else {
                $dateRange = $this->getDateRangeForPeriod($period);
            }

            $packages = Package::where('assigned_deliverer_id', $delivererId)
                              ->whereBetween('updated_at', $dateRange)
                              ->with(['sender', 'recipient'])
                              ->get();

            $transactions = FinancialTransaction::where('user_id', $delivererId)
                                              ->whereBetween('created_at', $dateRange)
                                              ->get();

            $topups = TopupRequest::where('processed_by_id', $delivererId)
                                 ->whereBetween('processed_at', $dateRange)
                                 ->with('client')
                                 ->get();

            $exportData = [
                'deliverer' => Auth::user()->name,
                'period' => $period,
                'date_range' => [
                    'start' => $dateRange[0]->toDateString(),
                    'end' => $dateRange[1]->toDateString()
                ],
                'summary' => [
                    'packages_delivered' => $packages->where('status', 'DELIVERED')->count(),
                    'packages_returned' => $packages->where('status', 'RETURNED')->count(),
                    'total_cod_collected' => $transactions->where('type', 'COD_COLLECTION')->sum('amount'),
                    'total_topups_processed' => $topups->where('status', 'VALIDATED')->sum('amount'),
                    'unique_clients_helped' => $topups->unique('client_id')->count()
                ],
                'packages' => $packages->map(function($package) {
                    return [
                        'tracking_number' => $package->tracking_number,
                        'status' => $package->status,
                        'cod_amount' => $package->cod_amount,
                        'sender' => $package->sender->name ?? 'N/A',
                        'recipient' => $package->recipient_name,
                        'updated_at' => $package->updated_at->toDateString()
                    ];
                }),
                'topups' => $topups->map(function($topup) {
                    return [
                        'request_code' => $topup->request_code,
                        'client_name' => $topup->client->name,
                        'amount' => $topup->amount,
                        'status' => $topup->status,
                        'processed_at' => $topup->processed_at?->toDateString()
                    ];
                }),
                'exported_at' => now()->toISOString()
            ];

            $filename = 'stats_deliverer_' . $delivererId . '_' . $period . '_' . now()->format('Y_m_d') . '.' . $format;

            if ($format === 'json') {
                return response()->json($exportData)
                               ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            }

            // Pour CSV
            $csv = $this->convertToCSV($exportData);

            return response($csv)
                        ->header('Content-Type', 'text/csv')
                        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==================== MÉTHODES PRIVÉES ====================

    private function getDateRangeForPeriod($period)
    {
        switch ($period) {
            case 'today':
                return [now()->startOfDay(), now()->endOfDay()];
            case 'week':
                return [now()->startOfWeek(), now()->endOfWeek()];
            case 'month':
                return [now()->startOfMonth(), now()->endOfMonth()];
            case 'quarter':
                return [now()->startOfQuarter(), now()->endOfQuarter()];
            case 'year':
                return [now()->startOfYear(), now()->endOfYear()];
            default:
                return null;
        }
    }

    private function convertToCSV($data)
    {
        $csv = "Deliverer,Period,Start Date,End Date,Packages Delivered,Packages Returned,COD Collected,Topups Processed,Unique Clients\n";

        $summary = $data['summary'];
        $csv .= implode(',', [
            $data['deliverer'],
            $data['period'],
            $data['date_range']['start'],
            $data['date_range']['end'],
            $summary['packages_delivered'],
            $summary['packages_returned'],
            $summary['total_cod_collected'],
            $summary['total_topups_processed'],
            $summary['unique_clients_helped']
        ]) . "\n";

        return $csv;
    }
}