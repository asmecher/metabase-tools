<?php

/**
 * Create a database in Metabase with the supplied configuration.
 */

use GuzzleHttp\Client;
use Illuminate\Database\Capsule\Manager as Capsule;
use Hidehalo\Nanoid\Client as NanoClient;

require_once('vendor/autoload.php');
require_once('includes/functions.php');

// Load the configuration file
$config = require_once('config/config.php');

// Establish the database connection for metabase installation
$capsule = new Capsule;
$capsule->addConnection($config['databases']['metabase'], 'metabase');
$metabaseConnection = $capsule->getConnection('metabase');

$databaseId = getMetabaseDatabaseId($metabaseConnection, $config['journalPath']);
$nanoClient = new NanoClient();

// Get the set of default cards
$defaultCards = require_once('default-cards.php');

$client = new Client(['base_uri' => $config['metabase']['baseUrl']]);
$headers = ['x-api-key' => $config['metabase']['apiKey']];

// Create a card.
echo "Creating cards...\n";
foreach ($defaultCards as $card) {
    echo " - {$card['name']}...\n";
    $response = $client->request('POST', '/api/card', [
        'headers' => $headers,
        'json' => $card,
    ]);
    if ($code = $response->getStatusCode() != 200) {
        echo "Received an unexpected status code: $code!\n";
        exit();
    }
    $json = json_decode($response->getBody());
    $cardId = $json->id;
    echo "  - Done! Card ID: $cardId\n"; //URL: {$config['metabase']['baseUrl']}/admin/databases/{$databaseId}\n";
}

