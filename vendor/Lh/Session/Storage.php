<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Session;

use ArrayAccess as IArrayAccess;
use Countable as ICountable;
use IteratorAggregate as IIteratorAggregate;
use Lh\Collections\KeyExistsException;
use Lh\Collections\KeyNotFoundException;
use Lh\Exceptions\ClassNotFoundException;
use Lh\Web\Application;

/**
 * Class Storage
 *
 * Storage class act as a bridge between codes and session data. Accessing session through $_SESSION is discouraged since it will lost their additional features such as:
 *  - Time to life: Each session data will automatically deleted when their time is expired. Time to life is reset every read or write NOT checking their existence
 *  - Flash message: Flash message will automatically removed after x number of hoop (request)
 *  - Variable scope
 * NOTE:
 *  - Storage object will be linked with $_SESSION by their full scope name
 *  - Accessing variable can be done by property like access. Ex: $obj->var_name is equal to $obj->get('var_name');
 *  - Accessing flash can done by property like access also but prefixed with 'flash'. Ex: $obj->flashVar_name is equal to $obj->getFlash('var_name');
 *
 * @see     SessionManager
 * @package Lh\Session
 */
class Storage implements IArrayAccess, ICountable, IIteratorAggregate {
	const STORAGE_VARIABLE = 1;
	const STORAGE_FLASH = 2;

	/** @var SessionManager Session manager used to start session if not started yet */
	private static $manager;
	/** @var string Session scope name */
	private $scope;
	/** @var string Actual scope used. This value could be appended by application name CRC32 */
	private $_scope;
	/** @var int The first time this session storage created. Mapped to __CREATED */
	private $createdAt;
	/** @var int Last time current session storage is accessed. Mapped to __LAST_ACCESS */
	private $lastAccessedAt;
	/** @var int Time limit for current session scope. Mapped to __TIME_LIMIT */
	private $timeLimit;
	/** @var array Contains meta data of each session. Mapped to __META */
	private $metaData;
	/** @var array Contains actual session data from user. Mapped to __VALUES */
	private $values;
	/** @var array Contains actual flash messages. Mapped to __FLASH_MESSAGES */
	private $flashMessages;

	/**
	 * Create new persistent storage through session
	 *
	 * @param string $scope
	 * @param int    $timeLimit
	 */
	public function __construct($scope = 'default', $timeLimit = 0) {
		$this->setScope($scope);
		try {
			$this->getManager()->start();
		} catch (ClassNotFoundException $ex) {
			// Do nothing since ClassNotFoundException only occur when 'shared' session data contains object which can't be defined by our class (PHP Incomplete Class)
			// 'Shared' session data means other application which reside in same public_html root folder. Maybe different framework co-exists in same root folder and they
			// implements their own auto loader.
		}
		$this->createLinkToSession();
		if ($this->metaData === null) {
			$this->initStorage();
			$this->timeLimit = $timeLimit;
		} else if ($this->timeLimit > 0 && time() > ($this->lastAccessedAt + $this->timeLimit)) {
			// Current storage already expired...
			$this->initStorage();
			$this->timeLimit = $timeLimit;
		} else {
			$this->lastAccessedAt = time();
		}
	}

	/**
	 * Set session manager
	 *
	 * @param SessionManager $manager
	 */
	public static function setManager(SessionManager $manager) {
		self::$manager = $manager;
	}

	/**
	 * Get session manager
	 *
	 * @return SessionManager
	 */
	public function getManager() {
		if (self::$manager === null) {
			self::$manager = Application::getInstance()->getServiceLocator()->getSessionManager();
		}

		return self::$manager;
	}

	/**
	 * Set storage scope name
	 *
	 * Each scope will not interfere with another scope. It's possible to have same name for variable name BUT in a different scope.
	 *
	 * @param string $scope
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function setScope($scope) {
		if (!preg_match('/^[a-z][a-z0-9_\\\]+$/i', $scope)) {
			throw new \InvalidArgumentException("Scope passed to container is invalid; must consist of alphanumerics, backslashes and underscores only");
		}

		$this->scope = $scope;
		$this->_scope = Application::getInstance()->getCrc32() . "_" . $scope;
	}

	/**
	 * Get scope name
	 *
	 * @param bool $fullName Return full name used in session management
	 *
	 * @return string
	 */
	public function getScope($fullName = false) {
		return $fullName ? $this->_scope : $this->scope;
	}

	/**
	 * Get time when this storage created
	 *
	 * @return int
	 */
	public function getCreatedAt() {
		return $this->createdAt;
	}

	/**
	 * Get time when this storage was accessed
	 *
	 * Access time updated each time writing or reading its value
	 *
	 * @return int
	 */
	public function getLastAccessedAt() {
		return $this->lastAccessedAt;
	}

	/**
	 * Get meta data of all persistent storage
	 *
	 * Returned meta data including values and flash messages meta data
	 *
	 * @return array
	 */
	public function getMetaData() {
		return $this->metaData;
	}

	/**
	 * Get all values (Variable only)
	 *
	 * Only returned variable type values. Flash messages not returned from this method.
	 *
	 * @return array
	 */
	public function getValues() {
		return $this->values;
	}

	/**
	 * Link each private property to $_SESSION using reference technique
	 */
	private function createLinkToSession() {
		$this->createdAt = &$_SESSION[$this->_scope]['__CREATED'];
		$this->lastAccessedAt = &$_SESSION[$this->_scope]['__LAST_ACCESS'];
		$this->timeLimit = &$_SESSION[$this->_scope]['__TIME_LIMIT'];
		$this->metaData = &$_SESSION[$this->_scope]['__META'];
		$this->values = &$_SESSION[$this->_scope]['__VALUES'];
		$this->flashMessages = &$_SESSION[$this->_scope]['__FLASH_MESSAGES'];
	}

	/**
	 * Init current storage with default value
	 */
	private function initStorage() {
		$this->metaData = array();
		$this->createdAt = time();
		$this->lastAccessedAt = time();
		$this->values = array();
		$this->flashMessages = array();
	}

	/**
	 * Get meta data for specific session data
	 *
	 * Will return meta data based on key. This will lookup at variable and flash messages meta data
	 *
	 * @param string $key
	 *
	 * @return array|null
	 */
	public function getMetaDataByKey($key) {
		return isset($this->metaData[$key]) ? $this->metaData[$key] : null;
	}

	/**
	 * Create meta data for session data
	 *
	 * @param string $key
	 * @param string $type
	 * @param int    $expire
	 *
	 * @return array
	 */
	private function createMetaData($key, $type, $expire) {
		$metaData = array(
			"name" => $key,
			"created" => time(),
			"accessed" => time()
		);
		switch ($type) {
			case Storage::STORAGE_FLASH:
				$metaData["type"] = Storage::STORAGE_FLASH;
				// Number(s) of hoop before flash message automatically deleted
				$metaData["hoop"] = max(1, (int)$expire);
				// Tell system not to process this flash message since it's just added
				$metaData["gc"] = false;
				break;
			case Storage::STORAGE_VARIABLE:
			default:
				$metaData["type"] = Storage::STORAGE_VARIABLE;
				$metaData["expire"] = max(0, (int)$expire);
				break;
		}

		$this->metaData[$key] = $metaData;

		return $this->metaData[$key];
	}

	/**
	 * Remove any expired data before iteration or counting
	 *
	 * @see Storage::count()
	 * @see Storage::getIterator()
	 */
	private function removeExpiredData() {
		foreach (array_keys($this->metaData) as $key) {
			$metaData = $this->getMetaDataByKey($key);
			if ($metaData["type"] == Storage::STORAGE_VARIABLE && $this->isExpired($metaData)) {
				$this->remove($key);
			} else if ($metaData["type"] == Storage::STORAGE_FLASH && !$this->isHoopValid($metaData)) {
				$this->removeFlash($key);
			}
		}
	}

	/**
	 * Check whether current key or meta data is expiring or not.
	 *
	 * If an array given then the signature must be from Storage::getMetaDataByKey
	 *
	 * @see Storage::getMetaDataByKey()
	 *
	 * @param string|array $metaData
	 *
	 * @throws \Lh\Collections\KeyNotFoundException
	 * @return bool
	 */
	public function isExpired($metaData) {
		if (is_string($metaData)) {
			$key = $metaData;
			$metaData = $this->getMetaDataByKey($metaData);
		} else {
			$key = $metaData["name"];
		}

		if ($metaData == null || $metaData["type"] !== Storage::STORAGE_VARIABLE) {
			throw new KeyNotFoundException("key", "Key '$key' don't exists in variable session type.");
		}

		if ($metaData["expire"] < 1) {
			return false;
		} else {
			return time() > ($metaData["accessed"] + $metaData["expire"]);
		}
	}

	/**
	 * Check whether current key exists as variable session data or not
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function contains($key) {
		$metaData = $this->getMetaDataByKey($key);
		if ($metaData === null || $metaData["type"] != Storage::STORAGE_VARIABLE) {
			return false;
		}

		try {
			if ($this->isExpired($metaData)) {
				$this->remove($key);

				return false;
			} else {
				return true;
			}
		} catch (KeyNotFoundException $ex) {
			return false;
		}
	}

	/**
	 * Add variable to session data
	 *
	 * Add value to current storage based on key. By default value stored will exists as long as user session exists.
	 * Specify time to expire if current value only valid for limited time (Ex: CAPTCHA)
	 *
	 * @param string   $key
	 * @param mixed    $value
	 * @param null|int $timeToExpire
	 *
	 * @throws \Lh\Collections\KeyExistsException
	 */
	public function add($key, $value, $timeToExpire = null) {
		if ($this->contains($key)) {
			throw new KeyExistsException('key', "Unable to add key '$key' to current storage since its already exists. Use set method instead.");
		}

		$this->set($key, $value, $timeToExpire);
	}

	/**
	 * Set previously added storage key (add one if key don't exists).
	 *
	 * @param string   $key
	 * @param mixed    $value
	 * @param null|int $timeToExpire
	 */
	public function set($key, $value, $timeToExpire = null) {
		if (($metaData = $this->getMetaDataByKey($key)) === null) {
			$metaData = $this->createMetaData($key, Storage::STORAGE_VARIABLE, ($timeToExpire === null) ? 0 : (int)$timeToExpire);
		} else {
			$metaData["accessed"] = time();
			if ($timeToExpire !== null) {
				// expireSet() in-lined for better performance
				$metaData["expire"] = is_numeric($timeToExpire) ? (int)$timeToExpire : 0;
			}
		}

		$this->lastAccessedAt = $metaData["accessed"];
		$this->metaData[$key] = $metaData;
		$this->values[$key] = $value;
	}

	/**
	 * Retrieve variable stored in current storage.
	 *
	 * @param string     $key
	 * @param null|mixed $default
	 *
	 * @return mixed|null
	 * @throws ClassNotFoundException
	 */
	public function get($key, $default = null) {
		if (!$this->contains($key)) {
			return $default;
		}

		$metaData = $this->getMetaDataByKey($key);
		$this->lastAccessedAt = $metaData["accessed"] = time();
		$this->metaData[$key] = $metaData;

		$buff = $this->values[$key];
		$incompleteClass = '__PHP_Incomplete_Class';
		if ($buff instanceof $incompleteClass) {
			// ToDo: Detect incomplete class name using serialize() to generate string representation then using ReGex to retrieve its class name
			throw new ClassNotFoundException("__PHP_Incomplete_Class", "Unable to reconstruct '$key' session data, Class definition not loaded yet. Please check your auto loader.");
		}

		return $buff;
	}

	/**
	 * Remove a variable from storage
	 *
	 * @param string $key
	 *
	 * @return bool true when removal is success
	 */
	public function remove($key) {
		unset($this->metaData[$key]);
		unset($this->values[$key]);

		return true;
	}

	/**
	 * Remove all non flash messages from current storage
	 */
	public function removeAll() {
		$keys = array_keys($this->values);
		foreach ($keys as $key) {
			unset($this->metaData[$key]);
			unset($this->values[$key]);
		}
	}

	/**
	 * Reset expire for specific key. Give null or 0 for session lifetime expiry time
	 *
	 * @param string   $key
	 * @param int|null $expire
	 *
	 * @throws \Lh\Collections\KeyNotFoundException
	 */
	public function setExpire($key, $expire) {
		if (($metaData = $this->getMetaDataByKey($key)) === null || $metaData["type"] != Storage::STORAGE_VARIABLE) {
			throw new KeyNotFoundException("key", "Unable to find key '$key' in current variable session. Unable to set expire.");
		}

		$metaData["expire"] = ($expire === null || !is_numeric($expire)) ? 0 : (int)$expire;
		$this->metaData[$key] = $metaData;
	}

	/**
	 * Retrieve expire data for current storage
	 *
	 * @param string $key
	 * @param bool   $flagAddLastAccess
	 *
	 * @return int PHP timestamp
	 * @throws \Lh\Collections\KeyNotFoundException
	 */
	public function getExpire($key, $flagAddLastAccess = false) {
		if (($metaData = $this->getMetaDataByKey($key)) === null || $metaData["type"] != Storage::STORAGE_VARIABLE) {
			throw new KeyNotFoundException("key", "Unable to find key '$key' in current storage. Unable to get expire data.");
		}

		if ($metaData["expire"] > 0) {
			return $flagAddLastAccess ? $metaData["accessed"] + $metaData["expire"] : $metaData["expire"];
		} else {
			return 0;
		}
	}

	/**
	 * Check whether current key or meta data is still a valid flash message or not.
	 * If an array given then the signature must be from Storage::getMetaDataByKey
	 *
	 * @see Storage::getMetaDataByKey()
	 *
	 * @param string|array $metaData
	 *
	 * @throws \Lh\Collections\KeyNotFoundException
	 * @return bool
	 */
	public function isHoopValid($metaData) {
		if (is_string($metaData)) {
			$key = $metaData;
			$metaData = $this->getMetaDataByKey($metaData);
		} else {
			$key = $metaData["name"];
		}

		if ($metaData == null || $metaData["type"] !== Storage::STORAGE_FLASH) {
			throw new KeyNotFoundException("key", "Key '$key' don't exists in flash messages.");
		}

		return $metaData["hoop"] > 0;
	}

	/**
	 * Check whether current key exists as flash message or not
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function containsFlash($key) {
		$metaData = $this->getMetaDataByKey($key);
		if ($metaData === null || $metaData["type"] != Storage::STORAGE_FLASH) {
			return false;
		}

		try {
			if (!$this->isHoopValid($metaData)) {
				$this->removeFlash($key);

				return false;
			}
		} catch (KeyNotFoundException $ex) {
			return false;
		}

		return true;
	}

	/**
	 * Add flash message into session storage
	 *
	 * Add flash message based on key. By default flash message will be deleted after 1 hoop
	 * Mainly used for store information message such as: Error, Info
	 *
	 * @param string   $key
	 * @param string   $value
	 * @param null|int $hoopToExpire
	 *
	 * @throws \Lh\Collections\KeyExistsException
	 */
	public function addFlash($key, $value, $hoopToExpire = 1) {
		if ($this->containsFlash($key)) {
			throw new KeyExistsException('key', "Unable to add key '$key' to current flash messages since its already exists. Use setFlash method instead.");
		}

		$this->setFlash($key, $value, $hoopToExpire);
	}


	/**
	 * Set previously added flash message (add one if key don't exists).
	 *
	 * @param string   $key
	 * @param string   $value
	 * @param null|int $hoopToExpire
	 */
	public function setFlash($key, $value, $hoopToExpire = null) {
		if (($metaData = $this->getMetaDataByKey($key)) === null) {
			$metaData = $this->createMetaData($key, Storage::STORAGE_FLASH, ($hoopToExpire === null) ? 1 : (int)$hoopToExpire);
		} else {
			$metaData["accessed"] = time();
			if ($hoopToExpire !== null) {
				// hoopSet() in-lined for better performance
				$metaData["hoop"] = is_numeric($hoopToExpire) ? (int)$hoopToExpire : 1;
				$metaData["gc"] = false;
			}
		}

		$this->lastAccessedAt = $metaData["accessed"];
		$this->metaData[$key] = $metaData;
		$this->flashMessages[$key] = $value;
	}

	/**
	 * Retrieve flash message. Retrieving flash message will not decreased its hoop
	 *
	 * @param string     $key
	 * @param null|mixed $default
	 *
	 * @return null|mixed
	 * @throws ClassNotFoundException
	 */
	public function getFlash($key, $default = null) {
		if (!$this->containsFlash($key)) {
			return $default;
		}

		$metaData = $this->getMetaDataByKey($key);
		$this->lastAccessedAt = $metaData["accessed"] = time();
		$this->metaData[$key] = $metaData;

		$buff = $this->flashMessages[$key];
		$incompleteClass = '__PHP_Incomplete_Class';
		if ($buff instanceof $incompleteClass) {
			// ToDo: Detect incomplete class name using serialize() to generate string representation then using ReGex to retrieve its class name

			throw new ClassNotFoundException("__PHP_Incomplete_Class", "Unable to reconstruct '$key' session data, Class definition not loaded yet. Please check your auto loader.");
		}

		return $buff;
	}

	/**
	 * Remove flash message
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function removeFlash($key) {
		unset($this->metaData[$key]);
		unset($this->flashMessages[$key]);

		return true;
	}

	/**
	 * Remove all flash messages from current storage
	 */
	public function removeAllFlash() {
		$keys = array_keys($this->flashMessages);
		foreach ($keys as $key) {
			unset($this->metaData[$key]);
			unset($this->flashMessages[$key]);
		}
	}

	/**
	 * Reset hoop of flash message.
	 *
	 * @param string $key
	 * @param int    $hoop
	 *
	 * @throws \Lh\Collections\KeyNotFoundException
	 */
	public function setHoop($key, $hoop) {
		if (($metaData = $this->getMetaDataByKey($key)) === null || $metaData["type"] != Storage::STORAGE_FLASH) {
			throw new KeyNotFoundException("key", "Unable to find key '$key' in current flash messages. Unable to set hoop.");
		}

		$metaData["hoop"] = is_numeric($hoop) ? (int)$hoop : 1;
		// Hoop counter reset, don't process this flash message inside gc
		$metaData["gc"] = false;
		$this->metaData[$key] = $metaData;
	}

	/**
	 * Retrieve hoop data from flash messages
	 *
	 * @param string $key
	 *
	 * @return int
	 * @throws \Lh\Collections\KeyNotFoundException
	 */
	public function getHoop($key) {
		if (($metaData = $this->getMetaDataByKey($key)) === null || $metaData["type"] != Storage::STORAGE_FLASH) {
			throw new KeyNotFoundException("key", "Unable to find key '$key' in current flash messages. Unable to get hoop data.");
		}

		return $metaData["hoop"];
	}

	/**
	 * Destroy current storage from $_SESSION variable.
	 *
	 * This method used for completely remove all session data. When use removeAll() and removeAllFlash() then storage trace still available at $_SESSION global
	 * variable. This method will remove residual storage trace. Residual storage trace only contain unique storage scope name.
	 */
	public function destroy() {
		unset($_SESSION[$this->_scope]);
	}

	/// REGION		- Magic methods for property like access
	/**
	 * Provide property like getter via magic method
	 *
	 * NOTE: prefix property name with 'flash' to access flash message
	 *
	 * @param string $name
	 *
	 * @return mixed|null
	 * @throws \Lh\Collections\KeyNotFoundException
	 */
	public function __get($name) {
		if ($this->contains($name)) {
			return $this->get($name);
		} else {
			if (strlen($name) > 5 && ($pos = strpos($name, "flash")) === 0) {
				// start as flashxxx
				$name = lcfirst(substr($name, 5));
				if ($this->containsFlash($name)) {
					return $this->getFlash($name);
				}
			}
		}

		throw new KeyNotFoundException("Unable to find '$name' at current session storage. It's neither variable nor flash message");
	}

	/**
	 * Provide property like setter via magic method
	 *
	 * NOTE: prefix property name with 'flash' to access flash message
	 *
	 * @param string $name
	 * @param mixed  $value
	 */
	public function __set($name, $value) {
		if (strlen($name) > 5 && ($pos = strpos($name, "flash")) === 0) {
			// start as flashxxx
			$name = lcfirst(substr($name, 5));
			$this->setFlash($name, $value);
		} else {
			$this->set($name, $value);
		}
	}

	/**
	 * Provide existence checking using isset()
	 *
	 * NOTE: prefix property name with 'flash' to access flash message
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset($name) {
		if (strlen($name) > 5 && ($pos = strpos($name, "flash")) === 0) {
			// start as flashxxx
			$name = lcfirst(substr($name, 5));

			return $this->containsFlash($name);
		} else {
			return $this->contains($name);
		}
	}

	/**
	 * Provida session data removal using unset()
	 *
	 * NOTE: prefix property name with 'flash' to access flash message
	 *
	 * @param string $name
	 */
	public function __unset($name) {
		if (strlen($name) > 5 && ($pos = strpos($name, "flash")) === 0) {
			// start as flashxxx
			$name = lcfirst(substr($name, 5));
			$this->removeFlash($name);
		} else {
			$this->remove($name);
		}
	}

	/// END REGION	- Magic methods

	/// REGION		- ArrayAccess implementations. These implementation is similat with magic methods just this is implemented so we can access by array
	/**
	 * Check whether current key exists in session storage or not.
	 *
	 * Actual implementation is similar with __isset() magic method
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset
	 *
	 * @return boolean true on success or false on failure.
	 */
	public function offsetExists($offset) {
		return $this->__isset($offset);
	}

	/**
	 * Retrieve value from session storage based on name.
	 *
	 * Actual implementation is similar with __get() magic method
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset
	 *
	 * @return mixed
	 */
	public function offsetGet($offset) {
		return $this->__get($offset);
	}

	/**
	 * Set value to current session storage.
	 *
	 * Actual implementation is similar with __set() magic method
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$this->__set($offset, $value);
	}

	/**
	 * Remove data from session storage.
	 *
	 * Actual implementation is similar with __unset() magic method
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset
	 *
	 * @return void
	 */
	public function offsetUnset($offset) {
		$this->__unset($offset);
	}

	/// END REGION	- ArrayAccess

	/// REGION		- Countable
	/**
	 * Count total data stored in current session (variables and flash messages)
	 *
	 * @link http://php.net/manual/en/countable.count.php
	 * @return int
	 */
	public function count() {
		$this->removeExpiredData();

		return count($this->metaData);
	}

	/// END REGION	- Countable

	/// REGION		- IteratorAggregate
	/**
	 * Retrieve an external iterator
	 *
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return StorageIterator
	 */
	public function getIterator() {
		$this->removeExpiredData();

		return new StorageIterator($this);
	}
	/// END REGION	- IteratorAggregate
}

// End of File: Storage.php 