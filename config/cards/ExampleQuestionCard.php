<?php

use Hidehalo\Nanoid\Client as NanoClient;

require_once('src/QuestionCard.php');

class ExampleQuestionCard extends QuestionCard {
    public function getName() : string
    {
        return 'Example Question Card';
    }
    public function getDescription() : string
    {
	return 'Example card description';
    }

    public function getSourceTable() : int
    {
	return 330;
    }

    public function getFields() : array
    {
	return [
	    ['field', 2455, (object) ['base-type' => 'type/BigInteger']],
	];
    }

    public function getJoins() : array
    {
	return [
	    (object) [
		'alias' => 'Publications - Current Publication',
		'condition' => [
		    '=',
		    ['field', 2449, (object) ['base-type' => 'type/BigInteger']],
		    ['field', 2399, (object) ['base-type' => 'type/BigInteger', 'join-alias' => 'Publications - Current Publication']],
		],
		'fields' => [
		    ['field', 2399, (object) ['base-type' => 'type/BigInteger', 'join-alias' => 'Publications - Current Publication']],
		],
		'source-table' => 338,
		'strategy' => 'left-join',
	    ],

	];
    }

    public function getResultMetadata() : array
    {
	return [
	    (object) [
		'base_type' => 'type/BigInteger',
		'coercion_strategy' => null,
		'description' => null,
		'display_name' => 'Submission ID',
		'effective_type' => 'type/BigInteger',
		'field_ref' => ['field', 2455, ['base-type' => 'type/BigInteger']],
		'fingerprint' => (object) [
		    'global' => (object) ['distinct-count' => 10000, 'nil%' => 0],
		    'type' => (object) [
			'type/Number' => (object) [
			    'avg' => 0,
			    'max' => 100,
			    'min' => -100,
			    'q1' => 12345,
			    'q3' => 123,
			    'sd' => 45,
			],
		    ],
		],
		'fk_target_field_id' => null,
		'id' => 2455,
	        'name' => 'submission_id',
		'semantic_type' => 'type/PK',
		'settings' => null,
		'visibility_type' => 'normal',
	    ],
	    (object) [
		'base_type' => 'type/BigInteger',
		'coercion_strategy' => null,
		'description' => null,
		'display_name' => 'Publications - Current Publication -> Publication ID',
		'effective_type' => 'type/BigInteger',
		'field_ref' => ['field', 2399, ['base-type' => 'type/BigInteger', 'join-alias' => 'Publications - Current Publication']],
		'fingerprint' => (object) [
		    'global' => (object) ['distinct-count' => 10000, 'nil%' => 0],
		    'type' => (object) [
			'type/Number' => (object) [
			    'avg' => 0,
			    'max' => 100,
			    'min' => -100,
			    'q1' => 12345,
			    'q3' => 123,
			    'sd' => 45,
			],
		    ],
		],
		'fk_target_field_id' => null,
		'id' => 2399,
	        'name' => 'publication_id',
		'semantic_type' => 'type/PK',
		'settings' => null,
		'visibility_type' => 'normal',
	    ],
	];
    }

}

