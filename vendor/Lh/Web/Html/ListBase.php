<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web\Html;

/**
 * Class ListBase
 *
 * Base class for <ul>, <ol> and <select> which can have item / value more than one.
 *
 * @package Lh\Web\Html
 */
abstract class ListBase extends Element {
	/** @var Element[] Contain List items */
	protected $items = array();

	/**
	 * Get item(s) from current list
	 *
	 * @return Element[]
	 */
	public function getItems() {
		return $this->items;
	}

	/**
	 * Return value for current un-ordered list
	 *
	 * Since list can have more than one item than specialized variable $items used instead of $value. We must override this method to give proper string value
	 *
	 * @return string
	 */
	public function getValue() {
		return implode("", $this->items);
	}

	/**
	 * Add an item into collections
	 *
	 * Since different list have different item type then this method must be abstracted. Each derived class must supply how to add item into $items variable
	 * Example <ul> and <ol> only accept <li> but <select> can accept either <option> or <optgroup>
	 *
	 * @param string|Element $item
	 *
	 * @return Element Item which successfully added into collection
	 */
	public abstract function addItem($item);

	/**
	 * Remove item based on index
	 *
	 * @param int $idx
	 *
	 * @throws \OutOfBoundsException
	 */
	public function removeItemAt($idx) {
		if ($idx >= count($this->items)) {
			throw new \OutOfBoundsException("Unable remove item at index $idx! Total item in current list is " . count($this->items));
		}

		array_splice($this->items, $idx, 1);
	}

	/**
	 * Remove all item(s) from current list
	 */
	public function clearItems() {
		$this->items = array();
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
			return sprintf('<%s %s>%s</%s>', $this->tagName, $this->compileAttributes(), $this->getValue(), $this->tagName);
		} else {
			return sprintf('<%s>%s</%s>', $this->tagName, $this->getValue(), $this->tagName);
		}
	}
}

// End of File: ListBase.php 