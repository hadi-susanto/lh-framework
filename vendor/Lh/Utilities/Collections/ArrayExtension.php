<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
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
			} else if (is_float($value) || is_int($value) ||is_double($value)) {
				if (is_infinite($value)) {
					$buff[] = sprintf("'%s' => infinity", $key);
				} else {
					$buff[] = sprintf("'%s' => %s", $key, $value);
				}
			} else if (is_bool($value)) {
				$buff[] = sprintf("'%s' => %s", $key, ($value ? "true" : "false"));
			} else if (is_string($value)) {
				$buff[] = sprintf("'%s' => '%s'", $key, $value);
			} else if (is_array($value)) {
				$buff[] = sprintf("'%s' => %s", $key, ArrayExtension::toString($value));
			} else if (is_resource($value) || is_object($value)) {
				$buff[] = sprintf("'%s' => %s", $key, (string)$value);
			} else {
				$buff[] = sprintf("'%s' => unknown", $key);
			}
		}

		return "(" . implode(", ", $buff) . ")";
	}
}

// End of File: ArrayExtension.php 
