<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Utilities\Collections;

/**
 * Class ArrayExtension
 *
 * @package Lh\Utilities\Collections
 * @static
 */
class ArrayExtension {
	/**
	 * Return string representation of an array
	 *
	 * This method used to printing array value into readable string. This method can be used against nested array.
	 *
	 * @param array $array
	 * @return string
	 */
	public static function toString($array) {
		$buff = array();

		foreach ($array as $key => $value) {
			if (is_null($value)) {
				$buff[] = sprintf("'%s' => null", $key);
			}
			else if (is_float($value) || is_int($value) || is_double($value)) {
				if (is_infinite($value)) {
					$buff[] = sprintf("'%s' => infinity", $key);
				}
				else {
					$buff[] = sprintf("'%s' => %s", $key, $value);
				}
			}
			else if (is_bool($value)) {
				$buff[] = sprintf("'%s' => %s", $key, ($value ? "true" : "false"));
			}
			else if (is_string($value)) {
				$buff[] = sprintf("'%s' => '%s'", $key, $value);
			}
			else if (is_array($value)) {
				$buff[] = sprintf("'%s' => %s", $key, ArrayExtension::toString($value));
			}
			else if (is_resource($value)) {
				$buff[] = sprintf("'%s' => %s", $key, get_resource_type($value));
			}
			else if (is_object($value)) {
				$buff[] = sprintf("'%s' => %s", $key, get_class($value));
			}
			else {
				$buff[] = sprintf("'%s' => unknown", $key);
			}
		}

		return "array(" . implode(", ", $buff) . ")";
	}

	/**
	 * Used to get index of specific item in an array. It will return -1 if specific item not found.
	 * It will use anonymous function / closure to determine which item to be search.
	 * This anonymous function will take one parameter and should return bool. Parameter in function will be item in each array and the function should return true when current item meet the criteria
	 *
	 * @param array    $array
	 * @param \Closure $closure
	 *
	 * @return int|string
	 */
	public static function indexOf(array &$array, \Closure $closure) {
		foreach ($array as $idx => $value) {
			if ($closure($value)) {
				return $idx;
			}
		}

		return -1;
	}
}

// End of File: ArrayExtension.php 
