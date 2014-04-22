<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MySql;

use Exception;
use Lh\Db\IQuery;
use Lh\Db\Query;
use Lh\Db\ResultSet;

/**
 * Class MySqlQuery
 *
 * @package Lh\Db\MySql
 * @method resource getNativeReader()
 */
class MySqlQuery extends Query {
	/** @var resource Mysql native reader */
	protected $nativeReader;
	/** @var int Affected row(s) from previous query. Contains total rows if SELECT query */
	private $affectedRows = 0;
	/** @var bool Does current query have a native reader */
	private $haveReader = false;

	/**
	 * Create new instance of MySqlQuery
	 *
	 * @param resource $nativeReader Resource from mysql_connect() or native reader MySqlAdapter
	 * @param int      $defaultFetchMode
	 * @param int      $affectedRows
	 */
	public function __construct($nativeReader, $defaultFetchMode = IQuery::FETCH_ASSOC, $affectedRows = 0) {
		parent::__construct($nativeReader, $defaultFetchMode);
		$this->affectedRows = $affectedRows;
		$this->haveReader = is_resource($nativeReader);
	}

	/**
	 * Get total row(s) in current query
	 *
	 * Return total affected data from query execution. This can be total of row(s) returned from SELECT or total row(s) affected from previous query
	 *
	 * @return int
	 */
	public function getNumRows() {
		if ($this->haveReader) {
			return mysql_num_rows($this->nativeReader);
		} else {
			return $this->affectedRows;
		}
	}

	/**
	 * Create driver specific exception
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previousException
	 *
	 * @return MySqlException
	 */
	protected function createException($message, $code = 0, Exception $previousException = null) {
		return new MySqlException($message, $code, $previousException);
	}

	/**
	 * Fetch data as associative array
	 *
	 * @return array
	 */
	public function fetchAssoc() {
		if (!$this->haveReader) {
			return null;
		}

		return mysql_fetch_assoc($this->nativeReader);
	}

	/**
	 * Fetch data as zero-based index array
	 *
	 * @return array
	 */
	public function fetchRow() {
		if (!$this->haveReader) {
			return null;
		}

		return mysql_fetch_row($this->nativeReader);
	}

	/**
	 * Fetch data as associative and zero-based index array
	 *
	 * @return array
	 */
	public function fetchBoth() {
		if (!$this->haveReader) {
			return null;
		}

		return mysql_fetch_array($this->nativeReader, MYSQL_BOTH);
	}

	/**
	 * Fetch data as stdClass object (default object type)
	 *
	 * @return mixed
	 */
	public function fetchObject() {
		if (!$this->haveReader) {
			return null;
		}

		return mysql_fetch_object($this->nativeReader);
	}

	/**
	 * Fetch all data from current row last row from underlying reader.
	 *
	 * @param int $fetchMode
	 *
	 * @throws MySqlException
	 * @return array
	 */
	public function fetchAll($fetchMode = IQuery::FETCH_ASSOC) {
		if ($fetchMode == IQuery::FETCH_NONE) {
			return array();
		}

		switch ($fetchMode) {
			case IQuery::FETCH_ASSOC:
			case IQuery::FETCH_ROW:
			case IQuery::FETCH_NUM:
			case IQuery::FETCH_BOTH:
			case IQuery::FETCH_OBJECT:
			case IQuery::FETCH_STD_CLASS:
			case IQuery::FETCH_CUSTOM_CLASS:
				$result = array();
				while (($row = $this->fetch($fetchMode)) != null) {
					$result[] = $row;
				}

				return $result;
			default:
				throw new MySqlException("Unknown fetch mode for MySqlQuery");
		}
	}
}

// End of File: MySqlQuery.php
