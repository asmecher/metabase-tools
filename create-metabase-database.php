<?php

/**
 * Create a database in Metabase with the supplied configuration.
 */

use GuzzleHttp\Client;

require_once('vendor/autoload.php');

// Load the configuration file
$config = require_once('config.php');

$client = new Client(['base_uri' => $config['metabase']['baseUrl']]);

echo "Creating Metabase database for {$config['journalPath']}...\n";
$response = $client->request('POST', '/api/database', [
    'headers' => [
        'x-api-key' => $config['metabase']['apiKey'],
    ],
    'json' => [
	'engine' => 'mysql',
	'name' => $config['journalPath'],
	'details' => [
            'host' => $config['databases']['single']['host'],
            'db' => $config['databases']['single']['database'],
            'user' => $config['databases']['single']['username'],
            'password' => $config['databases']['single']['password'],
	],
    ],
]);
if ($code = $response->getStatusCode() != 200) {
    echo "Received an unexpected status code: $code!\n";
} else {
    echo "Done!\n";
    $json = json_decode($response->getBody());
    echo "URL: {$config['metabase']['baseUrl']}/admin/databases/{$json->id}\n";
}

