<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh;

/**
 * Interface IBootstrap
 *
 * Define contract for any application derived class when entering a sequence. These methods should be called by derived class of ApplicationBase.
 * Each application may have different way to implement / injecting Bootstrap, therefore please refer to specific documentation
 *
 * @see ApplicationBase
 * @package Lh
 */
interface IBootstrap {
	/**
	 * Called when application is started for the first time or each request.
	 *
	 * @param ApplicationBase $application
	 * @param ServiceLocator  $serviceLocator
	 *
	 * @return void
	 */
	public function onStart(ApplicationBase $application, ServiceLocator $serviceLocator);

	/**
	 * Called before application ended their cycle
	 *
	 * @param ApplicationBase $application
	 * @param ServiceLocator  $serviceLocator
	 *
	 * @return void
	 */
	public function onEnd(ApplicationBase $application, ServiceLocator $serviceLocator);
}

// End of File: IBootstrap.php