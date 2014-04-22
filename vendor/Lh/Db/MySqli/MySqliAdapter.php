<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MySqli;

use Exception;
use Lh\Db\AdapterBase;
use Lh\Db\IQuery;
use Lh\Db\MySql\Builders\MySqlFactory;
use Lh\Db\Query;

/**
 * Class MysqliAdapter
 *
 * Adapter for mysqli driver (MySQL improved)
 *
 * @method MySqliExtended getNativeConnector()
 * @package Lh\Db
 */
class MySqliAdapter extends AdapterBase {
	/** @var \mysqli Native object for mysqli driver */
	protected $nativeConnector;
	/** @var int MySQL port */
	protected $port = 3306;
	/** @var string Socket file location (UNIX Only) */
	protected $socket = null;
	/** @var array mysqli init options */
	protected $initOptions = array();
	/** @var MySqlFactory MySQL factory instance */
	private $factory;
	/** @var MySqliPlatform MySQL Platform instance */
	private $platform;

	/**
	 * Get driver name
	 *
	 * @return string
	 */
	public function getName() {
		return "MySQLi";
	}

	/**
	 * Get error code from previous execution
	 *
	 * WARNING: This error code is database specific.
	 *
	 * @return int
	 */
	public function getErrorCode() {
		if ($this->nativeConnector !== null && ($code = $this->nativeConnector->errno) != null) {
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
		if ($this->nativeConnector !== null && ($msg = $this->nativeConnector->error) != null) {
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
	 * NOTE: Although this is mysqli driver, factory builder is shared with MySQL one
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
	 * @return MySqliPlatform
	 */
	public function getPlatform() {
		if ($this->platform === null) {
			$this->platform = new MySqliPlatform($this);
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
		if (isset($options["init"]) && is_array($options["init"])) {
			$this->initOptions = $options["init"];
		}
	}

	/**
	 * Create driver specific exception object
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previousException
	 *
	 * @return MySqliException
	 */
	protected function createException($message, $code = 0, Exception $previousException = null) {
		return new MySqliException($message, $code, $previousException);
	}

	/**
	 * Actual connect method
	 *
	 * @return bool
	 */
	protected function _open() {
		$this->nativeConnector = @new MySqliExtended($this->server, $this->username, $this->password, $this->dbName, $this->port, $this->socket, $this->initOptions);
		if ($this->nativeConnector->errno != 0) {
			return false;
		}

		return true;
	}

	/**
	 * Actual close method
	 *
	 * @return bool
	 */
	protected function _close() {
		return $this->nativeConnector->close();
	}

	/**
	 * Actual query method
	 *
	 * @param string $query
	 * @param int    $fetchMode
	 *
	 * @return MySqliQuery|Query|null
	 * @throws MySqliException
	 */
	protected function _query($query, $fetchMode) {
		$result = $this->nativeConnector->query($query);

		if ($result === false) {
			return null;
		} else if ($result instanceof \mysqli_result) {
			// SELECT, SHOW, DESCRIBE or EXPLAIN will return mysqli_result
			return new MySqliQuery($result, $fetchMode);
		} else if ($result === true) {
			// Other successful query will return true
			return new MySqliQuery(null, IQuery::FETCH_NONE, $this->nativeConnector->affected_rows);
		} else {
			// Unknown return type... Maybe latest mysqli driver support additional
			throw new MySqliException("Unexpected return type from mysqli::query() call! We got: " . get_class($result), -1);
		}
	}

	/**
	 * Actual prepare query method
	 *
	 * @param string $query
	 * @param array  $driverOptions
	 *
	 * @return MySqliStatement
	 */
	protected function _prepareQuery($query, $driverOptions) {
		$result = $this->nativeConnector->prepare($query);

		if ($result instanceof \mysqli_stmt) {
			return new MySqliStatement($result);
		} else {
			return null;
		}
	}

	/**
	 * Actual begin transaction method
	 *
	 * @return bool
	 */
	protected function _beginTransaction() {
		return $this->nativeConnector->begin_transaction();
	}

	/**
	 * Actual commit transaction method
	 *
	 * @return bool
	 */
	protected function _commitTransaction() {
		return $this->nativeConnector->commit();
	}

	/**
	 * Actual rollback transaction method
	 *
	 * @return bool
	 */
	protected function _rollbackTransaction() {
		return $this->nativeConnector->rollback();
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
		// Can be optimized by directly accessing protected method but I prefer this way

		$result = $this->query("SELECT LAST_INSERT_ID()");
		if ($result instanceof Query) {
			$row = $result->fetchRow();
			return $row[0];
		} else {
			return null;
		}
	}
}

// End of File: MySqliAdapter.php
