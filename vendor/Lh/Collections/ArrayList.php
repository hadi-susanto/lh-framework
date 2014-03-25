<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Collections;

use ArrayAccess as IArrayAccess;
use Countable as ICountable;
use InvalidArgumentException;
use IteratorAggregate as IIteratorAggregate;
use OutOfBoundsException;

/**
 * Class ArrayList
 *
 * Collection class based on zero-based index.
 *
 * @package Lh\Collections
 */
class ArrayList implements IIteratorAggregate, ICountable, IArrayAccess {
	/** @var array Store actual value(s) */
	protected $storage = array();
	/** @var null|string Class name for data type enforcement */
	protected $enforceValue = null;

	/**
	 * Create new ArrayList object
	 *
	 * @param null|array  $input
	 * @param null|string $enforceValueType Class name for enforcing data type
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct($input = null, $enforceValueType = null) {
		if (is_object($enforceValueType)) {
			$this->enforceValue = get_class($enforceValueType);
		} else if (is_string($enforceValueType)) {
			$this->enforceValue = $enforceValueType;
		} else if ($enforceValueType !== null) {
			throw new InvalidArgumentException("enforceValueType only accept object or string.");
		}
		if (is_array($input)) {
			foreach ($input as $value) {
				$this->checkEnforcement($value);
				$this->storage[] = $value;
			}
		}
	}

	/**
	 * Perform data type enforcement
	 *
	 * ArrayList have ability to enforce data type of its value. This method will check value based on class name
	 *
	 * @param $value
	 *
	 * @throws \InvalidArgumentException
	 */
	private function checkEnforcement(&$value) {
		if ($this->enforceValue !== null && $this->enforceValue !== get_class($value)) {
			throw new InvalidArgumentException(sprintf("Current array list value enforcement is set. Unable add value '%s' into ArrayList<%s>", get_class($value), $this->enforceValue));
		}
	}
	/**
	 * Return current instance as array
	 *
	 * @return array
	 */
	public function getArrayCopy() {
		if (count($this->storage) == 0) {
			return array();
		} else {
			return array_values($this->storage);
		}
	}

	/**
	 * Add an item into collection.
	 *
	 * @param mixed $value
	 */
	public function add($value) {
		$this->checkEnforcement($value);
		$this->storage[] = $value;
	}

	/**
	 * Remove all item(s) from current ArrayList
	 */
	public function clear() {
		$this->storage = array();
	}

	/**
	 * Check whether an item exists in collections or not
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function contains($value) {
		return in_array($value, $this->storage);
	}

	/**
	 * Get the first index of the given item
	 *
	 * @param mixed $findMe
	 *
	 * @return int|string
	 */
	public function indexOf($findMe) {
		foreach ($this->storage as $idx => $value) {
			if ($value == $findMe) {
				return $idx;
			}
		}

		return -1;
	}

	/**
	 * Insert an item at specific index
	 *
	 * @param int   $idx
	 * @param mixed $value
	 *
	 * @throws \OutOfBoundsException
	 */
	public function insert($idx, $value) {
		if ($idx > count($this->storage)) {
			throw new OutOfBoundsException("Unable to insert element at $idx! Maximum index for insert is " . count($this->storage));
		}
		$this->checkEnforcement($value);
		array_splice($this->storage, $idx, 0, array($value));
	}

	/**
	 * Get the last index of the given item
	 *
	 * @param mixed $findMe
	 *
	 * @return int
	 */
	public function lastIndexOf($findMe) {
		$result = -1;
		foreach ($this->storage as $idx => $value) {
			if ($value == $findMe) {
				$result = $idx;
			}
		}
		return $result;
	}

	/**
	 * Remove specific item from collection
	 *
	 * @param mixed $findMe
	 *
	 * @return bool
	 */
	public function remove($findMe) {
		foreach ($this->storage as $idx => $value) {
			if ($value == $findMe) {
				return $this->removeAt($idx);
			}
		}
		return false;
	}

	/**
	 * Remove an item based on index
	 *
	 * @param int $idx
	 *
	 * @return bool
	 * @throws \OutOfBoundsException
	 */
	public function removeAt($idx) {
		if ($idx >= count($this->storage)) {
			throw new OutOfBoundsException("Unable remove item at index $idx! ArrayList size is " . count($this->storage));
		}
		if (!array_key_exists($idx, $this->storage)) {
			return false;
		}
		array_splice($this->storage, $idx, 1);

		return true;
	}

	/**
	 * Sort current collection
	 *
	 * User can provide custom sort logic by passing the handler or callable as the parameter. Handler signature is:
	 *
	 * function compare($lhs, $rhs) {
	 *		return int;
	 * }
	 *
	 * Rules:
	 *  - function should return < 0 if $lhs less than $rhs
	 *  - function should return 0 if $lhs equal to $rhs
	 *  - function should return > 0 if $lhs greater than $rhs
	 *  - if floating unit returned from function it'll casted into integer
	 *
	 * @param null|callable $compareHandler
	 */
	public function sort($compareHandler = null) {
		if ($compareHandler === null) {
			sort($this->storage);
		} else if (is_callable($compareHandler)) {
			usort($this->storage, $compareHandler);
		}
	}

	/// REGION		- magic methods
	/**
	 * Provide collection checking using isset() function
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset($name) {
		return array_key_exists($name, $this->storage);
	}

	/**
	 * Provide removing an item from collection using unset()
	 *
	 * @param string $name
	 */
	public function __unset($name) {
		$this->removeAt($name);
	}
	/// END REGION	- magic methods

	/// REGION		- IteratorAggregate implementations
	/**
	 * Retrieve an external iterator
	 *
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return \Traversable An instance of an object implementing <b>Iterator</b> or <b>\Traversable</b>
	 */
	public function getIterator() {
		return new ArrayListIterator($this->storage);
	}
	/// END REGION	- IteratorAggregate

	/// REGION		- Countable
	/**
	 * Count total item(s) in ArrayList
	 *
	 * @return int
	 */
	public function count() {
		return count($this->storage);
	}
	/// END REGION	- Countable

	/// REGION		- ArrayAccess implementations
	/**
	 * Whether a offset exists
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param string $offset
	 *
	 * @return bool
	 */
	public function offsetExists($offset) {
		return $this->contains($offset);
	}

	/**
	 * Offset to retrieve
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param string $offset
	 *
	 * @throws OutOfBoundsException
	 * @return mixed
	 */
	public function offsetGet($offset) {
		if ($offset >= count($this->storage)) {
			throw new OutOfBoundsException("Unable to access value at index $offset. ArrayList size is " . count($this->storage));
		}
		return $this->storage[$offset];
	}

	/**
	 * Offset to set
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param string $offset
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$this->insert($offset, $value);
	}

	/**
	 * Offset to unset
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset
	 *
	 * @return void
	 */
	public function offsetUnset($offset) {
		$this->removeAt($offset);
	}
	/// END REGION	- ArrayAccess
}

// End of File: ArrayList.php 