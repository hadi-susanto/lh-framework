<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web\Html;

/**
 * Class UnOrderedList
 *
 * Represent <ul> html element
 *
 * @method ListItem[] getItems()
 *
 * @package Lh\Web\Html
 */
class UnOrderedList extends ListBase {
	/**
	 * Create new instance of <ul>
	 *
	 * This element don't have any item by default.
	 */
	public function __construct() {
		parent::__construct("ul", null);
	}

	/**
	 * Add an item into collections
	 *
	 * Since different list have different item type then this method must be abstracted. Each derived class must supply how to add item into $items variable
	 * Example <ul> and <ol> only accept <li> but <select> can accept either <option> or <optgroup>
	 *
	 * @param string|ListItem|Element $item
	 *
	 * @throws \InvalidArgumentException
	 * @return ListItem
	 */
	public function addItem($item) {
		if ($item instanceof ListItem) {
			$this->items[] = $item;
		} else if (is_string($item) || $item instanceof Element) {
			$item = new ListItem($item);
			$this->items[] = $item;
		} else {
			throw new \InvalidArgumentException("OrderedList::addItem() only accept ListItem or string or another Element");
		}

		return $item;
	}
}

// End of File: UnOrderedList.php 