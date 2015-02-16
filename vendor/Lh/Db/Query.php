<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db;

use Exception;
use IteratorAggregate as IIteratorAggregate;
use Lh\ApplicationException;
use Lh\Collections\ArrayList;
use Lh\IExchangeable;

/**
 * Class Query
 *
 * This class encapsulate all functionality for reading data stream from native database reader. This reader is FORWARD ONLY although some driver support for rewind
 * Objective this class is to provide similar way to accessing data with additional benefit(s) such as:
 *  1. Direct looping using foreach. Values are retrieved using fetch() and setFetchMode()
 *  2. Create Backward and Forward reader using ResultSet
 *  3. Retrieve native object for specialized reading method.
 *
 * IMPORTANT:
 *  1. Query reading are FORWARD ONLY, therefore calling fetchAll() must be performed before any fetch().
 *
 * @see Query::setFetchMode()
 * @package Lh\Db
 */
abstract class Query implements IQuery, IIteratorAggregate {
	/** @var mixed Native statement */
	protected $nativeReader;
	/** @var int Default fetch mode */
	protected $fetchMode;
	/** @var mixed Prototype object used with IQuery::FETCH_CUSTOM_CLASS */
	protected $prototype;
	/** @var ArrayList Cached result set used in iteration */
	protected $iterator;

	/**
	 * Create new instance of Query
	 *
	 * @param mixed $nativeReader
	 * @param int   $defaultFetchMode
	 */
	public function __construct($nativeReader, $defaultFetchMode = IQuery::FETCH_ASSOC) {
		$this->nativeReader = $nativeReader;
		$this->fetchMode = $defaultFetchMode;
	}

	/**
	 * Get native query reader
	 *
	 * Retrieve actual object / resources used to read from database
	 *
	 * @return mixed
	 */
	public function getNativeReader() {
		return $this->nativeReader;
	}

	/**
	 * Set default fetch mode
	 *
	 * Set default fetch mode for result set. See IQuery::FETCH_* for available fetch mode
	 *
	 * @param int $fetchMode
	 */
	public function setFetchMode($fetchMode) {
		$this->fetchMode = $fetchMode;
	}

	/**
	 * Retrieve default fetch mode for current reader
	 *
	 * @return int
	 */
	public function getFetchMode() {
		return $this->fetchMode;
	}

	/**
	 * Set prototype for fetch custom class
	 *
	 * Set custom class which will be used when FETCH_CUSTOM_CLASS is passed at fetch().
	 * This instanced class will be cloned each time and will call exchangeArray method.
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
			} catch (\ReflectionException $ex) {
				throw new ApplicationException("Prototype must have 'exchangeArray' method");
			}
		}

		$this->prototype = $prototype;
	}

	/**
	 * Get prototype object
	 *
	 * @return mixed
	 */
	public function getPrototype() {
		return $this->prototype;
	}

	/**
	 * Fetch data from underlying native reader and advance result pointer
	 *
	 * @param int $mode
	 *
	 * @throws \Lh\Db\DbException
	 *
	 * @return mixed
	 */
	public function fetch($mode) {
		switch ($mode) {
			case IQuery::FETCH_ASSOC:
				return $this->fetchAssoc();
			case IQuery::FETCH_ROW:
			case IQuery::FETCH_NUM:
				return $this->fetchRow();
			case IQuery::FETCH_BOTH:
				return $this->fetchBoth();
			case IQuery::FETCH_OBJECT:
			case IQuery::FETCH_STD_CLASS:
				return $this->fetchObject();
			case IQuery::FETCH_CUSTOM_CLASS:
				return $this->fetchCustomClass();
			case IQuery::FETCH_NONE:
				return null;
			default:
				throw $this->createException(__CLASS__ . " doesn't support given fetch mode! Fetch mode: " . $mode);
		}
	}

	/**
	 * Create appropriate exception for specific driver
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previousException
	 *
	 * @return DbException
	 */
	protected abstract function createException($message, $code = 0, Exception $previousException = null);

	/**
	 * Fetch data as custom class
	 *
	 * Fetch data as custom class defined from prototype. Alias for IQuery::fetch(IQuery::FETCH_CUSTOM_CLASS)
	 *
	 * @param null|mixed $prototype
	 *
	 * @see IQuery::FETCH_CUSTOM_CLASS
	 *
	 * @return mixed
	 *
	 * @throws \Lh\Db\DbException
	 */
	public function fetchCustomClass($prototype = null) {
		if ($prototype !== null) {
			$this->setPrototype($prototype);
		} else if ($this->prototype === null) {
			throw $this->createException("Unable to fetch as custom class since prototype class is null!");
		}

		$data = $this->fetchAssoc();
		if ($data != null) {
			$cloned = clone($this->prototype);
			$cloned->exchangeArray($data);

			return $cloned;
		} else {
			return null;
		}
	}

	/**
	 * Return query as cached result set
	 *
	 * Using fetchAll() method to generate cached row(s) for looping or another data processing.<br />
	 * Because the nature of reader and fetchAll are forward only then it's advisable to call this method before any fetch
	 *
	 * @return ResultSet
	 */
	public function toResultSet() {
		return new ResultSet($this, $this->getFetchMode(), $this->getPrototype());
	}


	/// REGION		- IteratorAggregate
	/**
	 * Retrieve an external iterator
	 *
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return \Traversable An instance of an object implementing <b>Iterator</b> or <b>Traversable</b>
	 */
	public function getIterator() {
		if ($this->iterator === null) {
			$this->iterator = new ArrayList($this->fetchAll($this->fetchMode));
		}

		return $this->iterator->getIterator();
	}
	/// END REGION	- IteratorAggregate
}

// End of File: QueryBase.php