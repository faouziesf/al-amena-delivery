<?php

namespace App\Services;

use App\Models\ActionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActionLogService
{
    /**
     * Enregistrer une action dans les logs
     */
    public function log($action, $modelType, $modelId, $oldValue = null, $newValue = null, $additionalData = [])
    {
        try {
            // S'assurer que la table action_logs existe
            if (!$this->tableExists('action_logs')) {
                return; // Ignorer silencieusement si la table n'existe pas
            }

            ActionLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'old_value' => $this->formatValue($oldValue),
                'new_value' => $this->formatValue($newValue),
                'additional_data' => is_array($additionalData) ? json_encode($additionalData) : $additionalData,
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ]);
        } catch (\Exception $e) {
            // Log l'erreur dans les logs système mais ne pas faire planter l'application
            Log::error('Erreur lors de l\'enregistrement du log d\'action: ' . $e->getMessage(), [
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Formater une valeur pour le stockage
     */
    private function formatValue($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }

    /**
     * Vérifier si une table existe
     */
    private function tableExists($tableName)
    {
        try {
            \DB::select("SELECT 1 FROM {$tableName} LIMIT 1");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Enregistrer une opération wallet
     */
    public function logWalletOperation($type, $user, $amount, $additionalData = [])
    {
        $this->log(
            'WALLET_' . $type,
            'UserWallet',
            $user->wallet ? $user->wallet->id : null,
            null,
            $amount,
            array_merge([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'amount' => $amount
            ], $additionalData)
        );
    }

    /**
     * Enregistrer une modification de client
     */
    public function logClientModification($client, $action, $oldData = [], $newData = [])
    {
        $this->log(
            'CLIENT_' . $action,
            'User',
            $client->id,
            $oldData,
            $newData,
            [
                'client_name' => $client->name,
                'client_email' => $client->email
            ]
        );
    }

    /**
     * Enregistrer une action sur un package
     */
    public function logPackageAction($package, $action, $additionalData = [])
    {
        $this->log(
            'PACKAGE_' . $action,
            'Package',
            $package->id,
            null,
            null,
            array_merge([
                'package_code' => $package->package_code ?? null,
                'sender_id' => $package->sender_id ?? null
            ], $additionalData)
        );
    }

    /**
     * Récupérer les logs récents pour un utilisateur
     */
    public function getRecentLogsForUser($userId, $limit = 20)
    {
        try {
            if (!$this->tableExists('action_logs')) {
                return collect(); // Retourner une collection vide
            }

            return ActionLog::where('user_id', $userId)
                           ->orderBy('created_at', 'desc')
                           ->limit($limit)
                           ->get();
        } catch (\Exception $e) {
            Log::error('Erreur récupération logs utilisateur: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Récupérer les logs pour un modèle spécifique
     */
    public function getLogsForModel($modelType, $modelId, $limit = 50)
    {
        try {
            if (!$this->tableExists('action_logs')) {
                return collect();
            }

            return ActionLog::where('model_type', $modelType)
                           ->where('model_id', $modelId)
                           ->orderBy('created_at', 'desc')
                           ->limit($limit)
                           ->get();
        } catch (\Exception $e) {
            Log::error('Erreur récupération logs modèle: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Nettoyer les anciens logs
     */
    public function cleanOldLogs($days = 90)
    {
        try {
            if (!$this->tableExists('action_logs')) {
                return 0;
            }

            $cutoffDate = now()->subDays($days);
            
            return ActionLog::where('created_at', '<', $cutoffDate)->delete();
        } catch (\Exception $e) {
            Log::error('Erreur nettoyage logs: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtenir des statistiques sur les logs
     */
    public function getLogStats($days = 30)
    {
        try {
            if (!$this->tableExists('action_logs')) {
                return [
                    'total_logs' => 0,
                    'recent_logs' => 0,
                    'top_actions' => [],
                    'active_users' => 0
                ];
            }

            $since = now()->subDays($days);

            return [
                'total_logs' => ActionLog::count(),
                'recent_logs' => ActionLog::where('created_at', '>=', $since)->count(),
                'top_actions' => ActionLog::where('created_at', '>=', $since)
                                        ->selectRaw('action, count(*) as count')
                                        ->groupBy('action')
                                        ->orderBy('count', 'desc')
                                        ->limit(10)
                                        ->get(),
                'active_users' => ActionLog::where('created_at', '>=', $since)
                                         ->distinct('user_id')
                                         ->count('user_id')
            ];
        } catch (\Exception $e) {
            Log::error('Erreur statistiques logs: ' . $e->getMessage());
            return [
                'total_logs' => 0,
                'recent_logs' => 0,
                'top_actions' => [],
                'active_users' => 0
            ];
        }
    }
}