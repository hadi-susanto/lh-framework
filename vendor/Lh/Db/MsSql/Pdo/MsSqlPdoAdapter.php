<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MsSql\Pdo;

use Exception;
use Lh\Db\MsSql\Builders\MsSqlFactory;
use Lh\Db\Pdo\PdoAdapterBase;

/**
 * Class MsSqlPdoAdapter
 *
 * @package Lh\Db\MsSql\Pdo
 */
class MsSqlPdoAdapter extends PdoAdapterBase {
	/** @var string The application name used in tracing. */
	protected $appName;
	/** @var null|string Specifies the path for the file used for trace data. */
	protected $traceFile;
	/** @var bool Specifies whether ODBC tracing is enabled (1 or true) or disabled (0 or false) for the connection being established. */
	protected $isTraceOn = false;
	/** @var string Specifies the name of the computer for tracing. */
	protected $wsid;

	/** @var bool Flag determine whether connection should be pooled or not. */
	protected $isPooled = true;
	/** @var bool Flag determine whether connection should be encrypted or not. */
	protected $isEncrypted = false;
	/** @var null|string Specifies the server and instance of the database mirror (if enabled and configured) to use when the primary server is unavailable. */
	protected $failOverPartner;
	/** @var int Specifies the number of seconds to wait before failing the connection attempt. */
	protected $loginTimeout = 0;
	/** @var string Specifies the transaction isolation level. The accepted values for this option are PDO::SQLSRV_TXN_* */
	protected $transactionIsolation;
	/** @var bool Specifies whether the client should trust (1 or true) or reject (0 or false) a self-signed server certificate. */
	protected $isTrustServerCertificate = false;
	/** @var int SQL Server port */
	protected $port = 1433;

	/** @var MsSqlPdoPlatform Platform tools for generate server specific issues */
	private $platform;
	/** @var MsSqlFactory Singleton factory object */
	private $factory;

	/**
	 * Get adapter name
	 *
	 * @return string
	 */
	public function getName() {
		return "PDO Sql Server";
	}

	/**
	 * Get dsn prefix used in PDO connection
	 *
	 * @return string
	 */
	public function getDsnPrefix() {
		return "sqlsrv:";
	}

	/**
	 * Get specific Factory builder for each driver
	 *
	 * Builder factory used to generate SQL for each driver or database. For maximum portability any query should build based on object style.
	 * This factory will able to create SELECT, INSERT, UPDATE, DELETE statement easily and will protect against query injection.
	 *
	 * @return \Lh\Db\Builders\IFactory
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
	 * @return MsSqlPdoPlatform
	 */
	public function getPlatform() {
		if ($this->platform === null) {
			$this->platform = new MsSqlPdoPlatform($this);
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
		if (isset($options["port"]) && is_numeric($options["port"])) {
			$this->port = (int)$options["port"];
		}
		if (isset($options["app"]) && is_string($options["app"])) {
			$this->appName = $options["app"];
		}
		if (isset($options["connectionPooling"]) && is_bool($options["connectionPooling"])) {
			$this->isPooled = $options["connectionPooling"];
		}
		if (isset($options["encrypt"]) && is_bool($options["encrypt"])) {
			$this->isEncrypted = $options["encrypt"];
		}
		if (isset($options["failOverPartner"]) && is_string($options["failOverPartner"])) {
			$this->failOverPartner = $options["failOverPartner"];
		}
		if (isset($options["loginTimeout"]) && is_numeric($options["loginTimeout"])) {
			$this->loginTimeout = max(0, (int)$options["loginTimeout"]);
		}
		if (isset($options["traceFile"]) && is_string($options["traceFile"])) {
			$this->traceFile = $options["traceFile"];
		}
		if (isset($options["traceOn"]) && is_bool($options["traceOn"])) {
			$this->isTraceOn = $options["traceOn"];
		}
		if (isset($options["transactionIsolation"]) && is_numeric($options["transactionIsolation"])) {
			$this->transactionIsolation = (int)$options["transactionIsolation"];
		}
		if (isset($options["trustServerCertificate"]) && is_bool($options["trustServerCertificate"])) {
			$this->isTrustServerCertificate = $options["trustServerCertificate"];
		}
		if (isset($options["wsid"]) && is_string($options["wsid"])) {
			$this->wsid = $options["wsid"];
		}
	}

	/**
	 * Generate DSN string for specific driver. Each driver maybe have their specific key which can be achieved by using options from application config
	 *
	 * @return string
	 */
	protected function generateDsn() {
		$tokens = array();
		if ($this->port != 1433) {
			$tokens[] = "Server=" . $this->server . "," . $this->port;
		} else {
			$tokens[] = "Server=" . $this->server;
		}
		if ($this->dbName != null) {
			$tokens[] = "Database=" . $this->dbName;
		}
		if ($this->appName != null) {
			$tokens[] = "APP=" . $this->appName;
		}
		if ($this->traceFile != null) {
			$tokens[] = "TraceFile=" . $this->traceFile;
		}
		if ($this->isTraceOn) {
			$tokens[] = "TraceOn=1";
		}
		if ($this->wsid != null) {
			$tokens[] = "WSID=" . $this->wsid;
		}
		if ($this->isPooled) {
			$tokens[] = "ConnectionPooling=1";
		}
		if ($this->isEncrypted) {
			$tokens[] = "Encrypt=1";
		}
		if ($this->failOverPartner != null) {
			$tokens[] = "Failover_Partner=" . $this->failOverPartner;
		}
		if ($this->loginTimeout > 0) {
			$tokens[] = "LoginTimeout=" . $this->loginTimeout;
		}
		if ($this->transactionIsolation != null) {
			$tokens[] = "TransactionIsolation=" . $this->transactionIsolation;
		}
		if ($this->isTrustServerCertificate) {
			$tokens[] = "TrustServerCertificate=1";
		}

		return "sqlsrv:" . implode(";", $tokens);
	}

	/**
	 * Create appropriate exception for specific driver
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previousException
	 *
	 * @return MsSqlPdoException
	 */
	protected function createException($message, $code = 0, Exception $previousException = null) {
		return new MsSqlPdoException($message, $code, $previousException);
	}

	/**
	 * Create specialized PdoQuery object
	 *
	 * @param \PDOStatement $statement
	 * @param int           $fetchMode
	 *
	 * @return MsSqlPdoQuery
	 */
	protected function createQuery(\PDOStatement &$statement, &$fetchMode) {
		return new MsSqlPdoQuery($statement, $fetchMode);
	}

	/**
	 * Create specialized LhPdoStatement object
	 *
	 * @param \PDOStatement $statement
	 *
	 * @return MsSqlPdoStatement
	 */
	protected function createStatement(\PDOStatement &$statement) {
		return new MsSqlPdoStatement($statement);
	}

	/**
	 * Get column(s) name from given table
	 *
	 * This will retrieve all column(s) name from a table in a database.
	 * NOTE: This method will be obsoleted when a metadata feature added into LH Framework since it's only retrieve column name instead of column definition
	 *
	 * @param string $tableName
	 *
	 * @throws MsSqlPdoException
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

// End of File: MsSqlPdoAdapter.php 
