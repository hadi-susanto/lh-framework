<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web\Http;

use Lh\Mvc\ControllerBase;
use Lh\Mvc\MasterView;
use Lh\Mvc\PageView;

/**
 * Class ResponseEventArgs
 *
 * @package Lh\Web\Http
 */
class ResponseEventArgs {
	/** @var ControllerBase Associated controller which code executed */
	private $controller;
	/** @var HttpResponse Current response object */
	private $response;
	/** @var PageView Current page view object */
	private $pageView;
	/** @var MasterView Current master view object if page view use one */
	private $masterView;

	/**
	 * Create new instance of ResponseEventArgs
	 *
	 * @param ControllerBase $controller
	 * @param HttpResponse   $response
	 * @param PageView       $pageView
	 * @param MasterView     $masterView
	 */
	public function __construct($controller, $response, $pageView, $masterView) {
		$this->controller = $controller;
		$this->response = $response;
		$this->pageView = $pageView;
		$this->masterView = $masterView;
	}

	/**
	 * Get current controller
	 *
	 * @return \Lh\Mvc\ControllerBase
	 */
	public function getController() {
		return $this->controller;
	}

	/**
	 * Get current response
	 *
	 * @return \Lh\Web\Http\HttpResponse
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * Get current page view
	 *
	 * @return \Lh\Mvc\PageView
	 */
	public function getPageView() {
		return $this->pageView;
	}

	/**
	 * Get current master view if available
	 *
	 * @return \Lh\Mvc\MasterView
	 */
	public function getMasterView() {
		return $this->masterView;
	}
}

// End of File: ResponseEventArgs.php 