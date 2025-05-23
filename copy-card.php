<?php

/**
 * Copy a Metabase card.
 */

use GuzzleHttp\Client;

require_once('vendor/autoload.php');
require_once('includes/functions.php');
require_once('src/MetabaseSchema.php');

// Load the configuration file
$config = require_once('config/config.php');

$stderr = fopen('php://stderr', 'w');

$headers = ['x-api-key' => $config['metabase']['apiKey']];
$client = new Client(['base_uri' => $config['metabase']['baseUrl'], 'headers' => $headers]);

$databaseId = getMetabaseDatabaseId_API($client, $config['journalPath']);

$sourceCardId = intval($argv[1]) ?? null;
if (!$sourceCardId) throw new \Exception('Please specify the source card ID on the command line.');

$targetCollectionId = intval($argv[2]) ?? null;
if (!$targetCollectionId) throw new \Exception('Please specify the target collection ID on the command line.');

fputs($stderr, "Getting card $sourceCardId... ");
$response = $client->request('GET', '/api/card/' . $sourceCardId);
if ($code = $response->getStatusCode() != 200) {
    throw new \Exception("Received an unexpected status code: $code!\n");
}
$cardRaw = (string) $response->getBody();
$card = json_decode($cardRaw);
fputs($stderr, "Done.\n");

fputs($stderr, "Get source database schema descriptor... ");
$response = $client->request('GET', '/api/database/' . $card->{'database_id'} . '?include=tables.fields');
if ($code = $response->getStatusCode() != 200) {
    throw new \Exception("Received an unexpected status code: $code!\n");
}
$sourceSchema = new MetabaseSchema(json_decode($response->getBody()));
fputs($stderr, "Done.\n");

fputs($stderr, "Get target database schema descriptor... ");
$response = $client->request('GET', '/api/database/' . $databaseId . '?include=tables.fields');
if ($code = $response->getStatusCode() != 200) {
    throw new \Exception("Received an unexpected status code: $code!\n");
}
$targetSchema = new MetabaseSchema(json_decode($response->getBody()));
fputs($stderr, "Done.\n");

// Process the card to remove/adapt content using strings tools to avoid having to code all nesting possibilities
$cardRaw = preg_replace_callback('/("database":)\d+/', fn($matches) => $matches[1] . $databaseId, $cardRaw);
$cardRaw = preg_replace_callback('/("database_id":)\d+/', fn($matches) => $matches[1] . $databaseId, $cardRaw);
$cardRaw = preg_replace_callback('/("table_id":)(\d+)/', fn($matches) => $matches[1] . $targetSchema->getTableId($sourceSchema->getTableName($matches[2])), $cardRaw);
$cardRaw = preg_replace_callback('/("source-table":)(\d+)/', fn($matches) => $matches[1] . $targetSchema->getTableId($sourceSchema->getTableName($matches[2])), $cardRaw);
$cardRaw = preg_replace_callback('/(\["field",)(\d+),/', function($matches) use ($sourceSchema, $targetSchema) {
    $sourceFieldId = $matches[2];
    $sourceTableName = $sourceSchema->getFieldTableName($sourceFieldId);
    return $matches[1] . $targetSchema->getFieldId($sourceTableName, $sourceSchema->getFieldName($sourceFieldId)) . ',';
}, $cardRaw);
$cardRaw = preg_replace_callback('/(\"fk_target_field_id":)(\d+)/', function($matches) use ($sourceSchema, $targetSchema) {
    $sourceFieldId = $matches[2];
    $sourceTableName = $sourceSchema->getFieldTableName($sourceFieldId);
    return $matches[1] . $targetSchema->getFieldId($sourceTableName, $sourceSchema->getFieldName($sourceFieldId));
}, $cardRaw);

// Using the decoded JSON, process more content a little more delicately
$card = json_decode($cardRaw);
foreach ($card->{'result_metadata'} as &$entry) {
    if (isset($entry->id)) {
	$sourceTableName = $sourceSchema->getFieldTableName($entry->id);
	$entry->id = $targetSchema->getFieldId($sourceTableName, $sourceSchema->getFieldName($entry->id));
    }
}
unset($card->id);
$card->collection_id = $targetCollectionId;

fputs($stderr, "Adding the modified card... ");
$response = $client->request('POST', '/api/card', ['json' => $card]);
if ($code = $response->getStatusCode() != 200) {
    throw new \Exception("Received an unexpected status code: $code!\n");
}
$json = json_decode($response->getBody());
$cardId = $json->id;
fputs($stderr, "Done! Card ID $cardId.\n");
