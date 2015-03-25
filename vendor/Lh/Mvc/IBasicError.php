<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Mvc;

/**
 * Interface IBasicError
 *
 * Defining errors which can be dispatched by Dispatcher while dispatching any RouteData. This interface is meant to be implemented by controller
 *
 * @see Dispatcher::dispatch
 * @see RouteData
 * @package Lh\Mvc
 */
interface IBasicError {
	/**
	 * This method will be dispatched when Router unable to determine user request or the request is contains invalid segment(s)
	 * NOTE: This method will be directly called by first instance of dispatcher because in no-match error we unable to determine user request
	 *
	 * @see Router::calculateRoute
	 * @return void
	 */
	public function noMatchAction();

	/**
	 * This method will be dispatched if Dispatcher is unable to find appropriate controller file.
	 * NOTE: This method likely called because if the appropriate controller file not found will be handled by @see Router::calculateRoute
	 * 		 If you encountered this kind of error then its error must be called manually by your code
	 *
	 * @return void
	 */
	public function noFileAction();

	/**
	 * This method will be dispatched when Dispatcher is unable create appropriate class based on RouteData. Reason this method to be called:
	 *  1. Your controller class isn't suffixed by 'Controller'
	 *  2. Your controller class isn't derived from @see ControllerBase
	 *
	 * @return void
	 */
	public function noClassAction();

	/**
	 * This method will be dispatched when Dispatcher is unable to find appropriate method in controller class. Reason this method to be called:
	 *  1. Your method name isn't suffixed by 'Action'
	 *  2. Your controller don't have 'xxxAction' method where xxx is method name defined from RouteData
	 *
	 * @return void
	 */
	public function noMethodAction();

	/**
	 * This method will be dispatched when Dispatcher unable to find appropriate view file from @see PageView::viewFileName
	 *
	 * @return void
	 */
	public function noViewAction();

	/**
	 * This method will be dispatched when Dispatcher unable to find Master View of current loaded VIEW.
	 * IMPORTANT: If this method called by Dispatcher::dispatchError() then there is an additional named parameter passed which named 'masterViewPath' which type is string
	 *
	 * @see PageView::setMasterView
	 * @see MasterView
	 *
	 * @return void
	 */
	public function noMasterViewAction();

	/**
	 * This method will be called when user manually called Dispatcher::dispatchError() or your configuration file contains any error.
	 * When configuration file contains error then Web Application will call this method instead of requested one.
	 * IMPORTANT: If this method called by Dispatcher::dispatchError() then there is an additional named parameter passed which named 'errorMessages' which type is string[]
	 *
	 * @see \Lh\Web\Application::start()
	 * @see \Lh\Web\Dispatcher::dispatchError()
	 *
	 * @return void
	 */
	public function genericAction();
}

// End of File: IErrorController.php 