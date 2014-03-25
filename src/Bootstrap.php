<?php
use Lh\Web\IWebBootstrap;

class Bootstrap implements IWebBootstrap {
	/**
	 * Called when application is started for the first time or each request.
	 *
	 * @param \Lh\ApplicationBase $application
	 * @param \Lh\ServiceLocator  $serviceLocator
	 *
	 * @return void
	 */
	public function onStart(\Lh\ApplicationBase $application, \Lh\ServiceLocator $serviceLocator) {

	}

	/**
	 * Called before application ended their cycle
	 *
	 * @param \Lh\ApplicationBase $application
	 * @param \Lh\ServiceLocator  $serviceLocator
	 *
	 * @return void
	 */
	public function onEnd(\Lh\ApplicationBase $application, \Lh\ServiceLocator $serviceLocator) {

	}

	/**
	 * Executed before Dispatcher::dispatch() called
	 *
	 * This event fired a moment after Dispatcher::dispatch() called. Therefore DispatchEventArgs will contains:
	 *  - Route data which containing user request and their parameter(s)
	 *  - Others components are not initialized yet. Example: controller and page view
	 * IMPORTANT: PRE DISPATCH is refer to dispatch() on ControllerBase::dispatch() instead of Dispatcher::dispatch()
	 *
	 * @param \Lh\Web\Dispatcher        $dispatcher
	 * @param \Lh\Web\DispatchEventArgs $e
	 *
	 * @return void
	 */
	public function onPreDispatch(\Lh\Web\Dispatcher $dispatcher, \Lh\Web\DispatchEventArgs $e) {

	}

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
	 * @param \Lh\Web\Dispatcher        $dispatcher
	 * @param \Lh\Web\DispatchEventArgs $e
	 *
	 * @return void
	 */
	public function onDispatch(\Lh\Web\Dispatcher $dispatcher, \Lh\Web\DispatchEventArgs $e) {

	}

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
	 * @param \Lh\Web\Dispatcher        $dispatcher
	 * @param \Lh\Web\DispatchEventArgs $e
	 *
	 * @return void
	 */
	public function onPostDispatch(\Lh\Web\Dispatcher $dispatcher, \Lh\Web\DispatchEventArgs $e) {

	}

	/**
	 * Executed before rendering view take place
	 *
	 * This event fired before view file existence being checked. Therefore this event is capable to change view file.
	 * IMPORTANT: Rendering process is not called yet and their cache will be null if render() is not called in any previous event
	 *
	 * @param \Lh\Web\Dispatcher      $dispatcher
	 * @param \Lh\Web\RenderEventArgs $e
	 *
	 * @return void
	 */
	public function onRender(\Lh\Web\Dispatcher $dispatcher, \Lh\Web\RenderEventArgs $e) {

	}

	/**
	 * Executed after rendering view take place
	 *
	 * This event fired after page view and their master view (if one) successfully rendered.
	 *
	 * @param \Lh\Web\Dispatcher      $dispatcher
	 * @param \Lh\Web\RenderEventArgs $e
	 *
	 * @return void
	 */
	public function onPostRender(\Lh\Web\Dispatcher $dispatcher, \Lh\Web\RenderEventArgs $e) {

	}

	/**
	 * Executed before HTTP Header and HTTP Cookie being sent
	 *
	 * @param \Lh\Web\Application            $application
	 * @param \Lh\Web\Http\ResponseEventArgs $e
	 *
	 * @return void
	 */
	public function onPreResponse(\Lh\Web\Application $application, \Lh\Web\Http\ResponseEventArgs $e) {

	}

	/**
	 * Executed after response (header, cookie, content) being sent
	 *
	 * @param \Lh\Web\Application            $application
	 * @param \Lh\Web\Http\ResponseEventArgs $e
	 *
	 * @return void
	 */
	public function onPostResponse(\Lh\Web\Application $application, \Lh\Web\Http\ResponseEventArgs $e) {

	}
}

// End of File: Bootstrap.php
 