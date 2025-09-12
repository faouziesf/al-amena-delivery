<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $wallet = $user->wallet;
        
        // Statistiques de base pour le dashboard client
        $stats = [
            'wallet_balance' => $wallet->balance ?? 0,
            'pending_amount' => $wallet->pending_amount ?? 0,
            'packages_in_progress' => 0, // À implémenter avec le système de colis
            'packages_delivered' => 0, // À implémenter avec le système de colis
        ];
        
        return view('client.dashboard', compact('user', 'wallet', 'stats'));
    }
}