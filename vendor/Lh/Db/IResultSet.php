<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db;

/**
 * Interface IResultSet
 * @package Lh\Db
 */
interface IResultSet {
	/**
	 * Set prototype object for current result set
	 *
	 * Any object which have exchangeArray() method is allowed to be prototype object. Prototype will be used when row from result set fetched
	 * using fetch custom class mode (IQuery::FETCH_CUSTOM_CLASS). This prototype will be cloned and exchangeArray() will called
	 *
	 * @param mixed|\Lh\IExchangeAble $prototype
	 */
	public function setPrototype($prototype);

	/**
	 * Get all row(s) from current result set
	 *
	 * Result will be returned in array format, but array type is determined from $fetchMode parameter.
	 * When parameter not specified then associative array returned. Row conversion performed when required.
	 *
	 * @see IQuery::FETCH_*
	 *
	 * @param int $fetchMode
	 *
	 * @return array
	 */
	public function getAll($fetchMode = IQuery::FETCH_ASSOC);

	/**
	 * Get row based on index
	 *
	 * Similar to getAll() but this method only retrieve specific row
	 *
	 * @param int $idx
	 * @param int $fetchMode
	 *
	 * @return mixed
	 */
	public function get($idx, $fetchMode = IQuery::FETCH_ASSOC);

	/**
	 * Return first row from current result set and reset pointer into zero
	 *
	 * @param int $fetchMode
	 *
	 * @return mixed
	 */
	public function first($fetchMode = IQuery::FETCH_ASSOC);

	/**
	 * Return previous row from current result set and decrement current pointer
	 *
	 * @param int $fetchMode
	 *
	 * @return mixed
	 */
	public function previous($fetchMode = IQuery::FETCH_ASSOC);

	/**
	 * Return current row from current result set
	 *
	 * @param int $fetchMode
	 *
	 * @return mixed
	 */
	public function current($fetchMode = IQuery::FETCH_ASSOC);

	/**
	 * Return next row from current result set and increment current pointer
	 *
	 * @param int $fetchMode
	 *
	 * @return mixed
	 */
	public function next($fetchMode = IQuery::FETCH_ASSOC);

	/**
	 * Return last row from current result set and set current pointer to total row(s) minus one.
	 *
	 * @param int $fetchMode
	 *
	 * @return mixed
	 */
	public function last($fetchMode = IQuery::FETCH_ASSOC);

	/**
	 * Count total row(s) in current result set
	 *
	 * @return int
	 */
	public function count();
}

// End of File: IResultSet.php 