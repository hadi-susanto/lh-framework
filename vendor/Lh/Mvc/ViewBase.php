<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Mvc;

use Lh\Collections\Dictionary;
use Lh\Collections\KeyExistsException;
use Lh\Io\FileNotFoundException;
use Lh\Web\Application;

/**
 * Class ViewBase
 *
 * This class contains basic methods of every kind of view / page.
 * NOTE: Partial view don't inherit variable from their parent to prevent variable name clashing
 *
 * @package Lh\Mvc
 */
abstract class ViewBase {
	/** @var bool Flag template file is required or not */
	private $requireView = true;
	/** @var Dictionary Store any variable for view file */
	protected $vars = null;
	/** @var string View file location */
	protected $viewFileName;
	/** @var string View file extension */
	protected $viewFileExtension = ".phtml";
	/** @var null|string Filename for partial rendering */
	private $cacheFileLocation = null;
	/** @var Dictionary Store cached $vars data before extraction for view file */
	private $cacheVars = null;
	/** @var null|string Store content from rendering process */
	private $cacheContent = null;
	/** @var bool Flag to determine whether rendering already done or not  */
	private $rendered = false;
	/**
	 * @var string Web application base path
	 *             This value taken from Lh\Web\Application::getBasePath()
	 * @see Lh\Web\Application::getBasePath()
	 */
	private $basePath;
	/**
	 * @var bool Should main script appended in URL?
	 *           Main script should be appended if your server don't support URL rewrite. Configurable from system/application.config.php
	 */
	private $appendMainScript = false;
	/**
	 * @var string Web Application main script
	 *             This value taken from Lh\Web\Application::getMainScript()
	 * @see Lh\Web\Application::getMainScript()
	 */
	private $mainScript;

	/**
	 * Default constructor for initialize variable
	 */
	public function __construct() {
		$this->vars = new Dictionary();
		$this->cacheVars = new Dictionary();

		$application = Application::getInstance();
		$this->basePath = $application->getBasePath();
		$this->mainScript = $application->getMainScript();
		$this->appendMainScript = $application->getAppendScript();
	}

	/**
	 * Determine whether view is required or not. This will affect rendered content
	 *
	 * @param boolean $requireView
	 */
	public function setRequireView($requireView) {
		$this->requireView = (bool)$requireView;
	}

	/**
	 * Determine whether current VIEW is require a template file or not.
	 *
	 * If a VIEW don't require a template file then rendering process will be skipped. Even appropriate template file exists but require view flag is disabled
	 * then rendering process still skipped. But if template file don't exists and require view flag is enabled then Dispatcher will dispatching noView action
	 *
	 * @see Dispatcher::dispatchNoView()
	 *
	 * @return boolean
	 */
	public function isRequireView() {
		return $this->requireView;
	}

	/**
	 * Used to changed view file extension. Changing extension will automatically change extension in view file
	 *
	 * @see PageView::getViewFileName()
	 *
	 * @param string $viewFileExtension
	 */
	public function setViewFileExtension($viewFileExtension) {
		if (($pos = strpos($viewFileExtension, ".")) !== 0) {
			$viewFileExtension = '.' . $viewFileExtension;
		}

		if (!empty($this->viewFileName)) {
			$prevExtension = $this->viewFileExtension;
		} else {
			$prevExtension = null;
		}

		$this->viewFileExtension = $viewFileExtension;
		// OK since changing file extension succeed then we will automatically change the view file
		if ($prevExtension != null && ($pos = strrpos($this->viewFileName, $prevExtension)) !== false) {
			// For performance we will use direct method instead of accessor method
			$this->viewFileName = substr($this->viewFileName, 0, $pos) . $viewFileExtension;
		}
	}

	/**
	 * Get view file extension. Its default is .phtml
	 *
	 * @return string
	 */
	public function getViewFileExtension() {
		return $this->viewFileExtension;
	}

	/**
	 * Set which view file will be rendered if different view is must be rendered based on condition.
	 * If view file don't end with view extension then it'll automatically appended
	 *
	 * @see PageView::getViewFileExtension()
	 *
	 * @param string $viewFileName
	 */
	public function setViewFileName($viewFileName) {
		if (strrpos($viewFileName, $this->viewFileExtension) === false) {
			$viewFileName .= $this->viewFileExtension;
		}
		$this->viewFileName = $viewFileName;
	}

	/**
	 * Get view file which will be used for rendering purpose
	 *
	 * @param bool $prePendBaseViewPath
	 *
	 * @return string
	 */
	public function getViewFileName($prePendBaseViewPath = false) {
		return $prePendBaseViewPath ? Application::getInstance()->getViewPath() . $this->viewFileName : $this->viewFileName;
	}

	/**
	 * This method allowed user to directly changed content(s). Usable in post-render event
	 *
	 * @param null|string $cacheContent
	 */
	public function setCacheContent($cacheContent) {
		$this->cacheContent = $cacheContent;
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
		return $this->cacheContent;
	}

	/**
	 * Determine whether rendering process already done or not
	 *
	 * @return bool
	 */
	public function isRendered() {
		return $this->rendered;
	}

	/**
	 * Bulk set variables for view file
	 *
	 * This method will replace any previous variable with the given one. If array given it must key value pair type and the key must be string type.
	 * Example: array("varName" => "varValue", "anotherVar" => new ClassName())
	 *
	 * @param Dictionary|array $vars
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setVars($vars) {
		if ($vars instanceof Dictionary) {
			$this->vars = $vars;
		} else if (is_array($vars)) {
			$this->vars = new Dictionary($vars);
		} else {
			throw new \InvalidArgumentException('Parameter $vars must be an instance of KeyValuePair or an array');
		}
	}

	/**
	 * Get all variable which used for view file
	 *
	 * @return Dictionary
	 */
	public function getVars() {
		return $this->vars;
	}

	/**
	 * Add variable into view file
	 *
	 * @param string $key   variable name
	 * @param mixed  $value variable value
	 *
	 * @return mixed
	 */
	public function addVar($key, $value) {
		$this->vars->set($key, $value);

		return $this->vars->get($key);
	}

	/**
	 * Remove variable from view file
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function removeVar($key) {
		$this->vars->remove($key);

		return !$this->vars->containsKey($key);
	}

	/**
	 * Check whether a variable exists in view file or not
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public function existsVar($key) {
		return $this->vars->containsKey($key);
	}

	/**
	 * Render current view based on view file.
	 *
	 * Safe render option available to perform file availability before including it.
	 *
	 * @param bool $safeRender
	 *
	 * @throws \Exception
	 * @return null|string
	 */
	public function renderContent($safeRender = false) {
		try {
			if ($this->rendered) {
				return $this->getCacheContent();
			}

			$this->rendered = true;
			ob_start();
			extract($this->vars->getArrayCopy());
			if ($safeRender) {
				if (is_file($this->getViewFileName(true))) {
					include($this->getViewFileName(true));
				}
			} else {
				include($this->getViewFileName(true));
			}

			$this->cacheContent = ob_get_clean();
			return $this->cacheContent;
		} catch (\Exception $ex) {
			$this->rendered = false;
			ob_end_clean();
			if (Application::getInstance()->isDebug()) {
				throw $ex;
			}

			return null;
		}
	}

	/**
	 * Perform partial render
	 *
	 * Partial render used to rendering another view file inside a view file. Please note that view variable is not shared between main template and
	 * partial template. If you want to use same variable at partial template then you must pass it again into partial template
	 *
	 * @param string                $partialViewPath
	 * @param null|array|Dictionary $vars
	 * @param bool                  $appendExtension
	 *
	 * @throws \Exception
	 * @return null|string
	 */
	public function partialRender($partialViewPath, $vars = null, $appendExtension = true) {
		try {
			if ($appendExtension && strrpos($partialViewPath, $this->viewFileExtension) === false) {
				$partialViewPath .= $this->viewFileExtension;
			}

			$this->cacheFileLocation = Application::getInstance()->getViewPath() . $partialViewPath;
			if (!is_file($this->cacheFileLocation)) {
				require_once(VENDOR_PATH . "Lh/Io/FileNotFoundException.php");
				throw new FileNotFoundException($this->cacheFileLocation, "Unable to find '$partialViewPath' in View folder.");
			}

			if (is_array($vars)) {
				$this->cacheVars = new Dictionary($vars);
			} else if ($vars instanceof Dictionary) {
				$this->cacheVars = $vars;
			} else {
				$this->cacheVars = null;
			}

			// Removing all local scope variable
			unset($partialViewPath);
			unset($appendExtension);
			unset($vars);
			if ($this->cacheVars !== null) {
				extract($this->cacheVars->getArrayCopy());
			}

			ob_start();
			include($this->cacheFileLocation);

			return ob_get_clean();
		} catch (\Exception $ex) {
			ob_end_clean();
			if (Application::getInstance()->isDebug()) {
				throw $ex;
			}

			return null;
		}
	}

	/**
	 * Enable direct printing of ViewBase object using magic method
	 *
	 * @return null|string
	 */
	public function __toString() {
		return $this->renderContent(true);
	}

	/**
	 * Create URL to our site or another site
	 *
	 * Create proper URL to our site from 'relative' URL. LH Framework is capable to work under site folder and support if your server don't have rewrite module.
	 * This will append appropriate bootstrap file (usually index.php) and application sub-folder from 'relative' URL. Relative URL defined as:
	 *   [namespace/]controller[/method-name][/parameter(s)]
	 *
	 * @param string $siteUrl relative URL
	 *
	 * @return string
	 */
	public function url($siteUrl) {
		if (strpos($siteUrl, "://") !== false) {
			// Probably absolute URL
			return $siteUrl;
		}

		// Remove '/' from beginning of relative URL
		if (strpos($siteUrl, "/") === 0) {
			$siteUrl = substr($siteUrl, 1);
		}
		// Append base path if required
		if ($this->basePath !== null) {
			$siteUrl = $this->basePath . $siteUrl;
		}
		// Append main script if required
		if ($this->appendMainScript) {
			$siteUrl = $this->mainScript . "/" . $siteUrl;
		}

		return $siteUrl;
	}

	/**
	 * Create absolute path to our site resource
	 *
	 * Create proper URL to our site resource such as JavaScript, CSS, images, etc. This will be useful if your server don't support url re-write
	 *
	 * @param string $path relative resource path
	 *
	 * @return string
	 */
	public function path($path) {
		// Remove '/' from beginning of relative URL
		if (strpos($path, "/") === 0) {
			$path = substr($path, 1);
		}
		// Append base path if required
		if ($this->basePath !== null) {
			$path = $this->basePath . $path;
		}

		return $path;
	}
}

// End of File: ViewBase.php 