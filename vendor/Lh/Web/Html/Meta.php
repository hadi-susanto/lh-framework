<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web\Html;

/**
 * Class Meta
 *
 * Represent <META> html element
 *
 * @package Lh\Web\Html
 */
class Meta extends Element {
	/**
	 * Create new instance of Meta
	 */
	public function __construct() {
		parent::__construct("META", null);
	}

	/**
	 * Any given value will be ignored since <META> will not have any value inside.
	 *
	 * @param string $value
	 */
	public function setValue($value) {
		// Do nothing since <META> will not have value
	}

	/**
	 * Compile current Element as string
	 *
	 * Compiling this element into string. Resulting string must be a valid html element with opening and closing tag.
	 *
	 * @param bool $forceShortStyle
	 *
	 * @return string
	 */
	public function toString($forceShortStyle = false) {
		if (count($this->attributes) > 0) {
			return sprintf('<meta %s />', $this->compileAttributes());
		} else {
			return '<meta />';
		}
	}
}

// End of File: Meta.php