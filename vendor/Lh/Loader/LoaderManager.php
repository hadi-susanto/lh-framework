<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Loader;

use Exception;
use Lh\ApplicationException;
use Lh\Collections\KeyExistsException;
use Lh\Exceptions\ClassNotFoundException;
use Lh\Exceptions\InvalidConfigException;
use Lh\Io\FileNotFoundException;
use Lh\ServiceBase;
use Lh\ServiceLocator;
use Lh\Web\Application;

/**
 * Class LoaderManager
 *
 * Used to manage registered auto loader classes OR to load a class manually from your code.
 * Please avoid misunderstanding between this class with autoloader stack from PHP.
 * PHP autoloader stack is storing function(s) from each auto loader class which implements IAutoLoader.
 * This class will manage each auto loader class and calling autoLoad method from it to load a class.
 *
 * IMPORTANT about default behaviour of LoaderManager:
 * This class will register LoaderManager::autoLoad() to spl_autoload_register function from PHP but NOT each auto loader autoLoad method.
 * Then this method will call autoLoad from each registered auto loader.
 * Using this pattern we able to control how auto loader behave like priority or skipping a auto loader
 *
 * @see IAutoLoader
 * @package Lh\Loader
 */
class LoaderManager extends ServiceBase {
	const OVERRIDE_ALLOW = "ALLOW";
	const OVERRIDE_DENY = "DENY";
	const OVERRIDE_THROW_EXCEPTION = "EXCEPTION";

	/**
	 * @var bool Flag telling how auto load handled by framework
	 *
	 * If this flag enabled (by default is enabled) then class auto loading will be handled by LoaderManager. When PHP request for non-exists
	 * class then LoaderManager::autoLoad() called. Inside that method each registered auto loader will be used for load the class. But if this
	 * flag disabled then each registered auto loader will register itself.
	 */
	private $systemManaged = true;
	/** @var string How we handle existing loader */
	private $overrideBehaviour;
	/** @var IAutoLoader[] Registered auto loader */
	private $autoLoaders = array();
	/** @var DefaultLoader Default auto loader for LH Framework functionality */
	private $defaultAutoLoader = null;
	/** @var string[] Path(s) for manual loading */
	private $basePaths = array();

	/**
	 * Create new instance of LoaderManager
	 *
	 * @param ServiceLocator $serviceLocator
	 */
	public function __construct(ServiceLocator $serviceLocator) {
		parent::__construct($serviceLocator);
		$serviceLocator->setLoaderManager($this);
		$this->basePaths[] = Application::getInstance()->getVendorPath();
	}


	/**
	 * Return user custom auto loader + default one
	 *
	 * @return IAutoLoader[]
	 */
	public function getAutoLoaders() {
		return array_merge($this->autoLoaders, array("default" => $this->defaultAutoLoader));
	}

	/**
	 * Initialize LoaderManager
	 *
	 * Initialize LoaderManager based on user options. Available user options:
	 *  - 'override'	=> Tell Loader manager how to override an existing auto loader
	 *  - 'loaders'		=> array of custom auto loader definition. These class must be implements @see IAutoLoader
	 * 					   Definition example:
	 *                     1. 'loaderClassName' (this class will be auto loaded based on class name)
	 *                     2. array('class' => 'loaderClassName'[, 'file' => 'loaderSourceFile'][, 'options' => array options])
	 *  - 'managed'		=> Tell loader manager to managed all auto loading function from this class instead of each AutoLoader class
	 *  - 'paths'		=> Additional lookup path for manual load
	 *
	 * @param array $options
	 *
	 * @see LoaderManager::autoLoad()
	 * @see LoaderManager::load()
	 */
	protected function _init(array $options) {
		// Does auto load is managed by LoaderManager or by each instance ?
		$this->systemManaged = (isset($options["managed"]) ? (bool)$options["managed"] : true);
		$this->overrideBehaviour = isset($options["override"]) ? $options["override"] : self::OVERRIDE_DENY;

		// Create default auto loader
		$this->defaultAutoLoader = new DefaultLoader();

		// Add user defined auto loaders
		if (isset($options["loaders"]) && is_array($options["loaders"])) {
			$this->processCustomAutoLoaders($options["loaders"]);
		}
		if ($this->systemManaged) {
			// OK registering this class as auto loader function with higher priority
			// Auto loading is managed by LH Framework
			spl_autoload_register(array($this, "autoLoad"), true, true);
		} else {
			// Auto loading will be managed by PHP
			foreach ($this->getAutoLoaders() as $autoLoader) {
				if (!$autoLoader->register()) {
					$this->addExceptionTrace(new ApplicationException("AutoLoader '" . $autoLoader->getName() . "' registration failed!"), __METHOD__);
				}
			}
		}

		// Additional path for manual loading
		if (isset($options["paths"]) && is_array($options["paths"])) {
			foreach ($options["paths"] as $path) {
				$this->basePaths[] = realpath($path) . DIRECTORY_SEPARATOR;
			}
		}
	}

	/**
	 * Processing custom autoloader from config file
	 *
	 * Load user custom auto loaders from configuration file. Array parameter can be:
	 * 1. string containing class name
	 * 2. array definition ('class' => 'ClassName'[, 'file' => 'fileLocation'][, 'options' => array])
	 *
	 * NOTE: If auto loader class not found, framework will try load it using DeafultLoader
	 *
	 * @see DefaultLoader
	 *
	 * @param array $autoLoaders
	 */
	private function processCustomAutoLoaders(array $autoLoaders) {
		// register default auto loader temporarily since some weird error occurred while processing custom auto loaders
		$this->defaultAutoLoader->register();

		foreach ($autoLoaders as $name => $autoLoader) {
			if (is_numeric($name)) {
				$name = "autoLoader_" . $name;
			}

			if (is_string($autoLoader)) {
				$class = $autoLoader;
				$source = null;
				$options = null;
			} else if (is_array($autoLoader)) {
				$class = isset($autoLoader["class"]) ? $autoLoader["class"] : null;
				$source = isset($autoLoader["file"]) ? $autoLoader["file"] : null;
				$options = (isset($autoLoader["options"]) && is_array($autoLoader["options"])) ? $autoLoader["options"] : array();
			} else {
				$this->addExceptionTrace(new InvalidConfigException(APPLICATION_PATH . "config/system/application.config.php", "Invalid value for key 'loaders' in section 'loaderManager'. Please check '$name' configuration it's only accept string or array"), __METHOD__);
				continue;
			}

			try {
				$this->addAutoLoader($name, $class, $source, $options);
			} catch (\Exception $ex) {
				$this->addExceptionTrace($ex, __METHOD__);
			}
		}

		// OK Custom Auto loader completed... Un-register it
		$this->defaultAutoLoader->unRegister();
	}

	/**
	 * Add an auto loader
	 *
	 * Add an auto loader into framework stack. Framework auto loader stack is different from PHP auto loader stack.
	 * This stack used when LoaderManager run in framework managed mode which is default behaviour
	 *
	 * @param string             $name
	 * @param string|IAutoLoader $autoLoader
	 * @param null|string        $source
	 * @param null|array         $options
	 *
	 * @throws \Lh\Collections\KeyExistsException
	 * @throws \Lh\Exceptions\ClassNotFoundException
	 * @throws \Lh\Io\FileNotFoundException
	 * @throws \InvalidArgumentException
	 * @return bool
	 */
	public function addAutoLoader($name, $autoLoader, $source = null, $options = null) {
		$name = trim($name);
		if (empty($name)) {
			throw new \InvalidArgumentException("Auto loader name can't be empty!");
		}
		if ($name == "default") {
			throw new \InvalidArgumentException("Can't use 'default' as auto loader name. This keyword is reserved for system default auto loader.");
		}
		if (empty($autoLoader)) {
			throw new \InvalidArgumentException("Auto loader definition can't be empty!");
		}

		if (array_key_exists($name, $this->autoLoaders)) {
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

		if (is_string($autoLoader)) {
			// Try to instantiated this class name
			if ($source !== null) {
				if (is_file($source)) {
					require_once($source);
				} else {
					throw new FileNotFoundException($source, "Unable to find '$source' in current folder: '" . getcwd() . "'");
				}
			}

			// Final check...
			if (!class_exists($autoLoader, true)) {
				if ($source !== null) {
					throw new ClassNotFoundException($autoLoader, "Unable to create instance of '$autoLoader'. PHP source file doesn't contains appropriate class definition.");
				} else {
					throw new ClassNotFoundException($autoLoader, "Unable to create instance of '$autoLoader'. Default auto loading unable to load the class.");
				}
			}

			$className = $autoLoader;
			$autoLoader = new $className();
			unset($className);
		}

		if (!($autoLoader instanceof IAutoLoader)) {
			throw new \InvalidArgumentException("Can't create instance of IAutoLoader from: " . get_class($autoLoader));
		}

		if ($options !== null) {
			$autoLoader->setOptions($options);
		}

		$this->autoLoaders[$name] = $autoLoader;

		return true;
	}

	/**
	 * Remove an auto loader from collection.
	 *
	 * Removing an auto loader will not unload the loaded class by its loader BUT the autoload function will be removed
	 *
	 * @param string $name
	 *
	 * @throws \RuntimeException
	 * @throws \InvalidArgumentException
	 * @return bool
	 */
	public function removeAutoLoader($name) {
		if (is_numeric($name)) {
			$name = "autoLoader_" . $name;
		}
		$name = trim($name);
		if ($name == "default") {
			throw new \InvalidArgumentException("Can't remove 'default' autoloader. This will break framework functionality.");
		}

		if (!array_key_exists($name, $this->autoLoaders)) {
			return false;
		}

		if (!$this->systemManaged && !$this->autoLoaders[$name]->unRegister()) {
			throw new \RuntimeException("Failed to un-register auto loader '$name'");
		}

		unset($this->autoLoaders[$name]);

		return !array_key_exists($name, $this->autoLoaders);
	}

	/**
	 * Auto loading process
	 *
	 * Responsible loading undefined class. This method will be called automatically by php system.
	 * This method will trying to load a class by calling autoLoad method from each registered auto loader
	 *
	 * @param $className
	 *
	 * @throws \Lh\Exceptions\ClassNotFoundException
	 */
	public function autoLoad($className) {
		foreach ($this->getAutoLoaders() as $autoLoader) {
			try {
				switch ($autoLoader->autoLoad($className)) {
					case IAutoLoader::LOAD_SUCCESS:
					case IAutoLoader::LOAD_ALREADY_LOADED:
						return;
					case IAutoLoader::LOAD_FILE_NOT_FOUND:
					case IAutoLoader::LOAD_CLASS_NOT_EXISTS:
					case IAutoLoader::LOAD_EXCEPTION:
					case IAutoLoader::LOAD_UNKNOWN:
					default:
						break;
				}
			} catch (\Exception $ex) {
				$this->addExceptionTrace($ex, __METHOD__);
			}
		}

		// All auto loader failed to load the class ?
		throw new ClassNotFoundException($className, "All registered auto loaders unable to load '$className'");
	}

	/**
	 * Load additional class / script
	 *
	 * Manually load a script / php file. If the given path is refer to actual file then it will automatically loaded but if it was not, base path is prepend
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function load($path) {
		if (is_file($path)) {
			require_once($path);
			return true;
		}

		foreach ($this->basePaths as $basePath) {
			if (is_file($basePath . $path)) {
				$file = $basePath . $path;
				if (is_file($file) && is_readable($file)) {
					require_once($file);
					return true;
				}
			}
		}

		return false;
	}
}

// End of File: LoaderManager.php 