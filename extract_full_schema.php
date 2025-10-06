<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔄 Extraction du schéma complet de la base de données...\n\n";

// Obtenir toutes les tables
$tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' AND name != 'migrations' ORDER BY name");

$fullSchema = [];

foreach ($tables as $table) {
    $tableName = $table->name;
    echo "  Extraction de {$tableName}...\n";
    
    // Obtenir les colonnes
    $columns = DB::select("PRAGMA table_info({$tableName})");
    
    // Obtenir les index
    $indexes = DB::select("PRAGMA index_list({$tableName})");
    
    // Obtenir les clés étrangères
    $foreignKeys = DB::select("PRAGMA foreign_key_list({$tableName})");
    
    $fullSchema[$tableName] = [
        'columns' => [],
        'indexes' => [],
        'foreign_keys' => []
    ];
    
    foreach ($columns as $col) {
        $fullSchema[$tableName]['columns'][] = [
            'cid' => $col->cid,
            'name' => $col->name,
            'type' => $col->type,
            'notnull' => $col->notnull,
            'dflt_value' => $col->dflt_value,
            'pk' => $col->pk
        ];
    }
    
    foreach ($indexes as $idx) {
        $indexInfo = DB::select("PRAGMA index_info({$idx->name})");
        $fullSchema[$tableName]['indexes'][] = [
            'name' => $idx->name,
            'unique' => $idx->unique,
            'columns' => array_map(fn($i) => $i->name, $indexInfo)
        ];
    }
    
    foreach ($foreignKeys as $fk) {
        $fullSchema[$tableName]['foreign_keys'][] = [
            'column' => $fk->from,
            'referenced_table' => $fk->table,
            'referenced_column' => $fk->to,
            'on_delete' => $fk->on_delete ?? 'NO ACTION',
            'on_update' => $fk->on_update ?? 'NO ACTION'
        ];
    }
}

// Sauvegarder dans un fichier JSON
file_put_contents(__DIR__ . '/full_schema.json', json_encode($fullSchema, JSON_PRETTY_PRINT));

echo "\n✅ Schéma complet extrait !\n";
echo "📄 Fichier créé: full_schema.json\n";
echo "📊 Tables extraites: " . count($fullSchema) . "\n\n";

// Afficher un résumé
echo "=== RÉSUMÉ DES TABLES ===\n";
foreach ($fullSchema as $tableName => $tableInfo) {
    echo "{$tableName}: " . count($tableInfo['columns']) . " colonnes\n";
}
