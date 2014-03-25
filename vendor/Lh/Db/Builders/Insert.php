<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Builders;

use Lh\Collections\Dictionary;
use Lh\Db\DbException;
use Lh\Db\IAdapter;

/**
 * Class Insert
 *
 * Represent INSERT INTO statement
 *
 * @package Lh\Db\Builders
 */
class Insert implements IInsert {
	/** @var \Lh\Db\IAdapter Used to retrieve platform object */
	protected $adapter;
	/** @var \Lh\Db\IPlatform Used while compiling query */
	protected $platform;
	/** @var string Target table name */
	protected $tableName;
	/** @var string[] Field(s) of target table */
	protected $fields = array();
	/** @var string[] Value(s) to be inserted */
	protected $values = array();
	/** @var null|ISelect Data source used for insert statement. */
	protected $selectTable = null;

	/**
	 * Create new instance of Insert
	 *
	 * @param string   $tableName
	 * @param IAdapter $adapter
	 */
	public function __construct($tableName = null, IAdapter $adapter = null) {
		if ($tableName !== null) {
			$this->into($tableName);
		}
		if ($adapter !== null) {
			$this->setAdapter($adapter);
		}
	}

	/**
	 * Set Adapter and their Platform
	 *
	 * @param IAdapter $adapter
	 *
	 * @return void
	 */
	public function setAdapter(IAdapter $adapter) {
		$this->adapter = $adapter;
		$this->platform = $adapter->getPlatform();
	}

	/**
	 * Get associated adapter
	 *
	 * @return IAdapter
	 */
	public function getAdapter() {
		return $this->adapter;
	}

	/**
	 * Compile SQL Query
	 *
	 * Compile SQL query as string without parameter. Any value will be compiled directly in the resulting query.
	 *
	 * @return string
	 * @throws \Lh\Db\DbException
	 */
	public function compile() {
		if ($this->adapter === null) {
			throw new DbException("Compiling SQL query require adapter support. No adapter have been specified.");
		}
		if (count($this->fields) == 0) {
			throw new DbException("There is no fields in INSERT statement");
		}

		return $this->compileWithParameters(null, false);
	}

	/**
	 * Compile SQL Query
	 *
	 * Compile SQL query using parameter helper. Resulting query will have parameter place holder and the parameter will stored at $parameterContainer.
	 *
	 * @see IStatement::execute()
	 *
	 * @param Dictionary $parameterContainer
	 * @param bool       $resetContainer
	 *
	 * @throws \Lh\Db\DbException
	 * @return string
	 */
	public function compileWithParameters(Dictionary $parameterContainer = null, $resetContainer = true) {
		if ($this->adapter === null) {
			throw new DbException("Compiling SQL query require adapter support. No adapter have been specified.");
		}
		if (count($this->fields) == 0) {
			throw new DbException("There is no fields in INSERT statement");
		}

		if ($resetContainer && $parameterContainer !== null) {
			$parameterContainer->clear();
		}
		$useParameter = ($parameterContainer !== null);

		$fragments = array();
		$fragments[] = "INSERT INTO " . $this->platform->quoteIdentifier($this->tableName);

		$buff = array();
		foreach ($this->fields as $field) {
			$buff[] = $this->platform->quoteIdentifier($field);
		}
		$fragments[] = "(" . implode(", ", $buff) . ")";
		unset($buff, $field);

		if ($this->selectTable !== null) {
			$fragments[] = $this->selectTable->compileWithParameters($parameterContainer, false);
		} else {
			$buff = array();
			foreach ($this->values as $value) {
				if ($useParameter) {
					$parameterName = "insert" . ($parameterContainer->count() + 1);
					$parameterContainer->set($parameterName, $value);
					$buff[] = $this->platform->formatParameterName($parameterName);
				} else {
					$buff[] = is_null($value) ? "NULL" : $this->platform->quoteValue($value);
				}
			}
			$fragments[] = "VALUES (" . implode(", ", $buff) . ")";
			unset($buff, $value, $parameterName);
		}

		return implode(" ", $fragments);
	}


	/**
	 * Target table name
	 *
	 * @param string $tableName
	 *
	 * @return IInsert
	 */
	public function into($tableName) {
		$this->tableName = $tableName;

		return $this;
	}

	/**
	 * Bulk set field(s) and their value(s)
	 *
	 * Bulk set for field(s) and their value(s) using array format. Array should be key value pair, the key will be used as field name.
	 * IMPORTANT: This will always reset previous set unlike SELECT which able to preserve previous field(s)
	 *
	 * @param array $values
	 *
	 * @throws \InvalidArgumentException
	 * @return IInsert
	 */
	public function values($values) {
		if (!is_array($values)) {
			throw new \InvalidArgumentException("Values must be an array of their field and their value");
		}
		$this->fields = array();
		$this->values = array();
		$this->selectTable = null;
		foreach ($values as $field => $value) {
			if (!is_string($field)) {
				throw new \InvalidArgumentException("One of the field name is not a string. Array for values must be string => string");
			}
			$this->fields[] = trim($field);
			$this->values[] = $value;
		}

		return $this;
	}

	/**
	 * Add a field and value to be inserted
	 *
	 * @param string     $field
	 * @param string|int $value
	 *
	 * @throws \InvalidArgumentException
	 * @return IInsert
	 */
	public function value($field, $value) {
		if (empty($field)) {
			throw new \InvalidArgumentException("Field must not empty.");
		}

		$this->fields[] = trim($field);
		$this->values[] = $value;

		return $this;
	}

	/**
	 * Generate INSERT INTO using SELECT statement
	 *
	 * This method tell that data source will be use another SELECT statement. Therefore any values from values() and value() will be ignored.
	 * IMPORTANT: The select statement should have same number of field with the $fields parameter
	 *
	 * @param string[] $fields
	 * @param ISelect  $select
	 *
	 * @return IInsert
	 * @throws \Lh\Db\DbException
	 * @throws \InvalidArgumentException
	 */
	public function fromTable($fields, ISelect $select) {
		if (!is_array($fields)) {
			throw new \InvalidArgumentException("Field(s) must me an array of string[]");
		}
		if (count($fields) != $select->columnCount()) {
			throw new DbException("Total insert field and total select field doesn't match.");
		}
		$this->fields = array();
		$this->values = array();
		$this->selectTable = $select;
		foreach ($fields as $field) {
			$this->fields[] = trim($field);
		}

		return $this;
	}
}

// End of File: Insert.php 