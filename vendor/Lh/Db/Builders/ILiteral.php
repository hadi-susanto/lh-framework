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
 * Interface ILiteral
 *
 * Providing free text implementation for generate SQL.
 *
 * @package Lh\Db\Builders
 */
interface ILiteral {
	/**
	 * Generate string representation of current object.
	 * IMPORTANT: Since it's can be contains any string then this maybe not cross database platform and can cause un-escaped string
	 *
	 * @param Dictionary $parameterContainer if string representation must be parameterized then pass an instance of Dictionary
	 *
	 * @return string
	 */
	public function toString(Dictionary $parameterContainer = null);
}

// End of File: ILiteral.php 