<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh;

/**
 * Interface IService
 *
 * Contract for any class which able to provide services for Framework. Each service class SHOULD NOT throw any error or exception(s).
 * Therefore any exception occurred should be logged by addExceptionTrace() method. Some predefined service class will be registered
 * with ServiceLocator and automatically created.
 *
 * @see ServiceLocator
 *
 * @package Lh
 */
interface IService {
	/**
	 * Initialization code for current service.
	 *
	 * @param array $options
	 *
	 * @return void
	 */
	public function init(array $options);

	/**
	 * Ask whether current service already initialized or not
	 *
	 * @return bool
	 */
	public function isInitialized();

	/**
	 * Return trace(s) of error occurred when initializing current service
	 *
	 * @return LazyExceptionTrace[]
	 */
	public function getExceptionTraces();

	/**
	 * Ask whether current service have any trace or not
	 *
	 * @return bool
	 */
	public function hasExceptionTrace();

	/**
	 * Clear any logged trace(s)
	 *
	 * @return void
	 */
	public function clearExceptionTraces();

	/**
	 * Add any exceptions occurred in current manager into pool for further investigation
	 *
	 * @param \Exception|LazyExceptionTrace $ex
	 * @param string|null                   $source
	 *
	 * @return void
	 */
	public function addExceptionTrace($ex, $source = null);
}

// End of File: IService.php 