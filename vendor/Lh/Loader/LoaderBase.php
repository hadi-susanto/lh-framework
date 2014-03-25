<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Loader;

/**
 * Class LoaderBase
 *
 * Basic class for every loader should have these feature / methods. Any derived class should only implements autoLoad()
 * which perform loading a php script based on given class name. Basically all loader should not registered them self,
 * LoaderManager is responsible to registering each loader and calling autoLoad() method.
 *
 * @package Lh\Loader
 */
abstract class LoaderBase implements IAutoLoader {
	/** @var bool Throw exception is used when manual register used */
	protected $throwException = false;
	/** @var bool Should this class loader prepended or appended when manual register used  */
	protected $isPrepend = false;
	/** @var bool Registered flag */
	protected $isRegistered = false;
	/** @var \Exception Store last exception when trying to load a class */
	protected $lastException = null;

	/**
	 * Create new instance of loader
	 *
	 * @param array $options
	 */
	public function __construct($options = array()) {
		$this->setOptions($options);
	}

	/**
	 * Get AutoLoader Fully Qualified Name.
	 *
	 * @return string
	 */
	public function getName() {
		return get_class($this);
	}

	/**
	 * Setting any options to affect current loader behaviour.
	 *
	 * @param array $options
	 *
	 * @return void
	 */
	public function setOptions($options) {
		$this->setRegisterMode(isset($options["registerMode"]) ? $options["registerMode"] : IAutoLoader::MODE_DEFAULT);
		$this->setThrowException(isset($options["throwException"]) ? (bool)$options["throwException"] : false);
	}

	/**
	 * Set register mode flag
	 *
	 * Register mode flag used when manual registration used. . Manual registration use spl_autoload_register to
	 * register autoLoad() method. Please see IAutoLoader::MODE_* for all available modes
	 *
	 * @param string $mode
	 *
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function setRegisterMode($mode = IAutoLoader::MODE_DEFAULT) {
		switch ($mode) {
			case IAutoLoader::MODE_DEFAULT:
			case IAutoLoader::MODE_APPEND:
				$this->isPrepend = false;
				break;
			case IAutoLoader::MODE_PREPEND:
				$this->isPrepend = true;
				break;
			default:
				throw new \InvalidArgumentException("Unknown mode given for register mode. Given value: '$mode'");
		}
	}

	/**
	 * Set throw exception flag
	 *
	 * Throw exception flag used when manual registration is used. Manual registration use spl_autoload_register to
	 * register autoLoad() method.
	 *
	 * @param bool $flag
	 *
	 * @return void
	 */
	public function setThrowException($flag) {
		$this->throwException = (bool)$flag;
	}

	/**
	 * Check whether this instance already registered in auto loader stack or not
	 *
	 * @return bool
	 */
	public function isRegistered() {
		return $this->isRegistered;
	}

	/**
	 * Manually register this auto loader
	 *
	 * Manual registration done by spl_autoload_register() from native PHP function. Use this method if your auto loader not registered
	 * in LoaderManager. This may useful if an autoloader only required for specific time or specific purpose. But generally it's not
	 * recommended to registering autoloader manually
	 *
	 * @return bool
	 */
	public function register() {
		$this->isRegistered = spl_autoload_register(array($this, "autoLoad"), $this->throwException, $this->isPrepend);

		return $this->isRegistered();
	}

	/**
	 * Un-registering this class from PHP autoloader stack
	 *
	 * This method will remove current class from PHP autoloader stack. Un-registering using this method only effective if you manually register
	 * itself using register() method. When current class registered by LoaderManager then you must removing it using LoaderManager
	 *
	 * @see spl_autoload_unregister
	 * @return bool
	 */
	public function unRegister() {
		$this->isRegistered = !spl_autoload_unregister(array($this, "autoLoad"));

		return !($this->isRegistered());
	}

	/**
	 * Get last exception occurred when auto load a class
	 *
	 * AutoLoader class MUST NOT ever throw an exception. Any exception occurred must be stored independently therefore application still running.
	 * When user code requesting un-exist class, error will occurred in user code space. This is intended way for easy tracking. If exception thrown
	 * from this class there is no way user know which code calling non-existed class. They only know the class name.
	 *
	 * @return \Exception
	 */
	public function getLastException() {
		return $this->lastException;
	}

	/**
	 * Enable direct printing of class name using magic method
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->getName();
	}

	/**
	 * Set last exception occurred in loader
	 *
	 * Since loader class SHOULD NOT throw any exception
	 *
	 * @param \Exception $ex
	 */
	protected function setLastException(\Exception $ex) {
		$this->lastException = $ex;
	}
}

// End of File: LoaderBase.php 