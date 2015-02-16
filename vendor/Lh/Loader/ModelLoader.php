<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Loader;

use Lh\Web\Application;

/**
 * Class ModelLoader
 *
 * This class will automatically load any class from Model folder in your application source folder. By default application source folder will be at 'src/' folder.
 * Application source folder can be changed while initializing Web Application from 'config/system/application.config.php'
 *
 * @package Lh\Loader
 */
class ModelLoader extends LoaderBase {
	/** @var string Where model class reside */
	protected $modelPath = null;

	/**
	 * Create new instance of ModelLoader
	 *
	 * @param array $options
	 */
	public function __construct($options = array()) {
		parent::__construct($options);
		$this->modelPath = Application::getInstance()->getModelPath();
	}

	/**
	 * Perform auto load
	 *
	 * Model auto loader will look at Model folder from your application source folder
	 * This will look-up based on:
	 *    1. Namespace and class name (based on PHP >= 5.3.0)
	 *    2. Class name which prepend with folder name using '_' (based on PHP < 5.3.0)
	 *
	 * @param string $className
	 *
	 * @return string
	 */
	public function autoLoad($className) {
		$path = $this->modelPath . str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $className) . ".php";

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

// End of File: ModelLoader.php 