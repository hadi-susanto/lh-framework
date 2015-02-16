<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db;

/**
 * Interface IQuery
 *
 * Contract for result returned by executing query against database
 *
 * @package Lh\Db
 */
interface IQuery {
	const FETCH_NONE = 0;
	const FETCH_ASSOC = 1;
	const FETCH_NUM = 2;
	const FETCH_ROW = 2;
	const FETCH_BOTH = 3;
	const FETCH_OBJECT = 4;
	const FETCH_STD_CLASS = 4;
	const FETCH_CUSTOM_CLASS = 5;

	/**
	 * Get native query reader
	 *
	 * Retrieve actual object / resources used to read from database
	 *
	 * @return mixed
	 */
	public function getNativeReader();

	/**
	 * Get total row(s) in current query
	 *
	 * Return total affected data from query execution. This can be total of row(s) returned from SELECT or total row(s) affected from previous query
	 *
	 * @return int
	 */
	public function getNumRows();

	/**
	 * Set default fetch mode
	 *
	 * Set default fetch mode for result set. See IQuery::FETCH_* for available fetch mode
	 *
	 * @param int $fetchMode
	 *
	 * @return void
	 */
	public function setFetchMode($fetchMode);

	/**
	 * Retrieve default fetch mode for current reader
	 *
	 * @return int
	 */
	public function getFetchMode();

	/**
	 * Set prototype for fetch custom class
	 *
	 * Set custom class which will be used when FETCH_CUSTOM_CLASS is passed at fetch().
	 * This instanced class will be cloned each time and will call exchangeArray method.
	 *
	 * @param mixed $prototype
	 */
	public function setPrototype($prototype);

	/**
	 * Get prototype object
	 *
	 * @return mixed
	 */
	public function getPrototype();

	/**
	 * Fetch data from underlying native reader and return the result
	 *
	 * @param int $mode
	 *
	 * @return mixed
	 */
	public function fetch($mode);

	/**
	 * Fetch data as associative array. Alias for IQuery::fetch(IQuery::FETCH_ASSOC)
	 *
	 * @see IQuery::FETCH_ASSOC
	 *
	 * @return array
	 */
	public function fetchAssoc();

	/**
	 * Fetch data as zero-based index array. Alias for IQuery::fetch(IQuery::FETCH_ROW)
	 *
	 * @see IQuery::FETCH_ROW
	 * @see IQuery::FETCH_NUM
	 *
	 * @return array
	 */
	public function fetchRow();

	/**
	 * Fetch data as associative and zero-based index array. Alias for IQuery::fetch(IQuery::FETCH_BOTH)
	 *
	 * @see IQuery::FETCH_BOTH
	 *
	 * @return array
	 */
	public function fetchBoth();

	/**
	 * Fetch data as stdClass object (default object type). Alias for IQuery::fetch(IQuery::FETCH_OBJECT)
	 *
	 * @see IQuery::FETCH_OBJECT
	 * @see IQuery::FETCH_STD_CLASS
	 *
	 * @return mixed
	 */
	public function fetchObject();

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
	 */
	public function fetchCustomClass($prototype = null);

	/**
	 * Fetch all data
	 *
	 * Fetch all data from current row till last row from underlying reader.
	 *
	 * @param int $fetchMode
	 *
	 * @return array
	 */
	public function fetchAll($fetchMode = IQuery::FETCH_ASSOC);

	/**
	 * Return query as cached result set
	 *
	 * Using fetchAll() method to generate cached row(s) for looping or another data processing.<br />
	 * Because the nature of reader and fetchAll are forward only then it's advisable to call this method before any fetch
	 *
	 * @return ResultSet
	 */
	public function toResultSet();
}

// End of File: IQuery.php