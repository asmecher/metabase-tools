<?php

/**
 * Configure a Metabase database for use with a single-journal view of a multi-journal OJS installation.
 * This provides Metabase with configuration that it can't automatically extract from the database schema
 * because the schema is based on views (or because foreign key constraints don't exist in the case of
 * OJS 3.3.0).
 */

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Hidehalo\Nanoid\Client;

require_once('vendor/autoload.php');
require_once('includes/functions.php');

// Load the configuration file
$config = require_once('config/config.php');

$nanoClient = new Client();

// Establish the database connections
$capsule = new Capsule;
foreach ($config['databases'] as $name => $dbConfig) {
    $capsule->addConnection($dbConfig, $name);
}

$singleConnection = $capsule->getConnection('single');
$multiConnection = $capsule->getConnection('multi');
$metabaseConnection = $capsule->getConnection('metabase');

$singleSm = $singleConnection->getDoctrineSchemaManager();
$multiSm = $multiConnection->getDoctrineSchemaManager();

$databaseId = getMetabaseDatabaseId($metabaseConnection, $config['journalPath']);

// For each view in the single-journal installation, determine what the primary key should be and configure it in Metabase.
echo "Identifying primary keys and setting configuration in Metabase...\n";
foreach (array_keys($singleSm->listViews()) as $viewName) {
    $primaryIndexes = array_filter($multiSm->listTableIndexes($viewName), fn($i) => $i->isPrimary());
    if (count($primaryIndexes) !== 1) throw new Exception("Table $viewName needs to have one primary index!");
    $primaryIndex = array_shift($primaryIndexes);

    $primaryColumns = $primaryIndex->getColumns();
    if (count($primaryColumns) !== 1) throw new Exception("Table $viewName has a compound primary key!");
    $primaryColumn = array_shift($primaryColumns);

    $count = $metabaseConnection
        ->table('metabase_field AS f')
        ->join('metabase_table AS t', 'f.table_id', '=', 't.id')
        ->where('t.db_id', $databaseId)
        ->where('f.name', $primaryColumn)
        ->where('t.name', $viewName)
        ->update(['f.semantic_type' => 'type/PK']);

    if ($count) echo " - $viewName.$primaryColumn\n";
}

// For each view in the single-journal installation, configure any foreign keys.
// This is not automatically detected from the source DB because OJS 3.3.0-x does not specify foreign key constraints.
// When OJS 3.3.0-x support is dropped (and all foreign key constraints are introduced), this can be automated.
echo "Setting foreign key configuration in Metabase...\n";
foreach ($config['foreignKeys'] as $foreignSpec => $primarySpec) {
    list($foreignTable, $foreignColumn) = explode('.', $foreignSpec);
    list($primaryTable, $primaryColumn) = explode('.', $primarySpec);

    $count = $metabaseConnection
        ->table('metabase_field AS f_fk')
        ->join('metabase_table AS t_fk', 'f_fk.table_id', '=', 't_fk.id')
        ->join('metabase_database AS d', 't_fk.db_id', '=', 'd.id')
        ->join('metabase_table AS t_pk', 't_pk.db_id', '=', 'd.id')
        ->join('metabase_field AS f_pk', 't_pk.id', '=', 'f_pk.table_id')
        ->where('t_pk.name', $primaryTable)
        ->where('f_pk.name', $primaryColumn)
        ->where('t_fk.name', $foreignTable)
        ->where('f_fk.name', $foreignColumn)
        ->where('d.id', $databaseId)
        ->update([
            'f_fk.semantic_type' => 'type/FK',
            'f_fk.fk_target_field_id' => $metabaseConnection->raw('f_pk.id'),
        ]);

    if ($count) echo " - $foreignSpec => $primarySpec\n";
}

// For each view in the single-journal installation, configure any enumeration columns.
echo "Setting up enumerations in Metabase...\n";
foreach ($config['enumerations'] as $columnSpec => $valueMap) {
    list($tableName, $columnName) = explode('.', $columnSpec);

    // Configure the metabase_fieldvalues and metabase_field entries. This is where
    // the list of possible options (and human-readable mappings) are entered.
    $count = $metabaseConnection
        ->table('metabase_fieldvalues AS fv')
        ->join('metabase_field AS f', 'fv.field_id', '=', 'f.id')
        ->join('metabase_table AS t', 'f.table_id', '=', 't.id')
        ->where('t.db_id', $databaseId)
        ->where('f.name', $columnName)
        ->where('t.name', $tableName)
        ->update([
            'f.semantic_type' => 'type/Enum',
            'f.has_field_values' => 'list',
            'fv.values' => json_encode(array_keys($valueMap)),
            'fv.human_readable_values' => json_encode(array_values($valueMap)),
        ]);

    if ($count) echo " - $columnSpec field values and field configured\n";

    // Configure the dimensions entry. This tells Metabase to present the user with
    // the human-readable forms.
    $result = $metabaseConnection
        ->table('metabase_field AS f')
        ->join('metabase_table AS t', 'f.table_id', '=', 't.id')
        ->leftJoin('dimension AS d', 'd.field_id', '=', 'f.id')
        ->where('t.db_id', $databaseId)
        ->where('f.name', $columnName)
        ->where('t.name', $tableName)
        ->select(['f.id AS field_id', 'f.name AS field_name', 'd.id AS dimension_id'])
        ->get();

    if ($result->count() !== 1) throw new Exception("Could not identify field for $columnSpec!");
    $row = $result->first();
    if ($row->dimension_id === null) {
        $metabaseConnection->table('dimension')->insert([
            'field_id' => $row->field_id,
            'created_at' => $metabaseConnection->raw('NOW()'),
            'updated_at' => $metabaseConnection->raw('NOW()'),
            'name' => $row->field_name,
            'type' => 'internal',
            'entity_id' => $nanoClient->generateId(21), // This is a unique ID for import/export purposes
        ]);
        echo " - $columnSpec received a new dimensions entry\n";
    }
}

echo "Done.\n";
