<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Collections;

use Countable as ICountable;
use SeekableIterator as ISeekableIterator;

/**
 * Class ArrayListIterator
 *
 * Helper class for iterating all item(s) in a ArrayList
 *
 * @package Lh\Collections
 */
class ArrayListIterator implements ISeekableIterator, ICountable {
	/** @var int Position on iterator */
	private $index = 0;
	/** @var int Total iteration */
	private $max = 0;
	/** @var array Array data which will be iterated */
	private $array;

	/**
	 * Create new instance of ArrayListIterator
	 *
	 * @param array $array
	 */
	public function __construct($array) {
		$this->array = $array;
		$this->max = count($this->array);
	}


	/**
	 * Return the current element
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 *
	 * @return mixed
	 */
	public function current() {
		return $this->array[$this->index];
	}

	/**
	 * Move forward to next element
	 *
	 * @link http://php.net/manual/en/iterator.next.php
	 *
	 * @return void
	 */
	public function next() {
		$this->index++;
	}

	/**
	 * Return the key of the current element
	 *
	 * @link http://php.net/manual/en/iterator.key.php
	 *
	 * @return mixed scalar
	 */
	public function key() {
		return $this->index;
	}

	/**
	 * Checks if current position is valid
	 *
	 * @link http://php.net/manual/en/iterator.valid.php
	 *
	 * @return bool
	 */
	public function valid() {
		return $this->index < $this->max;
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 *
	 * @return void
	 */
	public function rewind() {
		$this->index = 0;
	}

	/**
	 * Seeks to a position
	 *
	 * @link http://php.net/manual/en/seekableiterator.seek.php
	 *
	 * @param int $position
	 *
	 * @return void
	 */
	public function seek($position) {
		$this->index = (int)$position;
	}

	/**
	 * Count elements in current iterator
	 *
	 * @link http://php.net/manual/en/countable.count.php
	 *
	 * @return int
	 */
	public function count() {
		return $this->max;
	}
}

// End of File: ArrayListIterator.php 