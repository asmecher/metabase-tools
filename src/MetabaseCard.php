<?php

use Hidehalo\Nanoid\Client as NanoClient;

abstract class MetabaseCard {
    /** @var $databaseId The numeric identifier of the Metabase database this card is associated with. */
    protected int $databaseId;

    /**
     * Construct a metabase card with the specified database ID.
     */
    public function __construct(int $databaseId) {
	$this->databaseId = $databaseId;
    }

    /**
     * value must be a non-blank string.
     */
    abstract public function getName() : string;

    /**
     * nullable value must be a non-blank string.
     */
    abstract public function getDescription() : string;

    abstract public function getDatasetQuery() : object;

    /**
     * nullable value must be an integer greater than zero.
     */
    public function getCollectionId() : ?int
    {
	return null;
    }

    /**
     * nullable value must be an integer greater than zero.
     */
    public function getCollectionPosition() : ?int
    {
	return null;
    }

    /**
     * value must be a non-blank string.
     */
    public function getDisplay() : string {
	return 'table';
    }

    /**
     * Value must be a map.
     */
    public function getVisualizationSettings() : object {
	return (object) [];
    }

    /**
     * nullable value must be an array of valid results column metadata maps.
     */
    public function getResultMetadata() : ?array {
	return null;
    }

    /**
     * Get the JSON object representing this Metabase card.
     */
    function getJson() : object
    {
        $nanoClient = new NanoClient();
        return (object) [
            'collection_id' => $this->getCollectionId(),
            'collection_position' => $this->getCollectionPosition(),
            'dataset_query' => $this->getDatasetQuery(),
            'name' => $this->getName(),
            'display' => $this->getDisplay(),
            'description' => $this->getDescription(),
            'result_metadata' => $this->getResultMetadata(),
            'visualization_settings' => $this->getVisualizationSettings(),
        ];
    }
}
