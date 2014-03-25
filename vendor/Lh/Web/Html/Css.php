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
		} else {
			parent::__construct("link", null);
			$this->addAttribute("href", $src);
			$this->addAttribute("rel", "stylesheet");
			$this->linkedStyle = true;
		}

		$this->addAttribute("type", $type);
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
		if (strtolower($name) == "href") {
			$this->linkedStyle = true;
			$this->setTagName("link");
			parent::addAttribute("rel", "stylesheet");
		}
	}

	/**
	 * Remove an attribute from element
	 *
	 * Similar to Css::addAttribute(), removing 'href' will change its name into <STYLE>
	 *
	 * @param string $name
	 */
	public function removeAttribute($name) {
		parent::removeAttribute($name);
		if (strtolower($name) == "href") {
			$this->linkedStyle = false;
			$this->setTagName("style");
			parent::removeAttribute("rel");
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
			return sprintf('<%1$s %2$s>%3$s</%1$s>', $this->tagName, $this->compileAttributes(), $this->value);
		}
	}
}

// End of File: Css.php 