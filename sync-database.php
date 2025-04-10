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

$headers = ['x-api-key' => $config['metabase']['apiKey'], 'x-metabase-apikey' => $config['metabase']['mbApiKey']];
$client = new Client(['base_uri' => $config['metabase']['baseUrl'], 'headers' => $headers]);

$databaseId = getMetabaseDatabaseId_API($client, $config['journalPath']);

// Create the database in Metabase.
echo "Syncing database...\n";
$response = $client->request('POST', "/api/notify/db/{$databaseId}", [
    'json' => [
        'scan' => 'full',
        'synchronous' => 1,
    ],
]);
echo "Completed database scan!\n";
