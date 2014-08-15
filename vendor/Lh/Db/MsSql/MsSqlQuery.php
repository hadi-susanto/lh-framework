<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MsSql;

use Exception;
use Lh\Db\IQuery;
use Lh\Db\Query;

/**
 * Class MsSqlQuery
 *
 * @package Lh\Db\MsSql
 * @method resource getNativeReader()
 */
class MsSqlQuery extends Query {
	/** @var resource Sqlsrv native reader */
	protected $nativeReader;

	/**
	 * Get total row(s) in current query
	 *
	 * Return total affected data from query execution. This can be total of row(s) returned from SELECT or total row(s) affected from previous query
	 *
	 * @return int
	 */
	public function getNumRows() {
		// Although sqlsrv_num_rows() documentation says 'Retrieves the number of rows in a result set' but it will return all row in result set including affected row(s)
		$temp = sqlsrv_num_rows($this->nativeReader);
		if ($temp !== false) {
			return $temp;
		}

		$temp = sqlsrv_rows_affected($this->nativeReader);
		if ($temp !== false) {
			return $temp;
		}

		return 0;
	}

	/**
	 * Fetch data as associative array. Alias for IQuery::fetch(IQuery::FETCH_ASSOC)
	 *
	 * @see IQuery::FETCH_ASSOC
	 *
	 * @return array
	 */
	public function fetchAssoc() {
		return sqlsrv_fetch_array($this->nativeReader, SQLSRV_FETCH_ASSOC);
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
		return sqlsrv_fetch_array($this->nativeReader, SQLSRV_FETCH_NUMERIC);
	}

	/**
	 * Fetch data as associative and zero-based index array. Alias for IQuery::fetch(IQuery::FETCH_BOTH)
	 *
	 * @see IQuery::FETCH_BOTH
	 *
	 * @return array
	 */
	public function fetchBoth() {
		return sqlsrv_fetch_array($this->nativeReader, SQLSRV_FETCH_BOTH);
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
		return sqlsrv_fetch_object($this->nativeReader);
	}

	/**
	 * Fetch all data
	 *
	 * Fetch all data from current row till last row from underlying reader.
	 *
	 * @param int $fetchMode
	 *
	 * @throws MsSqlException
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
				throw new MsSqlException("Unknown fetch mode for MsSqlQuery");
		}
	}

	/**
	 * Create appropriate exception for specific driver
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previousException
	 *
	 * @return MsSqlException
	 */
	protected function createException($message, $code = 0, Exception $previousException = null) {
		return new MsSqlException($message, $code, $previousException);
	}
}

// End of File: MsSqlQuery.php
