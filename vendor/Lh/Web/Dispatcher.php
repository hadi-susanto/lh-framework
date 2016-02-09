<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web;

use Exception;
use Lh\Mvc\ControllerBase;
use Lh\Mvc\PageView;
use Lh\ServiceLocator;

/**
 * Class Dispatcher
 *
 * Each dispatcher instance will be dispatching controller based on RouteData. Every dispatcher will be associated with request and response object.
 * We can track dispatch request by their index. First dispatch request will be done automatically by framework and will always have index equal to zero.
 * Any index greater than zero means another dispatch request by user code.
 *
 * @see Application::start()
 * @package Lh\Web
 */
class Dispatcher {
	const CONTROLLER_LOADED = "LOADED";
	const CONTROLLER_NO_FILE = "NO_FILE";
	const CONTROLLER_NO_CLASS = "NO_CLASS";
	const CONTROLLER_INVALID_BASE_CLASS = "INVALID_BASE_CLASS";
	const CONTROLLER_NO_METHOD = "NO_METHOD";

	/** @var Dispatcher[] Store active / dispatched instance(s) of Dispatcher */
	private static $instances = array();
	/** @var Dispatcher Store currently executing dispatcher */
	private static $executingDispatcher = null;
	/** @var int Index of current dispatcher. Calculated form Dispatcher::$instances */
	private $index;
	/** @var Dispatcher Previous instance which new dispatch request made */
	private $previousInstance;
	/**
	 * Store service locator object. Used to pass instance into ControllerBase
	 *
	 * @var ServiceLocator
	 *
	 * @see Dispatcher::createController()
	 */
	private $serviceLocator;
	/** @var Http\HttpRequest Associated HttpRequest */
	private $request;
	/** @var Http\HttpResponse Associated HttpResponse */
	private $response;
	/** @var bool Flag determine current instance already dispatch or not */
	private $dispatched = false;
	/** @var bool Flag determine current instance finished their dispatch sequence or not */
	private $completed = false;
	/** @var RouteData Used to create controller and view */
	private $routeData;
	/** @var ControllerBase Controller class which contains user code */
	private $controller;
	/**
	 * Store user class which derived from WebBootstrap.
	 *
	 * This class will be used or called when specific point is reached. This is ultimate way to change how dispatcher working
	 *
	 * @var null||WebBootstrap
	 */
	private $bootstrapClass;

	/**
	 * Create new instance of Dispatcher
	 *
	 * @param \Lh\ServiceLocator $serviceLocator
	 * @param Http\HttpRequest   $request
	 * @param Http\HttpResponse  $response
	 */
	public function __construct(ServiceLocator $serviceLocator, Http\HttpRequest $request, Http\HttpResponse $response = null) {
		if ($response === null) {
			$response = new Http\HttpResponse();
		}
		$this->serviceLocator = $serviceLocator;
		$this->request = $request;
		$this->response = $response;
		$this->bootstrapClass = Application::getInstance()->getBootstrapClass();
	}

	/**
	 * Get all dispatched instance of Dispatcher
	 *
	 * @return \Lh\Web\Dispatcher[]
	 */
	public static function getInstances() {
		return self::$instances;
	}

	/**
	 * Get dispatcher instance at specific index
	 *
	 * Each dispatched instance of dispatcher will be registered by their index. First instance always have zero index and automatically called by framework.
	 *
	 * @param int $idx
	 *
	 * @return Dispatcher|null
	 */
	public static function getInstanceAt($idx) {
		if ($idx < 0 || $idx >= count(self::$instances)) {
			return null;
		}

		return self::$instances[$idx];
	}

	/**
	 * Return currently dispatching instance
	 *
	 * @return Dispatcher|null
	 */
	public static function getExecutingDispatcher() {
		return Dispatcher::$executingDispatcher;
	}

	/**
	 * Set dispatcher index
	 *
	 * Index will set set when instance is begin to dispatching by calling dispatch()
	 *
	 * @param int $value
	 *
	 * @see Dispatacher::dispatch()
	 */
	private function setIndex($value) {
		$this->index = $value;
	}

	/**
	 * Return dispatcher index
	 *
	 * @return int
	 */
	public function getIndex() {
		return $this->index;
	}

	/**
	 * Set calling instance of new dispatcher instance
	 *
	 * @param Dispatcher $value
	 * @param bool       $terminatePrevious
	 */
	private function setPreviousInstance($value, $terminatePrevious) {
		$this->previousInstance = ($value instanceof Dispatcher) ? $value : null;
		if ($this->previousInstance != null && (bool)$terminatePrevious) {
			$this->previousInstance->setCompleted(true);
		}
	}

	/**
	 * Get previous instance of current dispatcher
	 *
	 * @return \Lh\Web\Dispatcher
	 */
	public function getPreviousInstance() {
		return $this->previousInstance;
	}

	/**
	 * Get ServiceLocator object
	 *
	 * @return \Lh\ServiceLocator
	 */
	public function getServiceLocator() {
		return $this->serviceLocator;
	}

	/**
	 * Get HttpRequest object. Note this is differ from native PHP HttpRequest
	 *
	 * @return \Lh\Web\Http\HttpRequest
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * Get HttpResponse object
	 *
	 * @return \Lh\Web\Http\HttpResponse
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * Check whether current instance is already dispatched or not
	 *
	 * @return boolean
	 */
	public function isDispatched() {
		return $this->dispatched;
	}

	/**
	 * Set Completed property
	 *
	 * When this flag enabled then all subsequent dispatch process is voided. This flag is automatically enabled when new instance of dispatcher being dispatched.
	 * Usually this will void the rendering process since creating new dispatcher and dispatching it only visible at user code. User code is done after controller creation
	 *
	 * @param boolean $completed
	 */
	public function setCompleted($completed) {
		$this->completed = $completed;
	}

	/**
	 * Check whether complete flag is present or not
	 *
	 * @return boolean
	 */
	public function isCompleted() {
		return $this->completed;
	}

	/**
	 * Set route date which will be used in dispatch request
	 *
	 * @param RouteData $value
	 */
	private function setRouteData(RouteData $value) {
		$this->routeData = $value;
	}

	/**
	 * Get route date which used in dispatch request
	 *
	 * @return \Lh\Web\RouteData
	 */
	public function getRouteData() {
		return $this->routeData;
	}

	/**
	 * Set controller class
	 *
	 * Each controller must be derived from ControllerBase class
	 *
	 * @param ControllerBase $value
	 *
	 * @throws \InvalidArgumentException
	 * @see ControllerBase
	 */
	private function setController($value) {
		if (!($value instanceof ControllerBase)) {
			throw new \InvalidArgumentException("The given controller doesn't derived from ControllerBase class");
		}
		$this->controller = $value;
	}

	/**
	 * Get controller class
	 *
	 * @return ControllerBase
	 */
	public function getController() {
		return $this->controller;
	}

	/**
	 * Instantiating concrete Controller class
	 *
	 * This concrete controller class will be used while dispatching. We refer any code in controller as user code since it's created by user
	 *
	 * @return string
	 * @throws \RuntimeException
	 */
	private function createController() {
		if ($this->routeData == null) {
			throw new \RuntimeException("Unable to create controller ! No route data available");
		}

		// Step 00: Init
		$this->controller = null;
		// Step 01: Checking controller file availability
		if (count($this->routeData->getNamespaces()) > 0) {
			$fileName = implode(DIRECTORY_SEPARATOR, $this->routeData->getNamespaces()) . DIRECTORY_SEPARATOR . $this->routeData->getControllerClassName() . ".php";
		} else {
			$fileName = $this->routeData->getControllerClassName() . ".php";
		}
		$controllerFile = Application::getInstance()->getControllerPath() . $fileName;
		if (!is_file($controllerFile)) {
			return Dispatcher::CONTROLLER_NO_FILE;
		}
		require_once($controllerFile);

		// Step 02: Checking Controller Class availability
		$className = $this->routeData->getFullyQualifiedName();
		if (!class_exists($className, false)) {
			return Dispatcher::CONTROLLER_NO_CLASS;
		}
		try {
			$this->setController(new $className($this->serviceLocator, $this));
		} catch (\InvalidArgumentException $ex) {
			return Dispatcher::CONTROLLER_INVALID_BASE_CLASS;
		}

		// Step 03: Method must be exists
		if (!method_exists($this->controller, $this->routeData->getMethodName()) && !method_exists($this->controller, "__call")) {
			return Dispatcher::CONTROLLER_NO_METHOD;
		}

		return Dispatcher::CONTROLLER_LOADED;
	}

	/**
	 * Dispatching appropriate controller and their method based on RouteData.
	 *
	 * Dispatcher sequence
	 *  1. Creating concrete Controller
	 *  2. Creating PageView for the loaded controller
	 *  3. Dispatching method
	 *  4. Rendering content
	 *
	 * @param RouteData $routeData
	 * @param bool      $terminatePreviousInstance
	 *
	 * @throws \RuntimeException
	 * @throws \Lh\Exceptions\InvalidStateException
	 */
	public function dispatch(RouteData $routeData, $terminatePreviousInstance = true) {
		if ($this->dispatched) {
			throw new \RuntimeException("Can't dispatching using current instance! Current instance already dispatching RouteData.");
		}
		$previousInstance = Dispatcher::$executingDispatcher;

		if ($this->bootstrapClass !== null) {
			$e = new DispatchEventArgs($this, $routeData, null, null);
			$this->bootstrapClass->onPreDispatch($this, $e);
			if ($e->isDispatchCancelled()) {
				return;
			}
		}

		// Tell system that current dispatcher is already dispatching...
		$this->dispatched = true;
		Dispatcher::$executingDispatcher = $this;
		Dispatcher::$instances[] = $this;

		// Link current dispatcher with previous one
		$this->setIndex(count(Dispatcher::$instances) - 1);
		$this->setPreviousInstance($previousInstance, $terminatePreviousInstance);
		if ($this->previousInstance != null && $this->previousInstance->getRouteData()->toString() == $routeData->toString()) {
			// Cyclic checking
			die("Cyclic reference detected while processing: " . $routeData);
		}

		// Dispatching moved into internal code to make sure executing index is decreased
		$this->setRouteData($routeData);
		$this->_dispatch($routeData);
		Dispatcher::$executingDispatcher = $previousInstance;
	}

	/**
	 * Actual code for dispatching user request based on RouteData
	 *
	 * @see Dispatcher::setRouteData()
	 */
	private function _dispatch() {
		// Creating controller which will be dispatched
		switch ($this->createController()) {
			case Dispatcher::CONTROLLER_NO_FILE:
				$this->dispatchNoFile();

				return;
			case Dispatcher::CONTROLLER_NO_CLASS:
				$this->dispatchNoClass();

				return;
			case Dispatcher::CONTROLLER_INVALID_BASE_CLASS:
				$this->dispatchNoClass();

				return;
			case Dispatcher::CONTROLLER_NO_METHOD:
				$this->dispatchNoMethod();

				return;
			case Dispatcher::CONTROLLER_LOADED:
				break;
		}

		// In-case controller creation failed then dispatcher will create another instance of dispatcher and terminate current one.
		if ($this->isCompleted()) {
			return;
		}

		// Preparation for rendering page (this one still in pre-dispatch stage)
		$view = new PageView();
		$prefix = implode(DIRECTORY_SEPARATOR, $this->routeData->getNamespaceSegments());
		if (strlen($prefix) > 1) {
			$prefix .= DIRECTORY_SEPARATOR;
		}
		$view->setViewFileName($prefix . $this->routeData->getControllerSegment() . DIRECTORY_SEPARATOR . $this->routeData->getMethodSegment());
		foreach ($this->routeData->getNamedParameters() as $key => $value) {
			$this->request->addNamedParameters($key, $value);
		}
		$this->controller->setPageView($view);
		if ($this->isCompleted()) {
			return;
		}

		// This will enter user code... We don't know what will be happened. Preparing for the worst.
		// #01: Section Controller Code
		try {
			// Dispatching controller
			// Note: Every direct print in controller will be cached at cacheContent at controller base class
			if ($this->bootstrapClass !== null) {
				$e = new DispatchEventArgs($this, $this->routeData, $this->controller, $view);
				$this->bootstrapClass->onDispatch($this, $e);
				if ($e->isDispatchCancelled()) {
					return;
				}
			}

			$this->controller->initialize();
			if ($this->isCompleted()) {
				// Allow execution to be halt whenever initialization reach completed state
				return;
			}

			// Make sure finalize() method called since initialize() called
			$this->controller->dispatch($this->routeData->getMethodName(), $this->routeData->getParameters());
			$this->controller->finalize();
			if ($this->isCompleted()) {
				return;
			}

			if ($this->bootstrapClass !== null) {
				$e = new DispatchEventArgs($this, $this->routeData, $this->controller, $view);
				$this->bootstrapClass->onPostDispatch($this, $e);
				if ($e->isDispatchCancelled()) {
					return;
				}
			}
		} catch (Exception $ex) {
			$this->dispatchException($ex, "\\Lh\\Web\\Dispatcher::_dispatch() at controller creation");

			return;
		}

		// #02: Section View Code
		try {
			// Render view process
			if ($this->bootstrapClass !== null) {
				$e = new RenderEventArgs($this, $this->routeData, $this->controller, $view, $view->getMasterView());
				$this->bootstrapClass->onRender($this, $e);
			}

			// Render #01: Rendering PageView
			if ($view->isRequireView()) {
				if (!is_file($view->getViewFileName(true))) {
					// View is required but we don't have appropriate view file
					$this->dispatchNoView();

					return;
				}

				$view->renderContent();
			}
			// Render #02: Rendering MasterView
			$masterView = $view->getMasterView();
			if ($masterView !== null && $masterView->isRequireView()) {
				if (!is_file($masterView->getViewFileName(true))) {
					$this->dispatchNoMasterView($masterView->getViewFileName());

					return;
				}

				$masterView->renderContent();
			}

			if ($this->bootstrapClass !== null) {
				$e = new RenderEventArgs($this, $this->routeData, $this->controller, $view, $view->getMasterView());
				$this->bootstrapClass->onPostRender($this, $e);
			}
		} catch (Exception $ex) {
			$this->dispatchException($ex, "\\Lh\\Web\\Dispatcher::_dispatch() at rendering process");
		}
	}

	/**
	 * Dispatch no-match sequence
	 *
	 * This will render output stated that framework unable to determine user request. Usually user miss type URL
	 *
	 * @param int         $httpErrorCode
	 * @param null|string $httpErrorMessage
	 * @param bool        $useNewResponseObject
	 */
	public function dispatchNoMatch($httpErrorCode = 404, $httpErrorMessage = null, $useNewResponseObject = true) {
		$routeData = new RouteData();
		$routeData->setControllerSegment("error");
		$routeData->setMethodSegment("no-match");

		$this->processError($httpErrorCode, $httpErrorMessage, $useNewResponseObject, $routeData);
	}

	/**
	 * Dispatch no-file sequence
	 *
	 * This will render server error to client since there is no controller file. Similar to no-match request but this only happened when user manually dispatching a request.
	 * It means user code trying to dispatch a request but there is no appropriate file.
	 *
	 * @param int         $httpErrorCode
	 * @param null|string $httpErrorMessage
	 * @param bool        $useNewResponseObject
	 */
	public function dispatchNoFile($httpErrorCode = 500, $httpErrorMessage = null, $useNewResponseObject = true) {
		$routeData = new RouteData();
		$routeData->setControllerSegment("error");
		$routeData->setMethodSegment("no-file");

		$this->processError($httpErrorCode, $httpErrorMessage, $useNewResponseObject, $routeData);
	}

	/**
	 * Dispatch no-class sequence
	 *
	 * This will render server error to client since controller file don't contain class definition. This only happened when user manually dispatching a request
	 * and controller file is corrupted or there is miss type of controller class name
	 *
	 * @param int         $httpErrorCode
	 * @param null|string $httpErrorMessage
	 * @param bool        $useNewResponseObject
	 */
	public function dispatchNoClass($httpErrorCode = 500, $httpErrorMessage = null, $useNewResponseObject = true) {
		$routeData = new RouteData();
		$routeData->setControllerSegment("error");
		$routeData->setMethodSegment("no-class");

		$this->processError($httpErrorCode, $httpErrorMessage, $useNewResponseObject, $routeData);
	}

	/**
	 * Dispatch no-method sequence
	 *
	 * Happened when client request un-exists method segment. Client request any URL and coincidentally its request mapped to a proper controller but un-exists method.
	 *
	 * @param int         $httpErrorCode
	 * @param null|string $httpErrorMessage
	 * @param bool        $useNewResponseObject
	 */
	public function dispatchNoMethod($httpErrorCode = 500, $httpErrorMessage = null, $useNewResponseObject = true) {
		$routeData = new RouteData();
		$routeData->setControllerSegment("error");
		$routeData->setMethodSegment("no-method");

		$this->processError($httpErrorCode, $httpErrorMessage, $useNewResponseObject, $routeData);
	}

	/**
	 * Dispatch no-view sequence
	 *
	 * This will render server error to client since framework unable to find appropriate view file (template file). This can be happen because user forgot to
	 * supply default view file or redirect view file to non-exists one
	 *
	 * @param int         $httpErrorCode
	 * @param null|string $httpErrorMessage
	 * @param bool        $useNewResponseObject
	 */
	public function dispatchNoView($httpErrorCode = 500, $httpErrorMessage = null, $useNewResponseObject = true) {
		$routeData = new RouteData();
		$routeData->setControllerSegment("error");
		$routeData->setMethodSegment("no-view");

		$this->processError($httpErrorCode, $httpErrorMessage, $useNewResponseObject, $routeData);
	}

	/**
	 * Dispatch no-master-view sequence
	 *
	 * This will render server error to client since framework unable to find appropriate master view file from its view. This can be happen when user dynamically
	 * set master view but there is no master view file.
	 *
	 * @param string      $masterViewPath
	 * @param int         $httpErrorCode
	 * @param null|string $httpErrorMessage
	 * @param bool        $useNewResponseObject
	 */
	public function dispatchNoMasterView($masterViewPath, $httpErrorCode = 500, $httpErrorMessage = null, $useNewResponseObject = true) {
		$routeData = new RouteData();
		$routeData->setControllerSegment("error");
		$routeData->setMethodSegment("no-master-view");

		$routeData->addNamedParameter("masterViewPath", $masterViewPath);

		$this->processError($httpErrorCode, $httpErrorMessage, $useNewResponseObject, $routeData);
	}

	/**
	 * Dispatch error sequence
	 *
	 * This kind of dispatch called when user code (either controller or view) have un-expected error. This also called when user configuration file contains error.
	 *
	 * @param string      $errorMessage
	 * @param int         $httpErrorCode
	 * @param null|string $httpErrorMessage
	 * @param bool        $useNewResponseObject
	 */
	public function dispatchError($errorMessage, $httpErrorCode = 500, $httpErrorMessage = null, $useNewResponseObject = true) {
		$routeData = new RouteData();
		$routeData->setControllerSegment("error");
		$routeData->setMethodSegment("generic");

		$routeData->addNamedParameter("errorMessage", $errorMessage);

		$this->processError($httpErrorCode, $httpErrorMessage, $useNewResponseObject, $routeData);
	}

	/**
	 * Dispatch un-handled exception sequence
	 *
	 * This kind of dispatch called when user code (either controller or view) have un-expected error.
	 *
	 * @param Exception   $ex
	 * @param string      $source
	 * @param int         $httpErrorCode
	 * @param null|string $httpErrorMessage
	 * @param bool        $useNewResponseObject
	 */
	public function dispatchException(Exception $ex, $source, $httpErrorCode = 500, $httpErrorMessage = null, $useNewResponseObject = true) {
		$routeData = new RouteData();
		$routeData->setControllerSegment("error");
		$routeData->setMethodSegment("un-caught");

		$routeData->addNamedParameter("exception", $ex);
		$routeData->addNamedParameter("source", $source);

		$this->processError($httpErrorCode, $httpErrorMessage, $useNewResponseObject, $routeData);
	}

	/**
	 * Dispatch not-authenticated sequence
	 *
	 * This kind of dispatch must be called manually since there is no tight integration between application and authentication mechanism.
	 * User code have responsibility to check whether current user already authenticated or not.
	 * TIPS: Use Bootstrap feature to hook to each request made by user by handle onRequest()
	 *
	 * @param int         $httpErrorCode
	 * @param null|string $httpErrorMessage
	 * @param bool        $useNewResponseObject
	 */
	public function dispatchNotAuthenticated($httpErrorCode = 403, $httpErrorMessage = null, $useNewResponseObject = true) {
		$routeData = new RouteData();
		$routeData->setControllerSegment("error");
		$routeData->setMethodSegment("not-authenticated");

		$this->processError($httpErrorCode, $httpErrorMessage, $useNewResponseObject, $routeData);
	}

	/**
	 * Dispatch not-authorized sequence
	 *
	 * This kind of dispatch must be called manually since there is no tight integration between application and authentication mechanism.
	 * User code have responsibility to check whether current logged user is authorized to access specific resource or not
	 * TIPS: Use Bootstrap feature to hook to each request made by user by handle onRequest()
	 *
	 * @param int         $httpErrorCode
	 * @param null|string $httpErrorMessage
	 * @param bool        $useNewResponseObject
	 */
	public function dispatchNotAuthorized($httpErrorCode = 401, $httpErrorMessage = null, $useNewResponseObject = true) {
		$routeData = new RouteData();
		$routeData->setControllerSegment("error");
		$routeData->setMethodSegment("not-authorized");

		$this->processError($httpErrorCode, $httpErrorMessage, $useNewResponseObject, $routeData);
	}

	/**
	 * Dispatching error based route data
	 *
	 * @param int         $httpErrorCode
	 * @param null|string $httpErrorMessage
	 * @param bool        $useNewResponseObject
	 * @param RouteData   $routeData
	 */
	private function processError($httpErrorCode, $httpErrorMessage, $useNewResponseObject, $routeData) {
		if ($this->isDispatched()) {
			$response = $useNewResponseObject ? new Http\HttpResponse() : $this->response;
			$response->setStatusCode($httpErrorCode, $httpErrorMessage);
			$dispatcher = new Dispatcher($this->serviceLocator, $this->request, $response);
			$dispatcher->dispatch($routeData);
		} else {
			$this->response->setStatusCode($httpErrorCode, $httpErrorMessage);
			$this->dispatch($routeData, true);
		}
	}
}

// End of File: Dispatcher.php 