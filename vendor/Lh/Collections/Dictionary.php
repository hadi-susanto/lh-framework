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

/**
 * Class Dictionary
 *
 * Collection class based on key value pair where the key can be any type. Key dan value type can be enforced when creating instance onf Dictionary Object
 * Whenever key or value type is enforced then there is no way to remove the enforcement.
 * This class made based on System.Collections.Generic.Dictionary<TKey, TValue> from .net language
 *
 * @package Lh\Collections
 */
class Dictionary implements IIteratorAggregate, ICountable, IArrayAccess {
	/** @var array Dictionary key index(es) */
	protected $indexes = array();
	/** @var array Actual storage for dictionary value */
	protected $storage = array();
	/** @var null|string Class name for enforcing key data type */
	protected $enforceKey = null;
	/** @var null|string Class name for enforcing value data type */
	protected $enforceValue = null;

	/**
	 * Create new instance of dictionary
	 *
	 * @param null|array         $input
	 * @param null|string|object $enforceKeyType   Type enforcement for key
	 * @param null|string|object $enforceValueType Type enforcement for value
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct($input = null, $enforceKeyType = null, $enforceValueType = null) {
		if (is_object($enforceKeyType)) {
			$this->enforceKey = get_class($enforceKeyType);
		} else if (is_string($enforceKeyType)) {
			$this->enforceKey = $enforceKeyType;
		} else if ($enforceKeyType !== null) {
			throw new InvalidArgumentException("enforceKeyType only accept object or string.");
		}

		if (is_object($enforceValueType)) {
			$this->enforceValue = get_class($enforceValueType);
		} else if (is_string($enforceValueType)) {
			$this->enforceValue = $enforceValueType;
		} else if ($enforceValueType !== null) {
			throw new InvalidArgumentException("enforceValueType only accept object or string.");
		}

		if (is_array($input)) {
			foreach ($input as $key => $value) {
				$this->checkEnforcement($key, $value);
				$this->indexes[] = $key;
				$this->storage[] = $value;
			}
		}
	}

	/**
	 * Return all keys from current dictionary
	 *
	 * @return array
	 */
	public function getKeys() {
		return array_values($this->indexes);
	}

	/**
	 * Return all values from current dictionary
	 *
	 * @return array
	 */
	public function getValues() {
		return array_values($this->storage);
	}

	/**
	 * Determine index from given key
	 *
	 * @param string $key
	 *
	 * @return int
	 */
	private function indexFromKey($key) {
		$temp = array_keys($this->indexes, $key);
		if (count($temp) != 1) {
			return -1;
		} else {
			return $temp[0];
		}
	}

	/**
	 * Perform data type enforcement
	 *
	 * @param mixed $key
	 * @param mixed $value
	 *
	 * @throws \InvalidArgumentException
	 */
	private function checkEnforcement(&$key, &$value) {
		if ($this->enforceKey !== null && $this->enforceKey !== get_class($key)) {
			throw new InvalidArgumentException(sprintf("Current dictionary key enforcement is set. Unable add key '%s' into Dictionary<%s, %s>", get_class($key), $this->enforceKey, $this->enforceValue ? : "mixed"));
		}
		if ($this->enforceValue !== null && $this->enforceValue !== get_class($value)) {
			throw new InvalidArgumentException(sprintf("Current dictionary value enforcement is set. Unable add value '%s' into Dictionary<%s, %s>", get_class($value), $this->enforceKey, $this->enforceValue ? : "mixed"));
		}
	}

	/**
	 * Return current instance as array
	 *
	 * @return array
	 */
	public function getArrayCopy() {
		if (count($this->indexes) == 0) {
			return array();
		} else {
			return array_combine(array_values($this->indexes), array_values($this->storage));
		}
	}

	/**
	 * Add object into current collection. It will throw exception if key already exists
	 *
	 * @param mixed $key
	 * @param mixed $value
	 *
	 * @throws KeyExistsException
	 * @throws InvalidArgumentException
	 */
	public function add($key, $value) {
		$this->checkEnforcement($key, $value);
		if ($this->containsKey($key)) {
			throw new KeyExistsException("key", "Key: '$key' already exists in current KeyValuePair collections");
		}

		$this->indexes[] = $key;
		$this->storage[] = $value;
	}

	/**
	 * Remove all item(s) from current Dictionary
	 */
	public function clear() {
		$this->indexes = array();
		$this->storage = array();
	}

	/**
	 * Check whether current dictionary contains requested key or not
	 *
	 * @param mixed $key
	 *
	 * @return bool
	 */
	public function containsKey($key) {
		return in_array($key, $this->indexes);
	}

	/**
	 * Check whether current dictionary contains requested value or not
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function containsValue($value) {
		return in_array($value, $this->storage);
	}

	/**
	 * Removing object from collections based on key
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public function remove($key) {
		if (($idx = $this->indexFromKey($key)) != -1) {
			unset($this->indexes[$idx]);
			unset($this->storage[$idx]);

			return $this->containsKey($key);
		} else {
			// Key not found
			return false;
		}
	}

	/**
	 * Updating the object based on the given key. If the key don't exists then it'll be automatically created.
	 *
	 * @param mixed $key
	 * @param mixed $value
	 *
	 * @return mixed|null
	 */
	public function set($key, $value) {
		$this->checkEnforcement($key, $value);

		if (($idx = $this->indexFromKey($key)) != -1) {
			$this->storage[$idx] = $value;
		} else {
			$this->indexes[] = $key;
			$this->storage[] = $value;
		}
	}

	/**
	 * Try to get value from collections based on key
	 *
	 * @param mixed      $key
	 * @param null|mixed $default
	 *
	 * @return mixed|null
	 */
	public function get($key, $default = null) {
		if (($idx = $this->indexFromKey($key)) != -1) {
			return $this->storage[$idx];
		} else {
			return $default;
		}
	}

	/// REGION		- magic methods for property like access
	/**
	 * Enable isset() call using dynamic property
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset($name) {
		return $this->containsKey($name);
	}

	/**
	 * Enable unset() call using dynamic property
	 *
	 * @param string $name
	 */
	public function __unset($name) {
		$this->remove($name);
	}

	/**
	 * Enable add value using '$obj->property = value' style
	 *
	 * @param string $name
	 * @param mixed  $value
	 */
	public function __set($name, $value) {
		$this->set($name, $value);
	}

	/**
	 * Enable data retrieval using '$obj->property' style
	 *
	 * @param string $name
	 *
	 * @return mixed|null
	 * @throws KeyNotFoundException
	 */
	public function __get($name) {
		if (!$this->containsKey($name)) {
			throw new KeyNotFoundException("Unable to find key $name at current dictionary.");
		}
		return $this->get($name);
	}
	/// END REGION	- magic methods

	/// REGION		- IteratorAggregate implementations
	/**
	 * Retrieve an external iterator
	 *
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return \Traversable An instance of an object implementing <b>Iterator</b> or <b>Traversable</b>
	 */
	public function getIterator() {
		return new DictionaryIterator($this);
	}
	/// END REGION	- IteratorAggregate

	/// REGION		- Countable
	/**
	 * Count total element(s) in current dictionary
	 *
	 * @link http://php.net/manual/en/countable.count.php
	 *
	 * @return int The custom count as an integer.
	 */
	public function count() {
		return count($this->indexes);
	}
	/// END REGION	- Countable

	/// REGION		- ArrayAccess implementations
	/**
	 * Whether a offset exists
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset
	 *
	 * @return boolean true on success or false on failure.
	 *
	 * The return value will be casted to boolean if non-boolean was returned.
	 */
	public function offsetExists($offset) {
		return $this->containsKey($offset);
	}

	/**
	 * Offset to retrieve
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset
	 *
	 * @throws KeyNotFoundException
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		if (!$this->containsKey($offset)) {
			throw new KeyNotFoundException("Unable to find key $offset at current dictionary.");
		}
		return $this->get($offset);
	}

	/**
	 * Offset to set
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$this->set($offset, $value);
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
		$this->remove($offset);
	}
	/// END REGION	- ArrayAccess
}

// End of File: Dictionary.php