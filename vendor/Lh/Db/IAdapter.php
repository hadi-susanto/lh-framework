<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db;

/**
 * Interface IAdapter
 * @package Lh\Db
 */
interface IAdapter {
	/**
	 * Get adapter name
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Return native driver which communicating with database server / engine
	 *
	 * @return mixed
	 */
	public function getNativeConnector();

	/**
	 * Check whether current connection opened or not
	 *
	 * @return bool
	 */
	public function isOpened();

	/**
	 * Get exception occurred from previous execution
	 *
	 * @return DbException
	 */
	public function getLastException();

	/**
	 * Get error code from previous execution
	 *
	 * WARNING: This error code is database specific.
	 *
	 * @return int
	 */
	public function getErrorCode();

	/**
	 * Get error message from previous execution
	 *
	 * WARNING: This error message is database specific.
	 *
	 * @return string
	 */
	public function getErrorMessage();

	/**
	 * Get specific Factory builder for each driver
	 *
	 * Builder factory used to generate SQL for each driver or database. For maximum portability any query should build based on object style.
	 * This factory will able to create SELECT, INSERT, UPDATE, DELETE statement easily and will protect against query injection.
	 *
	 * @return \Lh\Db\Builders\IFactory
	 */
	public function getBuilderFactory();

	/**
	 * Get Platform object for each driver
	 *
	 * Platform object used in conjunction with Builder Factory to provide portability between database engine. Platform object is responsible for
	 * escaping any value and quoting it.
	 *
	 * @return IPlatform
	 */
	public function getPlatform();

	/**
	 * Open connection
	 *
	 * @return bool
	 */
	public function open();

	/**
	 * Close connection
	 *
	 * @return bool
	 */
	public function close();

	/**
	 * Execute given query and return a Query object.
	 *
	 * @param string|Builders\ISql $query
	 * @param int                  $fetchMode
	 *
	 * @return IQuery
	 */
	public function query($query, $fetchMode = IQuery::FETCH_ASSOC);

	/**
	 * Prepare query for multiple execution
	 *
	 * Prepare query for repeated execution and return statement object for further processing. It's a common sense to provide parameter in the query.
	 * Parameter are driver dependent please use SQL builder for preparing the SQL for portability.
	 *
	 * @param string|Builders\ISql $query
	 * @param array                $driverOptions
	 *
	 * @return IStatement
	 */
	public function prepareQuery($query, $driverOptions = array());

	/**
	 * Change Database / schema
	 *
	 * @param string $dbName
	 *
	 * @return bool
	 */
	public function changeDb($dbName);

	/**
	 * Begin transaction
	 *
	 * @return bool
	 */
	public function beginTransaction();

	/**
	 * Commit previous transaction
	 *
	 * @return bool
	 */
	public function commitTransaction();

	/**
	 * Rollback previous transaction
	 *
	 * @return bool
	 */
	public function rollbackTransaction();

	/**
	 * Return last auto-generated ID from last executed query or last value from sequence
	 *
	 * @param null|string $sequenceName
	 *
	 * @return int
	 */
	public function lastInsertId($sequenceName = null);

	/**
	 * Get column(s) name from given table
	 *
	 * This will retrieve all column(s) name from a table in a database.
	 * NOTE: This method will be obsoleted when a metadata feature added into LH Framework since it's only retrieve column name instead of column definition
	 *
	 * @param string $tableName
	 *
	 * @return string[]
	 */
	public function getColumnNames($tableName);

	/**
	 * Call native function provided by original php connector. Please refer to connector documentation for available method(s) and their parameter(s)
	 * Object call or function call supported by this method
	 *
	 * @param string $methodName
	 * @param array $parameters
	 *
	 * @return mixed|void
	 */
	public function callNativeFunction($methodName, $parameters);
}

// End of File: IAdapter.php 