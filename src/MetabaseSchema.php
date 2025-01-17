<?php

class MetabaseSchema {
    public array $tableIdsToNames = [];
    public array $fieldIdsToNames = [];
    public array $tableFields = [];
    public array $fieldIdsToFingerprints = [];

    public function __construct(public object $schema)
    {
	foreach ($schema->tables as $table) {
	    $this->tableIdsToNames[$table->id] = $table->name;
	    foreach ($table->fields as $field) {
		$this->fieldIdsToNames[$field->id] = $field->name;
		$this->fieldIdsToFingerprints[$field->id] = $field->fingerprint;
		$this->tableFields[$table->id][$field->id] = $field->name;
	    }
	}
    }

    public function getTableId(string $tableName) : int
    {
	$tableId = array_search($tableName, $this->tableIdsToNames);
	if ($tableId === false) throw new \Exception("Unable to locate table '{$tableName}'!");
	return $tableId;
    }

    public function getFieldId(string $tableName, string $fieldName) : int
    {
	$tableId = $this->getTableId($tableName);
	$fieldId = array_search($fieldName, $this->tableFields[$tableId]);
	if ($fieldId === false) throw new \Exception("Unable to locate field '{$fieldName}' in table '{$tableName}'!");
	return $fieldId;
    }

    public function getFieldFingerprint(int $fieldId) : object
    {
	return $this->fieldIdsToFingerprints[$fieldId];
    }
}
