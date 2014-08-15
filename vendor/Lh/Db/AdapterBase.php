<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db;

use Exception;
use Lh\ApplicationException;
use Lh\Collections\Dictionary;
use Lh\Db\Builders\ISelect;
use Lh\Db\Builders\ISql;
use Lh\Db\DbException;
use Lh\Db\IQuery;
use Lh\Exceptions\MethodNotFoundException;
use ReflectionClass;

/**
 * Class AdapterBase
 *
 * Base class of specific native PHP driver
 *
 * @package Lh\Db
 */
abstract class AdapterBase implements IAdapter {
	/** @var string Server location or File location */
	protected $server;
	/** @var string Identity used to connect to server  */
	protected $username;
	/** @var string Credential used to connect to server */
	protected $password;
	/** @var string Database or schema name */
	protected $dbName;
	/** @var mixed This will store reference to native object which communicating with Database layer */
	protected $nativeConnector;
	/** @var bool Connection opened flag */
	private $opened = false;
	/**
	 * Prefix for calling native function. Although AdapterBase provide most commonly used method there is a time when specific method or function existed for specific purpose.
	 * This prefix will be appended when NATIVE FUNCTION is called either by callNativeFunction or by magic method __call not native method from object.
	 * Example mysql_* function(s) can be called by MySqlAdapter object in these way (assuming $obj is MySqlAdapter object):
	 *  1. $obj->get_host_info()
	 *  2. $obj->callNativeFunction("get_host_info")
	 *  3. $obj->callNativeFunction("mysql_get_host_info")
	 *
	 * Both of example above will call mysql_get_host_info
	 *
	 * @var string
	 */
	protected $methodPrefix;
	/** @var ReflectionClass Reflection of native connector object. Used to call method directly from native object */
	private $reflection;
	/** @var DbException store any exception occurred while executing query */
	private $lastException;
	/** @var IStatement Previous executed statement */
	private $lastStatement;

	/**
	 * Create new instance on adapter
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
	 * Get native connector
	 *
	 * @return mixed
	 */
	public function getNativeConnector() {
		if ($this->nativeConnector === null) {
			$this->open();
		}

		return $this->nativeConnector;
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
	 * Get method prefix used in native fuction call
	 *
	 * @return string
	 */
	public function getMethodPrefix() {
		return $this->methodPrefix;
	}

	/**
	 * Get exception occurred from previous execution
	 *
	 * @return DbException
	 */
	public function getLastException() {
		return $this->lastException;
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
	 * Create appropriate exception for specific driver
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previousException
	 *
	 * @return DbException
	 */
	protected abstract function createException($message, $code = 0, Exception $previousException = null);

	/**
	 * Open connection
	 *
	 * Open connection database server / engine. Any resource or object represent the connection will be stored at nativeConnector property
	 * NOTE: This method only bootstrap for open connection. Actual process implemented by _open()
	 *
	 * @see AdapterBase::getNativeConnector()
	 * @return bool
	 * @throws \Lh\Db\DbException
	 */
	public function open() {
		if ($this->isOpened()) {
			return true;
		}

		try {
			$this->lastException = null;
			$this->opened = $this->_open();
			if ($this->nativeConnector == null) {
				// Invalid Driver
				throw new ApplicationException(sprintf("Invalid driver implementation for %s. Native driver is not created after calling _open()", get_class($this)));
			}

			return $this->opened;
		} catch (DbException $ex) {
			$this->lastException = $ex;

			return false;
		} catch (Exception $ex) {
			$this->lastException = $ex;

			return false;
		}
	}

	/**
	 * Connect to database
	 *
	 * Actual process which opening connection to database server / engine.
	 * This method must supply native object into nativeConnector
	 *
	 * @return bool
	 */
	protected abstract function _open();

	/**
	 * Close connection
	 *
	 * Close underlying connection. This method provided just for your convenience. PHP will automatically closed connection.
	 * NOTE: This method only bootstrap for closing connection. Actual implementation at _close()
	 *
	 * @return bool
	 */
	public function close() {
		if (!$this->isOpened()) {
			return false;
		}

		if ($this->_close()) {
			$this->opened = false;
			$this->nativeConnector = null;

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Actual process for closing connection
	 *
	 * @return bool true if connection successfully closed
	 */
	protected abstract function _close();

	/**
	 * Execute given query and return a Query object.
	 *
	 * @param string|Builders\ISql $query
	 * @param int                  $fetchMode
	 *
	 * @return IQuery
	 */
	public function query($query, $fetchMode = IQuery::FETCH_ASSOC) {
		try {
			if (!$this->isOpened() && !$this->open()) {
				return null;
			}
			$this->lastException = null;

			if (is_string($query)) {
				return $this->_query($query, $fetchMode);
			} else if ($query instanceof ISql) {
				return $this->_query($query->compile(), $fetchMode);
			} else {
				throw new \InvalidArgumentException("Invalid argument for query. Expecting string or ISql object, got: " . get_class($query));
			}
		} catch (DbException $ex) {
			$this->lastException = $ex;

			return null;
		} catch (Exception $ex) {
			$this->lastException = $ex;

			return null;
		}
	}

	/**
	 * Actual process sending query to database server / engine.
	 *
	 * This method will return ReaderBase object if it was SELECT query otherwise bool if success.
	 * Every failed operation should return false
	 *
	 * @param string $query
	 * @param int    $fetchMode
	 *
	 * @return Query
	 */
	protected abstract function _query($query, $fetchMode);

	/**
	 * Prepare query for multiple execution
	 *
	 * Prepare query for repeated execution and return statement object for further processing. It's a common sense to provide parameter in the query.
	 * Parameter are driver dependent please use SQL builder for preparing the SQL for portability.
	 *
	 * @param string|ISql $query
	 * @param array       $driverOptions
	 *
	 * @return IStatement
	 */
	public function prepareQuery($query, $driverOptions = array()) {
		try {
			if (!$this->isOpened() && !$this->open()) {
				return null;
			}
			$this->lastException = null;
			if ($this->lastStatement !== null) {
				$this->lastStatement->close();
			}

			if (is_string($query)) {
				$this->lastStatement = $this->_prepareQuery($query, $driverOptions);
			} else if ($query instanceof ISql) {
				$parameters = new Dictionary();
				$this->lastStatement = $this->_prepareQuery($query->compileWithParameters($parameters), $driverOptions);
				if ($this->lastStatement != null) {
					foreach ($parameters->getKeys() as $key) {
						$this->lastStatement->bindValue($key, $parameters->get($key));
					}
				}
			} else {
				throw new \InvalidArgumentException("Invalid argument for query. Expecting string or ISql object, got: " . get_class($query));
			}

			return $this->lastStatement;
		} catch (DbException $ex) {
			$this->lastException = $ex;

			return null;
		} catch (Exception $ex) {
			$this->lastException = $ex;

			return null;
		}
	}

	/**
	 * Actual method for preparing prepared query
	 *
	 * @param string $query
	 * @param array  $driverOptions
	 *
	 * @return IStatement
	 */
	protected abstract function _prepareQuery($query, $driverOptions);

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
		$this->lastException = null;

		return $this->_beginTransaction();
	}

	/**
	 * Actual implementation of Begin Transaction
	 *
	 * @return bool
	 */
	protected abstract function _beginTransaction();

	/**
	 * Commit previous transaction
	 *
	 * @return bool
	 */
	public function commitTransaction() {
		if (!$this->isOpened()) {
			return false;
		}
		$this->lastException = null;

		return $this->_commitTransaction();
	}


	/**
	 * Actual implementation of Commit Transaction
	 *
	 * @return bool
	 */
	protected abstract function _commitTransaction();

	/**
	 * Rollback previous transaction
	 *
	 * @return bool
	 */
	public function rollbackTransaction() {
		if (!$this->isOpened()) {
			return false;
		}
		$this->lastException = null;

		return $this->_rollbackTransaction();
	}


	/**
	 * Actual implementation of Rollback Transaction
	 *
	 * @return bool
	 */
	protected abstract function _rollbackTransaction();

	/**
	 * Call native function which provided by PHP native connector object.
	 *
	 * This will be useful if native object have some specialized method for performing difficult task which not offered by framework
	 * or the task is specific for a database connector. Example: SQL Server support for XML data type
	 *
	 * @param string $methodName
	 * @param array  $parameters
	 *
	 * @throws \Lh\Exceptions\MethodNotFoundException
	 * @throws \RuntimeException
	 * @throws DbException
	 * @return mixed|void
	 */
	public function callNativeFunction($methodName, $parameters) {
		if (!$this->isOpened() && !$this->open()) {
			throw $this->createException("Unable to open connection after calling open()!");
		}

		if (is_resource($this->nativeConnector)) {
			// Fallback since native connector is resource type...
			if (!empty($this->methodPrefix) && ($pos = strpos($methodName, $this->methodPrefix)) !== 0) {
				$methodName = $this->methodPrefix . $methodName;
			}

			if (!function_exists($methodName)) {
				throw new MethodNotFoundException($methodName, "Unable to find function '$methodName' at resource: " . get_resource_type($this->nativeConnector));
			}
			if (!is_array($parameters)) {
				$parameters = array($parameters);
			}

			return call_user_func_array($methodName, $parameters);
		} else {
			if (is_object($this->nativeConnector)) {
				if ($this->reflection === null) {
					$this->reflection = new ReflectionClass($this->nativeConnector);
				}

				try {
					$methodInfo = $this->reflection->getMethod($methodName);

					return $methodInfo->invoke($this->nativeConnector, $parameters);
				} catch (\ReflectionException $ex) {
					throw new MethodNotFoundException($methodName, "Unable to find method '$methodName' from object of: " . get_class($this->nativeConnector));
				}
			} else {
				throw new \RuntimeException("Unable to determine native method calling from native connector. Native connector type: " . $this->nativeConnector);
			}
		}

	}

	/// REGION		- Magic methods for calling native function
	/**
	 * Provide dynamic method call using magic method
	 *
	 * Dynamic method call intended to call native method or native function offered by the driver itself.
	 * This done by calling callNativeFunction()
	 *
	 * @param string $name
	 * @param mixed  $arguments
	 *
	 * @return mixed|void
	 */
	public function __call($name, $arguments) {
		return $this->callNativeFunction($name, $arguments);
	}
	/// END REGION	- Magic methods
}

// End of File: AdapterBase.php 
