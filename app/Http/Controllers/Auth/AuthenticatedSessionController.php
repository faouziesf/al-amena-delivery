<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use App\Services\ActionLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    protected $actionLogService;

    public function __construct(ActionLogService $actionLogService)
    {
        $this->actionLogService = $actionLogService;
    }

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        
        // Log de connexion
        $this->actionLogService->logLogin($user);
        
        // Mise à jour du last_login
        $user->update(['last_login' => now()]);

        // Redirection selon le rôle
        return $this->redirectBasedOnRole($user);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        if ($user) {
            $this->actionLogService->logLogout($user);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function redirectBasedOnRole($user): RedirectResponse
    {
        switch ($user->role) {
            case 'CLIENT':
                return redirect()->route('client.dashboard');
            case 'DELIVERER':
                return redirect()->route('deliverer.dashboard');
            case 'COMMERCIAL':
                return redirect()->route('commercial.dashboard');
            case 'DEPOT_MANAGER':
                return redirect()->route('depot-manager.dashboard');
            case 'SUPERVISOR':
                return redirect()->route('supervisor.dashboard');
            default:
                // Logout user with invalid/deprecated role
                Auth::guard('web')->logout();
                return redirect()->route('login')->with('error', 'Type de compte non reconnu ou désactivé. Veuillez contacter l\'administrateur.');
        }
    }
}