<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Mvc;

use Lh\ServiceLocator;
use Lh\Web\Dispatcher;
use Lh\Web\Http\HttpRequest;
use Lh\Web\Http\HttpResponse;

/**
 * Class ControllerBase
 *
 * Base class for every controller in LH Framework. This will be provide basic functionality for your actual coding.
 * Every method that accessible by MVC pattern must be suffixed by 'Action' therefore index will call indexAction().
 * Any other method still callable by PHP as long you call it manually.
 *
 * NOTE:
 *  1. Please override __call in derived controller if you want to handle un-existed method from the controller instead of by framework
 *  2. Any output / direct printing from controller will be cached and only printed in DEBUG MODE
 *
 * @see ControllerBase::getCacheContent()
 *
 * @package Lh\Mvc
 */
abstract class ControllerBase {
	/** @var ServiceLocator Used to get any service offere by framework */
	protected $serviceLocator;
	/** @var Dispatcher Current dispatcher which dispatching current controller */
	protected $dispatcher;
	/** @var PageView Page view for current controller */
	protected $pageView;
	/** @var string Cached data from controller method. Only flushed in debug environment */
	protected $cacheContent;

	/**
	 * ControllerBase constructor marked as final, therefore object instantiation is guaranteed to be successful
	 *
	 * @param ServiceLocator $serviceLocator
	 * @param Dispatcher     $dispatcher
	 */
	public final function __construct(ServiceLocator $serviceLocator, Dispatcher $dispatcher) {
		$this->serviceLocator = $serviceLocator;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Get service locator
	 *
	 * @return \Lh\ServiceLocator
	 */
	public function getServiceLocator() {
		return $this->serviceLocator;
	}

	/**
	 * Get current dispatcher
	 *
	 * @return Dispatcher
	 */
	public function getDispatcher() {
		return $this->dispatcher;
	}

	/**
	 * Shortcut method to retrieve HttpRequest from Dispatcher
	 *
	 * @return HttpRequest
	 */
	public function getRequest() {
		return $this->dispatcher->getRequest();
	}

	/**
	 * Shortcut method to retrieve HttpResponse from Dispatcher
	 *
	 * @return HttpResponse
	 */
	public function getResponse() {
		return $this->dispatcher->getResponse();
	}

	/**
	 * Set page view
	 *
	 * This method should only called by framework. No user code should called this one since there is no use to use another instance of page view.
	 * To change view file please use setViewFile from its object.
	 *
	 * @param PageView $pageView
	 *
	 * @see PageView::setViewFileName()
	 */
	public function setPageView(PageView $pageView) {
		$this->pageView = $pageView;
	}

	/**
	 * Get current page view
	 *
	 * @return PageView
	 */
	public function getPageView() {
		return $this->pageView;
	}

	/**
	 * Retrieve content from controller.
	 *
	 * This content produced by direct call of print or similar function in controller main body
	 *
	 * @return string
	 */
	public function getCacheContent() {
		return $this->cacheContent;
	}

	/**
	 * Shorthand for $this->pageView->url()
	 *
	 * Using PageView::url() to formatting given url to match server configuration. This is useful if you're unable to use virtual host or your application is in a sub-folder.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function url($url) {
		return $this->pageView->url($url);
	}

	/**
	 * Shorthand for $this->pageView->path()
	 *
	 * Using PageView::path() to formatting given path into absolute path. This is useful if you're unable to use virtual host or your application is in a sub-folder.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function path($path) {
		return $this->pageView->path($path);
	}

	/**
	 * Dispatch method from current controller
	 *
	 * Dispatching method for current controller based on user request.
	 * WARNING: non-existed method is not checked and can cause HAVOC. Please override __call in your controller class to handling it
	 *
	 * @param string $methodName
	 * @param array  $params
	 *
	 * @return mixed
	 */
	public function dispatch($methodName, $params) {
		// Sometimes user tends to print something in controller method...
		// This is a good thing while debugging BUT NOT for production

		ob_start();
		switch (count($params)) {
			case 0:
				$result = $this->{$methodName}();
				break;
			case 1:
				$result = $this->{$methodName}($params[0]);
				break;
			case 2:
				$result = $this->{$methodName}($params[0], $params[1]);
				break;
			case 3:
				$result = $this->{$methodName}($params[0], $params[1], $params[2]);
				break;
			case 4:
				$result = $this->{$methodName}($params[0], $params[1], $params[2], $params[3]);
				break;
			case 5:
				$result = $this->{$methodName}($params[0], $params[1], $params[2], $params[3], $params[4]);
				break;
			default:
				$result = call_user_func_array(array(&$this, $methodName), $params);
				break;
		}

		$this->cacheContent = ob_get_clean();
		return $result;
	}

	/**
	 * Controller initialize sequence
	 *
	 * This function will be called before framework dispatching user request. Typically this is act as Controller constructor but the execution is deferred.
	 * Using init method will ensure all basic controller variable such as HttpRequest, HttpResponse, PageView are initialized.
	 *
	 * @see ControllerBase::$request
	 * @see ControllerBase::$response
	 * @see ControllerBase::$pageView
	 * @see \Lh\Web\Http\HttpRequest
	 * @see \Lh\Web\Http\HttpResponse
	 * @see PageView
	 *
	 * @return void
	 */
	public function initialize() { }

	/**
	 * Controller finalize sequence
	 *
	 * This function will be called after framework complete the dispatch request. This method can be used to clean-up any used resources.
	 * NOTE: This method will be called before post-dispatch event.
	 */
	public function finalize() { }
}

// End of File: ControllerBase.php 