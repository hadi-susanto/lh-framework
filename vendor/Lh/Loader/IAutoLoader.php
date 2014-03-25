<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Loader;

/**
 * Interface IAutoLoader
 *
 * This Interface created for supporting user custom AutoLoader. Any derived class should be free from throwing exception.
 * Any exception occurred in this class should be stored for later investigation
 * Should be noted that register method only called if you want to register this auto loader independently without LoaderManager class
 *
 * @see LoaderManager
 *
 * @package Lh\Loader
 */
interface IAutoLoader {
	const MODE_DEFAULT = "DEFAULT";
	const MODE_PREPEND = "PREPEND";
	const MODE_APPEND = "APPEND";

	const LOAD_SUCCESS = 1;				// Class loading completed without error
	const LOAD_SKIPPED = 2;				// Current loader skipped requested class intentionally
	const LOAD_FILE_NOT_FOUND = -1;		// Class resolution success but required file is not found
	const LOAD_CLASS_NOT_EXISTS = -2;	// Although file is found and successfully loaded but class definition still missing.
	const LOAD_ALREADY_LOADED = 3;		// Requested class already loaded by current loader or by other loader
	const LOAD_EXCEPTION = -98;			// Exception occurred when auto loader in process
	const LOAD_UNKNOWN = -99;

	/**
	 * Get AutoLoader Fully Qualified Name.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * AutoLoader class MUST NOT ever throw an exception. Any exception occurred must be stored independently for further investigation
	 *
	 * @return \Exception
	 */
	public function getLastException();

	/**
	 * Setting any options to affect current loader behaviour.
	 *
	 * @param array $options
	 *
	 * @return void
	 */
	public function setOptions($options);

	/**
	 * Setting register mode for spl_autoload_register
	 *
	 * @param string $mode
	 *
	 * @return void
	 */
	public function setRegisterMode($mode = IAutoLoader::MODE_DEFAULT);

	/**
	 * Tell spl_autoload_register whether php should throw exception when registration is failed ot not
	 *
	 * @param $flag
	 *
	 * @return void
	 */
	public function setThrowException($flag);

	/**
	 * Check whether this instance already registered in auto loader stack or not
	 *
	 * @return bool
	 */
	public function isRegistered();

	/**
	 * Manually register this auto loader
	 *
	 * Manual registration done by spl_autoload_register() from native PHP function. Use this method if your auto loader not registered
	 * in LoaderManager. This may useful if an autoloader only required for specific time or specific purpose. But generally it's not
	 * recommended to registering autoloader manually
	 *
	 * @return bool
	 */
	public function register();

	/**
	 * Un-registering this interface / derived class from auto loader stack
	 *
	 * @see spl_autoload_unregister
	 * @return bool
	 */
	public function unRegister();

	/**
	 * Perform the actual class loading based on the given class name.
	 *
	 * @param string $className
	 *
	 * @return int status code from IAutoLoader::LOAD_*
	 */
	public function autoLoad($className);
}

// End of File: IAutoLoader.php 