<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db;

/**
 * Interface IStatement
 *
 * Defining contract for statement object. Statement is object that hold prepared SQL query for faster query execution.
 * Benefit using statement instead direct query are:
 *  - Multiple execution using parameter hence query checking only performed once by server
 *  - Prevent query injection since parameter passed using binary instead of text (Driver support)
 *
 * @package Lh\Db
 */
interface IStatement {
	const BIND_TYPE_AUTO = 1;
	const BIND_TYPE_STRING = 2;
	const BIND_TYPE_INTEGER = 3;
	const BIND_TYPE_DOUBLE = 4;
	const BIND_TYPE_BOOL = 5;
	const BIND_TYPE_NULL = 6;
	const BIND_TYPE_BLOB = 7;

	/**
	 * Get native statement object / resource
	 *
	 * @return mixed
	 */
	public function getNativeStatement();

	/**
	 * Get error code from previous executed statement
	 *
	 * @return int
	 */
	public function getErrorCode();

	/**
	 * Get error message from previous executed statement
	 *
	 * @return string
	 */
	public function getErrorMessage();

	/**
	 * Get all parameters for current statement
	 *
	 * @return array
	 */
	public function getParameters();

	/**
	 * Bind a parameter into prepared statement for use before execution.
	 *
	 * IMPORTANT:
	 *  - Each driver may be behave a little differently while bound a value. This framework trying its best to provide similar result.
	 *  - Binding should be done before statement execution.
	 *
	 * @param string $name
	 * @param mixed  $value
	 * @param int    $type
	 *
	 * @return void
	 */
	public function bindValue($name, $value, $type = IStatement::BIND_TYPE_AUTO);

	/**
	 * Remove all previous binds
	 *
	 * @return void
	 */
	public function clearBinds();

	/**
	 * Execute prepared query
	 *
	 * Execute prepared query with their parameters. The parameter(s) should be bound using IStatement::bindValue() method before any execution. Perform binding
	 * after IStatement execution could lead to un-expected error or exception.
	 *
	 * @param int $fetchMode
	 *
	 * @see IStatement::bindValue()
	 *
	 * @return IQuery
	 */
	public function execute($fetchMode = IQuery::FETCH_ASSOC);

	/**
	 * Close current statement and release their resources
	 *
	 * @return bool
	 */
	public function close();
}

// End of File: IStatement.php 
