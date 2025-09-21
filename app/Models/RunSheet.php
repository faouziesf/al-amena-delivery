<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class RunSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'sheet_code', 'deliverer_id', 'delegation_id', 'date',
        'status', 'package_types', 'sort_criteria', 'include_cod_summary',
        'packages_data', 'packages_count', 'total_cod_amount',
        'printed_at', 'started_at', 'completed_at',
        'preparation_notes', 'completion_notes', 'completion_stats',
        'route_optimization', 'estimated_distance', 'estimated_duration',
        'pdf_path', 'print_count', 'export_formats',
        'metadata', 'ip_address', 'user_agent'
    ];

    protected $casts = [
        'date' => 'date',
        'package_types' => 'array',
        'packages_data' => 'array',
        'completion_stats' => 'array',
        'route_optimization' => 'array',
        'export_formats' => 'array',
        'metadata' => 'array',
        'total_cod_amount' => 'decimal:3',
        'estimated_distance' => 'decimal:2',
        'include_cod_summary' => 'boolean',
        'printed_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ==================== RELATIONS ====================

    /**
     * Livreur assigné à cette feuille de route
     */
    public function deliverer()
    {
        return $this->belongsTo(User::class, 'deliverer_id');
    }

    /**
     * Délégation principale de la feuille de route
     */
    public function delegation()
    {
        return $this->belongsTo(Delegation::class, 'delegation_id');
    }

    /**
     * Obtenir les packages associés à cette feuille de route
     * Note: Les packages sont stockés en JSON, cette méthode les récupère
     */
    public function getPackages()
    {
        if (!$this->packages_data || !is_array($this->packages_data)) {
            return collect();
        }

        // Récupérer les IDs des packages depuis les données JSON
        $packageIds = collect($this->packages_data)->pluck('id')->filter()->toArray();

        if (empty($packageIds)) {
            return collect();
        }

        return Package::whereIn('id', $packageIds)->get();
    }

    // ==================== SCOPES ====================

    /**
     * Scope pour filtrer par livreur
     */
    public function scopeByDeliverer($query, $delivererId)
    {
        return $query->where('deliverer_id', $delivererId);
    }

    /**
     * Scope pour filtrer par délégation
     */
    public function scopeByDelegation($query, $delegationId)
    {
        return $query->where('delegation_id', $delegationId);
    }

    /**
     * Scope pour filtrer par statut
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pour les feuilles en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    /**
     * Scope pour les feuilles en cours
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'IN_PROGRESS');
    }

    /**
     * Scope pour les feuilles terminées
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'COMPLETED');
    }

    /**
     * Scope pour les feuilles d'aujourd'hui
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    /**
     * Scope pour une période de dates
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope pour les feuilles récentes
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }

    /**
     * Scope pour les feuilles imprimées
     */
    public function scopePrinted($query)
    {
        return $query->whereNotNull('printed_at');
    }

    /**
     * Scope pour les feuilles non imprimées
     */
    public function scopeNotPrinted($query)
    {
        return $query->whereNull('printed_at');
    }

    // ==================== ACCESSORS ====================

    /**
     * Obtenir le statut formaté en français
     */
    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'PENDING' => 'En attente',
            'IN_PROGRESS' => 'En cours',
            'COMPLETED' => 'Terminée',
            'CANCELLED' => 'Annulée',
            default => $this->status
        };
    }

    /**
     * Obtenir la couleur du statut pour l'affichage
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'PENDING' => 'text-yellow-600 bg-yellow-100',
            'IN_PROGRESS' => 'text-blue-600 bg-blue-100',
            'COMPLETED' => 'text-green-600 bg-green-100',
            'CANCELLED' => 'text-red-600 bg-red-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }

    /**
     * Obtenir l'icône du statut
     */
    public function getStatusIconAttribute()
    {
        return match($this->status) {
            'PENDING' => 'clock',
            'IN_PROGRESS' => 'play-circle',
            'COMPLETED' => 'check-circle',
            'CANCELLED' => 'x-circle',
            default => 'help-circle'
        };
    }

    /**
     * Obtenir les types de colis formatés
     */
    public function getPackageTypesDisplayAttribute()
    {
        if (!$this->package_types || !is_array($this->package_types)) {
            return 'Aucun';
        }

        $types = [
            'pickups' => 'Collectes',
            'deliveries' => 'Livraisons',
            'returns' => 'Retours'
        ];

        return collect($this->package_types)
            ->map(fn($type) => $types[$type] ?? $type)
            ->join(', ');
    }

    /**
     * Obtenir le critère de tri formaté
     */
    public function getSortCriteriaDisplayAttribute()
    {
        return match($this->sort_criteria) {
            'address' => 'Par adresse',
            'cod_amount' => 'Par montant COD',
            'created_at' => 'Par date de création',
            default => $this->sort_criteria
        };
    }

    /**
     * Obtenir le montant COD formaté
     */
    public function getFormattedCodAmountAttribute()
    {
        return number_format($this->total_cod_amount, 3) . ' DT';
    }

    /**
     * Obtenir la distance formatée
     */
    public function getFormattedDistanceAttribute()
    {
        if (!$this->estimated_distance) {
            return 'Non calculée';
        }
        return number_format($this->estimated_distance, 2) . ' km';
    }

    /**
     * Obtenir la durée formatée
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->estimated_duration) {
            return 'Non calculée';
        }
        
        $hours = floor($this->estimated_duration / 60);
        $minutes = $this->estimated_duration % 60;
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}min";
        }
        return "{$minutes}min";
    }

    /**
     * Obtenir le taux de completion
     */
    public function getCompletionRateAttribute()
    {
        if (!$this->completion_stats || !is_array($this->completion_stats)) {
            return 0;
        }

        $delivered = $this->completion_stats['packages_delivered'] ?? 0;
        $total = $this->packages_count;

        if ($total === 0) {
            return 0;
        }

        return round(($delivered / $total) * 100, 1);
    }

    /**
     * Obtenir la date formatée
     */
    public function getFormattedDateAttribute()
    {
        return $this->date->format('d/m/Y');
    }

    /**
     * Obtenir la date relative
     */
    public function getRelativeDateAttribute()
    {
        return $this->date->diffForHumans();
    }

    /**
     * Accesseur pour les packages (pour compatibilité)
     */
    public function getPackagesAttribute()
    {
        return $this->getPackages();
    }

    // ==================== MUTATORS ====================

    /**
     * S'assurer que package_types est toujours un array
     */
    public function setPackageTypesAttribute($value)
    {
        $this->attributes['package_types'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * S'assurer que packages_data est toujours un array
     */
    public function setPackagesDataAttribute($value)
    {
        $this->attributes['packages_data'] = is_array($value) ? json_encode($value) : $value;
    }

    // ==================== MÉTHODES HELPER ====================

    /**
     * Vérifier si la feuille est en attente
     */
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    /**
     * Vérifier si la feuille est en cours
     */
    public function isInProgress(): bool
    {
        return $this->status === 'IN_PROGRESS';
    }

    /**
     * Vérifier si la feuille est terminée
     */
    public function isCompleted(): bool
    {
        return $this->status === 'COMPLETED';
    }

    /**
     * Vérifier si la feuille est annulée
     */
    public function isCancelled(): bool
    {
        return $this->status === 'CANCELLED';
    }

    /**
     * Vérifier si la feuille a été imprimée
     */
    public function isPrinted(): bool
    {
        return !is_null($this->printed_at);
    }

    /**
     * Vérifier si la feuille peut être modifiée
     */
    public function canBeModified(): bool
    {
        return in_array($this->status, ['PENDING']);
    }

    /**
     * Vérifier si la feuille peut être démarrée
     */
    public function canBeStarted(): bool
    {
        return $this->status === 'PENDING' && $this->isPrinted();
    }

    /**
     * Vérifier si la feuille peut être terminée
     */
    public function canBeCompleted(): bool
    {
        return $this->status === 'IN_PROGRESS';
    }

    /**
     * Vérifier si la feuille peut être annulée
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['PENDING', 'IN_PROGRESS']);
    }

    /**
     * Obtenir le résumé des packages par type
     */
    public function getPackagesSummary(): array
    {
        if (!$this->packages_data || !is_array($this->packages_data)) {
            return [
                'pickups' => 0,
                'deliveries' => 0,
                'returns' => 0,
                'total' => 0
            ];
        }

        $packages = collect($this->packages_data);
        
        return [
            'pickups' => $packages->where('status', 'ACCEPTED')->count(),
            'deliveries' => $packages->whereIn('status', ['PICKED_UP', 'UNAVAILABLE'])->count(),
            'returns' => $packages->where('status', 'VERIFIED')->count(),
            'total' => $packages->count()
        ];
    }

    /**
     * Obtenir le résumé COD par statut
     */
    public function getCodSummary(): array
    {
        if (!$this->packages_data || !is_array($this->packages_data)) {
            return [
                'total_packages' => 0,
                'packages_with_cod' => 0,
                'total_cod_amount' => 0,
                'average_cod' => 0
            ];
        }

        $packages = collect($this->packages_data);
        $packagesWithCod = $packages->where('cod_amount', '>', 0);
        
        return [
            'total_packages' => $packages->count(),
            'packages_with_cod' => $packagesWithCod->count(),
            'total_cod_amount' => $packages->sum('cod_amount'),
            'average_cod' => $packagesWithCod->avg('cod_amount') ?: 0,
            'min_cod' => $packagesWithCod->min('cod_amount') ?: 0,
            'max_cod' => $packagesWithCod->max('cod_amount') ?: 0
        ];
    }

    /**
     * Obtenir les délégations impliquées
     */
    public function getInvolvedDelegations(): array
    {
        if (!$this->packages_data || !is_array($this->packages_data)) {
            return [];
        }

        $packages = collect($this->packages_data);
        $delegations = collect();
        
        // Délégations "from" (pour pickups et returns)
        $delegations = $delegations->concat($packages->pluck('delegation_from')->filter());
        
        // Délégations "to" (pour deliveries)
        $delegations = $delegations->concat($packages->pluck('delegation_to')->filter());
        
        return $delegations->unique()->values()->toArray();
    }

    // ==================== MÉTHODES D'ACTION ====================

    /**
     * Marquer comme imprimée
     */
    public function markAsPrinted(): self
    {
        if (!$this->isPrinted()) {
            $this->update([
                'printed_at' => now(),
                'print_count' => $this->print_count + 1
            ]);
            
            // Si c'est en attente et qu'elle est imprimée, passer en cours
            if ($this->status === 'PENDING') {
                $this->update([
                    'status' => 'IN_PROGRESS',
                    'started_at' => now()
                ]);
            }
        }
        
        return $this;
    }

    /**
     * Démarrer l'exécution de la feuille
     */
    public function start(string $notes = null): self
    {
        if ($this->canBeStarted()) {
            $this->update([
                'status' => 'IN_PROGRESS',
                'started_at' => now(),
                'preparation_notes' => $notes
            ]);
        }
        
        return $this;
    }

    /**
     * Terminer la feuille de route
     */
    public function complete(array $stats, string $notes = null): self
    {
        if ($this->canBeCompleted()) {
            $this->update([
                'status' => 'COMPLETED',
                'completed_at' => now(),
                'completion_stats' => $stats,
                'completion_notes' => $notes
            ]);
        }
        
        return $this;
    }

    /**
     * Annuler la feuille de route
     */
    public function cancel(string $reason = null): self
    {
        if ($this->canBeCancelled()) {
            $this->update([
                'status' => 'CANCELLED',
                'completion_notes' => $reason
            ]);
        }
        
        return $this;
    }

    /**
     * Calculer l'optimisation de route (placeholder)
     */
    public function calculateRouteOptimization(): self
    {
        // Placeholder pour intégration future avec APIs de cartographie
        // (Google Maps, MapBox, etc.)
        
        if (!$this->packages_data || !is_array($this->packages_data)) {
            return $this;
        }

        $packages = collect($this->packages_data);
        $totalDistance = $packages->count() * 5; // Estimation basique
        $totalDuration = $packages->count() * 15; // 15 min par colis
        
        $this->update([
            'estimated_distance' => $totalDistance,
            'estimated_duration' => $totalDuration,
            'route_optimization' => [
                'calculated_at' => now(),
                'method' => 'basic_estimation',
                'waypoints_count' => $packages->count()
            ]
        ]);
        
        return $this;
    }

    /**
     * Générer et sauvegarder le PDF
     */
    public function generatePdf(): string
    {
        // Générer le nom du fichier
        $filename = "run_sheet_{$this->sheet_code}_" . now()->format('Y-m-d_H-i-s') . '.pdf';
        $path = "run_sheets/{$this->deliverer_id}/{$filename}";
        
        // Sauvegarder le chemin
        $this->update(['pdf_path' => $path]);
        
        return $path;
    }

    /**
     * Supprimer le PDF associé
     */
    public function deletePdf(): bool
    {
        if ($this->pdf_path && Storage::disk('public')->exists($this->pdf_path)) {
            $deleted = Storage::disk('public')->delete($this->pdf_path);
            
            if ($deleted) {
                $this->update(['pdf_path' => null]);
            }
            
            return $deleted;
        }
        
        return true;
    }

    // ==================== MÉTHODES STATIQUES ====================

    /**
     * Créer une nouvelle feuille de route
     */
    public static function createForDeliverer(
        int $delivererId,
        int $delegationId,
        array $packagesData,
        array $options = []
    ): self {
        $totalCod = collect($packagesData)->sum('cod_amount');
        
        return self::create([
            'sheet_code' => self::generateSheetCode($delivererId),
            'deliverer_id' => $delivererId,
            'delegation_id' => $delegationId,
            'date' => $options['date'] ?? today(),
            'package_types' => $options['package_types'] ?? ['deliveries'],
            'sort_criteria' => $options['sort_criteria'] ?? 'address',
            'include_cod_summary' => $options['include_cod_summary'] ?? false,
            'packages_data' => $packagesData,
            'packages_count' => count($packagesData),
            'total_cod_amount' => $totalCod,
            'preparation_notes' => $options['notes'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Générer un code unique pour la feuille
     */
    public static function generateSheetCode(int $delivererId = null): string
    {
        $delivererId = $delivererId ?: auth()->id();
        
        do {
            $code = 'RS_' . $delivererId . '_' . strtoupper(Str::random(6)) . '_' . date('Ymd');
        } while (self::where('sheet_code', $code)->exists());

        return $code;
    }

    /**
     * Obtenir les statistiques des feuilles de route
     */
    public static function getStatsForDeliverer(int $delivererId, int $days = 30): array
    {
        $since = now()->subDays($days);
        
        $sheets = self::where('deliverer_id', $delivererId)
                     ->where('created_at', '>=', $since)
                     ->get();

        return [
            'total_sheets' => $sheets->count(),
            'completed_sheets' => $sheets->where('status', 'COMPLETED')->count(),
            'pending_sheets' => $sheets->where('status', 'PENDING')->count(),
            'in_progress_sheets' => $sheets->where('status', 'IN_PROGRESS')->count(),
            'total_packages' => $sheets->sum('packages_count'),
            'total_cod_amount' => $sheets->sum('total_cod_amount'),
            'average_packages_per_sheet' => $sheets->avg('packages_count'),
            'average_completion_rate' => $sheets->where('status', 'COMPLETED')->avg('completion_rate'),
            'period_days' => $days
        ];
    }

    // ==================== BOOT METHOD ====================

    protected static function boot()
    {
        parent::boot();

        // Auto-générer le code lors de la création
        static::creating(function ($runSheet) {
            if (empty($runSheet->sheet_code)) {
                $runSheet->sheet_code = self::generateSheetCode($runSheet->deliverer_id);
            }
        });

        // Nettoyer les fichiers lors de la suppression
        static::deleting(function ($runSheet) {
            $runSheet->deletePdf();
        });

        // Log des changements de statut
        static::updated(function ($runSheet) {
            if ($runSheet->isDirty('status')) {
                if (class_exists(\App\Services\ActionLogService::class)) {
                    app(\App\Services\ActionLogService::class)->log(
                        'RUN_SHEET_STATUS_CHANGED',
                        'RunSheet',
                        $runSheet->id,
                        $runSheet->getOriginal('status'),
                        $runSheet->status,
                        [
                            'sheet_code' => $runSheet->sheet_code,
                            'deliverer_id' => $runSheet->deliverer_id,
                            'packages_count' => $runSheet->packages_count
                        ]
                    );
                }
            }
        });
    }
}