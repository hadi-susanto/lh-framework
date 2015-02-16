<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh;

/**
 * Class ApplicationBase
 * @package Lh
 */
abstract class ApplicationBase {
	/** @var string Application name */
	protected $name;
	/** @var string Application environment state */
	protected $environment;
	/** @var bool Does this application run in debug mode */
	protected $isDebug = false;
	/** @var \Lh\ServiceLocator Providing access to Framework services */
	protected $serviceLocator;
	/**
	 * @var string CRC32 based on application name. Used in session persistence.
	 * @see \Lh\Session\Storage
	 */
	private $crc32;

	/**
	 * Construct application object. Application type determined from derived class
	 *
	 * @param ServiceLocator $serviceLocator
	 * @param array          $options
	 */
	public function __construct(ServiceLocator $serviceLocator, $options) {
		$this->serviceLocator = $serviceLocator;
		$this->setName(isset($options["name"]) ? $options["name"] : "default");
		$this->setEnvironment(isset($options["environment"]) ? $options["environment"] : null);
	}

	/**
	 * Set application differentiable application name
	 *
	 * When you're deploying more than one application in same web server it's recommended to have different name to prevent any un-wanted clash.
	 * Setting application name will automatically calculate its CRC32 based on their name.
	 *
	 * @param string $name
	 */
	protected function setName($name) {
		$this->name = $name;
		$this->crc32 = hash("crc32b", $name);
	}

	/**
	 * Get application name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get calculated CRC32 based on application name
	 *
	 * @return string
	 */
	public function getCrc32() {
		return $this->crc32;
	}

	/**
	 * Retrieve any user configuration. This will return value which passed by $options from start()
	 *
	 * @param string $key
	 *
	 * @see ApplicationBase::start()
	 *
	 * @return string|array|mixed
	 */
	public abstract function getUserOption($key);

	/**
	 * Set application environment status. This will also set isDebug.
	 *
	 * When environment set to debug or development then debug flag will be set. Environment status can be set from
	 * Virtual Directory setting or manually by using application.config.php (stored in config folder)
	 *
	 * @see ApplicationBase::isDebug()
	 *
	 * @param string $environment
	 */
	protected function setEnvironment($environment) {
		if (empty($environment)) {
			$environment = defined("APPLICATION_ENV") ? APPLICATION_ENV : "production";
		} else {
			$environment = strtolower($environment);
		}
		$this->isDebug = ($environment == "debug" || $environment == "development");
		$this->environment = $environment;
	}

	/**
	 * Get application environment value.
	 *
	 * This value automatically retrieved from APPLICATION_ENV.
	 * If APPLICATION_ENV not available then 'production' is assumed
	 *
	 * @return string
	 */
	public function getEnvironment() {
		return $this->environment;
	}

	/**
	 * Return whether debug mode / environment specified from application or not.
	 *
	 * Debug mode itself will not perform additional changing in configuration or application sequence.
	 * Debug mode only tell that any class which aware of it should behave differently.
	 * Currently Dispatcher will suppress any direct output from controller class BUT when debug mode activated
	 * these output will not suppressed.
	 *
	 * @return bool
	 */
	public function isDebug() {
		return $this->isDebug;
	}

	/**
	 * Return same value with APPLICATION_PATH global variable
	 *
	 * @return string
	 */
	public function getApplicationPath() {
		return APPLICATION_PATH;
	}

	/**
	 * Return path to vendor folder
	 *
	 * @return string
	 */
	public function getVendorPath() {
		return VENDOR_PATH;
	}

	/**
	 * Get ServiceLocator object
	 *
	 * @return \Lh\ServiceLocator
	 */
	public function getServiceLocator() {
		return $this->serviceLocator;
	}

	/**
	 * Run our application
	 *
	 * @param array $options
	 *
	 * @see ApplicationBase::getUserOption
	 *
	 * @return void
	 */
	abstract public function start(array $options);
}

// End of File: ApplicationBase.php 