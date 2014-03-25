<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Loader;

use Lh\Web\Application;

/**
 * Class DefaultLoader
 *
 * Default loader to load any class which resides at VENDOR folder. This will load any class based on their class name and their namespace.
 * Every namespace will be translated into folder and their class name will act as filename (appended by .php extension automatically)
 * Special notes for classes name with underscore. A word before underscore will be translated as folder name. For compatibility with other library.
 * Ex: class name 'Com_Example_AwesomeClassName' will be translated into 'Com/Example/AwesomeClassName.php'
 *
 * @package Lh\Loader
 */
class DefaultLoader extends LoaderBase {
	/** @var string Where vendor folder reside */
	protected $vendorPath;

	/**
	 * Create new instance of DefaultLoader
	 *
	 * @param array $options
	 */
	public function __construct($options = array()) {
		parent::__construct($options);
		$this->vendorPath = Application::getInstance()->getVendorPath();
	}


	/**
	 * Perform auto load
	 *
	 * This default auto loader will always trying to load a class from vendor folder.
	 * This will look-up based on:
	 *    1. Namespace and class name (based on PHP >= 5.3.0)
	 *    2. Class name which prepend with folder name using '_' (based on PHP < 5.3.0)
	 *
	 * @param string $className
	 *
	 * @throws \Exception
	 * @return string
	 */
	public function autoLoad($className) {
		$path = $this->vendorPath . str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $className) . ".php";

		try {
			if (!is_file($path) || !is_readable($path)) {
				return IAutoLoader::LOAD_FILE_NOT_FOUND;
			}

			require_once($path);
			if (!class_exists($className, false) && !interface_exists($className, false)) {
				return IAutoLoader::LOAD_CLASS_NOT_EXISTS;
			}

			return IAutoLoader::LOAD_SUCCESS;
		} catch (\Exception $ex) {
			$this->setLastException($ex);
			return IAutoLoader::LOAD_EXCEPTION;
		}
	}
}

// End of File: DefaultLoader.php 