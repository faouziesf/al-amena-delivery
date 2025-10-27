<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CriticalActionConfig extends Model
{
    use HasFactory;

    protected $table = 'critical_action_config';

    protected $fillable = [
        'action_type',
        'target_type',
        'description',
        'is_critical',
        'conditions',
    ];

    protected $casts = [
        'is_critical' => 'boolean',
        'conditions' => 'array',
    ];

    /**
     * Vérifie si une action est critique selon la configuration
     * 
     * @param string $actionType
     * @param string|null $targetType
     * @param array $data Données de l'action pour évaluer les conditions
     * @return bool
     */
    public static function isActionCritical($actionType, $targetType = null, $data = []): bool
    {
        $config = self::where('action_type', $actionType)
            ->where(function($query) use ($targetType) {
                $query->where('target_type', $targetType)
                      ->orWhereNull('target_type');
            })
            ->where('is_critical', true)
            ->first();

        if (!$config) {
            return false;
        }

        // Si pas de conditions, c'est critique par défaut
        if (empty($config->conditions)) {
            return true;
        }

        // Évaluer les conditions
        return self::evaluateConditions($config->conditions, $data);
    }

    /**
     * Évalue les conditions pour déterminer si une action est critique
     * 
     * @param array $conditions
     * @param array $data
     * @return bool
     */
    private static function evaluateConditions($conditions, $data): bool
    {
        foreach ($conditions as $field => $condition) {
            if (!isset($data[$field])) {
                continue;
            }

            $value = $data[$field];
            
            // Support pour différents types de conditions
            if (is_array($condition)) {
                // Condition avec opérateur
                $operator = $condition['operator'] ?? '=';
                $expectedValue = $condition['value'] ?? null;

                $result = match($operator) {
                    '=' => $value == $expectedValue,
                    '!=' => $value != $expectedValue,
                    '>' => $value > $expectedValue,
                    '<' => $value < $expectedValue,
                    '>=' => $value >= $expectedValue,
                    '<=' => $value <= $expectedValue,
                    'in' => in_array($value, (array)$expectedValue),
                    'not_in' => !in_array($value, (array)$expectedValue),
                    'contains' => str_contains($value, $expectedValue),
                    default => false,
                };

                if (!$result) {
                    return false;
                }
            } else {
                // Condition simple (égalité)
                if ($value != $condition) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Obtient toutes les actions critiques configurées
     */
    public static function getAllCriticalActions(): array
    {
        return self::where('is_critical', true)
            ->get()
            ->map(function($config) {
                return [
                    'action_type' => $config->action_type,
                    'target_type' => $config->target_type,
                    'description' => $config->description,
                ];
            })
            ->toArray();
    }

    // Scopes
    public function scopeCritical($query)
    {
        return $query->where('is_critical', true);
    }

    public function scopeForAction($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    public function scopeForTarget($query, $targetType)
    {
        return $query->where('target_type', $targetType);
    }
}
