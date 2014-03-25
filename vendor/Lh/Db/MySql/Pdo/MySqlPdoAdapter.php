<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MySql\Pdo;

use Exception;
use Lh\Db\Pdo\PdoAdapterBase;
use Lh\Db\MySql\Builders\MySqlFactory;
use PDOStatement;

/**
 * Class MySqlPdoAdapter
 *
 * @package Lh\Db\MySql\Pdo
 */
class MySqlPdoAdapter extends PdoAdapterBase {
	/** @var int MySQL server port */
	protected $port = 3306;
	/** @var string Unix socket file location. This will override IP and Port settings */
	protected $socket = null;
	/** @var MySqlPdoPlatform Platform tools for generate MySQL specific issues */
	private $platform;

	/**
	 * Get driver name
	 *
	 * @return string
	 */
	public function getName() {
		return "PDO MySQL";
	}

	/**
	 * Get pdo dsn prefix
	 *
	 * @return string
	 */
	public function getDsnPrefix() {
		return "mysql:";
	}

	/**
	 * Get MySqlFactory object
	 *
	 * Although this is PDO driver the SQL builder is shared with non PDO since the engine still MySQL
	 *
	 * @return \Lh\Db\Builders\IFactory
	 */
	public function getBuilderFactory() {
		return new MySqlFactory($this);
	}

	/**
	 * Get MySqlPdoPlatform object
	 *
	 * @return MySqlPdoPlatform
	 */
	public function getPlatform() {
		if ($this->platform === null) {
			$this->platform = new MySqlPdoPlatform($this);
		}

		return $this->platform;
	}

	/**
	 * Prepare additional options for specific driver
	 *
	 * Available keys for configuring MySQL PDO Driver:
	 *  - 'port' must be specified if your mysql server running not in default port (default: 3306)
	 *  - 'socket' if you prefer use socket connection than IP based connection
	 *  - 'init' if you want additional init command done while creating PDO driver.
	 * 	  init command should conform with MySQL PDO init command (please refer to their documentation)
	 *
	 * @param array $options
	 *
	 * @return void
	 */
	protected function prepareOptions($options) {
		if (isset($options["port"]) && is_numeric($options["port"])) {
			$this->port = (int)$options["port"];
		}
		if (isset($options["socket"]) && is_string($options["socket"]) && !empty($options["socket"])) {
			$this->socket = $options["socket"];
		}
		if (isset($options["init"]) && is_array($options["init"])) {
			$this->initOptions = $options["init"];
		}
	}

	/**
	 * Get DSN string to open connection
	 *
	 * Generate DSN string which used to connect to MySQL using PDO driver. IMPORTANT: If you specify socket option then host and port will be ignored
	 *
	 * @return string
	 */
	protected function generateDsn() {
		$tokens = array();
		if ($this->socket !== null) {
			$tokens[] = "unix_socket=" . $this->socket;
		} else {
			$tokens[] = "host=" . $this->server;
			$tokens[] = "port=" . $this->port;
		}
		if ($this->dbName != null) {
			$tokens[] = "dbname=" . $this->dbName;
		}

		return "mysql:" . implode(";", $tokens);
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
	protected function createException($message, $code = 0, Exception $previousException = null) {
		return new MySqlPdoException($message, $code, $previousException);
	}

	/**
	 * Create specialized PdoQuery object
	 *
	 * @param PDOStatement $statement
	 * @param int          $fetchMode
	 *
	 * @return MySqlPdoQuery
	 */
	protected function createQuery(PDOStatement &$statement, &$fetchMode) {
		return new MySqlPdoQuery($statement, $fetchMode);
	}

	/**
	 * Create MySqlPdoStatement
	 *
	 * @param \PDOStatement $statement
	 *
	 * @return MySqlPdoStatement
	 */
	protected function createStatement(PDOStatement &$statement) {
		return new MySqlPdoStatement($statement);
	}
}

// End of File: MySqlPdoAdapter.php 