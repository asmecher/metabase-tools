<?php

/**
 * Set the visibility for all tables to visible for the specified database.
 */

use GuzzleHttp\Client;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require_once('vendor/autoload.php');
require_once('includes/functions.php');

// Load the configuration file
$config = require_once('config/config.php');

$stderr = fopen('php://stderr', 'w');

// Establish the database connection for metabase installation
$capsule = new Capsule;
$capsule->addConnection($config['databases']['metabase'], 'metabase');
$metabaseConnection = $capsule->getConnection('metabase');

$databaseId = getMetabaseDatabaseId($metabaseConnection, $config['journalPath']);

$client = new Client(['base_uri' => $config['metabase']['baseUrl']]);
$headers = ['x-api-key' => $config['metabase']['apiKey']];

fputs($stderr, "Getting schema for database ID $databaseId... ");
$response = $client->request('GET', "/api/database/{$databaseId}/schema/?include_hidden=true", ['headers' => $headers]);
if ($code = $response->getStatusCode() != 200) {
    throw new \Exception("Received an unexpected status code: $code!\n");
}
$schemaRaw = (string) $response->getBody();
$schema = json_decode($schemaRaw);
fputs($stderr, "Done!\n");

$tableIds = [];
foreach ($schema as $schemaEntry) {
    if (($schemaEntry->visibility_type ?? null) === 'hidden') $tableIds[] = $schemaEntry->id;
}

if (!empty($tableIds)) {
    fputs($stderr, "Resetting visibility for tables " . json_encode($tableIds) . "... ");
    $response = $client->request('PUT', '/api/table', ['headers' => $headers, 'json' => (object) ['ids' => $tableIds, 'visibility_type' => null]]);
    if ($code = $response->getStatusCode() != 200) {
        throw new \Exception("Received an unexpected status code: $code!\n");
    }
    fputs($stderr, "Done!\n");
} else {
    fputs($stderr, "No hidden tables found.\n");
}
