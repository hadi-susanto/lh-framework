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
 * Class Update
 *
 * @package Lh\Db\Builders
 */
class Update implements IUpdate {
	/** @var \Lh\Db\IAdapter Used to retrieve platform object. */
	protected $adapter;
	/** @var \Lh\Db\IPlatform Used while compiling query */
	protected $platform;
	/** @var string Table name */
	protected $tableName;
	/** @var string[] Field names which will be updated */
	protected $fields = array();
	/** @var array New values for each field */
	protected $values = array();
	/** @var Where[] Where clauses */
	protected $wheres = array();

	/**
	 * Create Update Statement
	 *
	 * @param string   $tableName
	 * @param IAdapter $adapter
	 */
	public function __construct($tableName = null, IAdapter $adapter = null) {
		if ($tableName !== null) {
			$this->from($tableName);
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
	 * Compile SQL query as string without parameter. Any value will be compiled directly in the resulting query
	 *
	 * @throws \Lh\Db\DbException
	 * @return string
	 */
	public function compile() {
		if ($this->adapter === null) {
			throw new DbException("Compiling SQL query require adapter support. No adapter have been specified.");
		}
		if (empty($this->tableName)) {
			throw new DbException("There is no table in UPDATE statement.");
		}
		if (count($this->fields) == 0) {
			throw new DbException("There is no fields in UPDATE statement");
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
		if (empty($this->tableName)) {
			throw new DbException("There is no table in UPDATE statement.");
		}
		if (count($this->fields) == 0) {
			throw new DbException("There is no fields in UPDATE statement");
		}

		if ($resetContainer && $parameterContainer !== null) {
			$parameterContainer->clear();
		}
		$useParameter = ($parameterContainer !== null);

		$fragments = array();
		$fragments[] = "UPDATE " . $this->platform->quoteIdentifier($this->tableName);

		for ($i = 0; $i < count($this->fields); $i++) {
			$prefix = ($i == 0) ? "SET " : ", ";

			if ($this->values[$i] instanceof ILiteral) {
				$value = $this->values[$i]->toString($parameterContainer);
			} else {
				if ($useParameter) {
					$parameterName = "update" . ($parameterContainer->count() + 1);
					$parameterContainer->set($parameterName, $this->values[$i]);
					$value = $this->platform->formatParameterName($parameterName);
				} else {
					$value = is_null($this->values[$i]) ? "NULL" : $this->platform->quoteValue($this->values[$i]);
				}
			}

			$fragments[] = $prefix . $this->platform->quoteIdentifierList($this->fields[$i]) . " = " . $value;
		}
		unset($value);

		if (count($this->wheres) > 0) {
			// First where will be always appended
			$fragments[] = "WHERE " . $this->wheres[0]->toString($parameterContainer);
			$max = count($this->wheres);
			for ($i = 1; $i < $max; $i++) {
				$fragments[] = "AND " . $this->wheres[$i]->toString($parameterContainer);
			}
			unset($max, $i);
		}

		return implode(" ", $fragments);
	}

	/**
	 * Set table name which UPDATE statement executed
	 *
	 * @param string $tableName
	 *
	 * @return IUpdate
	 */
	public function from($tableName) {
		$this->tableName = $tableName;

		return $this;
	}

	/**
	 * Bulk set in UPDATE statement
	 *
	 * Bulk set always remove any previous sets
	 *
	 * @param array $sets
	 *
	 * @throws \InvalidArgumentException
	 * @return IUpdate
	 */
	public function sets($sets) {
		if (!is_array($sets)) {
			throw new \InvalidArgumentException("Values must be an array of their field and their value");
		}
		$this->fields = array();
		$this->values = array();
		foreach ($sets as $field => $value) {
			if (!is_string($field)) {
				throw new \InvalidArgumentException("One of the field name is not a string. Array for values must be string => string");
			}
			$this->fields[] = trim($field);
			$this->values[] = $value;
		}

		return $this;
	}

	/**
	 * Add a SET column_name = value clause to the UPDATE statement
	 *
	 * @param string     $field
	 * @param string|int $value
	 *
	 * @throws \InvalidArgumentException
	 * @return IUpdate
	 */
	public function set($field, $value) {
		if (empty($field)) {
			throw new \InvalidArgumentException("Field must not empty.");
		}

		$this->fields[] = trim($field);
		$this->values[] = $value;

		return $this;
	}

	/**
	 * Add a WHERE clause to the UPDATE statement
	 *
	 * @param string                        $field
	 * @param null|string|int|array|ISelect $value
	 * @param string                        $operator
	 *
	 * @throws \InvalidArgumentException
	 * @return IUpdate
	 */
	public function where($field, $value, $operator = '=') {
		if (empty($field)) {
			throw new \InvalidArgumentException("Field can't be empty for WHERE clause");
		}
		$this->wheres[] = new Where($this->platform, $field, $value, $operator);

		// Allow chaining
		return $this;
	}
}

// End of File: Update.php 