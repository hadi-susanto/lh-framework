<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Auth\Authenticator;

use Lh\Auth\AuthenticationResult;
use Lh\Auth\IAuthenticator;
use Lh\Auth\User;
use Lh\Db\DbException;
use Lh\Db\IPlatform;
use Lh\Exceptions\InvalidConfigException;
use Lh\Web\Application;

/**
 * Class DigestAuthenticator
 *
 * Basic authenticator using database as storage persistent. This engine WILL NEVER store password in plain text but stored their hashed value.
 * Password are hashed using password_hash() function which available since PHP 5.5.0 therefore for PHP before that a suitable forward porting required.
 * Forward porting for password_* is already done by someone else an this Framework will use it depend on PHP version
 *
 * @see password_verify()
 * @link https://github.com/ircmaxell/password_compat
 *       https://crackstation.net/hashing-security.htm
 *       http://www.jasypt.org/howtoencryptuserpasswords.html
 *       http://en.wikipedia.org/wiki/Salting_(cryptography)
 * @package Lh\Auth\Authenticator
 */
class DigestAuthenticator implements IAuthenticator {
	/** @var \Lh\Db\DbManager Used for retrieve registered adapter */
	private $dbManager;
	/** @var \Lh\Db\IAdapter Adapter for communicating with database instance */
	private $adapter;
	/** @var \Lh\Db\Builders\IFactory Used to create SELECT, UPDATE, DELETE statement */
	private $factory;
	/** @var bool Determine whether associated adapter is capable using parameterized query or not */
	private $parameterCapable = false;
	/** @var string Store table name which store user data */
	private $tableName = "user";
	/** @var array Default column(s) used in authentication process */
	private $columns = array(
		"id" => "id",
		"identity" => "username",
		"credential" => "password",
		"name" => "real_name"
	);
	/** @var string User identity (usually user login) */
	private $identity;
	/** @var string User credential (usually user password) */
	private $credential;
	/** @var null|array Store raw data from database row. It will contain(s) custom column(s). */
	private $rawData = null;
	/** @var null|User Will store User object when authentication succeed */
	private $authenticatedUser = null;

	/**
	 * Create instance of DigestAuthenticator
	 */
	public function __construct() {
		$this->dbManager = Application::getInstance()->getServiceLocator()->getDbManager();
		if (version_compare(PHP_VERSION, '5.5.0', '<')) {
			require_once("password.php");
		}
	}

	/**
	 * Set adapter from given adapter name.
	 *
	 * @param string|null $adapterName This adapter name should be registered in DbManager, give null to use default adapter
	 *
	 * @throws \Lh\Exceptions\InvalidConfigException
	 */
	private function setAdapter($adapterName) {
		if ($adapterName === null) {
			// Use default adapter
			$this->adapter = $this->dbManager->getDefaultAdapter();
			if ($this->adapter == null) {
				throw new InvalidConfigException(APPLICATION_PATH . "config/system/application.config.php", "Unable to use Database session engine since there is no default adapter. Please give at least one adapter at 'dbManager' config key");
			}
		} else {
			$this->adapter = $this->dbManager->getAdapter($adapterName);
			if ($this->adapter == null) {
				throw new InvalidConfigException(APPLICATION_PATH . "config/system/application.config.php", "Adapter '" . $adapterName . "' not found in DbManager adapter(s) collection.");
			}
		}

		$this->factory = $this->adapter->getBuilderFactory();
		$this->parameterCapable = ($this->adapter->getPlatform()->getParameterType() !== IPlatform::PARAMETER_NOT_SUPPORTED);
	}

	/**
	 * Return last identity which used for authentication process
	 *
	 * @return string
	 */
	public function getLastIdentity() {
		return $this->identity;
	}

	/**
	 * Return last credential used for authentication process
	 *
	 * @return string
	 */
	public function getLastCredential() {
		return $this->credential;
	}

	/**
	 * Return raw data from authenticate() method.
	 *
	 * Array returned in key value pair. Key name will correspond to column name
	 * NOTE: This method should remove any sensitive data such as password, hashed password, etc
	 *
	 * @return array
	 */
	public function getRawData() {
		return $this->rawData;
	}

	/**
	 * Return authenticated user object based on last authenticate() method call.
	 *
	 * @return User
	 */
	public function getUser() {
		return $this->authenticatedUser;
	}

	/**
	 * Settings for Database authenticator. Available option(s) key:
	 *  - 'adapterName'		=> Specify which database adapter to be used. It'll use default adapter when not specified
	 *  - 'tableName'		=> Specify sessions table name
	 *  - 'id'				=> Auto increment field.
	 *  - 'identity'		=> Which field contain username data (this field must be unique)
	 *  - 'credential'		=> Which field contain hashed password data
	 *  - 'name'			=> Which field contain user real name
	 *  - 'columns'			=> Additional column(s) for rawIdentity
	 *
	 * @see DigestAuthenticator::getRawData()
	 * @link https://crackstation.net/hashing-security.htm
	 *       http://www.jasypt.org/howtoencryptuserpasswords.html
	 *
	 * @param array $options
	 *
	 * @return void
	 */
	public function setOptions($options) {
		if (isset($options["adapterName"]) && !empty($options["adapterName"])) {
			$this->setAdapter($options["adapterName"]);
		} else {
			$this->setAdapter(null);
		}
		if (isset($options["tableName"]) && !empty($options["tableName"])) {
			$this->tableName = $options["tableName"];
		}
		if (isset($options["id"]) && is_string($options["id"])) {
			$this->columns["id"] = $options["id"];
		}
		if (isset($options["identity"]) && is_string($options["identity"])) {
			$this->columns["identity"] = $options["identity"];
		}
		if (isset($options["credential"]) && is_string($options["credential"])) {
			$this->columns["credential"] = $options["credential"];
		}
		if (isset($options["name"]) && is_string($options["name"])) {
			$this->columns["name"] = $options["name"];
		}
		if (isset($options["columns"])) {
			if (is_string($options["columns"])) {
				$options["columns"] = explode(",", $options["columns"]);
			}
			$this->columns = array_merge($this->columns, $options["columns"]);
		}
		// ToDo: Checking table existence
	}

	/**
	 * Clear any identity stored in current authenticator
	 *
	 * This will clear User object and raw data. IMPORTANT: clear identity data from authenticator
	 * WILL NOT logged-out current user although User stored in authenticator is same user with AuthenticationManager.
	 * User log-out sequence performed by AuthenticationManager::saveUser(null). Yes give null value to perform log-out
	 *
	 * @return bool
	 */
	public function clearIdentity() {
		$this->rawData = null;
		$this->authenticatedUser = null;

		return true;
	}

	/**
	 * Return hashed credential based on identity and plain credential
	 *
	 * It's essential thing that we store user password in hashed form instead of plain text. Hashed password are one way 'encryption' therefore
	 * there is no such way to retrieve user password. There is no recover password BUT reset password.
	 *
	 * @param mixed $identity
	 * @param mixed $credential plain credential
	 * @param array $options
	 *
	 * @return string
	 */
	public function hashCredential($identity, $credential, array $options = null) {
		if (isset($options["algo"])) {
			$algorithm = $options["algo"];
			unset($options["algo"]);
		} else {
			$algorithm = PASSWORD_BCRYPT;
		}

		return password_hash($credential, $algorithm, $options);
	}


	/**
	 * Perform authentication
	 *
	 * This class will authenticate identity and credential with data stored in database. To prevent data leak
	 * password are hashed using different salt. Hashing, verifying performed by native PHP library password_*() function
	 * NOTE:
	 *  - Password salt only required when password hash created not in verifying process
	 *  - This function is available in PHP >= 5.5 therefore appropriate library is automatically loaded when PHP version < 5.5
	 *
	 * @param string $identity
	 * @param string $credential
	 * @param array  $options
	 *
	 * @throws \Lh\Db\DbException
	 * @return AuthenticationResult
	 */
	public function authenticate($identity, $credential, array $options = null) {
		$this->clearIdentity();
		$this->identity = $identity;
		$this->credential = $credential;
		if (empty($identity)) {
			return new AuthenticationResult(AuthenticationResult::AUTH_FAILED_EMPTY_IDENTITY);
		}
		if (empty($credential)) {
			return new AuthenticationResult(AuthenticationResult::AUTH_FAILED_EMPTY_CREDENTIAL);
		}

		$select = $this->factory->select($this->columns)
			->from($this->tableName)
			->where($this->columns["identity"], $identity);

		if ($this->parameterCapable && ($statement = $this->adapter->prepareQuery($select)) !== null) {
			$query = $statement->execute();
			if ($query === null) {
				// Probably Database Error
				throw new DbException($statement->getErrorMessage(), $statement->getErrorCode(), $this->adapter->getLastException());
			}
		} else {
			// Either driver not able to use parameter of fallback since prepare query is failed
			$query = $this->adapter->query($select);
			if ($query === null) {
				// Probably Database Error
				throw new DbException($this->adapter->getErrorMessage(), $this->adapter->getErrorCode(), $this->adapter->getLastException());
			}
		}

		if ($query->getNumRows() == 0) {
			return new AuthenticationResult(AuthenticationResult::AUTH_FAILED_NO_MATCHING_IDENTITY);
		}

		$this->rawData = $query->fetchAssoc();
		if (password_verify($credential, $this->rawData["credential"])) {
			$this->authenticatedUser = new User();
			$this->authenticatedUser->exchangeArray($this->rawData);

			return new AuthenticationResult(AuthenticationResult::AUTH_SUCCESS);
		} else {
			return new AuthenticationResult(AuthenticationResult::AUTH_FAILED_INVALID_CREDENTIAL);
		}
	}
}

// End of File: DigestAuthenticator.php