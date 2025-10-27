<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'token',
        'token_hash',
        'last_used_at',
        'expires_at',
    ];

    protected $hidden = [
        'token_hash',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Générer un nouveau token unique pour un utilisateur
     */
    public static function generate($userId, $name = 'API Token')
    {
        // Générer un token unique
        $token = self::generateUniqueToken();
        
        // Hasher le token
        $hash = hash('sha256', $token);
        
        // Supprimer l'ancien token s'il existe (un seul token par client)
        self::where('user_id', $userId)->delete();
        
        // Créer le nouveau token
        $apiToken = self::create([
            'user_id' => $userId,
            'name' => $name,
            'token' => $token,
            'token_hash' => $hash,
        ]);
        
        return $apiToken;
    }

    /**
     * Générer un token unique avec préfixe
     */
    private static function generateUniqueToken()
    {
        $environment = app()->environment('production') ? 'live' : 'test';
        $randomBytes = bin2hex(random_bytes(32));
        
        return 'alamena_' . $environment . '_' . $randomBytes;
    }

    /**
     * Vérifier et récupérer un token
     */
    public static function verify($token)
    {
        if (!$token) {
            return null;
        }
        
        $hash = hash('sha256', $token);
        
        return self::where('token_hash', $hash)
            ->with('user')
            ->first();
    }

    /**
     * Mettre à jour la dernière utilisation
     */
    public function updateLastUsed()
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Vérifier si le token est expiré
     */
    public function isExpired()
    {
        if (!$this->expires_at) {
            return false;
        }
        
        return $this->expires_at->isPast();
    }

    /**
     * Obtenir le token masqué pour l'affichage
     */
    public function getMaskedTokenAttribute()
    {
        if (strlen($this->token) < 20) {
            return str_repeat('•', 40);
        }
        
        $prefix = substr($this->token, 0, 15);
        return $prefix . str_repeat('•', 40);
    }
}
