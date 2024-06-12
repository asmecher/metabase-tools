<?php

function getMetabaseDatabaseId($dbConnection, $journalPath) {
    $result = $dbConnection
        ->table('metabase_database')
        ->where('details', 'LIKE', '%"dbname":"' . $journalPath . '"%')
        ->orWhere('details', 'LIKE', '%"db":"' . $journalPath . '"%')
        ->pluck('id');
    if ($result->count() !== 1) throw new Exception("Could not find a Metabase database for path {$journalPath}!");
    return $result->first();
}
