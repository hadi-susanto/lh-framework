<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web;

use Lh\Mvc\ControllerBase;
use Lh\Mvc\PageView;

/**
 * Class DispatchEventArgs
 *
 * @package Lh\Web
 */
class DispatchEventArgs {
	/** @var Dispatcher Current dispatcher which executed */
	private $dispatcher;
	/** @var RouteData Route data which passed into dispatch() */
	private $routeData;
	/** @var ControllerBase User controller */
	private $controller;
	/** @var PageView Associated view file */
	private $pageView;
	/** @var bool Flag for cancelling dispatch sequence */
	private $cancelDispatch = false;

	/**
	 * Create new instance of DispatchEventArgs
	 *
	 * @param Dispatcher     $dispatcher
	 * @param RouteData      $routeData
	 * @param ControllerBase $controller
	 * @param PageView       $pageView
	 */
	public function __construct(Dispatcher $dispatcher, RouteData $routeData, $controller, $pageView) {
		$this->dispatcher = $dispatcher;
		$this->routeData = $routeData;
		$this->controller = $controller;
		$this->pageView = $pageView;
	}

	/**
	 * Get current dispatcher
	 *
	 * @return \Lh\Web\Dispatcher
	 */
	public function getDispatcher() {
		return $this->dispatcher;
	}

	/**
	 * Get current route data
	 *
	 * @return \Lh\Web\RouteData
	 */
	public function getRouteData() {
		return $this->routeData;
	}

	/**
	 * Get loaded controller
	 *
	 * @return \Lh\Mvc\ControllerBase
	 */
	public function getController() {
		return $this->controller;
	}

	/**
	 * Get page view associated with current route data dispatch
	 *
	 * @return \Lh\Mvc\PageView
	 */
	public function getPageView() {
		return $this->pageView;
	}

	/**
	 * Check whether current dispatch should be cancelled or not
	 *
	 * @return bool
	 */
	public function isDispatchCancelled() {
		return $this->cancelDispatch;
	}

	/**
	 * Tell framework to cancel current dispatch request
	 */
	public function cancelCurrentDispatch() {
		$this->cancelDispatch = true;
	}
}

// End of File: DispatchEventArgs.php 