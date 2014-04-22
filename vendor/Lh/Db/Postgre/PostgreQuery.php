<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Postgre;

use Exception;
use Lh\Db\IQuery;
use Lh\Db\Query;

/**
 * Class PostgreQuery
 *
 * @package Lh\Db\Postgre
 * @method resource getNativeReader()
 */
class PostgreQuery extends Query {
	/** @var resource Pgsql native reader */
	protected $nativeReader;

	/**
	 * Get total row(s) in current query
	 *
	 * Return total affected data from query execution. This can be total of row(s) returned from SELECT or total row(s) affected from previous query.
	 * Since there is no way we differentiate between SELECT and NON-SELECT we use sum affected row(s) and num row(s)
	 *
	 * @return int
	 */
	public function getNumRows() {
		return pg_affected_rows($this->nativeReader) + pg_num_rows($this->nativeReader);
	}

	/**
	 * Fetch data as associative array. Alias for IQuery::fetch(IQuery::FETCH_ASSOC)
	 *
	 * @see IQuery::FETCH_ASSOC
	 *
	 * @return array
	 */
	public function fetchAssoc() {
		return pg_fetch_assoc($this->nativeReader);
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
		return pg_fetch_row($this->nativeReader);
	}

	/**
	 * Fetch data as associative and zero-based index array. Alias for IQuery::fetch(IQuery::FETCH_BOTH)
	 *
	 * @see IQuery::FETCH_BOTH
	 *
	 * @return array
	 */
	public function fetchBoth() {
		return pg_fetch_array($this->nativeReader);
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
		return pg_fetch_object($this->nativeReader);
	}

	/**
	 * Fetch all data
	 *
	 * Fetch all data from current row till last row from underlying reader.
	 *
	 * @param int $fetchMode
	 *
	 * @throws PostgreException
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
				throw new PostgreException("Unknown fetch mode for PostgreQuery");
		}
	}

	/**
	 * Create appropriate exception for specific driver
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previousException
	 *
	 * @return PostgreException
	 */
	protected function createException($message, $code = 0, Exception $previousException = null) {
		return new PostgreException($message, $code, $previousException);
	}
}

// End of File: PostgreQuery.php 
