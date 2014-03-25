<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web;

/**
 * Class RouteData
 *
 * RouteData will store user request mapped to respective controller class name and method name. Parameter(s) from url segment also stored here
 * NOTE:
 *  - We will use Fully Qualified Name (short: FQN) for class name prefixed with their namespace.
 *  - Named parameter(s) values will be copied into HttpRequest instance when it's instance being dispatched because this instance not globally available between dispatch session
 *
 * @package Lh\Web
 */
class RouteData {
	/** @var string[] Namespace segment(s) from URL */
	protected $namespaceSegments;
	/** @var string[] Array of camel cased namespace */
	protected $namespaces;
	/** @var string Controller segment from URL */
	protected $controllerSegment;
	/** @var string Camel cased controller name */
	protected $controllerClassName;
	/** @var string Method name from URL */
	protected $methodSegment;
	/** @var string Camel cased method name */
	protected $methodName;
	/** @var string[] Parameters from URL segment(s) */
	protected $parameters;
	/** @var string[] Named parameter from URL segment(s) */
	protected $namedParameters;

	/**
	 * Create RouteData instance
	 */
	public function __construct() {
		$this->namespaceSegments = array();
		$this->controllerSegment = null;
		$this->controllerClassName = null;
		$this->methodSegment = null;
		$this->methodName = null;
		$this->parameters = array();
		$this->namedParameters = array();
	}

	/**
	 * Get NamespaceSegments property
	 *
	 * @return string[]
	 */
	public function getNamespaceSegments() {
		return $this->namespaceSegments;
	}

	/**
	 * Add a namespace segment into NamespaceSegments property
	 *
	 * @param string $namespaceSegment
	 */
	public function addNamespaceSegment($namespaceSegment) {
		// Translate all '-' into ' ' then upper case each word(s)
		$temp = ucwords(strtolower(str_replace("-", " ", $namespaceSegment)));
		// Now remove the ' '
		$temp = str_replace(" ", "", $temp);

		$this->namespaceSegments[] = $namespaceSegment;
		$this->namespaces[] = $temp;
	}

	/**
	 * Get Namespaces property
	 *
	 * @return string[]
	 */
	public function getNamespaces() {
		return $this->namespaces;
	}

	/**
	 * Set ControllerSegment property
	 *
	 * @param string $controllerSegment
	 */
	public function setControllerSegment($controllerSegment) {
		// Translate all '-' into ' ' then upper case each word(s)
		$temp = ucwords(strtolower(str_replace("-", " ", $controllerSegment)));
		// Now remove the ' '
		$temp = str_replace(" ", "", $temp);

		$this->controllerSegment = $controllerSegment;
		$this->controllerClassName = $temp . "Controller";
	}

	/**
	 * Get ControllerSegment property
	 *
	 * @return string
	 */
	public function getControllerSegment() {
		return $this->controllerSegment;
	}

	/**
	 * Get ControllerClassName property
	 *
	 * @return string
	 */
	public function getControllerClassName() {
		return $this->controllerClassName;
	}

	/**
	 * Obtain Fully Qualified Name (FQN) from the given route.
	 *
	 * @return string
	 */
	public function getFullyQualifiedName() {
		if (count($this->namespaces) > 0) {
			return implode('\\', $this->namespaces) . '\\' . $this->getControllerClassName();
		} else {
			return $this->getControllerClassName();
		}
	}

	/**
	 * Set MethodSegment property
	 *
	 * @param string $methodSegment
	 */
	public function setMethodSegment($methodSegment) {
		// Translate all '-' into ' ' then upper case each word(s)
		$temp = ucwords(strtolower(str_replace("-", " ", $methodSegment)));
		// Now remove the ' '
		$temp = str_replace(" ", "", $temp);

		$this->methodSegment = $methodSegment;
		$this->methodName = lcfirst($temp) . "Action";
	}

	/**
	 * Get MethodSegment property
	 *
	 * @return string
	 */
	public function getMethodSegment() {
		return $this->methodSegment;
	}

	/**
	 * Get MethodName property
	 *
	 * @return string
	 */
	public function getMethodName() {
		return $this->methodName;
	}

	/**
	 * Set Parameters property
	 *
	 * @param string[] $parameters
	 */
	public function setParameters($parameters) {
		$this->parameters = $parameters;
	}

	/**
	 * Get Parameters property
	 *
	 * @return string[]
	 */
	public function getParameters() {
		return $this->parameters;
	}

	/**
	 * Add a parameter into Parameters property
	 *
	 * @param string $param
	 */
	public function addParameter($param) {
		$this->parameters[] = urldecode($param);
	}

	/**
	 * Set NamedParameters property
	 *
	 * @param string[] $namedParameters
	 */
	public function setNamedParameters($namedParameters) {
		$this->namedParameters = $namedParameters;
	}

	/**
	 * Get NamedParameters property
	 *
	 * @return string[]
	 */
	public function getNamedParameters() {
		return $this->namedParameters;
	}

	/**
	 * Add a named parameter into NamedParameters property
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function addNamedParameter($key, $value) {
		if (is_string($value)) {
			$this->namedParameters[urldecode($key)] = urldecode($value);
		} else {
			$this->namedParameters[urldecode($key)] = $value;
		}
	}

	/**
	 * Checking RouteData validity
	 *
	 * A route data only valid when:
	 *  1. A method exists while controller also exists
	 *  2. A parameter exists while method name exists
	 *
	 * @return bool
	 */
	public function isValid() {
		$haveController = ($this->getControllerClassName() != null);
		$haveMethod = ($this->getMethodName() != null);
		$haveParameters = (count($this->getParameters()) > 0);

		// #01: A method can only exists when we have controller
		if ($haveMethod && !$haveController) {
			return false;
		}

		// #02: A parameters can only exists if we have method
		if ($haveParameters && !$haveMethod) {
			return false;
		}

		return true;
	}

	/**
	 * Return representation of current object in array format
	 *
	 * @return array
	 */
	public function toArray() {
		return array(
			"namespaceSegments" => $this->getNamespaceSegments(),
			"controllerSegment" => $this->getControllerSegment(),
			"methodSegment" => $this->getMethodSegment(),
			"namespaces" => $this->getNamespaces(),
			"controller" => $this->getControllerClassName(),
			"method" => $this->getMethodName(),
			"parameters" => $this->getParameters(),
			"namedParameters" => $this->getNamedParameters()
		);
	}

	/**
	 * Return URL like from current RouteData
	 *
	 * @return string
	 */
	public function toUrl() {
		$buff = array();
		if (count($this->namedParameters) > 0) {
			foreach ($this->namedParameters as $key => $value) {
				$buff[] = "$key:$value";
			}
		}

		return implode("/", array_merge($this->namespaceSegments, array($this->controllerSegment, $this->methodSegment), $this->parameters, $buff));
	}

	/**
	 * Return string representation of current RouteData
	 *
	 * @return string
	 */
	public function toString() {
		return $this->getFullyQualifiedName() . "::" . $this->getMethodName();
	}

	/**
	 * String conversion magic method
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->toString();
	}
}

// End of File: RouteData.php 