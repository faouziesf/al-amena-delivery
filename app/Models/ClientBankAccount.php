<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientBankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'bank_name',
        'account_holder_name',
        'iban',
        'is_default',
        'last_used_at'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    // Relations
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class, 'bank_account_id');
    }

    // Scopes
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('last_used_at', 'desc');
    }

    // Méthodes
    public function markAsUsed()
    {
        $this->update(['last_used_at' => now()]);
        return $this;
    }

    public function setAsDefault()
    {
        // Retirer le statut par défaut des autres comptes
        static::where('client_id', $this->client_id)
              ->where('id', '!=', $this->id)
              ->update(['is_default' => false]);

        // Définir celui-ci comme défaut
        $this->update(['is_default' => true]);

        return $this;
    }

    // Validation IBAN tunisien
    public function isValidTunisianIban()
    {
        // Format IBAN tunisien : TN59 XXXX XXXX XXXX XXXX XXXX XX
        // 24 caractères au total
        $iban = preg_replace('/\s+/', '', $this->iban);

        // Vérifier le format de base
        if (!preg_match('/^TN\d{22}$/', $iban)) {
            return false;
        }

        // Validation checksum IBAN (algorithme mod-97)
        return $this->validateIbanChecksum($iban);
    }

    private function validateIbanChecksum($iban)
    {
        // Déplacer les 4 premiers caractères à la fin
        $rearranged = substr($iban, 4) . substr($iban, 0, 4);

        // Remplacer les lettres par des chiffres (A=10, B=11, ..., Z=35)
        $numeric = '';
        for ($i = 0; $i < strlen($rearranged); $i++) {
            $char = $rearranged[$i];
            if (ctype_alpha($char)) {
                $numeric .= (ord(strtoupper($char)) - ord('A') + 10);
            } else {
                $numeric .= $char;
            }
        }

        // Calculer le modulo 97
        return bcmod($numeric, '97') == 1;
    }

    // Formatage pour affichage
    public function getFormattedIbanAttribute()
    {
        $iban = preg_replace('/\s+/', '', $this->iban);
        return chunk_split($iban, 4, ' ');
    }

    public function getMaskedIbanAttribute()
    {
        $iban = preg_replace('/\s+/', '', $this->iban);
        if (strlen($iban) < 8) return $iban;

        return substr($iban, 0, 4) . ' **** **** **** ' . substr($iban, -4);
    }

    // Validation des données bancaires
    public static function validateBankData($data)
    {
        $errors = [];

        // Nom de la banque
        if (empty($data['bank_name'])) {
            $errors['bank_name'] = 'Le nom de la banque est obligatoire.';
        }

        // Nom du titulaire
        if (empty($data['account_holder_name'])) {
            $errors['account_holder_name'] = 'Le nom du titulaire est obligatoire.';
        }

        // IBAN
        if (empty($data['iban'])) {
            $errors['iban'] = 'L\'IBAN est obligatoire.';
        } else {
            $iban = preg_replace('/\s+/', '', $data['iban']);
            if (!preg_match('/^TN\d{22}$/', $iban)) {
                $errors['iban'] = 'Format IBAN invalide. Format attendu: TN59 XXXX XXXX XXXX XXXX XXXX XX';
            }
        }

        return $errors;
    }

    // Utilitaires
    public function getDisplayName()
    {
        return $this->bank_name . ' - ' . $this->masked_iban;
    }

    public function canBeDeleted()
    {
        // Ne peut pas supprimer si c'est le seul compte ou s'il y a des retraits en cours
        $otherAccounts = static::where('client_id', $this->client_id)
                              ->where('id', '!=', $this->id)
                              ->count();

        $pendingWithdrawals = $this->withdrawalRequests()
                                   ->whereIn('status', ['PENDING', 'PROCESSING'])
                                   ->count();

        return $otherAccounts > 0 && $pendingWithdrawals === 0;
    }
}