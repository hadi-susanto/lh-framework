<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Pdo;

use Exception;
use Lh\Collections\Dictionary;
use Lh\Db\Builders\ISql;
use Lh\Db\IQuery;
use Lh\Exceptions\MethodNotFoundException;
use PDO;
use PDOException;
use ReflectionClass;

/**
 * Class PdoAdapterBase
 *
 * Base class of specific PDO driver
 *
 * @package Lh\Db\Pdo
 */
abstract class PdoAdapterBase implements IPdoAdapter {
	/** @var string Server location or File location */
	protected $server;
	/** @var string Identity used to connect to server */
	protected $username;
	/** @var string Credential used to connect to server */
	protected $password;
	/** @var string Database or schema name */
	protected $dbName;
	/**
	 * PDO Driver init options
	 *
	 * Init options passed in PDO constructor. These value should be changed by prepareOptions().
	 * Please consult vendor about setting init options via prepareOptions. Commonly you must specify 'init' key at your database configuration file
	 *
	 * @see AdapterBase::prepareOptions()
	 * @var array
	 */
	protected $initOptions = array();
	/** @var PDO This PHP PDO object. Native driver which communicating with database layer */
	protected $nativeConnector;
	/** @var bool Connection opened flag */
	private $opened = false;
	/** @var \Lh\Db\DbException store any exception occurred while executing query */
	private $lastException;
	/** @var string Store SQL STATE value. Common between PDO Driver */
	private $sqlState;
	/** @var int Store database error code. Differ between database driver */
	private $errorCode;
	/** @var string Store database error message. Differ between database driver */
	private $errorMessage;
	/** @var ReflectionClass Reflection of PDO object. Used to call method directly from PDO object */
	private $reflection;

	/**
	 * Get PDO init options
	 *
	 * @return array
	 */
	public function getInitOptions() {
		return $this->initOptions;
	}

	/**
	 * PDO Adapter constructor will have similar signature with regular Adapter class.
	 *
	 * @param string      $server
	 * @param string      $username
	 * @param string      $password
	 * @param null|string $dbName
	 * @param array       $options
	 */
	public function __construct($server, $username, $password, $dbName = null, array $options = null) {
		$this->server = $server;
		$this->username = $username;
		$this->password = $password;
		$this->dbName = $dbName;

		$this->prepareOptions($options);
	}

	/**
	 * Check whether current connection opened or not
	 *
	 * @return bool
	 */
	public function isOpened() {
		return $this->opened;
	}

	/**
	 * Get PDO object as native connector
	 *
	 * @return PDO
	 */
	public function getNativeConnector() {
		if ($this->nativeConnector === null) {
			$this->open();
		}
		return $this->nativeConnector;
	}

	/**
	 * Get exception occurred from previous execution
	 *
	 * @return \Lh\Db\DbException
	 */
	public function getLastException() {
		return $this->lastException;
	}

	/**
	 * Get error code from previous execution
	 *
	 * WARNING: This error code is database specific.
	 *
	 * @return int
	 */
	public function getErrorCode() {
		if (!empty($this->errorCode)) {
			return $this->errorCode;
		}
		if ($this->lastException != null) {
			return $this->lastException->getCode();
		}

		return 0;
	}

	/**
	 * Get error message from previous execution
	 *
	 * WARNING: This error message is database specific.
	 *
	 * @return string
	 */
	public function getErrorMessage() {
		if (!empty($this->errorMessage)) {
			return $this->errorMessage;
		}
		if ($this->lastException != null) {
			return $this->lastException->getMessage();
		}

		return null;
	}

	/**
	 * Get SQL STATE from previous execution
	 *
	 * IMPORTANT: These value are independent from database engine. SQL STATE retrieved from PDO Driver therefore its value will be same across PDO driver
	 *
	 * @return string
	 */
	public function getSqlState() {
		return $this->sqlState;
	}

	/**
	 * Reset error counter and flag
	 *
	 * Error counter and flag must be reset before any execution.
	 */
	private function resetError() {
		$this->lastException = null;
		$this->sqlState = PDO::ERR_NONE;
		$this->errorCode = 0;
		$this->errorMessage = null;
	}

	/**
	 * Set PDO Driver attribute
	 *
	 * Set attribute for current PDO object. Please refer to PDO documentation for available attribute and usage.
	 * Attribute key: PDO::ATTR_*
	 * Attribute value: PDO::* (any constant associated with the key)
	 *
	 * @param int $key
	 * @param int $value
	 *
	 * @return bool
	 * @throws \Lh\Db\DbException
	 */
	public function setAttribute($key, $value) {
		if (!$this->isOpened() && !$this->open()) {
			throw $this->createException("Unable to set PDO attribute! PDO object can't be instantiated.");
		}

		return $this->nativeConnector->setAttribute($key, $value);
	}

	/**
	 * Get PDO Driver attribute
	 *
	 * Retrieve attribute value from current PDO object. Please refer to PDO documentation for available attribute and usage
	 * Attribute key: PDO::ATTR_*
	 *
	 * @param int $key
	 *
	 * @return mixed
	 * @throws \Lh\Db\DbException
	 */
	public function getAttribute($key) {
		if (!$this->isOpened() && !$this->open()) {
			throw $this->createException("Unable to get PDO attribute! PDO object can't be instantiated.");
		}

		return $this->nativeConnector->getAttribute($key);
	}

	/**
	 * Prepare additional options for specific driver
	 *
	 * @param array $options
	 *
	 * @return void
	 */
	protected abstract function prepareOptions($options);

	/**
	 * Generate DSN string for specific driver. Each driver maybe have their specific key which can be achieved by using options from application config
	 *
	 * @return string
	 */
	protected abstract function generateDsn();

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
	 * Open connection to database using PDO object.
	 *
	 * @return bool
	 */
	public function open() {
		if ($this->isOpened()) {
			return true;
		}

		try {
			$this->resetError();
			$this->nativeConnector = new PDO($this->generateDsn(), $this->username, $this->password, $this->initOptions);
			$this->opened = true;

			return $this->opened;
		} catch (PDOException $ex) {
			$this->lastException = $ex;

			$tokens = explode(" ", $ex->getMessage(), 3);
			$this->sqlState = substr($tokens[0], 9, 5);
			$this->errorCode = $ex->getCode();
			$this->errorMessage = $tokens[2];

			return false;
		} catch (Exception $ex) {
			$this->lastException = $ex;
			$this->errorCode = $ex->getCode();
			$this->errorMessage = $ex->getMessage();

			return false;
		}
	}

	/**
	 * Close PDO Driver
	 *
	 * @return bool
	 */
	public function close() {
		if (!$this->isOpened()) {
			return false;
		}

		$this->nativeConnector = null;
		$this->opened = false;

		return true;
	}

	/**
	 * Execute given query and return a Query object.
	 *
	 * @param ISql|string $query
	 * @param int         $fetchMode
	 *
	 * @return IQuery|PdoQuery|null
	 */
	public function query($query, $fetchMode = IQuery::FETCH_ASSOC) {
		try {
			if (!$this->isOpened() && !$this->open()) {
				return null;
			}
			$this->resetError();

			if (is_string($query)) {
				$result = $this->nativeConnector->query($query);
			} else if ($query instanceof ISql) {
				$result = $this->nativeConnector->query($query->compile());
			} else {
				throw new \InvalidArgumentException("Invalid argument for query. Expecting string or ISelect object, got: " . get_class($query));
			}

			if ($result instanceof \PDOStatement) {
				return $this->createQuery($result, $fetchMode);
			} else {
				$errorInfo = $this->nativeConnector->errorInfo();
				$this->sqlState = $errorInfo[0];
				$this->errorCode = $errorInfo[1];
				$this->errorMessage = $errorInfo[2];
				unset($errorInfo);

				return null;
			}
		} catch (PDOException $ex) {
			$this->lastException = $ex;
			$tokens = explode(" ", $ex->getMessage(), 3);
			$this->sqlState = substr($tokens[0], 9, 5);
			$this->errorCode = $ex->getCode();
			$this->errorMessage = $tokens[2];

			return null;
		} catch (Exception $ex) {
			$this->lastException = $ex;
			$this->errorCode = $ex->getCode();
			$this->errorMessage = $ex->getMessage();

			return null;
		}
	}

	/**
	 * Prepare query for multiple execution
	 *
	 * Prepare query for repeated execution and return statement object for further processing. It's a common sense to provide parameter in the query.
	 * Parameter are driver dependent please use SQL builder for preparing the SQL for portability.
	 *
	 * @param string|ISql $query
	 * @param array       $driverOptions
	 *
	 * @return \Lh\Db\IStatement|null
	 */
	public function prepareQuery($query, $driverOptions = array()) {
		try {
			if (!$this->isOpened() && !$this->open()) {
				return null;
			}
			$this->resetError();

			$parameters = new Dictionary();
			if (is_string($query)) {
				$result = $this->nativeConnector->prepare($query, $driverOptions);
			} else if ($query instanceof ISql) {
				$result = $this->nativeConnector->prepare($query->compileWithParameters($parameters), $driverOptions);
			} else {
				throw new \InvalidArgumentException("Invalid argument for query. Expecting string or ISelect object, got: " . get_class($query));
			}

			if ($result instanceof \PDOStatement) {
				$statement = $this->createStatement($result);
				foreach ($parameters->getKeys() as $key) {
					// Prevent calling DictionaryIterator
					$statement->bindValue($key, $parameters->get($key));
				}

				return $statement;
			} else {
				$errorInfo = $this->nativeConnector->errorInfo();
				$this->sqlState = $errorInfo[0];
				$this->errorCode = $errorInfo[1];
				$this->errorMessage = $errorInfo[2];
				unset($errorInfo);

				return null;
			}

		} catch (PDOException $ex) {
			$this->lastException = $ex;
			$tokens = explode(" ", $ex->getMessage(), 3);
			$this->sqlState = substr($tokens[0], 9, 5);
			$this->errorCode = $ex->getCode();
			$this->errorMessage = $tokens[2];

			return null;
		} catch (Exception $ex) {
			$this->lastException = $ex;
			$this->errorCode = $ex->getCode();
			$this->errorMessage = $ex->getMessage();

			return null;
		}
	}

	/**
	 * Create specialized PdoQuery object
	 *
	 * @param \PDOStatement $statement
	 * @param int           $fetchMode
	 *
	 * @return PdoQuery
	 */
	protected abstract function createQuery(\PDOStatement &$statement, &$fetchMode);

	/**
	 * Create specialized LhPdoStatement object
	 *
	 * @param \PDOStatement $statement
	 *
	 * @return IPdoStatement
	 */
	protected abstract function createStatement(\PDOStatement &$statement);

	/**
	 * Change Database / schema
	 *
	 * @param string $dbName
	 *
	 * @return bool
	 */
	public function changeDb($dbName) {
		$result = $this->query("USE " . $dbName);

		return $result !== null;
	}

	/**
	 * Begin transaction
	 *
	 * @return bool
	 */
	public function beginTransaction() {
		if (!$this->isOpened() && !$this->open()) {
			return false;
		}
		$this->resetError();

		return $this->nativeConnector->beginTransaction();
	}

	/**
	 * Commit previous transaction
	 *
	 * @return bool
	 */
	public function commitTransaction() {
		if (!$this->isOpened()) {
			return false;
		}
		$this->resetError();

		return $this->nativeConnector->commit();
	}

	/**
	 * Rollback previous transaction
	 *
	 * @return bool
	 */
	public function rollbackTransaction() {
		if (!$this->isOpened()) {
			return false;
		}
		$this->resetError();

		return $this->nativeConnector->rollBack();
	}

	/**
	 * Return ID which used / generated from last INSERT statement. This ID will retrieved from auto increment value or sequence
	 *
	 * @param null|string $name
	 *
	 * @throws \Lh\Db\DbException
	 * @return int
	 */
	public function lastInsertId($name = null) {
		if (!$this->isOpened()) {
			throw $this->createException("Unable to get last insert id! There is no connection");
		}

		return $this->nativeConnector->lastInsertId($name);
	}

	/**
	 * Call method from PDO object
	 *
	 * Call native function provided by original php connector (PDO object in this case). Please refer to connector documentation for available method(s) and their parameter(s)
	 *
	 * @param string $methodName
	 * @param array  $parameters
	 *
	 * @throws \Lh\Db\DbException
	 * @throws \Lh\Exceptions\MethodNotFoundException
	 * @return mixed|void
	 */
	public function callNativeFunction($methodName, $parameters) {
		if (!$this->isOpened() && !$this->open()) {
			throw $this->createException("Unable to call PDO native method! PDO object can't be instantiated.");
		}

		if ($this->reflection === null) {
			$this->reflection = new ReflectionClass($this->nativeConnector);
		}

		try {
			$methodInfo = $this->reflection->getMethod($methodName);

			return $methodInfo->invoke($this->nativeConnector, $parameters);
		} catch (\ReflectionException $ex) {
			throw new MethodNotFoundException($methodName, "Unable to find method '$methodName' from object of: " . get_class($this->nativeConnector));
		}
	}

	/**
	 * Provide dynamic method call using magic method
	 *
	 * Dynamic method call intended to call native method from PDO object. This done by calling callNativeFunction()
	 *
	 * @param string $name
	 * @param mixed  $arguments
	 *
	 * @return mixed|void
	 */
	public function __call($name, $arguments) {
		return $this->callNativeFunction($name, $arguments);
	}
}

// End of File: PdoAdapterBase.php
