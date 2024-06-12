<?php

/**
 * Configure a list of default cards to be created using the create-default-cards.php script.
 */
return [
    [
        'collection_id' => null,
        'collection_position' => null,
        'dataset_query' => [
            'database' => $databaseId,
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
        ],
        'name' => 'New Query Name',
        'display' => 'table',
        'description' => 'New Query Description',
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
    ],
];
