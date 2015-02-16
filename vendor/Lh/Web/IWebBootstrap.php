<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web;

use Lh\IBootstrap;
use Lh\Web\Http\ResponseEventArgs;

/**
 * Interface WebBootstrap
 *
 * Application and Dispatcher will call each method at specific time / point of execution to enable customized handling.
 * IMPORTANT: Derived class and class name must be specified at system config file (system/application.config.php).
 * This config can have value either class name or array which containing 'class' and 'file' key. Example
 *  1. 'Bootstrap' will assume 'Bootstrap' as class name and a file 'Bootstrap.php' located at source folder
 *  2. array('class' => 'Bootstrap', 'file' => 'Bootstrap.php') will use 'Bootstrap' as class name and a file 'Bootstrap.php' located at source folder
 * NOTE: if you specify absolute path for 'file' key then source folder WILL NOT appended.
 *
 *
 * @package Lh\Web
 */
interface IWebBootstrap extends IBootstrap {
	/**
	 * Executed before Dispatcher::dispatch() called
	 *
	 * This event fired a moment after Dispatcher::dispatch() called. Therefore DispatchEventArgs will contains:
	 *  - Route data which containing user request and their parameter(s)
	 *  - Others components are not initialized yet. Example: controller and page view
	 * IMPORTANT: PRE DISPATCH is refer to dispatch() on ControllerBase::dispatch() instead of Dispatcher::dispatch()
	 *
	 * @param Dispatcher        $dispatcher
	 * @param DispatchEventArgs $e
	 *
	 * @return void
	 */
	public function onPreDispatch(Dispatcher $dispatcher, DispatchEventArgs $e);

	/**
	 * Executed before ControllerBase::dispatch() called
	 *
	 * This event fired a moment before ControllerBase::dispatch() called. Therefore DispatchEventArgs will contains:
	 *  - Route data which containing user request and their parameter(s)
	 *  - Controller class which will be executed
	 *  - Page view for controller
	 * IMPORTANT:
	 *  - This event only fired when a valid controller found and successfully created
	 *  - Will act initialization code for every controller
	 *
	 * @param Dispatcher        $dispatcher
	 * @param DispatchEventArgs $e
	 *
	 * @return void
	 */
	public function onDispatch(Dispatcher $dispatcher, DispatchEventArgs $e);

	/**
	 * Executed after ControllerBase::dispatch() called
	 *
	 * This event fired a moment after ControllerBase::dispatch() called. Therefore DispatchEventArgs will contains:
	 *  - Route data which containing user request and their parameter(s)
	 *  - Controller class which will be executed
	 *  - Page view for controller
	 * IMPORTANT:
	 *  - This event only fired when executed controller not throw an exception
	 *  - Will act as garbage collector for every controller since it's always called after ControllerBase::dispatch()
	 *
	 * @param Dispatcher        $dispatcher
	 * @param DispatchEventArgs $e
	 *
	 * @return void
	 */
	public function onPostDispatch(Dispatcher $dispatcher, DispatchEventArgs $e);

	/**
	 * Executed before rendering view take place
	 *
	 * This event fired before view file existence being checked. Therefore this event is capable to change view file.
	 * IMPORTANT: Rendering process is not called yet and their cache will be null if render() is not called in any previous event
	 *
	 * @param Dispatcher      $dispatcher
	 * @param RenderEventArgs $e
	 *
	 * @return void
	 */
	public function onRender(Dispatcher $dispatcher, RenderEventArgs $e);

	/**
	 * Executed after rendering view take place
	 *
	 * This event fired after page view and their master view (if one) successfully rendered.
	 *
	 * @param Dispatcher      $dispatcher
	 * @param RenderEventArgs $e
	 *
	 * @return void
	 */
	public function onPostRender(Dispatcher $dispatcher, RenderEventArgs $e);

	/**
	 * Executed before HTTP Header and HTTP Cookie being sent
	 *
	 * @param Application       $application
	 * @param ResponseEventArgs $e
	 *
	 * @return void
	 */
	public function onPreResponse(Application $application, ResponseEventArgs $e);

	/**
	 * Executed after response (header, cookie, content) being sent
	 *
	 * @param Application       $application
	 * @param ResponseEventArgs $e
	 *
	 * @return void
	 */
	public function onPostResponse(Application $application, ResponseEventArgs $e);
}

// End of File: IWebBootstrap.php