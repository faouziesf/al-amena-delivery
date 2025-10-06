<?php

$schema = json_decode(file_get_contents(__DIR__ . '/full_schema.json'), true);

$migrationContent = <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

PHP;

// Tableau pour l'ordre des tables (dépendances)
$orderedTables = [
    'users',
    'delegations',
    'client_profiles',
    'client_bank_accounts',
    'client_pickup_addresses',
    'saved_addresses',
    'user_wallets',
    'financial_transactions',
    'withdrawal_requests',
    'topup_requests',
    'deliverer_wallet_emptyings',
    'pickup_requests',
    'packages',
    'package_status_histories',
    'cod_modifications',
    'import_batches',
    'run_sheets',
    'manifests',
    'transit_routes',
    'transit_boxes',
    'tickets',
    'ticket_messages',
    'ticket_attachments',
    'complaints',
    'notifications',
    'action_logs',
    'wallet_transaction_backups',
    'transactions_table_alias',
    'password_reset_tokens',
    'sessions',
    'failed_jobs',
    'job_batches',
    'jobs',
    'personal_access_tokens',
    'cache',
    'cache_locks',
];

// Générer les tables dans l'ordre
foreach ($orderedTables as $tableName) {
    if (!isset($schema[$tableName])) continue;
    
    $tableInfo = $schema[$tableName];
    $migrationContent .= "\n        // Table: {$tableName}\n";
    $migrationContent .= "        Schema::create('{$tableName}', function (Blueprint \$table) {\n";
    
    $hasCreatedAt = false;
    $hasUpdatedAt = false;
    
    foreach ($tableInfo['columns'] as $col) {
        if ($col['name'] === 'created_at') $hasCreatedAt = true;
        if ($col['name'] === 'updated_at') $hasUpdatedAt = true;
        
        $line = convertColumnToLaravel($col, $tableName);
        if ($line) {
            $migrationContent .= "            {$line}\n";
        }
    }
    
    // Ajouter timestamps() si la table a created_at et updated_at
    if ($hasCreatedAt && $hasUpdatedAt) {
        $migrationContent .= "            \$table->timestamps();\n";
    }
    
    // Ajouter les index
    if (!empty($tableInfo['indexes'])) {
        $migrationContent .= "\n";
        foreach ($tableInfo['indexes'] as $idx) {
            if (strpos($idx['name'], '_unique') !== false && $idx['name'] !== 'sqlite_autoindex_' . $tableName . '_1') {
                continue; // Déjà géré par unique()
            }
            if (count($idx['columns']) > 0 && strpos($idx['name'], 'sqlite_autoindex') === false) {
                $cols = "'" . implode("', '", $idx['columns']) . "'";
                $migrationContent .= "            \$table->index([{$cols}]);\n";
            }
        }
    }
    
    $migrationContent .= "        });\n";
}

$migrationContent .= <<<'PHP'
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

PHP;

// Drop tables dans l'ordre inverse
foreach (array_reverse($orderedTables) as $tableName) {
    if (isset($schema[$tableName])) {
        $migrationContent .= "        Schema::dropIfExists('{$tableName}');\n";
    }
}

$migrationContent .= <<<'PHP'
    }
};

PHP;

// Sauvegarder la migration
file_put_contents(__DIR__ . '/database/migrations/2025_01_06_000000_create_complete_database_schema.php', $migrationContent);

echo "✅ Migration générée avec succès!\n";
echo "📄 Fichier: database/migrations/2025_01_06_000000_create_complete_database_schema.php\n";

function convertColumnToLaravel($col, $tableName) {
    $name = $col['name'];
    $type = strtolower($col['type']);
    $notnull = $col['notnull'];
    $default = $col['dflt_value'];
    $pk = $col['pk'];
    
    // Skip auto-increment primary key (géré par id())
    if ($pk && $name === 'id' && $type === 'integer') {
        return '$table->id();';
    }
    
    // Skip timestamps si déjà gérés
    if ($name === 'created_at' || $name === 'updated_at') {
        return null; // On les ajoutera à la fin
    }
    
    // Convertir le type
    $line = '';
    if (strpos($type, 'varchar') !== false || $type === 'text') {
        $line = "\$table->string('{$name}')";
    } elseif ($type === 'integer') {
        if (str_contains($name, '_id')) {
            $line = "\$table->unsignedBigInteger('{$name}')";
        } else {
            $line = "\$table->integer('{$name}')";
        }
    } elseif ($type === 'datetime' || $type === 'timestamp') {
        $line = "\$table->timestamp('{$name}')";
    } elseif ($type === 'tinyint(1)' || $type === 'boolean') {
        $line = "\$table->boolean('{$name}')";
    } elseif ($type === 'numeric' || $type === 'decimal') {
        $line = "\$table->decimal('{$name}', 15, 3)";
    } elseif ($type === 'longtext') {
        $line = "\$table->longText('{$name}')";
    } elseif ($type === 'mediumtext') {
        $line = "\$table->mediumText('{$name}')";
    } else {
        $line = "\$table->text('{$name}')";
    }
    
    // Nullable
    if (!$notnull || $name === 'remember_token') {
        $line .= '->nullable()';
    }
    
    // Default value
    if ($default !== null) {
        $cleanDefault = str_replace("'", '', $default);
        if ($cleanDefault === '0' || $cleanDefault === '1') {
            $line .= "->default({$cleanDefault})";
        } else {
            $line .= "->default('{$cleanDefault}')";
        }
    }
    
    // Unique
    if ($name === 'email' && $tableName === 'users') {
        $line .= '->unique()';
    }
    
    $line .= ';';
    
    return $line;
}
