<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db;

use ArrayAccess as IArrayAccess;
use InvalidArgumentException;
use IteratorAggregate as IIteratorAggregate;
use Lh\ApplicationException;
use Lh\Collections\ArrayListIterator;
use Lh\Exceptions\InvalidOperationException;
use Lh\Exceptions\InvalidStateException;
use Lh\IExchangeable;
use OutOfBoundsException;
use ReflectionException;
use Traversable;

/**
 * Class ResultSet
 *
 * Represent collection of row(s) from Query object. This object will read and cached all row(s) from respective reader.
 * Row(s) can be accessed using array like index, but index must be integer.
 *
 * @package Lh\Db
 */
class ResultSet implements IResultSet, IArrayAccess, IIteratorAggregate {
	/** @var int How data should be fetched. Read IQuery::FETCH_* */
	protected $fetchMode;
	/** @var mixed Prototype class used in IQuery::FETCH_CUSTOM_CLASS */
	protected $prototype;
	/** @var array() Cached data in array associative format */
	protected $cachedRows;
	/** @var int Current result set index pointer */
	protected $index = 0;
	/** @var int Total row(s). Should equal to count($cachedRows) */
	protected $max = 0;

	/**
	 * Create new instance of ResultSet
	 *
	 * IMPORTANT: While creating a result set, its instance will automatically cached all row(s) from IQuery object using fetchAll()
	 * Some driver is unable to perform data seeking, therefore IQuery object must be positioned at first row! Otherwise only remaining
	 * data row(s) cached into current result set
	 *
	 * @param IQuery     $query
	 * @param int        $fetchMode
	 * @param null|mixed $prototype
	 */
	public function __construct(IQuery $query, $fetchMode = IQuery::FETCH_ASSOC, $prototype = null) {
		$this->fetchMode = $fetchMode;
		if ($prototype !== null) {
			$this->setPrototype($prototype);
		}

		$this->cachedRows = $query->fetchAll(IQuery::FETCH_ASSOC);
		$this->index = 0;
		$this->max = count($this->cachedRows);
	}


	/**
	 * Set default fetch mode
	 *
	 * Determine how default fetch data should be done. This fetch mode will be used when user loop this instanced class directly.<br />
	 * Please see IQuery::FETCH_* for available fetch mode
	 *
	 * @see IQuery
	 * @param int $fetchMode
	 */
	public function setFetchMode($fetchMode) {
		$this->fetchMode = $fetchMode;
	}

	/**
	 * Get default fetch mode
	 *
	 * @return int
	 */
	public function getFetchMode() {
		return $this->fetchMode;
	}

	/**
	 * Set prototype object for current result set
	 *
	 * Any object which have exchangeArray() method is allowed to be prototype object. Prototype will be used when row from result set fetched
	 * using fetch custom class mode (IQuery::FETCH_CUSTOM_CLASS). This prototype will be cloned and exchangeArray() will called
	 *
	 * @param mixed $prototype
	 *
	 * @throws \Lh\ApplicationException
	 */
	public function setPrototype($prototype) {
		if (!is_object($prototype)) {
			throw new ApplicationException("Prototype must be an object type!");
		}

		if (get_class($prototype) == get_class($this->prototype)) {
			return;
		}

		if (!($prototype instanceof IExchangeable)) {
			try {
				$reflection = new \ReflectionClass($prototype);
				$methodInfo = $reflection->getMethod("exchangeArray");
				$parameters = $methodInfo->getParameters();
				if (count($parameters) < 1) {
					throw new ApplicationException("Prototype 'exchangeArray' method should accept at least one parameter!");
				}
			} catch (ReflectionException $ex) {
				throw new ApplicationException("Prototype must have 'exchangeArray' method");
			}
		}

		$this->prototype = $prototype;
	}

	/**
	 * Get prototype used for current result set
	 *
	 * @return mixed
	 */
	public function getPrototype() {
		return $this->prototype;
	}

	/**
	 * Convert associative array into requested type
	 *
	 * Used by get() and their derivative to convert row into user requested type
	 *
	 * @param array $row
	 * @param int   $mode
	 *
	 * @return array|mixed|null|object
	 * @throws \Lh\Exceptions\InvalidStateException
	 * @throws \InvalidArgumentException
	 */
	protected function convertCachedRow($row, $mode) {
		switch ($mode) {
			case IQuery::FETCH_ASSOC:
				return $row;
			case IQuery::FETCH_ROW:
			case IQuery::FETCH_NUM:
				return array_values($row);
			case IQuery::FETCH_BOTH:
				$idx = 0;
				$newArray = array();
				foreach ($row as $key => $value) {
					$newArray[$idx++] = $value;
					$newArray[$key] = $value;
				}
				unset($idx, $key, $value);

				return $newArray;
			case IQuery::FETCH_OBJECT:
			case IQuery::FETCH_STD_CLASS:
				return (object)$row;
			case IQuery::FETCH_CUSTOM_CLASS:
				if ($this->prototype === null) {
					throw new InvalidStateException("Fetching as custom class require prototype class defined beforehand.");
				}
				$cloned = clone($this->prototype);
				$cloned->exchangeArray($row);

				return $cloned;
			case IQuery::FETCH_NONE:
				return null;
			default:
				throw new InvalidArgumentException("Unknown fetch mode, unable to convert row data.");
		}
	}

	/**
	 * Get all row(s) from current result set
	 *
	 * Result will be returned in array format, but array type is determined from $fetchMode parameter.
	 * When parameter not specified then associative array returned. Row conversion performed when required.
	 *
	 * @param int $fetchMode
	 *
	 * @return array
	 */
	public function getAll($fetchMode = IQuery::FETCH_ASSOC) {
		if ($fetchMode == IQuery::FETCH_ASSOC) {
			return $this->cachedRows;
		} else {
			$buff = array();
			foreach ($this->cachedRows as $row) {
				$buff[] = $this->convertCachedRow($row, $fetchMode);
			}

			return $buff;
		}
	}

	/**
	 * Get row based on index
	 *
	 * Similar to getAll() but this method only retrieve specific row
	 *
	 * @param int $idx
	 * @param int $fetchMode
	 *
	 * @return array|mixed|null|object
	 * @throws \OutOfBoundsException
	 */
	public function get($idx, $fetchMode = IQuery::FETCH_ASSOC) {
		if ($idx >= $this->max) {
			throw new OutOfBoundsException("Index must be less than maximum items in result.");
		}

		if ($fetchMode == IQuery::FETCH_ASSOC) {
			return $this->cachedRows[$idx];
		} else {
			return $this->convertCachedRow($this->cachedRows[$idx], $fetchMode);
		}
	}

	/**
	 * Return first row from current result set and reset pointer into zero
	 *
	 * @param int $fetchMode
	 *
	 * @return mixed
	 */
	public function first($fetchMode = IQuery::FETCH_ASSOC) {
		$this->index = 0;
		return $this->get(0, $fetchMode);
	}

	/**
	 * Return previous row from current result set and decrement current pointer
	 *
	 * @param int $fetchMode
	 *
	 * @return mixed
	 */
	public function previous($fetchMode = IQuery::FETCH_ASSOC) {
		if ($this->index == 0) {
			return null;
		} else {
			$this->index--;

			return $this->get($this->index, $fetchMode);
		}
	}

	/**
	 * Return current row from current result set
	 *
	 * @param int $fetchMode
	 *
	 * @return mixed
	 */
	public function current($fetchMode = IQuery::FETCH_ASSOC) {
		return $this->get($this->index, $fetchMode);
	}

	/**
	 * Return next row from current result set and increment current pointer
	 *
	 * @param int $fetchMode
	 *
	 * @return mixed
	 */
	public function next($fetchMode = IQuery::FETCH_ASSOC) {
		if ($this->index == ($this->max - 1)) {
			return null;
		} else {
			$this->index++;

			return $this->get($this->index, $fetchMode);
		}
	}

	/**
	 * Return last row from current result set and set current pointer to total row(s) minus one.
	 *
	 * @param int $fetchMode
	 *
	 * @return mixed
	 */
	public function last($fetchMode = IQuery::FETCH_ASSOC) {
		$this->index = ($this->max - 1);

		return $this->get($this->index, $fetchMode);
	}


	/**
	 * Count total row(s) in current result set
	 *
	 * @return int
	 */
	public function count() {
		return $this->max;
	}

	/// REGION		- ArrayAccess: accessing object as array (have similar concept as indexer at .net)
	/**
	 * Check whether a offset exists in current result set or not
	 *
	 * NOTE: offset must be an integer
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param int $offset
	 *
	 * @throws InvalidArgumentException
	 * @return boolean true on success or false on failure.
	 */
	public function offsetExists($offset) {
		if (!is_int($offset)) {
			throw new InvalidArgumentException("ResultSet can only accessed by integer index.");
		}

		return array_key_exists($offset, $this->cachedRows);
	}

	/**
	 * Retrieve a row from current result set using array access (indexer)
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param int $offset
	 *
	 * @throws InvalidArgumentException
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		if (!is_int($offset)) {
			throw new InvalidArgumentException("ResultSet can only accessed by integer index.");
		}

		return $this->get($offset, $this->fetchMode);
	}

	/**
	 * Implemented because of ArrayAccess contract but it will throw exception since this operation is not allowed
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 *
	 * @throws \Lh\Exceptions\InvalidOperationException
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		throw new InvalidOperationException("Unable to add data to result set since result set is Read Only collection");
	}

	/**
	 * Implemented because of ArrayAccess contract but it will throw exception since this operation is not allowed
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset
	 *
	 * @throws \Lh\Exceptions\InvalidOperationException
	 * @return void
	 */
	public function offsetUnset($offset) {
		throw new InvalidOperationException("Unable to remove data from result set since result set is Read Only collection");
	}
	/// END REGION	- ArrayAccess

	/**
	 * Retrieve an external iterator
	 *
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return Traversable An instance of an object implementing <b>Iterator</b> or <b>Traversable</b>
	 */
	public function getIterator() {
		return new ArrayListIterator($this->getAll($this->fetchMode));
	}
}

// End of File: ResultSet.php