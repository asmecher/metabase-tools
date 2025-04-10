<?php

function getMetabaseDatabaseId_API($client, $journalPath) {
    $response = $client->request('GET', "/api/database");
    if ($code = $response->getStatusCode() != 200) {
        throw new \Exception("Received an unexpected status code: $code!\n");
    }
    $databasesRaw = (string) $response->getBody();
    $databases = json_decode($databasesRaw);
    foreach ($databases->data as $database) {
	if ($database->name === $journalPath) return $database->id;
    }
    throw new \Exception("Unable to find database ID for \"$journalPath\"!");
}

function getMetabaseDatabaseId_DB($dbConnection, $journalPath) {
    $result = $dbConnection
        ->table('metabase_database')
        ->where('details', 'LIKE', '%"dbname":"' . $journalPath . '"%')
        ->orWhere('details', 'LIKE', '%"db":"' . $journalPath . '"%')
        ->pluck('id');
    if ($result->count() !== 1) throw new Exception("Could not find a Metabase database for path {$journalPath}!");
    return $result->first();
}
