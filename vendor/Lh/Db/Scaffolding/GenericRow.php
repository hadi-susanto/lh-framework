<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Scaffolding;

use Lh\Exceptions\InvalidOperationException;
use Lh\Exceptions\PropertyNotFoundException;
use ArrayAccess as IArrayAccess;

/**
 * Class GenericRow
 *
 * Represent any row from a table. Generic row don't have concrete field or property. Data access provided to row value provided by:
 *  - Property like access (using __set() and __get() magic method)
 *  - Array like access (using ArrayAccess interface)
 *
 * @package Lh\Db\Scaffolding
 */
class GenericRow extends AbstractRow implements IArrayAccess {

	/**
	 * Create a generic row
	 *
	 * Generic row will represent a row of any table. Generic row will not have restriction for it's value. It only used for rapid form development, therefore
	 * usage of this class is not recommended. Better to use your concrete class or derived directly from AbstractRow
	 *
	 * @see AbstractRow
	 *
	 * @param string $tableName
	 * @param array  $row
	 */
	public function __construct($tableName, $row = array()) {
		parent::__construct($tableName);
		if ($row != null) {
			$this->setColumns(array_keys($row));
			$this->exchangeArray($row);
		}
	}

	/**
	 * Magic method for check column existence
	 *
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name) {
		return in_array($name, $this->columns);
	}

	/**
	 * Unset property is not allowed
	 *
	 * @param string $name
	 *
	 * @throws \Lh\Exceptions\InvalidOperationException
	 */
	public function __unset($name) {
		throw new InvalidOperationException("Unable to unset column from row which bound to an table. Please set to NULL value instead of unset.");
	}

	/**
	 * Magic method to retrieve column value
	 *
	 * @param string $name
	 * @return mixed
	 * @throws \Lh\Exceptions\PropertyNotFoundException
	 */
	public function __get($name) {
		if (!$this->__isset($name)) {
			throw new PropertyNotFoundException($this, $name, sprintf("Table '%s' don't have '%s' column.", $this->tableName, $name));
		}

		return $this->values[$name];
	}

	/**
	 * Magic method to set column value
	 *
	 * @param string $name
	 * @param mixed $value
	 * @throws \Lh\Exceptions\PropertyNotFoundException
	 */
	public function __set($name, $value) {
		if (!$this->__isset($name)) {
			throw new PropertyNotFoundException($this, $name, sprintf("Table '%s' don't have '%s' column.", $this->tableName, $name));
		}

		$this->values[$name] = $value;
	}


	/**
	 * String representation of object
	 *
	 * @link http://php.net/manual/en/serializable.serialize.php
	 *
	 * @return string the string representation of the object or null
	 */
	public function serialize() {
		return serialize(array(
			"tableName" => $this->tableName,
			"columns" => $this->columns,
			"values" => $this->values
		));
	}

	/**
	 * Constructs the object
	 *
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 * @param string $serialized The string representation of the object.
	 *
	 * @return void
	 */
	public function unserialize($serialized) {
		$unSerialized = unserialize($serialized);

		$this->tableName = $unSerialized["tableName"];
		$this->columns = $unSerialized["columns"];
		$this->values = $unSerialized["values"];
	}

	/**
	 * Whether a offset exists
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param string $offset An offset to check for.
	 *
	 * @return boolean true on success or false on failure.
	 */
	public function offsetExists($offset) {
		return $this->__isset($offset);
	}

	/**
	 * Offset to retrieve
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param string $offset The offset to retrieve.
	 *
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		return $this->__get($offset);
	}

	/**
	 * Offset to set
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param string $offset The offset to assign the value to.
	 * @param mixed  $value  The value to set.
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$this->__set($offset, $value);
	}

	/**
	 * Offset to unset
	 *
	 * Unset is not allowed since a row is bound to a table
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset The offset to unset.
	 *
	 * @throws \Lh\Exceptions\InvalidOperationException
	 * @return void
	 */
	public function offsetUnset($offset) {
		throw new InvalidOperationException("Unable to unset column from row which bound to an table. Please set to NULL value instead of unset.");
	}
}

// End of File: GenericRow.php 