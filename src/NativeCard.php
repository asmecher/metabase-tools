<?php

use Hidehalo\Nanoid\Client as NanoClient;

require_once('src/MetabaseCard.php');

abstract class NativeCard extends MetabaseCard {
    abstract public function getQuery() : string;

    /**
     * nullable sequence of parameter must be a map with :id and :type keys.
     */
    public function getParameters() : array
    {
	return [];
    }

    /**
     * (undocumented Metabase API component?)
     */
    public function getTemplateTags() : array
    {
	return [];
    }

    public function getDatasetQuery() : object
    {
        $nanoClient = new NanoClient();
        return (object) [
            'database' => $this->databaseId,
            'native' => [
                'query' => $this->getQuery(),
		'template-tags' => $this->getTemplateTags(),
            ],
            'type' => 'native',
        ];
    }

    public function getJson() : object
    {
	$json = parent::getJson();
	$json->parameters = $this->getParameters();
	return $json;
    }
}

