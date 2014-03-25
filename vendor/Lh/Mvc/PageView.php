<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Mvc;

/**
 * Class PageView
 *
 * Represent base HTML view for your controller. This view will store variable(s) from controller and these variable(s) are local scoped.
 *
 * @package Lh\Mvc
 */
class PageView extends ViewBase {
	/** @var null|MasterView Master template */
	private $masterView = null;

	/**
	 * Associate current PageView with a new MasterView
	 *
	 * Tell current page view to user master page file as base template. If you want to remove master page please give null value as parameter
	 *
	 * @param string|null $path
	 *
	 * @throws \InvalidArgumentException
	 * @return MasterView
	 */
	public function setMasterView($path) {
		if ($path === null) {
			$this->masterView = null;
		} else if (is_string($path)) {
			$masterView = new MasterView($this);
			$masterView->setViewFileName($path);
			$this->masterView = $masterView;
		} else {
			throw new \InvalidArgumentException("PageView::setMasterView() only accept string or null value");
		}

		return $this->masterView;
	}

	/**
	 * Get master template
	 *
	 * @return \Lh\Mvc\MasterView|null
	 */
	public function getMasterView() {
		return $this->masterView;
	}

	/**
	 * PageView will automatically set MasterView view requirement if set.
	 *
	 * @param bool $requireView
	 */
	public function setRequireView($requireView) {
		parent::setRequireView($requireView);
		if (($master = $this->getMasterView()) !== null) {
			$master->setRequireView($requireView);
		}
	}
}

// End of File: PageView.php 