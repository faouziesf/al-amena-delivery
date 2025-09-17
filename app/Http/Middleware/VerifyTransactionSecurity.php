<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\ActionLog;
use App\Models\FinancialTransaction;
use App\Services\ActionLogService;

class VerifyTransactionSecurity
{
    protected $actionLogService;

    public function __construct(ActionLogService $actionLogService)
    {
        $this->actionLogService = $actionLogService;
    }

    /**
     * Middleware de sécurité pour les transactions financières
     * Selon les spécifications du système de livraison
     */
    public function handle(Request $request, Closure $next, ...$securityLevels)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        // 1. Vérification de base de sécurité
        $basicCheck = $this->performBasicSecurityChecks($request, $user);
        if ($basicCheck !== true) {
            return $basicCheck;
        }

        // 2. Vérifications par niveau de sécurité
        foreach ($securityLevels as $level) {
            $levelCheck = $this->performSecurityLevelCheck($request, $user, $level);
            if ($levelCheck !== true) {
                return $levelCheck;
            }
        }

        // 3. Rate limiting spécialisé
        $rateLimitCheck = $this->performRateLimitCheck($request, $user);
        if ($rateLimitCheck !== true) {
            return $rateLimitCheck;
        }

        // 4. Détection d'activité suspecte
        $suspiciousCheck = $this->performSuspiciousActivityCheck($request, $user);
        if ($suspiciousCheck !== true) {
            return $suspiciousCheck;
        }

        // 5. Log de l'accès
        $this->logSecurityAccess($request, $user, $securityLevels);

        return $next($request);
    }

    /**
     * Vérifications de sécurité de base
     */
    private function performBasicSecurityChecks(Request $request, $user)
    {
        // Vérifier si le compte est actif
        if (!$user->isActive()) {
            $this->logSecurityViolation($request, $user, 'INACTIVE_ACCOUNT_ACCESS');
            return response()->json(['error' => 'Compte inactif'], 403);
        }

        // Vérifier l'intégrité de session
        if (!$this->verifySessionIntegrity($request, $user)) {
            $this->logSecurityViolation($request, $user, 'SESSION_INTEGRITY_VIOLATION');
            return response()->json(['error' => 'Session invalide'], 401);
        }

        // Vérifier les en-têtes de sécurité
        if (!$this->verifySecurityHeaders($request)) {
            $this->logSecurityViolation($request, $user, 'MISSING_SECURITY_HEADERS');
            return response()->json(['error' => 'En-têtes de sécurité manquants'], 400);
        }

        // Vérifier l'origine de la requête
        if (!$this->verifyRequestOrigin($request)) {
            $this->logSecurityViolation($request, $user, 'INVALID_ORIGIN');
            return response()->json(['error' => 'Origine de requête invalide'], 403);
        }

        return true;
    }

    /**
     * Vérifications par niveau de sécurité
     */
    private function performSecurityLevelCheck(Request $request, $user, $level)
    {
        switch ($level) {
            case 'wallet-modification':
                return $this->checkWalletModificationSecurity($request, $user);
                
            case 'cod-modification':
                return $this->checkCodModificationSecurity($request, $user);
                
            case 'withdrawal-request':
                return $this->checkWithdrawalSecurity($request, $user);
                
            case 'admin-action':
                return $this->checkAdminActionSecurity($request, $user);
                
            case 'financial-transaction':
                return $this->checkFinancialTransactionSecurity($request, $user);
                
            default:
                return true;
        }
    }

    /**
     * Sécurité pour modifications wallet
     */
    private function checkWalletModificationSecurity(Request $request, $user)
    {
        // Vérifier les permissions de rôle
        if (!in_array($user->role, ['CLIENT', 'COMMERCIAL', 'SUPERVISOR'])) {
            $this->logSecurityViolation($request, $user, 'UNAUTHORIZED_WALLET_ACCESS');
            return response()->json(['error' => 'Permissions insuffisantes'], 403);
        }

        // Vérifier l'état du wallet
        if ($user->isClient()) {
            $user->ensureWallet();
            $wallet = $user->wallet;
            
            // Vérifier l'intégrité du wallet
            if (!$wallet->isValid()) {
                $this->logSecurityViolation($request, $user, 'WALLET_INTEGRITY_VIOLATION');
                return response()->json(['error' => 'Wallet en état invalide'], 422);
            }
        }

        // Limite de fréquence pour modifications wallet
        $key = "wallet_modifications:{$user->id}";
        if (RateLimiter::tooManyAttempts($key, 10)) { // 10 tentatives par minute
            $this->logSecurityViolation($request, $user, 'WALLET_MODIFICATION_RATE_LIMIT');
            return response()->json(['error' => 'Trop de modifications de wallet'], 429);
        }
        
        RateLimiter::hit($key, 60);
        
        return true;
    }

    /**
     * Sécurité pour modifications COD (exclusivité commercial)
     */
    private function checkCodModificationSecurity(Request $request, $user)
    {
        // SEULS les commerciaux et superviseurs peuvent modifier les COD
        if (!in_array($user->role, ['COMMERCIAL', 'SUPERVISOR'])) {
            $this->logSecurityViolation($request, $user, 'UNAUTHORIZED_COD_MODIFICATION');
            return response()->json(['error' => 'Seuls les commerciaux peuvent modifier les COD'], 403);
        }

        // Vérifier si l'utilisateur est vérifié
        if (!$user->isVerified()) {
            $this->logSecurityViolation($request, $user, 'UNVERIFIED_USER_COD_MODIFICATION');
            return response()->json(['error' => 'Utilisateur non vérifié'], 403);
        }

        // Rate limiting strict pour modifications COD
        $key = "cod_modifications:{$user->id}";
        if (RateLimiter::tooManyAttempts($key, 20)) { // 20 modifications par heure
            $this->logSecurityViolation($request, $user, 'COD_MODIFICATION_RATE_LIMIT');
            return response()->json(['error' => 'Limite de modifications COD atteinte'], 429);
        }
        
        RateLimiter::hit($key, 3600); // 1 heure
        
        return true;
    }

    /**
     * Sécurité pour demandes de retrait
     */
    private function checkWithdrawalSecurity(Request $request, $user)
    {
        // Vérifier que c'est un client
        if (!$user->isClient()) {
            $this->logSecurityViolation($request, $user, 'NON_CLIENT_WITHDRAWAL_REQUEST');
            return response()->json(['error' => 'Seuls les clients peuvent demander des retraits'], 403);
        }

        // Vérifier les limites quotidiennes de retrait
        $todayWithdrawals = $user->withdrawalRequests()
            ->whereDate('created_at', today())
            ->count();

        if ($todayWithdrawals >= 5) { // Maximum 5 demandes par jour
            $this->logSecurityViolation($request, $user, 'DAILY_WITHDRAWAL_LIMIT_EXCEEDED');
            return response()->json(['error' => 'Limite quotidienne de retraits atteinte'], 429);
        }

        // Rate limiting pour demandes de retrait
        $key = "withdrawal_requests:{$user->id}";
        if (RateLimiter::tooManyAttempts($key, 3)) { // 3 tentatives par heure
            $this->logSecurityViolation($request, $user, 'WITHDRAWAL_REQUEST_RATE_LIMIT');
            return response()->json(['error' => 'Trop de demandes de retrait'], 429);
        }
        
        RateLimiter::hit($key, 3600);
        
        return true;
    }

    /**
     * Sécurité pour actions administratives
     */
    private function checkAdminActionSecurity(Request $request, $user)
    {
        // Vérifier les permissions d'administration
        if (!in_array($user->role, ['COMMERCIAL', 'SUPERVISOR'])) {
            $this->logSecurityViolation($request, $user, 'UNAUTHORIZED_ADMIN_ACTION');
            return response()->json(['error' => 'Permissions administratives requises'], 403);
        }

        // Vérifier les heures de travail pour actions sensibles
        $currentHour = now()->hour;
        if ($currentHour < 6 || $currentHour > 22) {
            $sensitiveActions = ['account_creation', 'wallet_emptying', 'cod_modification'];
            $route = $request->route()->getName();
            
            if (in_array($route, $sensitiveActions)) {
                $this->logSecurityViolation($request, $user, 'AFTER_HOURS_SENSITIVE_ACTION');
                // Log mais ne pas bloquer (juste alerter)
            }
        }

        return true;
    }

    /**
     * Sécurité pour transactions financières
     */
    private function checkFinancialTransactionSecurity(Request $request, $user)
    {
        // Vérifier les montants suspects
        $amount = $request->input('amount', 0);
        if (abs($amount) > 10000) { // Montant élevé
            $this->logSecurityViolation($request, $user, 'HIGH_AMOUNT_TRANSACTION', ['amount' => $amount]);
            // Log mais permettre (avec surveillance)
        }

        // Vérifier la cohérence des données de transaction
        if ($request->has(['user_id', 'type', 'amount'])) {
            if (!$this->validateTransactionData($request->all())) {
                $this->logSecurityViolation($request, $user, 'INVALID_TRANSACTION_DATA');
                return response()->json(['error' => 'Données de transaction invalides'], 422);
            }
        }

        return true;
    }

    /**
     * Rate limiting spécialisé selon l'action
     */
    private function performRateLimitCheck(Request $request, $user)
    {
        $route = $request->route()->getName();
        $limits = $this->getRateLimitsForRoute($route);
        
        if ($limits) {
            $key = "route_limit:{$route}:{$user->id}";
            
            if (RateLimiter::tooManyAttempts($key, $limits['max_attempts'])) {
                $this->logSecurityViolation($request, $user, 'ROUTE_RATE_LIMIT_EXCEEDED', [
                    'route' => $route,
                    'limit' => $limits['max_attempts']
                ]);
                
                return response()->json([
                    'error' => 'Limite de fréquence atteinte',
                    'retry_after' => RateLimiter::availableIn($key)
                ], 429);
            }
            
            RateLimiter::hit($key, $limits['decay_minutes'] * 60);
        }

        return true;
    }

    /**
     * Détection d'activité suspecte
     */
    private function performSuspiciousActivityCheck(Request $request, $user)
    {
        $suspiciousIndicators = [];

        // 1. Changement d'IP fréquent
        $recentIps = $this->getRecentUserIps($user->id, 24); // Dernières 24h
        if (count($recentIps) > 5) {
            $suspiciousIndicators[] = 'FREQUENT_IP_CHANGES';
        }

        // 2. Activité en dehors des heures normales
        $currentHour = now()->hour;
        if ($currentHour < 6 || $currentHour > 23) {
            $suspiciousIndicators[] = 'AFTER_HOURS_ACTIVITY';
        }

        // 3. Tentatives multiples d'actions sensibles
        $recentFailedAttempts = $this->getRecentFailedAttempts($user->id, 60); // Dernière heure
        if ($recentFailedAttempts > 10) {
            $suspiciousIndicators[] = 'MULTIPLE_FAILED_ATTEMPTS';
        }

        // 4. Géolocalisation suspecte (si disponible)
        if ($this->isGeoLocationSuspicious($request)) {
            $suspiciousIndicators[] = 'SUSPICIOUS_GEOLOCATION';
        }

        // 5. Pattern d'utilisation anormal
        if ($this->isUsagePatternAnormal($user, $request)) {
            $suspiciousIndicators[] = 'ABNORMAL_USAGE_PATTERN';
        }

        // Si activité suspecte détectée
        if (!empty($suspiciousIndicators)) {
            $this->logSuspiciousActivity($request, $user, $suspiciousIndicators);
            
            // Actions selon le niveau de suspicion
            if (count($suspiciousIndicators) >= 3) {
                // Suspicion élevée - bloquer
                return response()->json([
                    'error' => 'Activité suspecte détectée. Compte temporairement restreint.',
                    'contact_support' => true
                ], 403);
            } elseif (count($suspiciousIndicators) >= 2) {
                // Suspicion modérée - authentification supplémentaire
                return response()->json([
                    'error' => 'Vérification supplémentaire requise',
                    'requires_verification' => true
                ], 422);
            }
            // Suspicion faible - juste logging
        }

        return true;
    }

    /**
     * Vérification de l'intégrité de session
     */
    private function verifySessionIntegrity(Request $request, $user)
    {
        $sessionData = [
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ];

        $expectedHash = hash('sha256', json_encode($sessionData));
        $storedHash = session('security_hash');

        if (!$storedHash) {
            session(['security_hash' => $expectedHash]);
            return true;
        }

        return hash_equals($storedHash, $expectedHash);
    }

    /**
     * Vérification des en-têtes de sécurité
     */
    private function verifySecurityHeaders(Request $request)
    {
        // Vérifier CSRF token pour POST/PUT/DELETE
        if (in_array($request->method(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            if (!$request->hasHeader('X-CSRF-TOKEN') && !$request->has('_token')) {
                return false;
            }
        }

        // Vérifier Accept header
        if (!$request->acceptsJson()) {
            return false;
        }

        return true;
    }

    /**
     * Vérification de l'origine de la requête
     */
    private function verifyRequestOrigin(Request $request)
    {
        $allowedOrigins = [
            config('app.url'),
            'https://' . $request->getHost(),
            'http://' . $request->getHost() // Pour développement
        ];

        $origin = $request->header('Origin') ?? $request->header('Referer');
        
        if (!$origin) {
            return true; // Pas d'origine spécifiée (requêtes directes)
        }

        foreach ($allowedOrigins as $allowedOrigin) {
            if (str_starts_with($origin, $allowedOrigin)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtenir les limites de rate pour une route
     */
    private function getRateLimitsForRoute($route)
    {
        $routeLimits = [
            'api.wallet.balance' => ['max_attempts' => 120, 'decay_minutes' => 1],
            'api.withdrawals.create' => ['max_attempts' => 5, 'decay_minutes' => 60],
            'api.admin.wallets.adjust' => ['max_attempts' => 10, 'decay_minutes' => 60],
            'api.packages.financial.simulate' => ['max_attempts' => 30, 'decay_minutes' => 1],
        ];

        return $routeLimits[$route] ?? null;
    }

    /**
     * Validation des données de transaction
     */
    private function validateTransactionData($data)
    {
        // Vérifications de cohérence
        if (isset($data['amount']) && !is_numeric($data['amount'])) {
            return false;
        }

        if (isset($data['user_id']) && !is_numeric($data['user_id'])) {
            return false;
        }

        // Vérifier que les montants ne sont pas aberrants
        if (isset($data['amount']) && abs($data['amount']) > 1000000) {
            return false;
        }

        return true;
    }

    /**
     * Obtenir les IPs récentes d'un utilisateur
     */
    private function getRecentUserIps($userId, $hours)
    {
        return ActionLog::where('user_id', $userId)
            ->where('created_at', '>=', now()->subHours($hours))
            ->distinct('ip_address')
            ->pluck('ip_address')
            ->toArray();
    }

    /**
     * Obtenir les tentatives échouées récentes
     */
    private function getRecentFailedAttempts($userId, $minutes)
    {
        return ActionLog::where('user_id', $userId)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->whereIn('action_type', ['FAILED_LOGIN', 'FAILED_TRANSACTION', 'SECURITY_VIOLATION'])
            ->count();
    }

    /**
     * Vérification géolocalisation suspecte
     */
    private function isGeoLocationSuspicious(Request $request)
    {
        // Implémentation basique - peut être étendue avec des services de géolocalisation
        $ip = $request->ip();
        
        // Vérifier si l'IP est dans une liste de pays suspects (optionnel)
        // ou si la distance géographique par rapport aux connexions précédentes est trop importante
        
        return false; // À implémenter selon les besoins
    }

    /**
     * Détecter un pattern d'utilisation anormal
     */
    private function isUsagePatternAnormal($user, Request $request)
    {
        // Vérifier la fréquence d'actions par rapport aux habitudes de l'utilisateur
        $recentActions = ActionLog::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        $avgActionsPerHour = ActionLog::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count() / (30 * 24);

        // Si l'activité actuelle est 10x supérieure à la moyenne
        return $recentActions > ($avgActionsPerHour * 10);
    }

    /**
     * Log d'accès sécurisé
     */
    private function logSecurityAccess(Request $request, $user, $securityLevels)
    {
        $this->actionLogService->log(
            'SECURITY_ACCESS',
            'SecurityMiddleware',
            null,
            null,
            'GRANTED',
            [
                'route' => $request->route()->getName(),
                'method' => $request->method(),
                'security_levels' => $securityLevels,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]
        );
    }

    /**
     * Log de violation de sécurité
     */
    private function logSecurityViolation(Request $request, $user, $violationType, $additional = [])
    {
        $this->actionLogService->log(
            'SECURITY_VIOLATION',
            'SecurityMiddleware',
            null,
            null,
            $violationType,
            array_merge([
                'route' => $request->route()?->getName(),
                'method' => $request->method(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'violation_severity' => $this->getViolationSeverity($violationType)
            ], $additional)
        );
    }

    /**
     * Log d'activité suspecte
     */
    private function logSuspiciousActivity(Request $request, $user, $indicators)
    {
        $this->actionLogService->log(
            'SUSPICIOUS_ACTIVITY',
            'SecurityMiddleware',
            null,
            null,
            'DETECTED',
            [
                'indicators' => $indicators,
                'route' => $request->route()?->getName(),
                'method' => $request->method(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'risk_level' => count($indicators) >= 3 ? 'HIGH' : (count($indicators) >= 2 ? 'MEDIUM' : 'LOW')
            ]
        );
    }

    /**
     * Obtenir la sévérité d'une violation
     */
    private function getViolationSeverity($violationType)
    {
        $highSeverity = [
            'UNAUTHORIZED_COD_MODIFICATION',
            'WALLET_INTEGRITY_VIOLATION',
            'SESSION_INTEGRITY_VIOLATION'
        ];

        $mediumSeverity = [
            'UNAUTHORIZED_ADMIN_ACTION',
            'AFTER_HOURS_SENSITIVE_ACTION',
            'HIGH_AMOUNT_TRANSACTION'
        ];

        if (in_array($violationType, $highSeverity)) {
            return 'HIGH';
        } elseif (in_array($violationType, $mediumSeverity)) {
            return 'MEDIUM';
        } else {
            return 'LOW';
        }
    }
}