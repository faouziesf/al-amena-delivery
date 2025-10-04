<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manifest extends Model
{
    protected $fillable = [
        'manifest_number',
        'sender_id',
        'package_ids',
        'pickup_address_id',
        'pickup_address_name',
        'pickup_phone',
        'total_packages',
        'total_cod_amount',
        'total_weight',
        'pickup_request_id',
        'status',
        'generated_at'
    ];

    protected $casts = [
        'package_ids' => 'array',
        'generated_at' => 'datetime',
        'total_cod_amount' => 'decimal:3',
        'total_weight' => 'decimal:3'
    ];

    // Relations
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function pickupAddress(): BelongsTo
    {
        return $this->belongsTo(ClientPickupAddress::class, 'pickup_address_id');
    }

    public function pickupRequest(): BelongsTo
    {
        return $this->belongsTo(PickupRequest::class, 'pickup_request_id');
    }

    public function packages()
    {
        return Package::whereIn('id', $this->package_ids ?? []);
    }

    // Accessors
    public function getPackagesAttribute()
    {
        return $this->packages()->get();
    }

    const STATUS_EN_PREPARATION = 'CREATED';
    const STATUS_RAMASSAGE_PARTIEL = 'REQUESTED';
    const STATUS_RAMASSE = 'COLLECTED';

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            self::STATUS_EN_PREPARATION => ['text' => 'En préparation', 'color' => 'blue'],
            self::STATUS_RAMASSAGE_PARTIEL => ['text' => 'Ramassage demandé', 'color' => 'orange'],
            self::STATUS_RAMASSE => ['text' => 'Collecté', 'color' => 'green'],
            'CANCELLED' => ['text' => 'Annulé', 'color' => 'red'],
            default => ['text' => $this->status, 'color' => 'gray']
        };
    }

    // Méthodes de logique métier
    public function canBeDeleted(): bool
    {
        if ($this->status !== self::STATUS_EN_PREPARATION) {
            return false;
        }

        // Vérifier qu'aucun colis n'est passé au statut picked_up
        if (empty($this->package_ids)) {
            return true; // Manifeste vide peut être supprimé
        }

        // Vérifier s'il y a des colis PICKED_UP
        $pickedUpCount = Package::whereIn('id', $this->package_ids)
            ->where('status', 'PICKED_UP')
            ->count();

        return $pickedUpCount === 0;
    }

    public function canRemovePackage($packageId): bool
    {
        $package = Package::find($packageId);
        return $package && $package->status === 'AVAILABLE';
    }

    public function updateStatus()
    {
        $packages = $this->packages()->get();

        if ($packages->isEmpty()) {
            return;
        }

        $pickedUpCount = $packages->where('status', 'PICKED_UP')->count();
        $totalCount = $packages->count();

        if ($pickedUpCount === 0) {
            $this->status = self::STATUS_EN_PREPARATION;
        } elseif ($pickedUpCount === $totalCount) {
            $this->status = self::STATUS_RAMASSE;
        } else {
            $this->status = self::STATUS_RAMASSAGE_PARTIEL;
        }

        $this->save();
    }

    public function removePackage($packageId): bool
    {
        if (!$this->canRemovePackage($packageId)) {
            return false;
        }

        $packageIds = $this->package_ids;
        $key = array_search($packageId, $packageIds);

        if ($key !== false) {
            unset($packageIds[$key]);
            $this->package_ids = array_values($packageIds);
            $this->total_packages = count($packageIds);

            // Recalculer les totaux
            $packages = Package::whereIn('id', $packageIds)->get();
            $this->total_cod_amount = $packages->sum('cod_amount');
            $this->total_weight = $packages->sum('package_weight');

            $this->save();

            // Mettre à jour le statut
            $this->updateStatus();

            return true;
        }

        return false;
    }

    // Méthodes utiles
    public function generateManifestNumber(): string
    {
        return 'MAN-' . date('Y') . '-' . str_pad($this->getNextSequence(), 5, '0', STR_PAD_LEFT);
    }

    private function getNextSequence(): int
    {
        $currentYear = date('Y');
        $lastManifest = self::where('manifest_number', 'like', "MAN-{$currentYear}-%")
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastManifest) {
            return 1;
        }

        $lastNumber = (int) substr($lastManifest->manifest_number, -5);
        return $lastNumber + 1;
    }

    // Scopes
    public function scopeForClient($query, $clientId)
    {
        return $query->where('sender_id', $clientId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($manifest) {
            if (empty($manifest->manifest_number)) {
                $manifest->manifest_number = $manifest->generateManifestNumber();
            }
            if (empty($manifest->status)) {
                $manifest->status = self::STATUS_EN_PREPARATION;
            }
        });
    }
}
