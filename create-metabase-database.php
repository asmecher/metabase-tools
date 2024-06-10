<?php

/**
 * Create a database in Metabase with the supplied configuration.
 */

use GuzzleHttp\Client;

require_once('vendor/autoload.php');

// Load the configuration file
$config = require_once('config.php');

$client = new Client(['base_uri' => $config['metabase']['baseUrl']]);
$headers = ['x-api-key' => $config['metabase']['apiKey']];

// Create the database in Metabase.
echo "Creating Metabase database for {$config['journalPath']}...\n";
$response = $client->request('POST', '/api/database', [
    'headers' => $headers,
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
    exit();
}
$json = json_decode($response->getBody());
$databaseId = $json->id;
echo "Done! URL: {$config['metabase']['baseUrl']}/admin/databases/{$databaseId}\n";

// Create the user group in Metabase.
echo "Creating group...\n";
$response = $client->request('POST', '/api/permissions/group', [
    'headers' => $headers,
    'json' => ['name' => $config['journalPath']],
]);
if ($code = $response->getStatusCode() != 200) {
    echo "Received an unexpected status code: $code!\n";
    exit();
}
$json = json_decode($response->getBody());
$groupId = $json->id;
echo "Done! URL: {$config['metabase']['baseUrl']}/admin/permissions/data/group/{$groupId}\n";

// Identify the 'Administrators' and 'All Users' group IDs, in order to correctly adjust permissions.
echo "Identifying groups...\n";
// Get the group list.
$response = $client->request('GET', '/api/permissions/group', ['headers' => $headers]);
if ($code = $response->getStatusCode() != 200) {
    echo "Received an unexpected status code: $code!\n";
    exit();
}
$groups = json_decode($response->getBody(), true); // as array
$adminGroupId = array_reduce($groups, fn($carry, $item) => $item['name'] == 'Administrators' ? $item['id'] : $carry);
if (!$adminGroupId) throw new Exception("Unable to identify 'Administrators' group!");
$allUsersGroupId = array_reduce($groups, fn($carry, $item) => $item['name'] == 'All Users' ? $item['id'] : $carry);
if (!$adminGroupId) throw new Exception("Unable to identify 'All Users' group!");

// Get the current permissions graph.
echo "Getting the All Users permission graph...\n";
$response = $client->request('GET', '/api/permissions/graph', ['headers' => $headers]);
if ($code = $response->getStatusCode() != 200) {
    echo "Received an unexpected status code: $code!\n";
    exit();
}
$graph = json_decode($response->getBody());

// Remove self-serve permissions for All Users
$graph->groups->$allUsersGroupId->$databaseId = (object) [
    'data' => [
        'schemas' => 'none',
        'native' => 'none',
    ],
];

// Grant permission for the new group to the new database
$graph->groups->$groupId = (object) [
    $databaseId => [
        'data' => [
            'schemas' => 'all',
            'native' => 'write',
	],
    ],
];

// Post the modified permissions back to Metabase.
echo "Posting modified permissions...\n";
$response = $client->request('PUT', '/api/permissions/graph', [
    'headers' => $headers,
    'json' => $graph,
]);
if ($code = $response->getStatusCode() != 200) {
    echo "Received an unexpected status code: $code!\n";
    exit();
}
echo "Done!\n";

