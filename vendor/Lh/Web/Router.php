<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web;

use Lh\ServiceBase;
use Lh\ServiceLocator;

/**
 * Class Router
 *
 * IN SHORT: Router will extract URL into object which consumed by dispatcher. LH Framework is capable multi folder structure in MVC
 *
 * Router class have objective to extract user request (taken from URL) into appropriate controller, method and parameter(s). Please
 * note that LH Framework is not support 'module(s)' term instead every folder inside controller will be treated as namespace and it's
 * accessible by requested it in URL. Router will try to identify every segment in comparison with your application source folder.
 * Example: you have src/Controller/Another/HelloWorldController.php and it's accessed from www.example.com/another/hello-world. It's
 * Router job to map into HelloWorldController.php
 *
 * Segment pattern:
 * http://www.example.com/namespace/controller/method/param1/param2
 *
 * NOTE: in our framework modules term changed into namespace because we will support multi level namespace
 *
 * @package Lh\Web
 */
class Router extends ServiceBase {
	/** @var string Controller location. Used in detection */
	private $controllerPath;
	/** @var string Default namespace in MVC. Not applied yet */
	private $defaultNamespace;
	/** @var string Default controller when we didn't get any segments */
	private $defaultController;
	/** @var string Default method if method couldn't be extracted */
	private $defaultMethod;
	/** @var string Regex to determine controller and method validity. ToDo: Controller ReGex */
	private $controllerRegex;
	/** @var string Regex to determine parameter validity. ToDo: Parameter regex */
	private $parameterRegex;
	/** @var array Static routes for re-route any route data */
	private $staticRoutes = array();
	/** @var RouteData Processed route data based on URI and static routes */
	private $routeData;

	/**
	 * Create new instance of Router
	 *
	 * @param ServiceLocator $serviceLocator
	 */
	public function __construct(ServiceLocator $serviceLocator) {
		parent::__construct($serviceLocator);
		$serviceLocator->setRouter($this);
	}

	/**
	 * Get default namespace
	 *
	 * @return string
	 */
	public function getDefaultNamespace() {
		return $this->defaultNamespace;
	}

	/**
	 * Get default controller
	 *
	 * @return string
	 */
	public function getDefaultController() {
		return $this->defaultController;
	}

	/**
	 * Get default method
	 *
	 * @return string
	 */
	public function getDefaultMethod() {
		return $this->defaultMethod;
	}

	/**
	 * Get regex pattern for controller and method
	 *
	 * @return string
	 */
	public function getControllerRegex() {
		return $this->controllerRegex;
	}

	/**
	 * Get regex pattern for parameter
	 *
	 * @return string
	 */
	public function getParameterRegex() {
		return $this->parameterRegex;
	}

	/**
	 * Get static routes
	 *
	 * @return array
	 */
	public function getStaticRoutes() {
		return $this->staticRoutes;
	}

	/**
	 * Get processed route data
	 *
	 * @return \Lh\Web\RouteData
	 */
	public function getRouteData() {
		return $this->routeData;
	}

	/**
	 * Initialize Router
	 *
	 * Used to alter how Router class working on user Uri request. Available key:
	 *	- 'defaultNamespace'
	 *  - 'defaultController'
	 *  - 'defaultMethod'
	 *  - 'controllerRegex'
	 *  - 'methodRegex'
	 *  - 'staticRoutes'
	 *
	 * @param array $options
	 */
	public function _init(array $options) {
		$this->defaultNamespace = isset($options["defaultNamespace"]) ? $options["defaultNamespace"] : null;
		$this->defaultController = isset($options["defaultController"]) ? strtolower($options["defaultController"]) : "home";
		$this->defaultMethod = isset($options["defaultMethod"]) ? strtolower($options["defaultMethod"]) : "index";
		$this->controllerRegex = isset($options["controllerRegex"]) ? $options["controllerRegex"] : "[a-zA-Z][a-zA-Z0-9_-]*";
		$this->parameterRegex = isset($options["parameterRegex"]) ? $options["parameterRegex"] : "[a-zA-Z0-9_-]*";
		if (isset($options["staticRoutes"]) && is_array($options["staticRoutes"])) {
			$this->processStaticRoutes($options["staticRoutes"]);
		}

		$this->controllerPath = Application::getInstance()->getControllerPath();
	}

	/**
	 * Processing each routes from system config.
	 *
	 * This method will check and processing static route from system config. Any invalid static route will be ignored instead of throwing an error.
	 * Static route array definition should contain:
	 *  - 'pattern' => string
	 *  - 'action'  => array('controller' => string|int, 'method' => string|int, 'params' => null|int[])
	 *
	 * @param array $staticRoutes
	 */
	private function processStaticRoutes($staticRoutes) {
		foreach ($staticRoutes as $staticRoute) {
			if (!isset($staticRoute["pattern"]) || empty($staticRoute["pattern"])) {
				continue;
			}
			if (!isset($staticRoute["action"]) || !is_array($staticRoute["action"])) {
				continue;
			}

			$pattern = &$staticRoute["pattern"];
			if (strpos($pattern, '/') !== 0) {
				$pattern = '/' . $pattern;
			}
			$pattern = str_replace('/', '\/', $pattern);

			$action = &$staticRoute["action"];
			if (!isset($action["controller"]) || empty($action["controller"])) {
				continue;
			}
			if (!isset($action["method"]) || empty($action["method"])) {
				$action["method"] = $this->defaultMethod;
			}
			if (!isset($action["params"]) || !is_array($action["params"])) {
				$action["params"] = array();
			}

			$this->staticRoutes[] = $staticRoute;
		}
	}

	/**
	 * Determine controller, method, parameter(s) and named parameter(s) based on Uri
	 *
	 * Calculating static route from user config file. All static routes already checked for their validity. Each static route must have pattern, controller and
	 * method key. This checking done by Router::processStaticRoutes() method
	 *
	 * @param Uri $uri
	 *
	 * @return RouteData
	 */
	public function calculateRoute(Uri $uri) {
		// Processing static route data first !
		foreach ($this->staticRoutes as $staticRoute) {
			$pattern = $staticRoute["pattern"] ?: null;
			$action = $staticRoute["action"] ?: null;

			if ($pattern == null || $action == null) {
				continue;
			}

			$matches = null;
			if (preg_match("/^$pattern/i", $uri, $matches) !== 1) {
				continue;
			}

			// Match(es) found!
			$routeData = new RouteData($staticRoute);

			// Set Controller based on route action.
			if (is_numeric($action["controller"]) && isset($matches[$action["controller"]])) {
				$routeData->setControllerSegment($matches[$action["controller"]]);
			} else {
				$routeData->setControllerSegment($action["controller"]);
			}

			// Set Method based on route action
			if (is_numeric($action["method"]) && isset($matches[$action["method"]])) {
				$routeData->setMethodSegment($matches[$action["method"]]);
			} else {
				$routeData->setMethodSegment($action["method"]);
			}

			// Set Parameter or Named Parameter based on route action
			foreach ($action["params"] as $param) {
				$param = (int)$param;
				if ($param > 0 && isset($matches[$param])) {
					$match = $matches[$param];
					if (strpos($match, ":") !== false) {
						list($key, $value) = explode(":", $match, 2);
						// Prevent '/:param' added without any key. These case treated as same key and value pair
						$routeData->addNamedParameter($key ?: $value, $value);
					} else {
						$routeData->addParameter($match);
					}
				} else {
					$routeData->addParameter("");
				}
			}

			return $routeData;
		}

		// No static route data have been hit.
		$controllerFolder = $this->controllerPath;
		$routeData = new RouteData();
		$segments = $uri->getSegments();
		$camelizedSegments = $uri->getCamelizedSegments();

		$max = count($segments);
		$controllerFound = false;
		// #01: Namespace, Controller and Method detection
		for ($i = 0; $i < $max; $i++) {
			if (!$controllerFound) {
				if (is_file($controllerFolder . $camelizedSegments[$i] . "Controller.php")) {
					// Controller file found !
					$routeData->setControllerSegment($segments[$i]);
					$controllerFound = true;
				} else if (is_dir($controllerFolder . $camelizedSegments[$i])) {
					// We found folder...
					$controllerFolder .= $camelizedSegments[$i] . DIRECTORY_SEPARATOR;
					$routeData->addNamespaceSegment($segments[$i]);
				} else {
					// Neither controller or folder but we have a segment ? Break from loop, this RouteData is invalid
					break;
				}
			} else {
				// Assuming method name here if controller have been specified
				$routeData->setMethodSegment($segments[$i]);
				// OK current segment already used as method name don't process as parameter name
				$i++;
				break;
			}
		}

		// #02: Parameters and Named Parameters
		for (; $i < $max; $i++) {
			$segment = $segments[$i];
			if (($pos = strpos($segment, ":")) !== false) {
				list($key, $value) = explode(":", $segment, 2);
				// Prevent '/:param' added without any key. These case treated as same key and value pair
				$routeData->addNamedParameter($key ?: $value, $value);
			} else {
				$routeData->addParameter($segment);
			}
		}

		// #03: Routing complete
		if ($routeData->isValid()) {
			if ($routeData->getControllerClassName() == null) {
				$routeData->setControllerSegment($this->defaultController);
			}
			if ($routeData->getMethodName() == null) {
				$routeData->setMethodSegment($this->defaultMethod);
			}

			return $routeData;
		} else {
			$noRouteMatch = new RouteData();
			$noRouteMatch->setControllerSegment("error");
			$noRouteMatch->setMethodSegment("no-match");

			return $noRouteMatch;
		}
	}
}

// End of File: Router.php 
