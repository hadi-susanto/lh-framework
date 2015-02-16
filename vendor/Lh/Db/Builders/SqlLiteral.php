<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Builders;

use Lh\Collections\Dictionary;

/**
 * Class SqlLiteral
 *
 * Pure SQL literal string. Any string in this class will be returned unprocessed by any platform.
 *
 * @package Lh\Db\Builders
 */
class SqlLiteral implements ILiteral {
	/** @var string String expression */
	private $expression;

	/**
	 * Create new instance of SqlLiteral
	 *
	 * @param $expression
	 */
	public function __construct($expression) {
		$this->expression = $expression;
	}

	/**
	 * Set string expression
	 *
	 * Expression can be anything as long it's a valid sql expression. You should aware about expression which only available for specific database engine
	 * because some Database engine have their own expression for specific purpose
	 *
	 * @param string $expression
	 */
	public function setExpression($expression) {
		$this->expression = $expression;
	}

	/**
	 * Get string expression
	 *
	 * @return string
	 */
	public function getExpression() {
		return $this->expression;
	}

	/**
	 * As class name implying, literal will not create/add any parameter to collection
	 *
	 * @param Dictionary $parameterContainer
	 *
	 * @return string
	 */
	public function toString(Dictionary $parameterContainer = null) {
		return $this->expression;
	}

	/**
	 * Provide direct string printing using magic method
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->toString();
	}
}

// End of File: SqlLiteral.php