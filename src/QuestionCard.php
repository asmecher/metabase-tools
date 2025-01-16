<?php

use Hidehalo\Nanoid\Client as NanoClient;

require_once('src/MetabaseCard.php');

abstract class QuestionCard extends MetabaseCard {
    public function getFields() : array
    {
	return [];
    }

    public function getJoins() : array
    {
	return [];
    }

    public function getResultMetadata() : array
    {
	return [];
    }

    abstract public function getSourceTable() : int;

    public function getDatasetQuery() : object
    {
        $nanoClient = new NanoClient();
        return (object) [
            'database' => $this->databaseId,
            'query' => [
                'fields' => $this->getFields(),
		'joins' => $this->getJoins(),
		'source-table' => $this->getSourceTable(),
            ],
            'type' => 'query',
        ];
    }

    public function getJson() : object
    {
	$json = parent::getJson();
	$json->{'result_metadata'} = $this->getResultMetadata();
	return $json;
    }
}

