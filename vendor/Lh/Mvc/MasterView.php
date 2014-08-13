<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Mvc;

use Lh\Collections\KeyExistsException;
use Lh\Web\Application;
use Lh\Web\Html\Css;
use Lh\Web\Html\DocType;
use Lh\Web\Html\Meta;
use Lh\Web\Html\Script;

/**
 * Class MasterView
 *
 * MasterView act as base template for your HTML file such as header, menu navigation, and footer. Using template we can avoid unnecessary repeated
 * HTML code. Please note that master view SHOULD render actual content (we refer it as body content) manually by calling renderContent() in .phtml file.
 * You able to give default script(s), style(s) in .phtml file directly, each page view can add additional script / style / etc by calling addXxxx()
 * method provided by master view.
 *
 * Master view should be created from PageView instance although direct instantiation is allowed but it's not recommended. As stated above that master
 * view will have actual content to be rendered BUT this actual content not limited to one instance. Another page view can be added as placeholder.
 * Placeholder can be rendered in template file using $this->getPlaceholder('name')->renderContent()
 *
 * @see PageView
 * @see MasterView::renderBody()
 *
 * @package Lh\Mvc
 */
class MasterView extends ViewBase {
	const PREPEND_HEAD_TITLE = 'PREPEND';
	const APPEND_HEAD_TITLE = 'APPEND';
	const SET_HEAD_TITLE = 'SET';

	/** @var string Doctype for master page */
	private $docType;
	/** @var string Customizable <TITLE> */
	private $headTitle;
	/** @var Meta[] Customizable <META> */
	private $meta = array();
	/** @var Script[] Customizable <SCRIPT> */
	private $scripts = array();
	/** @var Css[] Customizable <STYLE> or <LINK> */
	private $styles = array();
	/** @var PageView Main page view used in renderBody() */
	private $bodyContent;
	/**
	 * Placeholders are collection(s) of PageView with specific name. Placeholder will act similar with renderPartialView() just differ in error handling.
	 * While rendering non-exists placeholder, the renderer will not thrown an exception / error.
	 *
	 * @see MasterView::renderPlaceholder
	 *
	 * @var PageView[]
	 */
	private $placeholders;

	/**
	 * Create new instance of MasterView
	 *
	 * @param PageView $bodyContent
	 */
	public function __construct(PageView $bodyContent) {
		parent::__construct();
		$this->bodyContent = $bodyContent;
		$this->scripts = array();
		$this->styles = array();
		$this->placeholders = array();
	}

	/**
	 * Set DOCTYPE for HMTL file
	 *
	 * @param string $docType
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setDocType($docType) {
		if (is_string($docType)) {
			$this->docType = new DocType($docType);
		} else if ($docType instanceof DocType) {
			$this->docType = $docType;
		} else {
			throw new \InvalidArgumentException("setDocType only accept string or DocType object");
		}
	}

	/**
	 * Get DOCTYPE of current view
	 *
	 * @return string
	 */
	public function getDocType() {
		if ($this->docType == null) {
			$this->docType = new DocType(DocType::HTML5);
		}

		return $this->docType;
	}

	/**
	 * Set string title for current view
	 *
	 * Head title should be retrieved using getHeadTitle method from current class. And master page file should be render this title since LH Framework don't
	 * automagically change HTML title. By default set head title will replace current title
	 *
	 * @param string $headTitle
	 * @param string $mode
	 */
	public function setHeadTitle($headTitle, $mode = MasterView::SET_HEAD_TITLE) {
		switch (strtoupper($mode)) {
			case self::PREPEND_HEAD_TITLE:
				$this->headTitle = $headTitle . $this->headTitle;
				break;
			case self::APPEND_HEAD_TITLE:
				$this->headTitle = $this->headTitle . $headTitle;
				break;
			default:
				$this->headTitle = $headTitle;
				break;
		}
	}

	/**
	 * get string title for current view
	 *
	 * @return string
	 */
	public function getHeadTitle() {
		return $this->headTitle;
	}

	/**
	 * Get all <meta> element for current master page
	 *
	 * @return \Lh\Web\Html\Meta[]
	 */
	public function getMeta() {
		return $this->meta;
	}

	/**
	 * Add meta data for current master page
	 *
	 * @param string|Meta $meta
	 * @param string|null $value Value is REQUIRED when $meta is given as string otherwise it will be ignored.
	 *
	 * @link http://www.w3schools.com/tags/tag_meta.asp for attribute detection
	 *
	 * @throws \InvalidArgumentException
	 */
	public function addMeta($meta, $value = null) {
		if (is_string($meta)) {
			if ($value === null) {
				throw new \InvalidArgumentException("Meta value is required when you specify meta description");
			}
			$attribute = strtolower($meta);

			$meta = new Meta();
			if (in_array($attribute, array("content-type", "default-style", "refresh"))) {
				$meta->addAttribute("http-equiv", $attribute);
				$meta->addAttribute("content", $value);
			} else if (in_array($attribute, array("application-name", "author", "description", "generator", "keywords"))) {
				$meta->addAttribute("name", $attribute);
				$meta->addAttribute("content", $value);
			} else {
				$meta->addAttribute($attribute, $value);
			}

			$this->meta[] = $meta;
		} else if ($meta instanceof Meta){
			$this->meta[] = $meta;
		} else {
			throw new \InvalidArgumentException("addMeta only accept either string or Meta object");
		}
	}

	/**
	 * Get all script for current master page
	 *
	 * @return Script[]
	 */
	public function getScripts() {
		return $this->scripts;
	}

	/**
	 * Add script to current master view
	 *
	 * @param string|Script $script
	 *
	 * @throws \InvalidArgumentException
	 */
	public function addScript($script) {
		if (is_string($script)) {
			$this->scripts[] = new Script($script);
		} else if ($script instanceof Script) {
			$this->scripts[] = $script;
		} else {
			throw new \InvalidArgumentException("addScript only accept either string or Script object");
		}
	}

	/**
	 * Remove script based on index
	 *
	 * @param int $idx
	 */
	public function removeScriptAt($idx) {
		unset($this->scripts[$idx]);
		$this->scripts = array_values($this->scripts);
	}

	/**
	 * Get all style for current master page
	 *
	 * @return Css[]
	 */
	public function getStyles() {
		return $this->styles;
	}

	/**
	 * Add style to current master page
	 *
	 * @param string|Css $style
	 *
	 * @throws \InvalidArgumentException
	 */
	public function addStyle($style) {
		if (is_string($style)) {
			$this->styles[] = new Css($style);
		} else if ($style instanceof Css) {
			$this->styles[] = $style;
		} else {
			throw new \InvalidArgumentException("addStyle only accept either string or Script object");
		}
	}

	/**
	 * Remove style based on index
	 *
	 * @param int $idx
	 */
	public function removeStyleAt($idx) {
		unset($this->styles[$idx]);
		$this->styles = array_values($this->styles);
	}

	/**
	 * Get all placeholder(s) for current master page
	 *
	 * @return \Lh\Mvc\PageView[]
	 */
	public function getPlaceholders() {
		return $this->placeholders;
	}

	/**
	 * Add placeholder to current master page
	 *
	 * Placeholder will act similar to partialRender() function of ViewBase but it will not throw an error if requested placeholder is not found.
	 * NOTE: An error will be thrown if your template file is expecting variable but you don't give one.
	 *
	 * @param string          $name
	 * @param string|PageView $view
	 * @param array           $vars
	 *
	 * @see MasterView::renderPlaceholder()
	 *
	 * @throws \Lh\Collections\KeyExistsException
	 * @throws \InvalidArgumentException
	 */
	public function addPlaceholder($name, $view, array $vars = null) {
		if (array_key_exists($name, $this->placeholders)) {
			throw new KeyExistsException("name", "Name: '$name' already used as placeholder!");
		}

		if (is_string($view)) {
			$path = $view;
			$view = new PageView();
			$view->setViewFileName($path);
			if ($vars !== null) {
				$view->setVars($vars);
			}
		} else if (!($view instanceof PageView)) {
			throw new \InvalidArgumentException('Invalid argument for $view, it\'s only accept either string or PageView object');
		}

		$this->placeholders[$name] = $view;
	}

	/**
	 * Remove placeholder based on name
	 *
	 * @param $name
	 */
	public function removePlaceholder($name) {
		unset($this->placeholders[$name]);
	}

	/**
	 * Get placeholder based on name
	 *
	 * @param string $name
	 *
	 * @return PageView|null
	 */
	public function getPlaceholder($name) {
		return array_key_exists($name, $this->placeholders) ? $this->placeholders[$name] : null;
	}

	/**
	 * Directly return rendered content of a placeholder
	 *
	 * @param string $name
	 *
	 * @return null|string
	 */
	public function renderPlaceholder($name) {
		$placeholder = $this->getPlaceholder($name);

		return ($placeholder !== null) ? $placeholder->renderContent(true) : null;
	}

	/**
	 * Render actual content
	 *
	 * Rendering body content will be use safe render to prevent any unexpected result. This content is your primary page view
	 *
	 * @return null|string
	 */
	public function renderBody() {
		if (!$this->bodyContent->isRendered() && $this->bodyContent->isRequireView()) {
			if (Application::getInstance()->isDebug()) {
				return $this->bodyContent->renderContent();
			} else {
				return $this->bodyContent->renderContent(true);
			}
		}

		// OK cached data can be used
		return $this->bodyContent->getCacheContent();
	}

	/**
	 * Return cache content from rendering process.
	 *
	 * To increase performance rendering process is cached. This method used to retrieve cached content. This method always return cached content
	 * even renderContent() is not called yet. Calling this before rendering will return null
	 *
	 * @return null|string
	 */
	public function getCacheContent() {
		if ($this->isRequireView()) {
			return parent::getCacheContent();
		} else {
			return $this->bodyContent->getCacheContent();
		}
	}
}

// End of File: MasterView.php 