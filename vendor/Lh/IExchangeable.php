<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh;

/**
 * Interface IExchangeable
 *
 * Define contract that each instantiated object able to fill their properties (including private and protected) from array values.
 * And this object also can be converted into simple array object. Array returned from toArray() should be compatible with exchangeArray() method! *
 * IMPORTANT: Any class which have trouble when serialized should implement this interface and Serializable interface (Native PHP interface). Example
 *
 * @example "Auth/User.php" 187 This User class have protected $permissions which will be stored in session file
 *
 * @package Lh
 */
interface IExchangeable {
	/**
	 * Set current instance properties from given array.
	 *
	 * @param array $values
	 *
	 * @return void
	 */
	public function exchangeArray(array $values);

	/**
	 * Return representation of current object in array format.
	 *
	 * Returned array should be compatible with exchangeArray()
	 *
	 * @return array
	 */
	public function toArray();
}

// End of File: IInterchangeable.php 