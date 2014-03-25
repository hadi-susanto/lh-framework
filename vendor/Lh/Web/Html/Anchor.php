<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web\Html;

/**
 * Class Anchor
 *
 * Represent <a> html element
 *
 * @package Lh\Web\Html
 */
class Anchor extends Element {
	/**
	 * Create new instance of Anchor
	 *
	 * @param string      $value This value will be used as anchor text
	 * @param null|string $href
	 */
	public function __construct($value, $href = null) {
		parent::__construct("a", $value);
		$this->setHref($href);
	}

	/**
	 * Set href attribute
	 *
	 * When $href value equal to null then href attribute is removed
	 *
	 * @param string $href
	 */
	public function setHref($href) {
		if ($href == null) {
			$this->removeAttribute("href");
		} else {
			$this->addAttribute("href", $href);
		}
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
			return sprintf('<a %s>%s</a>', $this->compileAttributes(), $this->value);
		} else {
			return sprintf('<a>%s</a>', $this->value);
		}
	}
}

// End of File: Anchor.php 