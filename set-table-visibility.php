<?php

/**
 * Set the visibility for all tables to visible for the specified database.
 */

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require_once('vendor/autoload.php');
require_once('functions.php');

// Load the configuration file
$config = require_once('config.php');

// Establish the database connection
$capsule = new Capsule;
$capsule->addConnection($config['databases']['metabase'], 'metabase');
$metabaseConnection = $capsule->getConnection('metabase');

$databaseId = getMetabaseDatabaseId($metabaseConnection, $config['journalPath']);

echo "Unhiding any hidden tables... ";
// Unhide all tables. (user_groups gets hidden by default)
$count = $metabaseConnection->table('metabase_table')->where('db_id', $databaseId)->update(['visibility_type' => null]);
echo "$count tables affected.\n";
