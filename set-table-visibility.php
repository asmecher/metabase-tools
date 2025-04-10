<?php

/**
 * Set the visibility for all tables to visible for the specified database.
 */

use GuzzleHttp\Client;

require_once('vendor/autoload.php');
require_once('includes/functions.php');

// Load the configuration file
$config = require_once('config/config.php');

$stderr = fopen('php://stderr', 'w');

$headers = ['x-api-key' => $config['metabase']['apiKey']];
$client = new Client(['base_uri' => $config['metabase']['baseUrl'], 'headers' => $headers]);

$databaseId = getMetabaseDatabaseId_API($client, $config['journalPath']);

fputs($stderr, "Getting schema for database ID $databaseId... ");
$response = $client->request('GET', "/api/database/{$databaseId}/schema/?include_hidden=true");
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
    $response = $client->request('PUT', '/api/table', ['json' => (object) ['ids' => $tableIds, 'visibility_type' => null]]);
    if ($code = $response->getStatusCode() != 200) {
        throw new \Exception("Received an unexpected status code: $code!\n");
    }
    fputs($stderr, "Done!\n");
} else {
    fputs($stderr, "No hidden tables found.\n");
}
