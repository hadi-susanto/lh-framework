<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MySqli;

use Exception;
use Lh\Db\IQuery;
use Lh\Db\Query;
use Lh\Db\ResultSet;

/**
 * Class MySqliQuery
 *
 * @package Lh\Db\MySqli
 * @method \mysqli_result getNativeReader()
 */
class MySqliQuery extends Query {
	/** @var \mysqli_result  Mysqli native reader */
	protected $nativeReader;
	/** @var int Affected rows */
	private $affectedRows = 0;
	/** @var bool Do current query have a reader? */
	private $haveReader = false;

	/**
	 * Create new instance of MySqliQuery
	 *
	 * @param \mysqli_result $nativeReader
	 * @param int            $defaultFetchMode
	 * @param int            $affectedRows
	 */
	public function __construct($nativeReader, $defaultFetchMode = IQuery::FETCH_ASSOC, $affectedRows = 0) {
		parent::__construct($nativeReader, $defaultFetchMode);
		$this->affectedRows = $affectedRows;
		$this->haveReader = ($nativeReader instanceof \mysqli_result);
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
			return $this->nativeReader->num_rows;
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
	 * @return MySqliException
	 */
	protected function createException($message, $code = 0, Exception $previousException = null) {
		return new MySqliException($message, $code, $previousException);
	}

	/**
	 * Fetch data as associative array. Alias for IQuery::fetch(IQuery::FETCH_ASSOC)
	 *
	 * @see IQuery::FETCH_ASSOC
	 *
	 * @return array
	 */
	public function fetchAssoc() {
		if (!$this->haveReader) {
			return null;
		}

		return $this->nativeReader->fetch_assoc();
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
		if (!$this->haveReader) {
			return null;
		}

		return $this->nativeReader->fetch_row();
	}

	/**
	 * Fetch data as associative and zero-based index array. Alias for IQuery::fetch(IQuery::FETCH_BOTH)
	 *
	 * @see IQuery::FETCH_BOTH
	 *
	 * @return array
	 */
	public function fetchBoth() {
		if (!$this->haveReader) {
			return null;
		}

		return $this->nativeReader->fetch_array(MYSQLI_BOTH);
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
		if (!$this->haveReader) {
			return null;
		}

		return $this->nativeReader->fetch_object();
	}

	/**
	 * Fetch all data from current row till last row from underlying reader.
	 *
	 * @param int $fetchMode
	 *
	 * @throws MySqliException
	 * @return array
	 */
	public function fetchAll($fetchMode = IQuery::FETCH_ASSOC) {
		if ($fetchMode == IQuery::FETCH_NONE) {
			return array();
		}

		switch ($fetchMode) {
			case IQuery::FETCH_ASSOC:
				return $this->nativeReader->fetch_all(MYSQLI_ASSOC);
			case IQuery::FETCH_ROW:
			case IQuery::FETCH_NUM:
				return $this->nativeReader->fetch_all(MYSQLI_NUM);
			case IQuery::FETCH_BOTH:
				return $this->nativeReader->fetch_all(MYSQLI_BOTH);
			case IQuery::FETCH_OBJECT:
			case IQuery::FETCH_STD_CLASS:
			case IQuery::FETCH_CUSTOM_CLASS:
				$result = array();
				while (($row = $this->fetch($fetchMode)) != null) {
					$result[] = $row;
				}

				return $result;
			default:
				throw new MySqliException("Unknown fetch mode for MySqliQuery");
		}
	}
}

// End of File: MysqliQuery.php
