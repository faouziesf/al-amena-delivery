<?php

namespace App\Services;

use App\Models\ActionLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ActionLogService
{
    public function log(string $actionType, $targetType = null, $targetId = null, $oldValue = null, $newValue = null, array $additionalData = [])
    {
        $user = Auth::user();
        
        if (!$user) {
            return null;
        }

        return ActionLog::create([
            'user_id' => $user->id,
            'user_role' => $user->role,
            'action_type' => $actionType,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'old_value' => is_array($oldValue) ? json_encode($oldValue) : $oldValue,
            'new_value' => is_array($newValue) ? json_encode($newValue) : $newValue,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'additional_data' => $additionalData,
        ]);
    }

    public function logLogin(User $user)
    {
        return ActionLog::create([
            'user_id' => $user->id,
            'user_role' => $user->role,
            'action_type' => 'USER_LOGIN',
            'target_type' => 'User',
            'target_id' => $user->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'additional_data' => [
                'login_time' => now()->toISOString()
            ],
        ]);
    }

    public function logLogout(User $user)
    {
        return ActionLog::create([
            'user_id' => $user->id,
            'user_role' => $user->role,
            'action_type' => 'USER_LOGOUT',
            'target_type' => 'User',
            'target_id' => $user->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'additional_data' => [
                'logout_time' => now()->toISOString()
            ],
        ]);
    }

    public function logWalletOperation(string $operation, User $user, $amount, array $context = [])
    {
        return ActionLog::create([
            'user_id' => Auth::user()->id ?? $user->id,
            'user_role' => Auth::user()->role ?? 'SYSTEM',
            'action_type' => 'WALLET_' . strtoupper($operation),
            'target_type' => 'UserWallet',
            'target_id' => $user->id,
            'new_value' => $amount,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'additional_data' => array_merge([
                'wallet_operation' => $operation,
                'amount' => $amount,
                'target_user_id' => $user->id
            ], $context),
        ]);
    }

    public function logFinancialTransaction(string $transactionId, string $type, $amount, User $user)
    {
        return $this->log(
            'FINANCIAL_TRANSACTION',
            'FinancialTransaction',
            $transactionId,
            null,
            $amount,
            [
                'transaction_type' => $type,
                'amount' => $amount,
                'target_user_id' => $user->id
            ]
        );
    }
}