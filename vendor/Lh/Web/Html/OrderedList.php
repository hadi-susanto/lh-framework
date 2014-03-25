<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web\Html;

/**
 * Class OrderedList
 *
 * Represent <ol> html element
 *
 * @method ListItem[] getItems()
 *
 * @package Lh\Web\Html
 */
class OrderedList extends ListBase {
	/**
	 * Create new instance of <ol>
	 *
	 * This element don't have any item by default.
	 */
	public function __construct() {
		parent::__construct("ol", null);
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

// End of File: OrderedList.php 