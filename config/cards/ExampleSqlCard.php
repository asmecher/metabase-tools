<?php

use Hidehalo\Nanoid\Client as NanoClient;

require_once('src/MetabaseCard.php');

class ExampleSqlCard extends MetabaseCard {
    public function getName() : string
    {
        return 'Example SQL Card';
    }
    public function getDescription() : string
    {
	return 'Example card description';
    }
    public function getDatabaseQuery() : object
    {
        $nanoClient = new NanoClient();
        return (object) [
            'database' => $this->databaseId,
            'native' => [
                'query' => '
                    SELECT DATE(ed.date_decided), COUNT(ed.edit_decision_id)
                    FROM
                        edit_decisions ed
                        LEFT JOIN edit_decisions AS ed_nonexist ON (
                            ed.edit_decision_id < ed_nonexist.edit_decision_id
                            AND ed.submission_id = ed_nonexist.submission_id
                        )
                    WHERE
                        ed.decision IN (2, 7, 29)
                        AND ed_nonexist.edit_decision_id IS NULL
                        AND ed.date_decided >= {{start_date}}
                        AND ed.date_decided <= {{end_date}}
                    GROUP BY DATE(ed.date_decided)',
                'template-tags' => [
                    'start_date' => [
                        'default' => '2023-01-01',
                        'dimension' => null,
                        'id' => $nanoClient->generateId(21), // This is a unique ID for import/export purposes
                        'display-name' => 'Start Date',
                        'name' => 'start_date',
                        'required' => true,
                        'type' => 'date',
                        'widget-type' => null,
                    ],
                    'end_date' => [
                        'default' => '2023-12-31',
                        'dimension' => null,
                        'id' => $nanoClient->generateId(21), // This is a unique ID for import/export purposes
                        'display-name' => 'End Date',
                        'name' => 'end_date',
                        'required' => true,
                        'type' => 'date',
                        'widget-type' => null,
                    ],
                ],
            ],
            'type' => 'native',
        ];
    }
}

