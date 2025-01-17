<?php

/**
 * Configure a list of default cards to be created using the create-default-cards.php script.
 */
use Hidehalo\Nanoid\Client as NanoClient;
$nanoClient = new NanoClient();

require_once('config/cards/ExampleSqlCard.php');
require_once('config/cards/ExampleQuestionCard.php');

return [
    new ExampleSqlCard($databaseId),
    new ExampleQuestionCard($databaseId, $schema),
];
