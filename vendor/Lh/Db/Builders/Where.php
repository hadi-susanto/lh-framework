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
 * Class Where
 *
 * Represent WHERE statement for used in other SQL statement
 *
 * @package Lh\Db\Builders
 */
class Where implements ILiteral {
	/** @var \Lh\Db\IPlatform Used while compiling query */
	private $platform;
	/** @var string Where operand field name */
	private $field;
	/** @var string|ISelect Where operand value */
	private $value;
	/** @var string Where operator */
	private $operator;

	/**
	 * Create new instance of Where clause
	 *
	 * @param \Lh\Db\IPlatform $platform
	 * @param string           $field
	 * @param string|ISelect   $value
	 * @param string           $operator
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct($platform, $field, $value, $operator = "=") {
		if (is_null($value) && !in_array($operator, array("=", "!=", "<>", "IS", "IS NOT"))) {
			throw new \InvalidArgumentException("Operator for NULL value is limited to '=', '!=', '<>', 'IS' or 'IS NOT'");
		}
		if ((is_array($value) || $value instanceof ISelect) && !in_array($operator, array("=", "!=", "<>", "IN", "NOT IN"))) {
			throw new \InvalidArgumentException("Operator for array or ISelect value is limited to '=', '!=', '<>', 'IN' or 'NOT IN'");
		}

		$this->platform = $platform;
		$this->field = $field;
		$this->value = $value;
		$this->operator = $operator;
	}

	/**
	 * Generate string representation of Where clause
	 *
	 * This will generate string representation of where clause, if the parameter container passed then it will create parameterized string
	 *
	 * @param Dictionary $parameterContainer
	 *
	 * @return string
	 */
	public function toString(Dictionary $parameterContainer = null) {
		$format = "%s %s %s";
		$field = null;
		$operator = $this->operator;
		$operand = null;
		$useParameter = ($parameterContainer !== null);

		// Field detection
		if ($this->field instanceof ILiteral) {
			$field = $this->field->toString($parameterContainer);
		} else {
			$field = $this->platform->quoteIdentifierList($this->field);
		}

		// Value detection
		if (is_null($this->value)) {
			$operator = ($operator == "=" || $operator == "IS") ? "IS" : "IS NOT";
			$operand = $useParameter ? null : "NULL";
		} else if (is_array($this->value)) {
			$operator = ($operator == "=" || $operator == "IN") ? "IN" : "NOT IN";
			$buff = array();
			foreach ($this->value as $value) {
				if ($useParameter) {
					$parameterName = "where" . ($parameterContainer->count() + 1);
					$parameterContainer->set($parameterName, $value);
					$buff[] = $this->platform->formatParameterName($parameterName);
				} else {
					$buff[] = $this->platform->quoteValue($value);
				}
			}
			$operand = "(" . implode(", ", $buff) . ")";
			unset($buff);
			// Since parameter already added we MUST prevent re-adding of parameter
			$useParameter = false;
		} else if ($this->value instanceof ISelect) {
			$operator = ($operator == "=" || $operator == "IN") ? "IN" : "NOT IN";
			if ($useParameter) {
				$operand = "(" . $this->value->compileWithParameters($parameterContainer, false) . ")";
			} else {
				$operand = "(" . $this->value->compile() . ")";
			}

		} else if ($this->value instanceof ILiteral) {
			$operand = $this->value->toString($parameterContainer);
		} else {
			$operand = $useParameter ? $this->value : $this->platform->quoteValue($this->value);
		}

		if ($useParameter) {
			$parameterName = "where" . ($parameterContainer->count() + 1);
			$parameterContainer->set($parameterName, $operand);

			return sprintf($format, $field, $operator, $this->platform->formatParameterName($parameterName));
		} else {
			return sprintf($format, $field, $operator, $operand);
		}
	}

	/**
	 * Provide direct string translation using magic method
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->toString();
	}
}

// End of File: Where.php 