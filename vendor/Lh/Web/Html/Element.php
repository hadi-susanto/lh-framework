<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web\Html;

/**
 * Class Element
 *
 * Base class for every HTML element. This class define that each element should have name, attributes and value.
 *
 * @package Lh\Web\Html
 */
abstract class Element {
	/** @var string Element tag name. Will used as opening and closing tag */
	protected $tagName;
	/** @var bool Does this element allowed short closing tag style? */
	protected $shortStyleAllowed = false;
	/** @var string|Element Value for current element */
	protected $value;
	/** @var string[] Attributes for current element */
	protected $attributes;

	/**
	 * Create a new instance of html element
	 *
	 * Create a new html element based on given name. Please note that we don't perform element name validity
	 *
	 * @param string              $tagName
	 * @param null|string|Element $value
	 */
	public function __construct($tagName, $value = null) {
		$this->tagName = $tagName;
		if ($value !== null) {
			$this->setValue($value);
		}
	}

	/**
	 * Set Element name
	 *
	 * @param string $name
	 */
	protected function setTagName($name) {
		$this->tagName = $name;
	}

	/**
	 * Get Element name
	 *
	 * @return string
	 */
	public function getTagName() {
		return $this->tagName;
	}

	/**
	 * Get 'id' attribute for current element
	 *
	 * @return string
	 */
	public function getId() {
		return $this->getAttribute("id");
	}

	/**
	 * Set 'id' attribute for current element.
	 *
	 * When working with HTML then only element with 'name' attribute will send to server. Therefore id will considered as its name if there is no 'name' attribute.
	 * If 'name' attribute already exists then it will use existing name instead of id.
	 *
	 * @param string $id
	 */
	public function setId($id) {
		$this->addAttribute("id", $id);
	}

	/**
	 * Get 'name' attribute for current element
	 */
	public function getName() {
		return $this->getAttribute("name");
	}

	/**
	 * Set 'name' attribute
	 *
	 * @param string $name
	 */
	public function setName($name) {
		$this->addAttribute("name", $name);
	}

	/**
	 * Get 'class' attribute
	 *
	 * Element can have multiple css class separated by space. By default this method will return css class in string format rather than array. Please
	 * use parameter to change this behaviour.
	 *
	 * @param bool $asArray Should we return result in array format
	 *
	 * @return string|string[]|null
	 */
	public function getClass($asArray = false) {
		$class = $this->getAttribute("class");
		if ($class !== null && $asArray) {
			return explode(" ", $class);
		} else {
			return $class;
		}
	}

	/**
	 * Set 'class' attribute
	 *
	 * @param string|string[] $class
	 */
	public function setClass($class) {
		if (is_array($class)) {
			$class = implode(" ", $class);
		}

		$this->addAttribute("class", $class);
	}

	/**
	 * Set value for current element
	 *
	 * Set value for current element. These value can be a literal string or another instance of Element
	 *
	 * @param string|Element $value
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setValue($value) {
		if (is_string($value) || ($value instanceof Element) || is_null($value)) {
			$this->value = $value;
		} else {
			throw new \InvalidArgumentException("Html element value can only contains string or another html element");
		}
	}

	/**
	 * Get value of current element
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Set allow short close style
	 *
	 * Commonly html element require a full closing tag such as </SCRIPT>, </BODY> etc but there is a special element with special case
	 * can have short closing style as <BR /> and <IMG />.
	 *
	 * @param boolean $allowShortCloseStyle
	 */
	protected function setShortStyleAllowed($allowShortCloseStyle) {
		$this->shortStyleAllowed = $allowShortCloseStyle;
	}

	/**
	 * Check whether current element is allowed to use short closing tag style
	 *
	 * @return boolean
	 */
	public function isShortStyleAllowed() {
		return $this->shortStyleAllowed;
	}

	/**
	 * Get attribute based on its name
	 *
	 * @param string $name
	 *
	 * @return string|null
	 */
	public function getAttribute($name) {
		$name = strtolower($name);

		return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : null;
	}

	/**
	 * Add an attribute to current element
	 *
	 * Add an attribute to an html element, these attribute can include custom attribute specified by JavaScript library.
	 * IMPORTANT: Attribute value will be enclosed by double quote sign! Any value contains double quote sign must be properly escaped
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @throws \InvalidArgumentException
	 */
	public function addAttribute($name, $value) {
		if (!is_string($name)) {
			throw new \InvalidArgumentException("Attribute name should be string.");
		}
		if (!is_string($value)) {
			throw new \InvalidArgumentException("Attribute value should be string.");
		}

		$name = strtolower($name);
		$this->attributes[$name] = $value;
	}

	/**
	 * Remove an attribute from element
	 *
	 * @param string $name
	 */
	public function removeAttribute($name) {
		$name = strtolower($name);
		unset($this->attributes[$name]);
	}

	/**
	 * Remove all attributes from element
	 */
	public function clearAttributes() {
		$this->attributes = array();
	}

	/**
	 * Compiling attribute(s) of current element
	 *
	 * Compiling attributes performed when toString() method called. Note that attribute value will be enclosed using double quote sign.
	 *
	 * @return string
	 */
	protected function compileAttributes() {
		$buff = array();
		foreach ($this->attributes as $name => $value) {
			$buff[] = sprintf('%s="%s"', $name, $value);
		}

		return implode(" ", $buff);
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
	public abstract function toString($forceShortStyle = false);

	/**
	 * Provide direct printing access using magic method
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->toString();
	}
}

// End of File: Element.php 