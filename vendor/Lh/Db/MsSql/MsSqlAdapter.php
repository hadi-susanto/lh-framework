<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MsSql;

use Exception;
use Lh\Db\AdapterBase;
use Lh\Db\MsSql\Builders\MsSqlFactory;
use Lh\Db\Query;
use string;

/**
 * Class MsSqlAdapter
 *
 * This class will use sqlsrv driver instead MsSql driver which obsoleted in PHP 5.3. LH Framework minimal requirement is PHP 5.3 therefore MsSql must be obsoleted
 * and it's usage is not recommended anymore.
 *
 * @method resource getNativeConnector()
 * @package Lh\Db\MsSql
 */
class MsSqlAdapter extends AdapterBase {
	/** @var resource Native object for sqlsrv driver */
	protected $nativeConnector;
	/** @var int SQL Server port */
	protected $port = 1433;
	/** @var array Connection info passed when connecting to SQL Server */
	protected $connectionInfo;
	/** @var MsSqlFactory SQL Server factory instance */
	private $factory;
	/** @var MsSqlPlatform SQL Server Platform instance */
	private $platform;
	/** @var string Should the result set from query be scrollable? */
	private $scrollable;

	/**
	 * Create new instance of MsSqlAdapter instance.
	 *
	 * This driver will use sqlsrv driver provided by microsoft. SQL Server native client is a mandatory!
	 *
	 * @param string      $server
	 * @param string      $username
	 * @param string      $password
	 * @param null|string $dbName
	 * @param array       $options
	 *
	 * @throws MsSqlException
	 */
	public function __construct($server, $username, $password, $dbName = null, array $options = null) {
		if (!function_exists("sqlsrv_connect")) {
			throw new MsSqlException("Sqlsrv driver not installed/loaded in your PHP Runtime!");
		}

		parent::__construct($server, $username, $password, $dbName, $options);
		$this->methodPrefix = "sqlsrv_";
	}


	/**
	 * Get adapter name
	 *
	 * @return string
	 */
	public function getName() {
		return "SQL Server";
	}

	/**
	 * Get error code from previous execution
	 *
	 * WARNING: This error code is database specific.
	 *
	 * @return int
	 */
	public function getErrorCode() {
		$errors = sqlsrv_errors();
		if (is_array($errors)) {
			return $errors[0]["code"];
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
		$errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
		if (is_array($errors)) {
			return $errors[0]["message"];
		}
		if ($this->getLastException() != null) {
			return $this->getLastException()->getMessage();
		}

		return null;
	}

	/**
	 * Get specific Factory builder for each driver
	 *
	 * Builder factory used to generate SQL for each driver or database. For maximum portability any query should build based on object style.
	 * This factory will able to create SELECT, INSERT, UPDATE, DELETE statement easily and will protect against query injection.
	 *
	 * @return MsSqlFactory
	 */
	public function getBuilderFactory() {
		if ($this->factory === null) {
			$this->factory = new MsSqlFactory($this);
		}

		return $this->factory;
	}

	/**
	 * Get Platform object for each driver
	 *
	 * Platform object used in conjunction with Builder Factory to provide portability between database engine. Platform object is responsible for
	 * escaping any value and quoting it.
	 *
	 * @return MsSqlPlatform
	 */
	public function getPlatform() {
		if ($this->platform === null) {
			$this->platform = new MsSqlPlatform($this);
		}

		return $this->platform;
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

		// Required connection info
		$this->connectionInfo = array(
			"UID" => $this->username,
			"PWD" => $this->password
		);
		// Optional connection info
		if ($this->dbName != null) {
			$this->connectionInfo["Database"] = $this->dbName;
		}
		if (isset($options["app"]) && is_string($options["app"])) {
			$this->connectionInfo["APP"] = $options["app"];
		}
		if (isset($options["connectionPooling"]) && is_bool($options["connectionPooling"])) {
			$this->connectionInfo["ConnectionPooling"] = $options["connectionPooling"];
		}
		if (isset($options["encrypt"]) && is_bool($options["encrypt"])) {
			$this->connectionInfo["Encrypt"] = $options["encrypt"];
		}
		if (isset($options["failOverPartner"]) && is_string($options["failOverPartner"])) {
			$this->connectionInfo["Failover_Partner"] = $options["failOverPartner"];
		}
		if (isset($options["loginTimeout"]) && is_numeric($options["loginTimeout"])) {
			$this->connectionInfo["LoginTimeout"] = max(0, (int)$options["loginTimeout"]);
		}
		if (isset($options["dateAsString"]) && is_bool($options["dateAsString"])) {
			$this->connectionInfo["ReturnDatesAsStrings"] = $options["dateAsString"];
		}
		if (isset($options["scrollable"]) && is_string($options["scrollable"])) {
			$this->scrollable = $options["scrollable"];
		} else {
			$this->scrollable = SQLSRV_CURSOR_FORWARD;
		}
		if (isset($options["traceFile"]) && is_string($options["traceFile"])) {
			$this->connectionInfo["TraceFile"] = $options["traceFile"];
		}
		if (isset($options["traceOn"]) && is_bool($options["traceOn"])) {
			$this->connectionInfo["TraceOn"] = $options["traceOn"];
		}
		if (isset($options["transactionIsolation"]) && is_numeric($options["transactionIsolation"])) {
			$this->connectionInfo["TransactionIsolation"] = $options["transactionIsolation"];
		}
		if (isset($options["trustServerCertificate"]) && is_bool($options["trustServerCertificate"])) {
			$this->connectionInfo["TrustServerCertificate"] = $options["trustServerCertificate"];
		}
		if (isset($options["wsid"]) && is_string($options["wsid"])) {
			$this->connectionInfo["WSID"] = $options["wsid"];
		}
	}

	/**
	 * Create appropriate exception for specific driver
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previousException
	 *
	 * @return MsSqlException
	 */
	protected function createException($message, $code = 0, Exception $previousException = null) {
		return new MsSqlException($message, $code, $previousException);
	}

	/**
	 * Open actual connection
	 *
	 * Actual process which opening connection to database server / engine.
	 * This method must supply native object into nativeConnector
	 *
	 * @return bool
	 */
	protected function _open() {
		if ($this->port != 1433) {
			$server = $this->server . ", " . $this->port;
		} else {
			$server = $this->server;
		}
		$this->nativeConnector = @sqlsrv_connect($server, $this->connectionInfo);

		if ($this->nativeConnector === false) {
			$this->nativeConnector = null;

			return false;
		} else {
			return true;
		}
	}

	/**
	 * Actual process for closing connection
	 *
	 * @return bool true if connection successfully closed
	 */
	protected function _close() {
		return sqlsrv_close($this->nativeConnector);
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
	 * @throws MsSqlException
	 * @return MsSqlQuery
	 */
	protected function _query($query, $fetchMode) {
		$result = sqlsrv_query($this->nativeConnector, $query, array(), array(
			"Scrollable" => $this->scrollable
		));

		if ($result === false) {
			return null;
		} else if (is_resource($result)) {
			return new MsSqlQuery($result, $fetchMode);
		} else {
			// Unexpected return type by LH Framework driver... Maybe latest driver support more return type
			throw new MsSqlException("Unexpected return type from sqlsrv_query() function. Please contact hd.susanto@yahoo.com about this issue or open ticket.");
		}
	}

	/**
	 * Actual method for preparing prepared query
	 *
	 * @param string $query
	 * @param array  $driverOptions
	 *
	 * @return MsSqlStatement
	 */
	protected function _prepareQuery($query, $driverOptions) {
		return new MsSqlStatement($this->nativeConnector, $query, $driverOptions);
	}

	/**
	 * Actual implementation of Begin Transaction
	 *
	 * @return bool
	 */
	protected function _beginTransaction() {
		return sqlsrv_begin_transaction($this->nativeConnector);
	}

	/**
	 * Actual implementation of Commit Transaction
	 *
	 * @return bool
	 */
	protected function _commitTransaction() {
		return sqlsrv_commit($this->nativeConnector);
	}

	/**
	 * Actual implementation of Rollback Transaction
	 *
	 * @return bool
	 */
	protected function _rollbackTransaction() {
		return sqlsrv_rollback($this->nativeConnector);
	}

	/**
	 * Get last insert ID
	 *
	 * Return last auto-generated ID from last executed query or last value from sequence
	 * ToDo: Using native method for performance purpose and remove redundant Query class
	 *
	 * @param null|string $sequenceName
	 *
	 * @return int
	 */
	public function lastInsertId($sequenceName = null) {
		if ($sequenceName === null) {
			$result = $this->query("SELECT @@IDENTITY");
		} else {
			$statement = $this->prepareQuery("SELECT IDENT_CURRENT(?)");
			$statement->bindValue("tableName", $sequenceName);
			$result = $statement->execute();
		}
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
	 * @throws MsSqlException
	 *
	 * @return string[]
	 */
	public function getColumnNames($tableName) {
		$query = $this->query("EXEC sp_columns " . $this->getPlatform()->quoteIdentifier($tableName));
		if ($query == null) {
			if ($this->getErrorMessage() != null) {
				$innerException = $this->createException($this->getErrorMessage(), $this->getErrorCode());
			} else {
				$innerException = null;
			}

			throw $this->createException("Failed to retrieve column(s) detail using sp_columns procedure. Please check your connection", 0, $innerException);
		}

		$columns = array();
		foreach ($query->fetchAll() as $row) {
			$columns[] = $row["COLUMN_NAME"];
		}

		return $columns;
	}
}

// End of File: MsSqlAdapter.php
