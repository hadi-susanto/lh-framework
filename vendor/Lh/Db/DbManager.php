<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db;

use InvalidArgumentException;
use Lh\ApplicationException;
use Lh\Collections\KeyExistsException;
use Lh\Exceptions\ClassNotFoundException;
use Lh\Exceptions\InvalidConfigException;
use Lh\Exceptions\InvalidOperationException;
use Lh\ServiceBase;
use Lh\ServiceLocator;

/**
 * Class DbManager
 *
 * This class will manage your adapter(s) or database connection. Adapter created based on config file in system/application.config.php
 * Please note that first added adapter will be considered default one whatever the name. To provide an adapter you must use 'adapters'
 * key in 'dbManager' section. 'adapters' key will consist your adapter settings:
 *  - driver: Which class will be used as your adapter driver
 *  - server: IP address of your server or file location
 *  - username: Identity used for connecting to DBase
 *  - password: Credential used for connecting to DBase
 *
 * @package Lh\Db
 */
class DbManager extends ServiceBase {
	const OVERRIDE_ALLOW = "ALLOW";
	const OVERRIDE_DENY = "DENY";
	const OVERRIDE_THROW_EXCEPTION = "EXCEPTION";

	/** @var string Override behaviour */
	private $overrideBehaviour;
	/** @var AdapterBase[] Registered adapters */
	private $adapters = array();
	/** @var string Default adapter name. First registered adapter considered default one. */
	private $defaultName = null;
	/** @var string[] common driver name in short hand */
	private $commonDriverNames = array(
		"mysql" => "Lh\\Db\\MySql\\MySqlAdapter",
		"mysqli" => "Lh\\Db\\MySqli\\MySqliAdapter",
		"mssql" => "Lh\\Db\\MsSql\\MsSqlAdapter",
		"sqlsrv" => "Lh\\Db\\MsSql\\MsSqlAdapter",
		"pdo.mysql" => "Lh\\Db\\MySql\\Pdo\\MySqlPdoAdapter",
		"pdo.mssql" => "Lh\\Db\\MsSql\\Pdo\\MsSqlPdoAdapter",
		"pdo.sqlsrv" => "Lh\\Db\\MsSql\\Pdo\\MsSqlPdoAdapter"
	);

	/**
	 * Create new instance of DbManager
	 *
	 * @param ServiceLocator $serviceLocator
	 */
	public function __construct(ServiceLocator $serviceLocator) {
		parent::__construct($serviceLocator);
		$serviceLocator->setDbManager($this);
	}

	/**
	 * Get all registered adapter(s)
	 *
	 * @return \Lh\Db\AdapterBase[]
	 */
	public function getAdapters() {
		return $this->adapters;
	}

	/**
	 * Retrieve adapter based on registered name
	 *
	 * @param string $name
	 *
	 * @return AdapterBase|null
	 */
	public function getAdapter($name) {
		if (array_key_exists($name, $this->adapters)) {
			return $this->adapters[$name];
		} else {
			return null;
		}
	}

	/**
	 * Retrieve default adapter
	 *
	 * @return AdapterBase|null
	 */
	public function getDefaultAdapter() {
		return $this->getAdapter($this->defaultName);
	}

	/**
	 * Initialize DbManager
	 *
	 * Initialize DbManager based on user options. Available user options:
	 *  - 'override'    => Tell database manager how to override an existing adapter
	 *  - 'adapters'    => Adapters definition to be used in application. Array key will be used as adapter name and
	 *                     and the first key will be assumed as default adapter.
	 *                       For adapter definition please refer to little specification below.
	 *
	 * Each array in 'adapters' key must be follow following structure:
	 *  - 'driver'		=> Driver name or full class name
	 *  - 'server'		=> Server location or file location (if using database file)
	 *  - 'username'	=> Credential for logging in into database server
	 *  - 'password'	=> Credential for logging in into database server
	 *  - 'dbName'		=> [OPTIONAL] Default database / schema name
	 *  - 'options'		=> [OPTIONAL] Specific options for current driver. It's depend on implemented driver
	 *
	 * @param array $options
	 *
	 * @return void
	 */
	protected function _init(array $options) {
		$this->overrideBehaviour = isset($options["override"]) ? $options["override"] : self::OVERRIDE_DENY;

		if (isset($options["adapters"]) && is_array($options["adapters"])) {
			$this->processCustomAdapters($options["adapters"]);
		}
	}

	/**
	 * Translate driver short name into fully qualified name
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	private function translateDriverName($name) {
		$_name = strtolower($name);
		foreach ($this->commonDriverNames as $shortName => $longName) {
			if ($_name == $shortName) {
				return $longName;
			}
		}

		return $name;
	}

	/**
	 * Process adapter from config file
	 *
	 * Load adapters from user file definitions. Adapters will be in key value pair, key will be used as adapter name
	 * and value will be extracted for driver, server, etc. First key will be assumed as default adapter.
	 *
	 * Each adapter definition should have following structure:
	 *  - 'driver'		=> Driver name or full class name
	 *  - 'server'		=> Server location or file location (if using database file)
	 *  - 'username'	=> Credential for logging in into database server
	 *  - 'password'	=> Credential for logging in into database server
	 *  - 'dbName'		=> [OPTIONAL] Default database / schema name
	 *  - 'options'		=> [OPTIONAL] Specific options for current driver. It's depend on implemented driver
	 *
	 * @param array $adapters
	 */
	private function processCustomAdapters(array $adapters) {
		foreach ($adapters as $name => $definitions) {

			$driver = isset($definitions["driver"]) ? $definitions["driver"] : null;
			$server = isset($definitions["server"]) ? $definitions["server"] : null;
			$username = isset($definitions["username"]) ? $definitions["username"] : null;
			$password = isset($definitions["password"]) ? $definitions["password"] : null;
			$dbName = isset($definitions["dbName"]) ? $definitions["dbName"] : null;
			$options = isset($definitions["options"]) ? $definitions["options"] : null;

			// Checking definitions validity
			if ($driver === null) {
				$this->addExceptionTrace(new InvalidConfigException(APPLICATION_PATH . "config/system/application.config.php", "Invalid value for key 'adapters' in section 'dbManager'. Driver name is missing"), __METHOD__);
				continue;
			}
			if ($server === null) {
				$this->addExceptionTrace(new InvalidConfigException(APPLICATION_PATH . "config/system/application.config.php", "Invalid value for key 'adapters' in section 'dbManager'. Server name is missing"), __METHOD__);
				continue;
			}

			try {
				$this->addAdapter($name, $driver, $server, $username, $password, $dbName, $options);
			} catch (\Exception $ex) {
				$this->addExceptionTrace($ex, __METHOD__);
			}
		}
	}

	/**
	 * Add adapter into DbManager
	 *
	 * This method will add an adapter into adapter collections. This method used while processing custom adapter. First adapter registered using this method
	 * will be considered default one by DbManager. It's NOT RECOMMENDED to call this method from user code because its public visibility because only .
	 * One exception if it is called from IWebBootstrap::onStart() event (dependency must be loaded manually)
	 *
	 * @param string $name
	 * @param string $driver
	 * @param string $server
	 * @param string $username
	 * @param string $password
	 * @param string $dbName
	 * @param array  $options
	 *
	 * @return bool
	 * @throws \Lh\Collections\KeyExistsException
	 * @throws \Lh\Exceptions\InvalidOperationException
	 * @throws \InvalidArgumentException
	 * @throws \Lh\Exceptions\ClassNotFoundException
	 */
	public function addAdapter($name, $driver, $server, $username, $password, $dbName, $options) {
		$driver = $this->translateDriverName($driver);
		if (empty($name)) {
			throw new InvalidArgumentException("Adapter name can't be empty!");
		}
		if (empty($driver)) {
			throw new InvalidArgumentException("Driver class can't be empty!");
		}
		if (empty($server)) {
			throw new InvalidArgumentException("Server name or file name can't be empty");
		}

		if (!class_exists($driver, true)) {
			throw new ClassNotFoundException($driver, "Unable to find required adapter class! Auto-loading failed, please specify custom auto loader if you using 3rd party compatible driver");
		}

		if (array_key_exists($name, $this->adapters)) {
			switch ($this->overrideBehaviour) {
				case self::OVERRIDE_DENY:
					return false;
				case self::OVERRIDE_THROW_EXCEPTION:
					throw new KeyExistsException("name", "Key: '$name' already registered as adapter");
				case self::OVERRIDE_ALLOW:
				default:
					break;
			}
		}

		/** @var AdapterBase $adapter */
		$adapter = new $driver($server, $username, $password, $dbName, $options);
		if (!($adapter instanceof IAdapter)) {
			throw new InvalidOperationException(sprintf("Unable cast %s as AdapterBase class", get_class($adapter)));
		}

		$this->adapters[$name] = $adapter;
		if ($this->defaultName === null) {
			$this->defaultName = $name;
		}

		return true;
	}

	/**
	 * Remove adapter from adapter collections
	 *
	 * NOTE: remove default adapter must be using force option since it can break other framework core functionality
	 *
	 * @param string $name
	 * @param bool   $forceRemoval
	 *
	 * @return bool
	 * @throws \Lh\ApplicationException
	 */
	public function removeAdapter($name, $forceRemoval = false) {
		if (!$forceRemoval && $name == $this->defaultName) {
			throw new ApplicationException("Unable to remove default adapter! To remove default adapter you must use force option.");
		}

		if (!array_key_exists($name, $this->adapters)) {
			return false;
		}

		unset($this->adapters[$name]);

		return true;
	}
}

// End of File: DbManager.php 
