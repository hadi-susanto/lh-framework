<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MsSql;

use Lh\Db\IQuery;
use Lh\Db\IStatement;

/**
 * Class MsSqlStatement
 *
 * Sqlsrv statement don't support lazy parameter binding. When we call sqlsrv_prepare() we should supply its parameters. Therefore it's impossible to create
 * statement resource from adapter object. Statement resource will be created by this class.
 *
 * @package Lh\Db\MsSql
 */
class MsSqlStatement implements IStatement {
	/** @var resource Sqlsrv resource link */
	protected $nativeConnector;
	/** @var string Prepared query string */
	protected $query;
	/** @var array Prepared statement options */
	protected $options;
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
	/** @var resource Sqlsrv statement resource link */
	protected $nativeStatement;

	/**
	 * Create new instance of MsSqlStatement
	 *
	 * @param resource $nativeConnector
	 * @param string   $query
	 * @param array    $options
	 */
	public function __construct($nativeConnector, $query, array $options = array()) {
		$this->nativeConnector = $nativeConnector;
		$this->query = $query;
		$this->options = $options;
	}

	/**
	 * Get native statement object / resource
	 *
	 * @return resource
	 */
	public function getNativeStatement() {
		return $this->nativeStatement;
	}

	/**
	 * Get error code from previous executed statement
	 *
	 * @return int
	 */
	public function getErrorCode() {
		$errors = sqlsrv_errors();
		if (is_array($errors)) {
			return $errors[0]["code"];
		}

		return 0;
	}

	/**
	 * Get error message from previous executed statement
	 *
	 * @return string
	 */
	public function getErrorMessage() {
		$errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
		if (is_array($errors)) {
			return $errors[0]["message"];
		}

		return null;
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
	 * IMPORTANT: Each driver may be behave a little differently while bound a value. This framework trying its best to provide similar result
	 *
	 * @param string $name
	 * @param mixed  $value
	 * @param int    $type
	 *
	 * @throws MsSqlException
	 * @return void
	 */
	public function bindValue($name, $value, $type = IStatement::BIND_TYPE_AUTO) {
		if ($this->nativeStatement !== null) {
			throw new MsSqlException("Parameter bindings should be done before any statement execution.");
		}

		if (!in_array($name, $this->paramNames)) {
			$this->paramNames[] = $name;
		}
		$this->paramValues[$name] = $value;
		$this->paramTypes[$name] = $type;
	}

	/**
	 * Remove all previous binds
	 *
	 * @throws MsSqlException
	 * @return void
	 */
	public function clearBinds() {
		if ($this->nativeStatement !== null) {
			throw new MsSqlException("Clearing bindings should be done before any statement execution.");
		}

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
	 * @return MsSqlQuery
	 */
	public function execute($fetchMode = IQuery::FETCH_ASSOC) {
		if ($this->nativeStatement === null) {
			$params = array();
			foreach ($this->paramValues as $name => $value) {
				$params[] = &$value;
			}
			// Prepare statement
			$this->nativeStatement = sqlsrv_prepare($this->nativeConnector, $this->query, $params, $this->options);
		}

		if (!is_resource($this->nativeStatement)) {
			return null;
		}

		// OK new we can execute the statement
		if (sqlsrv_execute($this->nativeStatement)) {
			return new MsSqlQuery($this->nativeStatement, $fetchMode);
		} else {
			return null;
		}
	}

	/**
	 * Close current statement and release their resources
	 *
	 * @return bool
	 */
	public function close() {
		if (is_resource($this->nativeStatement)) {
			return sqlsrv_free_stmt($this->nativeStatement);
		}

		return true;
	}
}

// End of File: MsSqlStatement.php 
