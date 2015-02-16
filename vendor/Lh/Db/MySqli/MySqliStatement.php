<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MySqli;

use Lh\Db\IQuery;
use Lh\Db\IStatement;

/**
 * Class MySqliStatement
 *
 * @package Lh\Db\MySqli
 */
class MySqliStatement implements IStatement {
	/** @var \mysqli_stmt Actual statement object from mysqli */
	protected $nativeStatement;
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

	/**
	 * Create new instance of MySqliStatement
	 *
	 * @param \mysqli_stmt $nativeStatement
	 */
	public function __construct($nativeStatement) {
		$this->nativeStatement = $nativeStatement;
	}

	/**
	 * Get native statement object
	 *
	 * @return \mysqli_stmt
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
		return $this->nativeStatement->errno;
	}

	/**
	 * Get error message from previous executed statement
	 *
	 * @return string
	 */
	public function getErrorMessage() {
		return $this->nativeStatement->error;
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
	 * @param string $name
	 * @param mixed  $value
	 * @param int    $type
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
	 */
	public function clearBinds() {
		$this->paramNames = array();
		$this->paramValues = array();
		$this->paramTypes = array();
	}

	/**
	 * Call bind_param() method in \mysqli_stmt object
	 *
	 * Perform data type detection from $this->paramTypes and prepare proper code for parameter binding. After parameter code completed
	 * then bind_param() method called using call_user_func_array() help. First argument of bind_param is type code and the rest is
	 * parameter value(s) in sequential order (MySQL don't support named parameter)
	 *
	 * @return bool
	 * @throws MySqliException
	 */
	private function prepareParameters() {
		if (count($this->paramValues) == 0) {
			return true;
		}
		// We will call mysqli_stmt->bind_param using call_user_func_array() since no of parameter is dynamic

		// First argument is type
		$args[] = "";
		foreach ($this->paramValues as $name => &$value) {
			$type = $this->paramTypes[$name];
			switch ($type) {
				case IStatement::BIND_TYPE_AUTO:
					if (is_numeric($value)) {
						$args[0] .= "d";	// Double data type
					} else if (is_null($value)) {
						$args[0] .= "i";	// Special treatment for NULL value
						$value = null;
					} else {
						$args[0] .= "s";	// Force as string
					}
					break;
				case IStatement::BIND_TYPE_INTEGER:
					$args[0] .= "i";
					break;
				case IStatement::BIND_TYPE_DOUBLE:
					$args[0] .= "d";
					break;
				case IStatement::BIND_TYPE_NULL:
					$args[0] .= "i";
					$value = null;
					break;
				case IStatement::BIND_TYPE_STRING:
				default:
					$args[0] .= "s";
					break;
			}
			// Subsequent args are value
			$args[] = &$value;
		}

		// Integrity checking
		if (strlen($args[0]) + 1 != count($args)) {
			throw new MySqliException("Integrity check failed, type(s) length doesn't match with parameters.");
		}

		return call_user_func_array(array($this->nativeStatement, "bind_param"), $args);
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
	 * @throws MySqliException
	 * @return MySqliQuery
	 */
	public function execute($fetchMode = IQuery::FETCH_ASSOC) {
		if (!$this->prepareParameters()) {
			throw new MySqliException("Failed to prepare parameters.");
		}

		$result = $this->nativeStatement->execute();
		if ($result === false) {
			return null;
		}

		$result = $this->nativeStatement->get_result();
		if ($result instanceof \mysqli_result) {
			return new MySqliQuery($result, $fetchMode);
		} else {
			return new MySqliQuery(null, IQuery::FETCH_NONE, $this->nativeStatement->affected_rows);
		}
	}

	/**
	 * Close current statement and release their resources
	 *
	 * @return bool
	 */
	public function close() {
		return $this->nativeStatement->close();
	}
}

// End of File: MySqliStatement.php 
