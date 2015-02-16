<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Scaffolding;

use Lh\Exceptions\InvalidStateException;
use Lh\IExchangeable;
use Serializable as ISerializable;

/**
 * Class AbstractRow
 *
 * Minimal properties and methods which a table row should have. This row will be used by AbstractTable class
 *
 * @see AbstractTable
 *
 * @package Lh\Db\Scaffolding
 */
abstract class AbstractRow implements IExchangeable, ISerializable {
	/** @var string Table name which this row came from */
	protected $tableName;
	/** @var string[] Column(s) name */
	protected $columns = array();
	/** @var array Pair between column name and its value */
	protected $values = array();

	/**
	 * @param string $tableName
	 */
	public function __construct($tableName) {
		$this->tableName = $tableName;
	}

	/**
	 * Set column(s) name from current row
	 *
	 * Setting row column(s) will automatically fill their values with NULL value for each column.
	 *
	 * @param string[] $columns
	 *
	 * @throws \Lh\Exceptions\InvalidStateException
	 */
	public function setColumns($columns) {
		if (count($this->columns) > 0) {
			throw new InvalidStateException("Unable to set columns when column(s) already defined");
		}

		$this->columns = $columns;
		$this->values = array();
		foreach ($columns as $column) {
			$this->values[$column] = null;
		}
	}

	/**
	 * Get column(s) name from current row
	 *
	 * @return string[]
	 */
	public function getColumns() {
		return $this->columns;
	}

	/**
	 * Set current instance properties from given array.
	 *
	 * Auto fill current row properties with their value from given array. Be warned that row columns must be defined first prior calling this method.
	 * Be note that unknown column from array parameter will be ignored and missing column value will be treated as NULL.
	 *
	 * @param array $values
	 *
	 * @throws \Lh\Exceptions\InvalidStateException
	 * @return void
	 */
	public function exchangeArray(array $values) {
		if (count($this->columns) == 0) {
			throw new InvalidStateException("Unable to exchange array! Columns definition not loaded yet.");
		}

		$this->values = array();
		foreach ($this->columns as $columnName) {
			$this->values[$columnName] = array_key_exists($columnName, $values) ? $values[$columnName] : null;
		}
	}

	/**
	 * Return current row field values
	 *
	 * Returned array should be compatible with exchangeArray()
	 *
	 * @return array
	 */
	public function toArray() {
		return $this->values;
	}
}

// End of File: AbstractRow.php 