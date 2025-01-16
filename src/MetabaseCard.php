<?php

abstract class MetabaseCard {
    /** @var $databaseId The numeric identifier of the Metabase database this card is associated with. */
    protected int $databaseId;

    public function __construct(int $databaseId) {
	$this->databaseId = $databaseId;
    }

    abstract function getName() : string;
    abstract function getDescription() : string;
    abstract function getJson() : array;
}
