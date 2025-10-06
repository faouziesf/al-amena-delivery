<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== 📊 CONSULTATION DE LA BASE DE DONNÉES ===\n\n";

try {
    // 1. Lister toutes les tables
    echo "📋 TABLES EXISTANTES:\n";
    $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
    foreach ($tables as $table) {
        echo "  - {$table->name}\n";
    }
    echo "\n";

    // 2. Vérifier la structure de la table users
    echo "👥 STRUCTURE DE LA TABLE 'users':\n";
    $userColumns = DB::select("PRAGMA table_info(users)");
    foreach ($userColumns as $col) {
        $pk = $col->pk ? ' [PK]' : '';
        $notnull = $col->notnull ? ' NOT NULL' : '';
        $default = $col->dflt_value ? " DEFAULT {$col->dflt_value}" : '';
        echo "  - {$col->name} ({$col->type}){$pk}{$notnull}{$default}\n";
    }
    echo "\n";

    // 3. Compter les utilisateurs
    echo "📊 STATISTIQUES:\n";
    $userCount = DB::table('users')->count();
    echo "  - Utilisateurs: {$userCount}\n";
    
    if (DB::getSchemaBuilder()->hasTable('delegations')) {
        $delegationCount = DB::table('delegations')->count();
        echo "  - Délégations: {$delegationCount}\n";
    }
    
    if (DB::getSchemaBuilder()->hasTable('packages')) {
        $packageCount = DB::table('packages')->count();
        echo "  - Packages: {$packageCount}\n";
    }
    
    if (DB::getSchemaBuilder()->hasTable('client_profiles')) {
        $profileCount = DB::table('client_profiles')->count();
        echo "  - Profils clients: {$profileCount}\n";
    }
    echo "\n";

    // 4. Lister les utilisateurs
    echo "👤 UTILISATEURS (avec rôles):\n";
    $users = DB::table('users')->select('id', 'name', 'email', 'role')->orderBy('id')->get();
    foreach ($users as $user) {
        echo "  [{$user->id}] {$user->name} ({$user->email}) - {$user->role}\n";
    }
    echo "\n";

    // 5. Vérifier la colonne last_login
    $hasLastLogin = false;
    foreach ($userColumns as $col) {
        if ($col->name === 'last_login') {
            $hasLastLogin = true;
            break;
        }
    }
    
    if ($hasLastLogin) {
        echo "✅ Colonne 'last_login' existe dans la table users\n";
    } else {
        echo "❌ Colonne 'last_login' MANQUANTE dans la table users\n";
        echo "   → Exécutez: php artisan migrate\n";
    }
    echo "\n";

    // 6. Vérifier les migrations
    if (DB::getSchemaBuilder()->hasTable('migrations')) {
        echo "📜 MIGRATIONS EXÉCUTÉES:\n";
        $migrations = DB::table('migrations')->orderBy('batch')->get();
        foreach ($migrations as $migration) {
            echo "  [{$migration->batch}] {$migration->migration}\n";
        }
    }

} catch (\Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}
