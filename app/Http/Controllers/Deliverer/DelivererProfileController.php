<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\FinancialTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
}