<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Postgre;

use Lh\Db\IQuery;
use Lh\Db\IStatement;

/**
 * Class PostgreStatement
 *
 * @package Lh\Db\Postgre
 */
class PostgreStatement implements IStatement {
	/** @var resource Pgsql native driver / connection */
	protected $nativeDriver;
	/** @var string Statement name */
	protected $statementName;
	/** @var string[] Parameter name collection */
	private $paramNames = array();
	/** @var array Parameter value collection */
	private $paramValues = array();
	/**
	 * @var int[] Parameter type collection
	 *
	 * Refer to IStatement::BIND_TYPE_* for available type(s)
	 */
	private $paramTypes = array();
	/** @var resource Pgsql query result resource link */
	private $nativeReader;

	/**
	 * Create new instance of PostgreStatement
	 *
	 * @param resource $nativeDriver Pgsql driver / connection resource link
	 * @param string   $statementName Statement name
	 */
	public function __construct($nativeDriver, $statementName) {
		$this->nativeDriver = $nativeDriver;
		$this->statementName = $statementName;
	}


	/**
	 * Get native statement object / resource
	 *
	 * NOTE: Since Postgre SQL don't have actual object / resource related to statement then it will return connection resource. Current connection resource is
	 * associated with the statement handle in Postgre server.
	 *
	 * @return resource
	 */
	public function getNativeStatement() {
		return $this->nativeDriver;
	}

	/**
	 * Get error code from previous executed statement
	 *
	 * @return int
	 */
	public function getErrorCode() {
		if (!is_resource($this->nativeReader)) {
			return 0;
		}

		return pg_result_error_field($this->nativeReader, PGSQL_DIAG_SQLSTATE);
	}

	/**
	 * Get error message from previous executed statement
	 *
	 * @return string
	 */
	public function getErrorMessage() {
		if (!is_resource($this->nativeReader)) {
			return 0;
		}

		return pg_result_error($this->nativeReader);
	}

	/**
	 * Get all parameters for current statement
	 *
	 * @return array
	 */
	public function getParameters() {
		return $this->paramValues;
	}

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
	public function bindValue($name, $value, $type = IStatement::BIND_TYPE_AUTO) {
		if (!in_array($name, $this->paramNames)) {
			$this->paramNames[] = $name;
		}
		$this->paramValues[$name] = $value;
		$this->paramTypes[$name] = $type;
	}

	/**
	 * Remove all previous binds
	 *
	 * @return void
	 */
	public function clearBinds() {
		$this->paramNames = array();
		$this->paramValues = array();
		$this->paramTypes = array();
	}

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
	public function execute($fetchMode = IQuery::FETCH_ASSOC) {
		$result = pg_execute($this->nativeReader, $this->statementName, array_values($this->paramValues));

		if (is_resource($result)) {
			return new PostgreQuery($result, $fetchMode);
		} else {
			return null;
		}
	}

	/**
	 * Close current statement and release their resources
	 *
	 * IMPORTANT: Based on PHP documentation 'although there is no PHP function for deleting a prepared statement, the SQL DEALLOCATE statement can be used for that purpose'
	 * Therefore this will use SQL query for performing this close. It will use direct pg_query for performance purpose
	 *
	 * @return bool
	 */
	public function close() {
		return is_resource(pg_query($this->nativeReader, "DEALLOCATE " . $this->statementName)) ? true : false;
	}
}

// End of File: PostgreStatement.php 
