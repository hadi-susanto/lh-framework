<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web\Html;

/**
 * Class ListItem
 *
 * Represent <li> html element
 *
 * @package Lh\Web\Html
 */
class ListItem extends Element {
	/**
	 * Create new instance of ListItem
	 *
	 * @param null|string|Element $value
	 */
	public function __construct($value = null) {
		parent::__construct("li", $value);
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
			return sprintf('<li %s>%s</li>', $this->compileAttributes(), $this->getValue());
		} else {
			return sprintf('<li>%s</li>', $this->getValue());
		}
	}
}

// End of File: ListItem.php 