<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'endpoint',
        'method',
        'ip_address',
        'response_status',
        'response_time',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Logger une requête API
     */
    public static function logRequest($user, $request, $response, $startTime)
    {
        return self::create([
            'user_id' => $user ? $user->id : null,
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'response_status' => $response->status(),
            'response_time' => microtime(true) - $startTime,
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Obtenir les statistiques d'utilisation pour un utilisateur
     */
    public static function getStatsForUser($userId)
    {
        $today = self::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->count();
        
        $thisMonth = self::where('user_id', $userId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        
        $recentActivity = self::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'method' => $log->method,
                    'endpoint' => '/' . $log->endpoint,
                    'time' => $log->created_at->diffForHumans(),
                    'status' => $log->response_status,
                ];
            });
        
        return [
            'today' => $today,
            'month' => $thisMonth,
            'recent_activity' => $recentActivity,
        ];
    }
}
