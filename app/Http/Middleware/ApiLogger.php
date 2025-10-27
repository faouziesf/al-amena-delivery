<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Auth;

class ApiLogger
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        // Logger la requête de manière asynchrone
        dispatch(function () use ($request, $response, $startTime) {
            $user = Auth::user();
            
            if ($user) {
                ApiLog::logRequest($user, $request, $response, $startTime);
            }
        })->afterResponse();
        
        return $response;
    }
}
