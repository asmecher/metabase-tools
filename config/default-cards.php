<?php

/**
 * Configure a list of default cards to be created using the create-default-cards.php script.
 */
use Hidehalo\Nanoid\Client as NanoClient;
$nanoClient = new NanoClient();

require_once('config/cards/ExampleSqlCard.php');

return [
    new ExampleSqlCard($databaseId),
];
