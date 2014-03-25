<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Pdo;

use Exception;
use Lh\Db\IQuery;
use Lh\Db\IStatement;
use PDO;

/**
 * Class LhPdoStatement
 *
 * @package Lh\Db\Pdo
 */
abstract class LhPdoStatement implements IPdoStatement {
	/** @var \PDOStatement Native PDO statement object */
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
	 * Create new instance of LhPdoStatement
	 *
	 * This class wrap some common functionality of PDOStatement. Since IPdoStatement derived from IStatement therefore not all PDOStatement feature
	 * available in IStatement.
	 *
	 * @param \PDOStatement $nativeStatement
	 */
	public function __construct(\PDOStatement $nativeStatement) {
		$this->nativeStatement = $nativeStatement;
	}

	/**
	 * Get native statement object / resource
	 *
	 * @return \PDOStatement
	 */
	public function getNativeStatement() {
		return $this->nativeStatement;
	}

	/**
	 * Get SQL STATE value from previous executed statement
	 *
	 * @return string
	 */
	public function getSqlState() {
		$errorInfo = $this->nativeStatement->errorInfo();

		return $errorInfo[0];
	}

	/**
	 * Get error code from previous executed statement
	 *
	 * @return int
	 */
	public function getErrorCode() {
		$errorInfo = $this->nativeStatement->errorInfo();

		return $errorInfo[1];
	}

	/**
	 * Get error message from previous executed statement
	 *
	 * @return string
	 */
	public function getErrorMessage() {
		$errorInfo = $this->nativeStatement->errorInfo();

		return $errorInfo[2];
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
	 * Prepare our parameter for query execution
	 *
	 * Parameter bound to PDOStatement object using bindValue() and param type is auto-detected if not specified
	 *
	 * @return bool
	 */
	private function prepareParameters() {
		if (count($this->paramValues) == 0) {
			return true;
		}

		foreach ($this->paramValues as $name => $value) {
			$type = $this->paramTypes[$name];
			switch ($type) {
				case IStatement::BIND_TYPE_AUTO:
					if (is_numeric($value)) {
						$this->nativeStatement->bindValue($name, $value, PDO::PARAM_INT);
					} else if (is_null($value)) {
						$this->nativeStatement->bindValue($name, null, PDO::PARAM_NULL);
					} else if (is_bool($value)) {
						$this->nativeStatement->bindValue($name, (bool)$value, PDO::PARAM_BOOL);
					} else if (is_resource($value)) {
						$this->nativeStatement->bindValue($name, $value, PDO::PARAM_LOB);
					} else {
						$this->nativeStatement->bindValue($name, $value, PDO::PARAM_STR);
					}
					break;
				case IStatement::BIND_TYPE_INTEGER:
					$this->nativeStatement->bindValue($name, $value, PDO::PARAM_INT);
					break;
				case IStatement::BIND_TYPE_DOUBLE:
					$this->nativeStatement->bindValue($name, $value, PDO::PARAM_INT);
					break;
				case IStatement::BIND_TYPE_NULL:
					$this->nativeStatement->bindValue($name, null, PDO::PARAM_NULL);
					break;
				case IStatement::BIND_TYPE_BOOL:
					$this->nativeStatement->bindValue($name, (bool)$value, PDO::PARAM_BOOL);
					break;
				case IStatement::BIND_TYPE_BLOB:
					$this->nativeStatement->bindValue($name, $value, PDO::PARAM_LOB);
					break;
				case IStatement::BIND_TYPE_STRING:
				default:
					$this->nativeStatement->bindValue($name, $value, PDO::PARAM_STR);
					break;
			}
		}

		return true;
	}

	/**
	 * Execute prepared query
	 *
	 * Execute prepared query with their parameters. Parameter passed using $parameters will override all parameter(s) from
	 * bindValue(). Any class implements this should make sure that clearBinds() called if $parameters is not null.
	 * IMPORTANT: since array() will be evaluated as null then checking must be use === operator instead of ==
	 *
	 * @param null|array $parameters
	 * @param int        $fetchMode
	 *
	 * @throws \Lh\Db\DbException
	 * @return IQuery
	 */
	public function execute($parameters = null, $fetchMode = IQuery::FETCH_ASSOC) {
		if (is_array($parameters)) {
			$this->clearBinds();
			foreach ($parameters as $param => $value) {
				$this->bindValue($param, $value);
			}
		}

		if (!$this->prepareParameters()) {
			throw $this->createException("Failed to prepare / bind parameters into statement object.");
		}

		if ($this->nativeStatement->execute()) {
			return $this->createPdoQuery($this->nativeStatement, $fetchMode);
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
		return $this->nativeStatement->closeCursor();
	}


	/**
	 * Create appropriate exception for specific driver
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previousException
	 *
	 * @return \Lh\Db\DbException
	 */
	protected abstract function createException($message, $code = 0, Exception $previousException = null);

	/**
	 * Create specialized PdoQuery object
	 *
	 * @param \PdoStatement $statement
	 * @param int           $fetchMode
	 *
	 * @return PdoQuery
	 */
	protected abstract function createPdoQuery(\PdoStatement &$statement, &$fetchMode);
}

// End of File: LhPdoStatement.php