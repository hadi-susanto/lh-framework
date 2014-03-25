<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh;

use Exception;

/**
 * Class ServiceBase
 *
 * Used for enforcing any class that act as Service for Framework or others to be singleton based.
 * This class also providing basic error handling using LazyExceptionTrace. Any class derived from this base SHOULD NOT throw any exception.
 *
 * @see LazyExceptionTrace
 *
 * @package Lh
 */
abstract class ServiceBase implements IService {
	/** @var bool Flag for initialization sequence */
	private $initialized = false;
	/**
	 * Don't use ArrayList for performance reason
	 *
	 * @var LazyExceptionTrace[]
	 * @see ArrayList
	 */
	private $exceptionTraces = array();
	/** @var ServiceLocator Used to retrieve other service */
	protected $serviceLocator;

	/**
	 * Create instance of Service
	 *
	 * @param ServiceLocator $serviceLocator
	 */
	public function __construct(ServiceLocator $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
	}

	/**
	 * Ask whether current service already initialized or not
	 *
	 * @return bool
	 */
	public function isInitialized() {
		return $this->initialized;
	}

	/**
	 * Return trace(s) of error occurred when initializing current service
	 *
	 * @return LazyExceptionTrace[]
	 */
	public function getExceptionTraces() {
		return $this->exceptionTraces;
	}

	/**
	 * Ask whether current service have any trace or not
	 *
	 * @return bool
	 */
	public function hasExceptionTrace() {
		return count($this->exceptionTraces) > 0;
	}

	/**
	 * Clear any logged trace(s)
	 *
	 * @return void
	 */
	public function clearExceptionTraces() {
		$this->exceptionTraces = array();
	}

	/**
	 * Add any exceptions occurred in current manager into pool for further investigation
	 *
	 * @param Exception|LazyExceptionTrace $ex
	 * @param string|null                  $source
	 */
	public function addExceptionTrace($ex, $source = null) {
		if ($ex instanceof Exception) {
			if ($source == null) {
				$source = "Unknown Trace Location";
			}
			$ex = new LazyExceptionTrace($ex, $source, time());
		}

		if ($ex instanceof LazyExceptionTrace) {
			$this->exceptionTraces[] = $ex;
		}
	}

	/**
	 * Initialization code for current service.
	 *
	 * Since any service should not throw any error / exception then we provide this wrapper to suppress any user code which able to throw any exception.
	 * Actual initialization is performed at _init()
	 *
	 * @param array $options
	 */
	public function init(array $options) {
		if ($this->initialized) {
			return;
		}

		try {
			$this->_init($options);
		} catch (Exception $ex) {
			$this->addExceptionTrace($ex, __METHOD__);
		}
		$this->initialized = true;
	}

	/**
	 * Actual initialization code for current manager. ManagerBase::init() act as main entry and catch any error
	 *
	 * @param array $options
	 *
	 * @return void
	 */
	protected abstract function _init(array $options);
}

// End of File: ServiceBase.php