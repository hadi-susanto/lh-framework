<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Scaffolding;

use Lh\Db\Builders\IFactory;
use Lh\Db\DbException;
use Lh\Db\IAdapter;
use Lh\Db\IQuery;
use Lh\IExchangeable;

/**
 * Class AbstractTable
 * @package Lh\Db\Scaffolding
 */
abstract class AbstractTable {
	/** @var string Table name */
	protected $tableName;
	/** @var IAdapter Adapter used to communicate with database backend */
	protected $adapter;
	/** @var IFactory Factory used for scaffolding */
	protected $factory;
	/** @var IExchangeable */
	protected $prototype;
	/** @var GenericRow Default prototype if user don't supply one */
	private $genericPrototype;

	/**
	 * Create a table representation
	 *
	 * @param string   $tableName
	 * @param IAdapter $adapter
	 */
	public function __construct($tableName, IAdapter $adapter) {
		$this->tableName = $tableName;
		$this->adapter = $adapter;
		$this->factory = $adapter->getBuilderFactory();
		$this->genericPrototype = new GenericRow($tableName);
	}

	/**
	 * Get adapter
	 *
	 * @return \Lh\Db\IAdapter
	 */
	public function getAdapter() {
		return $this->adapter;
	}

	/**
	 * Set row prototype
	 *
	 * This row prototype will be used for each row(s) retrieved from current table. If no prototype defined then a generic row will be used.
	 *
	 * @param \Lh\IExchangeable $prototype
	 */
	public function setPrototype(IExchangeable $prototype) {
		$this->prototype = $prototype;
	}

	/**
	 * Get row prototype
	 *
	 * Get current row prototype associated with current table
	 *
	 * @return \Lh\IExchangeable
	 */
	public function getPrototype() {
		return $this->prototype;
	}

	/**
	 * Get table name
	 *
	 * @return string
	 */
	public function getTableName() {
		return $this->tableName;
	}

	/**
	 * Check whether our generic row have columns definition or not
	 *
	 * This method will check whether generic row already have column. Generic row must have column(s) definition before they can be used in operation. Every magic
	 * methods inside relies on columns definition.
	 *
	 * @throws \Lh\Db\DbException
	 */
	private function checkGenericColumns() {
		if (count($this->genericPrototype->getColumns()) == 0) {
			$this->genericPrototype->setColumns($this->adapter->getColumnNames($this->tableName));

			// Make sure that columns definition loaded
			if (count($this->genericPrototype->getColumns()) == 0) {
				throw new DbException("Unable to  determine column(s) name from table: '" . $this->tableName . "'.");
			}
		}
	}

	/**
	 * Return shallow copy of associated row
	 *
	 * This method will return a (shallow) copy of current row. It will use clone method to create a copy. Be note that GenericRow instance also IExchangeable instance.
	 * NOTE: Type casting is required to enable further assistance.
	 *
	 * @return IExchangeable
	 */
	public function createRow() {
		if ($this->prototype instanceof IExchangeable) {
			return clone($this->prototype);
		} else {
			$this->checkGenericColumns();

			return clone($this->genericPrototype);
		}
	}

	/**
	 * Fetch row(s) from current table
	 *
	 * Fetch row(s) from current table based on given where clause. Where clause must be an array with key value pair which key will be used as field and value
	 * will be used as where value. If you want to use different operator then specify them in $operators
	 *
	 * @param array                $wheres    Key value pair where key used as field and value used as where value
	 * @param array                $operators Key value pair where key used as field and value used as operator
	 * @param null|string|string[] $orderBy   Used to tell how data to be sorted
	 * @param int                  $limit     Limit result set
	 * @param int                  $offset    Result set offset
	 *
	 * @return GenericRow[]|array|null
	 */
	public function selectRows($wheres = array(), $operators = array(), $orderBy = null, $limit = 0, $offset = 0) {
		$select = $this->factory->select("*")
			->from($this->tableName);

		foreach ($wheres as $field => $value) {
			$operator = (array_key_exists($field, $operators) ? $operators[$field] : "=");
			$select->where($field, $value, $operator);
		}

		if ($orderBy !== null) {
			$select->orderBy($orderBy);
		}
		if (is_int($limit) && $limit > 0) {
			$select->limit($limit);
		}
		if (is_int($offset) && $offset > 0) {
			$select->offset($offset);
		}

		$query = $this->adapter->query($select);
		if ($query == null) {
			return null;
		}

		if ($this->prototype instanceof IExchangeable) {
			$query->setPrototype($this->prototype);
		} else {
			$this->checkGenericColumns();
			$query->setPrototype($this->genericPrototype);
		}

		return $query->fetchAll(IQuery::FETCH_CUSTOM_CLASS);
	}

	/**
	 * Insert row into current table
	 *
	 * @param array|IExchangeable $values
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @return bool
	 */
	public function insertRow($values) {
		$insert = $this->factory->insert($this->tableName);
		if (is_array($values)) {
			$insert->values($values);
		} else if ($values instanceof IExchangeable) {
			$insert->values($values->toArray());
		} else {
			throw new \InvalidArgumentException("AbstractTable::insertRow() method only accept array or instance of IExchangeable.");
		}

		$query = $this->adapter->query($insert);
		if ($query == null) {
			return false;
		}

		return ($query->getNumRows() == 1);
	}

	/**
	 * Retrieve last insert ID
	 *
	 * This method will retrieve last auto generated ID from INSERT statement which invoked by insertRow() method.
	 *
	 * @return int
	 */
	public function lastInsertId() {
		return $this->adapter->lastInsertId();
	}

	/**
	 * Update data from current table
	 *
	 * This method will update actual data in your table. New values are set by key value pair in $sets variable. Where filter done by $where variable.
	 * Update will be performed by Update object which retrieved from database adapter using Builder helper
	 *
	 * @see IFactory
	 * @see IUpdate
	 *
	 * @param array|IExchangeable $sets      Key value pair where key used ad field and value used as new value in SET statement
	 * @param array               $wheres    Key value pair where key used as field and value used as where value
	 * @param array               $operators Key value pair where key used as field and value used as operator
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @return int
	 */
	public function updateRows($sets, $wheres = array(), $operators = array()) {
		$update = $this->factory->update($this->tableName);
		if (is_array($sets)) {
			$update->sets($sets);
		} else if ($sets instanceof IExchangeable) {
			$update->sets($sets->toArray());
		} else {
			throw new \InvalidArgumentException("AbstractTable::updateRows() method only accept array or instance of IExchangeable.");
		}

		foreach ($wheres as $field => $value) {
			$operator = (array_key_exists($field, $operators) ? $operators[$field] : "=");
			$update->where($field, $value, $operator);
		}

		$query = $this->adapter->query($update);
		if ($query == null) {
			return -1;
		}

		return $query->getNumRows();
	}

	/**
	 * Delete data from current table
	 *
	 * This method will delete data from associated table. Be warned, it will use DELETE statement against your table. Please use $where filter to avoid
	 * accidentally data loss.
	 *
	 * @param array $wheres    Key value pair where key used as field and value used as where value
	 * @param array $operators Key value pair where key used as field and value used as operator
	 *
	 * @return int
	 */
	public function deleteRows($wheres = array(), $operators = array()) {
		$delete = $this->factory->delete($this->tableName);
		foreach ($wheres as $field => $value) {
			$operator = (array_key_exists($field, $operators) ? $operators[$field] : "=");
			$delete->where($field, $value, $operator);
		}

		$query = $this->adapter->query($delete);
		if ($query == null) {
			return -1;
		}

		return $query->getNumRows();
	}
}

// End of File: AbstractTable.php 