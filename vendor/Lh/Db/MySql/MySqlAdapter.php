<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MySql;

use Exception;
use Lh\Db\AdapterBase;
use Lh\Db\Builders\ISql;
use Lh\Db\IPlatform;
use Lh\Db\IQuery;
use Lh\Db\IStatement;
use Lh\Db\MySql\Builders\MySqlFactory;
use Lh\Db\Query;
use Lh\Db\DbException;

/**
 * Class MySqlAdapter
 *
 * Adapter for mysql driver (default MySQL driver for PHP)
 *
 * @obsoleted at PHP 5.5
 * @method resource getNativeConnector()
 * @package Lh\Db\MySql
 */
class MySqlAdapter extends AdapterBase {
	/** @var int MySQL port */
	protected $port = 3306;
	/** @var string Socket file location (UNIX Only) */
	protected $socket = null;
	/** @var bool New link flag for opening connection */
	protected $newLink = false;
	/** @var int Flags used in opening connection */
	protected $clientFlags = 0;
	/** @var MySqlFactory MySQL factory instance */
	private $factory;
	/** @var MySqlPlatform MySQL Platform instance */
	private $platform;

	/**
	 * Create new instance of MySqlAdapter
	 *
	 * @param string $server
	 * @param string $username
	 * @param string $password
	 * @param null   $dbName
	 * @param array  $options
	 */
	public function __construct($server, $username, $password, $dbName = null, array $options = null) {
		parent::__construct($server, $username, $password, $dbName, $options);
		$this->methodPrefix = "mysql_";
	}

	/**
	 * Get driver name
	 *
	 * @return string
	 */
	public function getName() {
		return "MySQL";
	}

	/**
	 * Used to detect whether current PHP Installation support this adapter or not. If current server don't support requested driver then AdapterManager will throw
	 * an exception. This is a safety function to ensure that your web application will work with given adapter(s) configuration.
	 *
	 * @return bool
	 */
	public function isDriverAvailable() {
		return extension_loaded("mysql");
	}


	/**
	 * Get error code from previous execution
	 *
	 * WARNING: This error code is database specific.
	 *
	 * @return int
	 */
	public function getErrorCode() {
		if (is_resource($this->nativeConnector) && ($code = mysql_errno($this->nativeConnector)) != null) {
			return $code;
		}
		if ($this->getLastException() != null) {
			return $this->getLastException()->getCode();
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
		if (is_resource($this->nativeConnector) && ($msg = mysql_error($this->nativeConnector)) != null) {
			return $msg;
		}
		if ($this->getLastException() != null) {
			return $this->getLastException()->getMessage();
		}

		return null;
	}

	/**
	 * Get MySQL Factory builder
	 *
	 * Builder factory used to generate SQL for each driver or database. For maximum portability any query should build based on object style.
	 * This factory will able to create SELECT, INSERT, UPDATE, DELETE statement easily and will protect against query injection.
	 *
	 * @return MySqlFactory
	 */
	public function getBuilderFactory() {
		if ($this->factory === null) {
			$this->factory = new MySqlFactory($this);
		}

		return $this->factory;
	}

	/**
	 * Get MySqlPlatform
	 *
	 * Platform object used in conjunction with Builder Factory to provide portability between database engine. Platform object is responsible for
	 * escaping any value and quoting it.
	 *
	 * @return MySqlPlatform
	 */
	public function getPlatform() {
		if ($this->platform === null) {
			$this->platform = new MySqlPlatform($this);
		}

		return $this->platform;
	}

	/**
	 * Prepare connect options
	 *
	 * @param array $options
	 */
	protected function prepareOptions($options) {
		if (isset($options["port"]) && is_numeric($options["port"])) {
			$this->port = (int)$options["port"];
		}
		if (isset($options["socket"]) && is_string($options["socket"]) && !empty($options["socket"])) {
			$this->socket = $options["socket"];
		}
		if (isset($options["newLink"]) && is_bool($options["newLink"])) {
			$this->newLink = $options["newLink"];
		}
		if (isset($options["flags"]) && is_int($options["flags"])) {
			$this->clientFlags = $options["flags"];
		}
	}

	/**
	 * Create driver specific exception object
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previousException
	 *
	 * @return MySqlException
	 */
	protected function createException($message, $code = 0, Exception $previousException = null) {
		return new MySqlException($message, $code, $previousException);
	}

	/**
	 * Actual connect method
	 *
	 * @return bool
	 */
	protected function _open() {
		if ($this->socket !== null) {
			$this->nativeConnector = @mysql_connect(":" . $this->socket, $this->username, $this->password, $this->newLink, $this->clientFlags);
		} else {
			$this->nativeConnector = @mysql_connect($this->server . ":" . $this->port, $this->username, $this->password, $this->newLink, $this->clientFlags);
		}
		if (mysql_errno($this->nativeConnector) != 0) {
			return false;
		}

		if ($this->dbName != null) {
			mysql_select_db($this->dbName, $this->nativeConnector);
			if (mysql_errno($this->nativeConnector) != 0) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Actual close method
	 *
	 * @return bool
	 */
	protected function _close() {
		return mysql_close($this->nativeConnector);
	}

	/**
	 * Actual query method
	 *
	 * @param string $query
	 * @param int    $fetchMode
	 *
	 * @return MySqlQuery|Query|null
	 * @throws MySqlException
	 */
	protected function _query($query, $fetchMode) {
		$result = mysql_query($query, $this->nativeConnector);

		if ($result === false) {
			return null;
		} else if (is_resource($result)) {
			// For SELECT, SHOW, DESCRIBE, EXPLAIN and other statements returning resultset, mysql_query() returns a resource on success, or FALSE on error.
			return new MySqlQuery($result, $fetchMode);
		} else if ($result === true) {
			// For other type of SQL statements, INSERT, UPDATE, DELETE, DROP, etc, mysql_query() returns TRUE on success or FALSE on error.
			return new MySqlQuery(null, IQuery::FETCH_NONE, mysql_affected_rows($this->nativeConnector));
		} else {
			// Unknown return type
			throw new MySqlException("Unexpected return type from mysql_query() call. We got: " . get_class($result), -1);
		}
	}

	/**
	 * MySQL driver don't support prepared query
	 *
	 * @param string|ISql $query
	 * @param array       $driverOptions
	 *
	 * @return IStatement|void
	 * @throws MySqlException
	 */
	public function prepareQuery($query, $driverOptions = array()) {
		throw new MySqlException("MySql driver doesn't support prepared statement, consider use MySqli or PDO MySQL.");
	}

	/**
	 * MySQL driver don't support prepared query
	 *
	 * @param string $query
	 * @param array  $driverOptions
	 *
	 * @return IStatement|void
	 * @throws MySqlException
	 */
	protected function _prepareQuery($query, $driverOptions) {
		throw new MySqlException("MySql driver doesn't support prepared statement, consider use MySqli or PDO MySQL.");
	}

	/**
	 * Actual begin transaction method
	 *
	 * @return bool
	 */
	protected function _beginTransaction() {
		$result = $this->query("BEGIN");

		return $result !== null;
	}

	/**
	 * Actual commit transaction method
	 *
	 * @return bool
	 */
	protected function _commitTransaction() {
		$result = $this->query("COMMIT");

		return $result !== null;
	}

	/**
	 * Actual rollback transaction method
	 *
	 * @return bool
	 */
	protected function _rollbackTransaction() {
		$result = $this->query("ROLLBACK");

		return $result !== null;
	}

	/**
	 * Return last auto-generated ID from last executed query or last value from sequence
	 *
	 * MySql don't support sequence therefore $sequenceName will be ignored and always return last insert ID from INSERT statement
	 *
	 * @param null|string $sequenceName
	 *
	 * @return int|null
	 */
	public function lastInsertId($sequenceName = null) {
		$result = $this->query("SELECT LAST_INSERT_ID()");
		if ($result instanceof Query) {
			$row = $result->fetchRow();
			return $row[0];
		} else {
			return null;
		}
	}

	/**
	 * Get column(s) name from given table
	 *
	 * This will retrieve all column(s) name from a table in a database.
	 * NOTE: This method will be obsoleted when a metadata feature added into LH Framework since it's only retrieve column name instead of column definition
	 *
	 * @param string $tableName
	 *
	 * @throws MySqlException
	 *
	 * @return string[]
	 */
	public function getColumnNames($tableName) {
		$query = $this->query("DESCRIBE " . $this->getPlatform()->quoteIdentifier($tableName));
		if ($query == null) {
			if ($this->getErrorMessage() != null) {
				$innerException = $this->createException($this->getErrorMessage(), $this->getErrorCode());
			} else {
				$innerException = null;
			}

			throw $this->createException("Failed to retrieve column(s) detail using DESCRIBE query. Please check your connection", 0, $innerException);
		}

		$columns = array();
		foreach ($query->fetchAll() as $row) {
			$columns[] = $row["Field"];
		}

		return $columns;
	}
}

// End of File: MySqlAdapter.php 