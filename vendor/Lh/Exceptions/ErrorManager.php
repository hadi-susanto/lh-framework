<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Exceptions;

use Exception;
use Lh\Collections\KeyExistsException;
use Lh\Io\FileNotFoundException;
use Lh\ServiceBase;
use Lh\ServiceLocator;

/**
 * Class ErrorManager
 *
 * This class will manage any catchable error from PHP code. Please note that not every PHP error are caught by this class.
 * Compile error will not handled by this class, any error which occurred before this class registered is not caught and handled by PHP.
 * It's really recommended to add an custom error handler which logs any error caused by user code
 *
 * @see IErrorHandler
 * @package Lh\Exceptions
 */
class ErrorManager extends ServiceBase {
	const OVERRIDE_ALLOW = "ALLOW";
	const OVERRIDE_DENY = "DENY";
	const OVERRIDE_THROW_EXCEPTION = "EXCEPTION";

	/** @var string How we handle existing error handler */
	private $overrideBehaviour;
	/** @var bool Determine whether default error handler will be executed or not */
	private $preventDefault;
	/** @var bool Determine whether un-caught exception trapped by ErrorManager or not */
	private $isExceptionTrapped = false;
	/** @var DefaultHandler Default handler which mimic PHP error handler */
	private $defaultErrorHandler;
	/** @var IErrorHandler[] Registered error handler(s) */
	private $errorHandlers = array();

	/**
	 * Create new instance of ErrorManager
	 *
	 * @param ServiceLocator $serviceLocator
	 */
	public function __construct(ServiceLocator $serviceLocator) {
		parent::__construct($serviceLocator);
		$serviceLocator->setErrorManager($this);
	}

	/**
	 * Get whether default error handler enabled or not
	 *
	 * @return bool
	 */
	public function isDefaultPrevented() {
		return $this->preventDefault;
	}

	/**
	 * Setting behaviour of default error handler
	 *
	 * @param bool $value
	 */
	public function setDefaultPrevented($value) {
		$this->preventDefault = (bool)$value;
	}

	/**
	 * Check whether exception is trapped by framework or not
	 *
	 * @return bool
	 */
	public function isExceptionTrapped() {
		return $this->isExceptionTrapped;
	}

	/**
	 * Return user custom error handler + default one
	 *
	 * @return IErrorHandler[]
	 */
	public function getErrorHandlers() {
		return array_merge($this->errorHandlers, array("default" => $this->defaultErrorHandler));
	}

	/**
	 * Initialize ErrorManager
	 *
	 * Initialize ErrorManager based on user options. Available user options:
	 *  - 'preventDefault'	=> Disable default error handler from catching any error. @see DefaultHandler
	 *  - 'trapException'	=> Should ErrorManager handle un-caught exception ?
	 *  - 'override'		=> Tell ErrorManager how to override an existing error handler
	 *  - 'handlers'		=> array of custom error handler definition. These class must be implements @see IErrorHandler
	 *                           Definition example:
	 *                           1. 'errorHandlerClassName' (the class will be auto loaded by auto loaders)
	 *                           2. array('class' => 'ClassName'[, 'file' => 'fileLocation'][, 'options' => array])
	 *
	 * @see IErrorHandler
	 * @see DefaultHandler
	 *
	 * @param array $options
	 *
	 * @return bool
	 */
	public function _init(array $options) {
		// Should we prevent default handler from executing their methods ?
		$this->setDefaultPrevented(isset($options["preventDefault"]) ? (bool)$options["preventDefault"] : false);
		// How to handle same name error handler
		$this->overrideBehaviour = isset($options["override"]) ? $options["override"] : self::OVERRIDE_DENY;

		// Create default auto loader
		$this->defaultErrorHandler = new DefaultHandler();
		// Create user error handler
		if (isset($options["handlers"]) && is_array($options["handlers"])) {
			$this->processCustomErrorHandlers($options["handlers"]);
		}

		set_error_handler(array($this, 'errorHandler'));
		if (isset($options["trapException"]) && is_bool($options["trapException"]) && $options["trapException"]) {
			$this->beginTrapException();
		}

		return true;
	}

	/**
	 * Process custom error handler
	 *
	 * Processing user custom error handlers from configuration file. Array parameter can be:
	 * 1. string containing class name
	 * 2. array definition ('class' => 'ClassName'[, 'file' => 'fileLocation'][, 'options' => array])
	 *
	 * @param array $handlers
	 */
	private function processCustomErrorHandlers(array $handlers) {
		foreach ($handlers as $name => $handler) {
			if (is_numeric($name)) {
				$name = "errorHandler_" . $name;
			}

			if (is_string($handler)) {
				$class = $handler;
				$source = null;
				$options = null;
			} else if (is_array($handler)) {
				$class = isset($handler["class"]) ? $handler["class"] : null;
				$source = isset($handler["file"]) ? $handler["file"] : null;
				$options = (isset($handler["options"]) && is_array($handler["options"])) ? $handler["options"] : array();
			} else {
				$this->addExceptionTrace(new InvalidConfigException(APPLICATION_PATH . "config/system/application.config.php", "Invalid value for key 'handlers' in section 'errorManager'. Please check '$name' configuration it's only accept string or array"), __METHOD__);
				continue;
			}

			try {
				$this->addErrorHandler($name, $class, $source, $options);
			} catch (Exception $ex) {
				$this->addExceptionTrace($ex, __METHOD__);
			}
		}
	}

	/**
	 * Tell ErrorManager to trap un-caught Exception
	 */
	public function beginTrapException() {
		if ($this->isExceptionTrapped) {
			return;
		}

		set_exception_handler(array($this, "exceptionHandler"));
		$this->isExceptionTrapped = true;
	}

	/**
	 * Stop ErrorManager from trap un-caught Exception
	 */
	public function stopTrapException() {
		restore_exception_handler();
		$this->isExceptionTrapped = false;
	}

	/**
	 * Add an error handler
	 *
	 * Add another error handler into framework error handler stack. Each registered error handler will be called in case of error, therefore using error handler
	 * for logging purpose is really recommended.
	 *
	 * @param string               $name
	 * @param string|IErrorHandler $errorHandler
	 * @param null|string          $source
	 * @param null|array           $options
	 *
	 * @return bool
	 * @throws \Lh\Collections\KeyExistsException
	 * @throws ClassNotFoundException
	 * @throws \InvalidArgumentException
	 * @throws \Lh\Io\FileNotFoundException
	 */
	public function addErrorHandler($name, $errorHandler, $source = null, $options = null) {
		$name = trim($name);
		if ($name == "default") {
			throw new \InvalidArgumentException("Can't use 'default' as error handler name. This keyword is reserved for system default error handler.");
		}

		if (array_key_exists($name, $this->errorHandlers)) {
			switch ($this->overrideBehaviour) {
				case self::OVERRIDE_ALLOW:
					break;
				case self::OVERRIDE_DENY:
					return false;
				case self::OVERRIDE_THROW_EXCEPTION:
				default:
					throw new KeyExistsException("name", "Key: '$name' already registered as AutoLoader.");
			}
		}

		if (is_string($errorHandler)) {
			// Trying to instantiated current IErrorHandler from class name.
			if ($source !== null) {
				// Source file available, try to load
				if (is_file($source)) {
					require_once($source);
				} else {
					throw new FileNotFoundException($source, "Unable to find '$source' in current folder: '" . getcwd() . "'");
				}
			}

			if (!class_exists($errorHandler, true)) {
				if ($source !== null) {
					throw new ClassNotFoundException($errorHandler, "Unable to create instance of '$errorHandler'. Given source file is doesn't contains appropriate class definition.");
				} else {
					throw new ClassNotFoundException($errorHandler, "Unable to create instance of '$errorHandler'. Auto loading class is failed.");
				}
			}

			// Class definition should be loaded here
			$className = $errorHandler;
			$errorHandler = new $className();
			unset($className);
		}

		if (!($errorHandler instanceof IErrorHandler)) {
			throw new \InvalidArgumentException("Can't create instance of IErrorHandler from: " . get_class($errorHandler));
		}

		if ($options !== null) {
			$errorHandler->setOptions($options);
		}

		$this->errorHandlers[$name] = $errorHandler;

		return true;
	}

	/**
	 * Remove an error handler
	 *
	 * @param string $name
	 *
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function removeErrorHandler($name) {
		if (is_numeric($name)) {
			$name = "errorHandler_" . $name;
		}
		$name = trim($name);
		if ($name == "default") {
			throw new \InvalidArgumentException("Can't remove 'default' error handler. Disable default error handler instead.");
		}

		if (!array_key_exists($name, $this->errorHandlers)) {
			return false;
		}

		unset($this->errorHandlers[$name]);

		return !array_key_exists($name, $this->errorHandlers);
	}

	/**
	 * Framework main entry of error handler
	 *
	 * Framework error handler. This method registered as global PHP error instead method(s) from IErrorHandler
	 * Differ from auto loading class which stackable, PHP error handler is not stackable therefore this method will call
	 * method(s) from IErrorHandler. This way we provide 'stackable' error handler.
	 *
	 * @param int    $code
	 * @param string $message
	 * @param string $file
	 * @param int    $line
	 * @param array  $context
	 *
	 * @return bool
	 */
	public function errorHandler($code, $message, $file, $line, $context) {
		$isHandled = true;
		foreach ($this->errorHandlers as $errorHandler) {
			$isHandled = $isHandled && $errorHandler->handleError($code, $message, $file, $line, $context);
		}
		if (!$this->preventDefault) {
			$isHandled = $isHandled && $this->defaultErrorHandler->handleError($code, $message, $file, $line, $context);
		}

		return $isHandled;
	}

	/**
	 * Framework exception handler. This method registered as global PHP error instead method(s) from IErrorHandler
	 * Differ from auto loading class which stackable, PHP error handler is not stackable therefore this method will call
	 * method(s) from IErrorHandler. This way we provide 'stackable' error handler.
	 *
	 * @param Exception $ex
	 */
	public function exceptionHandler(Exception $ex) {
		foreach ($this->errorHandlers as $errorHandler) {
			$errorHandler->handleException($ex);
		}
		if (!$this->preventDefault) {
			$this->defaultErrorHandler->handleException($ex);
		}
	}
}

// End of File: ErrorManager.php 