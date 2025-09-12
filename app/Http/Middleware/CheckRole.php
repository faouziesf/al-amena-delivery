<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();
        
        // Vérifier que le compte est actif
        if ($user->account_status !== 'ACTIVE') {
            Auth::logout();
            return redirect('login')->withErrors([
                'account' => 'Votre compte n\'est pas actif. Contactez un administrateur.'
            ]);
        }

        // Vérifier que l'utilisateur a le bon rôle
        if (!in_array($user->role, $roles)) {
            abort(403, 'Accès non autorisé pour votre rôle.');
        }

        return $next($request);
    }
}