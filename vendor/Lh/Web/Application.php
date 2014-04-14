<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web;

/**
 * Since auto loader not loaded yet then manual loading is required
 */
require_once(VENDOR_PATH . "Lh/ApplicationBase.php");

use Lh\ApplicationBase;
use Lh\Auth\AuthenticationManager;
use Lh\Db\DbManager;
use Lh\Exceptions\ErrorManager;
use Lh\Loader\LoaderManager;
use Lh\ServiceLocator;
use Lh\Session\SessionManager;
use Lh\Web\Http\ResponseEventArgs;

/**
 * Class Application
 *
 * The main class which boot web application. This will initialize LH Framework and calling your code. Phase in this application:
 *  - Loading basic dependencies
 *  - Registering Auto Loader
 *  - Init Router class
 *  - Calculate routing based user URI
 *  - Dispatch / calling your code
 *
 * @package Lh\Web
 * @singleton
 */
class Application extends ApplicationBase {
	/** @var Application Application singleton instance */
	private static $instance;
	/** @var bool Application initialization flag */
	private static $isInitialized = false;
	/** @var array Application user options. These values read from config/user/application.config.php */
	private $userOptions;
	/** @var string Your main script file which usually index.php */
	private $mainScript;
	/** @var bool Should main script appended in URL? */
	private $appendScript = false;
	/** @var string Relative folder from your application into web server root folder */
	private $basePath = null;
	/** @var string Location of your application source folder */
	private $sourcePath = null;
	/** @var null|IWebBootstrap Providing bootstrap class for handling dispatcher sequence */
	private $bootstrapClass = null;
	/** @var string[] Error message(s) while initializing Web Application */
	private $configErrors = array();

	/**
	 * Create new instance of Web Application
	 *
	 * Array key options:
	 *  - 'name'			=> Application Name
	 *  - 'mainScript'		=> Boot file for web application. Give 'auto' for auto detection.
	 *  - 'appendScript'	=> Should any URL creation append main script? This should be used if your server don't support URL rewrite
	 *  - 'environment'		=> Determine application environment. Defaulting to APPLICATION_ENV
	 *  - 'sourcePath'		=> Determine user code location
	 *
	 * @param \Lh\ServiceLocator $serviceLocator
	 * @param array              $options        This options obtained from config/system/application.config.php
	 */
	public function __construct(ServiceLocator $serviceLocator, $options) {
		parent::__construct($serviceLocator, $options);
		$this->setMainScript(isset($options["mainScript"]) ? $options["mainScript"] : null);
		if (($pos = strrpos($_SERVER["SCRIPT_NAME"], $this->getMainScript())) !== 1) {
			$this->setBasePath(substr($_SERVER["SCRIPT_NAME"], 0, $pos));
			unset($pos);
		} else {
			$this->setBasePath("/");
		}
		$this->setAppendScript(isset($options["appendScript"]) ? $options["appendScript"] : false);
		$this->setSourcePath(isset($options["sourcePath"]) ? $options["sourcePath"] : null);
		$this->setBootstrapClass(isset($options["bootstrap"]) ? $options["bootstrap"] : null);
	}

	/**
	 * Get web application singleton instance
	 *
	 * @return \Lh\Web\Application
	 */
	public static function getInstance() {
		return self::$instance;
	}

	/**
	 * Check whether current web application already initialized or not
	 *
	 * @return bool
	 */
	public static function isInitialized() {
		return self::$isInitialized;
	}

	/**
	 * Set our main script file.
	 *
	 * Give 'auto' for auto detection of script file. Main script file usually is your index.php file. Can be set by 'mainScript' option key from application.config.php
	 * IMPORTANT: Main script file is differ from Bootstrap class. Main script used as main entry of an application (which called when an application started).
	 *
	 * @param string $file
	 *
	 * @see WebBootstrap
	 */
	private function setMainScript($file) {
		if (empty($file)) {
			$file = "auto";
		}
		if (strtolower(trim($file)) !== "auto") {
			$this->mainScript = $file;
		} else {
			$tokens = explode("/", $_SERVER["SCRIPT_NAME"]);
			$this->mainScript = end($tokens);
			unset($tokens);
		}
	}

	/**
	 * Get main script file
	 *
	 * Main script is a script which called by web server. It's usually your index.php file
	 *
	 * @return string
	 */
	public function getMainScript() {
		return $this->mainScript;
	}

	/**
	 * Tell Framework to append main script in each URL generation
	 *
	 * Append script only affected PageView::url() and ControllerBase::url(). Append script should be activated if your web server don't support mod_rewrite
	 *
	 * @param bool $value
	 *
	 * @see Lh\Mvc\PageView::url()
	 * @see Lh\Mvc\ControllerBase::url()
	 */
	private function setAppendScript($value) {
		$this->appendScript = (bool)$value;
	}

	/**
	 * Get append script flag
	 *
	 * This flag tell whether main script should be appended in URL or not. This flag only configurable from config file.
	 * You must use set this flag to true when your server don't support url rewrite
	 *
	 * @return boolean
	 */
	public function getAppendScript() {
		return $this->appendScript;
	}

	/**
	 * Set relative folder of our application from web server root folder
	 *
	 * @param string $path
	 */
	private function setBasePath($path) {
		$this->basePath = $path;
	}

	/**
	 * Get base path of your application
	 *
	 * LH Framework designed to work either using virtual host or without virtual host. When you application stored under a sub-directory
	 * then base path will contains the sub-directory. Base path will automatically detected. Example (assuming accessing HomeController):
	 *  - http://www.example.com/my-app/home/welcome: then base path is '/my-app/'
	 *  - http://my-app.example.com/home/welcome: then base path is '/'
	 *
	 *
	 * @return string
	 */
	public function getBasePath() {
		return $this->basePath;
	}

	/**
	 * Set your application source folder
	 *
	 * Application source folder are specialized folder containing your Controller, Model and View codes (we refer it as user code). We give
	 * user ability to move this special folder to another location by specifying 'sourcePath' key in application config file
	 *
	 * @param string $path
	 */
	private function setSourcePath($path) {
		if (empty($path) || !is_dir($path)) {
			$this->sourcePath = $this->getApplicationPath() . "src/";
		} else {
			$this->sourcePath = realpath($path) . DIRECTORY_SEPARATOR;
		}
	}

	/**
	 * Prepare a bootstrap class based on config.
	 *
	 * Bootstrap class will provide user code to customize the way LH Framework executed. It can terminate it, changing user request, etc
	 * Bootstrap will be hook into Application and Dispatcher sequence
	 *
	 * @param array $config
	 */
	private function setBootstrapClass($config) {
		if ($config == null) {
			return;
		}

		if (is_string($config)) {
			$className = $config;
			$fileLocation = $this->getSourcePath() . $config . ".php";
		} else {
			$className = isset($config["class"]) ? $config["class"] : null;
			$fileLocation = isset($config["file"]) ? $config["file"] : null;
			if (!is_file($fileLocation)) {
				// Probably only filename
				$fileLocation = $this->getSourcePath() . $fileLocation;
			}
		}

		if ($className == null || $fileLocation == null) {
			// Invalid config
			$this->configErrors[] = "[Application] Invalid config for 'bootstrap' key. This key only accept string or array containing 'class' and 'file' key. Location: system/application.config.php";
			return;
		}

		if (!is_file($fileLocation) || !is_readable($fileLocation)) {
			$this->configErrors[] = "[Application] Unable to read bootstrap class file";
			return;
		}

		// Please note that Auto loader is not ready yet (manual loading is required)
		require_once(VENDOR_PATH . "Lh/IBootstrap.php");
		require_once(VENDOR_PATH . "Lh/Web/IWebBootstrap.php");
		require_once($fileLocation);

		$this->bootstrapClass = new $className();
		if (!($this->bootstrapClass instanceof IWebBootstrap)) {
			$this->configErrors[] = "[Application] The given bootstrap class ('$className') don't implements IWebBootstrap";
			$this->bootstrapClass = null;
		}
	}

	/**
	 * Return where your application codes resides. Usually this is your src folder
	 *
	 * @return string
	 */
	public function getSourcePath() {
		return $this->sourcePath;
	}

	/**
	 * Get Model folder location
	 *
	 * @return string
	 */
	public function getModelPath() {
		return $this->sourcePath . "Model" . DIRECTORY_SEPARATOR;
	}

	/**
	 * Get View folder location
	 *
	 * @return string
	 */
	public function getViewPath() {
		return $this->sourcePath . "View" . DIRECTORY_SEPARATOR;
	}

	/**
	 * Get Controller folder location
	 *
	 * @return string
	 */
	public function getControllerPath() {
		return $this->sourcePath . "Controller" . DIRECTORY_SEPARATOR;
	}

	/**
	 * Get bootstrap class
	 *
	 * @see WebBootstrap
	 *
	 * @return IWebBootstrap|null
	 */
	public function getBootstrapClass() {
		return $this->bootstrapClass;
	}

	/**
	 * Retrieve any user configuration. This will return value which passed by $options from start()
	 *
	 * @param string $key
	 *
	 * @see ApplicationBase::start()
	 *
	 * @return string|array|mixed
	 */
	public function getUserOption($key) {
		return isset($this->userOptions[$key]) ? $this->userOptions[$key] : null;
	}

	/**
	 * Preparing our web application based on config file and the given options.
	 * This function will load a minimum set of classes required to running our application by default.
	 * Other dependency will be loaded dynamically by AutoLoader. Initialize sequence:
	 *  1. Custom PHP Settings from 'phpSettings' key
	 *  2. Internal application variable
	 *  3. Loading basic dependencies
	 *  4. AutoLoader		: Providing basic auto loading class)
	 *  5. DatabaseManager	: Providing database connection for others module or user code)
	 *  6. ErrorManager		: Provide error handling while user code initialization)
	 *  7. SessionManager	: Providing session management)
	 *  8. Router			: Mapping user request into appropriate controller
	 *
	 * @param array $options
	 *
	 * @throws \RuntimeException
	 * @return Application
	 */
	public static function init(array $options) {
		if (self::$isInitialized) {
			throw new \RuntimeException(__CLASS__ . " already initialized. Application can only initialized once!");
		}

		// Change any PHP ini settings before any script execution. This is the earliest script execution from bootstrap file.
		if (isset($options["phpSettings"]) && is_array($options["phpSettings"])) {
			foreach ($options["phpSettings"] as $key => $value) {
				ini_set($key, $value);
			}
			unset($key, $value);
		}

		// Load basic requirement to run our web application. Scripts above do not need any dependencies
		self::loadBasicDependencies();

		// Create application singleton instance and their dependencies
		$serviceLocator = new ServiceLocator();
		self::$instance = new Application($serviceLocator, isset($options["application"]) ? $options["application"] : null);

		if (self::$instance->bootstrapClass !== null) {
			self::$instance->bootstrapClass->onStart(self::$instance, $serviceLocator);
		}

		// AutoLoader initialization and registration
		$loaderManager = new LoaderManager($serviceLocator);
		$loaderManager->init((isset($options["loaderManager"]) && is_array($options["loaderManager"])) ? $options["loaderManager"] : array());
		if ($loaderManager->hasExceptionTrace()) {
			foreach ($loaderManager->getExceptionTraces() as $trace) {
				$ex = $trace->getException();
				self::$instance->configErrors[] = sprintf("[%s] %s. Location: %s (Line: %s)", $trace->getSource(), $ex->getMessage(), $ex->getFile(), $ex->getLine());
			}
		}

		// DatabaseManager / Adapters initialization and registration
		$dbManager = new DbManager($serviceLocator);
		$dbManager->init((isset($options["dbManager"]) && is_array($options["dbManager"])) ? $options["dbManager"] : array());
		if ($dbManager->hasExceptionTrace()) {
			foreach ($dbManager->getExceptionTraces() as $trace) {
				$ex = $trace->getException();
				self::$instance->configErrors[] = sprintf("[%s] %s. Location: %s (Line: %s)", $trace->getSource(), $ex->getMessage(), $ex->getFile(), $ex->getLine());
			}
		}

		// ErrorHandler initialization and registration
		$errorManager = new ErrorManager($serviceLocator);
		$errorManager->init((isset($options["errorManager"]) && is_array($options["errorManager"])) ? $options["errorManager"] : array());
		if ($errorManager->hasExceptionTrace()) {
			foreach ($errorManager->getExceptionTraces() as $trace) {
				$ex = $trace->getException();
				self::$instance->configErrors[] = sprintf("[%s] %s. Location: %s (Line: %s)", $trace->getSource(), $ex->getMessage(), $ex->getFile(), $ex->getLine());
			}
		}

		// SessionManager initialization and registration
		$sessionManager = new SessionManager($serviceLocator);
		$sessionManager->init((isset($options["sessionManager"]) && is_array($options["sessionManager"])) ? $options["sessionManager"] : array());
		if ($sessionManager->hasExceptionTrace()) {
			foreach ($sessionManager->getExceptionTraces() as $trace) {
				$ex = $trace->getException();
				self::$instance->configErrors[] = sprintf("[%s] %s. Location: %s (Line: %s)", $trace->getSource(), $ex->getMessage(), $ex->getFile(), $ex->getLine());
			}
		}

		// AuthManager initialization and registration. Authentication checking performed at start()
		$authManager = new AuthenticationManager($serviceLocator);
		$authManager->init((isset($options["authenticationManager"]) && is_array($options["authenticationManager"])) ? $options["authenticationManager"] : array());
		if ($authManager->hasExceptionTrace()) {
			foreach ($authManager->getExceptionTraces() as $trace) {
				$ex = $trace->getException();
				self::$instance->configErrors[] = sprintf("[%s] %s. Location: %s (Line: %s)", $trace->getSource(), $ex->getMessage(), $ex->getFile(), $ex->getLine());
			}
		}

		// Preparing Router
		$router = new Router($serviceLocator);
		$router->init((isset($options["router"]) && is_array($options["router"])) ? $options["router"] : array());
		if ($router->hasExceptionTrace()) {
			foreach ($router->getExceptionTraces() as $trace) {
				$ex = $trace->getException();
				self::$instance->configErrors[] = sprintf("[%s] %s. Location: %s (Line: %s)", $trace->getSource(), $ex->getMessage(), $ex->getFile(), $ex->getLine());
			}
		}

		self::$isInitialized = true;
		return self::$instance;
	}

	/**
	 * Run application
	 *
	 * Run your application after all initialization completed. Running sequence:
	 *  1. Creation of HttpRequest and Uri (retrieved from user request)
	 *  2. Calculating RouteData (mapping to controller from user request)
	 *  3. Checking Access Control List if activated
	 *  4. Dispatching controller
	 *  5. Rendering VIEW
	 *  6. Send HttpResponse
	 *
	 * @param array $options
	 */
	public function start(array $options) {
		$this->userOptions = $options;

		$request = new Http\HttpRequest();
		$dispatcher = new Dispatcher($this->serviceLocator, $request, new Http\HttpResponse());
		if (count($this->configErrors) > 0) {
			$message = "Please fix following error(s) first:";
			foreach ($this->configErrors as $error) {
				$message .= "\n - " . $error;
			}
			$message .= "\n\nAbove error usually come from invalid configuration file. Please re-check your config file.";

			$dispatcher->dispatchError($message);
		} else {
			try {
				$routeData = $this->serviceLocator->getRouter()->calculateRoute($request->getUri());
				$dispatcher->dispatch($routeData);
			} catch (\Exception $ex) {
				$dispatcher->dispatchException($ex, "\\Lh\\Web\\Application::start()");
			}
		}

		foreach (Dispatcher::getInstances() as $dispatcher) {
			if ($dispatcher->isCompleted() || !$dispatcher->isDispatched()) {
				continue;
			}

			$controller = $dispatcher->getController();
			$response = $dispatcher->getResponse();
			$view = $controller->getPageView();
			$masterView = $view->getMasterView();
			if ($this->bootstrapClass !== null) {
				$this->bootstrapClass->onPreResponse($this, new ResponseEventArgs($controller, $response, $view, $masterView));
			}

			if ($response->isRedirect()) {
				break;
			}
			$response->sendHeaders();
			$response->sendCookies();

			// ToDo: event response (considered for removal since there is no use)
			if ($masterView != null) {
				print($masterView->getCacheContent());
			} else {
				print($view->getCacheContent());
			}

			if ($this->isDebug() && ($temp = trim($controller->getCacheContent())) != "") {
				print($temp);
				unset($temp);
			}

			// ToDo: event post-response (considered for removal since there is no use)
			if ($this->bootstrapClass !== null) {
				$this->bootstrapClass->onPostResponse($this, new ResponseEventArgs($controller, $response, $view, $masterView));
			}
		}

		if ($this->bootstrapClass !== null) {
			$this->bootstrapClass->onEnd($this, $this->serviceLocator);
		}

		// Cleaning up after everything completed...
		$this->serviceLocator->getSessionManager()->gc();
	}

	/**
	 * Load minimal set of dependencies
	 *
	 * Load a set of classes which used for framework to work properly. Before the AutoLoader kick-in we must load every dependencies here
	 * Basic classes such as:
	 *  - ApplicationException
	 * 	- AutoLoader
	 *  - Router
	 *  - etc
	 *
	 * Actually all classes below AutoLoader are optional. Since AutoLoader already kicked in then it can be auto loaded. But since it'll must be loaded
	 * then load the class here as basic dependencies are faster than using AutoLoader
	 */
	private static function loadBasicDependencies() {
		if (Application::$isInitialized) {
			return;
		}
		// Basic class
		require_once(VENDOR_PATH . "Lh/ApplicationException.php");
		require_once(VENDOR_PATH . "Lh/IBootstrap.php");
		require_once(VENDOR_PATH . "Lh/IService.php");
		require_once(VENDOR_PATH . "Lh/ServiceBase.php");
		require_once(VENDOR_PATH . "Lh/ServiceLocator.php");
		require_once(VENDOR_PATH . "Lh/Collections/Dictionary.php");
		require_once(VENDOR_PATH . "Lh/Collections/DictionaryIterator.php");
		require_once(VENDOR_PATH . "Lh/Web/IWebBootstrap.php");
		// AutoLoader
		require_once(VENDOR_PATH . "Lh/Loader/IAutoLoader.php");
		require_once(VENDOR_PATH . "Lh/Loader/LoaderBase.php");
		require_once(VENDOR_PATH . "Lh/Loader/DefaultLoader.php");
		require_once(VENDOR_PATH . "Lh/Loader/LoaderManager.php");
		// Database
		require_once(VENDOR_PATH . "Lh/Db/DbManager.php");
		// Error Handler
		require_once(VENDOR_PATH . "Lh/Exceptions/IErrorHandler.php");
		require_once(VENDOR_PATH . "Lh/Exceptions/DefaultHandler.php");
		require_once(VENDOR_PATH . "Lh/Exceptions/ErrorManager.php");
		// Session
		require_once(VENDOR_PATH . "Lh/Session/SessionManager.php");
		require_once(VENDOR_PATH . "Lh/Session/Storage.php");
		// Authentication
		require_once(VENDOR_PATH . "Lh/Auth/AuthenticationManager.php");
		// Router
		require_once(VENDOR_PATH . "Lh/Web/Router.php");
		require_once(VENDOR_PATH . "Lh/Web/Uri.php");
		require_once(VENDOR_PATH . "Lh/Web/RouteData.php");
		// Http
		require_once(VENDOR_PATH . "Lh/Web/Http/HttpRequest.php");
		require_once(VENDOR_PATH . "Lh/Web/Http/HttpResponse.php");
		// MVC
		require_once(VENDOR_PATH . "Lh/Web/Dispatcher.php");
		require_once(VENDOR_PATH . "Lh/Mvc/ControllerBase.php");
		require_once(VENDOR_PATH . "Lh/Mvc/ViewBase.php");
		require_once(VENDOR_PATH . "Lh/Mvc/PageView.php");
	}
}

// End of File: Application.php 