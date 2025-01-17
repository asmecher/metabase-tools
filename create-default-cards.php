<?php

/**
 * Create a database in Metabase with the supplied configuration.
 */

use GuzzleHttp\Client;
use Illuminate\Database\Capsule\Manager as Capsule;

require_once('vendor/autoload.php');
require_once('includes/functions.php');
require_once('src/MetabaseSchema.php');

// Load the configuration file
$config = require_once('config/config.php');

// Establish the database connection for metabase installation
$capsule = new Capsule;
$capsule->addConnection($config['databases']['metabase'], 'metabase');
$metabaseConnection = $capsule->getConnection('metabase');

$databaseId = getMetabaseDatabaseId($metabaseConnection, $config['journalPath']);

$client = new Client(['base_uri' => $config['metabase']['baseUrl']]);
$headers = ['x-api-key' => $config['metabase']['apiKey']];

echo "Get schema descriptor... ";
$response = $client->request('GET', '/api/database/' . $databaseId . '?include=tables.fields', ['headers' => $headers]);
if ($code = $response->getStatusCode() != 200) {
    echo "Received an unexpected status code: $code!\n";
    exit();
}
$schema = new MetabaseSchema(json_decode($response->getBody()));
echo "Done.\n";

// Get the set of default cards
$defaultCards = require_once('config/default-cards.php');

// Create a card.
echo "Creating cards...\n";
foreach ($defaultCards as $card) {
    echo " - {$card->getName()}...\n";
    $response = $client->request('POST', '/api/card', [
        'headers' => $headers,
        'json' => $card->getJson(),
    ]);
    if ($code = $response->getStatusCode() != 200) {
        echo "Received an unexpected status code: $code!\n";
        exit();
    }
    $json = json_decode($response->getBody());
    $cardId = $json->id;
    echo "  - Done! Card ID: $cardId\n"; //URL: {$config['metabase']['baseUrl']}/admin/databases/{$databaseId}\n";
}

