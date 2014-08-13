<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web\Html;

use Lh\Exceptions\InvalidStateException;

/**
 * Class Css
 *
 * Represent either <LINK rel="stylesheet"> or <STYLE type="text/css"> html element
 *
 * @package Lh\Web\Html
 */
class Css extends Element {
	/** @var bool Do the css should use <LINK> instead of <STYLE> */
	private $linkedStyle = false;

	/**
	 * Create new instance of Css
	 *
	 * @param null|string $src
	 * @param string      $type
	 */
	public function __construct($src = null, $type = "text/css") {
		if ($src === null) {
			parent::__construct("style", null);
			$this->linkedStyle = false;
		} else {
			parent::__construct("link", null);
			parent::addAttribute("rel", "stylesheet");
			parent::addAttribute("href", $src);
			$this->linkedStyle = true;
		}

		parent::addAttribute("type", $type);
		$this->setShortStyleAllowed(true);
	}

	/**
	 * Add an attribute to current element
	 *
	 * Add an attribute to an html element, these attribute can include custom attribute specified by JavaScript library.
	 * IMPORTANT:
	 *  - Attribute value will be enclosed by double quote sign! Any value contains double quote sign must be properly escaped
	 *  - Adding 'href' attribute to Css will change its name into <LINK>
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function addAttribute($name, $value) {
		parent::addAttribute($name, $value);

		$name = strtolower($name);
		if ($name == "href") {
			$this->linkedStyle = true;
			$this->setTagName("link");
			parent::addAttribute("rel", "stylesheet");
		}
	}

	/**
	 * Remove an attribute from element
	 *
	 * Similar to Css::addAttribute(), removing 'href' or 'rel' will change its element into <STYLE>
	 *
	 * @param string $name
	 */
	public function removeAttribute($name) {
		parent::removeAttribute($name);

		$name = strtolower($name);
		if ($name == "href") {
			$this->linkedStyle = false;
			$this->setTagName("style");
			parent::removeAttribute("rel");
		} else if ($name == "rel") {
			$this->linkedStyle = false;
			$this->setTagName("style");
			parent::removeAttribute("href");
		}
	}

	/**
	 * Set value for current element
	 *
	 * Set value for current element. These value can be a literal string or another instance of Element
	 * NOTE: setValue() only worked with <STYLE> not <LINK>
	 *
	 * @param Element|string $value
	 *
	 * @throws \Lh\Exceptions\InvalidStateException
	 */
	public function setValue($value) {
		if ($this->linkedStyle) {
			throw new InvalidStateException("Unable to set value for <LINK> element. Please remove 'href' attribute first.");
		}

		parent::setValue($value);
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
		if ($this->linkedStyle) {
			return sprintf('<link %s />', $this->compileAttributes());
		} else {
			// Make sure our <STYLE> don't have 'rel' attribute
			parent::removeAttribute("rel");

			return sprintf('<style %s>%s</style>', $this->compileAttributes(), $this->value);
		}
	}
}

// End of File: Css.php 