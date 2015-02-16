<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Postgre;

use Exception;
use Lh\Db\AdapterBase;
use Lh\Db\Postgre\Builders\PostgreFactory;

/**
 * Class PostgreAdapter
 *
 * @method resource getNativeConnector()
 * @package Lh\Db\Postgre
 */
class PostgreAdapter extends AdapterBase {
	/** @var resource Pgsql resource link */
	protected $nativeConnector;
	/** @var int Postgre SQL default port */
	protected $port = 5432;
	/** @var array Array storing connection related data for pg_connect() function */
	protected $connectionInfo;
	/** @var PostgreFactory Postgre SQL factory instance */
	private $factory;
	/** @var PostgrePlatform Postgre SQL Platform instance */
	private $platform;
	/** @var resource Pgsql query resource. It's used to get error code using pg_result_error_field() */
	private $queryResource;

	/**
	 * Create new instance of PostgreAdapter
	 *
	 * @param string $server
	 * @param string $username
	 * @param string $password
	 * @param null   $dbName
	 * @param array  $options
	 */
	public function __construct($server, $username, $password, $dbName = null, array $options = null) {
		parent::__construct($server, $username, $password, $dbName, $options);
		$this->methodPrefix = "pg_";
	}

	/**
	 * Prepare additional options for specific driver
	 *
	 * @param array $options
	 *
	 * @return void
	 */
	protected function prepareOptions($options) {
		// Port is used when opening connection and passed in server name instead of connection info
		if (isset($options["port"]) && is_numeric($options["port"])) {
			$this->port = (int)$options["port"];
		}

		$this->connectionInfo = array();
		$this->connectionInfo[] = "host=" . $this->server;
		$this->connectionInfo[] = "port=" . $this->port;
		$this->connectionInfo[] = "user='" . addslashes($this->username) . "'";
		$this->connectionInfo[] = "password='" . addslashes($this->password) . "'";

		if ($this->dbName != null) {
			$this->connectionInfo[] = "dbname='" . addslashes($this->dbName) . "'";
		}

		if (isset($options["connectTimeout"]) && is_int($options["connectTimeout"])) {
			$this->connectionInfo[] = "connect_timeout=" . $options["connectTimeout"];
		}
		if (isset($options["options"]) && is_string($options["options"])) {
			$this->connectionInfo[] = "options='" . $options["options"] . "'";
		}
		if (isset($options["sslMode"]) && is_string($options["sslMode"])) {
			$this->connectionInfo[] = "sslmode='" . $options["sslMode"] . "'";
		}
	}

	/**
	 * Create appropriate exception for specific driver
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previousException
	 *
	 * @return PostgreException
	 */
	protected function createException($message, $code = 0, Exception $previousException = null) {
		return new PostgreException($message, $code, $previousException);
	}

	/**
	 * Open connection
	 *
	 * Actual process which opening connection to database server / engine.
	 * This method must supply native object into nativeConnector
	 *
	 * @return bool
	 */
	protected function _open() {
		$connectionString = implode(" ", $this->connectionInfo);
		$this->nativeConnector = @pg_connect($connectionString);

		if (is_resource($this->nativeConnector)) {
			return true;
		} else{
			$this->nativeConnector = null;

			return false;
		}
	}

	/**
	 * Actual process for closing connection
	 *
	 * @return bool true if connection successfully closed
	 */
	protected function _close() {
		return pg_close($this->nativeConnector);
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
	 * @throws PostgreException
	 * @return PostgreQuery
	 */
	protected function _query($query, $fetchMode) {
		$this->queryResource = pg_query($this->nativeConnector, $query);

		if (is_resource($this->queryResource)) {
			return new PostgreQuery($this->queryResource, $fetchMode);
		} else if ($this->queryResource === false) {
			return null;
		} else {
			throw new PostgreException("Unexpected return type from pg_query() function. Please contact hd.susanto@yahoo.com about this issue or open ticket.");
		}
	}

	/**
	 * Actual method for preparing prepared query
	 *
	 * @param string $query
	 * @param array  $driverOptions
	 *
	 * @return PostgreStatement
	 */
	protected function _prepareQuery($query, $driverOptions) {
		$result = pg_prepare($this->nativeConnector, "lh_statement", $query);

		if (is_resource($result)) {
			return new PostgreStatement($this->nativeConnector, "lh_statement");
		} else {
			return null;
		}
	}

	/**
	 * Actual implementation of Begin Transaction
	 *
	 * @return bool
	 */
	protected function _beginTransaction() {
		$result = pg_query($this->nativeConnector, "BEGIN");

		return is_resource($result);
	}

	/**
	 * Actual implementation of Commit Transaction
	 *
	 * @return bool
	 */
	protected function _commitTransaction() {
		$result = pg_query($this->nativeConnector, "COMMIT");

		return is_resource($result);
	}

	/**
	 * Actual implementation of Rollback Transaction
	 *
	 * @return bool
	 */
	protected function _rollbackTransaction() {
		$result = pg_query($this->nativeConnector, "ROLLBACK");

		return is_resource($result);
	}

	/**
	 * Get adapter name
	 *
	 * @return string
	 */
	public function getName() {
		return "Postgre SQL";
	}

	/**
	 * Get error code from previous execution
	 *
	 * WARNING: This error code is database specific.
	 *
	 * @return int
	 */
	public function getErrorCode() {
		if (is_resource($this->queryResource)) {
			pg_result_error_field($this->queryResource, PGSQL_DIAG_SQLSTATE);
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
		if (is_resource($this->queryResource)) {
			pg_result_error($this->queryResource, PGSQL_DIAG_SQLSTATE);
		}

		return null;
	}

	/**
	 * Get specific Factory builder for each driver
	 *
	 * Builder factory used to generate SQL for each driver or database. For maximum portability any query should build based on object style.
	 * This factory will able to create SELECT, INSERT, UPDATE, DELETE statement easily and will protect against query injection.
	 *
	 * @return PostgreFactory
	 */
	public function getBuilderFactory() {
		if ($this->factory === null) {
			$this->factory = new PostgreFactory($this);
		}

		return $this->factory;
	}

	/**
	 * Get Platform object for each driver
	 *
	 * Platform object used in conjunction with Builder Factory to provide portability between database engine. Platform object is responsible for
	 * escaping any value and quoting it.
	 *
	 * @return PostgrePlatform
	 */
	public function getPlatform() {
		if ($this->platform === null) {
			$this->platform = new PostgrePlatform($this);
		}

		return $this->platform;
	}

	/**
	 * Return last auto-generated ID from last executed query or last value from sequence
	 *
	 * @param null|string $sequenceName
	 *
	 * @return int
	 */
	public function lastInsertId($sequenceName = null) {
		if ($sequenceName == null) {
			$query = pg_query($this->nativeConnector, "SELECT lastval();");
		} else {
			$sequenceName = $this->getPlatform()->quoteValue($sequenceName);
			$query = pg_query($this->nativeConnector, "SELECT currval($sequenceName)");
		}

		if (is_resource($query)) {
			$row = pg_fetch_row($query);

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
	 * @throws PostgreException
	 *
	 * @return string[]
	 */
	public function getColumnNames($tableName) {
		$tableName = $this->getPlatform()->quoteValue($tableName);
		$query = $this->query("SELECT * FROM information_schema.columns WHERE table_schema = 'public' AND table_name = $tableName ORDER BY ordinal_position");
		if ($query == null) {
			if ($this->getErrorMessage() != null) {
				$innerException = $this->createException($this->getErrorMessage(), $this->getErrorCode());
			} else {
				$innerException = null;
			}

			throw $this->createException("Failed to retrieve column(s) detail using information_schema.columns table. Please check your connection", 0, $innerException);
		}

		$columns = array();
		foreach ($query->fetchAll() as $row) {
			$columns[] = $row["column_name"];
		}

		return $columns;
	}
}

// End of File: PostgreAdapter.php 
