<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Session;

use Lh\Exceptions\ClassNotFoundException;
use Lh\Exceptions\InvalidConfigException;
use Lh\Io\FileNotFoundException;
use Lh\ServiceBase;
use Lh\ServiceLocator;
use Lh\Web\Application;

/**
 * Class SessionManager
 *
 * @package Lh\Session
 */
class SessionManager extends ServiceBase {
	/** @var bool Is started flag */
	private $started = false;
	/** @var ISessionHandler Session handler */
	private $handler;

	/**
	 * Create new instance of SessionManager
	 *
	 * @param ServiceLocator $serviceLocator
	 */
	public function __construct(ServiceLocator $serviceLocator) {
		parent::__construct($serviceLocator);
		$serviceLocator->setSessionManager($this);
		$this->storage = array();
	}

	/**
	 * Check whether current session already started or not
	 *
	 * @return bool
	 */
	public function isStarted() {
		return $this->started;
	}

	/**
	 * Alias for session_id()
	 *
	 * @see session_id()
	 * @return string
	 */
	public function getSessionId() {
		return session_id();
	}

	/**
	 * SessionManager initialization
	 *
	 * Initializing session handler for current request. Options:
	 *  - 'handler'		=> Definition of custom session handling
	 * 						Definition example:
	 * 						1. 'sessionHandlerClassName' (the class will be auto loaded by auto loaders)
	 *						2. array('class' => 'ClassName'[, 'file' => 'fileLocation'][, 'options' => array])
	 *  - 'autoStart'	=> Automatically start session after initializing
	 *
	 * @param array $options
	 */
	public function _init(array $options) {
		if (isset($options["handler"]) && !empty($options["handler"])) {
			$this->setSessionHandler($options["handler"]);
		}
		Storage::setManager($this);
		if (isset($options["autoStart"]) && is_bool($options["autoStart"]) && $options["autoStart"]) {
			$this->start();
		}
	}

	/**
	 * Start session management.
	 *
	 * NOTE: $_SESSION will not available before calling this or explicitly by session_start() which is discouraged
	 *
	 * @see session_start()
	 */
	public function start() {
		if ($this->isStarted()) {
			return;
		}

		session_start();
		$this->started = true;
	}

	/**
	 * Destroy session
	 *
	 * Destroying session done by session_destroy() function. By default destroying session will remove all session value(s)
	 * that's mean all of Storage object will be invalidated
	 *
	 * @param bool $resetSession
	 *
	 * @see Storage
	 * @see session_destroy()
	 */
	public function destroy($resetSession = true) {
		if ($resetSession) {
			unset($_SESSION);
		}
		session_destroy();
	}

	/**
	 * Regenerate session ID
	 *
	 * Regenerate session ID is performed by calling session_regenerate_id() which will destroy session an re-create a new one.
	 * This method will destroy or invalidated all of your session data by default.
	 *
	 * @param bool $resetSession
	 */
	public function regenerateSessionId($resetSession = true) {
		if ($resetSession) {
			unset($_SESSION);
		}
		session_regenerate_id($resetSession);
	}

	/**
	 * Set custom session handler
	 *
	 * Definition for custom SessionHandler. Definitions can be class name or array with current specification:
	 *  - 'class'	=> Class name which implements ISessionHandler
	 *  - 'file'	=> [OPTIONAL] file location containing class definition
	 *  - 'options'	=> [OPTIONAL] options for session handler
	 *
	 * @param $definition
	 */
	private function setSessionHandler($definition) {
		try {
			if (is_string($definition)) {
				$sessionHandler = $definition;
				$source = null;
				$options = null;
			} else {
				if (is_array($definition)) {
					$sessionHandler = isset($definition["class"]) ? $definition["class"] : null;
					$source = isset($definition["file"]) ? $definition["file"] : null;
					$options = isset($definition["options"]) ? $definition["options"] : null;
				} else {
					throw new InvalidConfigException(APPLICATION_PATH . "config/system/application.config.php", "Invalid value for key 'handler' in section 'sessionManager'. It's only accept string or array");
				}
			}
			// Final Checking
			if ($sessionHandler == null) {
				throw new InvalidConfigException(APPLICATION_PATH . "config/system/application.config.php", "Invalid value for key 'handler' in section 'sessionManager'. It's only accept string or array");
			}

			if ($source !== null) {
				// Source file available, try to load
				if (is_file($source)) {
					require_once($source);
				} else {
					throw new FileNotFoundException($source, "Unable to find '$source' in current folder: '" . getcwd() . "'");
				}
			}

			if (!class_exists($sessionHandler, true)) {
				if ($source !== null) {
					throw new ClassNotFoundException($sessionHandler, "Unable to create instance of '$sessionHandler'. Given source file is doesn't contains appropriate class definition.");
				} else {
					throw new ClassNotFoundException($sessionHandler, "Unable to create instance of '$sessionHandler'. Auto loading class is failed.");
				}
			}

			// Class definition should be loaded here
			$className = $sessionHandler;
			$sessionHandler = new $className();
			unset($className);

			if (!($sessionHandler instanceof ISessionHandler)) {
				throw new \InvalidArgumentException("Can't create instance of ISessionHandler from: " . get_class($sessionHandler));
			}

			$this->handler = $sessionHandler;
			$sessionHandler->setOptions($options);

			// Override PHP session handling
			session_set_save_handler(
				array($sessionHandler, 'open'),
				array($sessionHandler, 'close'),
				array($sessionHandler, 'read'),
				array($sessionHandler, 'write'),
				array($sessionHandler, 'destroy'),
				array($sessionHandler, 'gc')
			);

			// Prevent un-expected side-effects from the way PHP internally destroys objects on shutdown and may prevent the session write and close from being called
			register_shutdown_function('session_write_close');
		} catch (\Exception $ex) {
			$this->addExceptionTrace($ex, __METHOD__);
			$this->handler = null;
		}
	}

	/**
	 * Garbage collection
	 *
	 * Perform garbage collect for each object in $_SESSION. This will remove any expired session data and decrement loop counter for flash messages.
	 * Upon completion session will stored by issuing session_write_close()
	 *
	 * @see session_write_close()
	 */
	public function gc() {
		if (!$this->isStarted()) {
			return;
		}

		$prefix = Application::getInstance()->getCrc32() . "_";
		$currentTime = time();
		foreach ($_SESSION as $name => &$storage) {
			if (strpos($name, $prefix) === false) {
				// Not our session (maybe another framework instance running in different folder)
				continue;
			} else if ($storage['__TIME_LIMIT'] > 0 && $currentTime > ($storage['__LAST_ACCESS'] + $storage['__TIME_LIMIT'])) {
				unset($_SESSION[$name]);
				continue;
			}

			foreach ($storage['__META'] as $key => &$metaData) {
				if ($metaData["type"] == Storage::STORAGE_VARIABLE) {
					if ($metaData["expire"] > 0 && $currentTime > ($metaData["accessed"] + $metaData["expire"])) {
						// OK Current variable key is expired
						unset($storage['__META'][$key]);
						unset($storage['__VALUES'][$key]);
					}
				} else if ($metaData["type"] == Storage::STORAGE_FLASH) {
					if ($metaData["gc"]) {
						$metaData["hoop"]--;
					} else {
						$metaData["gc"] = true;
					}

					if ($metaData["hoop"] < 1) {
						unset($storage['__META'][$key]);
						unset($storage['__FLASH_MESSAGES'][$key]);
					}
				}
			}
		}
		// OK process complete, removing session
		session_write_close();
	}
}

// End of File: SessionManager.php 