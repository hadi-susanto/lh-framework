<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web\Html;

/**
 * Class Script
 *
 * Represent <SCRIPT> html element
 *
 * @package Lh\Web\Html
 */
class Script extends Element {
	/**
	 * Create new instance of Script
	 *
	 * @param null|string $src
	 * @param string      $type
	 */
	public function __construct($src = null, $type = "text/javascript") {
		parent::__construct("script", null);
		if ($src !== null) {
			$this->addAttribute("src", $src);
		}
		$this->addAttribute("type", $type);
		$this->setShortStyleAllowed(false);

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
		return sprintf('<script %s>%s</script>', $this->compileAttributes(), $this->value);
	}
}

// End of File: Script.php 