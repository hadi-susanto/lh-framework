<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Collections;

use Countable as ICountable;
use Iterator as IIterator;

/**
 * Class DictionaryIterator
 *
 * Helper class for iterating all item(s) in a Dictionary
 *
 * @see Dictionary
 * @package Lh\Collections
 */
class DictionaryIterator implements IIterator, ICountable {
	/** @var \Lh\Collections\Dictionary Object that will be iterated */
	private $dictionary;
	/** @var array Keys from $dictionary */
	private $keys;
	/** @var mixed Current index of $dictionary */
	private $currentKey;

	/**
	 * Create new instance of DictionaryIterator
	 *
	 * @param Dictionary $dictionary
	 */
	public function __construct(Dictionary $dictionary) {
		$this->dictionary = $dictionary;
		$this->keys = $this->dictionary->getKeys();
		$this->currentKey = current($this->keys);
	}

	/**
	 * Return the current element
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 *
	 * @return mixed
	 */
	public function current() {
		return $this->dictionary->get($this->currentKey);
	}

	/**
	 * Move forward to next element
	 *
	 * @link http://php.net/manual/en/iterator.next.php
	 *
	 * @return void
	 */
	public function next() {
		$this->currentKey = next($this->keys);
	}

	/**
	 * Return the key of the current element
	 *
	 * @link http://php.net/manual/en/iterator.key.php
	 *
	 * @return mixed scalar
	 */
	public function key() {
		return $this->currentKey;
	}

	/**
	 * Checks if current position is valid
	 *
	 * @link http://php.net/manual/en/iterator.valid.php
	 *
	 * @return bool
	 */
	public function valid() {
		return $this->currentKey !== false;
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 *
	 * @return void
	 */
	public function rewind() {
		$this->currentKey = reset($this->keys);
	}

	/**
	 * Count elements of current iterator
	 *
	 * @link http://php.net/manual/en/countable.count.php
	 *
	 * @return int
	 */
	public function count() {
		return count($this->keys);
	}
}

// End of File: DictionaryIterator.php 