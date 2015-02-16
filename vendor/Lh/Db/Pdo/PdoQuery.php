<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Pdo;

use Exception;
use IteratorAggregate as IIteratorAggregate;
use Lh\ApplicationException;
use Lh\Db\IQuery;
use Lh\Db\ResultSet;
use Lh\IExchangeable;
use PDO;
use PDOStatement;

/**
 * Class PdoQuery
 *
 * @package Lh\Db\Pdo
 */
abstract class PdoQuery implements IQuery, IIteratorAggregate {
	/** @var PDOStatement Native statement */
	protected $pdoStatement;
	/** @var int Default fetch mode */
	protected $fetchMode;
	/** @var mixed Prototype object used with IQuery::FETCH_CUSTOM_CLASS */
	protected $prototype;
	/** @var ResultSet Cached result set used in iteration */
	protected $resultSet;

	/**
	 * Create new instance of PdoQuery
	 *
	 * @param \PDOStatement $pdoStatement
	 * @param int           $defaultFetchMode
	 */
	public function __construct(PDOStatement $pdoStatement, $defaultFetchMode = IQuery::FETCH_ASSOC) {
		$this->pdoStatement = $pdoStatement;
		$this->fetchMode = $defaultFetchMode;
	}

	/**
	 * Get native query reader
	 *
	 * Retrieve actual object / resources used to read from database
	 *
	 * @return PDOStatement
	 */
	public function getNativeReader() {
		return $this->pdoStatement;
	}

	/**
	 * Get total row(s) in current query
	 *
	 * Return total affected data from query execution. This can be total of row(s) returned from SELECT or total row(s) affected from previous query
	 *
	 * @return int
	 */
	public function getNumRows() {
		return $this->pdoStatement->rowCount();
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
	 * Get prototype object
	 *
	 * @return mixed
	 */
	public function getPrototype() {
		return $this->prototype;
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
	 * Create appropriate exception for specific driver
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previousException
	 *
	 * @return \Lh\Db\DbException
	 */
	protected abstract function createException($message, $code = 0, Exception $previousException = null);

	/**
	 * Fetch data from underlying native reader and return the result
	 *
	 * @param int $mode
	 *
	 * @throws \Exception
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
				throw new \Exception(__CLASS__ . " doesn't support given fetch mode! Fetch mode: " . $mode);
		}
	}

	/**
	 * Fetch data as associative array. Alias for IQuery::fetch(IQuery::FETCH_ASSOC)
	 *
	 * @see IQuery::FETCH_ASSOC
	 *
	 * @return array
	 */
	public function fetchAssoc() {
		return $this->pdoStatement->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Fetch data as zero-based index array. Alias for IQuery::fetch(IQuery::FETCH_ROW)
	 *
	 * @see IQuery::FETCH_ROW
	 * @see IQuery::FETCH_NUM
	 *
	 * @return array
	 */
	public function fetchRow() {
		return $this->pdoStatement->fetch(PDO::FETCH_NUM);
	}
	/**
	 * Fetch data as associative and zero-based index array. Alias for IQuery::fetch(IQuery::FETCH_BOTH)
	 *
	 * @see IQuery::FETCH_BOTH
	 *
	 * @return array
	 */
	public function fetchBoth() {
		return $this->pdoStatement->fetch(PDO::FETCH_BOTH);
	}

	/**
	 * Fetch data as stdClass object (default object type). Alias for IQuery::fetch(IQuery::FETCH_OBJECT)
	 *
	 * @see IQuery::FETCH_OBJECT
	 * @see IQuery::FETCH_STD_CLASS
	 *
	 * @return mixed
	 */
	public function fetchObject() {
		return $this->pdoStatement->fetch(PDO::FETCH_OBJ);
	}

	/**
	 * Fetch data as custom class
	 *
	 * Fetch data as custom class defined from prototype. Alias for IQuery::fetch(IQuery::FETCH_CUSTOM_CLASS)
	 *
	 * @param null $prototype
	 *
	 * @see IQuery::FETCH_CUSTOM_CLASS
	 *
	 * @return mixed|null
	 *
	 * @throws \Exception
	 */
	public function fetchCustomClass($prototype = null) {
		if ($prototype !== null) {
			$this->setPrototype($prototype);
		} else if ($this->prototype === null) {
			throw new \Exception("Unable to fetch as custom class since prototype class is null!");
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
	 * Fetch all data
	 *
	 * Fetch all data from current row till last row from underlying reader.
	 *
	 * @param int $fetchMode
	 *
	 * @throws \Lh\Db\DbException
	 * @return array
	 */
	public function fetchAll($fetchMode = IQuery::FETCH_ASSOC) {
		if ($fetchMode == IQuery::FETCH_NONE) {
			return array();
		}

		switch ($fetchMode) {
			case IQuery::FETCH_ASSOC:
				return $this->pdoStatement->fetchAll(PDO::FETCH_ASSOC);
			case IQuery::FETCH_ROW:
			case IQuery::FETCH_NUM:
				return $this->pdoStatement->fetchAll(PDO::FETCH_NUM);
			case IQuery::FETCH_BOTH:
				return $this->pdoStatement->fetchAll(PDO::FETCH_BOTH);
			case IQuery::FETCH_OBJECT:
			case IQuery::FETCH_STD_CLASS:
				return $this->pdoStatement->fetchAll(PDO::FETCH_OBJ);
			case IQuery::FETCH_CUSTOM_CLASS:
				$result = array();
				while (($row = $this->fetchCustomClass()) != null) {
					$result[] = $row;
				}

				return $result;
			default:
				throw $this->createException("Unknown fetch mode for " . get_class($this));
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
		if ($this->resultSet === null) {
			$this->resultSet = new ResultSet($this, $this->getFetchMode(), $this->getPrototype());
		}

		return $this->resultSet;
	}


	/// REGION		- IteratorAggregate
	/**
	 * Retrieve an external iterator
	 *
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return \Traversable An instance of an object implementing <b>Iterator</b> or <b>Traversable</b>
	 */
	public function getIterator() {
		return $this->toResultSet()->getIterator();
	}
	/// END REGION	- IteratorAggregate
}

// End of File: PdoQuery.php 