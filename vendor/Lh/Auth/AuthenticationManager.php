<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Auth;

use Lh\Exceptions\ClassNotFoundException;
use Lh\Exceptions\InvalidConfigException;
use Lh\Io\FileNotFoundException;
use Lh\ServiceBase;
use Lh\ServiceLocator;
use Lh\Session\Storage;

/**
 * Class AuthManager
 *
 * This class will manage user credential with the help of authenticator. Your code should use this class to get Authenticator object.
 * After authentication succeed then its your responsible to persist the user data. You can persist data using saveUser() and getCurrentUser() method
 * IMPORTANT: User stored using this class will use Storage object and this object is have a one day live time (86400 seconds)
 *
 * @see Storage
 * @package Lh\Auth
 */
class AuthenticationManager extends ServiceBase {
	/** @var IAuthenticator Authenticator class which providing authentication mechanism */
	private $authenticator;
	/** @var string Session storage name */
	private $sessionName = "auth";
	/** @var int Time to live of authentication session storage. It's defaulted to one day */
	private $ttl = 86400;
	/** @var \Lh\Session\Storage Session storage for authentication persistence */
	private $storage;

	/**
	 * Create new instance of AuthenticationManager
	 *
	 * @param ServiceLocator $serviceLocator
	 */
	public function __construct(ServiceLocator $serviceLocator) {
		parent::__construct($serviceLocator);
		$serviceLocator->setAuthManager($this);
	}

	/**
	 * Get authenticator object
	 *
	 * This authenticator object should be used for authentication mechanism. For successful authentication you able to get the User
	 * object from it. Note that the user object is not persisted until you manually persist is using AuthenticationManager::saveUser()
	 *
	 * @see AuthenticationManager::saveUser()
	 *
	 * @return \Lh\Auth\IAuthenticator
	 */
	public function getAuthenticator() {
		return $this->authenticator;
	}

	/**
	 * Initialize AuthenticationManager
	 *
	 * Initializing authentication manager. Options:
	 *  - 'authenticator'	=> Definition of user authenticator
	 * 							Definition example:
	 * 							1. 'authenticatorClassName' (the class will be auto loaded by auto loaders)
	 *							2. array('class' => 'ClassName'[, 'file' => 'fileLocation'][, 'options' => array])
	 *  - 'sessionName'		=> Session name for persistent storage.
	 *  - 'ttl'				=> Session storage time to live. Value is in second(s)
	 *
	 * @param array $options
	 *
	 * @return void
	 */
	protected function _init(array $options) {
		if (isset($options["authenticator"]) && !empty($options["authenticator"])) {
			$this->setAuthenticator($options["authenticator"]);
		}
		if (isset($options["sessionName"]) && is_string($options["sessionName"])) {
			$this->sessionName = $options["sessionName"];
		}
		if (isset($options["ttl"]) && is_int($options["ttl"])) {
			$this->ttl = $options["ttl"];
		}
	}

	/**
	 * Set authenticator object to be used in framework
	 *
	 * Create and setting authenticator for current session. Parameter signature:
	 *  1. String class filename
	 *  2. Array definition which consist of:
	 *     - 'class'	=> Class name which implements IAuthenticator
	 *     - 'file'		=> [OPTIONAL] file location containing class definition
	 *     - 'options'	=> [OPTIONAL] options for authenticator (Each authenticator may expose different options)
	 *
	 * @param $authenticator
	 */
	private function setAuthenticator($authenticator) {
		try {
			if (is_string($authenticator)) {
				$class = $authenticator;
				$source = null;
				$options = null;
			} else {
				if (is_array($authenticator)) {
					$class = isset($authenticator["class"]) ? $authenticator["class"] : null;
					$source = isset($authenticator["file"]) ? $authenticator["file"] : null;
					$options = isset($authenticator["options"]) ? $authenticator["options"] : null;
				} else {
					throw new InvalidConfigException(APPLICATION_PATH . "config/system/application.config.php", "Invalid value for key 'authenticator' in section 'authenticationManager'. It's only accept string or array");
				}
			}
			// Final Checking
			if ($class == null) {
				throw new InvalidConfigException(APPLICATION_PATH . "config/system/application.config.php", "Invalid value for key 'authenticator' in section 'authenticationManager'. It's only accept string or array");
			}

			if ($source !== null) {
				// Source file available, try to load
				if (is_file($source)) {
					require_once($source);
				} else {
					throw new FileNotFoundException($source, "Unable to find '$source' in current folder: '" . getcwd() . "'");
				}
			}

			if (!class_exists($class, true)) {
				if ($source !== null) {
					throw new ClassNotFoundException($class, "Unable to create instance of '$class'. Given source file is doesn't contains appropriate class definition.");
				} else {
					throw new ClassNotFoundException($class, "Unable to create instance of '$class'. Auto loading class is failed.");
				}
			}

			// Class definition should be loaded here
			$className = $class;
			$class = new $className();
			unset($className);

			if (!($class instanceof IAuthenticator)) {
				throw new \InvalidArgumentException("Can't create instance of IAuthenticator from: " . get_class($class));
			}

			$this->authenticator = $class;
			$class->setOptions($options);
		} catch (\Exception $ex) {
			$this->addExceptionTrace($ex, __METHOD__);
			$this->authenticator = null;
		}
	}

	/**
	 * Save user data into persistent storage
	 *
	 * Authentication mechanism WILL NOT perform saving data into persistent storage. Therefore we must manually use this method to persist user data object.
	 * Persistent storage is controlled by SessionManager class and their configuration. Our class will use default persistent storage.
	 *
	 * @see SessionManager
	 *
	 * @param User $user
	 */
	public function saveUser(User $user) {
		if ($this->storage === null) {
			$this->storage = new Storage($this->sessionName, $this->ttl);
		}

		$this->storage->set("user", $user);
	}

	/**
	 * Get current user from persistent storage
	 *
	 * Retrieve current user from persistent storage if available. Please note that auth persistent storage are timed. These storage will be invalidated after
	 * one day (86400 seconds). Therefore each request made should try to obtain user data (obtaining user data will reset storage life)
	 *
	 * @return User|null
	 */
	public function getCurrentUser() {
		if ($this->storage === null) {
			$this->storage = new Storage($this->sessionName, $this->ttl);
		}

		return $this->storage->get("user");
	}

	/**
	 * Store custom auth session data
	 *
	 * Used when additional session data (which related to authentication) should be stored together with session data. These variable will be stored under
	 * same namespace with authentication session data
	 *
	 * @param string   $key	Key for session variable
	 * @param mixed    $value Value for the variable
	 * @param null|int $dataTtl Time to live for current key
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setAuthData($key, $value, $dataTtl = null) {
		if ($this->storage === null) {
			$this->storage = new Storage($this->sessionName, $this->ttl);
		}
		if ($key == 'user') {
			throw new \InvalidArgumentException("Can't use 'user' as key since it's already used for user session key.");
		}

		$this->storage->set($key, $value, $dataTtl);
	}

	/**
	 * Get variable from authentication session storage
	 *
	 * Used to access session data which stored under authentication namespace storage. These data should be stored using setAuthData() directly or using Storage
	 * class indirectly (by using authentication namespace)
	 *
	 * @param string     $key
	 * @param null|mixed $default
	 *
	 * @see Storage
	 *
	 * @return mixed|null
	 */
	public function getAuthData($key, $default = null) {
		if ($this->storage === null) {
			$this->storage = new Storage($this->sessionName, $this->ttl);
		}

		return $this->storage->get($key, $default);
	}

	/**
	 * Remove custom auth session data
	 *
	 * This will remove any custom data stored in auth session storage based on given key.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function removeAuthData($key) {
		if ($this->storage === null) {
			$this->storage = new Storage($this->sessionName, $this->ttl);
		}

		return $this->storage->remove($key);
	}

	/**
	 * Destroy auth session data
	 *
	 * This method will destroy auth session data completely by using destroy() method of Storage class
	 *
	 * @see Lh\Session\Storage::destroy()
	 */
	public function destroyAuthSession() {
		if ($this->storage === null) {
			$this->storage = new Storage($this->sessionName, $this->ttl);
		}

		$this->storage->destroy();
	}
}

// End of File: AuthenticationManager.php