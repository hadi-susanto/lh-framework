<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Validations;

/**
 * Interface IValidation
 *
 * Enable an object to validate itself.
 *
 * @package Lh\Validations
 */
interface IValidation {
	/**
	 * Check whether current object validity.
	 *
	 * @return bool
	 */
	public function isValid();

	/**
	 * Return messages of invalid component in array format
	 *
	 * @return string[]
	 */
	public function getInvalidMessages();
}

// End of File: IValidation.php 