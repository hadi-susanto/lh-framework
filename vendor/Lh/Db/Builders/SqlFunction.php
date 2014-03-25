<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Builders;

use Lh\Collections\Dictionary;

/**
 * Class SqlFunction
 *
 * Used as placeholder for calling native SQL function such as Aggregate function
 *
 * @package Lh\Db\Builders
 */
class SqlFunction implements ILiteral {
	/** @var \Lh\Db\IPlatform Used while compiling query */
	private $platform;
	/** @var string SQL function name */
	private $functionName;
	/** @var array Function parameter(s) */
	private $parameters = array();

	/**
	 * Create new instance of SqlFunction
	 *
	 * @param \Lh\Db\IPlatform $platform
	 * @param string           $functionName
	 */
	public function __construct($platform, $functionName) {
		$this->platform = $platform;
		$this->setFunctionName($functionName);
	}

	/**
	 * Set SQL function name
	 *
	 * This function name will be used when toString() called
	 *
	 * @param string $functionName
	 */
	public function setFunctionName($functionName) {
		$this->functionName = strtoupper($functionName);
	}

	/**
	 * Get SQL function name
	 *
	 * @return string
	 */
	public function getFunctionName() {
		return $this->functionName;
	}

	/**
	 * Add parameter to the function
	 *
	 * @param string $parameter
	 * @param bool   $isIdentifier Does the $parameter is sql identifier ?
	 */
	public function addParameter($parameter, $isIdentifier = true) {
		$this->parameters[] = array("value" => $parameter, "isIdentifier" => $isIdentifier);
	}

	/**
	 * Get all parameter(s) from current SqlFunction
	 *
	 * @return array
	 */
	public function getParameters() {
		return $this->parameters;
	}

	/**
	 * Generate string representation of current SqlFunction
	 *
	 * @param Dictionary $parameterContainer
	 *
	 * @return string
	 */
	public function toString(Dictionary $parameterContainer = null) {
		$buff = array();
		foreach ($this->parameters as $parameter) {
			if ($parameter["isIdentifier"]) {
				$buff[] = $this->platform->quoteIdentifierList($parameter["value"], array("^", "*", "/", "+", "-", "DISTINCT"));
			} else if ($parameterContainer !== null) {
				$parameterName = "param" . ($parameterContainer->count() + 1);
				$parameterContainer->set($parameterName, $parameter["value"]);
				$buff[] = $this->platform->formatParameterName($parameterName);
			} else {
				if (is_null($parameter["value"])) {
					$buff[] = "NULL";
				} else if ($parameter["value"] instanceof ILiteral) {
					$buff[] = $parameter["value"]->toString($parameterContainer);
				} else {
					$buff[] = $this->platform->quoteValue($parameter["value"]);
				}
			}
		}
		unset($parameter);
		$parameters = "(" . implode(", ", $buff) . ")";

		return $this->functionName . $parameters;
	}

	/**
	 * Provide direct print using magic method
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->toString();
	}
}

// End of File: SqlFunction.php