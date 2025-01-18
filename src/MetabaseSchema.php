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

    public function getTableName(int $tableId) : string
    {
	if (!isset($this->tableIdsToNames[$tableId])) throw new \Exception("Unknown table ID $tableId!");
	return $this->tableIdsToNames[$tableId];
    }

    public function getFieldName(int $fieldId) : string
    {
	if (!isset($this->fieldIdsToNames[$fieldId])) throw new \Exception("Unknown field ID $fieldId!");
	return $this->fieldIdsToNames[$fieldId];
    }

    public function getFieldId(string $tableName, string $fieldName) : int
    {
	$tableId = $this->getTableId($tableName);
	$fieldId = array_search($fieldName, $this->tableFields[$tableId]);
	if ($fieldId === false) throw new \Exception("Unable to locate field '{$fieldName}' in table '{$tableName}'!");
	return $fieldId;
    }

    public function getFieldTableId(int $fieldId) : int
    {
	foreach ($this->tableFields as $tableId => $fields) {
	    if (isset($fields[$fieldId])) return $tableId;
	}
	throw new \Exception("Unable to find a table for field ID $fieldId.");
    }

    public function getFieldTableName(int $fieldId) : string
    {
	return $this->getTableName($this->getFieldTableId($fieldId));
    }

    public function getFieldFingerprint(int $fieldId) : object
    {
	return $this->fieldIdsToFingerprints[$fieldId];
    }
}
