<?php

use Hidehalo\Nanoid\Client as NanoClient;

abstract class MetabaseCard {
    /** @var $databaseId The numeric identifier of the Metabase database this card is associated with. */
    protected int $databaseId;

    public function __construct(int $databaseId) {
	$this->databaseId = $databaseId;
    }

    abstract function getName() : string;
    abstract function getDescription() : string;
    abstract function getDatabaseQuery() : object;

    function getJson() : object
    {
        $nanoClient = new NanoClient();
        return (object) [
            'collection_id' => null,
            'collection_position' => null,
            'dataset_query' => $this->getDatabaseQuery(),
            'name' => $this->getName(),
            'display' => 'table',
            'description' => $this->getDescription(),
            'parameters' => [
                (Object) [
                    'default' => '2023-01-01',
                    'id' => $nanoClient->generateId(21), // This is a unique ID for import/export purposes
                    'name' => 'Start Date',
                    'required' => true,
                    'slug' => 'start_date',
                    'target' => ['variable', ['template-tag', 'start_date']],
                    'type' => 'date/single',
                ],
                (Object) [
                    'default' => '2023-12-31',
                    'id' => $nanoClient->generateId(21), // This is a unique ID for import/export purposes
                    'name' => 'End Date',
                    'required' => true,
                    'slug' => 'end_date',
                    'target' => ['variable', ['template-tag', 'end_date']],
                    'type' => 'date/single',
                ],
            ],
            'result_metadata' => null,
            'visualization_settings' => (Object) [],
        ];
    }
}
