<?php

/**
 * Sync the Metabase database.
 */

use GuzzleHttp\Client;
use Illuminate\Database\Capsule\Manager as Capsule;

require_once('vendor/autoload.php');
require_once('includes/functions.php');

// Load the configuration file
$config = require_once('config/config.php');


// Establish the database connection for metabase installation
$capsule = new Capsule;
$capsule->addConnection($config['databases']['metabase'], 'metabase');
$metabaseConnection = $capsule->getConnection('metabase');

$databaseId = getMetabaseDatabaseId($metabaseConnection, $config['journalPath']);

$client = new Client(['base_uri' => $config['metabase']['baseUrl']]);
$headers = ['x-metabase-apikey' => $config['metabase']['mbApiKey']];

// Create the database in Metabase.
echo "Syncing database...\n";
$response = $client->request('POST', "/api/notify/db/{$databaseId}", [
    'headers' => $headers,
    'json' => [
        'scan' => 'full',
        'synchronous' => 1,
    ],
]);
echo "Completed database scan!\n";
