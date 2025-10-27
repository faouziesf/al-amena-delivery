<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CriticalActionConfig;

class CriticalActionConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $actions = [
            [
                'action_type' => 'USER_ROLE_CHANGED',
                'target_type' => 'User',
                'description' => 'Changement de rôle utilisateur',
                'is_critical' => true,
            ],
            [
                'action_type' => 'FINANCIAL_VALIDATION',
                'target_type' => 'Transaction',
                'description' => 'Validation financière importante',
                'is_critical' => true,
                'conditions' => json_encode([
                    'amount' => ['operator' => '>', 'value' => 1000]
                ]),
            ],
            [
                'action_type' => 'IMPERSONATION_START',
                'target_type' => 'User',
                'description' => 'Début de session impersonation',
                'is_critical' => true,
            ],
            [
                'action_type' => 'IMPERSONATION_STOP',
                'target_type' => 'User',
                'description' => 'Fin de session impersonation',
                'is_critical' => true,
            ],
            [
                'action_type' => 'SYSTEM_SETTING_CHANGED',
                'target_type' => 'SystemSetting',
                'description' => 'Modification paramètre système',
                'is_critical' => true,
            ],
            [
                'action_type' => 'USER_CREATED',
                'target_type' => 'User',
                'description' => 'Création d\'un nouvel utilisateur',
                'is_critical' => false,
            ],
            [
                'action_type' => 'PACKAGE_STATUS_CHANGED',
                'target_type' => 'Package',
                'description' => 'Changement de statut colis important',
                'is_critical' => true,
                'conditions' => json_encode([
                    'new_status' => ['operator' => 'in', 'value' => ['CANCELLED', 'LOST']]
                ]),
            ],
            [
                'action_type' => 'USER_DELETED',
                'target_type' => 'User',
                'description' => 'Suppression d\'un utilisateur',
                'is_critical' => true,
            ],
            [
                'action_type' => 'FINANCIAL_CHARGE_CREATED',
                'target_type' => 'FixedCharge',
                'description' => 'Création d\'une charge fixe',
                'is_critical' => false,
            ],
            [
                'action_type' => 'VEHICLE_CREATED',
                'target_type' => 'Vehicle',
                'description' => 'Ajout d\'un nouveau véhicule',
                'is_critical' => false,
            ],
        ];

        foreach ($actions as $action) {
            CriticalActionConfig::create($action);
        }
    }
}
