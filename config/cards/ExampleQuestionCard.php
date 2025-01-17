<?php

use Hidehalo\Nanoid\Client as NanoClient;

require_once('src/QuestionCard.php');
require_once('src/MetabaseSchema.php');

class ExampleQuestionCard extends QuestionCard {
    public int $submissionIdFieldId;
    public int $publicationIdFieldId;

    public function __construct(int $databaseId, public MetabaseSchema $schema) {
	parent::__construct($databaseId);
	$this->submissionIdFieldId = $this->schema->getFieldId('submissions', 'submission_id');
	$this->publicationIdFieldId = $this->schema->getFieldId('publications', 'publication_id');
    }

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
	return $this->schema->getTableId('submissions');
    }

    public function getFields() : array
    {
	return [
	    ['field', $this->submissionIdFieldId, (object) ['base-type' => 'type/BigInteger']],
	];
    }

    public function getJoins() : array
    {
	return [
	    (object) [
		'alias' => 'Publications - Current Publication',
		'condition' => [
		    '=',
		    ['field', $this->schema->getFieldId('submissions', 'current_publication_id'), (object) ['base-type' => 'type/BigInteger']],
		    ['field', $this->publicationIdFieldId, (object) ['base-type' => 'type/BigInteger', 'join-alias' => 'Publications - Current Publication']],
		],
		'fields' => [
		    ['field', $this->publicationIdFieldId, (object) ['base-type' => 'type/BigInteger', 'join-alias' => 'Publications - Current Publication']],
		],
		'source-table' => $this->schema->getTableId('publications'),
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
		'field_ref' => ['field', $this->submissionIdFieldId, ['base-type' => 'type/BigInteger']],
		'fingerprint' => $this->schema->getFieldFingerprint($this->submissionIdFieldId),
		'fk_target_field_id' => null,
		'id' => $this->submissionIdFieldId,
	        'name' => 'submission_id',
		'semantic_type' => 'type/PK',
		'settings' => null,
		'visibility_type' => 'normal',
	    ],
	    (object) [
		'base_type' => 'type/BigInteger',
		'coercion_strategy' => null,
		'description' => null,
		'display_name' => 'Current Publication ID',
		'effective_type' => 'type/BigInteger',
		'field_ref' => ['field', $this->publicationIdFieldId, ['base-type' => 'type/BigInteger', 'join-alias' => 'Publications - Current Publication']],
		'fingerprint' => $this->schema->getFieldFingerprint($this->publicationIdFieldId),
		'fk_target_field_id' => null,
		'id' => $this->publicationIdFieldId,
	        'name' => 'publication_id',
		'semantic_type' => 'type/PK',
		'settings' => null,
		'visibility_type' => 'normal',
	    ],
	];
    }

    public function getVisualizationSettings() : object {
	return (object) [
	    'column_settings' => (object) [
		'["name", "publication_id"]' => (object) [
		    'column_title' => 'Current Publication ID',
		],
	    ]
	];
    }
}

