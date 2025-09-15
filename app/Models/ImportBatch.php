<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_code',
        'user_id',
        'filename',
        'total_rows',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'status',
        'started_at',
        'completed_at',
        'errors',
        'summary',
        'file_path'
    ];

    protected $casts = [
        'errors' => 'array',
        'summary' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function packages()
    {
        return $this->hasMany(Package::class, 'import_batch_id');
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'COMPLETED');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'FAILED');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'PROCESSING');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper methods
    public function isCompleted()
    {
        return $this->status === 'COMPLETED';
    }

    public function isFailed()
    {
        return $this->status === 'FAILED';
    }

    public function isProcessing()
    {
        return $this->status === 'PROCESSING';
    }

    public function isPending()
    {
        return $this->status === 'PENDING';
    }

    public function getSuccessRateAttribute()
    {
        if ($this->total_rows === 0) return 0;
        return round(($this->successful_rows / $this->total_rows) * 100, 1);
    }

    public function getFailureRateAttribute()
    {
        if ($this->total_rows === 0) return 0;
        return round(($this->failed_rows / $this->total_rows) * 100, 1);
    }

    public function getProcessingTimeAttribute()
    {
        if (!$this->started_at) return null;
        
        $endTime = $this->completed_at ?? now();
        return $this->started_at->diffInSeconds($endTime);
    }

    public function getFormattedProcessingTimeAttribute()
    {
        $seconds = $this->getProcessingTimeAttribute();
        if (!$seconds) return 'N/A';
        
        if ($seconds < 60) {
            return $seconds . ' secondes';
        } elseif ($seconds < 3600) {
            return round($seconds / 60, 1) . ' minutes';
        } else {
            return round($seconds / 3600, 1) . ' heures';
        }
    }

    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'PENDING' => 'En attente',
            'PROCESSING' => 'En cours',
            'COMPLETED' => 'Terminé',
            'FAILED' => 'Échoué',
            'CANCELLED' => 'Annulé',
            default => $this->status
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'PENDING' => 'text-orange-600 bg-orange-100',
            'PROCESSING' => 'text-blue-600 bg-blue-100',
            'COMPLETED' => 'text-green-600 bg-green-100',
            'FAILED' => 'text-red-600 bg-red-100',
            'CANCELLED' => 'text-gray-600 bg-gray-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }

    public function hasErrors()
    {
        return $this->failed_rows > 0 && !empty($this->errors);
    }

    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_path || !file_exists(storage_path('app/' . $this->file_path))) {
            return 'N/A';
        }
        
        $bytes = filesize(storage_path('app/' . $this->file_path));
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Actions
    public function markAsStarted()
    {
        $this->update([
            'status' => 'PROCESSING',
            'started_at' => now()
        ]);
        return $this;
    }

    public function markAsCompleted($summary = [])
    {
        $this->update([
            'status' => 'COMPLETED',
            'completed_at' => now(),
            'summary' => $summary
        ]);
        return $this;
    }

    public function markAsFailed($errors = [])
    {
        $this->update([
            'status' => 'FAILED',
            'completed_at' => now(),
            'errors' => $errors
        ]);
        return $this;
    }

    public function incrementProcessed($successful = true)
    {
        $this->increment('processed_rows');
        
        if ($successful) {
            $this->increment('successful_rows');
        } else {
            $this->increment('failed_rows');
        }
        
        return $this;
    }

    public function addError($row, $error, $data = [])
    {
        $errors = $this->errors ?? [];
        $errors[] = [
            'row' => $row,
            'error' => $error,
            'data' => $data,
            'timestamp' => now()->toISOString()
        ];
        
        $this->update(['errors' => $errors]);
        return $this;
    }

    public function getErrorsForRow($row)
    {
        if (!$this->errors) return [];
        
        return collect($this->errors)->where('row', $row)->all();
    }

    public function getTopErrors($limit = 5)
    {
        if (!$this->errors) return [];
        
        return collect($this->errors)
            ->groupBy('error')
            ->map(function ($errors, $error) {
                return [
                    'error' => $error,
                    'count' => $errors->count(),
                    'rows' => $errors->pluck('row')->take(10)->all()
                ];
            })
            ->sortByDesc('count')
            ->take($limit)
            ->values()
            ->all();
    }

    // Static methods
    public static function createForUser($userId, $filename, $totalRows)
    {
        return static::create([
            'batch_code' => 'IMP_' . strtoupper(Str::random(8)) . '_' . date('Ymd'),
            'user_id' => $userId,
            'filename' => $filename,
            'total_rows' => $totalRows,
            'processed_rows' => 0,
            'successful_rows' => 0,
            'failed_rows' => 0,
            'status' => 'PENDING'
        ]);
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($batch) {
            if (empty($batch->batch_code)) {
                $batch->batch_code = 'IMP_' . strtoupper(Str::random(8)) . '_' . date('Ymd');
            }
        });

        static::deleting(function ($batch) {
            // Supprimer le fichier associé
            if ($batch->file_path && file_exists(storage_path('app/' . $batch->file_path))) {
                unlink(storage_path('app/' . $batch->file_path));
            }
        });
    }
}