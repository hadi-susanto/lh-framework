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
 * Class GenericLoader
 *
 * Generic auto loader based which can be adjusted based on user criteria. While DefaultLoader always looking up at VENDOR_PATH,
 * this Generic pattern can be looking at more than one location at once. This location can be changed by 'paths' key options and
 * specify array of location in ascending order.
 *
 * @package Lh\Loader
 */
class GenericLoader extends LoaderBase {
	/** @var string[] Lookup path(s) for class file definition */
	protected $paths;

	/**
	 * Create new instance of GenericLoader
	 *
	 * @param array $options
	 */
	public function setOptions($options) {
		parent::setOptions($options);
		if (isset($options["paths"]) && is_array($options["paths"])) {
			$this->paths = array();
			foreach ($options["paths"] as $path) {
				$this->paths[] = realpath($path) . DIRECTORY_SEPARATOR;
			}
		} else {
			$this->paths = array(
				Application::getInstance()->getModelPath(),
				Application::getInstance()->getVendorPath()
			);
		}
	}

	/**
	 * Perform auto load
	 *
	 * This auto loader will always trying to load a class from path(s) specified in $this->paths variable.
	 * This will look-up based on:
	 *    1. Namespace and class name (based on PHP >= 5.3.0)
	 *    2. Class name which prepend with folder name using '_' (based on PHP < 5.3.0)
	 *
	 * @param string $className
	 *
	 * @return string
	 */
	public function autoLoad($className) {
		$result = IAutoLoader::LOAD_UNKNOWN;
		foreach ($this->paths as $basePath) {
			$path = $basePath . str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $className) . ".php";

			try {
				if (!is_file($path) || !is_readable($path)) {
					$result = IAutoLoader::LOAD_FILE_NOT_FOUND;
					continue;
				}

				require_once($path);
				if (!class_exists($className, false) && !interface_exists($className, false)) {
					$result = IAutoLoader::LOAD_CLASS_NOT_EXISTS;
					continue;
				}

				$result = IAutoLoader::LOAD_SUCCESS;
				break;
			} catch (\Exception $ex) {
				$this->setLastException($ex);
				$result = IAutoLoader::LOAD_EXCEPTION;
			}
		}

		return $result;
	}
}

// End of File: GenericLoader.php 