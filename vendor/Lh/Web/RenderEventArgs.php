<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web;

use Lh\Mvc\ControllerBase;
use Lh\Mvc\MasterView;
use Lh\Mvc\PageView;

/**
 * Class RenderEventArgs
 *
 * Event argument while dispatcher begin rendering process. Contains any object related to change rendering process
 *
 * @package Lh\Web
 */
class RenderEventArgs {
	/** @var Dispatcher Current dispatcher which executed */
	private $dispatcher;
	/** @var RouteData Route data which passed into dispatch() */
	private $routeData;
	/** @var ControllerBase User controller */
	private $controller;
	/** @var PageView Associated view file */
	private $pageView;
	/** @var MasterView Associated master view file */
	private $masterView;

	/**
	 * Create new instance of RenderEventArgs
	 *
	 * @param Dispatcher     $dispatcher
	 * @param RouteData      $routeData
	 * @param ControllerBase $controller
	 * @param PageView       $pageView
	 * @param MasterView     $masterView
	 */
	public function __construct($dispatcher, $routeData, $controller, $pageView, $masterView) {
		$this->dispatcher = $dispatcher;
		$this->routeData = $routeData;
		$this->controller = $controller;
		$this->pageView = $pageView;
		$this->masterView = $masterView;
	}

	/**
	 * Get Controller used in execution
	 *
	 * @return \Lh\Mvc\ControllerBase
	 */
	public function getController() {
		return $this->controller;
	}

	/**
	 * Get Dispatcher used in execution
	 *
	 * @return \Lh\Web\Dispatcher
	 */
	public function getDispatcher() {
		return $this->dispatcher;
	}

	/**
	 * Get Route data which passed into dispatcher object
	 *
	 * @return \Lh\Web\RouteData
	 */
	public function getRouteData() {
		return $this->routeData;
	}

	/**
	 * Get view file which used for rendering
	 *
	 * @return \Lh\Mvc\PageView
	 */
	public function getPageView() {
		return $this->pageView;
	}

	/**
	 * Get master page file if current view page have one
	 *
	 * @return \Lh\Mvc\MasterView
	 */
	public function getMasterView() {
		return $this->masterView;
	}
}

// End of File: RenderEventArgs.php 