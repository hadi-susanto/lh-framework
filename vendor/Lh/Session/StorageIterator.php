<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Session;

use Countable as ICountable;
use Iterator as IIterator;

/**
 * Class StorageIterator
 *
 * Iterator for Storage object, this will iterate all object based on storage metadata.
 * Variables and flash messages will be returned in order they added into storage
 *
 * @package Lh\Session
 */
class StorageIterator implements IIterator, ICountable {
	const VARIABLES = 1;
	const FLASH_MESSAGES = 2;
	const BOTH = 3;

	/** @var Storage Object which will be iterated */
	private $storage;
	/** @var array Storage keys */
	private $keys;
	/** @var mixed Current iteration key */
	private $currentKey = null;

	/**
	 * Create new instance of StorageIterator
	 *
	 * @param Storage|string $storage
	 * @param int            $type
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct($storage, $type = StorageIterator::BOTH) {
		if ($storage instanceof Storage) {
			$this->storage = $storage;
		} else if (is_string($storage)) {
			$this->storage = new Storage($storage);
		} else {
			throw new \InvalidArgumentException("Unable to create instance of Storage from: " . get_class($storage));
		}

		if ($type == StorageIterator::BOTH) {
			$this->keys = array_keys($this->storage->getMetaData());
		} else {
			$keys = $this->storage->getMetaData();
			foreach ($keys as $key => $metaData) {
				if ($metaData["type"] == Storage::STORAGE_VARIABLE && $type == StorageIterator::VARIABLES) {
					$this->keys[] = $key;
				} else if ($metaData["type"] == Storage::STORAGE_FLASH && $type == StorageIterator::FLASH_MESSAGES) {
					$this->keys[] = $key;
				}
			}
		}
		$this->currentKey = current($this->keys);
	}


	/**
	 * Return the current element
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 */
	public function current() {
		$metaData = $this->storage->getMetaDataByKey($this->currentKey);
		switch ($metaData["type"]) {
			case Storage::STORAGE_FLASH:
				return $this->storage->getFlash($this->currentKey);
			case Storage::STORAGE_VARIABLE:
			default:
				return $this->storage->get($this->currentKey);
		}
	}

	/**
	 * Move forward to next element
	 *
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next() {
		$this->currentKey = next($this->keys);
	}

	/**
	 * Return the key of the current element
	 *
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key() {
		return $this->currentKey;
	}

	/**
	 * Checks if current position is valid
	 *
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid() {
		return $this->currentKey !== false;
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind() {
		$this->currentKey = reset($this->keys);
	}

	/**
	 * Count elements of an object
	 *
	 * @link http://php.net/manual/en/countable.count.php
	 * @return int
	 */
	public function count() {
		return count($this->keys);
	}
}

// End of File: StorageIterator.php 